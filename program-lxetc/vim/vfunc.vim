"*************************  Vfunc   ******************************

let g:spell_wlist = ''
let g:spell_cword = ''
let g:qpr_read = 0
let g:spell_single = 1
let g:gdb_attach = 0
let g:g_php_help = 0

let g:gdb_do_tfs = 0

function! UpdateVars()
	"let @s = expand('%:t'). ':' . line('.')
endfunction



function! IncrementVersion()
	vi lib/sgbl.php
	normal gg
	call search("ver_release")
	normal 10
	write
endfunction

function! GetCWordOld()
	let old = col('.')
	let first = expand('<cword>')
	normal! el
	let c1 = GetCharUnderCursor()
	normal! l
	let c2 = GetCharUnderCursor()
	let c = c1 . c2
	if (match(c, "->") != -1)
		normal ll
		let second = expand('<cword>')
		call cursor(0, old)
		return first . '->' . second
	else
		call cursor(0, old)
		return first
	
	endif

endfunction

function! GetCWord()
	let var = &isk
	if (bufname('%') == bufname('.gt_data'))
		set isk+=>,-,[,],',"
	else
		set isk+=>,-
	endif

	let first = expand('<cword>')
	let &isk = var
	return first
endfunction



function! BufNameNoCwd()
  let fname = expand('%:p')
  let fname = substitute(fname, getcwd(). '/', '', '')
  return fname
endfunction

function! ReadInclude()
	let f = getline('.')
	let f = substitute(f, '#include <\(.*\)>', '\1', '')
	exe 'r ' . f
endfunction

function! PhpEnter()
	set ft=php
	let $VIM_GDBCMD = "xdebug.php"
	call ArticleAbbr()
"let $VIM_GDBCMD = "debugclient"
	"source ~/.etc/vim/vhtmlab.vim
	source ~/.etc/vim/php-indent.vim
	vmap <buffer> <M-A> :call FixCprint()<CR>
	source ~/.etc/vim/syntax/php.vim
	call MapGrep()
	"set omnifunc=phpcomplete#CompletePHP
	setlocal isk+=$
	ab xd xdebug_break();

	map <silent>	<M-c>	:cf .php.err<CR>
	imap <silent>	<M-c>	<C-C>:cf .php.err<CR>
endfunction

function! GdbEnterFunction()
	call GdbScrollCall("step")
	return
	if (g:gdb_attach)
		exe 'Ide b ' . expand('<cword>')
		Ide c
	else
		call GdbScrollCall("step")
	endif
endfunction

command! -nargs=1 CvsDiff call CVSdiff("<args>")
function! CVSdiff(cvsversion)
  " append a:filename to keep extension and therefore highlighting mode
  let fname = BufNameNoCwd()
  let patchname = tempname()
  let tempname  = tempname()
  let newname   = tempname() . '.cdv.tmp.' . expand('%:t:e')
  silent! execute "!cvs diff -a -r " . a:cvsversion . " " . fname . " > " . patchname
  if getfsize(patchname)
  	silent! execute "!cp " . fname . " " . tempname
  	silent! execute "!patch -R -o " . newname . " " . tempname . " < " . patchname
  	execute "vertical diffsplit " . newname
  	call delete(tempname)
	normal 
  else
   echo "File Up to Date"
  endif

  call delete(patchname)
endfunction

function! WarnEdit(name, flag)
	set vi=
	exe 'normal /^' . a:name . ""
	normal 0
	exe 'normal /\d ' 
	exe 'normal r' . a:flag
	exit
endfunction

au BufHidden *.cdv.tmp.* call CdvHidden()

function! CdvHidden()
	if expand('%') == bufname('.cdv.tmp.')
  		call delete(expand('%'))
		bw!
		set nodiff 
		set foldcolumn=0
		set wrap
	else
		call input('error inside cdvhidd.')
	endif
endfunction!

function! CvsLastDiff()
		let fname = BufNameNoCwd()
		let status = system('cvs status ' .fname)
		let ver = substitute(status, "\n", '-', 'g')
		let ver = substitute(ver, '\_.*\(Repository revision:[^-]*\)\_.*', '\1', '')
		let ver = substitute(ver, 'Repository revision:\s\+\(\S*\)\s\+.*', '\1', '')
		call CVSdiff(ver)
endfunction

function! ShellFixVimRun()
	if (&ft == 'php')
		exe '!vim -E -c "call FixVimRunPhp(\"' .BufNameNoCwd(). '\")" -c x Makefile'
	else
		exe '!vim -E -c "call FixVimRun(\"' .BufNameNoCwd(). '\")" -c x Makefile'
	endif
endfunction


function! FixVimRunPhp(filename)
	normal! gg
	call search("^vim_run:")
	normal k
	exe 's+\([^/]*\)/.*+\1/' . a:filename . '+'
endfunction

function! FixVimRun(filename)
		if (match(a:filename, '/src/') == -1)
			echo a:filename. ': the file is not executable'
			return
		else
			let fname = substitute(a:filename, '/src/', '/', 'g')
			let fname = substitute(fname, '.cpp$', '', 'g')
		endif
		exe 'g/^vim_run: /normal kddio	' . fname
endfunction

function! ExecShellWithDir()
	:exe '!zsh -c "cd \"' . expand('%:h') . '\" ; zsh "'
endfunction


function! AllEnter()
	let $VIM_PS=expand("%:t:r")
endfunction

function! ProblemReportRead()
		set vi=
		silent! %s/^From-To:.*$/&:/
		silent! %s/^By://g
		silent! g/^From-To:/normal J
		silent! %s/^From-To: //g
		"silent! g/^Reason:/d
		normal z-
		write
		if bufname('%') == bufname('pr-audit')
			$
		endif
endfunction

function! PRStatus()
		exe 'set stl=%4*[%2*%n%4*]\ %3*%{expand(\"%:t\")}\ %2*%m\ %4*%<%0(______________________________%)%3*%2*\ %=%*[' .g:pr_state. ']'
endfunction

function! ProblemReportEnter()
		hi mStateChange ctermfg=cyan
		hi mPRKeyword ctermfg=DarkCyan
		hi mHours ctermfg=darkgreen
		syntax match mStateChange '.*->[^:]*:' 
		syntax match mHours 'NHours:'
		call ArticleEnter()
		let g:pr_state=system("cat /tmp/$PRCLIENT-$PRNUM.prfs")
		let g:pr_state=escape(g:pr_state, ' ')
		let g:pr_state=substitute(g:pr_state, "\n", "", "g")
		call PRStatus()
		call PRMap()
		map q :qall!<CR>
		map <C-d><C-d> :wq<CR>
endfunction

function! PRMap()
	map <F50>cf :let g:pr_state='feedback'<CR>:call PRStatus()<CR>
	map <F50>ca :let g:pr_state='analyzed'<CR>:call PRStatus()<CR>
	map <F50>cc :let g:pr_state='closed'<CR>:call PRStatus()<CR>
	map <F50>co :let g:pr_state='open'<CR>:call PRStatus()<CR>
endfunction

function! ProblemReportQuit()
		silent exe '!echo ' .g:pr_state. ' > /tmp/$PRCLIENT-$PRNUM.prfs'
		qa
endfunction

function! QprReadNew()
		normal mt
		1,$d_
		exe 'r !qprs -d $PRCLIENT'
		normal 't
endfunction

function! QprReadPre()
		1,$d_
		exe 'r !qprs -d $PRCLIENT'
endfunction

function! OpenPr()
		let l = getline('.')
		if match(l, "^#") == -1
			call search('^#', "bW")
		endif
		normal 0
		call search('\d', 'W')
		let pr_num=expand('<cword>')
		let n = line('.')
		let n = n + 1
		let pr_state = getline(n)
		let pr_state = substitute(pr_state, '^\S\+\s\+\(\S\+\).*', '\1', 'g')
		exe '!epr ' .$PRCLIENT. ' ' .pr_num. ' ' .pr_state
endfunction

