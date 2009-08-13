include Rules.make

export DIRS = bin

.PHONY: bin

all: $(DIRS)
	@for i in $(DIRS) ; do if ! $(MAKE) -C $$i ; then exit 2 ; fi ; done

clean:
	@for i in $(DIRS) ; do if ! $(MAKE) -C $$i clean ; then exit 2 ; fi ; done


