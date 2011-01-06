#!/usr/bin/php -q
<?php
/**
 * $Horde: groupware/scripts/setup.php,v 1.34.2.6 2010-04-10 13:42:46 jan Exp $
 *
 * This script finishes the installation of Horde Groupware Webmail Edition.
 */

/**
 * Shows a prompt for a single configuration setting.
 *
 * @param array $config  The array that should contain the configuration
 *                       array in the end.
 * @param string $name   The name of the configuration setting.
 * @param array $field   A part of the parsed configuration tree as returned
 *                       from Horde_Config.
 */
function question(&$config, $name, $field)
{
    global $cli;

    if (!isset($field['desc'])) {
        // This is a <configsection>.
        $config[$name] = array();
        foreach ($field as $sub => $sub_field) {
            question($config[$name], $sub, $sub_field);
        }
        return;
    }

    $question = $field['desc'];
    $default = $field['default'];
    $values = null;
    if (isset($field['switch'])) {
        $values = array();
        foreach ($field['switch'] as $case => $case_field) {
            $values[$case] = $case_field['desc'];
        }
    } else {
        switch ($field['_type']) {
        case 'boolean':
            $values = array(true => 'Yes', false => 'No');
            $default = (int)$default;
            break;
        case 'enum':
            $values = $field['values'];
            break;
        }
        if (!empty($field['required'])) {
            $question .= '*';
        }
    }

    while (true) {
        $config[$name] = $cli->prompt($question, $values, $default);
        if (empty($field['required']) || $config[$name] !== '') {
            break;
        } else {
            $cli->writeln($cli->red('This field is required.'));
        }
    }

    if (isset($field['switch']) &&
        !empty($field['switch'][$config[$name]]['fields'])) {
        foreach ($field['switch'][$config[$name]]['fields'] as $sub => $sub_field) {
            question($config, $sub, $sub_field);
        }
    }
}

/**
 * Asks for the database superuser and password.
 */
function get_db_user($update, $create_db)
{
    global $cli;
    static $db_user, $db_pass;

    if (!isset($db_user)) {
        $db_user = $cli->prompt('Database superuser for '
                                . ($update ? 'updating' : 'creating') . ' the '
                                . ($create_db ? 'database' : 'tables')
                                . ' if necessary for your database system:');
        $db_pass = $cli->prompt('Specify a password for the database user:');
    }

    return array($db_user, $db_pass);
}

/**
 * Determines or asks for the PHP CLI location.
 */
function get_php_cli()
{
    if (isset($_SERVER['_']) && basename($_SERVER['_']) != 'setup.php') {
        return $_SERVER['_'];
    }
    require_once 'System.php';
    return $GLOBALS['cli']->prompt(wordwrap('Cannot find the location of your PHP CLI program. Please specify the full path to your PHP CLI or hit Enter if PHP is in your path.'), null, System::which('php', 'php'));
}

/**
 * Asks for the database settings and creates the SQL configuration.
 */
function config_db()
{
    global $conf, $cli;

    $sql_config = $GLOBALS['config']->_configSQL('');
    question($GLOBALS['sql'], 'phptype', $sql_config['switch']['custom']['fields']['phptype']);
    $conf['sql'] = $GLOBALS['sql'];
    if ($GLOBALS['bundle'] == 'webmail') {
        $conf['auth']['driver'] = 'application';
        $conf['auth']['params']['app'] = 'imp';
    } else {
        $conf['auth']['driver'] = 'sql';
    }
    $conf['prefs']['driver'] = $conf['datatree']['driver'] =
        $conf['token']['driver'] = $conf['vfs']['type'] =
        $conf['alarms']['driver'] = $conf['lock']['driver'] =
        $conf['perms']['driver'] = $conf['group']['driver'] =
        $conf['share']['driver'] = 'sql';

    write_config();

    $cli->writeln($cli->bold('Done configuring database settings.'));
    $cli->writeln();
}

/**
 * Creates the database and/or tables.
 *
 * @param boolean $tables_only  Whether to create the tables only.
 * @param boolean $update       Whether to update an existing database.
 */
