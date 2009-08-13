hi Delimiter ctermfg=brown
hi phpParent ctermfg=4
source ~/.etc/vim/syntax/html.vim

syn keyword phpFunctions dprint dprint_r lsystem dprintr lfile lfile_get_contents lsql_open lfile_put_contents lxshell_return lxfile_cp lxfile_cp_rec csb contained
syn keyword phpStructure __construct exception lxexception this
syn match	phpIdentifier	"$this"	contained contains=phpThis display
syn match	phpIdentifier	"$gbl"	contained contains=phpGlobal display
syn match	phpIdentifier	"$sgbl"	contained contains=phpGlobal display
syn match	phpIdentifier	"$login"	contained contains=phpGlobal display
syn match	phpIdentifier	"$ghtml"	contained contains=phpGlobal display
syn keyword phpThis $this
syn keyword phpGlobal $gbl $sgbl $login $ghtml
hi phpMemberSelector ctermfg=6
hi phpMethodsVar ctermfg=10
hi phpidentifier ctermfg=2
hi phpStorageClass ctermfg=6
hi phpType ctermfg=6
hi phpStructure ctermfg=14
hi phpThis ctermfg=3
hi phpGlobal ctermfg=15
