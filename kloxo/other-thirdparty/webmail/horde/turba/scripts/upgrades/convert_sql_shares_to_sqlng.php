#!/usr/bin/env php
<?php
/**
 * This script migrates Turba's share data from the SQL Horde_Share
 * driver to the next-generation SQL Horde_Share driver.
 *
 * It is supposed to run at any time after migrating Turba to the latest DB
 * schema version. The schema migration already migrates the data once, but
 * this script can be used to migrate the data again, e.g. if starting to use
 * the NG driver at a later time.
 */

function checkError($var)
{
    if (is_a($var, 'PEAR_Error')) {
        die($var->toString());
    }
    return $var;
}

@define('AUTH_HANDLER', true);
@define('HORDE_BASE', dirname(__FILE__) . '/../../..');

/* Set up the CLI environment */
require_once HORDE_BASE . '/lib/core.php';
require_once 'Horde/CLI.php';
if (!Horde_CLI::runningFromCli()) {
    exit("Must be run from the command line\n");
}
$cli = &Horde_CLI::singleton();
$cli->init();

/* Grab what we need to steal the DB config */
require_once HORDE_BASE . '/config/conf.php';
require_once 'MDB2.php';

$config = $GLOBALS['conf']['sql'];
unset($config['charset']);
/* MUST use a reference here. */
$db = &MDB2::factory($config);
$db->setOption('field_case', CASE_LOWER);
$db->setOption('portability', MDB2_PORTABILITY_FIX_CASE | MDB2_PORTABILITY_ERRORS | MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);

$delete = $cli->prompt('Delete existing shares from the NEW backend before migrating the OLD backend? This should be done to avoid duplicate entries or primary key collisions in the storage backend from earlier migrations.', array('y' => 'Yes', 'n' => 'No'), 'n');

if ($delete == 'y' || $delete == 'Y') {
    $db->query('DELETE FROM turba_sharesng');
    $db->query('DELETE FROM turba_sharesng_users');
    $db->query('DELETE FROM turba_sharesng_groups');
}

$whos = array('creator', 'default', 'guest');
$perms = array(PERMS_SHOW,
               PERMS_READ,
               PERMS_EDIT,
               PERMS_DELETE);

$shares = checkError($db->queryAll('SELECT * FROM turba_shares', null, MDB2_FETCHMODE_ASSOC));

$sql = 'INSERT INTO turba_sharesng (share_id, share_name, share_owner, share_flags, attribute_name, attribute_desc, attribute_params';
$count = 0;
foreach ($whos as $who) {
    foreach ($perms as $perm) {
        $sql .= ', perm_' . $who . '_' . $perm;
        $count++;
    }
}
$sql .= ') VALUES (?, ?, ?, ?, ?, ?, ?' . str_repeat(', ?', $count) . ')';
$stm = checkError($db->prepare($sql));

foreach ($shares as $share) {
    $values = array($share['share_id'],
                    $share['share_name'],
                    $share['share_owner'],
                    $share['share_flags'],
                    $share['attribute_name'],
                    $share['attribute_desc'],
                    $share['attribute_params']);
    foreach ($whos as $who) {
        foreach ($perms as $perm) {
            $values[] = (bool)($share['perm_' . $who] & $perm);
        }
    }
    checkError($stm->execute($values));
}

foreach (array('user', 'group') as $what) {
    $sql = 'INSERT INTO turba_sharesng_' . $what . 's (share_id, ' . $what . '_uid';
    $count = 0;
    foreach ($perms as $perm) {
        $sql .= ', perm_' . $perm;
        $count++;
    }
    $sql .= ') VALUES (?, ?' . str_repeat(', ?', $count) . ')';
    $stm = checkError($db->prepare($sql));

    foreach ($db->queryAll('SELECT * FROM turba_shares_' . $what . 's', null, MDB2_FETCHMODE_ASSOC) as $share) {
        $values = array($share['share_id'],
                        $share[$what . '_uid']);
        foreach ($perms as $perm) {
            $values[] = (bool)($share['perm'] & $perm);
        }
        checkError($stm->execute($values));
    }
}
