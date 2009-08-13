#/bin/bash
beans=`cat /proc/user_beancounters | grep priv`
max=`echo $beans | awk '{ print $4;}'`
use=`echo $beans | awk '{ print $2;}'`
let "per=$use*100/$max"
let "umb=$use/256"
let "tmb=$max/256"
let "fmb=$tmb - $umb"
echo $fmb
