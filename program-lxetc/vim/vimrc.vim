"/************************  Vimrc    **************************/

function! ReMaps()
	nor	H 	I
	nor	I	H
	nor	j	h
	nor	h 	i
	nor	i	gk
	nor	k 	gj
	nor	l	l
	nor	gi	gk
	nor	gk 	gj
	"nor	;	:
	"nor ,   ;
	"nor m   ,
	"nor M   m
	nor w  :call BashSkipWord()<CR>
	ounmap w
	inor <C-w> <C-o>:call DeleteBashWordInsert()<CR>
endfunction

let g:loaded_matchparen = 1


function! UnMapArrow()
	inor <Up> <Nop>
	nor <Up> <Nop>
	inor <Down> <Nop>
	nor <Down> <Nop>
	inor <Right> <Nop>
	nor <Right> <Nop>
	inor <Left> <Nop>
	nor <Left> <Nop>
endfunction


set nocompatible
call ReMaps()
"The smallest workable vimrc, ends here. So when load is troubling get out here
"finish

autocmd!



iab  fdoc 	/** 
				   \* @return void 
				   \* @param 
				   \* @param 
				   \* @desc 
				   \*/ 

set linebreak
let g:list="/bin/ls -AFd [^\\\(audio\\\)]*[^\\\(.o\\\)]"
let g:list="/bin/ls -AF"
let g:dull = 1
let g:hostname = ''
let g:title_set = 0

let g:spell_option = ''
set whichwrap=b,s,<,>,[,],h,l,~
source ~/.etc/vim/vfunc.vim
set path+=/usr/lib/qt-2.2.2/include,~/.etc,~,~/.etc/vim,~/.etc/bin,/usr/local/include,include
set ai sm wmnu splitbelow  hlsearch confirm hidden nosol is
set cin
set ve=block splitright
set ic scs
set previewheight=6
set dir=~/.etc/.tmp,.,/tmp
set ls=2
set ts=4 sw=4
set wim=full,list
set ssop=buffers,winsize
set ruf=%1*%l%*%3c%2m[%n]\ %<%{expand('%:r')}
set guioptions=m
set isfname-==
set isfname+=:
let g:gdb_cwindow = 0
let g:gdb_busy_sign = 'Busy:'
set notitle
set showbreak=\|
set noshowfulltag
set sbo+=hor
set ttymouse=xterm

set clipboard=unnamed
"set includeexpr=substitute(v:fname,'^/','./','g')

