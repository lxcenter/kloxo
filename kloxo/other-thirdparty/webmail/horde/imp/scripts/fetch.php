#!/usr/bin/env php
<?php
/**
 * Command-line fetchmail script.
 *
 * Sample crontab job for fetches performed every 15 minutes:
 *     0,15,30,45 * * * * php /path/to/horde/scripts/fetch.php <username> <password> > /dev/null 2>&1 &
 *
 * $Horde: imp/scripts/fetch.php,v 1.1.2.1 2010/02/04 18:28:56 slusarz Exp $
 *
 * Copyright 2010 Terence Jacyno <tjacyno@galasoft.net>
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Terence Jacyno <tjacyno@galasoft.net>
 */

// Get the username and password from the command line arguments.
if ($argc != 3) {
    exit("Usage: $argv[0] <username> <password>\n");
}
$username = $argv[1];
$password = $argv[2];

@define('AUTH_HANDLER', true);
@define('IMP_BASE', dirname(__FILE__) . '/..');
@define('HORDE_BASE', IMP_BASE . '/..');

// Do CLI checks and environment setup first.
require_once HORDE_BASE . '/lib/core.php';
require_once HORDE_BASE . '/lib/Horde/CLI.php';

// Make sure no one runs this from the web.
if (!Horde_CLI::runningFromCLI()) {
    exit("Must be run from the command line\n");
}

// Load the CLI environment - make sure there's no time limit, init some
// variables, etc.
Horde_CLI::init();

// Include needed libraries.
require_once HORDE_BASE . '/lib/base.php';

// Authenticate to Horde.
$auth = Auth::singleton($conf['auth']['driver']);
$auth->setAuth($username, array());

// Authenticate to IMP.
if (!$registry->callByPackage('imp', 'authenticate', array($username, array('password' => $password)))) {
    exit("IMP authentication failed.");
}

// Attach a listener of the status messages associated to the fetch operations.
require_once HORDE_BASE . '/lib/Horde/Notification/Listener.php';

class Notification_Listener_fetchstatus extends Notification_Listener
{
    function Notification_Listener_fetchstatus()
    {
        $this->_handles = array(
            'horde.error'   => array(),
            'horde.success' => array(),
            'horde.warning' => array(),
            'horde.message' => array(),
            'horde.alarm'   => array()
        );
    }

    function getName()
    {
        return 'fetchstatus';
    }

    function notify(&$messageStack, $options = array())
    {
        if (count($messageStack)) {
            while ($message = array_shift($messageStack)) {
                echo $this->getMessage($message);
            }
        }
    }

    function getMessage($message)
    {
        $event = $this->getEvent($message);
        return $event->getMessage();
    }

}

$listener = $GLOBALS['notification']->attach('fetchstatus', null, 'Notification_Listener_fetchstatus');

// Make sure we are in IMP scope
$registry->pushApp('imp');

// Fetch mail.
require_once IMP_BASE . '/lib/Fetchmail.php';
$fetchmailAccounts = new IMP_Fetchmail_Account();
$accountIndexes = array();
for ($i = 0, $count = $fetchmailAccounts->count(); $i < $count; ++$i) {
    $accountIndexes[$i] = $i;
}
@IMP_Fetchmail::fetchMail($accountIndexes);

// Filter messages.
if ($prefs->getValue('filter_on_display')) {
    require_once IMP_BASE . '/lib/Filter.php';
    $imp_filter = new IMP_Filter();
    $imp_filter->filter($imp_mbox['mailbox']); // $imp_mbox['mailbox']=='INBOX'
}

// Display the status messages associated to the fetch and filter operations.
$notification->notify(array('listeners' => array('fetchstatus')));
