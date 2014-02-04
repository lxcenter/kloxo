#	Compiling and pre-installing(upcp and version update from GUI will do that also)
#	lxrestart
#	lxexec
#	lxsuexec
#	lxphpsu
#	kloxo.exe
#.	kloxo.exe.core
LIBS="-lssl"

all:
	rm -f lxsuexec
	rm -f lxexec
	rm -f lxrestart
	rm -f lxphpsu
	rm -f kloxo.exe
	rm -f kloxo.o

	rm -f /usr/bin/lxsuexec
	rm -f /usr/bin/lxexec
	rm -f /usr/sbin/lxrestart
	rm -f ../cexe/lxrestart
	rm -f ../cexe/lxsuexec
	rm -f ../cexe/lxexec
	rm -f ../cexe/lxphpsu
	rm -f ../sbin/kloxo.exe
	rm -f ../sbin/kloxo.exe.core

#	gcc -o lxrestart lxrestart.c
#	cp lxrestart ../cexe/lxrestart
#	cp ../cexe/lxrestart /usr/sbin/
#	chown root:root /usr/sbin/lxrestart
#	chmod 755 /usr/sbin/lxrestart
#	chmod ug+s /usr/sbin/lxrestart

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

	gcc -o kloxo.exe ./kloxo.c ${LIBS}
	cp kloxo.exe ../sbin/kloxo.exe
	cp kloxo.exe ../sbin/kloxo.exe.core

	rm -f lxsuexec
	rm -f lxexec
	rm -f lxrestart
	rm -f lxphpsu
	rm -f kloxo.exe
	rm -f kloxo.o

	echo "######### Done."