function create_db($tables_only = false, $update = false)
{
    global $conf, $cli, $registry;

    if (empty($conf['sql']['phptype'])) {
        config_db();
    }

    $mdb_supported = array('fbsql', 'ibase', 'mssql', 'mysql', 'mysqli', 'oci8', 'pgsql', 'querysim', 'sqlite');
    if (!in_array($conf['sql']['phptype'], $mdb_supported)) {
        $cli->message('Your database type is not supported for creating databases and tables automatically. Please see the manual at docs/INSTALL for how to setup the database manually.', 'cli.warning');
        return false;
    }

    if ($tables_only) {
        $create_db = false;
    } else {
        $create_db = $cli->prompt("Should we create the database for you? If yes, you need to provide a database\nuser that has permissions to create new databases on your system. If no, we\nwill only create the database tables for you.", array('y' => 'Yes', 'n' => 'No'), 'y');
        $create_db = $create_db == 'y';
    }

    list($db_user, $db_pass) = get_db_user($update, $create_db);
    $sql = array_merge($conf['sql'],
                       array('username' => $db_user, 'password' => $db_pass));

    // Don't set the database when listing databases.
    $db_name = $sql['database'];
    unset($sql['charset'], $sql['database']);

    // Create database from schemas.
    $cli->writeln('Loading database module...');
    require_once 'MDB2.php';
    $mdb2 = &MDB2::factory($sql);
    $mdb2->setOption('seqcol_name', 'id');
    if (is_a($mdb2, 'PEAR_Error')) {
        $cli->message('Loading the module for database type ' . $sql['phptype'] . ' failed. Please see the manual at docs/INSTALL for how to setup the database manually. Error messages:', 'cli.error');
        $cli->writeln($mdb2->getMessage());
        $cli->writeln($mdb2->getUserInfo());
        return false;
    }
    $manager = &$mdb2->loadModule('Manager');
    if ($create_db) {
        $databases = $manager->listDatabases();
        if (is_a($databases, 'PEAR_Error') &&
            $databases->getCode() != MDB2_ERROR_UNSUPPORTED) {
            $cli->message('Listing the current databases failed. Please see the manual at docs/INSTALL for how to setup the database manually. Error messages:', 'cli.error');
            $cli->writeln($databases->getMessage());
            $cli->writeln($databases->getUserInfo());
            return false;
        }
        if (is_array($databases) && in_array($db_name, $databases)) {
            $cli->message('Database ' . $db_name . ' already exists, skipping.', 'cli.warning');
            $create_db = false;
        }
    }

    $sql['database'] = $db_name;
    $cli->writeln($update ? 'Updating database...' : 'Creating database...');
    require_once 'MDB2/Schema.php';
    $schema = &MDB2_Schema::factory($sql, array('seqcol_name' => 'id',
                                                'portability' => MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE,
                                                'force_defaults' => false));
    if (is_a($schema, 'PEAR_Error')) {
        $cli->message(
            'Reading the existing table structure failed. Error messages:',
            'cli.error');
        $cli->writeln($schema->getMessage());
        $cli->writeln($schema->getUserInfo());
        return false;
    }
    if ($update) {
        $previous = $schema->getDefinitionFromDatabase();
        if (is_a($previous, 'PEAR_Error')) {
            $cli->message(
                'Reading the existing table structure failed. Error messages:',
                'cli.error');
            $cli->writeln($previous->getMessage());
            $cli->writeln($previous->getUserInfo());
            return false;
        }
    } else {
        $previous = false;
    }
    $success = $schema->updateDatabase(HORDE_BASE . '/scripts/sql/create.xml',
                                       $previous,
                                       array('create' => $create_db,
                                             'name' => $db_name));
    if (is_a($success, 'PEAR_Error')) {
        $cli->message(($update ? 'Updating' : 'Creating') . ' the '
                      . ($create_db ? 'database' : 'tables')
                      . ' failed. Please see the manual at docs/INSTALL for how to setup the database manually. Error messages:',
                      'cli.error');
        $cli->writeln($success->getMessage());
        $cli->writeln($success->getUserInfo());
        return false;
    }

    $cli->message('Successfully ' . ($update ? 'updated' : 'created')
                  . ' the ' . ($create_db ? 'database.' : 'global tables.'),
                  'cli.success');

    // Create application tables.
    foreach ($registry->listApps() as $application) {
        $schema_file = $registry->get('fileroot', $application) .
            '/scripts/sql/' . $application . '.xml';
        if (file_exists($schema_file)) {
            $success = $schema->updateDatabase($schema_file,
                                               $previous,
                                               array('name' => $db_name));
            if (is_a($success, 'PEAR_Error')) {
                $cli->message(
                    sprintf('%s the tables for %s (%s) failed. Error messages:',
                            $update ? 'Updating' : 'Creating',
                            $registry->get('name', $application), $application),
                    'cli.error');
                $cli->writeln($success->getMessage());
                $cli->writeln($success->getUserInfo());
                return false;
            } else {
                $cli->message(
                    sprintf('Successfully %s the tables for %s (%s).',
                            $update ? 'updated' : 'created',
                            $registry->get('name', $application), $application),
                    'cli.success');
            }
        }
    }

    if (!$update) {
        $cli->writeln();
        $metar = $cli->prompt('Should we build the database with METAR weather stations now? This is necessary if you want to display METAR weather information. Building the database requires a network connection.', array('y' => 'Yes', 'n' => 'No'), 'y');
        if ($metar == 'y') {
            $php_cli = get_php_cli();
            $cli->writeln('Creating METAR database...');
            $cmd = $php_cli . ' ' . HORDE_BASE
                . '/pear/data/Services_Weather/buildMetarDB.php'
                . ' -t ' . $sql['phptype'] . ' -r ' . $sql['protocol']
                . ' -d ' . $sql['database'] . ' -u ' . $sql['username']
                . ' -p ' . $sql['password'];
            if (isset($sql['hostspec'])) {
                $cmd .= ' -h ' . $sql['hostspec'];
            }
            system($cmd . ' -l');
            system($cmd . ' -a');
        }
    }

    $cli->writeln($cli->bold('Done ' . ($update ? 'updating' : 'creating')
                             . ($create_db ? ' database.' : ' tables.')));
    $cli->writeln();
}

