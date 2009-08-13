nn=`tty`
pp="`echo $nn| cut -c-8`"
[ $pp =  "/dev/tty" ] && setmetamode meta > /dev/null
unset pp nn
. .etc/f.bashrc
if [ `tty` = "/dev/tty1"  ] ; then
	echo -n "Running FetchMail  ...."
	loadkeys  -q .etc/keys
	if [ "$HOSTNAME" = "root" ] ; then 
		ftcm &
		fetcheck.sh &
		screen -q -wipe 
		echo ""
		read  -p "Wanna Start X? " input
		if [ "$input" != "n" ] ; then
			xinit &
		fi
	fi
	scr -RR terminal bash --rcfile ~/.etc/f.bashrc
fi
if [ -f .local.bash_profile ] ; then
	source .local.bash_profile
fi
