#!/usr/bin/env php
<?php

@define('AUTH_HANDLER', true);
@define('HORDE_BASE', dirname(__FILE__) . '/..');

// Do CLI checks and environment setup first.
require_once HORDE_BASE . '/lib/core.php';
require_once 'Horde/CLI.php';

// Make sure no one runs this from the web.
if (!Horde_CLI::runningFromCLI()) {
    exit("Must be run from the command line\n");
}

// Load the CLI environment - make sure there's no time limit, init some
// variables, etc.
Horde_CLI::init();

// Include needed libraries.
require_once HORDE_BASE . '/lib/base.php';
require_once 'Horde/Perms.php';
require_once 'Horde/Share.php';
require_once 'Horde/String.php';
require_once 'Benchmark/Timer.php';
require_once 'Console/Getopt.php';
$conf['log']['name'] = '/dev/null';

/* Setting up command line. */
$getopt = new Console_Getopt();
$argv = $getopt->readPHPArgv();
if (is_a($argv, 'PEAR_Error')) {
    echo $argv->getMessage() . "\n";
    exit(2);
}
$cmd = basename(array_shift($argv));
$description = <<<EOD
This script creates a large numbers of shares with varying permission in the specified share backend and then runs some benchmarks on common share actions.

Example: $cmd -b sqlng -c pgsql

-b, --backend        The backend to test. Must be configured in the conf.php
                     configuration file.
-c, --configuration  The configuration to use for the specified backend.
--users              Number of users to create (default: 10000).
--groups             Number of groups to assume (default: 10).
--sharing-users      Percentage of users actually sharing with others
                     (default: 5).
--max-users          Maximum number of users to share with (default: 4).
--max-groups         Maximum number of groups to share with (default: 1).
--runs               Number of executed test calls (default: 500).

EOD;

$result = $getopt->getopt2(
    $argv, 'b:c:',
    array('backend=', 'configuration=', 'users=', 'groups=', 'sharing-users=',
          'max-users=', 'max-groups=', 'runs='));
if (is_a($result, 'PEAR_Error')) {
    echo $description . "\n" . $result->getMessage() . "\n";
    exit(2);
}

$options = array();
foreach ($result[0] as $option) {
    $name = str_replace('--', '', $option[0]);
    if ($name == 'b') $name = 'backend';
    if ($name == 'c') $name = 'configuration';
    $options[$name] = $option[1];
}
if (!isset($options['users'])) $options['users'] = 10000;
if (!isset($options['groups'])) $options['groups'] = 10;
if (!isset($options['sharing-users'])) $options['sharing-users'] = 5;
if (!isset($options['max-users'])) $options['max-users'] = 4;
if (!isset($options['max-groups'])) $options['max-groups'] = 1;
if (!isset($options['runs'])) $options['runs'] = 500;
$choices = array('sql', 'sqlng', 'kolab', 'datatree');
if (!isset($options['backend']) || !in_array($options['backend'], $choices)) {
    echo $description . "\n";
    echo '--backend must be one of ' . implode(', ', $choices) . "\n";
    exit(2);
}
if (!isset($options['configuration'])) {
    echo $description . "\n";
    echo 'Missing --configuration argument.' . "\n";
    exit(2);
}

/* Load configuration. */
if (!file_exists(dirname(__FILE__) . '/share-stress-test-conf.php')) {
    echo "Configuration file share-stress-test-conf.php missing.\n";
    exit(1);
}

require dirname(__FILE__) . '/share-stress-test-conf.php';

/* Create storage backend. */
switch ($options['backend']) {
case 'sql':
case 'sqlng':
    $conf['sql'] = isset($conf['share']['sql'][$options['configuration']])
        ? $conf['share']['sql'][$options['configuration']]
        : array();
    echo "Creating tables...";
    require_once 'Horde/SQL/Manager.php';
    $manager = Horde_SQL_Manager::getInstance($conf['sql']);
    $manager->_writer->db->query('DROP TABLE IF EXISTS test_shares');
    $manager->_writer->db->query('DROP TABLE IF EXISTS test_shares_groups');
    $manager->_writer->db->query('DROP TABLE IF EXISTS test_shares_users');
    $result = $manager->updateSchema(dirname(__FILE__) . '/share-stress-test-' . $options['backend'] . '.xml');
    if (is_a($result, 'PEAR_Error')) {
        echo "\n" . $result->toString();
        exit(1);
    }
    echo "done\n";
    break;
default:
    echo "Storage configuration for ${options['backend']} not implemented yet.\n";
    exit(1);
}

/* Create share backend. */
$class = 'Horde_Share_' . implode('_', array_map(array('String', 'ucfirst'), explode('_', $options['backend'])));
$shares = Horde_Share::singleton('test', $options['backend']);
if ($options['backend'] == 'sqlng') {
    $shares->_table = 'test_shares';
}

/* Start timer. */
$timer = new Benchmark_Timer();

/* Create test shares. */
echo "Creating ${options['users']} users.\n";
$timer->start();
$users = $groups = 0;
for ($i = 0; $i < $options['users']; $i++) {
    /* Create share. */
    $share = $shares->newShare(sprintf('user%0' . strlen($options['users']) . 'd', $i),
                               md5(uniqid(mt_rand(), true)));
    $shares->addShare($share);

    /* Add permissions with some probability. */
    if (rand(0, 100) <= $options['sharing-users']) {
        for ($j = 0, $r = rand(0, $options['max-users']); $j < $r; $j++) {
            $share->addUserPermission(sprintf('user%0' . strlen($options['users']) . 'd', rand(0, $options['users'] - 1)), PERMS_SHOW | PERMS_READ);
            $users++;
        }
        for ($j = 0, $r = rand(0, $options['max-groups']); $j < $r; $j++) {
            $share->addGroupPermission(sprintf('group%0' . strlen($options['groups']) . 'd', rand(0, $options['groups'] - 1)), PERMS_SHOW | PERMS_READ);
            $groups++;
        }
    }

    /* Progress marker. */
    if (!($i % ($options['users'] / 10))) {
        echo '.';
    }
}
$timer->stop();
echo "\nTime spent: " . $timer->timeElapsed() . " seconds\n";
echo "Sharing with $users users and $groups groups.\n";

echo "\nExecuting ${options['runs']} listShares() calls.\n";
$timer->start();
for ($i = 0; $i < $options['runs']; $i++) {
    $shares->listShares(sprintf('user%0' . strlen($options['users']) . 'd', rand(0, $options['users'] - 1)));

    /* Progress marker. */
    if (!($i % ($options['runs'] / 10))) {
        echo '.';
    }
}
$timer->stop();
echo "\nTime spent: " . $timer->timeElapsed() . " seconds\n";