/**
 * Asks for the administrator settings and creates the configuration.
 */
function config_admin()
{
    global $cli;

    if (empty($GLOBALS['conf']['sql']['phptype'])) {
        create_db();
    }

    if ($GLOBALS['bundle'] == 'webmail') {
        $admin_user = $cli->prompt('Specify an ' . $cli->bold('existing') . ' mail user who you want to give administrator permissions (optional):');
    } else {
        $sql = $GLOBALS['conf']['sql'];
        unset($sql['charset']);
        require_once 'MDB2.php';
        $mdb2 = &MDB2::factory($sql);
        $mdb2->setOption('seqcol_name', 'id');
        $manager = &$mdb2->loadModule('Manager');
        $tables = $manager->listTables();
        if (is_a($tables, 'PEAR_Error')) {
            $cli->message('An error occured while trying to find the installed database tables. Error messages:', 'cli.error');
            $cli->writeln($tables->getMessage());
            $cli->writeln($tables->getUserInfo());
            return;
        }
        if (!in_array('horde_users', $tables)) {
            $cli->message('You didn\'t create the necessary database tables.', 'cli.warning');
            if ($cli->prompt('Do you want to create the tables now?', array('y' => 'Yes', 'n' => 'No'), 'y') == 'y') {
                create_db(true);
            } else {
                return;
            }
        }
        while (true) {
            $admin_user = $cli->prompt('Specify a user name for the administrator account:');
            if (empty($admin_user)) {
                $cli->writeln($cli->red('An administration user is required'));
                continue;
            }
            $admin_pass = $cli->prompt('Specify a password for the adminstrator account:');
            if (empty($admin_user)) {
                $cli->writeln($cli->red('An administrator password is required'));
            } else {
                require_once 'Horde/Auth.php';
                $auth = &Auth::singleton($GLOBALS['conf']['auth']['driver']);
                $exists = $auth->exists($admin_user);
                if (is_a($exists, 'PEAR_Error')) {
                    $cli->message('An error occured while trying to list the users. Error messages:', 'cli.error');
                    $cli->writeln($exists->getMessage());
                    $cli->writeln($exists->getUserInfo());
                    return;
                }
                if ($exists) {
                    if ($cli->prompt('This user exists already, do you want to update his password?', array('y' => 'Yes', 'n' => 'No'), 'y') == 'y') {
                        $result = $auth->updateUser($admin_user, $admin_user, array('password' => $admin_pass));
                    } else {
                        break;
                    }
                } else {
                    $result = $auth->addUser($admin_user, array('password' => $admin_pass));
                }
                if (is_a($result, 'PEAR_Error')) {
                    $cli->message('An error occured while adding or updating the adminstrator. Error messages:', 'cli.error');
                    $cli->writeln($result->getMessage());
                    $cli->writeln($result->getUserInfo());
                    return;
                }
                break;
            }
        }

    }

    $GLOBALS['conf']['auth']['admins'] = array($admin_user);
    write_config();

    $cli->writeln($cli->bold('Done configuring administrator settings.'));
    $cli->writeln();
}

