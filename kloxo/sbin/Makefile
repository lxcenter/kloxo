include ../Rules.make

PROGS = lxmisc


all: $(PROGS)

lxserver: lxserver.c commands.c
	gcc -o lxserver src/lxserver.c ${INCLUDE_PATH}

lxrestart: lxrestart.c
	gcc -o lxrestart src/lxrestart.c ${INCLUDE_PATH}

lxmisc: lxmisc.c
	gcc -o lxmisc src/lxmisc.c ${INCLUDE_PATH}

clean:
	rm -f *.o $(PROGS)

package:
	-mkdir -p $(INSTALLDIR)/sbin/
	/bin/cp $(PROGS) $(INSTALLDIR)/sbin/



vim_run:
	./lxserver
