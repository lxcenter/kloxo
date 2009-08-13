
.PHONY: sbin httpdocs

SUBDIRS = sbin httpdocs

all: $(SUBDIRS)

$(SUBDIRS):
	make -C $@


vim_run:
	sample.lxlabs.com/clients/client.php


xvclean:
	-find . -follow -name '.xvpics' -exec rm -rf '{}' ';'

clean_all: 
	-find . -follow -name 'core' -exec rm -f '{}' ';'

cvsclean:
	-find . -follow -name '.#*' -exec rm '{}' ';'

distclean: clean_all cvsclean
	-find . \( -name tags  -o -name ID \) -exec rm -f '{}' ';'
	-find . -name '.xvpics' -exec rm -rf '{}' ';'
	-find . -name '*.o' -exec rm -rf '{}' ';'
	-find . -name '.*.o' -exec rm -rf '{}' ';'
	-find . -name error-output -exec rm -rf '{}' ';'

realclean: distclean
	-find . -name 'CVS' -exec rm -rf '{}' ';'