/**
 * Updates PEAR repository to the new location.
 */
function relocate_pear()
{
    global $cli;

    $cli->writeln();
    $cli->writeln('Updating PEAR commands...');
    $pear_dir = realpath(HORDE_BASE) . DIRECTORY_SEPARATOR . 'pear';
    foreach (array('pear', 'peardev', 'php/pearcmd.php') as $file) {
        $file = $pear_dir . '/' . $file;
        if (!is_writable($file)) {
            require_once 'Horde/Util.php';
            $cli->message(Util::realPath($file) . ' is not writable.', 'cli.error');
            return;
        }
        $contents = file_get_contents($file);
        $fp = fopen($file, 'w');
        fwrite($fp, str_replace('@pear_dir@', $pear_dir, $contents));
        fclose($fp);
    }

    $cli->writeln('Updating PEAR configuration...');
    if (!is_writable($pear_dir . '/horde.ini')) {
        require_once 'Horde/Util.php';
        $cli->message(Util::realPath($pear_dir . '/horde.ini') . ' is not writable.', 'cli.error');
        return;
    }
    require_once 'PEAR/Config.php';
    $config = new PEAR_Config($pear_dir . '/horde.ini', '#no#system#config#', false, false);
    $old_dir = substr($config->get('php_dir'), 0, -4);
    $config->noRegistry();
    $config->set('php_dir', $pear_dir . DIRECTORY_SEPARATOR . 'php', 'user');
    $config->set('data_dir', $pear_dir . DIRECTORY_SEPARATOR . 'data');
    $config->set('www_dir', $pear_dir . DIRECTORY_SEPARATOR . 'www');
    $config->set('cfg_dir', $pear_dir . DIRECTORY_SEPARATOR . 'cfg');
    $config->set('ext_dir', $pear_dir . DIRECTORY_SEPARATOR . 'ext');
    $config->set('doc_dir', $pear_dir . DIRECTORY_SEPARATOR . 'docs');
    $config->set('test_dir', $pear_dir . DIRECTORY_SEPARATOR . 'tests');
    $config->set('cache_dir', $pear_dir . DIRECTORY_SEPARATOR . 'cache');
    $config->set('download_dir', $pear_dir . DIRECTORY_SEPARATOR . 'download');
    $config->set('temp_dir', $pear_dir . DIRECTORY_SEPARATOR . 'temp');
    $config->set('bin_dir', $pear_dir);
    $config->writeConfigFile();

    $cli->writeln('Updating PEAR packages...');
    require_once 'File/SearchReplace.php';
    $file = new File_SearchReplace($old_dir, $pear_dir, null, $pear_dir . '/php/');
    $file->doSearch();
    $cli->writeln('Updated ' . $file->getNumOccurences() . ' files.');

    $cli->writeln($cli->bold('Done updating PEAR location.'));
    $cli->writeln();
}

