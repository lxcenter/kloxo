#set -o posix
#export SHELLOPTS

export NNTPSERVER=yellow.geeks.org
export SLRNPULL_ROOT=~/mail/news
export IRCNAME="The One And Only"
export HISTIGNORE=\&:ls:'ls -al':ps:'ps x':scr:cd:lv:lnx:fortune:reboot:shutdown:halt:
#export FIGNORE=.o:.*:
export GLOBIGNORE=
export LIBDIR=~/.etc
export BASH_ENV=~/.etc/bashrc
export ENV=~/.etc/zshrc
export SHELL=/bin/zsh
export INPUTRC=~/.etc/readln/inputrc
export HISTFILE=~/.etc/.tmp/.bash_history
#export HISTFILESIZE=18888
export SAVEHIST=19999
export HISTSIZE=7555
#export GZIP="-8"
#export SCREENDIR=$HOME/.etc/.tmp/.screens
export SCREENRC=$HOME/.etc/screen/screenrc
#export XENVIRONMENT=~/.etc/xxwin/xresource
export MCWD=~/.etc/.tmp/.mcwd
export MTOOLSRC=~/.etc/mtoolsrc
export DIALOGRC=~/.etc/dialogrc
export XINITRC=~/.etc/xxwin/xinitrc
export LYNX_CFG=~/.etc/net/etc_linx.conf
export LC_ALL="C"
export LISTMAX="44440"
export HOSTNAME=`hostname`
export LYNX_LSS=~/.etc/net/llynx.lss
export VIMINIT="so ~/.etc/vim/vimrc.vim"
export TSM_DEVICE="/dev/ttyS0"
export GVIMINIT="so ~/.etc/vim/vimrc.vim"
export WGETRC=~/.etc/net/wgetrc
#export XAUTHORITY=~/.etc/xxwin/xauthority
export MPEGRC=~/.etc/VimAmp
export LESSKEY=~/.etc/lesskey
#export DISPLAY=Lingan:0.0
export VISUAL=~/.etc/bin/brvim.sh
export EDITOR=$VISUAL
export MAILCHECK=300
#export MAILDIR=~/mail
export WNHOME=/usr/local/WordNet-1.7.1
export TMPDIR=/tmp
export VNC_SERVER=titan
export GNATSD=support.lxlabs.com
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/etc/rc.d/init.d:/usr/X11R6/bin:/usr/games:/usr/local/sbin:/usr/local/bin:/usr/lib/cdwtools/:$HOME/.etc/bin:$HOME/.etc/bin/aascripts/:$HOME/.etc/bin/apt/:$WNHOME/bin:$JAVA_HOME/bin:$HOME/bin:


if [ "$TERM" = "xterm-color" ] ; then
export VIM_TERM="xterm"
fi
#export LS_COLORS="di=0;36:ex=00;32:ln=06;33:bd=00;34:cd=00;34:no=00:fi=00;31:pi=40;31:so=00;35:or=00;05;37;41:mi=00;05;37;41:*.cmd=00;32:*.exe=00;32:*.com=00;32:*.btm=00;32:*.bat=00;32:*.sh=00;32:*.csh=00;32:*.tar=00;34:*.tgz=00;34:*.arj=00;34:*.taz=00;34:*.lzh=00;34:*.zip=00;34:*.z=00;34:*.Z=00;34:*.gz=00;34:*.bz2=00;34:*.bz=00;34:*.tz=00;34:*.rpm=00;34:*.cpio=00;34:*.jpg=00;35:*.gif=00;35:*.bmp=00;35:*.xbm=00;35:*.xpm=00;35:*.png=00;35:*.tif=00;35:"
export LS_COLORS="di=0;36:ex=00;32:ln=06;33:bd=00;34:cd=00;34:no=00:fi=00;38:pi=40;31:so=00;35:or=00;00;37;35:mi=00;00;37;00:*.cmd=00;32:*.exe=00;32:*.com=00;32:*.btm=00;32:*.bat=00;32:*.sh=00;32:*.csh=00;32:*.tar=00;34:*.tgz=00;34:*.arj=00;34:*.taz=00;34:*.lzh=00;34:*.zip=00;34:*.z=00;34:*.Z=00;34:*.gz=00;34:*.bz2=00;34:*.bz=00;34:*.tz=00;34:*.rpm=00;34:*.cpio=00;34:*.jpg=00;35:*.gif=00;35:*.bmp=00;35:*.xbm=00;35:*.xpm=00;35:*.png=00;35:*.tif=00;35:"

mbin=~/.etc/bin
metc=~/.etc
mmpg=~/.xmms
etmp=~/.etc/.tmp
btmp=~/.etc/bin/.tmp
bscr=~/.etc/bin/Scripts

if [ "$WINDOW" = 0 ] || [ "$WINDOW" = 1 ] || [ "$WINDOW" = 2 ] ; then
ulimit -c unlimited
fi

if [ -z "$VIM_PSE" ]  && [ -z "$SSH_TTY" ] 
then 
#send-tty `tty` 
fi

if [ -n "$SSH_TTY" ]  ; then
	if [ -f ~/dbase/motd ] ; then
		echo
		cat ~/dbase/motd
		echo
	fi

	local sshcl
	sshcl=`echo $SSH_CLIENT | cut -f 1 -d ' '`
	export DISPLAY=$sshcl:0
elif [ $OSTYPE = "linux" ] ; then
	if [ -x /usr/games/fortune ] ; then
		fortune
	fi
fi


stty erase 
stty kill 
stty start undef
stty stop undef
stty -ixon


. ~/.etc/zshrc

preexec() {
}

if [ -f ~/.etc/local-f.zshrc ] ; then
	source ~/.etc/local-f.zshrc
fi


case `uname` in
*Interix*)  source ~/.etc/interix/f.intsh ;;
esac

export PWLIBDIR=/home/root/wh/inc/new/pwlib
export OPENH323DIR=/home/root/wh/inc/new/openh323
