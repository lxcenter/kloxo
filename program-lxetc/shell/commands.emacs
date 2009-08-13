#!/bin/zsh
qeval()
{
    fn=$1
    shift
    args=""
    for arg in $*; do args="$args "\"$arg\"; done
    gnuclient -batch -q -eval "(save-excursion \
        (split-window-vertically) \
        (other-window 1 nil) \
        (condition-case err \
            ($fn $args) \
            (t (delete-window))))"
}

mn () 
{ 
    gnuclient -batch -eval "(woman \"$1\")"  ;
}

info () 
{ 
    qeval info $1
}


edit () 
{ 
    gnuclient "$1" ;
}

open () 
{ 
    qeval find-file-other-frame $1
}

view () 
{ 
    gnuclient -batch -v "$1" ;
}




rename-buffer ()
{       
    gnuclient -eval "(rename-buffer \"$1\")"
}



set-variable ()
{       
    gnuclient -eval "(set-variable \"$1\" \"$2\")"
}


vi () 
{ 
    gnuclient -batch -eval "(find-file \"$1\")" ;
}

cd ()
{
    builtin cd "${1:-$HOME}";
    gnuclient -batch -eval "(cd \"`pwd`\")" > /dev/null  2>&1;
}

ixgrep ()
{        
    pat=$1
    shift
    args=""
    for arg in $*; do args="$args "\"$arg\"; done
    gnuclient -batch -eval "(igrep nil \"$pat\" '($args))"
}


igrep ()
{        
    gnuclient -batch -eval "(grep \"grep -n $*\")"
}

lnx ()
{
     gnuclient -batch -eval "(w3m-dtree-on-cur \"$1\")"
}