/**
 * Updates from older versions.
 */
function update()
{
    global $cli, $bundle;

    if (!$GLOBALS['conf_created']) {
        $cli->message('This installation has already been configured. You can only update installations that haven\'t been set up before.', 'cli.error');
        return;
    }

    $old_dir = $cli->prompt('Specify the directory of the old Horde Groupware Webmail Edition version that you would like to update. You have to provide the full path to the directory:');
    $old_dir = rtrim($old_dir, '/\\');
    if (!is_dir($old_dir)) {
        $cli->message('"' . $old_dir . '" is not a valid directory.', 'cli.error');
        return;
    }
    if (!is_readable($old_dir)) {
        $cli->message('Cannot access directory "' . $old_dir . '".',
                      'cli.error');
        return;
    }
    if (realpath($old_dir) == realpath(HORDE_BASE)) {
        $cli->message('Please specify the directory of the ' . $cli->bold('old') . ' Horde Groupware Webmail Edition version, not the directory of the new version.', 'cli.error');
        return;
    }
    if (!defined('BUNDLE_NAME')) {
        if (file_exists($old_dir . '/lib/bundle.php')) {
            require $old_dir . '/lib/bundle.php';
        } else {
            define('BUNDLE_VERSION', '1.0');
            define('BUNDLE_NAME', $bundle);
        }
    }
    if (BUNDLE_NAME != $bundle) {
        $cli->message('The software in directory ' . $old_dir
                      . ' is not Horde Groupware Webmail Edition',
                      'cli.error');
        return;
    }

    /* Copy all old config files to the new config directories. */
    require_once 'File/Find.php';
    $config_dirs = File_Find::search('/^config$/', $old_dir, 'perl', false,
                                     'directories');
    $cli->writeln();
    $cli->writeln('Copying old configuration files...');
    foreach ($config_dirs as $config_dir) {
        $config_files = File_Find::search('/.php$/', $config_dir, 'perl', false);
        foreach ($config_files as $config_file) {
            $target_file = HORDE_BASE . str_replace($old_dir, '', $config_file);
            $cli->writeln($config_file . ' -> ' . Util::realPath($target_file));
            if (!@copy($config_file, $target_file)) {
                $cli->message('Cannot copy "'. $config_file . '" to "'
                              . $target_file . '".',
                              'cli.error');
                return;
            }
        }
    }

    /* Append all configuration updates to the config files. */
    $update_files = File_Find::search('|/scripts/upgrades/config/.*\.php$|',
                                      HORDE_BASE, 'perl');
    sort($update_files);
    $cli->writeln();
    $cli->writeln('Patching configuration files...');
    foreach ($update_files as $update_file) {
        $target_dir = substr($update_file, 0,
                             strpos($update_file, '/scripts/upgrades/config/'))
            . '/config/';
        if (!preg_match('/(.*)_([\d.]+)_([\d.]+)$/',
                        basename($update_file, '.php'),
                        $matches)) {
            $cli->message('Unknown update file ' . Util::realPath($update_file),
                          'cli.warning');
            continue;
        }
        list(, $target_file, $old_version, $new_version) = $matches;
        if (version_compare($new_version, '1.2.9', '>') ||
            version_compare($old_version, BUNDLE_VERSION, '<')) {
            continue;
        }
        $cli->writeln(str_replace(HORDE_BASE . '/', '', $target_dir . $target_file) . ' to ' . $new_version);
        $fp = @fopen($target_dir . $target_file, 'a');
        if (!$fp) {
            $cli->message('Cannot open "'. $target_dir . $target_file
                          . '" for writing.',
                          'cli.error');
            return;
        }
        fwrite($fp, "\n//\n// CONFIGURATION UPDATES FOR VERSION $new_version:\n//\n\n");
        fwrite($fp, file_get_contents($update_file));
        fclose($fp);
    }
    $cli->writeln($cli->bold('Done creating configuration files.'));

    $php_cli = get_php_cli();
    list($db_user, $db_pass) = get_db_user(true, false);
    putenv('HORDE_DB_USER=' . $db_user);
    putenv('HORDE_DB_PASS=' . $db_pass);

    /* Point of no return. */
    $cli->writeln();
    if ('y' != $cli->prompt(wordwrap('Changing existing data after this point. Did you create backups of your old data, and are you sure that you want to continue?'),
                            array('y' => 'Yes', 'n' => 'No'))) {
        return;
    }

    $cli->writeln();
    $cli->writeln('Running pre-update scripts...');
    foreach ($GLOBALS['update_scripts']['pre'] as $old_version => $scripts) {
        if (version_compare($old_version, BUNDLE_VERSION, '<')) {
            continue;
        }
        foreach ($scripts as $script) {
            $cli->writeln(str_replace(HORDE_BASE . '/', '', $script));
            system($php_cli . ' ' . $script, $return);
            if ($return) {
                return;
            }
        }
    }

    /* Load old Horde configuration. */
    include HORDE_BASE . '/config/conf.php';

    /* Update old configuration with new cookie path and new default
     * settings. */
    $conf['cookie']['path'] = $GLOBALS['webroot'];
    if (!isset($conf['alarms']['driver'])) {
        $conf['alarms']['driver'] = 'sql';
    }
    if (!isset($conf['lock']['driver'])) {
        $conf['lock']['driver'] = 'sql';
    }

    require_once 'Horde/Array.php';
    foreach ($GLOBALS['update_scripts']['conf'] as $old_version => $apps) {
        if (version_compare($old_version, BUNDLE_VERSION, '<')) {
            continue;
        }
        foreach ($apps as $app => $new_conf) {
            if ($app != 'horde') {
                continue;
            }
            $conf = Horde_Array::array_merge_recursive_overwrite($conf, $new_conf);
        }
    }
    $GLOBALS['conf'] = $conf;

    /* Re-create configuration. */
    write_config();

    create_db(true, true);

    $cli->writeln();
    $cli->writeln('Running post-update scripts...');
    foreach ($GLOBALS['update_scripts']['post'] as $old_version => $scripts) {
        if (version_compare($old_version, BUNDLE_VERSION, '<')) {
            continue;
        }
        foreach ($scripts as $script) {
            $cli->writeln(str_replace(HORDE_BASE . '/', '', $script));
            system($php_cli . ' ' . $script);
        }
    }

    $cli->writeln($cli->bold('Done updating Horde Groupware Webmail Edition.'));
    $cli->writeln();
}

