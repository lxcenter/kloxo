#!/bin/sh
export MUID=0
export GID=0
export TARGET=/usr/local/lxlabs/ext/php/bin/php_cgi
export NON_RESIDENT=1
exec /usr/local/lxlabs/kloxo/cexe/lxphpsu $*
