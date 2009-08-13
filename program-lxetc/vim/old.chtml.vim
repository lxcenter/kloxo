" Vim syntax file
" Language:	chtml PHP 2.0
" Maintainer:	Lutz Eymers <ixtab@polzin.com>
" URL:		http://www.isp.de/data/chtml.vim
" Email:	Subject: send syntax_vim.tgz
" Last change:	2003 May 11
"
" Options	chtml_sql_query = 1 for SQL syntax highligthing inside strings
"		chtml_minlines = x     to sync at least x lines backwards

" For version 5.x: Clear all syntax items
" For version 6.x: Quit when a syntax file was already loaded
if version < 600
  syntax clear
elseif exists("b:current_syntax")
  finish
endif

if !exists("main_syntax")
  let main_syntax = 'chtml'
endif

if version < 600
  so <sfile>:p:h/html.vim
else
  runtime! syntax/html.vim
  unlet b:current_syntax
endif

if version < 600
  so <sfile>:p:h/html.vim
else
  runtime! syntax/c.vim
  unlet b:current_syntax
endif

syn cluster htmlPreproc add=chtmlRegionInsideHtmlTags

if exists( "chtml_sql_query")
  if chtml_sql_query == 1
    syn include @chtmlSql <sfile>:p:h/sql.vim
    unlet b:current_syntax
  endif
endif
syn cluster chtmlSql remove=sqlString,sqlComment

syn case match

" Env Variables
syn keyword chtmlEnvVar SERVER_SOFTWARE SERVER_NAME SERVER_URL GATEWAY_INTERFACE   contained
syn keyword chtmlEnvVar SERVER_PROTOCOL SERVER_PORT REQUEST_METHOD PATH_INFO  contained
syn keyword chtmlEnvVar PATH_TRANSLATED SCRIPT_NAME QUERY_STRING REMOTE_HOST contained
syn keyword chtmlEnvVar REMOTE_ADDR AUTH_TYPE REMOTE_USER CONTEN_TYPE  contained
syn keyword chtmlEnvVar CONTENT_LENGTH HTTPS HTTPS_KEYSIZE HTTPS_SECRETKEYSIZE  contained
syn keyword chtmlEnvVar HTTP_ACCECT HTTP_USER_AGENT HTTP_IF_MODIFIED_SINCE  contained
syn keyword chtmlEnvVar HTTP_FROM HTTP_REFERER contained
syn keyword chtmlEnvVar PHP_SELF contained

syn case ignore

" Internal Variables
syn keyword chtmlIntVar phperrmsg php_self contained

" Comment
syn region chtmlComment		start="/\*" end="\*/"  contained contains=chtmlTodo

