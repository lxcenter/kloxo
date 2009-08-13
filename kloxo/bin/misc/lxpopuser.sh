if [ -z "`/usr/bin/id -g lxpopgroup 2>/dev/null`" ]; then
	/usr/sbin/groupadd -g 1005 -r lxpopgroup >/dev/null 2>&1 || :
fi

if [ -z "`/usr/bin/id -u lxpopuser 2>/dev/null`" ]; then
	/usr/sbin/useradd -u 1005 -r -M -d /home/kloxo/mail/  -s /sbin/nologin -c "Vpopmail User" -g lxpopgroup lxpopuser 2>&1 || :
fi
