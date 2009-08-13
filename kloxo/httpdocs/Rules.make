
.%.o:%.php
	php -e $< | sed "s/<b>//g" | sed "s/<.b>//g" | sed "s/<br>//g" | sed "s/<br \/>//g" ; exit $$PIPESTATUS
	touch $@

