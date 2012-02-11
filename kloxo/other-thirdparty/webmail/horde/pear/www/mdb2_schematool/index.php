<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2008 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Igor Feghali                           |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2_Schema enables users to maintain RDBMS independant schema files |
// | in XML that can be used to manipulate both data and database schemas |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith, Igor Feghali nor the names of his contributors may be   |
// | used to endorse or promote products derived from this software       |
// | without specific prior written permission.                           |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// | Author: Igor Feghali <ifeghali@php.net>                              |
// +----------------------------------------------------------------------+
//
// $Id: index.php,v 1.3 2009/02/22 21:43:22 ifeghali Exp $
//

/**
 * This is all rather ugly code, thats probably very much XSS exploitable etc.
 * However the idea was to keep the magic and dependencies low, to just
 * illustrate the MDB2_Schema API a bit.
 */

$error = '';
if (isset($_COOKIE['error']) && $_COOKIE['error']) {
    $error = $_COOKIE['error'];
    setcookie('error','');
}

if (!isset($_REQUEST['loaded'])) {
    require_once 'class.inc.php';
    $defaults = new MDB2_Schema_Example();
    $defaults->saveCookies();
    header('location: index.php?loaded=1');
    exit;
}

$databases = array(
    'mysql'  => 'MySQL',
    'mysqli' => 'MySQLi',
    'pgsql'  => 'PostGreSQL',
    'sqlite' => 'SQLite'
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
      <head><title>MDB2_Schema Web Frontend</title></head>
<body>
<?php
if (strlen($error)) {
    echo '<h1>Error</h1>';
    echo '<div id="errors"><ul>';
    echo "<li>$error</li>";
    echo '</ul></div>';
}
?>
    <form method="get" action="action.php">
    <fieldset>
    <legend>Database information</legend>

    <table>
    <tr>
    <td><label for="type">Database Type:</label></td>
        <td>
        <select name="type" id="type">
<?php
    foreach ($databases as $key => $name) {
        echo str_repeat(' ', 8).'<option value="' . $key . '"';
        if (isset($_REQUEST['type']) && $_REQUEST['type'] == $key) {
            echo ' selected="selected"';
        }
        echo ">$name</option>\n";
    }
?>
        </select>
        </td>
    </tr>
    <tr>
        <td><label for="user">Username:</label></td>
        <td><input type="text" name="user" id="user" value="<?php @print $_REQUEST['username']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="pass">Password:</label></td>
        <td><input type="text" name="pass" id="pass" value="<?php @print $_REQUEST['password']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="host">Host:</label></td>
        <td><input type="text" name="host" id="host" value="<?php @print $_REQUEST['hostspec']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="name">Databasename:</label></td>
        <td><input type="text" name="name" id="name" value="<?php @print $_REQUEST['database']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="char">Table Charset:</label></td>
        <td><input type="text" name="char" id="char" value="<?php @print $_REQUEST['charset']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="file">Filename:</label></td>
        <td><input type="text" name="file" id="file" value="<?php @print $_REQUEST['file']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="dump">Dump:</label></td>
        <td><input type="radio" name="action" id="dump" value="dump" <?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dump') {echo (' checked="checked"');} ?> />
        <select id="dumptype" name="dumptype">
            <option value="all"<?php if (isset($_REQUEST['dumptype']) && $_REQUEST['dumptype'] == 'all') {echo (' selected="selected"');} ?>>All</option>
            <option value="structure"<?php if (isset($_REQUEST['dumptype']) && $_REQUEST['dumptype'] == 'structure') {echo (' selected="selected"');} ?>>Structure</option>
            <option value="content"<?php if (isset($_REQUEST['dumptype']) && $_REQUEST['dumptype'] == 'content') {echo (' selected="selected"');} ?>>Content</option>
        </select>
        </td>
    </tr>
    <tr>
        <td><label for="create">Create:</label></td>
        <td><input type="radio" name="action" id="create" value="create" <?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'create') { echo 'checked="checked"';} ?> /></td>
    </tr>
    <tr>
        <td><label for="update">Update:</label></td>
        <td><input type="radio" name="action" id="update" value="update" <?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update') { echo 'checked="checked"';} ?> /></td>
    </tr>
    <tr>
        <td><label for="update">Initialize:</label></td>
        <td><input type="radio" name="action" id="initialize" value="initialize" <?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'initialize') { echo 'checked="checked"';} ?> /></td>
    </tr>
    </table>
    </fieldset>

    <fieldset>
    <legend>Options</legend>
    <table>
    <tr>
        <td><label for="log_line_break">Log line break:</label></td>
        <td><input type="text" name="log_line_break" id="log_line_break" value="<?php @print $_REQUEST['log_line_break']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="idxname_format">Index Name Format:</label></td>
        <td><input type="text" name="idxname_format" id="idxname_format" value="<?php @print $_REQUEST['idxname_format']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="DBA_username">DBA_username:</label></td>
        <td><input type="text" name="DBA_username" id="DBA_username" value="<?php @print $_REQUEST['DBA_username']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="DBA_password">DBA_password:</label></td>
        <td><input type="text" name="DBA_password" id="DBA_password" value="<?php @print $_REQUEST['DBA_password']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="default_table_type">Default Table Type:</label></td>
        <td><input type="text" name="default_table_type" id="default_table_type" value="<?php @print $_REQUEST['default_table_type']; ?>" /></td>
    </tr>
    <tr>
        <td><label for="debug">Debug:</label></td>
        <td><input type="checkbox" name="debug" id="debug" value="1" <?php if (isset($_REQUEST['debug']) && $_REQUEST['debug']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="use_transactions">Use Transactions:</label></td>
        <td><input type="checkbox" name="use_transactions" id="use_transactions" value="1" <?php if (isset($_REQUEST['use_transactions']) && $_REQUEST['use_transactions']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="quote_identifier">Quote Identifier:</label></td>
        <td><input type="checkbox" name="quote_identifier" id="quote_identifier" value="1" <?php if (isset($_REQUEST['quote_identifier']) && $_REQUEST['quote_identifier']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="force_defaults">Force Defaults:</label></td>
        <td><input type="checkbox" name="force_defaults" id="force_defaults" value="1" <?php if (isset($_REQUEST['force_defaults']) && $_REQUEST['force_defaults']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="portability">Portability:</label></td>
        <td><input type="checkbox" name="portability" id="portability" value="1" <?php if (isset($_REQUEST['portability']) && $_REQUEST['portability']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="show_structure">Show database structure:</label></td>
        <td><input type="checkbox" name="show_structure" id="show_structure" value="1" <?php if (isset($_REQUEST['show_structure']) && $_REQUEST['show_structure']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="disable_query">Do not modify database:</label></td>
        <td><input type="checkbox" name="disable_query" id="disable_query" value="1" <?php if (isset($_REQUEST['disable_query']) && $_REQUEST['disable_query']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    <tr>
        <td><label for="drop_missing_tables">Drop obsolete tables:</label></td>
        <td><input type="checkbox" name="drop_missing_tables" id="drop_missing_tables" value="1" <?php if (isset($_REQUEST['drop_missing_tables']) && $_REQUEST['drop_missing_tables']) {echo (' checked="checked"');} ?>/></td>
    </tr>
    </table>
    </fieldset>

    <p><input type="submit" name="submit" value="ok" /><input type="button" value="reset" /></p>
    </form>
</body>
</html>
