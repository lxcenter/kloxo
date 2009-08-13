
au BufRead *.scp call ScpRead()
au BufEnter *.sclist call SclistRead()
au BufCreate,VimEnter *.sclist call IswitchbInit()
au BufAdd,BufCreate,VimEnter * call AddToBufferList()
au BufUnload * call RemoveFromList()
au BufEnter *.scp call ScpEnter()
au BufLeave,VimLeave *.sclist call SclistLeave()
au BufLeave,VimLeave *.scp  call ScpLeave()
au BufEnter urls.urls call UrlListEnter()
au BufEnter *.lxsql call LxsqlEnter()

function! ArticleLeave()

endfunction

function! UrlListEnter()
	map <buffer> <M-a> :call BrowserRun("local")<CR>
	map <buffer> <M-A> :call BrowserRun("vnc")<CR>
	map <buffer> <M-r> :call SetVncServer()<CR>
	map <buffer> q :q!<CR>
	let g:stl_b=$VNC_SERVER
	call SetStl()
	$
endfunction

function! AddToBufferList()
	call InitBufList()
	let q = expand('<afile>')
	if !MvContainsElement(g:buf_list, ',', q)
		let g:buf_list =  MvAddElement(g:buf_list, ',', q)
	endif
endfunction

function! RemoveFromList()
	call InitBufList()
	let q = expand('<afile>')
	if MvContainsElement(g:buf_list, ',', q)
		let g:buf_list =  MvRemoveElement(g:buf_list, ',', q)
	endif
endfunction

function! LxsqlEnter()
	syn match Lxsql '^\w*'
	hi Lxsql ctermfg=darkcyan
	syn match LxVar '%\w*'
	hi LxVar ctermfg=5
	syn match LxsqlComment '^//.*'
	hi LxsqlComment ctermfg=13
	syn keyword SyncServer syncserver
	hi SyncServer ctermfg=14
	syn match LxsqlQuotaIn '#\w*'
	hi LxsqlQuotaIn ctermfg=12
	syn match LxsqlQuota '^#\w*'
	hi LxsqlQuota ctermfg=6
	set linebreak
endfunction

function! DartRead()
	call ArticleEnter()
	set syntax=html
	set isf-=#
endfunction

function! SetVncServer()
	let l =  input("ServerName: ")
	let $VNC_SERVER = l
	let g:stl_b=$VNC_SERVER
	call SetStl()
endfunction


function! BrowserRun(arg)
	if (a:arg == "vnc")
		call system("ssh $VNC_SERVER 'galeon -n \"" . getline('.') . "\"'")
	else
		call system("gln " . getline('.'))
	endif
endfunction



function! ScDelItself()
	call system("rm -f " . expand('%'))
endfunction

function! ScpLeave()
	call ScDelItself()
endfunction

function! SclistLeave()
	call ScDelItself()
	call IswitchbLeave()
endfunction


function! SelectScreen()
	let n = ScreenGetWindow()
	"call system("screen -X selectdisp " . $OLD_SDIPLAY_TTY . " " . n)
	call system("screen -X select " . n)
	call IswitchbSaveVar()
	q!
endfunction

function! SelectElinksHistoryUrl()
	let n = getline('.')
	let w = substitute(n, '\s\+\w\+$', '', '')
	let f = substitute(w, '\S\+$', '', '')
	let w = substitute(w, f, '', '')
	call system("echo '" . w . "' > ~/.elinks/goto_url")
endfunction

	let counter = 0
"inoremap <expr> <C-L> ListItem()
"inoremap <expr> <C-R> ListReset()

func! ListItem()
	let g:counter += 1
	return g:counter . '. '
	endfunc

func! ListReset()
	let g:counter = 0
	return ''
	endfunc


function! SelectAllScreen()
	let n = ScreenGetWindow()
	call ScreenSendCommand("selectall", n)
	q!
endfunction

function! ScreenGetWindow()
	let n = getline('.')
	let n = substitute(n, '\(\d\+\):.*', '\1', '')
	return n
endfunction

function! ScreenSendCommand(command, win)
	call system("screen -X " . a:command . " " . a:win)
endfunction

function! ScreenDelete()
	let n = ScreenGetWindow()
	d
	write
	call system("screen -X msgwait 0")
	call system("screen -p " . n . " -X kill ")
	call system("screen -X msgwait 3")
endfunction

function! ScreenVDelete()
	if (line('.') == line("'<"))
		call system("screen -X msgwait 0")
	endif
	let n = ScreenGetWindow()
	call system("screen -p " . n . " -X kill ")
	if (line('.') == line("'>"))
		call system("screen -X msgwait 3")
		'<,'>d
		write
	endif
endfunction



