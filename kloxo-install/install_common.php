<?php
//
//    Kloxo, Hosting Panel
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2010     LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

class remote { }
$downloadserver = "http://download.lxcenter.org/";

function slave_get_db_pass() {
    $file = "/usr/local/lxlabs/kloxo/etc/slavedb/dbadmin";
    if (!file_exists($file)) {
        return null;
    }
    $var = file_get_contents($file);
    $rmt = unserialize($var);
    return $rmt->data['mysql']['dbpassword'];
}

function addLineIfNotExistTemp($filename, $pattern, $comment) {
    $cont = our_file_get_contents($filename);

    if (!preg_match("+$pattern+i", $cont)) {
        our_file_put_contents($filename, "\n$comment \n\n", true);
        our_file_put_contents($filename, $pattern, true);
        our_file_put_contents($filename, "\n\n\n", true);
    } else {
        print("Pattern '$pattern' Already present in $filename\n");
    }
}

function check_default_mysql($dbroot, $dbpass) {
    system("service mysqld restart");

    if ($dbpass) {
        exec("echo \"show tables\" | mysql -u $dbroot -p\"$dbpass\" mysql", $out, $return);
    } else {
        exec("echo \"show tables\" | mysql -u $dbroot mysql", $out, $return);
    }

    if ($return) {
        print("Fatal Error: Could not connect to Mysql Localhost using user $dbroot and password \"$dbpass\"\n");
        print("If this is a brand new install, you can completely remove mysql by running the commands below\n");
        print("            rm -rf /var/lib/mysql\n");
        print("            rpm -e mysql-server\n\n");
        print("And then run the installer again\n");
        exit;
    }

}


function parse_opt($argv) {
    unset($argv[0]);
    if (!$argv) {
        return null;
    }
    foreach ($argv as $v) {
        if (strstr($v, "=") === false || strstr($v, "--") === false) {
            continue;
        }
        $opt = explode("=", $v);
        $opt[0] = substr($opt[0], 2);
        $ret[$opt[0]] = $opt[1];
    }
    return $ret;
}

function our_file_get_contents($file) {
    $string = null;

    $fp = fopen($file, "r");

    if (!$fp) {
        return null;
    }


    while (!feof($fp)) {
        $string .= fread($fp, 8192);
    }
    fclose($fp);
    return $string;

}

function our_file_put_contents($file, $contents, $appendflag = false) {

    if ($appendflag) {
        $flag = "a";
    } else {
        $flag = "w";
    }

    $fp = fopen($file, $flag);

    if (!$fp) {
        return null;
    }

    fwrite($fp, $contents);

    fclose($fp);
}

function password_gen() {
    $data = mt_rand(2, 30);
    $pass = "lx" . $data; // lx is a indentifier
    return $pass;
}


function strtil($string, $needle) {
    if (strrpos($string, $needle)) {
        return substr($string, 0, strrpos($string, $needle));
    } else {
        return $string;
    }
}

function strtilfirst($string, $needle) {
    if (strpos($string, $needle)) {
        return substr($string, 0, strpos($string, $needle));
    } else {
        return $string;
    }
}


function strfrom($string, $needle) {
    return substr($string, strpos($string, $needle) + strlen($needle));
}

function char_search_beg($haystack, $needle) {
    if (strpos($haystack, $needle) === 0) {
        return true;
    }
    return false;
}


function install_rhn_sources($osversion) {
    global $downloadserver;
    if (!file_exists("/etc/sysconfig/rhn/sources")) {
        return;
    }

    $data = our_file_get_contents("/etc/sysconfig/rhn/sources");
    if (!preg_match('/lxcenter/i', $data)) {
        $ndata = "yum lxcenter-updates " . $downloadserver . "download/update/$osversion/\$ARCH/\nyum lxcenter-lxupdates http://download.lxcenter.org/download/update/lxgeneral";
        //append it to the file...
        our_file_put_contents("/etc/sysconfig/rhn/sources", "\n\n", true);
        our_file_put_contents("/etc/sysconfig/rhn/sources", $ndata, true);
        our_file_put_contents("/etc/sysconfig/rhn/sources", "\n\n", true);
    }
}

function install_yum_repo($osversion) {
    if (!file_exists("/etc/yum.repos.d")) {
        print("No yum.repos.d dir detected!\n");
        return;
    }
    if (file_exists("/etc/yum.repos.d/lxcenter.repo")) {
        print("LxCenter yum repository file already present.\n");
        return;
    }

    $cont = our_file_get_contents("../lxcenter.repo.template");
    $cont = str_replace("%distro%", $osversion, $cont);
    our_file_put_contents("/etc/yum.repos.d/lxcenter.repo", $cont);
}


function find_os_version() {
    if (file_exists("/etc/fedora-release")) {
        $release = trim(file_get_contents("/etc/fedora-release"));
        $osv = explode(" ", $release);
        if (strtolower($osv[1]) === 'core') {
            $osversion = "fedora-" . $osv[3];
        } else {
            $osversion = "fedora-" . $osv[2];
        }

        return $osversion;
    }

    if (file_exists("/etc/redhat-release")) {
        $release = trim(file_get_contents("/etc/redhat-release"));
        $osv = explode(" ", $release);
        if (isset($osv[6])) {
            $osversion = "rhel-" . $osv[6];
        } else {
            $oss = explode(".", $osv[2]);
            $osversion = "centos-" . $oss[0];
        }
        return $osversion;
    }


    print("This Operating System is currently *NOT* supported.\n");
    exit;

}

