##########################################################################
# Look at /tmp/fvwm* output to see what fvwm is defining things
# to be on your platform
#
# Need to muddle with comment characters so these lines don't get ignored by m4
# but this needs to get passed through fvwm w/o wreaking havoc

#FVWM-M4-Defines
#SERVERHOST=mhlabs.mheads
#CLIENTHOST=mhlabs.mheads
#HOSTNAME=mhlabs.mheads
#OSTYPE=Linux
#USER=root
#VERSION=11
#VENDOR=The XFree86 Project, Inc
#RELEASE=3330
#WIDTH=1024
#HEIGHT=768
#X_RESOLUTION=2951
#Y_RESOLUTION=2954
#PLANES=8
#CLASS=PseudoColor
#COLOR=Yes
#FVWM_VERSION=2.2
#OPTIONS=SHAPE XPM M4 
#FVWMDIR=/usr/X11R6/lib/X11/fvwm2
#
#XRELEASE=3330


# Get the user options.  This is the only file the casual user needs
# to worry about.
# define(`FVWM_USER_MODULE_PATH',`.')




# To keep .fvwm2rc.defines clean, we define default values as necessary 
# through a separate file



# Now read the user options


# Now read in some interesting macros...


IgnoreModifiers L



###############################################################################
# Set our all-important paths.
# Note that your "executables" path (the one your shell uses to search for
# programs it tries to exec) is set *before* fvwm starts.  Usually it is
# set by .xinitrc or .xsession, which hopefully sources some environment
# settings files like /etc/env, /etc/profile, ~/.env, ~/.profile, etc.#
ModulePath /usr/X11R6/lib/X11/fvwm2/:/usr/local/libexec/fvwm/2.5.5/:/root/.etc/bin/modules/:
ImagePath /usr/X11R6/include/X11/pixmaps/:/usr/share/icons/:/usr/share/icons/mini/:/usr/share/pixmaps/:/usr/share/pixmaps/redhat/:
ImagePath   /usr/X11R6/include/X11/bitmaps/:/usr/share/icons/:/usr/share/icons/mini/:/usr/share/pixmaps/:/usr/share/pixmaps/redhat/:

# This is the real magic of AnotherLevel - decorations...
# To date it is supposed to work for Win95, Mwm, and Afterstep



