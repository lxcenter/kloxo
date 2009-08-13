shopt -s cdable_vars
shopt -u dotglob
#set -o posix
#export SHELLOPTS

export NNTPSERVER=yellow.geeks.org
export SLRNPULL_ROOT=~/mail/news
export IRCNAME="The One And Only"
export HISTIGNORE=\&:ls:'ls -al':ps:'ps x':scr:cd:lv:lnx:fortune:reboot:shutdown:halt:
export FIGNORE=.o:.*:
export GLOBIGNORE=
export LIBDIR=~/.etc
export BASH_ENV=~/.etc/bashrc
export ENV=~/.etc/bashrc
export SHELL=/bin/bash
export INPUTRC=~/.etc/readln/inputrc
export HISTFILE=~/.etc/.tmp/.bash_history
export HISTFILESIZE=3333
export HISTSIZE=3333
export GZIP="-8"
export SCREENDIR=$HOME/.etc/.tmp/.screens
export SCREENRC=$HOME/.etc/screen/screenrc
export XENVIRONMENT=~/.etc/xxwin/xdefaults
export MCWD=~/.etc/.tmp/.mcwd
export MTOOLSRC=~/.etc/mtoolsrc
export XINITRC=~/.etc/xxwin/xinitrc
export LYNX_CFG=~/.etc/net/etc_linx.conf
export LYNX_LSS=~/.etc/net/llynx.lss
export VIMINIT="so ~/.etc/vim/vimrc.vim"
export GVIMINIT="so ~/.etc/vim/vimrc.vim"
export WGETRC=~/.etc/net/wgetrc
#export XAUTHORITY=~/.etc/xxwin/xauthority
export MPEGRC=~/.etc/VimAmp
export LESSKEY=~/.etc/lesskey
#export DISPLAY=Lingan:0.0
export VISUAL=vim
export EDITOR=vim
export MAILCHECK=300
#export MAILDIR=~/mail
export WNHOME=/usr/local/WordNet-1.7.1
#export IRCRC=~/.etc/irc/ircrc
export TMPDIR=/tmp
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/bin/mh:/etc/rc.d/init.d:/usr/X11R6/bin:/usr/games:/usr/local/bin:/usr/lib/cdwtools/:$HOME/.etc/bin:$HOME/.etc/bin/aascripts/:$WNHOME/bin:/home/lxlabs.com/gnats/bin:/home/lxlabs.com/gnats/libexec/gnats/:


if [ "$TERM" = "xterm-color" ] ; then
export VIM_TERM="xterm"
fi
#export LS_COLORS="di=0;36:ex=00;32:ln=06;33:bd=00;34:cd=00;34:no=00:fi=00;31:pi=40;31:so=00;35:or=00;05;37;41:mi=00;05;37;41:*.cmd=00;32:*.exe=00;32:*.com=00;32:*.btm=00;32:*.bat=00;32:*.sh=00;32:*.csh=00;32:*.tar=00;34:*.tgz=00;34:*.arj=00;34:*.taz=00;34:*.lzh=00;34:*.zip=00;34:*.z=00;34:*.Z=00;34:*.gz=00;34:*.bz2=00;34:*.bz=00;34:*.tz=00;34:*.rpm=00;34:*.cpio=00;34:*.jpg=00;35:*.gif=00;35:*.bmp=00;35:*.xbm=00;35:*.xpm=00;35:*.png=00;35:*.tif=00;35:"
export LS_COLORS="di=0;36:ex=00;32:ln=06;33:bd=00;34:cd=00;34:no=00:fi=00;37:pi=40;31:so=00;35:or=00;00;37;35:mi=00;00;37;00:*.cmd=00;32:*.exe=00;32:*.com=00;32:*.btm=00;32:*.bat=00;32:*.sh=00;32:*.csh=00;32:*.tar=00;34:*.tgz=00;34:*.arj=00;34:*.taz=00;34:*.lzh=00;34:*.zip=00;34:*.z=00;34:*.Z=00;34:*.gz=00;34:*.bz2=00;34:*.bz=00;34:*.tz=00;34:*.rpm=00;34:*.cpio=00;34:*.jpg=00;35:*.gif=00;35:*.bmp=00;35:*.xbm=00;35:*.xpm=00;35:*.png=00;35:*.tif=00;35:"

mbin=~/.etc/bin
metc=~/.etc
mmpg=~/.xmms
etmp=~/.etc/.tmp
btmp=~/.etc/bin/.tmp
bscr=~/.etc/bin/Scripts

if [ "$WINDOW" = 0 ] || [ "$WINDOW" = 1 ] || [ "$WINDOW" = 2 ] ; then
ulimit -c unlimited
fi

if [ -x /usr/games/fortune ] ; then
	fortune
fi
stty erase 
stty kill 
#stty start undef
#stty stop undef
stty -ixon

. ~/.etc/bashrc


export PWLIBDIR=/home/root/wh/inc/new/pwlib
export OPENH323DIR=/home/root/wh/inc/new/openh323
