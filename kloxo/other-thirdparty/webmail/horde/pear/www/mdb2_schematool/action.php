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
// $Id: action.php,v 1.3 2009/02/22 13:30:40 ifeghali Exp $
//

/**
 * This is all rather ugly code, thats probably very much XSS exploitable etc.
 * However the idea was to keep the magic and dependencies low, to just
 * illustrate the MDB2_Schema API a bit.
 */
setcookie('error','');

require_once 'MDB2/Schema.php';
require_once 'class.inc.php';

$data =& MDB2_Schema_Example::factory($_GET);
if (PEAR::isError($data)) {
    setcookie('error', $data->getMessage());
    header('location: index.php');
    exit;
}

$schema =& MDB2_Schema::factory($data->dsn, $data->options);
if (PEAR::isError($schema)) {
    $error = $schema->getMessage() . ' ' . $schema->getUserInfo();
    setcookie('error', $error);
    header('location: index.php');
    exit;
}

switch ($data->action) { 
/* DUMP DATABASE */
case 'dump':
    switch ($data->dumptype) {
    case 'structure':
        $dump_what = MDB2_SCHEMA_DUMP_STRUCTURE;
        break;
    case 'content':
        $dump_what = MDB2_SCHEMA_DUMP_CONTENT;
        break;
    default:
        $dump_what = MDB2_SCHEMA_DUMP_ALL;
        break;
    }
    $dump_config = array(
        'output_mode' => 'file',
        'output' => $data->file
    );

    $definition = $schema->getDefinitionFromDatabase();
    if (PEAR::isError($definition)) {
        $error = $definition->getMessage() . ' ' . $definition->getUserInfo();
    } else {
        $operation = $schema->dumpDatabase($definition, $dump_config, $dump_what);
        if (PEAR::isError($operation)) {
            $error = $operation->getMessage() . ' ' . $operation->getUserInfo();
        }
    }
    break;

/* UPDATE DATABASE */
case 'update':
    if ($data->disable_query) {
        $debug_tmp = $schema->db->getOption('debug');
        $schema->db->setOption('debug', true);
        $debug_handler_tmp = $schema->db->getOption('debug_handler');
        $schema->db->setOption('debug_handler', 'printQueries');
    }

    $dump_config = array(
        'output_mode' => 'file',
        'output' => $data->file.'.old'
    );
    $definition = $schema->getDefinitionFromDatabase();
    if (PEAR::isError($definition)) {
        $error = $definition->getMessage() . ' ' . $definition->getUserInfo();
    } else {
        $operation = $schema->dumpDatabase($definition, $dump_config, MDB2_SCHEMA_DUMP_ALL);
        if (PEAR::isError($operation)) {
            $error = $operation->getMessage() . ' ' . $operation->getUserInfo();
        } else {
            $operation = $schema->updateDatabase($data->file
                , $data->file.'.old', array(), $data->disable_query
            );
            if (PEAR::isError($operation)) {
                $error = $operation->getMessage() . ' ' . $operation->getUserInfo();
            }
        }
    }

    if ($data->disable_query) {
        $schema->db->setOption('debug', $debug_tmp);
        $schema->db->setOption('debug_handler', $debug_handler_tmp);
    }
    break;

/* CREATE DATABASE */
case 'create':
    if ($data->disable_query) {
        $debug_tmp = $schema->db->getOption('debug');
        $schema->db->setOption('debug', true);
        $debug_handler_tmp = $schema->db->getOption('debug_handler');
        $schema->db->setOption('debug_handler', 'printQueries');
    }

    $definition = $schema->parseDatabaseDefinition(
        $data->file, false, array(), $schema->options['fail_on_invalid_names']
    );
    if (PEAR::isError($definition)) {
        $error = $definition->getMessage() . ' ' . $definition->getUserInfo();
    } else {
        $schema->db->setOption('disable_query', $data->disable_query);
        $operation = $schema->createDatabase($definition);
        $schema->db->setOption('disable_query', false);

        if (PEAR::isError($operation)) {
            $error = $operation->getMessage() . ' ' . $operation->getUserInfo();
        }
    }

    if ($data->disable_query) {
        $schema->db->setOption('debug', $debug_tmp);
        $schema->db->setOption('debug_handler', $debug_handler_tmp);
    }
    break;

/* INITIALIZE DATABASE */
case 'initialize':
    if ($data->disable_query) {
        $debug_tmp = $schema->db->getOption('debug');
        $schema->db->setOption('debug', true);
        $debug_handler_tmp = $schema->db->getOption('debug_handler');
        $schema->db->setOption('debug_handler', 'printQueries');
    }

    $definition = $schema->getDefinitionFromDatabase();
    if (PEAR::isError($definition)) {
        $error = $definition->getMessage() . ' ' . $definition->getUserInfo();
    } else {
        $schema->db->setOption('disable_query', $data->disable_query);
        $operation = $schema->writeInitialization($data->file, $definition);
        if (PEAR::isError($operation)) {
            $error = $operation->getMessage() . ' ' . $operation->getUserInfo();
        }
        $schema->db->setOption('disable_query', false);
    }

    if ($data->disable_query) {
        $schema->db->setOption('debug', $debug_tmp);
        $schema->db->setOption('debug_handler', $debug_handler_tmp);
    }
    break;
}

include 'result.php';
$schema->disconnect();

function printQueries(&$db, $scope, $message)
{
    if ($scope == 'query') {
        echo $message.$db->getOption('log_line_break');
    }
    MDB2_defaultDebugOutput($db, $scope, $message);
}

?>
