nn=`tty`
pp="`echo $nn| cut -c-8`"
#[ $pp =  "/dev/tty" ] && setmetamode meta > /dev/null
unset pp nn
. ~/.etc/f.zshrc
if [ `tty` = "/dev/tty1"  ] ; then
	echo  Running KbdRate...
	kbdrate -r 40 -d 10
	echo "Running FetchMail  ...."
	loadkeys  -q .etc/keys
	if echo `hostname` | grep -q lxlabs.com ; then
		ftcm &
#prgcheck.sh &
		net-check &
		screen -q -wipe 
		echo -n "Wanna Start X? " 
		read input
		if [ "$input" != "n" ] ; then
			xinit &
		fi
	fi
#scr -RR terminal zsh
fi
if [ -f .local.bash_profile ] ; then
	source .local.bash_profile
fi