/**
 * Writes the current configuration to the conf.php file.
 */
function write_config()
{
    $GLOBALS['cli']->writeln();
    $GLOBALS['cli']->writeln('Writing main configuration file');

    $php_config = $GLOBALS['config']->generatePHPConfig(new Variables(), $GLOBALS['conf']);
    $fp = fopen(HORDE_BASE . '/config/conf.php', 'w');
    if (!$fp) {
        $GLOBALS['cli']->message('Cannot write configuration file '. HORDE_BASE . '/config/conf.php', 'cli.error');
        exit;
    }
    fwrite($fp, $php_config);
    fclose($fp);

    // Reload configuration.
    include HORDE_BASE . '/config/conf.php';
    $GLOBALS['conf'] = $conf;
}

// No auth.
@define('AUTH_HANDLER', true);

// Find the base file path of Horde.
@define('HORDE_BASE', dirname(__FILE__) . '/..');

// Do CLI checks and environment setup first.
require_once HORDE_BASE . '/lib/core.php';
require_once 'Horde/CLI.php';

// Enable error reporting.
$error_level = E_ALL;
if (defined('E_STRICT')) {
    $error_level &= ~E_STRICT;
}
ini_set('error_reporting', $error_level);
ini_set('display_errors', 1);

// Make sure no one runs this from the web.
if (!Horde_CLI::runningFromCLI()) {
    exit("Must be run from the command line\n");
}

// Load the CLI environment - make sure there's no time limit, init some
// variables, etc.
$cli = &Horde_CLI::singleton();
$cli->init();