" Function names
syn keyword chtmlFunctions  Abs Ada_Close Ada_Connect Ada_Exec Ada_FetchRow contained
syn keyword chtmlFunctions  Ada_FieldName Ada_FieldNum Ada_FieldType contained
syn keyword chtmlFunctions  Ada_FreeResult Ada_NumFields Ada_NumRows Ada_Result contained
syn keyword chtmlFunctions  Ada_ResultAll AddSlashes ASort BinDec Ceil ChDir contained
syn keyword chtmlFunctions  AdaGrp ChMod ChOwn Chop Chr ClearStack ClearStatCache contained
syn keyword chtmlFunctions  closeDir CloseLog Cos Count Crypt Date dbList  contained
syn keyword chtmlFunctions  dbmClose dbmDelete dbmExists dbmFetch dbmFirstKey contained
syn keyword chtmlFunctions  dbmInsert dbmNextKey dbmOpen dbmReplace DecBin DecHex contained
syn keyword chtmlFunctions  DecOct doubleval Echo End ereg eregi ereg_replace contained
syn keyword chtmlFunctions  eregi_replace EscapeShellCmd Eval Exec Exit Exp contained
syn keyword chtmlFunctions  fclose feof fgets fgetss File fileAtime fileCtime contained
syn keyword chtmlFunctions  fileGroup fileInode fileMtime fileOwner filePerms contained
syn keyword chtmlFunctions  fileSize fileType Floor Flush fopen fputs FPassThru contained
syn keyword chtmlFunctions  fseek fsockopen ftell getAccDir GetEnv getHostByName contained
syn keyword chtmlFunctions  getHostByAddr GetImageSize getLastAcess contained
syn keyword chtmlFunctions  getLastbrowser getLastEmail getLastHost getLastMod contained
syn keyword chtmlFunctions  getLastref getLogDir getMyInode getMyPid getMyUid contained
syn keyword chtmlFunctions  getRandMax getStartLogging getToday getTotal GetType contained
syn keyword chtmlFunctions  gmDate Header HexDec HtmlSpecialChars ImageArc contained
syn keyword chtmlFunctions  ImageChar ImageCharUp IamgeColorAllocate  contained
syn keyword chtmlFunctions  ImageColorTransparent ImageCopyResized ImageCreate contained
syn keyword chtmlFunctions  ImageCreateFromGif ImageDestroy ImageFill contained
syn keyword chtmlFunctions  ImageFilledPolygon ImageFilledRectangle contained
syn keyword chtmlFunctions  ImageFillToBorder ImageGif ImageInterlace ImageLine contained
syn keyword chtmlFunctions  ImagePolygon ImageRectangle ImageSetPixel  contained
syn keyword chtmlFunctions  ImageString ImageStringUp ImageSX ImageSY Include contained
syn keyword chtmlFunctions  InitSyslog intval IsSet Key Link LinkInfo Log Log10 contained
syn keyword chtmlFunctions  LosAs Mail Max Md5 mi_Close mi_Connect mi_DBname contained
syn keyword chtmlFunctions  mi_Exec mi_FieldName mi_FieldNum mi_NumFields contained
syn keyword chtmlFunctions  mi_NumRows mi_Result Microtime Min MkDir MkTime msql contained
syn keyword chtmlFunctions  msql_connect msql_CreateDB msql_dbName msql_DropDB contained
syn keyword chtmlFunctions  msqlFieldFlags msql_FieldLen msql_FieldName contained
syn keyword chtmlFunctions  msql_FieldType msql_FreeResult msql_ListDBs contained
syn keyword chtmlFunctions  msql_Listfields msql_ListTables msql_NumFields contained
syn keyword chtmlFunctions  msql_NumRows msql_RegCase msql_Result msql_TableName contained
syn keyword chtmlFunctions  mysql mysql_affected_rows mysql_close mysql_connect contained
syn keyword chtmlFunctions  mysql_CreateDB mysql_dbName mysqlDropDB  contained
syn keyword chtmlFunctions  mysql_FieldFlags mysql_FieldLen mysql_FieldName contained
syn keyword chtmlFunctions  mysql_FieldType mysql_FreeResult mysql_insert_id contained
syn keyword chtmlFunctions  mysql_listDBs mysql_Listfields mysql_ListTables contained
syn keyword chtmlFunctions  mysql_NumFields mysql_NumRows mysql_Result  contained
syn keyword chtmlFunctions  mysql_TableName Next OctDec openDir OpenLog  contained
syn keyword chtmlFunctions  Ora_Bind Ora_Close Ora_Commit Ora_CommitOff contained
syn keyword chtmlFunctions  Ora_CommitOn Ora_Exec Ora_Fetch Ora_GetColumn contained
syn keyword chtmlFunctions  Ora_Logoff Ora_Logon Ora_Parse Ora_Rollback Ord  contained
syn keyword chtmlFunctions  Parse_str PassThru pclose pg_Close pg_Connect contained
syn keyword chtmlFunctions  pg_DBname pg_ErrorMessage pg_Exec pg_FieldName contained
syn keyword chtmlFunctions  pg_FieldPrtLen pg_FieldNum pg_FieldSize  contained
syn keyword chtmlFunctions  pg_FieldType pg_FreeResult pg_GetLastOid pg_Host contained
syn keyword chtmlFunctions  pg_NumFields pg_NumRows pg_Options pg_Port  contained
syn keyword chtmlFunctions  pg_Result pg_tty phpInfo phpVersion popen pos pow contained
syn keyword chtmlFunctions  Prev PutEnv QuoteMeta Rand readDir ReadFile ReadLink contained
syn keyword chtmlFunctions  reg_Match reg_replace reg_Search Rename Reset return  contained
syn keyword chtmlFunctions  rewind rewindDir RmDir rSort SetCookie SetErrorReporting contained
syn keyword chtmlFunctions  SetLogging SetShowInfo SetType shl shr Sin Sleep contained
syn keyword chtmlFunctions  Solid_Close Solid_Connect Solid_Exec Solid_FetchRow contained
syn keyword chtmlFunctions  Solid_FieldName Solid_FieldNum Solid_FreeResult  contained
syn keyword chtmlFunctions  Solid_NumFields Solid_NumRows Solid_Result Sort contained
syn keyword chtmlFunctions  Spundtex Sprintf Sqrt Srand strchr strtr  contained
syn keyword chtmlFunctions  StripSlashes strlen strchr strstr strtok strtolower contained
syn keyword chtmlFunctions  strtoupper strval substr sybSQL_CheckConnect contained
syn keyword chtmlFunctions  sybSQL_DBUSE sybSQL_Connect sybSQL_Exit contained
syn keyword chtmlFunctions  sybSQL_Fieldname sybSQL_GetField sybSQL_IsRow  contained
syn keyword chtmlFunctions  sybSQL_NextRow sybSQL_NumFields sybSQL_NumRows contained
syn keyword chtmlFunctions  sybSQL_Query sybSQL_Result sybSQL_Result sybSQL_Seek contained
syn keyword chtmlFunctions  Symlink syslog System Tan TempNam Time Umask UniqId contained
syn keyword chtmlFunctions  Unlink Unset UrlDecode UrlEncode USleep Virtual contained
syn keyword chtmlFunctions  SecureVar contained

