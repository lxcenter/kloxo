/* lynx_cfg.h.  Generated automatically by configure.  */
/* The configure script translates "config.hin" into "lynx_cfg.h" */
#ifndef LYNX_CFG_H
#define LYNX_CFG_H 1

/* #undef ALL_CHARSETS */		/* AC_ARG_WITH(charsets) */
#define ALT_CHAR_SET acs_map		/* CF_ALT_CHAR_SET */
#define ANSI_VARARGS 1		/* CF_VARARGS */
/* #undef ARCHIVE_ONLY */		/* CF_ARG_DISABLE(dired-archive) */
#define BZIP2_PATH "/usr/bin/bzip2"		/* CF_PATH_PROG(bzip2) */
#define CAN_SET_ERRNO 1		/* CF_SET_ERRNO */
#define CHMOD_PATH "/bin/chmod"		/* CF_PATH_PROG(chmod) */
#define CJK_EX 1			/* CF_ARG_ENABLE(cjk) */
#define COLOR_CURSES 1		/* defined by CF_COLOR_CURSES */
#define COMPRESS_PATH "/usr/bin/compress"		/* CF_PATH_PROG(compress) */
#define COPY_PATH "/bin/cp"		/* CF_PATH_PROG(cp) */
/* #undef CURS_PERFORMANCE */		/* CF_CURS_PERFORMANCE */
/* #undef DEBUG */			/* configure --enable-debug */
/* #undef DECL_ERRNO */
/* #undef DECL_GETGRGID */
/* #undef DECL_GETGRNAM */
/* #undef DECL_STRSTR */
/* #undef DECL_SYS_ERRLIST */
#define DIRED_SUPPORT 1		/* AC_ARG_WITH(dired) */
/* #undef DISABLE_BIBP */		/* CF_ARG_DISABLE(bibp-urls) */
/* #undef DISABLE_FINGER */		/* CF_ARG_DISABLE(finger) */
/* #undef DISABLE_FTP */		/* CF_ARG_DISABLE(ftp) */
/* #undef DISABLE_GOPHER */		/* CF_ARG_DISABLE(gopher) */
/* #undef DISABLE_NEWS */		/* CF_ARG_DISABLE(news) */
#define DISP_PARTIAL 1		/* CF_ARG_ENABLE(partial) */
/* #undef DONT_TRACK_INTERNAL_LINKS */ /* CF_ARG_DISABLE(internal-links) */
/* #undef ENABLE_IPV6 */		/* CF_CHECK_IPV6 */
/* #undef ENABLE_NLS */		/* defined if NLS is requested */
/* #undef ENABLE_OPTS_CHANGE_EXEC */	/* CF_ARG_ENABLE(change-exec) */
#define EXEC_LINKS 1		/* CF_ARG_ENABLE(exec-links) */
#define EXEC_SCRIPTS 1		/* CF_ARG_ENABLE(exec-scripts) */
#define EXP_ADDRLIST_PAGE 1	/* CF_ARG_ENABLE(addrlist-page) */
#define EXP_ALT_BINDINGS 1		/* CF_ARG_ENABLE(alt-bindings) */
/* #undef EXP_CHARSET_CHOICE */	/* CF_ARG_ENABLE(charset-choice) */
/* #undef EXP_CHARTRANS_AUTOSWITCH */	/* CF_ARG_ENABLE(font-switch) */
#define EXP_FILE_UPLOAD 1		/* CF_ARG_ENABLE(file-upload) */
#define EXP_JUSTIFY_ELTS 1		/* CF_ARG_ENABLE(justify-elts) */
#define EXP_KEYBOARD_LAYOUT 1	/* CF_ARG_ENABLE(kbd-layout) */
#define EXP_LIBJS 1		/* CF_ARG_ENABLE(libjs) */
#define EXP_NESTED_TABLES 1	/* CF_ARG_ENABLE(nested-tables) */
#define EXP_PERSISTENT_COOKIES 1	/* CF_ARG_ENABLE(persistent-cookies) */
#define EXP_READPROGRESS 1		/* CF_ARG_ENABLE(read-eta) */
#define FANCY_CURSES 1		/* defined by CF_FANCY_CURSES */
/* #undef GCC_NORETURN */		/* CF_GCC_ATTRIBUTES */
/* #undef GCC_PRINTF */		/* CF_GCC_ATTRIBUTES */
/* #undef GCC_UNUSED */		/* CF_GCC_ATTRIBUTES */
#define GETGROUPS_T gid_t		/* AC_TYPE_GETGROUPS */
#define GZIP_PATH "/bin/gzip"		/* CF_PATH_PROG(gzip) */
#define HAVE_ALLOCA 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_ALLOCA_H 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_ARGZ_H 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_ARPA_INET_H 1
#define HAVE_ASSUME_DEFAULT_COLORS 1 /* ncurses extension */
/* #undef HAVE_BSD_TOUCHLINE */	/* CF_CURS_TOUCHLINE */
/* #undef HAVE_CATGETS */		/* defined if you want to use non-GNU catgets */
#define HAVE_CBREAK 1
/* #undef HAVE_CURSESX_H */
#define HAVE_CUSERID 1
/* #undef HAVE_DCGETTEXT */		/* defined by AM_GNU_GETTEXT */
#define HAVE_DEFINE_KEY 1
#define HAVE_DELSCREEN 1		/* defined by CF_CURSES_FUNCS  */
#define HAVE_DIRENT_H 1		/* defined by AC_HEADER_DIRENT */
#define HAVE_FCNTL_H 1		/* have <fcntl.h> */
#define HAVE_FTIME 1
/* #undef HAVE_GAI_STRERROR */	/* CF_CHECK_IPV6 */
/* #undef HAVE_GETADDRINFO */		/* CF_CHECK_IPV6 */
#define HAVE_GETATTRS 1
#define HAVE_GETBEGX 1
#define HAVE_GETBEGY 1
#define HAVE_GETBKGD 1		/* defined by CF_COLOR_CURSES */
#define HAVE_GETCWD 1
#define HAVE_GETGROUPS 1
/* #undef HAVE_GETTEXT */		/* defined if you want to use non-GNU gettext */
#define HAVE_GETTIMEOFDAY 1
#define HAVE_GETUID 1
#define HAVE_H_ERRNO 1
#define HAVE_INET_ATON 1		/* CF_INET_ADDR */
/* #undef HAVE_JCURSES_H */
#define HAVE_KEYPAD 1
#define HAVE_LC_MESSAGES 1		/* locale messages */
/* #undef HAVE_LIBINTL_H */		/* AM_GNU_GETTEXT, or cleanup from that */
#define HAVE_LIMITS_H 1
#define HAVE_LOCALE_H 1
#define HAVE_LSTAT 1		/* defined by CF_FUNC_LSTAT */
#define HAVE_MALLOC_H 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_MKSTEMP 1
#define HAVE_MKTEMP 1
#define HAVE_MMAP 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_MUNMAP 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_NAPMS 1
/* #undef HAVE_NCURSES_H */		/* defined if we include <ncurses.h> */
/* #undef HAVE_NCURSES_NCURSES_H */	/* defined if we include <ncurses/ncurses.h> */
#define HAVE_NCURSES_TERM_H 1	/* have <ncurses/term.h> */
#define HAVE_NEWPAD 1
#define HAVE_NEWTERM 1
#define HAVE_NL_TYPES_H 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_PNOUTREFRESH 1
#define HAVE_POPEN 1
#define HAVE_PUTENV 1
#define HAVE_READDIR 1
#define HAVE_RESIZETERM 1
/* #undef HAVE_RESOLV_H */
#define HAVE_SETENV 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_SETLOCALE 1
#define HAVE_SETUID 1
#define HAVE_SIGACTION 1		/* CF_FUNC_SIGACTION */
#define HAVE_SIZECHANGE 1		/* defined by CF_SIZECHANGE */
#define HAVE_STDARG_H 1		/* CF_VARARGS */
#define HAVE_STDLIB_H 1
#define HAVE_STPCPY 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_STRCASECMP 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_STRCHR 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_STRERROR 1
#define HAVE_STRING_H 1
#define HAVE_SYSLOG_H 1
/* #undef HAVE_SYSV_TOUCHLINE */	/* CF_CURS_TOUCHLINE */
/* #undef HAVE_SYS_DIR_H */		/* defined by AC_HEADER_DIRENT */
#define HAVE_SYS_FCNTL_H 1		/* have <sys/fcntl.h> */
/* #undef HAVE_SYS_FILIO_H */		/* have <sys/filio.h> */
#define HAVE_SYS_IOCTL_H 1		/* have <sys/ioctl.h> */
/* #undef HAVE_SYS_NDIR_H */		/* defined by AC_HEADER_DIRENT */
#define HAVE_SYS_PARAM_H 1		/* defined by AM_GNU_GETTEXT */
#define HAVE_SYS_TIMEB_H 1		/* have <sys/timeb.h> */
#define HAVE_SYS_WAIT_H 1		/* have <sys/wait.h> */
#define HAVE_TERMIOS_H 1		/* have <termios.h> */
#define HAVE_TERMIO_H 1		/* have <termio.h> */
#define HAVE_TERM_H 1		/* have <term.h> */
#define HAVE_TOUCHLINE 1
#define HAVE_TOUCHWIN 1
#define HAVE_TRUNCATE 1
#define HAVE_TTYNAME 1
#define HAVE_TTYTYPE 1
/* #undef HAVE_TYPE_UNIONWAIT */	/* CF_UNION_WAIT */
#define HAVE_UNISTD_H 1		/* have <unistd.h> */
#define HAVE_UNSETENV 1
#define HAVE_USE_DEFAULT_COLORS 1	/* ncurses extension */
#define HAVE_UTMP 1		/* CF_UTMP */
#define HAVE_UTMP_UT_HOST 1	/* CF_UTMP_UT_HOST */
#define HAVE_UTMP_UT_SESSION 1	/* CF_UTMP_UT_SESSION */
#define HAVE_UTMP_UT_XSTATUS 1	/* CF_UTMP_UT_XSTATUS */
#define HAVE_UTMP_UT_XTIME 1	/* CF_UTMP_UT_XTIME */
/* #undef HAVE_VALUES_H */		/* defined by AM_GNU_GETTEXT */
#define HAVE_VARARGS_H 1		/* CF_VARARGS */
#define HAVE_VASPRINTF 1
/* #undef HAVE_VFORK_H */		/* have <vfork.h> */
#define HAVE_WAITPID 1
#define HAVE_WBORDER 1
#define HAVE_WREDRAWLN 1
/* #undef HAVE_XCURSES */		/* CF_PDCURSES_X11 */
#define HAVE___ARGZ_COUNT 1	/* defined by AM_GNU_GETTEXT */
#define HAVE___ARGZ_NEXT 1		/* defined by AM_GNU_GETTEXT */
#define HAVE___ARGZ_STRINGIFY 1	/* defined by AM_GNU_GETTEXT */
/*Ligesh*/
#define IGNORE_CTRL_C  	1	/* FIXME: make tests? */
/* #undef INCLUDE_PROTOTYPES */	/* CF_SOCKS5 */
#define INSTALL_ARGS "-c"		/* CF_PATH_PROG(install) */
#define INSTALL_PATH "/usr/bin/install"		/* CF_PATH_PROG(install) */
/* #undef LINUX */			/* FIXME: make tests? */
#define LOCALE 1			/* for locale support */
#define LONG_LIST 1		/* CF_ARG_DISABLE(long-list) */
#define LYNXCGI_LINKS 1		/* CF_ARG_ENABLE(cgi-links) */
#define LYNX_CFG_FILE "/usr/lib/lynx.cfg"		/* $libdir/lynx.cfg */
#define LYNX_LSS_FILE "/usr/lib/lynx.lss"		/* $libdir/lynx.lss */
#define LYNX_RAND_MAX INT_MAX		/* CF_SRAND */
/* #undef LY_FIND_LEAKS */		/* CF_ARG_ENABLE(find-leaks) */
/* #undef LY_TRACELINE */		/* CF_ARG_ENABLE(vertrace) */
#define MKDIR_PATH "/bin/mkdir"		/* CF_PATH_PROG(mkdir) */
#define MV_PATH "/bin/mv"			/* CF_PATH_PROG(mv) */
#define NCURSES 1			/* defined for ncurses support */
/* #undef NCURSES_BROKEN */		/* defined for ncurses color support */
/* #undef NEED_PTEM_H */		/* defined by CF_SIZECHANGE */
/* #undef NEED_REMOVE */		/* defined by CF_REMOVE_BROKEN */
/* #undef NGROUPS */			/* defined by CF_NGROUPS */
/* #undef NO_CHANGE_EXECUTE_PERMS */	/* CF_ARG_DISABLE(dired-xpermit) */
/* #undef NO_CONFIG_INFO */		/* CF_ARG_DISABLE(config-info) */
/* #undef NO_EXTENDED_HTMLDTD */	/* CF_ARG_DISABLE(extended-dtd) */
/* #undef NO_LYNX_TRACE */		/* CF_ARG_DISABLE(trace) */
/* #undef NO_OPTION_FORMS */		/* CF_ARG_DISABLE(forms-options) */
/* #undef NO_OPTION_MENU */		/* CF_ARG_DISABLE(option-menu) */
/* #undef NO_PARENT_DIR_REFERENCE */	/* CF_ARG_DISABLE(parent-dir-refs) */
#define NSL_FORK 1			/* CF_ARG_ENABLE(nsl-fork) */
#define OK_GZIP 1			/* CF_ARG_DISABLE(dired-gzip) */
/* #undef OK_INSTALL */
#define OK_OVERRIDE 1		/* CF_ARG_DISABLE(dired-override) */
#define OK_PERMIT 1		/* CF_ARG_DISABLE(dired-permit) */
#define OK_TAR 1			/* CF_ARG_DISABLE(dired-tar) */
#define OK_UUDECODE 1		/* CF_ARG_DISABLE(dired-uudecode) */
#define OK_ZIP 1			/* CF_ARG_DISABLE(dired-zip) */
/* #undef REAL_UNIX_SYSTEM */		/* CF_SLANG_UNIX_DEFS */
#define RLOGIN_PATH "/usr/bin/rlogin"		/* CF_PATH_PROG(rlogin) */
#define RM_PATH "/bin/rm"			/* CF_PATH_PROG(rm) */
/* #undef SOCKS */			/* CF_SOCKS, CF_SOCKS5 */
#define SOURCE_CACHE 1		/* CF_ARG_ENABLE(source-cache) */
#define STDC_HEADERS 1
#define SYSTEM_MAIL "/usr/sbin/sendmail"		/* CF_DEFINE_PROG */
#define SYSTEM_MAIL_FLAGS "-t -oi"	/* defined by CF_SYSTEM_MAIL_FLAGS */
#define SYSTEM_NAME "linux-gnu"		/* CF_CHECK_CACHE */
#define TAR_PATH "/bin/tar"			/* CF_PATH_PROG(tar) */
#define TELNET_PATH "/usr/bin/telnet"		/* CF_PATH_PROG(telnet) */
#define TERMIO_AND_CURSES 1	/* CF_TERMIO_AND_CURSES workaround */
/* #undef TERMIO_AND_TERMIOS */	/* CF_TERMIO_AND_TERMIOS workaround */
#define TN3270_PATH "tn3270"		/* CF_PATH_PROG(tn3270) */
#define TOUCH_PATH "/bin/touch"		/* CF_PATH_PROG(touch) */
/* #undef ULTRIX */			/* config.sub */
#define UNCOMPRESS_PATH "/bin/gunzip"		/* CF_PATH_PROG(gunzip) */
#define UNDERLINE_LINKS 1		/* CF_ARG_ENABLE(underlines) */
#define UNIX 1
#define UNZIP_PATH "/usr/bin/unzip"		/* CF_PATH_PROG(unzip) */
#define USE_COLOR_STYLE 1		/* CF_ARG_ENABLE(color-style) */
#define USE_DEFAULT_COLORS 1	/* CF_ARG_ENABLE(default-colors) */
/* #undef USE_EXECVP */		/* CF_ARG_DISABLE(full-paths) */
#define USE_EXTERNALS 1		/* CF_ARG_ENABLE(externs) */
/* #undef USE_FCNTL */		/* CF_FIONBIO */
#define USE_OPENSSL_INCL 1		/* CF_SSL */
#define USE_PRETTYSRC 1		/* CF_ARG_ENABLE(prettysrc) */
#define USE_SCROLLBAR 1		/* CF_ARG_ENABLE(scrollbar) */
/* #undef USE_SLANG */		/* AC_ARG_WITH(screen=slang) */
/* #undef USE_SOCKS4_PREFIX */	/* CF_SOCKS5 */
/* #undef USE_SOCKS5 */		/* CF_SOCKS5 */
#define USE_SSL 1			/* CF_SSL */
#define USE_SYSV_UTMP 1		/* CF_UTMP */
/* #undef USE_ZLIB */			/* AC_ARG_WITH(zlib) */
/* #undef UTMPX_FOR_UTMP */		/* use <utmpx.h> since <utmp.h> not found */
#define UUDECODE_PATH "/usr/bin/uudecode"		/* CF_PATH_PROG(uudecode) */
/* #undef WAITPID_USES_UNION */	/* CF_FUNC_WAIT */
/* #undef WAIT_USES_UNION */		/* CF_FUNC_WAIT */
/* #undef XCURSES */			/* CF_PDCURSES_X11 */
#define ZCAT_PATH "/bin/zcat"		/* CF_PATH_PROG(zcat) */
#define ZIP_PATH "/usr/bin/zip"			/* CF_PATH_PROG(zip) */
/* #undef _ALL_SOURCE */		/* AC_AIX */
/* #undef inline */			/* defined by AC_C_INLINE */
#define lynx_rand lrand48		/* CF_SRAND */
#define lynx_srand srand48		/* CF_SRAND */
/* #undef mode_t */			/* defined by AC_TYPE_MODE_T */
/* #undef off_t */			/* defined by AC_TYPE_OFF_T */
/* #undef pid_t */			/* defined by AC_TYPE_PID_T */
/* #undef uid_t */			/* defined by AC_TYPE_UID_T */
/* #undef ut_name */			/* CF_UTMP */
#define ut_xstatus ut_exit.e_exit		/* CF_UTMP_UT_XSTATUS */
/* #undef ut_xtime */			/* CF_UTMP_UT_XTIME */