// Check if conf.php is writeable.
if ((file_exists(HORDE_BASE . '/config/conf.php') &&
     !is_writable(HORDE_BASE . '/config/conf.php')) ||
    !is_writable(HORDE_BASE . '/config')) {
    require_once 'Horde/Util.php';
    $cli->message(Util::realPath(HORDE_BASE . '/config/conf.php') . ' is not writable.', 'cli.error');
}

// We need a valid conf.php to instantiate the registry.
$conf_created = false;
if (!file_exists(HORDE_BASE . '/config/conf.php')) {
    if (!is_writable(HORDE_BASE . '/config')) {
        exit(1);
    }
    copy(HORDE_BASE . '/config/conf.php.dist', HORDE_BASE . '/config/conf.php');
    $conf_created = true;
}

// Load libraries, instanticate objects.
require_once 'Horde/Config.php';
require_once 'Horde/Form.php';
require_once 'Horde/Form/Action.php';
require_once 'Horde/Variables.php';
$registry = &Registry::singleton();
$config = new Horde_Config('horde');
$bundle = 'webmail';
umask(0);
$conf['log']['enabled'] = false;
$cli->clearScreen();
$cleared = true;

// Define upgrade scripts.
$update_scripts = array(
    'pre' => array(
        '1.1' => array(
            HORDE_BASE . '/turba/scripts/upgrades/2.1_to_2.2_sql_schema.php',
            HORDE_BASE . '/turba/scripts/upgrades/2007-06-17_flatten_shares.php',
            HORDE_BASE . '/nag/scripts/upgrades/2006-04-18_add_creator_and_assignee_fields.php',
        ),
        '1.2-RC1' => array(
            HORDE_BASE . '/scripts/upgrades/2008-08-29_fix_mdb2_sequences.php',
        ),
    ),
    'post' => array(
        '1.1' => array(
            HORDE_BASE . '/scripts/upgrades/convert_datatree_groups_to_sql.php',
            HORDE_BASE . '/scripts/upgrades/convert_datatree_perms_to_sql.php',
            HORDE_BASE . '/kronolith/scripts/upgrades/convert_datatree_shares_to_sql.php',
            HORDE_BASE . '/mnemo/scripts/upgrades/convert_datatree_shares_to_sql.php',
            HORDE_BASE . '/nag/scripts/upgrades/convert_datatree_shares_to_sql.php',
            HORDE_BASE . '/turba/scripts/upgrades/convert_datatree_shares_to_sql.php',
        ),
    ),
    'conf' => array(
        '1.1' => array(
            'horde' => array(
                'group' => array('driver' => 'sql', 'driverconfig' => 'horde'),
                'perms' => array('driver' => 'sql', 'driverconfig' => 'horde'),
                'share' => array('driver' => 'sql'),
            ),
        ),
    ),
);

// Is this a first time run?
if ($conf_created) {
    $webroot = $cli->prompt('What is the web root path on your web server for this installation, i.e. the path of the address you use to access Horde Groupware Webmail Edition in your browser?', null, '/' . basename(dirname(dirname(__FILE__))));
    $cli->writeln();
    $conf['cookie']['path'] = $webroot;
    unlink(HORDE_BASE . '/config/conf.php');
}

// Main menu.
while (true) {
    if (!$cleared) {
        $cli->writeln();
    }
    $menu = $cli->prompt('Horde Groupware Webmail Edition Configuration Menu',
                         array('Exit', 'Configure database settings',
                               'Create database or tables',
                               'Configure administrator settings',
                               'Update PEAR for a new or changed location',
                               'Update from an older Horde Groupware Webmail Edition version'));
    switch ($menu) {
    case 0:
        break 2;
    case 1:
        config_db();
        break;
    case 2:
        create_db();
        break;
    case 3:
        config_admin();
        break;
    case 4:
        relocate_pear();
        break;
    case 5:
        update();
    }
    $cleared = false;
}

// Finished.
$cli->writeln();
$cli->writeln($cli->bold('Thank you for using Horde Groupware Webmail Edition!'));
$cli->writeln();