" Conditional
syn keyword chtmlConditional  if else elseif endif switch endswitch contained

" Repeat
syn keyword chtmlRepeat  while endwhile contained

" Repeat
syn keyword chtmlLabel  case default contained

" Statement
syn keyword chtmlStatement  break return continue exit contained

" Operator
syn match chtmlOperator  "[-=+%^&|*!]" contained
syn match chtmlOperator  "[-+*/%^&|]=" contained
syn match chtmlOperator  "/[^*]"me=e-1 contained
syn match chtmlOperator  "\$" contained
syn match chtmlRelation  "&&" contained
syn match chtmlRelation  "||" contained
syn match chtmlRelation  "[!=<>]=" contained
syn match chtmlRelation  "[<>]" contained

" Identifier
syn match  chtmlIdentifier "$\h\w*" contained contains=chtmlEnvVar,chtmlIntVar,chtmlOperator


" Include
syn keyword chtmlInclude  include contained

" Definesag
syn keyword chtmlDefine  Function contained

" String
syn region chtmlString keepend matchgroup=None start=+"+ skip=+\\\\\|\\"+  end=+"+ contains=chtmlIdentifier,chtmlSpecialChar,@chtmlSql contained

" Number
syn match chtmlNumber  "-\=\<\d\+\>" contained

" Float
syn match chtmlFloat  "\(-\=\<\d+\|-\=\)\.\d\+\>" contained

" SpecialChar
syn match chtmlSpecialChar "\\[abcfnrtyv\\]" contained
syn match chtmlSpecialChar "\\\d\{3}" contained contains=chtmlOctalError
syn match chtmlSpecialChar "\\x[0-9a-fA-F]\{2}" contained

syn match chtmlOctalError "[89]" contained


syn match chtmlParentError "[)}\]]" contained

" Todo
syn keyword chtmlTodo TODO Todo todo contained

" Parents
syn cluster chtmlInside contains=chtmlComment,chtmlFunctions,chtmlIdentifier,chtmlConditional,chtmlRepeat,chtmlLabel,chtmlStatement,chtmlOperator,chtmlRelation,chtmlString,chtmlNumber,chtmlFloat,chtmlSpecialChar,chtmlParent,chtmlParentError,chtmlInclude

syn cluster chtmlTop contains=@chtmlInside,chtmlInclude,chtmlDefine,chtmlParentError,chtmlTodo
syn region chtmlParent	matchgroup=Delimiter start="(" end=")" contained contains=@chtmlInside
syn region chtmlParent	matchgroup=Delimiter start="{" end="}" contained contains=@chtmlInside
syn region chtmlParent	matchgroup=Delimiter start="\[" end="\]" contained contains=@chtmlInside

syn region chtmlRegion keepend matchgroup=Delimiter start="#HTML_END" skip=+(.*>.*)\|".\{-}>.\{-}"\|/\*.\{-}>.\{-}\*/+ end="#HTML_BEGIN" contains=@chtmlTop
syn region chtmlRegionInsideHtmlTags keepend matchgroup=Delimiter start="#HTML_END" skip=+(.*>.*)\|/\*.\{-}>.\{-}\*/+ end="#HTML_BEGIN" contains=@chtmlTop contained

" sync
if exists("chtml_minlines")
  exec "syn sync minlines=" . chtml_minlines
else
  syn sync minlines=100
endif

" Define the default highlighting.
" For version 5.7 and earlier: only when not done already
" For version 5.8 and later: only when an item doesn't have highlighting yet
if version >= 508 || !exists("did_chtml_syn_inits")
  if version < 508
    let did_chtml_syn_inits = 1
    command -nargs=+ HiLink hi link <args>
  else
    command -nargs=+ HiLink hi def link <args>
  endif

  HiLink chtmlComment		Comment
  HiLink chtmlString		String
  HiLink chtmlNumber		Number
  HiLink chtmlFloat		Float
  HiLink chtmlIdentifier	Identifier
  HiLink chtmlIntVar		Identifier
  HiLink chtmlEnvVar		Identifier
  HiLink chtmlFunctions		Function
  HiLink chtmlRepeat		Repeat
  HiLink chtmlConditional	Conditional
  HiLink chtmlLabel		Label
  HiLink chtmlStatement		Statement
  HiLink chtmlType		Type
  HiLink chtmlInclude		Include
  HiLink chtmlDefine		Define
  HiLink chtmlSpecialChar	SpecialChar
  HiLink chtmlParentError	Error
  HiLink chtmlOctalError	Error
  HiLink chtmlTodo		Todo
  HiLink chtmlOperator		Operator
  HiLink chtmlRelation		Operator

  delcommand HiLink
endif

let b:current_syntax = "chtml"

if main_syntax == 'chtml'
  unlet main_syntax
endif

" vim: ts=8