function! QprEnter()

		map <M-w> :call OpenPr()<CR><CR>
		map <M-a> <M-w>
		map q :q!<CR>
		%s/\[:date:\(.*\):.*/[\1]/g
		write
		hi QprPriHigh ctermfg=Magenta
		hi QprNum ctermfg=DarkGreen
		hi QprPriMedium ctermfg=Cyan
		hi QprPriLow ctermfg=Green
		hi QprTime ctermfg=DarkCyan
		syntax match QprNum '^# \d\+:'
		syntax match QprPriMedium '^medium'
		syntax match QprPriLow '^low'
		syntax match QprPriHigh '^high'
		syntax match QprTime '\s\d\d:\d\d'
endfunction

function! VimEnter()
	if(winnr()!=1)
		return
	endif
	if(isdirectory(expand("%")) && argc()==1)
		exe "silent !touch " . expand('~') . "/.etc/.tmp/Browser."
		exe ":cd " . expand("%")
		vi ~/.etc/.tmp/Browser.
		call BrowserEnter()
		"bd! 1
	endif
	if (!g:title_set && !strlen($SVIM_TITLE))
		call system("set_screen_title 'Vim: " . GetDirHome() . " - "  .expand('%:~') . "'")
	endif
	"let $VIM_PSE= $VIM_PSE . "\e[01;36mv\e[00m"
	let $VIM_PSE= $VIM_PSE . "v"
endfunction

function! GetDirHome()
	let l = getcwd()
	let l = substitute(l, expand('~'), '~', '')
	return l
endfunction

function! LocalMachine()
	if (match(hostname(), "lxlabs.com") != -1)
		return 1
	else
		return 0
	endif
endfunction

function! CreateFunctionDefinition()
		!echo -e "#ifndef _LXFUNCTION_H_\n#define _LXFUNCTION_H_\n" > lxfunctions.h
		g/^{/normal w!
endfunction

function! ReplaceTemplate()
		let a = expand('%:p:r')
		let a = substitute(a, '/home/.*\.com/html', '', '')
		exe '%s+/template/template.html+' .a. '.html+'
		exe 'silent! %s+/template/template_right.html+' .a. '_right.html+'
endfunction

function! PdfEnter()
	set nowrap
	set sidescroll=1
endfunction

function! KernelEnter()
	set stl=-------------------------------------------------------------
	sp Documentation/Configure.help
	res 1
	wincmd w
	hi StatusLine ctermfg=darkcyan
	noremap <silent>  <Up>  <Up>:call KernelGetName()<CR>
	map <silent> i  <Up>
	map  <silent>  k  <Down>
	noremap <silent> <Down> <Down>:call KernelGetName()<CR>
	map <M-a> :call KernelYes()<CR>
	map <M-n> :call KernelNo()<CR>
	map <M-m> :call KernelModule()<CR>
endfunction

function! PutInLynx()
	let l = expand('<cfile>')
	exe '!scpaste aamain 4 "g' .l. '"'
endfunction

function! KernelYes()
	let s = getline('.')
	let s = substitute(s, '=n', '=y', '')
	let s = substitute(s, '=m', '=y', '')
	let s = substitute(s, '# \(\S*\).*', '\1=y', '')
	call setline(line('.'), s)
endfunction

function! KernelNo()
	let s = getline('.')
	let s = substitute(s, '=y', '=n', '')
	let s = substitute(s, '=m', '=y', '')
	call setline(line('.'), s)
endfunction

function! KernelModule()
	let s = getline('.')
	let s = substitute(s, '=y', '=m', '')
	let s = substitute(s, '=n', '=m', '')
	let s = substitute(s, '# \(\S*\).*', '\1=m', '')
	call setline(line('.'), s)
endfunction

function! KernelGetName()
	let s = getline('.')
	let s = matchstr(s, '\w\+_\w\+')
	if s == '#' || s == ''
		return
	endif
	call ColorCurrentBrows()
	"call input(s)
	wincmd w
	let v:errmsg = ''
	exe 'silent! /^\<' . s . '\>'
	if v:errmsg != ''
		echohl StatusLine
		exe '/^\s*$'
		call ColorCurrentBrows()
		normal z-
		echo 'No Help Available'
		echohl None
		wincmd w
		return
	endif
	echo ''
	noremap K <Up>
	normal K
	"hi Current ctermfg=Red
	call ColorCurrentBrows()
	wincmd w
endfunction

function! CreateBrowser()
	let g:not_standalone = 1
	if !exists('browser_file')
		let brsf = substitute(getcwd(), '/', '_', 'g')
		let g:browser_file = '~/.etc/.tmp/Browser.' .brsf
	endif
	let ht = winheight(0) - 2
	exe ht . 'sp ' .g:browser_file
endfunction

function! ToggleStl(flag, val)
	if(exists(a:flag))
		let g:stl_b=ClearVar(g:stl_b, a:val)
	else
		let g:stl_b = g:stl_b. a:val
	endif
	call SetStl()
endfunction

function! RestoreHeight()
	if(exists('g:list_winheight'))
		exe "resize " .  g:list_winheight
		unlet g:list_winheight
	endif
endfunction

function! ToggleWindow()
	let winht = winheight(0)
	if(exists("g:win_old_height"))
		silent wincmd k
		exe "resize " . g:win_old_height
		unlet g:win_old_height
	else
		silent wincmd j
		let g:win_old_height = winht
		let  ht = winht + winheight(0) - 1 
		exe "resize " . ht
	endif

	if bufname('%') == bufname('.gt_data') && line('.') == line('$')
		normal z-
	endif
endfunction

function! GetFileFromList()
	let var = getline('.')
	let mx = '^[^"]\+"\([^"]\+\)".*'
	let var = substitute(var,mx,'\1','')
	return var
endfunction

function! ListProcess(split)
	let var = GetFileFromList()
	let prevf = expand('#')
	let g:position = line('.')
	bd! ~/.etc/.tmp/Vimlist.
	if (a:split == "s")
		exe "sb " . var
	elseif(a:split == "v")
		exe "vs " . var
	elseif(a:split == "d")
		exe "bd " . var
		call ListBuf()
		exe g:position
		call ColorCurrentList()
	elseif(a:split == "l")
		if match(prevf, expand(var)) != -1
			exe 'b ' .g:list_bfile
		endif
		exe "buffer " . var
	endif
	"call RestoreHeight()
endfunction

function! ColorFileInList()
	hi FileName ctermfg=DarkCyan
	syntax match FileName '".*"'
endfunction

function! ListMarks()
	exe "redir! > " . expand('~') . "/.etc/.tmp/Vimark."
	let g:list_bfile=expand('#')
	silent marks
	redir END
	let ht = winheight(0)-2
	exe ht . "sp ~/.etc/.tmp/Vimark."
endfunction

function! MarkEnter()
endfunction



function! InitBufList()
	if exists("g:buf_list")
		return
	endif
	let i = 1
	let n = bufnr('$')
	let g:buf_list = ""
	while  i <= n
		let g:buf_list = MvAddElement(g:buf_list, ",", bufname(i))
		let i = i + 1
	endwhile
endfunction


function! ListBuf()
	"exe "redir! > " . expand('~') . "/.etc/.tmp/Vimlist."
	let this_file = bufname('%')
	let g:list_bfile=expand('#')
	let ht = winheight(0)-2
	exe ht . "sp ~/.etc/.tmp/Vimlist."
	1,$d_
	"let g:buf_list =  MvRemovePattern(g:buf_list, ',', ".*Vimlist\.")
	let g:buf_list = MvPullToBack(g:buf_list, ",", this_file)
	call append('0', g:buf_list)
	%s/,//g
	g/Vimlist/d_
	1
	w!
	let g:buf_list = MvPushToFront(g:buf_list, ",", this_file)
	call ColorCurrentBrows()
endfunction




function! ListQuit()
	let curf=expand('#')
	bd
	if match(g:list_bfile , 'Browser.') == -1
		exe 'b ' . g:list_bfile
	endif
	exe 'b ' . curf
	unlet g:list_bfile
endfunction



function! ListEnter()
	"setlocal nu
	"hi Current ctermfg=Darkred
	"set nobuflisted
	setlocal modifiable
	let curf=bufnr('#')
	silent g/\.gt_/d_
	silent g/^$/d_
	call histdel("/", -1)
	silent g/No File/d_
	call histdel("/", -1)
	silent g/Browser\./d_
	call histdel("/", -1)
	"silent 1d _
	exe 'silent! %s#' .getcwd(). '/##'
	silent update
	exe 'silent! /^\s*' . curf . ' '
	call histdel("/", -1)
	call ColorCurrentList()
	set bufhidden=delete
	set buftype="nofile,nowrite"
	set noswapfile
	"call ColorFileInList()
	hi Percent ctermfg = Brown
	syntax match Percent '[%+]'
	call ColorMoveMap('Brows')
	map <buffer> <C-l>	  :call ListProcess("l")<CR>
	map <buffer> <C-s>	  :call ListProcess("s")<CR>
	map <buffer> <C-v>	  :call ListProcess("v")<CR>
	map <buffer> <C-f>	  :Buffer<CR>
	map <buffer> <C-d>	  :call ListProcess("d")<CR>
	nor <buffer> <C-q>    :call ListQuit()<CR>
	map <buffer> <C-j>	  <C-q>
	map <buffer> <C-m> <C-l>
endfunction

function! ListLeave()
	call IswitchbLeave()
	"syntax clear Percent Curfile
endfunction


function! Test()
	let mx='\s*\(\S\+\)\s*\(.*\)'
	echo "gdb var" g:gdb_var
	let g:gdb_var=matchstr(g:gdb_var,mx)
	while(g:gdb_var!='')
		let var=substitute(g:gdb_var,mx,'\1','')
		let g:gdb_var=substitute(g:gdb_var,mx,'\2','')
		echo var
	endwhile
endfunction


let www_client = "lynx"
let X_www_client = "xterm -e ".g:www_client
let mail_client = "mutt"
let X_mail_client = "xterm -e ". g:mail_client

let url_regexp='\v((http|ftp):\/\/)?(\a[A-Za-z0-9\-]*\.)+\a\w+(\/[A-Za-z0-9\-~\.]+)*\/?'
let mail_regexp='\v[A-Za-z0-9\-\.]+\@(\a[A-Za-z0-9\-]*\.)+\a\w+'

function! Followlink()
        let text = expand("<cWORD>")
        let mail = matchstr(text, g:mail_regexp)
        if mail != ""
                if has("gui_running")
                        let command = g:X_mail_client ." ". mail
                else
                        let command = g:mail_client ." ". mail
                endif
                execute "!" command
                return
        endif
        let url = matchstr(text, g:url_regexp)
        if url != ""
                if has("gui_running")
                        let command = g:X_www_client ." ". url
                else
                        let command = g:www_client ." ". url
                endif
                execute "!" command
                return
        endif
        normal! ^M
endfunction

"nnoremap <cr> :call Followlink()<CR>

function! QuickFixEnter()
	if (&buftype=="quickfix")
		hi search ctermfg=none
		map	<M-w>	<C-w>_
	endif
endfunction

function! GetArrayPos()
	let arrpos = 0
	let pos = line('.')
	while match(getline('.'), '{{') == -1
		if (match(getline('.'), '}, {') != -1)
			let arrpos = arrpos + 1
		endif
		-1
	endwhile
	exe pos
	return arrpos
endfunction
			
	

function! GetMiddleVariable(var)
	let s = getline('.')
	if match(s, '}, {') != -1
		let g:arraypos = GetArrayPos()
		return a:var
	endif
	if match(s, '{{') != -1
		let s = substitute(s, '\s*\(\S*\)\s*=\s*{{', '\1', '')
		if g:arraypos == -1
			let g:arraypos = 0
			return a:var
		endif
		let s = s . '[' .g:arraypos. ']'
		let g:arraypos = 0
	else
		let s = substitute(s, '\s*\(\S*\)\s*=\s*{', '\1', '')
	endif
	let s = s. '.' .a:var
	return s
endfunction

function! GdbMyAttach()

	if (&ft == 'php') 
		"let g:gdb_pending = 'bstart'
		call system("killall -18 dbgclient")
		return
	endif

	let g:gdb_sync = 1
	call GdbAttach()

	if g:gdb_do_tfs 
			Ide fs
			Ide fs
	endif

	let g:gdb_sync = 0

	let g:gdb_attach = 1
endfunction

function! SwitchToGdbWindow()
	if (bufname('%') == bufname('.gt_data'))
		vertical resize 30
		normal j
	else 
		normal l
		vertical resize +80
	endif
endfunction



" Function to take gdb to a certain point. First u have to jump to the dummy_debug_function, which makes sure that all the classes get loaded. Then u can issue the real break command.
function! GdbPhpReachHere()
	let g:gdb_sync = 1
	let filename = bufname('%')
	Ide bstart
	Ide break dummy_debug_function
	Ide c
	exe 'b ' . filename
	exe "Ide break " .expand("%:p").":".line('.')
	Ide c
	let g:gdb_sync = 0
endfunction


function! GdbFullVar(data, flag)
	let g:arraypos = -1
	if bufname('%') != bufname('.gt_data')
		call GdbCall("p", a:flag.a:data)
		return
	endif
	"Just call the variable directly... This is for php.
	call GdbCall("p", a:flag.a:data)
	return

	let position = line('.')
	exe position
	let mvar = ''
	?Gdb>
	let gdb_pos = line('.')
	exe position
	normal [{
	while line('.') != gdb_pos + 1
		let mvar = GetMiddleVariable(mvar)
		normal [{
	endwhile
	let mvar = GetMiddleVariable(mvar)
	let mvar = a:flag. mvar. a:data
	exe position
	call GdbCall("p", mvar)
endfunction

function! WinsizeFile()
	let lines = line('$')
	let lines = lines + 1
	if(lines>12)
		resize 12
	else
		exe "resize " . lines
	endif
endfunction

function! HexvalMapToggle()
	call ToggleStl('g:hexval_map', '0x%B:%o:%V:')
	if(exists("g:hexval_map"))
		unlet g:hexval_map
	else
		let g:hexval_map = &stl
	endif
endfunction


function! WindowMapToggle()
	call ToggleStl('g:window_map', 'W:')
	if(exists("g:window_map"))
		call WindowUnMap()
		unmap gs
		unlet g:window_map
	else
		let g:window_map = &stl
		call WindowMap()
	endif
endfunction

function! SetScrollOther()
	let g:scroll_other = winnr()
	silent wincmd p
endfunction

function! ScrollOther(val)
	if exists('g:scroll_other')
		exe 'silent! ' .g:scroll_other . ' wincmd w'
	else
		silent! wincmd p
	endif
	exe 'silent! ' a:val
	normal zz
	silent! wincmd p
endfunction

function! CmdWinEnter()
	imap <buffer> <C-c> <Esc>
endfunction

function! ScrollMap()
	map d	:call ScrollOther("+1")<CR>
	map e	:call ScrollOther("-1")<CR>
endfunction

function! ScrollUnMap()
	"map s <F50>
	"unmap f
	unmap d
	nor e	 ge
endfunction


function! ScrollMapToggle()
	call ToggleStl('g:scroll_map', 'S:')
	if(exists("g:scroll_map"))
		call ScrollUnMap()
		unlet g:scroll_map
	else
		let g:scroll_map = &stl
		call ScrollMap()
	endif
endfunction



function! DialNumber()
	let val = expand("<cword>")
	silent vi ~/.etc/net/ppp/dial
	exe '%s/ATDT.*/ATDT' . val . '"'
	update
	silent e #
	silent !pppd call dial
endfunction



function! QuickFixLeave()
	if (&buftype=="quickfix")
		hi search ctermfg=DarkGreen
	endif
endfunction

function! SpaceMap()
	if (exists("g:mspace"))
		unmap <space>
		unlet g:mspace
	else
		nor <space> <C-f><C-E><C-E>
		let g:mspace=1
	endif
endfunction


function! CtrEnter()
	call ArticleEnter()
	set vi=
	"silent! %s/\[\d\+m//g
"write
	syntax match MyName "\[msg(.*)\]"
	syntax match MyName "\[(.*)\]"
	syntax match Othername "\*\*\[.*()\]"
	syntax match ChInfo "תשת.*"
	syntax match MChTalk "(.*)"
	syntax match PTime "\[\d\+:\d\+.M\]"
	syntax match ChTalk "<.*>"
	syntax match StartIrcLog "IRC log started.*"
	hi StartIrcLog ctermfg=darkgreen
	hi MyName ctermfg=darkyellow
	hi Othername ctermfg=darkcyan
	hi ChInfo ctermfg=green
	hi ChTalk ctermfg=darkcyan
	hi MChTalk ctermfg=13
	hi PTime ctermfg=blue
	map <F50>vc	:call CtrClean()<CR>
	map q	:q<CR>
	map <F50>vi :call ClearNonImportant()<CR>
endfunction

function! CtrClean()
	 g/תשת/d
	 g/^-NickServ(services/d
	 g/^-ChanServ(services/d
	 g/^-MemoServ(services/d
	 write
endfunction

function! CtrNew()
	echo "File Does not Exist"
	exit
endfunction

function! ClearNonImportant()
	let @a = ""
	g/lxl[,-\.:]/call StoreLines()
	1,$d_
	normal "ap
endfunction

function! StoreLines()
	let l = getline('.')
	let p = substitute(l, '.*lxl[,-\.:]\s\+\(.*\)', '\1', '')
	if p == l
		return
	endif
	exe '.-' . p . ',.y A'
endfunction


function! CtrTmpLeave()
	if expand('%') == bufname('.ctr.tmp')
		!rm %
	else
		call input('error inside ctrtmpleave.')
	endif
endfunction

function! CtrLeave()
	q!
endfunction


function! SearchThesaurus()
	let word = expand('<cword>')
	vi ~/dbase/dict/misc/roget.dlxl
	exe 'normal /' . word
	call histadd('search', word)
	let @/ = word
endfunction

	

function! ArticleEnter()

	if expand('%:e') == 'art'
		set ft=art
	endif
   " if !bufexists('q') 
   " 	vertical split q
   " 		normal 
   " 		vertical resize 80
   " endif
	set nocindent
	set smartindent
	setlocal tags+=~/dbase/dict/.tags_word
	set fo+=2
	set linebreak
	set showbreak=\|
	map <buffer>	<M-c> 	:call MySpellCheck()<CR>
	map <buffer>	<M-a> 	:call SpellList('single')<CR>
	map <buffer>	<M-1> 	:call SpellList('single')<CR>
	map <buffer>	<M-q> 	:call SearchThesaurus()<CR>
	syntax clear Mispel
	if g:spell_wlist != ""
		exe ":syntax keyword Mispel " . g:spell_wlist 
	endif
	call VirtualMove()
	syntax clear CurMisspell
	"if g:spell_cword != ''
		"exe 'syn keyword CurMisspell ' .g:spell_cword
	"endif
	hi Mispel cterm=underline ctermfg=DarkCyan
	hi CurMisspell cterm=underline ctermfg=Red
	call ArticleAbbr()
	inor <buffer> <M-D> <C-d>
endfunction

function! ArticleAbbr()
	"call input(expand('%'))
	source ~/.etc/vim/vhtmlab.vim
 	iab <buffer> eln Please open PMB for further discussions. Looking forward to working with you. Thanks and Regards. Ligesh (CTO Lxlabs)
	iab <buffer>	rd	Regards,
	iab <buffer>	u 	you
	iab <buffer>	i 	I
	iab <buffer>    lxf <lxf>
	iab <buffer>    nlxf </lxf>
	iab <buffer>  r  are
	iab <buffer>	ur	your
	iab <buffer>	teh	the
	iab <buffer>	sm :-)
	iab <buffer>	abt about
	iab <buffer>	tng	thing
	iab <buffer>  ursf yourself
	iab <buffer> U You
	iab <buffer> Ur Your
	" <M-D> ִ is a map for <C-d>, since <C-D> is mapped to abb expansion.
	iab <buffer> llp ִ--:: Ligesh :: http://ligesh.com
	iab <buffer> llc ִ--:: Ligesh :: Lxadmin Core Developer :: http://demo.lxadmin.com:7778 :: http://lxlabs.com/software/lxadmin/
	iab <buffer> lll ִ--:: Ligesh :: http://lxlabs.com
	iab <buffer> llr ִ--:: Ligesh :: http://calliphonia.com :: The Obligatory Rock Band ::
	iab <buffer> llx ִ--:: Lxhelp :: lxhelp.at.lxlabs.com :: http://lxlabs.com ::
	iab <buffer> qq [quote]
	iab <buffer> nqq [/quote]

	iab <buffer>	lgs	ִ-Ligesh
	iab <buffer>	lxx        +--------------------------+
			\\|          lxlabs          \|
			\\| 705, 5th Main, 6th Cross \|
			\\|      Hal IIIrd stage     \|
			\\|    Bangalore - 560075    \|
			\\|          India           \|
			\\|  Ph: +91 (80) 525-0706   \|
			 \+--------------------------+

	iab <buffer>	lgg     +------------------------------------------+
	 	      \\|    K T Ligesh    \|   ligesh@lxlabs.com   \|
	 	      \\|      lxlabs      \| http://www.lxlabs.com \|
	 	      \\| Bangalore, India \|   +91 (80) 525-0706   \|
	 	      \+------------------------------------------+

	iab <buffer>	lx	ִ:r ~/.etc/mail/lxlabs.signִ
	iab <buffer>	lg	ִ:r ~/.etc/mail/ligesh_lxlabs.signִ
	iab <buffer>  lga ִ:r ~/.etc/mail/ligesh.signִ
	iab <buffer>  cht http://lxlabs.com/support/chat.shtml
	"source ~/.etc/vim/vartab.vim
endfunction

function! SetViminfo()
	set viminfo='20,h,n~/.etc/vim/.tmp/.viminfo
endfunction

function! LynxEnter()
	set viminfo='20,h,n~/.etc/vim/.tmp/.viminfo.lynx
	call ArticleEnter()
endfunction

function! MuttRead()
	/^$
	if line('.') > 12
		normal z
	endif
endfunction


function! MuttEnter()
	set viminfo='20,h,n~/.etc/vim/.tmp/.viminfo.mutt
	call system("rm -f ~/.etc/.tmp/mutt-vim-send.chk")
	rviminfo!
	set filetype=mail
	set syn=mail
	let g:spell_option = '-e --add-email-quote=">" --add-email-quote=":" --email-margin=7'
	syn on
	call ArticleEnter()
	map	<buffer> <C-t>	gg/^To: /e<CR>
	map	<buffer> <M-a>	gg/^To: /e<CR>
	map	<buffer> q	:qa<CR>
	map	<buffer> <M-s>	gg/^Subject: /e<CR>
	map <buffer> <C-d><C-d> :call MuttSendDirectly()<CR>
	map <buffer> <C-d><C-f> :q!<CR>
	badd ~/.etc/mail/aliases
endfunction

function! MuttSendDirectly()
	call system("touch ~/.etc/.tmp/mutt-vim-send.chk")
	write
	exit
endfunction

	
function! OutEnter()
	map	<buffer> q	:q<CR>
endfunction

function!  AsmRefresh() 
	let winn = bufwinnr("*.[sg]db")
	exe winn . " wincmd w"
	silent edit!
	call AsmFilter()
	wincmd p
endfunction



function! SpellList(flag)
	if (a:flag == "single")
		let g:spell_cword = expand('<cword>')
		let g:spell_single = 1
	else
		call MySpellCheck()
		if  g:spell_wlist == ''
				return
		endif
	
		let g:spell_cword = substitute(g:spell_wlist, '\s*\(\w*\).*', '\1', '')
	1
	silent! exe 'normal /\<' .g:spell_cword. "\\\>\<C-m>"
	endif
	syntax clear CurMisspell
	if g:spell_cword != ''
		exe 'syn keyword CurMisspell ' .g:spell_cword
	endif
	call system("echo " .g:spell_cword. "|aspell  -a check > /tmp/.vspell.err")
	9sp /tmp/.vspell.err
	if getline(2) == '*' || getline(2) ==  ''
			bw!
			syn clear CurMisspell
			return
	endif
	call SpellCleanup()
	"exe 'syntax match Mispel "' .curword. '"'
endfunction

function! SpellCheckWord()
	if g:spell_cword == ''
		bw! /tmp/.vspell.err
		syn clear CurMisspell
		return
	endif
	call system("echo " .g:spell_cword. "|aspell  -a check > /tmp/.vspell.err")
	e!
	call SpellCleanup()
endfunction
	

function! SpellCleanup()
	call append(0, "")
	g/International Ispell/d
	silent! %s/^& //
	silent! %s/ \d\+ \d\+:/:/
	normal! 2w
	call ColorWord()
endfunction

function! SpellSetCurrentWord()
	let prev = g:spell_cword
	let subs = '.*' .g:spell_cword. '\s*\(\S*\)\s*.*'
	let g:spell_cword = substitute(g:spell_wlist, subs, '\1' , '')
endfunction

function! FixSpellErrorAll()
		normal! wbyw
		bw!
		exe '%s/' .g:spell_cword. '/' .@0. '/g'
endfunction
	
function! FixSpellError()
	if g:spell_single
		normal! wbyw
		bw!
		normal! wbde"0P
		syn clear CurMisspell
		return
	else
		call SpellSetCurrentWord()
		normal! wbyw
		wincmd p
		normal! wbde"0P
	endif
	silent! exe 'normal /\<' .g:spell_cword. "\\\>\<C-M>"
	syntax clear CurMisspell
	if g:spell_cword != ''
		exe 'syn keyword CurMisspell ' .g:spell_cword
	endif
	wincmd p
	call SpellCheckWord()
endfunction


function! ColorWord()
	syntax clear CurrentSpellWord
	exe 'syntax match CurrentSpellWord "\<' .expand('<cword>'). '\>"'
endfunction

function! SpellIgnoreWord()
	call system('echo ' .g:spell_cword. ' >> ~/.etc/data/spell_personal.pws')
	if (g:spell_single)
		bw!
		syn clear CurMisspell
		call MySpellCheck()
		return
	endif
	wincmd p
	call MySpellCheck()
	if g:spell_wlist == ''
		bw! /tmp/.vspell.err
		syn clear CurMisspell
		return
	endif
	let g:spell_cword = substitute(g:spell_wlist, '\s*\(\w*\)\s*.*', '\1', '')
	exe 'normal /\<' .g:spell_cword. '\>'
	syntax clear CurMisspell
	if g:spell_cword != ''
		exe 'syn keyword CurMisspell ' .g:spell_cword
	endif
	wincmd p
	call SpellCheckWord()
endfunction

function! SpellSkipWord()
	if (g:spell_single)
		syntax clear CurMisspell
		bw!
		return
	endif
	wincmd p
	call SpellSetCurrentWord()
	silent! exe 'normal /\<' .g:spell_cword. '\>'
	syntax clear CurMisspell
	if g:spell_cword != ''
		exe 'syn keyword CurMisspell ' .g:spell_cword
	endif
	wincmd p
	call SpellCheckWord()
endfunction


function! SpellListEnter()
	set autoread
	map <buffer> <C-m> :call FixSpellError()<CR>
	nor <buffer> l	  <C-Right>:call ColorWord()<CR>
	nor <buffer> j	  <C-Left>:call ColorWord()<CR>
	nor <buffer> i		gkwb:call ColorWord()<CR>
	nor <buffer> k	   gjwb:call ColorWord()<CR>
	map <buffer> <Left> j
	map <buffer> <Right> l
	map <buffer> <Up> i
	map <buffer> <Down> k
	nor <buffer> <silent> q	:bw!<CR>:silent! !rm /tmp/.vspell.err<CR>
	nor <buffer> <silent> r	:call SpellIgnoreWord()<CR>
	nor <buffer> <silent> s	:call SpellSkipWord()<CR>
	nor <buffer> <silent> a	:call FixSpellErrorAll()<CR>
endfunction

function! HNewFile()
	let value = expand("%:t")
	let value = substitute(value, '\(.*\)', '\U\1', '')
	let value = substitute(value, '\.', '_', 'g')
	let value = '_' .value. '_'
	call setline(1, '#ifndef ' .value)
	call append(1, '#endif /* ' .value. ' */')
	call append(1, '')
	call append(1, '')
	call append(1, '#define ' .value)
	3
endfunction
	


function! MySpellCheck()
	update
	let g:spell_source_file = expand('%')
	setlocal tags=~/dbase/dict/.tags_word
	"let g:spell_wlist = system("aspell -p ~/.etc/data/spell_personal.pws --add-extra-dicts=$HOME/.etc/data/permanent_words.pws -l " .g:spell_option. " < " .expand("%"))
	let g:spell_wlist = system("aspell -l " .g:spell_option. " < " .expand("%"))
	let g:spell_wlist = substitute(g:spell_wlist, "\n", " ", "g")
	syntax clear Mispel
	if g:spell_wlist != ""
		exe ":syntax keyword Mispel " . g:spell_wlist 
	endif
endfunction


function! JumpNextShow()
	cn
	normal zz
	call ColorCurrentBrows()
endfunction

function! JumpPrevShow()
	cN
	normal zz
	call ColorCurrentBrows()
endfunction

function! ColorMoveMap(var)
	let g:fvar=a:var
	nor <silent> <buffer>	i	k:call ColorCurrent{g:fvar}()<CR>
	nor <silent> <buffer>	k	j:call ColorCurrent{g:fvar}()<CR>
	vnor  <buffer>	i 	k
	vnor <buffer>	k	j
	map  <buffer>	<Up>	i
	map  <buffer>	<Down>	k
endfunction

function! ColorMoveUnMap()
	nor	i	k
	nor	k	j
	unmap	<Up>
	unmap	<Down>
endfunction


function! JustColor()
	"let curline=substitute(getline("."),"[\*\.\~]","\\\\&","g")
	let curline=escape(getline("."),'"~[]$&\')
	exe "syn match Current " . "\"^" . curline . "$\""
endfunction
	
function! ElanceCutProject()
		let url = getline('.')
		let ln = line('.')
		let ln = ln - 1
		let head = getline(ln)

		let url = substitute(url, '^\s*\(\S*\)\s*$', '\1', '')

		let fnm = substitute(url, '.*/\(.*\)', '\1', 'g')
		let fnm = substitute(fnm, '[&?=]', '_', 'g')
		let fnm = g:eln_date . '/' . fnm
		let lurl = 'g' . url
		let lurl = lurl . '^Jp^J^U'. fnm . '.eln^Jap^J^U' . fnm . '.html^J'

		let lurl = LynxKey(lurl)
		let $lurl = lurl
		!echo $lurl  >> cmd.lnx
		let $head = head
		!echo "$head" >> main-list.meln
		exe '!echo " ' . fnm . '.eln" >> main-list.meln'
		"!echo >> main-list.meln
endfunction

function! MainMelnRead()
	$
	?-----------
	badd ~/dbase/elance/bid-list.meln
endfunction

function! MelnRead()
	hi MelHead ctermfg=darkcyan
	syntax match MelHead "^[a-zA-Z1-9].*"
	noremap <M-w> gf
	map <M-2> <C-q>
	set vi=
endfunction 

function! GetChordsOld(n)
	let l = a:n
	normal "ayykk
	let l = l - 1 | if l == 0 | return | endif
	normal "byykk
	let l = l - 1 | if l == 0 | return | endif
	normal "cyykk
	let l = l - 1 | if l == 0 | return | endif
	normal "dyykk
	let l = l - 1 | if l == 0 | return | endif
	normal "eyykk
	let l = l - 1 | if l == 0 | return | endif
	normal "fyykk
	let l = l - 1 | if l == 0 | return | endif
	normal "gyykk
endfunction

function! GetChords(f)
	let n = a:f
	while n > 0
		let v = nr2char(123 - n) 
		exe 'normal "' . v . 'yykk'
		let n = n - 1
	endwhile
endfunction

function! PutChords(f)
	let n = a:f
	while n > 0
		let v = nr2char(123 - n) 
		exe 'normal "' . v . 'pk'
		let n = n - 1
	endwhile
endfunction


function! GetDateFromMail()
	let date = getline('.')
	let g:eln_date = substitute(date, 'Date: \S\+,\s\+\(\d\+\)\s\+\(\S\+\)\s\+\(\S\+\).*', '\2.\1.\3', '')
endfunction


function! ElanceStart()
		set vi=
		g/Date: /call GetDateFromMail()
		silent! exe '!mkdir -p ' . g:eln_date
		exe '!echo  ---------------------------------------  >> main-list.meln'
		!rm -f cmd.lnx fin.lnx
		silent g/elance.com.*job.jobid/call ElanceCutProject()
		call ExeLynx()
		!rm -f %
		quit!
endfunction

au! BufRead *.eln call ElnRead()

function! ExeLynx()
		let $TMPDIR = "/tmp"
		let quit = LynxKey('q^J')
		let $quit = quit
		!echo "$quit" >> cmd.lnx
		!cat header.lnx cmd.lnx > fin.lnx
		!rm cmd.lnx
		"!lynx -cmd_script=fin.lnx login.html
		"!rm fin.lnx
endfunction

function! SeeProjectLynx()
		let name = expand('%:t:r')
		let name = substitute(name, 'job_jobid_\(\d*\)_rid_\(.*\)', 'job?jobid=\1\&rid=\2', '')
		let name = 'http://www.elance.com/' . name
		exe '!scpaste aamain 4 "g' .name. '"'
endfunction

function! SeeLocal()
		let name = expand("%:p:r")
		let name = name . '.html'
		exe '!lnx ' . name
endfunction

function! ElnRead()
		silent! g/Logged in as/1,.d
		silent! g/HOME  .  BUY SERVICES/.,$d
		silent! g/btn_post_similar_project.gif/d
		silent! g/Contacting this buyer/.,.+2d
		silent! g/feedback for others/d
		write
		map <M-z> :call SeeProjectLynx()<CR><CR>
		map <M-a> :call SeeLocal()<CR>
endfunction




function! LynxKey(string)
	let s = substitute(a:string, '.', 'key &;', 'g')
	let s = substitute(s, 'key ^;key \(.\);', 'key ^\1;', 'g')
	let s = substitute(s, ';', '\n', 'g')
	return s
endfunction


function! MasterEnter()
	set nowrap
	setlocal tags=tags_dbase,.tags_dbase
	set nomodifiable
	set isk+=+
	"cmap <space> _
	map <buffer> o <C-]>
	map <buffer> <C-m> <C-]>
endfunction


function! BackUpWord()
	"call system("echo " .expand("<cword>").  " >> ~/.etc/nsword")
	silent exe "!echo " .expand("<cword>").  " >> ~/dbase/nsword"
endfunction

function! GetAudioFileName(l)
	let l = substitute(a:l, '\(.*\)', '\L\1', '')
	let d = substitute(l, '\(.\).*', '\1', '')
	let r = 'misc/audio/' .d. '.dsp/' .l. '.wav'
	return r
endfunction

function! PlayAudioFile(f)
	let l = a:f
	if filereadable(l)
		call system('play ' . l)
	else
		echohl ErrorMsg
		echo "   " .l. " File Doesn't exit"
		echohl None
	endif
endfunction


function! DictPronounce()
	normal mk
	let l = getline('.')
	if match(l, '@WORD') == -1
		?@WORD
		let l = getline('.')
	endif

	let l = substitute(l, '@WORD: ', '', '')
	let l = GetAudioFileName(l)
	call PlayAudioFile(l)
	'k
endfunction

function! WordPronounce()
	let word = expand('<cword>')
	let l = GetAudioFileName(word)
	call PlayAudioFile(l)
endfunction

function! DictEnter()
	map <buffer> <M-q>  :call DictPronounce()<CR>
	map <buffer> <M-a>	:call WordPronounce()<CR>
	call DictCommon()
endfunction
	
function! DictWordFind()
	call BackUpWord()
	let l = expand('<cword>')
	let l = substitute(l, '\d$', '', '')
	exe 'tjump ' . l
endfunction


function! DictRootFind()
	let l = expand('<cword>')
	vi ~/dbase/dict/misc/root.dlxl
	exe '/root: ' . l
endfunction

function! DictWordFindV()
	let l = @0
	let l = substitute(l, '.*', '\L&', '')
	let l = substitute(l, ' ', '+', 'g')
	exe 'tjump ' . l
endfunction

function! DictThesFind()
	let l = expand('<cword>')
	vi misc/roget.ths
	let l = '\<' .l. '\>'
	exe '/' .l
	let @/ = l
endfunction
	

function! DictCommon()
	setlocal viminfo=""
	"set noic
	setlocal nowritebackup
	setlocal noswf
	setlocal isk+=+,-,(,)
	setlocal nomodifiable
	map <buffer> <space> <C-f>
	map <buffer> <M-1>   <C-T>
	map <buffer> <M-w>	:call DictWordFind()<CR>z<CR>
	vmap <buffer> <M-w>	y:call DictWordFindV()<CR>z<CR>
	map <buffer> <M-s>  :call DictRootFind()<CR>z<CR>
	map <buffer> <M-d>  :call DictThesFind()<CR>zz
	call AddDictFiles()
	set tags=~/dbase/dict/.tags_word
endfunction


function! AddDictFiles()
	badd ~/dbase/dict/misc/roget.dlxl
	badd ~/dbase/dict/master_word.dctf
	badd ~/dbase/dict/misc/pronounciation.dlxl
	badd ~/dbase/dict/misc/root.dlxl
	badd ~/dbase/dict/misc/spelling-test.art
endfunction

function! MasterWordEnter()
	noremap <buffer> /  /^
	call DictCommon()
	map <buffer> <M-q>  :call WordPronounce()<CR>
	map <buffer> <M-a>  :call WordPronounce()<CR>
endfunction

function! WgetRead()
	map <F50>cd :%s/^\s\+\d\+\. //<CR>
endfunction

function! NsWordEnter()
	set viminfo=""
	"set noic
	set nowritebackup
	setlocal tags=~/dbase/dict/.tags_word
	map <space> <C-f>
	map	<M-q>	<C-]>
	map 	<M-1>   <C-T>
endfunction

function! MlistEnter()
	set syntax=
	set viminfo='20,h,n~/.etc/vim/.tmp/mlist.viminfo
	rviminfo!
	map q :call CheckMailBox()<CR><CR>
	map m :call SendMailBox()<CR><CR>
	"call system("screen -p $WINDOW -X title Mlist")
	let g:title_set = 1
endfunction


function! GetMboxLine()
		let l = getline('.')
		let l = substitute(l, ' ', '', 'g')
		if l == ""
			return l
		endif
		if match(l, "/") == -1
	 		let l = '~/mail/mboxes/' . l
		endif
		return l
endfunction

function! CheckNewMail(file)
	let fr = libcallnr("liblxvim.so", "file_access_time", expand(a:file))
	let fm = getftime(expand(a:file))
	if fm > fr
		return 1
	else
		return 0
	endif
endfunction


function! BashSkipWord()
	set isk-=_
	normal! w
	set isk+=_
	call BashSkipMetaChar()
endfunction

function! BashSkipMetaChar()
	while 1
		let c = GetCharUnderCursor()
		if IsMetaChar(c)
			let eol = col('$') - 1
			if eol == 0 || col('.') == eol
				normal k0
			else
				normal l
			endif
		else
			break
		endif
	endwhile
endfunction

function! IsMetaChar(c)
	let c = a:c
	if (match ('_~.,/;:"+-=!@#$%^&()]{}[ ', c) != -1)
		return 1
	elseif (match (c, "'") != -1)
		return 1
	else
		return 0
endfunction

function! GetCharUnderCursor()
         normal "pyl
		 return getreg('p')
endfunction

function! DeleteBashWordInsert()
	if col('.') == 1
		normal! a 
		return
	endif


	if (col('.') == (col('$') - 1) )
		normal! az
	endif

	normal! h
	while 1
		if IsMetaChar(GetCharUnderCursor())
			normal! xh
		else
			break
		endif
	endwhile

	normal! l
	let oldisk = &isk
	set isk-=_
	if col('.') == col('$') - 1
		normal! dbx
	else
		normal! db
	endif
	let &isk = oldisk
endfunction

function! ClearMetaCharWord(string, pos)
	let l = a:string
	let n = a:pos
	let endl = strpart(l, n - 1)

	let l = strpart(l, 0, n - 2)

	let l = substitute(l, "[\\\]\\\{\\\}\\\[~`\.,/;:\'\"+-=!@#$%^&() ]*$", '' , '')
	let l = substitute(l, '[a-zA-Z0-9]*$', '' , 'g')
	let l = l . endl
	let g:command_pos = n + strlen(l) - strlen(a:string)
	return l
endfunction


function! DeleteBashWordCmdline()
	let l = getcmdline()
	let n = getcmdpos()
	let l = ClearMetaCharWord(l, n)
	call setcmdpos(g:command_pos)
	return l
endfunction

cnor <C-w> <C-\>eDeleteBashWordCmdline()<CR>


function! GetMailbox()

	let prevpos = line('.')
	let mailbox = ""
	call MlistBegBlock()
	+1
	while 1 
		if match(getline('.'), "[") != -1
			break
		endif
		let l = GetMboxLine()
		if CheckNewMail(l)
			let mailbox = l
			break
		endif
		+1
	endwhile

	if mailbox == ""
		exe prevpos
		let mailbox = GetMboxLine()
	endif

	return mailbox
endfunction

function! CheckMailBox()
	exe '!mutf ' . GetMailbox()
endfunction

function! SendMailBox()
	exe '!mutf ' . GetMboxLine() . ' -e "push m"'
endfunction

function! MlistBegBlock()
	while 1
		if match (getline('.'), "[") != -1
			break
		else
			-1
		endif
	endwhile
endfunction

function! Savereg()
 	exe ":redir >> " . expand("~/.etc/vim/vreg.vim")
	silent reg qrstuv
	redir END
	silent !vim -c "\%s/\^M//ge
	\|\%s/\^R//ge 
	\|\%s/\^W//ge
	\| \%s/\^H//ge 
	\| \%s/\^A//ge 
	\|exit" ~/.etc/vim/vreg.vim
endfunction

function! Makesess()
	let sss=&ssop
	let &ssop="buffers,curdir"
	exe "mksession! " .v:this_session
	exe 'sp ' . v:this_session
	call append(0, 'source ~/.etc/vim/vimrc.vim')
	call append(0, 'set nocp')
	g/^cd /.
	let vinum = line('.')
	let first_file = bufname(1)
	if first_file != ''
		call append(vinum, 'silent vi ' .first_file)
	else
		call append(vinum, 'silent vi ' .bufname(2))
	endif
	silent! %s/^edit/silent! edit/
	silent! %s/^args/silent args/
	silent! g/set nocompatible/d_
	update
	bw
	"exe "!echo \'source ~/.etc/vim/vimrc\' >>" .v:this_session
	"exe "!echo silent e\\! >>" .v:this_session
	syn on
	let &ssop=sss
endfunction

function! DotManEnter()
	map	<buffer> <space>	<C-F>
	if(winnr() != 1)
		map	<buffer> q	:bd<CR>
	else
		map	<buffer> q	:q<CR>
	endif
	map <buffer> <CR> K
	set ft=man
	set viminfo='20,h,rgdb,n~/.etc/vim/.tmp/.viminfo.man
	syn match manOptionDesc "^\s\+[+-][-]\?[a-z0-9]\S*"
endfunction


function! CmdMap()
	nor	<buffer> <C-m>	<C-M><C-M>
endfunction

function! DeCmdMap()
	if(mapcheck("<C-M>")=="<CR><CR>")
		unmap <C-M>
	endif
endfunction

function! Manual(flag,comm)

	if(a:flag == 'sys')
		"call system('/usr/bin/man -P eless ' . a:comm . '>/dev/null')
		silent exe '!/usr/bin/man ' . a:comm . 
		   \' 2>&1 | sed "s/.//g" > ~/.etc/.tmp/Manual. 2>&1'
	elseif a:flag == 'prg'
		if (&ft == 'php' || g:g_php_help) 
			let g:g_php_help = 1
			let c = substitute(a:comm, '\(.\).*', '\1', '')
			let com = substitute(a:comm, '_', '-', 'g')
			silent exe ':!cat /usr/share/php/' . c . '/function.' . com . '.txt > ~/.etc/.tmp/Manual.'
		else
			silent exe ':!/usr/bin/man -S 2:3:1:4:5:6:7:8:9:n ' . a:comm . 
		   \' 2>&1 | sed "s/.//g" > ~/.etc/.tmp/Manual. 2>&1'
		endif
	else
		silent exe ':!/usr/bin/man ' .a:flag. ' ' .a:comm. 
		   \' 2>&1 | sed "s/.//g" > ~/.etc/.tmp/Manual. 2>&1'

	endif
	let wbufn = bufwinnr("Manual.")
	let mbufn = bufnr("Manual.")
	let ht = winheight(0) - 2
	let g:win_old_height = winheight(0)
	if (wbufn == -1)
		if (mbufn == -1)
			silent exe ht . "sp ~/.etc/.tmp/Manual."
		else
			silent exe "sb " .mbufn
			exe "resize " .ht
		endif
	endif
endfunction

function! ManualChange()
	let mwinn = bufwinnr("Manual.")
	if (mwinn == winnr()) 
		 silent vi ~/.etc/.tmp/Manual. 
		 exe "normal gg7\<C-e>zz"
	elseif(mwinn == -1)
		silent 25split ~/.etc/.tmp/Manual.
		let g:stl = &stl
		"set stl = %{expand('%:t')}%m%=%2*[%{getcwd()}\ %*#%{$ps1}%2*]
			\%*%4l%2*%3c%r
	else 
		exe "silent " .  mwinn ." wincmd w"
		silent e! ~/.etc/.tmp/Manual.
		call DotManEnter()
		wincmd _
	 endif 
endfunction

function! CompileMap(flag)
	let g:make = a:flag


	if(a:flag == 1)
		map <silent>	<M-c>	:call Compile("make")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("make")<CR>
	elseif(a:flag == 2)
		map <silent>	<M-c>	:call Compile("mak")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("mak")<CR>

	elseif(a:flag == 3)
		map <silent>	<M-c>	:call Compile("ma")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("ma")<CR>
	elseif(a:flag == 4)
		map <silent>	<M-c>	:call Compile("m")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("m")<CR>


	elseif(a:flag == 5)
		map <silent>	<M-c>	:call Compile("cc")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("cc")<CR>
	elseif(a:flag == 6)
		map <silent>	<M-c>	:call Compile("tc")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("tc")<CR>
	elseif(a:flag == 7)
		map <silent>	<M-c>	:call Compile("run")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("run")<CR>
	elseif(a:flag == 8)
		map <silent>	<M-c>	:call Compile("pc")<CR>
		imap <silent>	<M-c>	<C-C>:call Compile("pc")<CR>
	endif
endfunction

function! StatusError()
	let g:stl_b =  ClearVar(g:stl_b, 'E:[^:]*:')
	if v:shell_error != 0 && v:shell_error != 139
		let g:stl_b = g:stl_b . 'E:' . v:shell_error . ':'
	endif
	call SetStl()
endfunction


function! Compile(flag)
	update
	echohl statusline
	let file = tempname()
	let dont_read_cf = 0

	let clib = '-llxlib'

	if (&ft == 'php')
		let g:old_efm = &errorformat
		let &errorformat = '%m in %f on line %l'
	endif

	if(a:flag=="make")
		call IdeMake()
		return 

	elseif(a:flag == 'mak')
		if(filereadable("Premake"))
			source Premake
		endif
		exe ':!make  2>&1|tee ' .file. '; exit $PIPESTATUS;'

	elseif(a:flag=="ma")
		if(filereadable("Premake"))
			source Premake
		endif

   		silent exe '!make >' .file. ' 2>&1; exit $PIPESTATUS '
		

		if (v:shell_error == 0)

			let cmdfile = tempname()
			call system('make -n vim_run > '. cmdfile)

			if v:shell_error == 2
		    	echo "Please create a 'vim_run' target in the makefile. See :help vim_run" 
				exe 'silent !rm -f ' .file. ' ' .cmdfile
				return 
			endif

			exe 'silent !chmod 755 ' .cmdfile
			echohl IdeMessage
			echo 'Make Successful... Executing.. '
			echohl None
			exe '!'. cmdfile

			if (v:shell_error == 139)
				echo "Starting Gdb, Please wait....                          "
   	        	let exename = GetExe('name')
   	        	exe 'Ide file ' .exename
				exe 'Ide core core'
				exe 'silent !rm -f ' .file. ' ' .cmdfile
   	        	return
			endif
	
			let dont_read_cf = 1
		endif

		if(filereadable("Postmake"))
			source Postmake
		endif

	elseif(a:flag=="m")
		if(filereadable("Premake"))
			source Premake
		endif
   		silent exe '!make >' .file. ' 2>&1; exit $PIPESTATUS '
		if(filereadable("Postmake"))
			source Postmake
		endif

	elseif(a:flag=="spm")
		if(filereadable("Premake"))
			source Premake
		endif
   		silent exe '!make -C ' .expand('%:p'). ' ' .expand('%:t:r') 2>&1; exit $PIPESTATUS '
		if(filereadable("Postmake"))
			source Postmake
		endif


	elseif(a:flag=="cc")
		exe ':!if (g++ -g % -o ' .expand("%:p:r"). ' ' .clib.
		\' 2>&1 |tee ' .file. '; exit $PIPESTATUS ) ;then '
		\expand("%:p:r").
		\'; echo -n "$?\$ " ;fi'

	elseif(a:flag=="tc")
		exe ':!g++ -g % -o ' .expand('%:p:r'). ' ' .clib.
		\' 2>&1|tee ' .file. '; exit $PIPESTATUS ;' 
		
	elseif(a:flag=="run")
		exe ':!if make vim_run;then echo -n "$?\$ " ; '
		\'read a;else echo -n $?; fi'
		let dont_read_cf = 1

	elseif(a:flag=="pc")
		exe ':!if !%;then echo -n "$?\$ " ; '
		\'read a;else echo -n $?; fi'
		let dont_read_cf = 1
	endif

	if(!dont_read_cf )
		exe  ':cf ' .file
		exe 'silent! !rm -f ' .file
	endif

	if (&ft == 'php')
		let &errorformat = g:old_efm
	endif

	echohl None
endfunction

function! ID_search()
	let erfil = tempname()
	exe '!lid -R grep ' .expand("<cword>").  ' > ' .erfil
	exe "cf " .erfil
	exe "!rm " .erfil
endfunction

function! GrepSearch(string)
	let efs=tempname()
	exe '!clearexec grep -ni -w -r ' .a:string. ' . >' .efs
	exe 'cf ' .efs
	exe " \:!rm " .efs
	cc
endfunction

function! PrintLineInfo(efs)
	call system("echo '"  . bufname('%') .':' . line('.') . ':' .  getline('.') . "'>>" . a:efs)
endfunction

function! VimGrep(expr)
	let efs = tempname()
	exe 'bufdo silent g/' . a:expr . '/call PrintLineInfo("' . efs . '")'
	if (filereadable(efs))
		exe ':cf ' . efs
		call system('rm -f ' . efs)
	endif
endfunction


function! MapGrep()
	set grepprg=clearexec\ grep\ -n
	map	<M-g> 	:call GrepSearch(expand('<cword>'))<CR>
endfunction

function! MapID()
	set grepprg=lid\ -R\ grep
	map <M-g> :call ID_search()<CR><CR>
endfunction

function! MapGrepFind()
	let og=find -type f -exec grep -n
endfunction

function! Toggle(flag, ...)
	if a:flag == 'grep' 
		if(exists('g:grep'))
			unlet g:grep
			call MapID()
		else
			let g:grep = 1
			call MapGrep()
		endif
	elseif(a:flag=="syn")
		if exists('syntax_on')
			syntax off
		else
			syntax on
			source ~/.etc/vim/vcols.vim
		endif
	elseif(a:flag=="sync")
		if g:gdb_sync
			let g:gdb_sync = 0
		else
			let g:gdb_sync = 1
		endif
	elseif(a:flag=="a")
		call ToggleStl("g:append", 'A:')
		if(exists("g:append"))
			unmap yy
			unmap dd
			unlet g:append
		else 
			let @a=""
			nor yy "Ayy
			nor dd "Add
			let g:append=&stl
		endif
	else 
		exe 'let val = ' .a:flag
		if a:0 > 1
			exe 'let set_val = ' . a:1
		else
			let set_val = 1
		endif

		if val
			exe 'let ' .a:flag. '= 0'
		else
			exe 'let ' .a:flag. '= ' . set_val
		endif
	endif
endfunction

function! Restore(flag)
	if(a:flag=="r")
		set t_ti=7[?47h
		set t_te=[2J[?47l8
	else 
		set t_ti=
		set t_te=
	endif
endfunction

function! Delete()
	normal iv
	normal /SONG:
	let pa=line(".")
	normal n
	if(pa==line("."))
		normal G
	endif
	normal id
endfunction

function! Print()
	set noic
	set nowrapscan
	let name = tempname()
	let name = substitute(name, '/', '_', 'g')
	echo name
	normal vk
	normal /^From[^:]
	let pa=line(".")
	"if(pa==line("."))
	"	normal G
	"endif
	normal iv
	exe "'<,'>w! >> ". name
	set wrapscan
	normal '<k
endfunction

function! ChangeCd(...)
	if(a:0 == 0)
		13sp ~/.etc/.tmp/dirstack
	else
		exe ":cd " .system("lcd " .a:1)
	endif
endfunction

function! ChangeScd(...)
	if( a:0==0 )
		13sp ~/.etc/.tmp/sdirstack
	else
		exe ":cd " . system("escd " . a:1)
	endif
endfunction

function! BufferFunc(count, line, end)
	echo a:count a:line a:end
	if a:count == 0
		1
		call ColorCurrentList()
		return
	endif

	bd! ~/.etc/.tmp/Vimlist.
	let buf =  a:count - a:line + 1
	exe 'b ' . buf
endfunction

function! ExportQueryString(val)
	if exists('g:gdb_running') && g:gdb_running == 1
		:exe 'Ide set environment QUERY_STRING ' .a:val
		:exe 'Ide set environment REQUEST_METHOD GET'
	else
		let $QUERY_STRING = a:val
		let $REQUEST_METHOD = "GET"
	endif
endfunction!

function! DoChange(prog)
	let l = getline('.')
	let s = substitute(l, '^\s*\(\S*\).*', '\1', '')
	let d = substitute(l, '^\s*\S*\s*', '', '')
	echo system(a:prog . ' -v ' .s. ' ' .d) 
endfunction

function! RemoveCprint()
	silent! s/printf("//g
	silent! s/\\n");//g
	silent! s/\\"/"/g
	silent! s/");$//g
endfunction



function! ChangeFiles(flag)
	if a:flag == 'm'
		let prog = '/bin/mv'
	elseif a:flag == 'c'
		let prog = '/bin/cp'
	else 
		echo 'Unknown flag'
		return
	endif
	g/./call DoChange(prog)
endfunction
	
function! ViSameDir()
	let l = expand('%:p')
	normal :vi 
endfunction


function! MapViSameDir()
	let l = expand('%:h')
	let l = escape(l, ' ')
	if l == ""
		let  l = l . "./"
	else 
		let l = l . '/'
	endif
	exe "map <buffer> <F50>vf :n " . l
endfunction

function! TimidityCnf()
	call append("$", "bank 0")
	call append("$", "0 /usr/share/timidity/instruments/nyguitar amp=70")
endfunction

au BufRead,BufNewFile * call MapViSameDir()

com! -nargs=* -complete=file Mancmd  call Manual(<f-args>)
com! -nargs=* -complete=tag GrepCmd  call GrepSearch(<f-args>)
com! -nargs=* -complete=tag VimGrep  call VimGrep(<f-args>)

com! -nargs=* -complete=dir Lc call ChangeCd(<f-args>)
com! -nargs=* -complete=dir Lf call ChangeScd(<f-args>)
com! -nargs=* -complete=dir Idesource Ide source <args>
com! -nargs=* -complete=file Idecore Ide core <args>
com! -nargs=* -complete=file Idefile Ide <args>
com! -nargs=0 -range=0 Buffer call BufferFunc(<count>,<line1>, <line2>)


"*************************  Vfunc   ******************************

"* ~/.etc/vim/vcpp  *
"* ~/.etc/vim/vfunc *
"* ~/.etc/vim/vimrc *
"* ~/.etc/vim/vmaps *
"* ~/.etc/vim/vmeta *
"* ~/.etc/vim/vwin  *
"* ~/.etc/vim/vtmp  *
"* ~/.etc/vim/vcols *
