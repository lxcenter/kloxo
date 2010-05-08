#	Compiling and pre-installing(upcp and version update from GUI will do that also)
#	lxrestart
#	lxexec
#	lxsuexec
#	lxphpsu
#	kloxo.exe
#.	kloxo.exe.core
LIBS="-lssl"

all:
	gcc -o lxrestart lxrestart.c
	rm -f ../cexe/lxrestart
	cp lxrestart ../cexe/lxrestart
	rm -f lxrestart
	cp ../cexe/lxrestart /usr/sbin/
	chown root:root /usr/sbin/lxrestart
	chmod 755 /usr/sbin/lxrestart
	chmod ug+s /usr/sbin/lxrestart

	gcc -o lxsuexec ./lxsuexec.c
	gcc -o lxexec ./lxexec.c
	cp lxsuexec ../cexe/lxsuexec
	cp lxexec ../cexe/lxexec
	cp ../cexe/lxsuexec /usr/bin/lxsuexec
	chmod 755 /usr/bin/lxsuexec
	chmod ug+s /usr/bin/lxsuexec
	cp ../cexe/lxexec /usr/bin/lxexec
	chmod 755 /usr/bin/lxexec
	chmod ug+s /usr/bin/lxexec

	gcc -o lxphpsu ./lxphpsu.c
	cp lxphpsu ../cexe/lxphpsu
	chmod 755 ../cexe/lxphpsu
	chmod ug+s ../cexe/lxphpsu

	rm -f kloxo.o
	gcc -o kloxo.exe ./kloxo.c ${LIBS}
	cp kloxo.exe ../sbin/kloxo.exe
	cp kloxo.exe ../sbin/kloxo.exe.core
	rm -f kloxo.exe

	echo "######### Done."
