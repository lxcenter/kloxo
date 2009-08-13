#!/bin/sh
file=~/.ssh/id_dsa
if [ $# = 0 ] ; then echo "Usage: $0 <username>@<servername>"  ; exit ; fi
if ! [ -f $file ] ; then  ssh-keygen -d -q -N '' -f $file ; fi;
cat $file.pub | ssh "$@" "(mkdir -p .ssh; cat>>.ssh/authorized_keys2 ; chmod -R 700 .ssh )"

