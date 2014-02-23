#!/bin/sh
#    Kloxo, Hosting Control Panel
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2014	LxCenter
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#   This file is loaded from kloxo-wrapper.sh
#
__path_php_path="/usr/local/lxlabs/ext/php/php";
__path_program_root="/usr/local/lxlabs/kloxo";
__path_slave_db="/usr/local/lxlabs/kloxo/etc/conf/slave-db.db";

__path_server_path="/usr/local/lxlabs/kloxo/sbin/kloxo.php";
__path_server_exe="/usr/local/lxlabs/kloxo/sbin/kloxo.exe";
__path_low_memory_file="/usr/local/lxlabs/kloxo/etc/flag/lowmem.flag";

kill_and_save_pid() {
	name=$1
	kill_pid $name;
	usleep 100;
	save_pid $name;
}

save_pid() {
	echo $$ > "$__path_program_root/pid/kloxo.pid";
}

kill_pid() {
	name=$1
	pid=`cat $__path_program_root/pid/kloxo.pid`;
	kill $pid 2>/dev/null
	usleep 1000
	kill -9 $pid 2>/dev/null
}

wrapper_main() {

	if [ -f $__path_slave_db ] ; then
		string="slave";
	else 
		string="master";
	fi


	mkdir ../log 2>/dev/null
	mkdir ../pid 2>/dev/null
	while : ; do

		if [ -f $__path_low_memory_file ] ; then
			/bin/cp $__path_server_exe.core $__path_server_exe;
			chmod 755 $__path_server_exe;
		    echo "Starting Kloxo core (lowmem)"
			$__path_server_exe $string >/dev/null 2>&1;
		else
		    echo "Starting Kloxo core"
			$__path_php_path $__path_server_path $string >/dev/null 2>&1;
	 	fi
			sleep 10;
	done
}