if has("terminfo")
  set t_Co=16
  if (match($OSTYPE, "interix") != -1 || match($OS, "Windows") != -1)
	  set t_AB=[%?%p1%{8}%<%t%p1%{40}%+%e%p1%{92}%+%;%dm
	  set t_AF=[%?%p1%{8}%<%t%p1%{30}%+%e%p1%{82}%+%;%dm
  else 
	 " set t_AB=[%?%p1%{8}%<%t%p1%{40}%+%e%p1%{92}%+%;%dm
	  "set t_AF=[%?%p1%{8}%<%t%p1%{30}%+%e%p1%{82}%+%;%dm
  endif
else
  set t_Co=16
  set t_Sf=[3%dm
  set t_Sb=4%dm
endif

function! SetStl()
	if (g:stl_b =='')
		exe 'set stl='. g:stl_a . g:stl_b . g:stl_c 
	else
		exe 'set stl=' .g:stl_a. '\ [' .g:stl_b. ']\ ' .g:stl_c
	endif
endfunction

" We have this in two places... Must remove one.
function! HtmlIncludeExpr(a)
	let ret = substitute(a:a,'^/','','')
	let ret = substitute(ret,'/$','/content.html', '')
	let ret = substitute(ret,'^\(.\):','/\1/', '')
	return ret
endfunction

set includeexpr=HtmlIncludeExpr(v:fname)

function! VirtualMove()
	nor	i	gk
	nor	k	gj
	nor	<Up>	g<Up>
	nor	<Down>	g<Down>
	ino	<M-l>	<C-o>l
	ino	<M-j>	<Left>
	ino	<Up>	<C-o>g<Up>
	ino	<Down>	<C-o>g<Down>
	nor	<F50>l	g$
	nor	<F50>h	g0
	"call UnMapArrow()
endfunction


function! FolderStl()
	let val = expand("%:h:t")

	if g:mystl_val == 1
		return  val
	endif

	let tmp = expand('%:h:h:t')
	if tmp == '' 
		return val
	endif
	let val = tmp . '/' . val

	if g:mystl_val == 2
		return val
	endif

	let tmp = expand('%:h:h:h:t')
	if tmp == ''
		return val
	endif
	let val = tmp. '/' . val
	return  val
endfunction


if strlen($SSH_TTY) || strlen($SSH_CLIENT)
	let g:hostname = system('hostname')
	 let g:hostname = substitute(g:hostname, '\(\w*\).*', '\1', '')
	let g:hostname = substitute(g:hostname, '\..*', '', ''). ':'
endif

function! GetHostName()
	if strlen($SSH_TTY)
		return substitute(hostname(), '\..*', '', ''). ':'
	endif
	return ''
endfunction

function! MyStl(...)
	if a:0 == 0
		let g:mystl_val = 1
	else
		let g:mystl_val = a:1
	endif
	let g:stl_a='%4*[%2*%n%4*]\ %3*%{expand(\"%:t\")}\ %2*%m\ %4*%<%0(______________________________%)%3*%{FolderStl()}%2*\ %='
	let g:stl_b=''
	let g:stl_c='%4*[%3*%{g:hostname}%2*/%*%{fnamemodify(getcwd(),\":t\")}\ %2*%{$ps1}%4*\ %2*%3c]%2*\ %*%4l'
	call SetStl()
endfunction


call MyStl(3)

set scrolloff=3
set wig=*.o
set guifont=10x20
set hi=100
set shm=aIoOtT
set wcm=<C-Z>
set tm=1000
set bs=2
set history=500
"set makeprg=if\ make\ \$\*\ ;then\ .ea;fi
set grepprg=lid\ -R\ grep
"set tags=./.tags,.tags,./TAGS,tags,TAGS,tagfile,../tagfile
set dict=~/.etc/vim/cdict
set rtp+=~/.etc/vim
set noequalalways
set foldmethod=marker
set nofoldenable
set showcmd
set ttimeout notimeout
set lazyredraw
set sidescroll=1


function! SetViminfo()
	set viminfo='20,h,!,rgdb,n~/.etc/vim/.tmp/.viminfo
endfunction
call SetViminfo()

syntax on
"au  CursorHold * call UpdateVars()


function! VimLeave()
	if ($VIM_PSE == "v")
		"call system('screen -p ' .$WINDOW. ' -X  title "Vim-Exit: ' . GetDirHome() . ' "')
	endif
endfunction

function! ChtmlEnter()
	source ~/.etc/vim/vhtmlab.vim
	set ft=c
	"map <buffer> <M-1> :call append('.', "#HTML_BEGIN")<CR>
	"map <buffer> <M-q> :call append('.', "#HTML_END")<CR>
	vmap <buffer> <M-A> :call FixCprint()<CR>
endfunction

function! FixCprint()
	call RemoveCprint()

	if line('.') == line("'>")
		let line = line("'<")
		let line = line - 1
		if (&ft == 'php')
			call append(line, "?>")
			call append("'>", "<?")
		else
			call append(line, "#HTML_BEGIN")
			call append("'>", "#HTML_END")
		endif
	endif
endfunction

au BufEnter *.chtml call ChtmlEnter()
au BufRead elance_list.tmp call ElanceStart()
au BufNewFile *.ctr call CtrNew()
au BufEnter *.ctr call CtrEnter()
au BufRead *.wget call WgetRead()
au BufRead,BufNewFile *.dart call DartRead()
au  VimEnter * call VimEnter()
au VimLeave * call VimLeave()
au	BufRead *.{M,m}ake* set ft=make
au  BufReadPost * if line("'\"") | silent! exe "normal '\"" | endif
au  BufEnter * call AllEnter()
au  BufEnter *.mnu	call MenuEnter()
au  BufNewFile *.qpr call QprReadNew()
au 	BufRead *.qpr call QprReadPre()
au 	BufEnter *.qpr call QprEnter()
au  BufRead *.prf call ProblemReportRead()
au  BufEnter *.prf call ProblemReportEnter()
au  BufHidden *.prf call ProblemReportQuit()
au  BufLeave *.mnu	call MenuLeave()
au  BufEnter *.out	call OutEnter()
au  BufLeave *.out	call OutLeave()
au  BufEnter *.{art}	call ArticleEnter()
au  BufLeave *.{art}	call ArticleLeave()
au  BufRead /tmp/mutt*	call MuttEnter()
au  BufRead *.w3m/w3mtmp*	call MuttEnter()
au  BufRead /tmp/*/L*	call LynxEnter()
au  BufRead	/tmp/mutt*	call MuttRead()
au  BufEnter	/tmp/elinks*	call MuttEnter()
au  BufRead	linksarea-*	call MuttEnter()
au  BufRead *dict/*.dict/* call DictEnter()
au  BufRead *dict/*.lxl call DictEnter()
au BufRead *.mll call MlistEnter()
au BufRead,BufNewFile *.php* call PhpEnter()
au BufRead,BufNewFile *.inc* call PhpEnter()
au CmdWinEnter * call CmdWinEnter()

au  BufEnter /tmp/mutt* call ArticleEnter()
au  BufReadPost .gt_data $
au  BufReadPost *.gdb  call AsmFilter()
au  BufRead,BufEnter *.py set ts=8
au  BufNewFile *.h call HNewFile()

au BufEnter .vspell.err call SpellListEnter()
"au BufLeave .vspell.err call SpellListLeave()

au BufRead *.meln call MelnRead()
au BufRead main-list.meln call MainMelnRead()

"au  BufRead .config call KernelEnter()


au  BufEnter *dirstack call DirStackEnter()
au  BufLeave *dirstack call DirStackLeave()

au  FileChangedShell Playlist. e! ~/.etc/VimAmp/Playlist.
au  FileChangedShell .plot  bd .plot
au  FileChangedShell *.[sg]db  call AsmRefresh()
au  BufNewFile,BufRead *.[sS]  set filetype=c

au  FileChangedShell Manual. call ManualChange()
au  Bufread ~/logiX/src/* set tags=/mnt/back/minix_tags
au  Bufread ~/logiX/src/* set path+=~/logiX/include
au  Bufnewfile,Bufread,BufAdd *.{[ch],cc,py,cpp} so ~/.etc/vim/vcpp.vim
au  Bufnewfile,Bufread Manual.  normal gg2
au  BufEnter Vimlist.	call ListEnter()
au  WinEnter,BufEnter Vimlist. call IswitchbInit()
au  BufLeave Vimlist.	call ListLeave()
au  BufEnter Manual. call DotManEnter()
au  BufEnter  master_dbase.lir call MasterEnter()
au  BufLeave  master_dbase.lir call MasterLeave()
au  BufEnter  master_word.dctf call MasterWordEnter()
au  BufEnter  roget.ths call DictEnter()
au  BufEnter  nsword call NsWordEnter()
au  BufEnter  .vlist call DirStart()
au  BufLeave  .vlist call DirEnd()
au  BufNewFile a.{cc,c} 0r ~/.etc/vim/cskeleton.cc
au  BufNewFile timidity.cfg.* call TimidityCnf()
au  BufNewFile t.cc 0r ~/.etc/vim/cskeleton.cc
au  BufRead,BufNewFile,BufEnter *.{htm?,shtm?,js,css,htm,cgi,pl} so ~/.etc/vim/vhtml.vim
au  BufEnter .gt_data	call GdbDataEnter()
au  BufLeave .gt_data	call GdbDataLeave()
au  BufEnter *.ptx	call PdfEnter()
au  BufEnter *.java	set tags+=/usr/java/jdk1.3.0_02/src/tags
au  BufLeave *.java	set tags-=/usr/java/jdk1.3.0_02/src/tags


source ~/.etc/vim/multi-value.vim
source ~/.etc/vim/vbfnc.vim
source ~/.etc/vim/vmaps.vim
source ~/.etc/vim/vaucmd.vim
source ~/.etc/vim/vswitchb.vim
source ~/.etc/vim/taglist.vim
source ~/.etc/vim/ide.vim
	source ~/.etc/vim/vfmap.vim
	source ~/.etc/vim/vsmap.vim
	source ~/.etc/vim/vmmap.vim
	source ~/.etc/vim/vzmap.vim
source ~/.etc/vim/vcols.vim
source ~/.etc/vim/info.vim
source ~/.etc/vim/cmdline-complete.vim
"source ~/.etc/vim/search-compl.vim



if filereadable(expand('~/.etc/vim/vtmp.vim'))
		source ~/.etc/vim/vtmp.vim
endif

if filereadable(expand('~/.etc/vim/vcyg.vim'))
	source ~/.etc/vim/vcyg.vim
endif

call VirtualMove()
"source ~/.etc/vim/spellcheck.vim
"******* This should come last ******

"source ~/.etc/vim/vmeta.vim
if(&t_ti=="")
	hi linenr	cterm=none 
	"source ~/.etc/vim/vmeta
else
	set <Del>=[3~
endif

if has("win32")
source ~/.etc/vim/vwin
endif

com! -nargs=? Mystl call MyStl(<f-args>) 
"**********************  Vimrc *************************

"* ~/.etc/vim/vcpp  *
"* ~/.etc/vim/vfunc *
"* ~/.etc/vim/vimrc *
"* ~/.etc/vim/vmaps *
"* ~/.etc/vim/vmeta *
"* ~/.etc/vim/vwin  *
"* ~/.etc/vim/vtmp  *
"* ~/.etc/vim/vcols *
