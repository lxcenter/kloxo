"*************************** Vbfnc ***************

let g:old_cwd =  ''
let g:browser_help_on = 0
let g:browse_start_dir = ''
let g:update_prog = 0

let g:browser_func = ''

let g:browser_file = '~/.etc/.tmp/Browser.'

function! BrowserLeaveC()
endfunction

function! BrowserEnterC()
	call BrowserSyntax()
endfunction

function! ColorMoveMap(var)
	let g:fvar=a:var
	nor <silent>	<Up>	k:call ColorCurrent{g:fvar}()<CR>
	nor <silent>	<Down>	j:call ColorCurrent{g:fvar}()<CR>
	vnor 	i 	k
	vnor	k	j
	map 	i    <Up>
	map 	k <Down>
endfunction

function! ColorCurrentList()
	hi CurrentFile ctermbg=Black ctermfg=DarkGreen guifg=#00cccc
	syn clear Current CurrentFile
	let curline = getline(".")

	let curfile = matchstr(curline, '".*"')
	let curfile = escape(curfile, '"~[]$&\')

	if(curfile != "")
		exe "syn match CurrentFile " . "\"" . curfile . "\""
	endif
endfunction

function! BrowserActionC()

	if exists('g:browser_select_all') 
		let dirval = g:browser_select_all
	else 
		let dirval = getline(".")
	endif

	let dirval = getcwd() .'/'. dirval
	let dirval = escape(dirval, " $&'-()%")


	if(exists("g:not_standalone"))
		exe 'bd!' . g:browser_file
	endif

	exe "e " . dirval 

	if exists('g:not_standalone')
		unlet g:not_standalone
	endif

endfunction


if !exists('g:VimAmp')
	let g:VimAmp = 0
endif

function! ChangeDir(val)
	let dirval = escape(a:val, " $&'-()%")
	exe "lcd " . dirval
endfunction

function! ChangeDirLine()
	let dirval = getline('.')
	bd
	let dirval = escape(dirval, " $&'-()%")
	exe 'lcd ' . dirval
endfunction


function! DirStackEnter()
	set nu
	imap 	<Tab>	<C-F>
	map	f	:call ChangeDir(getline("."))<CR>:bd<CR>
			\:exe '23sp ' .g:browser_file<CR><Space>
	map	l	:call ChangeDirLine()<CR>
	map <CR> l
	normal 49%
	"map	<F50>f	:bd<CR>
endfunction

function! DirStackLeave()
	iun	<Tab>
	unmap l
	unmap f
	unmap <CR>
	"map	<F50>f	:q<CR>
endfunction

function! BrowserLeaveLock()
	set nonu
	nor	<Left>	<Left>
	"call ReMaps()
	exe 'bd! '. g:browser_file
	only
endfunction


function! Process(val)
	let g:browser_help_on = 0

	if match(a:val, "Directory of:") != -1
		return 
	endif

	if bufname("%") != bufname("Browser.") 
		return
	endif		

	let val = substitute(a:val, '@$', '', '')

	if isdirectory(val) && !exists('g:browser_select_all')
		let dirval=escape(val, " $&'-()%")
		exe 'lcd ' dirval
		let g:browse_start_dir = getcwd()

		if(val != '..')
			let g:prepos = line('.')
		endif

		%d _
		call setline(1, '\	Directory of: ' . getcwd())
		call append(1, '..')
		2
		exe 'r !' . g:list
		if  a:val != '..' || g:prepos == 0 
			2
		else
			exe g:prepos
		endif

		update
		call ColorCurrentBrows()
	else 
		call BrowserActionC{g:browser_func}()
	endif
endfunction

function! ToggleAll()
	if(exists("g:browser_select_all"))
		unlet g:browser_select_all
		call ColorCurrentBrows()
		call ColorMoveMap("Brows")
		syn clear Select
	else
		let g:browser_select_all=getcwd() . "/*"
		syn match Current  ".*"
		nor i	k
		nor k	j
	endif
endfunction


function! BrowserSyntax()
	syn match directory "[^ ].*\/"
	syn match Heading ".*Directory of: .*"
	hi Heading cterm=none ctermfg=DarkGreen
endfunction

function! ColorCurrentBrows()
	syn clear Current
	"let curline=escape(getline("."),'"*~[]$&|> ')
	let curline=escape(getline("."),"\'*~[].")
	exe "syn match Current " . "\'^" . curline . "$\'"
endfunction


function! SelectAdd(val)
	if !exists('g:browser_select_all')
		let g:browser_select_all=''
	endif

	let g:browser_select_all=g:browser_select_all . "	" . getcwd() . "/" . a:val
	let sel=escape(a:val,"*.~")
	exe "syn match Select \"" .  sel . "\""
endfunction

function! SelectClear()
	syn clear Select 
	if  exists("g:browser_select_all") 
		unlet g:browser_select_all
	endif
endfunction

function! BrowserHelpToggle()
	if g:browser_help_on
		let g:browser_help_on = 0
		2
		2,8d
		"call setline(2, '  Press <?> for Browser Keystrokes')
		write
	else
		let g:browser_help_on = 1
		call append(1, "        ?: Toggle Help")
		call append(1,  "        q: Quit Browser")
		call append(1,  "        g: Change Directory")
		call append(1,  "        r: Recursively Add Directory")
		call append(1,  "        s: Select,    S: UnSelect")
		call append(1,  "        a: Select-All")
		call append(1,  "    Enter: Add Selected Song(s)")
		call append(1, "Keystrokes:")
		2
		call ColorCurrentBrows()
		write
	endif

endfunction

function! BrowserEnter()
	nor <Space> 	<Space>

	map ?	:call BrowserHelpToggle()<CR>
	if g:update_prog
		set ls=2
		return
	endif
	if !exists('g:browser_file')
		let g:browser_file = '~/.etc/.tmp/Browser.'
	endif
	set modifiable
	"set nu
	set nowrap
	set wrapscan
	let g:ic=&ic
	set ic
	"call WinsizeFile()


	if g:dull == 'Dark'
		hi Select ctermfg=DarkRed
	else
		hi Select ctermfg=Yellow
	endif

	if (exists('g:not_standalone'))
		let g:list = "ls -F -I '*.o'"
	endif
	syn clear Select
	let g:prepos = 0
	if(exists("g:browser_select_all")) 
		call ToggleAll()
	endif

	if g:browse_start_dir == ''
		let cur = getline(1)
		if  match(cur, 'Directory of:') != -1
			let cur = substitute(cur, '\s\+Directory of: ', '', '')
		else
			let cur = '.'
		endif
	else
		let cur = g:browse_start_dir
	endif

	if(isdirectory(cur))
		let cur = escape(cur, " %$&'-()")
		exe 'lcd ' .cur
		else
			lcd /
	endif

	let b_pos = line('.')

	%d _
	call setline(1, "\	Directory of: " . getcwd())
	call append(line('$'), '  Press ? for Keyboard Shortcuts')
	call append(line('$'), "..")
	$
	exe "r !" . g:list
	update
	if(winnr() == 1)
		exe b_pos
	else 
		2
	endif

	if exists('g:not_standalone')
		exe 'silent! /' .expand('#:t'). '$'
	endif

	call ColorMoveMap("Brows")
	map	<Left>	:call Process("..")<CR>
	map	<BS>	<Left>
	map	j	<Left>
	map	<Right>	:call Process(getline("."))<CR><Space>
	map	<C-m>	<Right>
	map	l	<Right>
	map	a	:call ToggleAll()<CR>
	map	g	:ChangeDirectory 

	if(exists('g:not_standalone'))
		nor q	  :unlet g:not_standalone<Bar>b #<Bar> exe 'bd! ' .g:browser_file<CR>:q!<CR>
	else
		map q	:q!<CR>
	endif

	map	<F50>d  <nop>

	call BrowserEnterC{g:browser_func}()

	call ColorCurrentBrows()

endfunction

function! RecurseAdd(val)
	if (!isdirectory(a:val))
		return
	endif
	let dirval = escape(a:val, " $&%'-()")
	let dirval = getcwd() . "/" . dirval
	call system("find " .dirval. " -type f >> ~/.etc/VimAmp/VimAmp.m3u")
	call system("killall -USR1 lxmpg")
	call AmpBrowserExit()
endfunction

function! BrowserUnMap()
	unmap a
	unmap <Left>
	unmap <Right>
	unmap <Up>
	unmap <Down>
	unmap <Enter>
endfunction
	

function! BrowserLeave()
	if g:update_prog
		return
	endif

	call BrowserUnMap()

	if(!exists('g:not_standalone'))
		map   <Left>	<C-^>
		map	j	<C-^>
	else
		nor	<Left>	<Left>
	endif

	call BrowserLeaveC{g:browser_func}()
endfunction


function! BrowserDelete()
	unmap <Left>
	"call ReMaps()
endfunction

	

com! -complete=dir -nargs=* ChangeDirectory call Process(<f-args>)

"*************************** End Vbfnc ***************