/*
 * U/Win defines vfork() as a macro in vfork.h, which is included from unistd.h.
 */
#ifndef HAVE_VFORK_H
/* #undef vfork */			/* defined by AC_FUNC_FORK */
#endif

/* 'const' may be defined externally by the compiler-wrapper, as in 'unproto'
 * or by AC_C_CONST
 */
#ifndef const
/* #undef const */
#endif

/*
 * The configure script generates LYHelp.h (handcrafted makefiles may not do
 * this, so we set a definition):
 */
#define HAVE_LYHELP_H 1

/* FIXME:DGUX (done in $host_os case-statement) */
/* FIXME:DGUX_OLD */
/* FIXME:HP_TERMINAL */
/* FIXME:REVERSE_CLEAR_SCREEN_PROBLEM */
/* FIXME:SHORTENED_RBIND */
/* FIXME:SNAKE */
/* FIXME:SVR4_BSDSELECT (done in $host_os case-statement) */

/* Some older socks libraries, especially AIX need special definitions */
#if defined(_AIX) && !defined(USE_SOCKS5)
/* #undef accept */
/* #undef bind */
/* #undef connect */
/* #undef getpeername */
/* #undef getsockname */
/* #undef listen */
/* #undef recvfrom */
/* #undef select */
#endif

#ifdef HAVE_SYSLOG_H
/* #undef SYSLOG_REQUESTED_URLS */	/* CF_ARG_ENABLE(syslog) */
#endif

#ifndef HAVE_LSTAT
#define lstat stat
#endif

#ifdef DECL_GETGRGID
extern struct group * getgrgid ();
#endif

#ifdef DECL_GETGRNAM
extern struct group * getgrnam ();
#endif

#ifdef DECL_STRSTR
extern char * strstr ();
#endif

#endif /* LYNX_CFG_H */
