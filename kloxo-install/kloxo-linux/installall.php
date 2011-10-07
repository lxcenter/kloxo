<?php 
//
//    Kloxo, Hosting Panel
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009-2011     LxCenter
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

include_once "htmllib/lib/include.php";

$installcomp['mail'] = array("vpopmail", "courier-imap-toaster", "courier-authlib-toaster", "qmail", "safecat", "httpd", "spamassassin", "ezmlm-toaster", "autorespond-toaster");
$installcomp['web'] = array("httpd", "pure-ftpd");
$installcomp['dns'] = array("bind", "bind-chroot");
$installcomp['database'] = array("mysql");


function install_general_mine($value) {
    $value = implode(" ", $value);
    print("Installing $value ....\n");
    system("PATH=\$PATH:/usr/sbin yum -y install $value");
}


function installcomp_mail() {
    system('pear channel-update "pear.php.net"'); // to remove old channel warning
    system("pear upgrade --force pear"); // force is needed
    system("pear upgrade --force Archive_Tar"); // force is needed
    system("pear upgrade --force structures_graph"); // force is needed
    system("pear install log");
}


function install_main() {

    global $installcomp;
    global $argv;
    $comp = array("web", "mail", "dns", "database");

    $list = parse_opt($argv);

    if ($list['server-list']) {
        $serverlist = implode(",", $list['server-list']);
    } else {
        $serverlist = $comp;
    }

    foreach ($comp as $c) {
        flush();
        if (array_search($c, $serverlist) !== false) {
            print("Installing $c Components....");
            $req = $installcomp[$c];
            $func = "installcomp_$c";
            if (function_exists($func)) {
                $func();
            }
            install_general_mine($req);
            print("\n");
        }
    }

    $pattern = "Include /etc/httpd/conf/kloxo/kloxo.conf";
    $file = "/etc/httpd/conf/httpd.conf";
    $comment = "#Kloxo";
    addLineIfNotExist($file, $pattern, $comment);
    mkdir("/etc/httpd/conf/kloxo/");
    $dir_path = dirname(__FILE__);
    copy("$dir_path/kloxo.conf", "/etc/httpd/conf/kloxo/kloxo.conf");
    touch("/etc/httpd/conf/kloxo/virtualhost.conf");
    touch("/etc/httpd/conf/kloxo/webmail.conf");
    touch("/etc/httpd/conf/kloxo/init.conf");
    mkdir("/etc/httpd/conf/kloxo/forward/");
    touch("/etc/httpd/conf/kloxo/forward/forwardhost.conf");


    $options_file = "/var/named/chroot/etc/global.options.named.conf";

    $example_options  = "acl \"lxcenter\" {\n";
    $example_options .= " localhost;\n";
    $example_options .= "};\n\n";
    $example_options .= "options {\n";
    $example_options .= " max-transfer-time-in 60;\n";
    $example_options .= " transfer-format many-answers;\n";
    $example_options .= " transfers-in 60;\n";
    $example_options .= " auth-nxdomain yes;\n";
    $example_options .= " allow-transfer { \"lxcenter\"; };\n";
    $example_options .= " allow-recursion { \"lxcenter\"; };\n";
    $example_options .= " recursion no;\n";
    $example_options .= " version \"LxCenter-1.0\";\n";
    $example_options .= "};\n\n";
    $example_options .= "# Remove # to see all DNS queries\n";
    $example_options .= "#logging {\n";
    $example_options .= "# channel query_logging {\n";
    $example_options .= "# file \"/var/log/named_query.log\";\n";
    $example_options .= "# versions 3 size 100M;\n";
    $example_options .= "# print-time yes;\n";
    $example_options .= "# };\n\n";
    $example_options .= "# category queries {\n";
    $example_options .= "# query_logging;\n";
    $example_options .= "# };\n";
    $example_options .= "#};\n";
    if (!lfile_exists($options_file)) {
        touch($options_file);
        chown($options_file, "named");
    }
    $cont = lfile_get_contents($options_file);
    $pattern = "options";
    if (!preg_match("+$pattern+i", $cont)) {
        file_put_contents($options_file, "$example_options\n");
    }
    $pattern = 'include "/etc/kloxo.named.conf";';
    $file = "/var/named/chroot/etc/named.conf";
    $comment = "//Kloxo";
    addLineIfNotExist($file, $pattern, $comment);
    touch("/var/named/chroot/etc/kloxo.named.conf");
    chown("/var/named/chroot/etc/kloxo.named.conf", "named");

}

function addLineIfNotExist($filename, $pattern, $comment) {
    $cont = lfile_get_contents($filename);

    if (!preg_match("+$pattern+i", $cont)) {
        file_put_contents($filename, "\n$comment \n\n", FILE_APPEND);
        file_put_contents($filename, $pattern, FILE_APPEND);
        file_put_contents($filename, "\n\n\n", FILE_APPEND);
    } else {
        print("Pattern '$pattern' Already present in $filename\n");
    }


}


function checkIfYes($arg) {
    return ($arg == 'y' || $arg == 'yes' || $arg == 'Y' || $arg == 'YES');
}


function getAcceptValue($soft) {
    print("Do you want me to install $soft Components? (YES/no):");
    flush();
    $argq = fread(STDIN, 5);
    $arg = trim($argq);
    if (!$arg) {
        $arg = 'yes';
    }
    return $arg;
}

install_main();