define(`XTERM', `xterm')
#XTermOtherHost <Host> <Title>
#Starts an xterm running a telnet/rlogin to another host (the xterm runs locally)
AddToFunc XTermOtherHost
+ "I" exec xterm -name remotexterm -T $0 -n $0 -e rlogin $1

###########################################################################
#
# Set misc. interaction preferences
#
#ExecUseShell /bin/bash
ExecUseShell /bin/zsh
ClickTime	150
TitleStyle	LeftJustified -- flat
ColorMapFocus	FollowsFocus
DeskTopSize	1x1
OpaqueMoveSize	30
EdgeResistance	0 0
EdgeScroll	0 0

# DECORS: Now we are ready to include the choosen decoration...




# make sure we have that decor





# WM_STYLE     =FVWM95
# WM_STYLE_PATH=/etc/X11/AnotherLevel/decors/FVWM95


##########################################################################
# Fvwm95 Look and Feel
#========================================================================#



# Uncomment the following if you don' want the CLOSE (X) button
#define(`NO_CLOSE_BUTTON')

# HILIGHTFORE_COLOR, HILIGHTBACK_COLOR are the title bar colors for the window
# with the focus



# WINFORE_COLOR, WINBACK_COLOR are the title bar colors for the window w/o the focus



# MENUFORE_COLOR, MENUBACK_COLOR MENUHI_COLOR are the colors for menus




# MENUSHADE_COLOR is the color for greyed out menu items


# BG_COLOR is the color that the background will initially get set to
# Edit .fvwm2rc.init to change what commands get run at startup


# First, make sure we start with a clean base
#TitleStyle	LeftJustified ActiveUp (Solid grey30 -- flat)  ActiveDown (Solid black  -- flat)  Inactive (Solid black -- flat)

ButtonStyle All	-- UseTitleStyle flat
#ButtonStyle Active	MiniIcon -- UseTitleStyle flat
#ButtonStyle 2	Pixmap win95-close-full.xpm -- flat
#ButtonStyle 4	Pixmap win95-maximize-full.xpm -- flat
#ButtonStyle 6	Pixmap win95-minimize-full.xpm -- UseTitleStyle flat
ButtonStyle 2 Vector 4 30x30@3 70x70@1  70x30@4 30x70@1
#ButtonStyle 4 Vector 4 50x30@1 70x70@0  30x70@0 50x30@1
ButtonStyle 4 Vector 5 30x30@0  30x70@1 70x70@0  70x30@0 30x30@1
ButtonStyle 6 Vector 4 50x70@1 70x30@0  30x30@1 50x70@1


BorderStyle	Active  -- HiddenHandles NoInset
BorderStyle	Inactive --  HiddenHandles NoInset
WindowFont	lucidasans-bold-14
HilightColor	grey73 grey36
#MenuStyle	Black grey55 grey28  *helvetica-bold-r-*-120-* win
MenuStyle * 	mwm, Foreground grey73, Background black
MenuStyle * 	HilightBack grey29 , ActiveFore grey66
MenuStyle *	HiLight3DOff
MenuStyle *	SeparatorsShort 
#MenuStyle *	NoIcon
MenuStyle * 	Font lucidasans-bold-12 
#MenuStyle * 	Font 10x20
#MenuStyle *	 Animation
#MenuStyle *	 MenuFace, PopupDelay,
#MenuStyle *	 Pop-upOffset +0-0 



Style "*"	ForeColor grey67, BackColor grey27
Style "*"	Button 1
Style "*"	Button 2, Button 4, Button 6 
Style "*"	FvwmButtons, FvwmBorder, NoDecorHint, NoFuncHint
Style "*"	DumbPlacement, RandomPlacement
Style "*" 	Handles, HandleWidth 5, BorderWidth 10, StickyIcon
Style "*"	ClickToFocus, GrabFocus
Style "*"	NoIcon, NoIconTitle, DecorateTransient
#Style "*"	MiniIcon shadowman-mini.xpm

# read in the Mouse settings and customize them
Style "kppp"  Slippery, nohandles, borderwidth 0,
Style "ee"  notitle, nohandles, borderwidth 0,
Style "rdesktop" Slippery
#Style "kppp" StartsOnPage 0 1 0,  CirculateSkip
Style "xtel" StartsOnPage 0 1 0
Style "x11amp" NoTitle, NoHandles, BorderWidth 0 ,Slippery
Style "xmms" NoTitle, NoHandles, BorderWidth 0 ,Slippery
Style "nxterm" NoTitle, NoHandles,  BorderWidth 0 
Style "nxtwin" NoTitle, NoHandles,  BorderWidth 0 
Style "xmdi" HilightBack "#00420b",  ForeColor "#00420b", BackColor grey27
Style "lxlclientshareff" HilightBack "#00527b",  ForeColor "#00527b", BackColor grey27
Style "lxlxchat" HilightBack "#00527b",  ForeColor "#00527b", BackColor grey27
Style "Vncviewer" HilightBack "#00527b",  ForeColor "#00527b", BackColor grey27
Style "lxlclientsharefl" HilightBack "#007040",  ForeColor "#00527b", BackColor grey27
Style "lxlclientshareo" HilightBack "#704200",  ForeColor "#00527b", BackColor grey27
Style "xmlance" HilightBack "#00420b",  ForeColor "#00420b", BackColor grey27
#Style "emacs" NoTitle, NoHandles,  BorderWidth 0 
Style "nemacs" NoTitle, NoHandles,  BorderWidth 0 
#Style "emdict" NoHandles, TitleAtBottom, TitleUnderlines0, NoBorder, BorderWidth 0, HandleWidth 1, NoButton, HilightBack "#a0920b"
Style "emdict" NoHandles, TitleAtBottom,   BorderWidth 0, HandleWidth 1, NoButton 3, HilightBack "#30020b", HilightFore "#00a5b8"
Style "ncemacs" NoTitle, NoHandles,  BorderWidth 0, StartsOnDesk -1
Style "anaconda" NoTitle, NoHandles,  BorderWidth 0 
Style "gtktetris" NoTitle, NoHandles,  BorderWidth 0 


###########################################################################
# Mouse Bindings
# Contexts mean:
#     R = Root Window                 rrrrrrrrrrrrrrrrrrrrrr
#     W = Application Window          rIrrrrFSSSSSSSSSFrrrrr
#     F = Frame Corners               rrrrrrS13TTTT642Srrrrr
#     S = Frame Sides                 rIrrrrSWWWWWWWWWSrrrrr
#     T = Title Bar                   rrrrrrSWWWWWWWWWSrrrrr
#     I = Icon                        rIrrrrFSSSSSSSSSFrrrrr
#                                     rrrrrrrrrrrrrrrrrrrrrr
# Numbers are buttons 1 3 5      6 4 2
#
# Modifiers: (A)ny (C)ontrol (S)hift (M)eta

# Root window clicks
#     Button	Context Modifi 	Function
#Mouse 1		R   	A       Menu StartMenu Nop
#Mouse 2		R	A	WindowList
#Mouse 3		R	A	Menu AUTO_WM_CONFIG Nop
#
#Mouse 1		T 	A	Function "Move-or-Raise-or-Maximize"
#Mouse 1		FS 	A	Resize
#Mouse 2		FST 	A	Menu "Window-Ops-Basic" Nop
#Mouse 2		A 	C	Menu "Window-Ops-Basic" Nop
#Mouse 2		A 	M	WindowList
#Mouse 3		TSIF 	A	RaiseLower
#Mouse 1		A 	CSM	RaiseLower
#Mouse 2		A 	CSM	Function "Move-or-Raise"
#Mouse 3		A 	CSM	Function "Resize-or-Raise"
#Mouse 1		I 	A	Function "Iconify-move-maximize"
#Mouse 2		I 	A 	Menu "Window-Ops" Nop
#Mouse 3		I 	A	Function "Iconify-maximize"
#
#
Mouse 0	1 N	Menu Window-Ops Close
#
#
Mouse 0	2 N	Close
Mouse 0	4 N	Maximize
Mouse 0	6 N	Iconify
#Mouse 3	6 N	Replace

AddToFunc "ShareProper" "I" Next [nxterm] Raise
+ "I" Iconify -1
+ "I" Current Raise
+ "I" Next [nxterm] Focus

AddToFunc "FocusRaise" "I" Focus
+ "I" Iconify -1
+ "I" Raise


AddToFunc "NextFocusRaise" "I" Next [!iconic CurrentPage !lxl* CurrentDesk *] FlipFocus
+ "I" Current Raise

AddToFunc "PrevFocusRaise" "I" prev [!iconic CurrentPage CurrentDesk *] Focus
+ "I" Current Raise

AddToFunc  "NextFocusAny" "I" next [!iconic * ] Focus
+ "I" Current Raise

AddToFunc "SetupFunction"
#+ "I" Module FvwmTaskBar 
#+ "I" Module FvwmButtons


###########################################################################
#
# Fonts - one for window titles, another for icons, and another for the menus
#
WindowFont	*helvetica*bold-r*12*
IconFont	fixed
#MenuStyle	black grey66 grey31 *helvetica*medium-r*12* win



AddToMenu WindowManagers
+ ""	Title
+  "FVWM 95"		Restart /usr/X11R6/bin/RunWM.Fvwm95
+  "Lesstif WM"		Restart /usr/X11R6/bin/RunWM.MWM
+  "AfterStep"		Restart /usr/X11R6/bin/RunWM.AfterStep
#wmaker not found




###########################################################################
#
# Set up the basic colors
#

###########################################################################
# Stuff to do at start-up
# User initialization is done in .fvwm2rc.init, included at bottom.
##########

AddToFunc "InitFunction"
+ "I" SetupFunction
+ "I" StartupFunction
+ "I" EndSetupFunction

AddToFunc "RestartFunction"
+ "I" SetupFunction
+ "I" EndSetupFunction

###########################################################################
# Set the decoration styles and window options for specific apps
# Order does matter... if compatible styles are set for a single window
# in multiple Style commands, then the styles are ORed together. If
# conflicting styles are set, the last one specified is used.

###########################################################################
#
# Include all the other support files
#

# Various complex functions


###########################################################################
# Now define some handy complex functions
# (I)mmediate, (M)otion, (C)lick, (D)oubleclick

# This one moves and then raises the window if you drag the mouse,
# only raises the window if you click, or does a full maximize if 
# you double click


AddToFunc "Move-or-Raise-or-Maximize" 
+ "M" Move
+ "M" Raise
+ "C" Raise
+ "D" Maximize

AddToFunc "Move-or-Raise" "I" WarpToWindow 0 0 
+ "M" Move
+ "M" Raise
+ "C" Raise

AddToFunc "Move-and-Raise"
+ "I" Move
+ "I" Raise

#
# This one moves and then lowers the window if you drag the mouse,
# only lowers the window if you click, or does a RaiseLower if you double 
# click
#
AddToFunc "Move-or-Lower" 
+ "M" Move
+ "M" Lower
+ "C" Lower
+ "D" RaiseLower

#
# This one moves or (de)iconifies:
#
AddToFunc "Move-or-Iconify" 
+ "M" Move
+ "D" Iconify

#
# This one resizes and then raises the window if you drag the mouse,
# only raises the window if you click,  or does a RaiseLower if you double 
# click
#
AddToFunc "SpecResize"
+ "M" Resize

AddToFunc "Resize-or-Raise" "I" WarpToWindow 100 100
+ "M" Resize
+ "M" Raise
+ "C" Raise
+ "D" RaiseLower

AddToFunc "Iconify-Move-Maximize" 
+ "C" Iconify
+ "D" Iconify
+ "D" Maximize
+ "M" Move

AddToFunc "Iconify-Maximize" 
+ "C" Iconify
+ "C" Maximize

AddToFunc "Shade-or-Raise"
+ "M" Move
+ "C" Raise
+ "D" WindowShade

AddToFunc "Maximize_Function"
+ "M" Move
+ "C" Maximize
+ "D" WindowShade

AddToFunc "Delete-or-Popup" 
+ "M" PopUp Window-Ops-Basic
+ "C" PopUp Window-Ops-Basic
+ "D" Delete

AddToFunc "Close-or-Popup" 
+ "M" PopUp Window-Ops-Basic
+ "C" PopUp Window-Ops-Basic
+ "D" Close

AddToFunc Raise-and-Stick "I" Raise
+ "I" Stick

AddToFunc MailFunction
+ "I" Next [$0] Iconify -1
+ "I" Next [$0] focus
+ "I" None [$0] Exec $0 $1

AddToFunc StartXTerm "I" Exec xterm
+ "I" Wait xterm
+ "I" Next [xterm] Focus 

AddToFunc PrintFunction
+ "I" Raise
+ "I" Exec xvwd -id $w



# Various key bindings



###########################################################################
# Now some keyboard shortcuts.
# Contexts mean:
#     R = Root Window                 rrrrrrrrrrrrrrrrrrrrrr
#     W = Application Window          rIrrrrFSSSSSSSSSFrrrrr
#     F = Frame Corners               rrrrrrS13TTTT642Srrrrr
#     S = Frame Sides                 rIrrrrSWWWWWWWWWSrrrrr
#     T = Title Bar                   rrrrrrSWWWWWWWWWSrrrrr
#     I = Icon                        rIrrrrFSSSSSSSSSFrrrrr
#                                     rrrrrrrrrrrrrrrrrrrrrr
# Numbers are buttons, odd on left, even on right:  1 3 5      6 4 2
#
# Modifiers: (A)ny (C)ontrol (S)hift (M)eta

# press arrow in the root window, and move the pointer to other virtual wins
Key Left	R	N	CursorMove -5 +0
Key Right	R	N	CursorMove +5 +0
Key Up		R	N	CursorMove +0 -5
Key Down	R	N	CursorMove +0 +5

Key j		R	N	CursorMove -5 +0
Key l		R	N	CursorMove +5 +0
Key i		R	N	CursorMove +0 -5
Key k		R	N	CursorMove +0 +5






# Arrow Keys
# press arrow + control SHIFT meta anywhere, and scroll by 1 page
#Key Left	A	3	Scroll -100 0
#Key Right	A	3	Scroll +100 +0
#Key Up		A	3	Scroll +0   -100
#Key Down	A	3	Scroll +0   +100


Key Left	A	3	GotoDesk -1 -2 +2
Key Right	A	3	GotoDesk 1
Key Up		A	3	Desk 2
Key Down	A	3	Desk -2


Key j	A	3	GotoDesk 0 -1
Key l	A	3	GotoDesk 0 1
Key i	A	3	Desk 0 -2
Key k	A	3	Desk 0 0
Key o	A	3	Desk 0 2


#Key Left	A	CM	Scroll -100 0
#Key Right	A	CM	Scroll +100 +0
#Key Up		A	CM	Scroll +0   -100
#Key Down	A	CM	Scroll +0   +100
#

#Key j		A	3	Scroll -100 0
#Key l		A	3	Scroll +100 +0
#Key i		A	3	Scroll +0   -100
#Key k		A	3	Scroll +0   +100
#




Key Left	A	2	Scroll -10 0
Key Right	A	2	Scroll +10 +0
Key Up		A	2	Scroll +0   -10
Key Down	A	2	Scroll +0   +10

Key Left	A	S3	Scroll -10 0
Key Right	A	S3	Scroll +10 +0
Key Up		A	S3	Scroll +0   -10
Key Down	A	S3	Scroll +0   +10

Key j		A	S3	Scroll -10 0
Key l   	A	S3	Scroll +10 +0
Key i		A	S3	Scroll +0   -10
Key k		A	S3	Scroll +0   +10




# Keypad Arrow keys, scroll by 10% of page
Key KP_4	A	2	CursorMove -5 +0
Key KP_1	A	2	CursorMove -5 +5
Key KP_6	A	2	CursorMove +5 0
Key KP_9	A	2	CursorMove +5 -5
Key KP_8	A	2	CursorMove 0 -5
Key KP_2	A	2	CursorMove 0 +5
Key KP_3	A	2	CursorMove +5 +5
Key KP_7	A	2	CursorMove -5 -5
#
#Key KP_4	A	2S	CursorMove -10 +0
#Key KP_6	A	2S	CursorMove +10 0
#Key KP_8	A	2S	CursorMove 0 -10
#Key KP_2	A	2S	CursorMove 0 +10
#
#Key KP_4	A	2C	CursorMove -1 +0
#Key KP_6	A	2C	CursorMove +1 0
#Key KP_8	A	2C	CursorMove 0 -1
#Key KP_2	A	2C	CursorMove 0 +1
#
Key KP_7	A	CSM	CursorMove -100 -100
Key KP_1	A	CS	CursorMove +98 +98

# Keyboard accelerators
#Key F34		A	N	Popup StartMenu Root 0 80
Key r		A	3S	Exec rds
Key s		A	3S	Popup StartMenu Root 0 70
Key s		A	CSM	Popup StartMenu Root 0 70
Key a		A	3	Function  StartMms
Key e		A	3	Next [nemacs] Function FocusRaise
Key a		A	3	Next [nxterm] Function FocusRaise
Key w		A	3	Next [nxtwin] Function FocusRaise
Key r		A	3	Next [rdesktop] Function FocusRaise
Key g		A	3	Next [Galeon] Function FocusRaise
Key m		A	3	Next [Vncviewer !iconic] Function FocusRaise
Key c		A	3	Next [lxlclientsharef* !iconic] Function FocusRaise
Key x		A	3	Next [lxlclientshareo* !iconic] Function FocusRaise
key i		A	3   Function ShareProper
Key n		A	3	WindowList SelectOnRelease, root 0 0 CurrentDesk Alphabetic
Key Tab 	A	3	Function NextFocusRaise
Key Tab 	A	S3	Prev [!iconic CurrentScreen *] Focus
Key Tab 	A	M	Function NextFocusRaise
Key Tab 	A	2M	Function NextFocusRaise
Key Tab		A	SM	Function PrevFocusRaise
Key Tab		A	2SM	Function PrevFocusRaise
Key grave	A	3	Next [!iconic CurrentScreen *] Focus
Key grave	A	M	Next [!iconic CurrentPage CurrentDesk *] Focus
Key grave	A	2M	Next [!iconic CurrentScreen *] Focus
Key grave	A	C	Exec source ~/.etc/zshrc ; fvxt
Key space	A	M	Popup "Window-Ops-Basic"  Window +0 +0
Key Return	A	CS	RaiseLower
Key Return	A	2M	RaiseLower
Key KP_Delete	A	CM	Exec exec ktop
Key Escape	A	M	WindowList Root 0 0 SelectOnRelease, DeskNum, CurrentDesk, Alphabetic
Key Escape	A	C	WindowList Root 0 0 
Key F4		A	M	Close_Destroy
Key t		A	C3	Module FvwmTaskBar 
Key t		A	S3	KillModule FvwmTaskBar
Key b		A	C3	Module FvwmButtons
Key b		A	S3	KillModule FvwmButtons
Key p		A	C3	Function SpecPageFunc
Key p		A	S3	KillModule SpecPager
Key s		A	C3	Function SpecTaskFunc
#Key s		A	S3	KillModule SpecTaskBar
Key F3		A	SM	Module FvwmForm TalkForm
Key F1		A	CS	Popup "Window-Ops"
Key F2		A	CS	WindowList
Key F3		A	CS	Popup "Hosts"
Key F4		A	CS	Popup "Applications"
Key F5		A	CS	Popup "Utilities"
Key F6		A	CS	Popup "Multimedia"
Key F7		A	CS	Move
Key F8		A	CS	Resize
Key Return	A	CS	WindowList 1 -1


Key Home	A	3	Desk 0 0
Key comma	A	3	Desk 0 0
Key End		A	3	Desk 0 1
Key period	A	3	Desk 0 1


Key BackSpace	A	CSM	Delete
Key KP_Decimal  A	CSM	Destroy
Key Escape	A	CSM	FvwmForm LogoutVerify
Key z		A	CSM	Popup "RootStart"
Key x		A	CSM	Function StartXterm
Key t		WT	CSM	Function Raise-and-Stick
Key p		A	CSM	Popup "Preferences"
Key w		A	CSM	Popup "Window-Ops"
Key g		A	CSM	Popup "Games"
Key r 		A	CSM	Refresh

AddToFunc "StartAmp"  "I" Exec exec mount /dev/cdrom
+ "I" Exec x11amp 
+ "I" Wait x11amp
+ "I" Exec x11amp -p
+ "I" Current  iconify

AddToFunc "StartMms"  "I" Exec exec mount /dev/cdrom
+ "I" Exec xmms 
+ "I" Wait xmms
+ "I" Exec xmms -p
+ "I" Current  iconify

# Do mouse movements with keys, so you never have to touch the mouse!
# Great if you don't have one, or are just switching between neighboring
# windows, or don't have far to go
#eg. KeyMouseMoves F9 F10 F11 F12 SM 1
#                  L  D   U   R   Mods Amount
#                  0   1   2   3   4    5

AddToFunc "KeyMouseMoves" 
+ "I" Key $0 A $4 CursorMove  -$5   0
+ "I" Key $1 A $4 CursorMove    0  $5
+ "I" Key $2 A $4 CursorMove    0 -$5
+ "I" Key $3 A $4 CursorMove   $5   0

#Let the FKeys do this, with vi-like bindings
# You may want to comment this out if you prefer
# emacs or other fvwm bindings for Ctrl-Shift &/or Shift-Meta + F9-F12
Function KeyMouseMoves F9 F10 F11 F12 CS 1
Function KeyMouseMoves F9 F10 F11 F12 SM 7

# May want to extend this for left-handed users

# These KeyMouseMoves are closer to where your hands'll be

#but VI people get two degrees of motion, if they
#dont mind moving off the home row temporarily
KeyMouseMoves j k i l CSM 10
#KeyMouseMoves y u i o CSM 1


# You may want to use xev to check these keycodes and use xmodmap
# to make sure the keysyms are what are below-- I don't
# have a win95 compatible keyboard to try this on

#Key Tab	A	CM	Next [CurrentScreen *xterm] focus
#Key F1  A	M	Next [ *kfm	] focus
#Key Tab	A	CM	Prev [ *lxlnxterm] focus
Key Tab		A	CSM	Prev [CurrentScreen *xterm] focus

AddToFunc StartKfm "I" Exec ka kfm
+ "I" Exec ekfms
+ "I" Exec kfm


# Creation of the menus



###########################################################################
#
# Now create the menus
#




# This is for the Start menu of the FvwmTaskBar


AddToMenu StartMenu "Start "  Title
+ "New &shell"     	Exec  exec xterm
+ "&Programs %mini-penguin.xpm%"		Popup AUTO_WM_CONFIG


# seek for big applications already on disk...





AddToMenu AUTO_WM_CONFIG "Programs %mini-penguin.xpm%" Title

# AUTOMATIC MENU generation - begin





# wmconfig --output=fvwm2 --rootmenu="AUTO_WM_CONFIG"  --no-icons


#
# Starting configuration for menu "AUTO_WM_CONFIG"
#
# Icon specification for Apps under "AUTO_WM_CONFIG"
# The menus...
AddToMenu	AUTO_WM_CONFIG	"Administration"	Popup	AUTO_WM_CONFIG.Administration
AddToMenu	AUTO_WM_CONFIG	"Amusements"	Popup	AUTO_WM_CONFIG.Amusements
AddToMenu	AUTO_WM_CONFIG	"Applications"	Popup	AUTO_WM_CONFIG.Applications
AddToMenu	AUTO_WM_CONFIG	"Games"	Popup	AUTO_WM_CONFIG.Games
AddToMenu	AUTO_WM_CONFIG	"Graphics"	Popup	AUTO_WM_CONFIG.Graphics
AddToMenu	AUTO_WM_CONFIG	"Internet"	Popup	AUTO_WM_CONFIG.Internet
AddToMenu	AUTO_WM_CONFIG	"Multimedia"	Popup	AUTO_WM_CONFIG.Multimedia
AddToMenu	AUTO_WM_CONFIG	"Networking"	Popup	AUTO_WM_CONFIG.Networking
AddToMenu	AUTO_WM_CONFIG	"System"	Popup	AUTO_WM_CONFIG.System
AddToMenu	AUTO_WM_CONFIG	"Utilities"	Popup	AUTO_WM_CONFIG.Utilities
# The following is added by package: Help system
AddToMenu	AUTO_WM_CONFIG	"Help system"	Exec	gnome-help-browser &

#
# Starting configuration for menu "Administration"
#
# Icon specification for Apps under "Administration"
Style	"Help Tool"	MiniIcon	mini-question.xpm
Style	"Kernel Configuration"	MiniIcon	mini-penguin.xpm
Style	"Network Configuration"	MiniIcon	mini-penguin.xpm
Style	"Text-mode tool menu"	MiniIcon	mini-penguin.xpm
# The menus...
AddToMenu	AUTO_WM_CONFIG.Administration	"Administration"	Title
# The following is added by package: helptool
AddToMenu	AUTO_WM_CONFIG.Administration	"Help Tool %mini-question.xpm%"	Exec	helptool &
# The following is added by package: kernelcfg
AddToMenu	AUTO_WM_CONFIG.Administration	"Kernel Configuration %mini-penguin.xpm%"	Exec	kernelcfg &
# The following is added by package: xload
AddToMenu	AUTO_WM_CONFIG.Administration	"Load Monitor"	Exec	xload &
# The following is added by package: netcfg
AddToMenu	AUTO_WM_CONFIG.Administration	"Network Configuration %mini-penguin.xpm%"	Exec	netcfg &
# The following is added by package: printtool
AddToMenu	AUTO_WM_CONFIG.Administration	"Printer Tool"	Exec	printtool &
# The following is added by package: setup
AddToMenu	AUTO_WM_CONFIG.Administration	"Text-mode tool menu %mini-penguin.xpm%"	Exec	xterm -e setup &
# The following is added by package: top
AddToMenu	AUTO_WM_CONFIG.Administration	"top"	Exec	xterm -e lxtop &

#
# Starting configuration for menu "Amusements"
#
# Icon specification for Apps under "Amusements"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Amusements	"Amusements"	Title
# The following is added by package: xeyes
AddToMenu	AUTO_WM_CONFIG.Amusements	"xeyes"	Exec	xeyes &
# The following is added by package: xscreensaver
AddToMenu	AUTO_WM_CONFIG.Amusements	"xscreensaver (1min timeout)"	Exec	xscreensaver -timeout 1 -cycle 1 &

#
# Starting configuration for menu "Applications"
#
# Icon specification for Apps under "Applications"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Applications	"Applications"	Title
# The following is added by package: Applications
AddToMenu	AUTO_WM_CONFIG.Applications	"Applications"	Exec	&
# The following is added by package: Calendar
AddToMenu	AUTO_WM_CONFIG.Applications	"Calendar"	Exec	gnomecal &
# The following is added by package: GHex
AddToMenu	AUTO_WM_CONFIG.Applications	"GHex"	Exec	ghex &
# The following is added by package: GnomeCard
AddToMenu	AUTO_WM_CONFIG.Applications	"GnomeCard"	Exec	gnomecard &
# The following is added by package: gnotepad+
AddToMenu	AUTO_WM_CONFIG.Applications	"gnotepad+"	Exec	gnp &
# The following is added by package: Gnumeric spreadsheet
AddToMenu	AUTO_WM_CONFIG.Applications	"Gnumeric spreadsheet"	Exec	gnumeric &
# The following is added by package: Time tracking tool
AddToMenu	AUTO_WM_CONFIG.Applications	"Time tracking tool"	Exec	gtt &

#
# Starting configuration for menu "Games"
#
# Icon specification for Apps under "Games"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Games	"Games"	Title
# The following is added by package: AisleRiot
AddToMenu	AUTO_WM_CONFIG.Games	"AisleRiot"	Exec	sol &
# The following is added by package: FreeCell
AddToMenu	AUTO_WM_CONFIG.Games	"FreeCell"	Exec	freecell &
# The following is added by package: Games
AddToMenu	AUTO_WM_CONFIG.Games	"Games"	Exec	&
# The following is added by package: Gnibbles
AddToMenu	AUTO_WM_CONFIG.Games	"Gnibbles"	Exec	gnibbles &
# The following is added by package: Gnobots
AddToMenu	AUTO_WM_CONFIG.Games	"Gnobots"	Exec	gnobots &
# The following is added by package: GnobotsII
AddToMenu	AUTO_WM_CONFIG.Games	"GnobotsII"	Exec	gnobots2 &
# The following is added by package: Gnome Mines
AddToMenu	AUTO_WM_CONFIG.Games	"Gnome Mines"	Exec	gnomine &
# The following is added by package: Gnome-Stones
AddToMenu	AUTO_WM_CONFIG.Games	"Gnome-Stones"	Exec	gnome-stones &
# The following is added by package: Gnotravex
AddToMenu	AUTO_WM_CONFIG.Games	"Gnotravex"	Exec	gnotravex &
# The following is added by package: GTali
AddToMenu	AUTO_WM_CONFIG.Games	"GTali"	Exec	gtali &
# The following is added by package: Iagno
AddToMenu	AUTO_WM_CONFIG.Games	"Iagno"	Exec	iagno &
# The following is added by package: Mahjongg
AddToMenu	AUTO_WM_CONFIG.Games	"Mahjongg"	Exec	mahjongg &
# The following is added by package: Same Gnome
AddToMenu	AUTO_WM_CONFIG.Games	"Same Gnome"	Exec	same-gnome &

#
# Starting configuration for menu "Graphics"
#
# Icon specification for Apps under "Graphics"
Style	"Ghostview"	MiniIcon	mini-gv.xpm
# The menus...
AddToMenu	AUTO_WM_CONFIG.Graphics	"Graphics"	Title
# The following is added by package: Electric Eyes
AddToMenu	AUTO_WM_CONFIG.Graphics	"Electric Eyes"	Exec	ee &
# The following is added by package: gv
AddToMenu	AUTO_WM_CONFIG.Graphics	"Ghostview %mini-gv.xpm%"	Exec	gv &
# The following is added by package: GQview
AddToMenu	AUTO_WM_CONFIG.Graphics	"GQview"	Exec	gqview &
# The following is added by package: Graphics
AddToMenu	AUTO_WM_CONFIG.Graphics	"Graphics"	Exec	&

#
# Starting configuration for menu "Internet"
#
# Icon specification for Apps under "Internet"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Internet	"Internet"	Title
# The following is added by package: gftp
AddToMenu	AUTO_WM_CONFIG.Internet	"gftp"	Exec	gftp &
# The following is added by package: Internet
AddToMenu	AUTO_WM_CONFIG.Internet	"Internet"	Exec	&
# The following is added by package: Netscape Communicator
AddToMenu	AUTO_WM_CONFIG.Internet	"Netscape Communicator"	Exec	/usr/bin/netscape-communicator &
# The following is added by package: xchat IRC client
AddToMenu	AUTO_WM_CONFIG.Internet	"xchat IRC client"	Exec	xchat &

#
# Starting configuration for menu "Multimedia"
#
# Icon specification for Apps under "Multimedia"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Multimedia	"Multimedia"	Title
# The following is added by package: Audio Mixer
AddToMenu	AUTO_WM_CONFIG.Multimedia	"Audio Mixer"	Exec	gmix &
# The following is added by package: CD Player
AddToMenu	AUTO_WM_CONFIG.Multimedia	"CD Player"	Exec	gtcd &
# The following is added by package: ESD Volume Meter
AddToMenu	AUTO_WM_CONFIG.Multimedia	"ESD Volume Meter"	Exec	vumeter &
# The following is added by package: Extace Waveform Display
AddToMenu	AUTO_WM_CONFIG.Multimedia	"Extace Waveform Display"	Exec	extace &
# The following is added by package: Multimedia
AddToMenu	AUTO_WM_CONFIG.Multimedia	"Multimedia"	Exec	&
# The following is added by package: X11amp
AddToMenu	AUTO_WM_CONFIG.Multimedia	"X11amp"	Exec	x11amp &

#
# Starting configuration for menu "Networking"
#
# Icon specification for Apps under "Networking"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Networking	"Networking"	Title
# The following is added by package: minicom
AddToMenu	AUTO_WM_CONFIG.Networking	"Minicom"	Exec	xterm -e minicom &
# The following is added by package: ncftp
AddToMenu	AUTO_WM_CONFIG.Networking	"ncftp"	Exec	xterm -e ncftp &
# The following is added by package: telnet
AddToMenu	AUTO_WM_CONFIG.Networking	"telnet"	Exec	xterm -e telnet &
# The following is added by package: usernet
AddToMenu	AUTO_WM_CONFIG.Networking	"Usernet"	Exec	usernet &

#
# Starting configuration for menu "System"
#
# Icon specification for Apps under "System"
# The menus...
AddToMenu	AUTO_WM_CONFIG.System	"System"	Title
# The following is added by package: About Myself
AddToMenu	AUTO_WM_CONFIG.System	"About Myself"	Exec	userinfo &
# The following is added by package: Change Password
AddToMenu	AUTO_WM_CONFIG.System	"Change Password"	Exec	userpasswd &
# The following is added by package: Control Panel
AddToMenu	AUTO_WM_CONFIG.System	"Control Panel"	Exec	control-panel &
# The following is added by package: Disk Management
AddToMenu	AUTO_WM_CONFIG.System	"Disk Management"	Exec	usermount &
# The following is added by package: GnoRPM
AddToMenu	AUTO_WM_CONFIG.System	"GnoRPM"	Exec	gnorpm &
# The following is added by package: LinuxConf
AddToMenu	AUTO_WM_CONFIG.System	"LinuxConf"	Exec	linuxconf &
# The following is added by package: System
AddToMenu	AUTO_WM_CONFIG.System	"System"	Exec	&
# The following is added by package: Time Tool
AddToMenu	AUTO_WM_CONFIG.System	"Time Tool"	Exec	timetool &

#
# Starting configuration for menu "Utilities"
#
# Icon specification for Apps under "Utilities"
Style	"Rxvt"	MiniIcon	mini-sh1.xpm
# The menus...
AddToMenu	AUTO_WM_CONFIG.Utilities	"Utilities"	Title
AddToMenu	AUTO_WM_CONFIG.Utilities	"Mail"	Popup	AUTO_WM_CONFIG.Utilities.Mail
AddToMenu	AUTO_WM_CONFIG.Utilities	"Misc"	Popup	AUTO_WM_CONFIG.Utilities.Misc
AddToMenu	AUTO_WM_CONFIG.Utilities	"Sound"	Popup	AUTO_WM_CONFIG.Utilities.Sound
# The following is added by package: xcalc
AddToMenu	AUTO_WM_CONFIG.Utilities	"Calculator"	Exec	xcalc &
# The following is added by package: Color Browser...
AddToMenu	AUTO_WM_CONFIG.Utilities	"Color Browser..."	Exec	gcolorsel &
# The following is added by package: xfontsel
AddToMenu	AUTO_WM_CONFIG.Utilities	"Font Selector"	Exec	xfontsel &
# The following is added by package: Font Selector...
AddToMenu	AUTO_WM_CONFIG.Utilities	"Font Selector..."	Exec	gfontsel &
# The following is added by package: GNOME DiskFree
AddToMenu	AUTO_WM_CONFIG.Utilities	"GNOME DiskFree"	Exec	gdiskfree &
# The following is added by package: GNOME Search Tool
AddToMenu	AUTO_WM_CONFIG.Utilities	"GNOME Search Tool"	Exec	gsearchtool &
# The following is added by package: GNOME terminal
AddToMenu	AUTO_WM_CONFIG.Utilities	"GNOME terminal"	Exec	gnome-terminal &
# The following is added by package: rxvt
AddToMenu	AUTO_WM_CONFIG.Utilities	"Rxvt %mini-sh1.xpm%"	Exec	rxvt &
# The following is added by package: Simple Calculator
AddToMenu	AUTO_WM_CONFIG.Utilities	"Simple Calculator"	Exec	gcalc &
# The following is added by package: Stripchart Plotter
AddToMenu	AUTO_WM_CONFIG.Utilities	"Stripchart Plotter"	Exec	gstripchart &
# The following is added by package: System Info...
AddToMenu	AUTO_WM_CONFIG.Utilities	"System Info..."	Exec	guname &
# The following is added by package: System monitor
AddToMenu	AUTO_WM_CONFIG.Utilities	"System monitor"	Exec	gtop &
# The following is added by package: Text File Viewer
AddToMenu	AUTO_WM_CONFIG.Utilities	"Text File Viewer"	Exec	gless --nostdin &
# The following is added by package: User Listing
AddToMenu	AUTO_WM_CONFIG.Utilities	"User Listing"	Exec	gw &
# The following is added by package: Utilities
AddToMenu	AUTO_WM_CONFIG.Utilities	"Utilities"	Exec	&
# The following is added by package: xditview
AddToMenu	AUTO_WM_CONFIG.Utilities	"xditview"	Exec	xditview &
# The following is added by package: xedit
AddToMenu	AUTO_WM_CONFIG.Utilities	"xedit"	Exec	xedit &
# The following is added by package: xfm
AddToMenu	AUTO_WM_CONFIG.Utilities	"xfm"	Exec	xfm &
# The following is added by package: xman
AddToMenu	AUTO_WM_CONFIG.Utilities	"xman"	Exec	xman &
# The following is added by package: xrn
AddToMenu	AUTO_WM_CONFIG.Utilities	"xrn"	Exec	xrn &

#
# Starting configuration for menu "Mail"
#
# Icon specification for Apps under "Mail"
Style	"Elm"	MiniIcon	mini-mail.xpm
Style	"Mutt"	MiniIcon	mini-mail.xpm
# The menus...
AddToMenu	AUTO_WM_CONFIG.Utilities.Mail	"Mail"	Title
# The following is added by package: elm
AddToMenu	AUTO_WM_CONFIG.Utilities.Mail	"Elm %mini-mail.xpm%"	Exec	xterm -e elm
# The following is added by package: exmh
AddToMenu	AUTO_WM_CONFIG.Utilities.Mail	"exmh"	Exec	exmh &
# The following is added by package: mutt
AddToMenu	AUTO_WM_CONFIG.Utilities.Mail	"Mutt %mini-mail.xpm%"	Exec	xterm -e mutt
# The following is added by package: pine
AddToMenu	AUTO_WM_CONFIG.Utilities.Mail	"pine"	Exec	xterm -e pine &
# The following is added by package: xmailbox
AddToMenu	AUTO_WM_CONFIG.Utilities.Mail	"xmailbox"	Exec	xmailbox &

#
# Starting configuration for menu "Misc"
#
# Icon specification for Apps under "Misc"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Utilities.Misc	"Misc"	Title
# The following is added by package: ical
AddToMenu	AUTO_WM_CONFIG.Utilities.Misc	"Ical"	Exec	ical
# The following is added by package: info
AddToMenu	AUTO_WM_CONFIG.Utilities.Misc	"info"	Exec	xterm -e info &
# The following is added by package: lynx
AddToMenu	AUTO_WM_CONFIG.Utilities.Misc	"lynx"	Exec	xterm -e lynx &
# The following is added by package: slrn
AddToMenu	AUTO_WM_CONFIG.Utilities.Misc	"slrn"	Exec	xterm -e slrn &
# The following is added by package: trn
AddToMenu	AUTO_WM_CONFIG.Utilities.Misc	"trn"	Exec	xterm -e trn &

#
# Starting configuration for menu "Sound"
#
# Icon specification for Apps under "Sound"
# The menus...
AddToMenu	AUTO_WM_CONFIG.Utilities.Sound	"Sound"	Title
# The following is added by package: xmixer
AddToMenu	AUTO_WM_CONFIG.Utilities.Sound	"XMixer"	Exec	xmixer &
# The following is added by package: xplaycd
AddToMenu	AUTO_WM_CONFIG.Utilities.Sound	"XPlayCD"	Exec	xplaycd &

# AUTOMATIC MENU generation - end

AddToMenu StartMenu
+ "" Nop



+ "System &Utilities %mini-connect.xpm%"	Popup SystemUtilities
+ "P&references %mini-prefs.xpm%" 		Popup "Preferences"
+ "&Window Ops "	Popup Window-Ops
+ "" Nop







*XLockWarpPointer
*XLockFont 		fixed
*XLockButtonFont 	*helvetica-bold-r-*-120-*
*XLockInputFont 	fixed
*XLockFore 		black
*XLockBack 		grey69
*XLockItemFore 		black
*XLockItemBack 		grey47
*XLockLine 		center
*XLockText 		"Available Xlock modes"
*XLockSelection		Option single
*XLockLine 		expand


# We add the random thing separately, to be sure that we have at least one selected
# option
*XLockChoice    Option random 	on 	"Random    "

*XLockLine	expand
*XLockLine	expand
*XLockLine	expand

*XLockButton	quit "Screen Saver"
*XLockCommand	Exec xlock -nice -16 -mode $(Option) -nolock
# somebody is gonna hate me for this one... :-)
#*XLockButton	quit "Background"
#*XLockCommand	Exec xlock -nice NICELEVEL -mode $(Option) -nolock -inroot
*XLockButton	quit "Lock Screen"
*XLockCommand	Exec xlock -nice -16 -mode $(Option)
*XLockButton	quit "Cancel" ^[
*XLockCommand	Nop


AddToMenu StartMenu
+ "Sreen Saver %mini-lock.xpm%"	FvwmForm XLock
+ "" Nop



AddToMenu StartMenu
+ "A&bout Fvwm %mini-exclam.xpm%"		FvwmForm About
+ "" Nop

#ifdef(`BROWSER_HELP_COMMAND',`
#AddToMenu StartMenu "&Help Fvwm MiniTitleIcon(question)" Exec BROWSER_HELP_COMMAND
#+ "" Nop
#')

AddToMenu StartMenu "E&xit %mini-stop.xpm%"	Popup Quit-Verify

AddToMenu "Quit-Verify" "Really Quit Fvwm?" Title
+ "&Restart %mini-turn.xpm%"		Restart fvwm
+ ""						Nop
+ "&Switch To... "	Popup WindowManagers
+ ""						Nop
+ "&Yes, Really Quit %mini-exclam.xpm%"	Quit
+""
#+ "Re&boot %mini-question.xpm%" 	Exec reboot
#+ "Sh&utdown %mini-cross.xpm%" 		Exec halt


AddToMenu ScrollPreferences "Change Scroll Behavior" Title
+ "&On"    		EdgeScroll 100 100
+ "O&ff"   		EdgeScroll 0 0
+ "&Horizontal Only"    EdgeScroll 100 0
+ "&Vertical Only"      EdgeScroll 0 100
+ "&Partial"        	EdgeScroll 50 50

AddToMenu Colours  "Color Settings" Title
+ "Color &Map"       Exec xcmap
+ "&Reset Color Map" Exec xstdcmap -default


AddToFunc SetRootCursor "I" Exec xsetroot -cursor_name $0


AddToMenu RootCursor "Set Root Cursor" Title
+ "&Reset to X_cursor" SetRootCursor X_cursor
+ "&Miscellany" Popup MiscellanyCursors
+ "&Arrows" Popup ArrowCursors
+ "Cor&ners" Popup CornersCursors
+ "&Crosses" Popup CrossesCursors
+ "&Objects" Popup ObjectsCursors

#AddCursorToMenu($1=Menu,$2=Cursor)



AddToMenu ArrowCursors "arrow" SetRootCursor arrow
AddToMenu ArrowCursors "based_arrow_down" SetRootCursor based_arrow_down
AddToMenu ArrowCursors "based_arrow_up" SetRootCursor based_arrow_up
AddToMenu ArrowCursors "double_arrow" SetRootCursor double_arrow
AddToMenu ArrowCursors "question_arrow" SetRootCursor question_arrow
AddToMenu ArrowCursors "sb_down_arrow" SetRootCursor sb_down_arrow
AddToMenu ArrowCursors "sb_h_double_arrow" SetRootCursor sb_h_double_arrow
AddToMenu ArrowCursors "sb_left_arrow" SetRootCursor sb_left_arrow
AddToMenu ArrowCursors "sb_right_arrow" SetRootCursor sb_right_arrow
AddToMenu ArrowCursors "sb_up_arrow" SetRootCursor sb_up_arrow
AddToMenu ArrowCursors "sb_v_double_arrow" SetRootCursor sb_v_double_arrow
AddToMenu ArrowCursors "top_left_arrow" SetRootCursor top_left_arrow


AddToMenu CornersCursors "top_left_corner" SetRootCursor top_left_corner
AddToMenu CornersCursors "top_side" SetRootCursor top_side
AddToMenu CornersCursors "top_right_corner" SetRootCursor top_right_corner
AddToMenu CornersCursors "left_side" SetRootCursor left_side
AddToMenu CornersCursors "right_side" SetRootCursor right_side
AddToMenu CornersCursors "bottom_left_corner" SetRootCursor bottom_left_corner
AddToMenu CornersCursors "bottom_side" SetRootCursor bottom_side
AddToMenu CornersCursors "bottom_right_corner" SetRootCursor bottom_right_corner


AddToMenu CrossesCursors "X_cursor" SetRootCursor X_cursor
AddToMenu CrossesCursors "cross" SetRootCursor cross
AddToMenu CrossesCursors "cross_reverse" SetRootCursor cross_reverse
AddToMenu CrossesCursors "crosshair" SetRootCursor crosshair
AddToMenu CrossesCursors "diamond_cross" SetRootCursor diamond_cross
AddToMenu CrossesCursors "iron_cross" SetRootCursor iron_cross
AddToMenu CrossesCursors "tcross" SetRootCursor tcross


AddToMenu ObjectsCursors "boat" SetRootCursor boat
AddToMenu ObjectsCursors "bogosity" SetRootCursor bogosity
AddToMenu ObjectsCursors "box_spiral" SetRootCursor box_spiral
AddToMenu ObjectsCursors "circle" SetRootCursor circle
AddToMenu ObjectsCursors "clock" SetRootCursor clock
AddToMenu ObjectsCursors "coffee_mug" SetRootCursor coffee_mug
AddToMenu ObjectsCursors "dot" SetRootCursor dot
AddToMenu ObjectsCursors "dotbox" SetRootCursor dotbox
AddToMenu ObjectsCursors "draft_large" SetRootCursor draft_large
AddToMenu ObjectsCursors "draft_small" SetRootCursor draft_small
AddToMenu ObjectsCursors "draped_box" SetRootCursor draped_box
AddToMenu ObjectsCursors "exchange" SetRootCursor exchange
AddToMenu ObjectsCursors "fleur" SetRootCursor fleur
AddToMenu ObjectsCursors "gobbler" SetRootCursor gobbler
AddToMenu ObjectsCursors "gumby" SetRootCursor gumby
AddToMenu ObjectsCursors "hand1" SetRootCursor hand1
AddToMenu ObjectsCursors "hand2" SetRootCursor hand2
AddToMenu ObjectsCursors "heart" SetRootCursor heart
AddToMenu ObjectsCursors "icon" SetRootCursor icon
AddToMenu ObjectsCursors "man" SetRootCursor man
AddToMenu ObjectsCursors "mouse" SetRootCursor mouse
AddToMenu ObjectsCursors "num_glyphs" SetRootCursor num_glyphs
AddToMenu ObjectsCursors "pencil" SetRootCursor pencil
AddToMenu ObjectsCursors "pirate" SetRootCursor pirate
AddToMenu ObjectsCursors "plus" SetRootCursor plus
AddToMenu ObjectsCursors "rtl_logo" SetRootCursor rtl_logo
AddToMenu ObjectsCursors "sailboat" SetRootCursor sailboat
AddToMenu ObjectsCursors "shuttle" SetRootCursor shuttle
AddToMenu ObjectsCursors "spider" SetRootCursor spider
AddToMenu ObjectsCursors "spraycan" SetRootCursor spraycan
AddToMenu ObjectsCursors "star" SetRootCursor star
AddToMenu ObjectsCursors "target" SetRootCursor target
AddToMenu ObjectsCursors "trek" SetRootCursor trek
AddToMenu ObjectsCursors "umbrella" SetRootCursor umbrella
AddToMenu ObjectsCursors "watch" SetRootCursor watch


AddToMenu TeesButtonsAnglesCursors "bottom_tee" SetRootCursor bottom_tee
AddToMenu TeesButtonsAnglesCursors "left_tee" SetRootCursor left_tee
AddToMenu TeesButtonsAnglesCursors "leftbutton" SetRootCursor leftbutton
AddToMenu TeesButtonsAnglesCursors "ll_angle" SetRootCursor ll_angle
AddToMenu TeesButtonsAnglesCursors "lr_angle" SetRootCursor lr_angle
AddToMenu TeesButtonsAnglesCursors "ul_angle" SetRootCursor ul_angle
AddToMenu TeesButtonsAnglesCursors "ur_angle" SetRootCursor ur_angle
AddToMenu TeesButtonsAnglesCursors "middlebutton" SetRootCursor middlebutton
AddToMenu TeesButtonsAnglesCursors "right_tee" SetRootCursor right_tee
AddToMenu TeesButtonsAnglesCursors "rightbutton" SetRootCursor rightbutton
AddToMenu TeesButtonsAnglesCursors "top_tee" SetRootCursor top_tee


AddToMenu MiscellanyCursors "center_ptr" SetRootCursor center_ptr
AddToMenu MiscellanyCursors "left_ptr" SetRootCursor left_ptr
AddToMenu MiscellanyCursors "right_ptr" SetRootCursor right_ptr
AddToMenu MiscellanyCursors "sizing" SetRootCursor sizing
AddToMenu MiscellanyCursors "xterm" SetRootCursor xterm



AddToFunc Speed "I" Exec xset m $0

AddToMenu MouseSettings "Mouse Settings" Title
+ "&1 Ultra Fastest" 	Speed "20 5"
+ "&2 Next Fastest"  	Speed "15 6"
+ "&3 Faster"	  	Speed "10 7"
+ "&4 Fast"	  	Speed "7 8"
+ "&5 Normal"	  	Speed "5 10"
+ "&6 Slower"	  	Speed "default"
+ "&N No acceleration"  Speed "0 10000"
+ "" Nop
+ "&Right Handed (1 2 3)" Exec xmodmap -e "pointer = 1 2 3"
+ "L&eft Handed (3 2 1)" Exec xmodmap -e "pointer = 3 2 1"

AddToFunc "Start"
+ "I" CursorMove -100 100
+ "I" Popup StartMenu



AddToFunc AutoRaiseSpeed 
+ "I" KillModule FvwmAuto
+ "I" FvwmAuto $0



AddToMenu AutoRaiseMenu "AutoRaise" Title
+ "O&ff" KillModule FvwmAuto
+ "" Nop

+ "&0 ms" Function AutoRaiseSpeed 0
+ "&200 ms" Function AutoRaiseSpeed 200
+ "&400 ms" Function AutoRaiseSpeed 400
+ "&500 ms" Function AutoRaiseSpeed 500
+ "&1000 ms" Function AutoRaiseSpeed 1000
+ "&3000 ms" Function AutoRaiseSpeed 3000


AddToMenu AudioSettings "Audio Settings" Title
+ "&On" Module FvwmAudio
+ "O&ff" KillModule FvwmAudio
#+ "Event mapping" FvwmForm AudioEvents

AddToMenu XResources "X Resources" Title
+ "Lo&ad .Xdefaults" Exec xrdb -load .Xdefaults
+ "&Merge .Xdefaults" Exec xrdb -merge .Xdefaults
+ "L&oad .Xresources" Exec xrdb -load .Xresources
+ "M&erge .Xresources" Exec xrdb -merge .Xresources
+ "" Nop
+ "&Display current settings" Exec rxvt -T XResources\ --\ Use\ Meta-PgUp/Dn\ to\ Scroll -n XResources  -e sh -c "xrdb -query && read waitforreturn"




AddToFunc Wharf
+ "I"	KillModule FvwmButtons
+ "I"	KillModule FvwmPager
+ "I"	Module FvwmWharf



*PreferencesButtonsRows 1
*PreferencesButtonsFore black
*PreferencesButtonsBack grey47
AddToMenu Preferences "Preferences Button Bar%mini-modules.xpm%" Module FvwmButtons PreferencesButtons


#At least give it a title

AddToMenu Preferences "" Nop



# MenuAndButtonEntryLM(&FvwmConfig,hammer,hammer_3d,Module FvwmConfig)

AddToMenu Preferences "&Background%mini-colors.xpm%" FvwmScript ScreenSetup

*PreferencesButtons(Icon mini-colors.xpm, Action "FvwmScript ScreenSetup")



AddToMenu Preferences "&Root Cursor%mini-cross.xpm%" Popup RootCursor

*PreferencesButtons(Icon mini-cross.xpm, Action "Popup RootCursor")



AddToMenu Preferences "&Mouse" Popup MouseSettings

*PreferencesButtons(Icon mini-mouse.xpm, Action "Popup MouseSettings")



AddToMenu Preferences "&Colours%mini-colors.xpm%" Popup Colours

*PreferencesButtons(Icon mini-colors.xpm, Action "Popup Colours")


# MenuAndButtonEntryLM(A&udio,audiovol,Multimedia3,Popup AudioSettings)

AddToMenu Preferences "&AutoRaise%mini-raise.xpm%" Popup AutoRaiseMenu

*PreferencesButtons(Icon mini-raise.xpm, Action "Popup AutoRaiseMenu")



AddToMenu Preferences "&Scroll Setup%mini-scroll-arrows.xpm%" Popup ScrollPreferences

*PreferencesButtons(Icon mini-scroll-arrows.xpm, Action "Popup ScrollPreferences")



AddToMenu Preferences "&X Resources%mini-x2.xpm%" Popup XResources

*PreferencesButtons(Icon mini-x2.xpm, Action "Popup XResources")


#xkeycaps not found

AddToMenu Preferences "Save &Desktop to new.xinitrc%mini-floppy.xpm%" Module FvwmSave

*PreferencesButtons(Icon mini-floppy.xpm, Action "Module FvwmSave")





*SystemUtilitiesButtonsRows 1
*SystemUtilitiesButtonsFore black
*SystemUtilitiesButtonsBack grey47
AddToMenu SystemUtilities "SystemUtilities Button Bar%mini-modules.xpm%" Module FvwmButtons SystemUtilitiesButtons


#At least give it a title

AddToMenu SystemUtilities "" Nop



#FIX: not quite right
#ifelse(eval(5 != 5),`0',errprint(`Not 5 args to DefineProgram for xterm -T Root_Window -n Root_Window  -e sh -c su 
#'))dnl
Style "xterm" MiniIcon mini-shadowman-64.xpm
AddToMenu SystemUtilities "&Root shell%mini-shadowman-64.xpm%" Exec xterm -T Root_Window -n Root_Window  -e sh -c su 

*SystemUtilitiesButtons(Icon mini-shadowman-64.xpm, Action 'Exec "xterm" xterm -T Root_Window -n Root_Window  -e sh -c su ')






AddToMenu SystemUtilities "T&op%mini-run.xpm%" Exec rxvt -name top -T Top -n Top -e lxtop

*SystemUtilitiesButtons(Icon mini-run.xpm, Action "Exec rxvt -name top -T Top -n Top -e lxtop")



AddToMenu SystemUtilities "I&dentify Window%mini-question.xpm%" Module FvwmIdent

*SystemUtilitiesButtons(Icon mini-question.xpm, Action "Module FvwmIdent")



#FIX: not quite right
#ifelse(eval(5 != 5),`0',errprint(`Not 5 args to DefineProgram for rxvt -T WindowInfo\ --\ Use\ Meta-PgUp/Dn\ to\ Scroll -n WindowInfo  -e sh -c "xwininfo && read waitforreturn"
#'))dnl
Style "rxvt" MiniIcon mini-windows.xpm
AddToMenu SystemUtilities "&Window Info" Exec rxvt -T WindowInfo\ --\ Use\ Meta-PgUp/Dn\ to\ Scroll -n WindowInfo  -e sh -c "xwininfo && read waitforreturn"

*SystemUtilitiesButtons(Icon mini-windows.xpm, Action 'Exec "rxvt" rxvt -T WindowInfo\ --\ Use\ Meta-PgUp/Dn\ to\ Scroll -n WindowInfo  -e sh -c "xwininfo && read waitforreturn"')





AddToMenu SystemUtilities "&Talk Module" Module FvwmTalk

*SystemUtilitiesButtons(Icon mini-talk.xpm, Action "Module FvwmTalk")



#AddToMenu SystemUtilities "&Pager Module%mini-pager.xpm%" Module FvwmPager 0 1

#*SystemUtilitiesButtons(Icon mini-pager.xpm, Action "Module FvwmPager 0 1")

AddToMenu SystemUtilities "&Pager Module%mini-pager.xpm%" Module FvwmButtons
*SystemUtilitiesButtons(Icon mini-pager.xpm, Action "Module FvwmButtons")



AddToMenu SystemUtilities "Task &Bar%mini-exp.xpm%" Module FvwmTaskBar

*SystemUtilitiesButtons(Icon mini-exp.xpm, Action "Module FvwmTaskBar")




AddToMenu SystemUtilities "W&harf%mini-pager.xpm%" Function Wharf

*SystemUtilitiesButtons(Icon mini-pager.xpm, Action "Function Wharf")


AddToMenu SystemUtilities "" Nop

AddToMenu SystemUtilities "Fvwm &Command"  FvwmForm TalkForm

*SystemUtilitiesButtons(Icon mini-talk.xpm, Action "FvwmForm TalkForm")



AddToMenu SystemUtilities "Reread .&Xdefaults%mini-exclam.xpm%" Exec xrdb -merge .Xdefaults

*SystemUtilitiesButtons(Icon mini-exclam.xpm, Action "Exec xrdb -merge .Xdefaults")



AddToMenu SystemUtilities "Restart &Fvwm2%mini-turn.xpm%" Restart fvwm2

*SystemUtilitiesButtons(Icon mini-turn.xpm, Action "Restart fvwm2")


AddToMenu SystemUtilities "" Nop

AddToMenu SystemUtilities "Re&fresh Screen%mini-ray.xpm%" Refresh

*SystemUtilitiesButtons(Icon mini-ray.xpm, Action "Refresh")



AddToMenu SystemUtilities "Re&capture All Windows"  Recapture

*SystemUtilitiesButtons(Icon mini-recapture.xpm, Action "Recapture")





#
# This defines the most common window operations
#


*Window-OpsButtonsRows 1
*Window-OpsButtonsFore black
*Window-OpsButtonsBack grey47
AddToMenu Window-Ops "Window-Ops Button Bar%mini-modules.xpm%" Module FvwmButtons Window-OpsButtons


#At least give it a title

AddToMenu Window-Ops "" Nop


# Shortcuts: MRAL H/I SXTW DCK BPWUL F

AddToMenu Window-Ops "&Move%mini-move.xpm%" Function Move-and-Raise

*Window-OpsButtons(Icon mini-move.xpm, Action "Function Move-and-Raise")



AddToMenu Window-Ops "&Resize" Function Resize-or-Raise

*Window-OpsButtons(Icon mini-resize.xpm, Action "Function Resize-or-Raise")



AddToMenu Window-Ops "R&aise%mini-raise.xpm%" Raise

*Window-OpsButtons(Icon mini-raise.xpm, Action "Raise")



AddToMenu Window-Ops "L&ower%mini-lower.xpm%" Lower

*Window-OpsButtons(Icon mini-lower.xpm, Action "Lower")



AddToMenu Window-Ops "&Hide/Restore%mini-iconify.xpm%" Iconify

*Window-OpsButtons(Icon mini-iconify.xpm, Action "Iconify")



AddToMenu Window-Ops "&Stick/Unstick%mini-stick.xpm%" Stick

*Window-OpsButtons(Icon mini-stick.xpm, Action "Stick")



AddToMenu Window-Ops "Ma&ximize/Reset" Maximize 100 100





AddToMenu Window-Ops "Maximize &Tall/Reset%mini-maxtall.xpm%" Maximize 0 100

*Window-OpsButtons(Icon mini-maxtall.xpm, Action "Maximize 0 100")



AddToMenu Window-Ops "Maximize &Wide/Reset%mini-maxwide.xpm%" Maximize 100 0

*Window-OpsButtons(Icon mini-maxwide.xpm, Action "Maximize 100 0")


AddToMenu Window-Ops "" Nop

AddToMenu Window-Ops "Cascad&e" FvwmCascade -resize 0 0 80 60





AddToMenu Window-Ops "Tile Hori&zontal" FvwmTile -h 0 0 99 90





AddToMenu Window-Ops "Tile &Vertical" FvwmTile 0 0 99 90




AddToMenu Window-Ops "" Nop

AddToMenu Window-Ops "&Delete" Delete





AddToMenu Window-Ops "&Close%mini-cross.xpm%" Close

*Window-OpsButtons(Icon mini-cross.xpm, Action "Close")



AddToMenu Window-Ops "&Blast%mini-bomb.xpm%" Destroy

*Window-OpsButtons(Icon mini-bomb.xpm, Action "Destroy")


AddToMenu Window-Ops "" Nop

AddToMenu Window-Ops "ScrollBar%mini-scroll-arrows.xpm%" Module FvwmScroll 2 2

*Window-OpsButtons(Icon mini-scroll-arrows.xpm, Action "Module FvwmScroll 2 2")


#xvwd not found

AddToMenu Window-Ops "Ca&pture Windows" FvwmForm Capture





AddToMenu Window-Ops "Clean-&Up Module%mini-pencil.xpm%" Module FvwmClean

*Window-OpsButtons(Icon mini-pencil.xpm, Action "Module FvwmClean")



AddToMenu Window-Ops "Window &List Module" Module FvwmWinList

*Window-OpsButtons(Icon mini-windows.xpm, Action "Module FvwmWinList")


AddToMenu Window-Ops "" Nop

AddToMenu Window-Ops "Switch to..." WindowList





AddToMenu Window-Ops "Re&fresh Screen%mini-ray.xpm%" Refresh

*Window-OpsButtons(Icon mini-ray.xpm, Action "Refresh")



# Shortcuts: H/I MSNXCK 
AddToMenu "Window-Ops-Basic" 
+ "Ma&ximize"       		Maximize 100 100
+ "&Move %mini-move.xpm%"	Move-or-Raise
+ "&size %mini-resize.xpm%" Resize-or-Raise
+ "Mi&nimize %mini-iconify.xpm%" Iconify 1
+ "Stick/Un&z%mini-stick.xpm%" Stick
+ "Special &Resize"         SpecResize
+ ""                		Nop
+ "&Close %mini-cross.xpm%"	Close_Destroy
+ "&Destroy %mini-bomb.xpm%" 	Destroy

AddToFunc Close_Destroy "I" Close


# .fvwm2rc.hostmenus sets up the various menus for working w/ hosts

###############################################################
# This file is m4-included by .fvwm2rc.menus, which was in turn
# included by .fvwm2rc.m4










# Continues the "Hosts" menu from above.










# These commands should command before any menus or functions are defined,
# and before the internal pager is started.



#Remember, options are additive-- they get "OR"-ed together
# Also note that some of these are "FvwmAlias"-es... see m4 macro
# FvwmAlias and the following lines, above.
Style "Fvwm*"		NoTitle,NoHandles,BorderWidth 1
Style "*clock"		StaysOnTop,Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1
Style "xosview"		StaysOnTop,Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1
Style "xbiff"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
Style "xlassie"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
#Style "Gnuplot"			WindowListSkip, StaysOnTop, Sticky, CirculateSkip, BorderWidth 1, NeverFocus, Lenience
Style "Gnuplot"			WindowListSkip, StaysOnTop, Sticky, CirculateSkip, BorderWidth 1, !FPGrabFocus, Lenience
Style "xbchat"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
Style "xblchat"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
Style "xbchat_once_only"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
Style "xbnet"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
Style "xbclient"		WindowListSkip, StaysOnTop, Sticky, CirculateSkip, NoTitle,NoHandles,BorderWidth 1, NeverFocus, Lenience
Style "xeyes"		NoTitle,NoHandles,BorderWidth 1, CirculateSkip
Style "xload"		StaysOnTop,Sticky, NoTitle,NoHandles,BorderWidth 1, CirculateSkip
#Style "ppp"		StaysOnTop,Sticky, NoTitle,NoHandles,BorderWidth 1, CirculateSkip
Style "Msgs"		CirculateSkip
Style "*onsole"		CirculateSkip
Style "xman"		NoTitle,NoHandles,BorderWidth 1, CirculateSkip



# Module settings



###########################################################################
##### FvwmButtons
*FvwmButtonsFore grey55
*FvwmButtonsBack grey34
#*FvwmButtonsFont BASIC_FONT-bold-r-*-*-10-*-*-*-*-*-*-*
*FvwmButtonsFont None

*FvwmButtonsGeometry 75x14-0-0
# Layout: specify rows or columns, not both
*FvwmButtonsRows 1
# Define the buttons to use.....
*fvwmbuttons(4x1,Swallow "FvwmPager" "FvwmPager -2 +2" )
#*FvwmButtons(Swallow "XLoad" 'Exec nice -16 xload -bg black -fg grey48 -hl grey49 -nolabel -update 5')

Style "FvwmButtons"	StaysOnTop, WindowListSkip,CirculateSkip, NoTitle,NoHandles,BorderWidth 0, Sticky, !FPGrabFocus

###########################################################################
##### Pager - each module configures the pager in it's own way
Style "FvwmPager"	StaysOnTop,WindowListSkip,CirculateSkip, NoTitle,NoHandles,BorderWidth 0, Sticky
*FvwmPagerFont -*helvetica-bold-r-*-*-10-*-*-*-*-*-*-*
*FvwmPagerGeometry +0+0
*FvwmPagerDeskTopScale 40
*FvwmPagerLabel -2 Dusk
*FvwmPagerLabel -1 Dusk
*FvwmPagerLabel 0 Dusk
*FvwmPagerLabel 1 Apps
*FvwmPagerLabel 2 Other
*FvwmPagerLabel 3 3
*FvwmPagerLabel 4 4
*FvwmPagerSmallFont 5x8
#*FvwmPagerBack #908090
#*FvwmPagerFore #484048
*FvwmPagerHilight #027071
*FvwmPagerBack black
*FvwmPagerFore grey75
*FvwmPagerWindowColors grey55 #700000 grey79 #008000
*FvwmPagerBalloons
*FvwmPagerBalloonBack	bisque
*FvwmPagerBalloonFore	black



###########################################################################
##### Pager - each module configures the pager in it's own way
Style "SpecPager"	StaysOnTop,WindowListSkip,CirculateSkip, NoTitle,NoHandles,BorderWidth 0, Sticky
*SpecPagerFont -*helvetica-bold-r-*-*-10-*-*-*-*-*-*-*
*SpecPagerGeometry 150x50-0-30
*SpecPagerDeskTopScale 40
*SpecPagerLabel 0 Desk
*SpecPagerLabel 1 Apps
*SpecPagerLabel 2 2
*SpecPagerLabel 3 3
*SpecPagerSmallFont 5x8
#*SpecPagerBack #908090
#*SpecPagerFore #484048
*SpecPagerHilight grey41
*SpecPagerBack black
*SpecPagerFore grey74
*SpecPagerWindowColors grey54 #700000 grey81 #008000
*SpecPagerBalloons
*SpecPagerBalloonBack	bisque
*SpecPagerBalloonFore	black

###########################################################################
###### PowerButtons (a button bar at the bottom of the screen)
*PowerButtonsRows 1
*PowerButtonsGeometry -150-30
*PowerButtonsFore black
*PowerButtonsBack grey46
*PowerButtons(Icon mini-display.xpm, Action 'Exec "xterm" xterm')
*PowerButtons(Icon mini-book1.xpm, Action 'Exec "xman" xman')
Style "PowerButtons" StaysOnTop,Sticky, NoTitle,NoHandles,BorderWidth 0

###########################################################################
##### NoClutter
*FvwmNoClutter 3600 Iconify 1
*FvwmNoClutter 86400 Delete
*FvwmNoCLutter 172800 Destroy

###########################################################################
##### Identify
*FvwmIdentBack #00000
*FvwmIdentFore grey
*FvwmIdentFont -*helvetica-medium-r-*-*-12-*-*-*-*-*-*-*

###########################################################################
##### FvwmWinList
*FvwmWinListBack black
*FvwmWinListFore grey65
*FvwmWinListFont -*helvetica-bold-r-*-*-10-*-*-*-*-*-*-*
*FvwmWinListAction Click1 Iconify -1,Focus
*FvwmWinListAction Click2 Iconify
*FvwmWinListAction Click3 Module "FvwmIdent" FvwmIdent
*FvwmWinListUseSkipList
*FvwmWinListGeometry +0-1
        
###########################################################################
##### FvwmTaskBar
Style "FvwmTaskBar"	BorderWidth 0, NoHandles,
Style "FvwmTaskBar"	StaysOnTop, WindowListSkip,CirculateSkip, NoTitle
#StartsOnPage 0 1 0
*FvwmTaskBarGeometry +1-0
*FvwmTaskBarFore grey66
*FvwmTaskBarBack black
*FvwmTaskBarTipsFore red
*FvwmTaskBarTipsBack green
*FvwmTaskBarFont -*helvetica-bold-r-*-*-12-*-*-*-*-*-*-*
*FvwmTaskBarSelFont lucidasans-bolditalic-14 
#-*helvetica-bold-r-*-*-12-*-*-*-*-*-*-*
*FvwmTaskBarAction Click1 Iconify -1,Raise,Focus
*FvwmTaskBarAction Click2 Module "FvwmIdent" FvwmIdent
*FvwmTaskBarAction Click3 Resize
*FvwmTaskBarUseSkipList
#*FvwmTaskBarDeskOnly
#*FvwmTaskBarAutoStick
*FvwmTaskBarStartName Lxl
*FvwmTaskBarStartMenu StartMenu
#*FvwmTaskBarStartIcon shadowman-mini.xpm
*FvwmTaskBarBellVolume 10
*FvwmTaskBarMailBox /var/spool/mail/root
*FvwmTaskBarClockFormat %H:%M
#*FvwmTaskBarShowTips
#*FvwmTaskBarAutoHide

###########################################################################
##### SpecTaskBar
Style "SpecTaskBar"	BorderWidth 0,NoHandles
Style "SpecTaskBar"	StaysOnTop, WindowListSkip,CirculateSkip, NoTitle
#Style "SpecTaskBar"	SkipMapping, 
#StartsOnPage 0 1 0
*SpecTaskBarGeometry -0-10
*SpecTaskBarFore grey67
*SpecTaskBarBack black
*SpecTaskBarFont -*helvetica-bold-r-*-*-12-*-*-*-*-*-*-*
*SpecTaskBarSelFont lucidasans-bolditalic-14 
*SpecTaskBarUseSkipList
#*SpecTaskBarDeskOnly
#*SpecTaskBarAutoStick
*SpecTaskBarStartName Lxl
*SpecTaskBarStartMenu StartMenu


###########################################################################
##### FvwmAudio
*FvwmAudioDir /usr/share/sound
*FvwmAudioPlayCmd play
*FvwmAudioDelay 0
# User must add a new column to each line below, specifying
# a sound file from SOUND_DIR (define-d in .fvwm2rc-defines)
*FvwmAudio startup 
*FvwmAudio shutdown 
*FvwmAudio unknown 
*FvwmAudio add_window 
*FvwmAudio raise_window 
*FvwmAudio lower_window 
*FvwmAudio focus_change_window 
*FvwmAudio destroy_window 
*FvwmAudio iconify 
*FvwmAudio deiconify 
*FvwmAudio toggle_paging 
*FvwmAudio new_page 
*FvwmAudio new_desk 
*FvwmAudio configure_window 
*FvwmAudio window_name 
*FvwmAudio icon_name 
*FvwmAudio res_class
*FvwmAudio res_name
*FvwmAudio end_windowlist

###########################################################################
######## FvwmScroll
*FvwmScrollFore gray76
*FvwmScrollBack gray51

###########################################################################
######## FvwmIconBox
*FvwmIconBoxIconBack    #cfcfcf
*FvwmIconBoxIconHiFore  black
*FvwmIconBoxIconHiBack  LightSkyBlue
*FvwmIconBoxBack        LightSlateGray
*FvwmIconBoxGeometry    6x1+0-0
*FvwmIconBoxMaxIconSize 64x50
*FvwmIconBoxFont        -adobe-helvetica-bold-r-normal--10-100-75-75-p-57-iso8859-1
*FvwmIconBoxSortIcons
*FvwmIconBoxPadding     4
*FvwmIconBoxLines       6
*FvwmIconBoxPlacement   Left Top
#*FvwmIconBoxPixmap      
*FvwmIconBoxHideSC 	Horizontal
*FvwmIconBoxSortIcons
# mouse bindings
*FvwmIconBoxMouse       1       Click           RaiseLower
*FvwmIconBoxMouse       1       DoubleClick     Iconify
*FvwmIconBoxMouse       2       Click           Iconify -1, Focus
*FvwmIconBoxMouse       3       Click           Module "FvwmIdent" FvwmIdent
*FvwmIconBoxSBWidth	2
# Key bindings
*FvwmIconBoxKey         r       RaiseLower
*FvwmIconBoxKey         space   Iconify
*FvwmIconBoxKey         d       Close
# FvwmIconBox built-in functions
*FvwmIconBoxKey         n       
*FvwmIconBoxKey         p       Prev
*FvwmIconBoxKey         h       Left
*FvwmIconBoxKey         j       Down
*FvwmIconBoxKey         k       Up
*FvwmIconBoxKey         l       Right
# Icon file spcifications
#*FvwmIconBox            "Fvwm*"         -

############################################################################
######### FvwmBanner
Style "FvwmBanner"   	StaysOnTop,Sticky, WindowListSkip,CirculateSkip, BorderWidth 0, ClickToFocus

############################################################################
######### FvwmWharf - Afterstep's thing...
Style "FvwmWharf" StaysOnTop,Sticky, WindowListSkip,CirculateSkip, ClickToFocus, NoTitle,NoHandles,BorderWidth 0
*FvwmWharfAnimate
*FvwmWharfAnimateMain
*FvwmWharfGeometry	-0+0
*FvwmWharfColumns	1 
*FvwmWharfTextureType	255
*FvwmWharfBgColor	gray51
*FvwmWharf AfterStep AfterStep.xpm Folder
*FvwmWharf Shutdown  ShutDown.xpm  Quit
*FvwmWharf xlock KeysOnChain.xpm FvwmForm XLock
*FvwmWharf ~Folder
*FvwmWharf xclock nil Swallow "xclock" xclock -bg "#8e8a9e" -fg "#00003f" -geometry 45x45-1-1 -padding 0 &
*FvwmWharf xbiff  nil Swallow "xbiff" xbiff -bg "#8e8a9e" -fg "#00003f" -geometry 45x45-1-1 &
*FvwmWharf xload  nil Swallow "xload" xload -nolabel -hl black -bg "#8e8a9e" -geometry 48x48-1-1 &
*FvwmWharf xload  nil Exec xterm -ut -T top -e top &
*FvwmWharf Pager nil Swallow "FvwmPager" Module FvwmPager 0 0
*FvwmWharf xterm monitor-check.xpm  Exec xterm -bg black -geometry 80x25 -sl 500 -sb -ls -T 'xterm: root@lingan.mheads' &
#emacs not found

#gimp not found
 
*FvwmWharf elm writeletter.xpm Exec xterm -T "elm" -e elm &
*FvwmWharf Netscape netscape.png Exec netscape &
*FvwmWharf Recycler  recycler.xpm  Restart fvwm2

############################################################################
#
# FvwmScript - The very nice scripting module
#
*FvwmScriptPath  /etc/X11/AnotherLevel/scripts




############################################################################
#
# FvwmForm LogoutVerify -- Modified from FvwmForm man page.
#

*LogoutVerifyGrabServer
*LogoutVerifyWarpPointer
*LogoutVerifyFont          *helvetica-bold-r*24*
*LogoutVerifyButtonFont    *helvetica*medium-r*12*
*LogoutVerifyFore          black
*LogoutVerifyBack          grey68
*LogoutVerifyItemFore      black
*LogoutVerifyItemBack      grey49
# begin items
*LogoutVerifyLine          center
*LogoutVerifyText         "Do you really want to logout?"
*LogoutVerifyLine          expand
*LogoutVerifyButton        quit      "Yes" Y
*LogoutVerifyCommand       Quit
*LogoutVerifyButton        quit      "Cancel" 
*LogoutVerifyCommand       Nop
*LogoutVerifyButton        quit      "Restart Fvwm" R
*LogoutVerifyCommand       Restart
# Fvwm window style
Style "LogoutVerify" 		StaysOnTop,Sticky

###############################################################################
#
# FvwmForm About -- Fvwm's About Box
#

*AboutWarpPointer
*AboutFore		black
*AboutBack		grey67
*AboutItemFore      	black
*AboutItemBack      	grey50
*AboutFont		*helvetica-bold-r-*-*-12-*-*-*-*-*-*-*

*AboutLine		center
*AboutText		"AnotherLevel - a FVWM2 configuration"
*AboutLine		center
*AboutText		"Copyright (C) 1997, Red Hat Software, Inc."

*AboutLine left
*AboutText "Serverhost=lingan.mheads, Clienthost=lingan.mheads"
*AboutLine left
*AboutText "Hostname=lingan.mheads, Ostype=Linux, User=root"
*AboutLine left
*AboutText "Version=11, Revision=6"
*AboutLine left
*AboutText "Vendor=The XFree86 Project, Inc, Release=3330"
*AboutLine left
*AboutText "Width=1024, Height=768"
*AboutLine left
*AboutText "X_Resolution=2951, Y_Resolution=2954"
*AboutLine left
*AboutText "Planes=8, Class=PseudoColor, Color=Yes"
*AboutLine left
*AboutText "Fvwm_Version=2.2, Options=SHAPE XPM M4 "
*AboutLine left
*AboutText "Fvwmdir=/usr/X11R6/lib/X11/fvwm2"

*AboutLine		center
*AboutButton		quit	"Dismiss"

Style "About" NoButton 2, NoButton 4, NoButton 6, StaysOnTop,Sticky

###########################################################################
#
# FvwmForm Xrsh -- Start a program on a remote machine
#

*XrshFont         *helvetica*medium-r*12*
*XrshButtonFont   fixed
*XrshFore         black
*XrshBack         grey68
*XrshItemFore          black
*XrshItemBack          grey48
# begin items
*XrshLine         center
*XrshText         "Xrsh"
*XrshFont         *helvetica*medium-r*n*12*
*XrshLine         expand
*XrshSelection    Host single
*XrshChoice _arg1USER_HOST_LIST _arg1USER_HOST_LIST off "_arg1user_host_list"
*XrshChoice shiftUSER_HOST_LIST shiftUSER_HOST_LIST off "Shiftuser_host_list"

*XrshLine         expand


*XrshButton	quit "emacs" E
*XrshCommand    Exec $(_arg1USER_HOST_LIST?xon _arg1USER_HOST_LIST)$(shiftUSER_HOST_LIST?xon shiftUSER_HOST_LIST) emacs

*XrshButton	quit "xemacs" X
*XrshCommand    Exec $(_arg1USER_HOST_LIST?xon _arg1USER_HOST_LIST)$(shiftUSER_HOST_LIST?xon shiftUSER_HOST_LIST) xemacs

*XrshButton	quit "xload" L
*XrshCommand    Exec $(_arg1USER_HOST_LIST?xon _arg1USER_HOST_LIST)$(shiftUSER_HOST_LIST?xon shiftUSER_HOST_LIST) xload

*XrshButton	quit "netscape" N
*XrshCommand    Exec $(_arg1USER_HOST_LIST?xon _arg1USER_HOST_LIST)$(shiftUSER_HOST_LIST?xon shiftUSER_HOST_LIST) netscape

*XrshButton	quit "xterm" T
*XrshCommand    Exec $(_arg1USER_HOST_LIST?xon _arg1USER_HOST_LIST)$(shiftUSER_HOST_LIST?xon shiftUSER_HOST_LIST) xterm

*XrshButton       quit "Cancel" ^[
*XrshCommand      Nop

###########################################################################
#
# FvwmForm Rlogin -- Rlogin to an arbitrary remote machine
#

*RloginWarpPointer
*RloginFont         *helvetica*medium-r*12*
*RloginButtonFont   fixed
*RloginInputFont    *helvetica*medium-r*12*
*RloginFore         black
*RloginBack         grey69
*RloginItemFore     black
*RloginItemBack     grey49
# begin items
*RloginLine         center
*RloginText         "Login to Remote Host"
*RloginLine center
*RloginText "Host:"
*RloginInput HostName 20 ""
*RloginLine         center
*RloginSelection    UserSel   single
*RloginChoice       Default   Default   on   "root"
*RloginChoice       Custom    Custom    off  "Username:"
*RloginInput        UserName  10   ""
*RloginLine         expand
*RloginButton       quit "Login" ^M
*RloginCommand Exec xterm  -T xterm@$(HostName) -e rlogin $(HostName) $(Custom?-l $(UserName))
*RloginButton       restart   "Clear"
*RloginCommand Beep
*RloginButton       quit "Cancel"
*RloginCommand Nop

###########################################################################
#
# FvwmForm TalkForm FvwmTalk module which dissappears after use
# (Was: MyTalk & MyFvwmTalk from Dave Goldberg)
#

*TalkFormWarpPointer
*TalkFormFont *helvetica*medium-r*12*
*TalkFormButtonFont *helvetica*medium-r*12*
*TalkFormInputFont BASIC-FONT*medium-r*12*
*TalkFormFore black
*TalkFormBack grey69
*TalkFormItemFore black
*TalkFormItemBack grey48
*TalkFormLine center
*TalkFormText "Fvwm Function"
*TalkFormInput Func 40 ""
*TalkFormLine expand
*TalkFormButton quit "Run" ^M
*TalkFormCommand $(Func)
*TalkFormButton restart "Clear" ^R
*TalkFormButton quit "Cancel" ^C
*TalkFormCommand Nop

###########################################################################
#
# Capture Window (from man page)
#

*CaptureFont         *helvetica*medium-r*12*
*CaptureButtonFont   fixed
*CaptureInputFont    *helvetica*medium-r*12*
*CaptureLine        center
*CaptureText        "Capture Window"
*CaptureLine        left
*CaptureText        "File: "
*CaptureInput       file      25   "/tmp/Capture"
*CaptureLine        left
*CaptureText        "Printer: "
*CaptureInput       printer        20   "ps1"
*CaptureLine        expand
*CaptureSelection   PtrType   single
*CaptureChoice      PS   ps   on   "PostScript"
*CaptureChoice      Ljet ljet off  "HP LaserJet"
*CaptureLine        left
*CaptureText        "xwd options:"
*CaptureLine        expand
*CaptureSelection   Options   multiple
*CaptureChoice      Brd  -nobdrs   off  "No border"
*CaptureChoice      Frm  -frame    on   "With frame"
*CaptureChoice      XYZ  -xy  off  "XY "
 
###########################################################################
#
# FvwmForm AudioEvents
#
# This is incomplete in that it'd be cool if you could save your settings
# and reload them.... but I don't even use FvwmAudio, alas I can't
# concentrate on this for now

*AudioEventsFont         fixed
*AudioEventsButtonFont   fixed
*AudioEventsFore         black
*AudioEventsBack         grey69
*AudioEventsItemFore     black
*AudioEventsItemBack     grey47

*AudioEventsLine left
*AudioEventsText "startup"
*AudioEventsInput startup 50 "bud.au"

*AudioEventsText "shutdown"
*AudioEventsInput shutdown 50 ""
*AudioEventsLine left
*AudioEventsText "unknown"
*AudioEventsInput unknown 50 "doh.au"
*AudioEventsLine left
*AudioEventsText "add_window"
*AudioEventsInput add_window 50 "gong.wav"
*AudioEventsLine left
*AudioEventsText "raise_window"
*AudioEventsInput raise_window 50 "chimes.wav"
*AudioEventsLine left
*AudioEventsText "lower_window"
*AudioEventsInput lower_window 50 "beep1.wav"
*AudioEventsLine left
*AudioEventsText "focus_change_window"
*AudioEventsInput focus_change_window 50 "beep1.wav"
*AudioEventsLine left
*AudioEventsText "destroy_window"
*AudioEventsInput destroy_window 50 "splat.wav"
*AudioEventsLine left
*AudioEventsText "iconify"
*AudioEventsInput iconify 50 "splat.wav"
*AudioEventsLine left
*AudioEventsText "deiconify"
*AudioEventsInput deiconify 50 "ploop.au"
*AudioEventsLine left
*AudioEventsText "toggle_paging"
*AudioEventsInput toggle_paging 50 "fwop.au"
*AudioEventsLine left
*AudioEventsText "new_page"
*AudioEventsInput new_page 50 "beam_trek.au"
*AudioEventsLine left
*AudioEventsText "new_desk"
*AudioEventsInput new_desk 50 "beam_trek.au"
*AudioEventsLine left
*AudioEventsText "configure_window"
*AudioEventsInput configure_window 50 "huh.au"
*AudioEventsLine left
*AudioEventsText "window_name"
*AudioEventsInput window_name 50 "beep.au"
*AudioEventsLine left
*AudioEventsText "icon_name"
*AudioEventsInput icon_name 50 "beep.au"
*AudioEventsLine left
*AudioEventsText "res_class"
*AudioEventsInput res_class 50 "beep.au"
*AudioEventsLine left
*AudioEventsText "res_name"
*AudioEventsInput res_name 50 "beep.au"
*AudioEventsLine left
*AudioEventsText "end_windowlist"
*AudioEventsInput end_windowlist 50 "beep.au"
*AudioEventsLine expand
*AudioEventsButton quit "Quit" 
*AudioEventsButton nop "Apply" A
#FIX: Add the rest in here, if this works
*AudioEventsCommand *FvwmAudio raise_window $(raise_window)
*AudioEventsCommand *FvwmAudio lower_window $(lower_window)

###########################################################################





# User initialization



###########################################################################
#
# Stuff to do at start-up
#

# SetupFunction gets run at Init and Restart

AddToFunc "SetupFunction"
#+ "I" Exec xsetroot -solid black
#+ "I" Exec xmodmap -e "pointer = 3 2 1"

# EndSetupFunction gets run at Init and Restart, but after StartupFunction
# is executed for the Init case


AddToFunc "SpecPageFunc"  "I" Style "SpecPager" SkipMapping, StartsOnPage 0 0 1
+ "I" Module SpecPager 0 1
+ "I" Wait SpecPager 
+ "I" Style "SpecPager" SkipMapping, StartsOnPage 0 1 0
+ "I" Module SpecPager 0 1
+ "I" Wait SpecPager
+ "I" Style "SpecPager" SkipMapping, StartsOnPage 0 1 1
+ "I" Module SpecPager 0 1

AddToFunc "SpecTaskFunc" "I" Style "SpecTaskBar"  StartsOnPage 0 1 0
+ "I" Module SpecTaskBar
+ "I" Wait SpecTaskBar
+ "I" Style "SpecTaskBar"  StartsOnPage 0 0 1
+ "I" Module SpecTaskBar
+ "I" Wait SpecTaskBar
+ "I" Style "SpecTaskBar"  StartsOnPage 0 1 1
+ "I" Module SpecTaskBar
+ "I" Wait SpecTaskBar

AddToFunc "EndSetupFunction"
#+ "I" Next [!iconic CurrentScreen XTERM] Focus
#+ "I" CursorMove -100 100

# StartupFunction only gets run at Init, not at Restart.
# It is run after SetupFunction, but before EndSetupFunction
AddToFunc "StartupFunction" "I" Exec /bin/rm ~/.etc/.tmp/.xtermno*
#+ "I" FvwmButtons
#+ "I" Exec xmchat
#+ "I" Wait lxlxchat
#+ "I" Current Iconify 
#+ "I" Exec cxt
#+ "I" Exec dxts
#+ "I" Wait xmdict
+ "I" Exec xbiff-start 
#+ "I" Wait xbiff
+ "I" Exec nxt
#+ "I" Wait nemacs
#+ "I" Current Maximize


AddToFunc "RestartFunction" 
+ "I" Module FvwmButtons
#+ "I" Exec emacs

#+ "I" Exec emacs -geometry 84x48-1+1
#+ "I" Exec XTERM -geometry 80x22+1-BOTTOM_EDGE


DestroyFunc WindowListFunc

AddToFunc WindowListFunc  "I" WindowId $0 Iconify -1
+ "I" WindowId $0 Focus
+ "I" WindowId $0 Raise
#+ "I" WindowId $0 WarpToWindow 5p 5p


#unclutter not found


###########################################################################
# Local Variables:
# rm-trailing-spaces: t
# page-delimiter: "^#####"
# End:




#
