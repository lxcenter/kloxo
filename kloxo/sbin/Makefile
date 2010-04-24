
#       Compiling and installing lxrestart
#
#
all:
        gcc -o lxrestart src/lxrestart.c
        rm -f ../cexe/lxrestart
        cp lxrestart ../cexe/lxrestart
        rm -f lxrestart
        cp ../cexe/lxrestart /usr/sbin/
        chown root:root /usr/sbin/lxrestart
        chmod 755 /usr/sbin/lxrestart
        chmod ug+s /usr/sbin/lxrestart
        echo "######### Done."