function! SclistRead()
	map ` :q!<CR>
	map <C-q> :q!<CR>
	map <C-g> :q!<CR>
	map <M-q> :q!<CR>
	nor <CR> :call SelectScreen()<CR>
	nor [5~ :call SelectAllScreen()<CR>
	nor <C-a> :call SelectAllScreen()<CR>
	set viminfo=
	silent! unmap <C-d><C-d>
	nor <C-d> :call ScreenDelete()<CR>
	vnor <C-d> :call ScreenVDelete()<CR>
	set nowrap
	call ColorMoveMap("Brows")
	vnor <Up> <Up>
	vnor <Down> <Down>
	normal 0
	"silent! exe '/' . $OLD_WINDOW . ':'
	if (search($SVIM_TITLE))
		exe 'g/' . $SVIM_TITLE. '/d'
		write
	endif

	silent! exe '/' . $OTHER_WINDOW . ':'
	hi Current ctermfg=darkgreen
	call ColorCurrentBrows()
endfunction



function! ScpRead()
	set viminfo=
	map q :q<CR>
	$
endfunction

function! ScpEnter()
	if (line('.') == 1)
		$
	endif
endfunction



au BufEnter *.smslist call SmsListEnter()
"au VimEnter *.smslist call SmsListRead()

function! SmsListEnter()
	e!
	call ArticleEnter()
	map <buffer> r :call SmsCompose("norm")<CR>
	map <buffer> R :call SmsCompose("cword")<CR>
	map <M-s> :call SmsSend()<CR>
	map <M-s> :call SmsSave()<CR>
	map <M-r> 4dd
	map <M-q> :call SmsCompose("any")<CR>
	map <C-d><C-d> :call SmsSend()<CR>
	map <F50>r :call SmsListRead()<CR><CR>
	map q :call SmsQuit()<CR><CR>
	set viminfo=

	if (bufname("success.smslist") == '') 
			badd ~/dbase/sms/sent/sent.fail.smslist
			badd ~/dbase/sms/sent/sent.success.smslist
	endif
	call SmsSyntax()
endfunction

function! SmsQuit()
	!rm -f tmp-new.sms
	q!
endfunction

function! SmsSyntax()
	hi SMSLine ctermfg=darkcyan
	syn match SMSLine '::\d\d\d\d\d'
	syn match SMSLine '::+\d\d\d\d\d\d\d'
endfunction

function! ClearNewline(s)
	return substitute(a:s, "\n", '', '')
endfunction

function! SmsSave()
	let dt = system('date +%F+%H')
	let dt = ClearNewline(dt)
	exe '!/bin/cp -i ' . expand('%:p') . ' ~/dbase/sms/sms.' . dt . '.smslist'
endfunction


function! SmsListRead()
	call system("mobmgr -c")
	e!
	call SmsSyntax()
	$-2
endfunction

function! SmsGetNumber(flag)
	let l = getline('.')
	if (a:flag == "any")
		let n = substitute(l, '\s*\(\d\+\)\s\+\(\d\+\).*', '\1\2', '')
	elseif (a:flag == 'cword')
		let n = expand('<cWORD>')
		let n = substitute(n, ':', '', 'g')
	else
		let n = substitute(l, '::\(\S\+\).*', '\1', '')
	endif
	return n
endfunction


function! SmsCompose(flag)
	let g:sms_number = SmsGetNumber(a:flag)
	let g:old_stl = &stl
	let &stl = '[%c]    %t %m Compose ' . g:sms_number

	if (a:flag == "norm")
		let line = line('.')
		let line = line + 1
		let reply = getline(line)
		let reply = '> ' . reply
		sp tmp-new.sms
		1,$d
		call append(1, reply)
	else 
		sp tmp-new.sms
		1,$d
	endif
	call ArticleEnter()
	map <buffer>	<M-a> 	:call SpellList('single')<CR>
	map <buffer> <M-c> :call MySpellCheck()<CR>

	$
	normal o
	normal o
	startinsert
	normal 
endfunction

function! NewTempFile(base)
	let base = a:base
	let n = 0
	while 1 
		let name = base . '--' . n
		if (!filereadable(name))
			return name
		endif
		let n = n + 1
	endwhile
endfunction
			

function! SmsSend()
	let l = getline('.')
	let l = substitute(l, "'", '"', 'g')
	"call system("mobmgr-queue -s " . g:sms_number . " '" . l . "' &")
	"call system("echo '" . l. "' >" . NewTempFile("/var/spool/sms/" . g:sms_number))
	exe "!echo '" . l ."' | gnokii --sendsms " . g:sms_number . " -r"

	let date=system('date')
	let date = ClearNewline(date)
	exe "!echo '\\n" . g:sms_number . ": " . date .  ":"  . l ." ' >> ~/dbase/sms/sent/sent.success.smslist"
	let &stl = g:old_stl
	bw!
	call system('rm  -f tmp-new.sms')
endfunction


