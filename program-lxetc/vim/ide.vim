
" Module to Make Vim an Ide.
"			K T Ligesh ligesh@lxlinux.com


let g:var = ""
exe 'amenu .1 &Project.Project\ Name<Tab>' .g:var. ' :<CR>'
function! UpdateProject()
	exe 'aun .10 &Project.Project\ Name<Tab>' .g:var
	if (v:this_session == '')
		let g:var = "No_Project"
	else
		let g:var = v:this_session
	endif
	let g:var = escape(g:var, '.')
	exe 'amenu .10 &Project.Project\ Name<Tab>' .g:var. ' :<CR>'
endfunction

hi IdeMessage  ctermfg=red ctermbg=black guifg=red guibg=black


amenu <silent>.1 &Project.&Run<Tab>Ctl-F9	:update <Bar> call IdeMake()<CR>
amenu <silent> .2 &Project.&Build<Tab>Ctl-F10	:update <Bar> make<CR>
amenu .4 &Project.Edit\ &Makefile :vi Makefile<CR>
amenu .9 &Project.-SEP1-	:
amenu .11 &Project.&Create  :browse mksession <CR>: call UpdateProject()<CR>
amenu .12 &Project.&Load :browse source<CR>:call UpdateProject()<CR>
amenu .13 &Project.&Save :call SaveSession()<CR>
call UpdateProject()

hi user5	cterm=bold ctermfg=red ctermbg=black guifg=red guibg=black

function! SaveSession()
	if v:this_session == ''
		browse mksession
	else
		exe 'mksession! ' .v:this_session
	endif
	call UpdateProject()
endfunction

amenu .1 &Debug.Repeat\ &Last<Tab>Ctl-Alt-L	:Ide <CR>
amenu &Debug.&Ide<Tab>Ctl-Alt-I     :Ide 
amenu &Debug.Start\ G&db     :Idestart <CR>
amenu <silent> &Debug.&Step     :Ide step <CR>
amenu <silent> &Debug.&Next     :Ide next <CR>
amenu <silent> &Debug.&Continue    :Ide continue <CR>
amenu <silent> &Debug.&Print    :exe 'Ide print ' . expand('<cword>')<CR>
amenu <silent> &Debug.&Break	   :exe "Ide break " .expand("%:t").":".line('.')<CR>
amenu <silent> &Debug.&Run	   :exe "Ide run " .GetExe('args')<CR>
amenu <silent> &Debug.&Until	   :exe "Ide until " .expand("%:t").":".line('.')<CR>
amenu <silent> &Debug.&Quit	   :Ide q <CR>
amenu <silent> &Debug.&Interrupt	   :Ide vintr<CR>
amenu <silent> &Debug.&Terminate	   :Ide vterm<CR>
amenu <silent> &Debug.&Kill\ Gdb\    :call DoGdbKill()<CR>:call DoGdbQuit()<CR>
amenu <silent> &Debug.&Other\ Commands.&Up\ Stack	:Ide up<CR>
amenu <silent> &Debug.&Other\ Commands.&Down\ Stack	:Ide down<CR>
amenu <silent> &Debug.&Other\ Commands.Info\ &Break :Ide info break<CR>
amenu <silent> &Debug.&Other\ Commands.&Stepi	:Ide stepi<CR>
amenu <silent> &Debug.&Other\ Commands.&Nexti	:Ide nexti<CR>
amenu <silent> &Debug.&Other\ Commands.&Help	:Ide help<CR>

amenu &Debug.-SEP2-	:
amenu <silent> &Debug.&Assembley :Asmopen<CR>

hi Current ctermfg=DarkGreen guifg=Green
map <silent> <C-F9>	:update <Bar> call IdeMake()<CR>
map <silent> <C-F10>	:update <Bar>make <CR>
map <silent>  <C-M-L>	:Ide<CR>
map  <C-M-I>	:Ide 
map <Leader>b	:Ide 
map <Leader>c	:Ide<CR>
map <S-LeftMouse> :exe 'Ide print ' . expand('<cword>')<CR> 

if !exists('g:gdb_statusline')
	let g:gdb_statusline = 1
endif


if !exists('g:gdb_file')
	let g:gdb_file = ''
endif

if !exists('g:gdb_running')
	let g:gdb_running = 0
endif
if !exists('g:gdb_disass')
	let g:gdb_disass = 0
endif

if !exists('g:gdb_pid')
	let g:gdb_pid = 0
endif

if !exists('g:gdb_vimserver')
	let g:gdb_vimserver = 0
endif

if !exists('g:g_cygwin')
	let g:g_cygwin = 0
endif

if !exists('g:gdb_cwindow')
	let g:gdb_cwindow = 1
endif
if !exists('g:gdb_cmd')
	let g:gdb_cmd = ''
endif

if !exists('g:gdb_working')
	let g:gdb_working = 0
endif

if !exists('g:gdb_sync')
	let g:gdb_sync = 0
endif


if !exists('g:gdb_pending')
	let g:gdb_pending = ''
endif


if !exists('g:gdb_busy_sign')
	let g:gdb_busy_sign = 'Busy:'
endif


com! -nargs=* -complete=tag  Ide call GdbCall(<f-args>)
com! -nargs=* -complete=tag Idestart call GdbCall('start', <f-args>)
com! -nargs=* -complete=file Idemake call IdeMake()
com! -nargs=0  Asmopen call AsmColor("open")
com! -nargs=*  Asmshow call AsmShow(<f-args>)


au!  BufEnter .gt_data	call GdbDataEnter()
au!  BufLeave .gt_data	call GdbDataLeave()
au!  BufReadPost .gt_data call GdbDataRead()
"au!  VimLeave  *  call GdbVimLeave()


function! GdbVimLeave()
	if g:gdb_running && g:gdb_pid
		call DoGdbKill()
	endif
endfunction

function! GetExe(flag)
	let cmd = system("make -n vim_run")
	if v:shell_error == 2
		echo "Please create a 'run' target in the makefile. See :help vim_run" 
		return ''
	endif
	if a:flag == "name"
		let ret = substitute(cmd, '\(\f*\).*', '\1', '') 
	elseif  a:flag == "args"
		let ret = substitute(cmd, '^\s*\f*\s*', '', '') 
	else 
		let ret = substitute(cmd, '\(\f*\)\(\p*\).*', '\2', '') 
	endif
	return ret
endfunction

function! IdeMake()
	update
	echohl statusline
	if(filereadable("Premake"))
		source Premake
	endif
	let file = tempname()
	let cmdfile = tempname()
	echohl IdeMessage
        echo 'Making.. '
        echohl None
	exe "!make 2>&1| tee " .file. " ; exit $PIPESTATUS "

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
		else
			exe "cf ". file
			if g:gdb_cwindow
			cwindow
		endif
	endif
	exe 'silent !rm -f ' .file. ' ' .cmdfile
endfunction

function! GdbDataRead()
	$
	normal z-
	"set nobuflisted
endfunction


function! ColorCurrentBrows()
	syn clear Current
	"let curline=escape(getline('.'),'*~[]$&|> ')
	let curline = escape(getline("."), "\'*~[].")
	exe "syn match Current " . "\'^" .curline. "$\'"
endfunction



function! ClearVar(var, val)
	let mx = '\C\(.*\)' .a:val. '\(.*\)'
	let ret = substitute(a:var, mx, '\1\2', '')
	return ret
endfunction


function! MyStlGdb()
	hi statusline	term=none cterm=none ctermbg=black ctermfg=darkgreen
	hi statuslineNC	term=none cterm=none ctermbg=black ctermfg=darkred
	hi user1	term=none ctermfg=DarkGreen guifg=DarkCyan
	hi user3	term=none ctermfg=DarkCyan guifg=DarkCyan
	hi user4	term=none ctermfg=Black ctermbg=DarkBlue guifg=black guibg=DarkBlue

	let g:stl_a='%*[%*%n]\ %3*%2f\ %2*%m%='
	let g:stl_b=''
	let g:stl_c='%*%4l%2*%3c'
	call SetStl()
endfunction

com! -nargs=* -complete=tag Ide call GdbCall(<f-args>)

function! SetStl()
	if (g:stl_b =='')
		exe 'set stl='. g:stl_a . g:stl_b . g:stl_c 
	else
		exe 'set stl=' .g:stl_a. '\ [' .g:stl_b. ']\ ' .g:stl_c
	endif
endfunction


function! AsmFilter()
	let g:pos=line(".")
	silent %s/\s\+/ /g
	silent %s/<.*>://
	silent update
	exe g:pos
	unlet g:pos
endfunction

function! GetAsmAddress()
	if exists('g:gdb_asm_address')
		let var = g:gdb_asm_address
		unlet  g:gdb_asm_address
		return var
	endif

	let var = system("cat .gt_fpos")
	if (v:shell_error != 0)
		echo "The program is not being run"
		return ''
	endif
	syntax clear Current
	let var = substitute(var, '^.*:', '','g')
	let var = substitute(var, '.$', '','g')
	return var
endfunction

function! AsmShow(var)
	let g:gdb_asm_address = a:var
	call AsmColor('open')
endfunction

function! AsmColor(flag)
	"hi Current ctermfg = DarkGreen
	let asmwin = bufwinnr(".gt_asm")
	if(asmwin == -1)
		if(a:flag != "open")
			return
		endif
		let sprsave = &splitright
		let &splitright = 1
		vs .gt_asm
		let &splitright = sprsave
		let asmwin = bufwinnr(".gt_asm")
	endif

	if (asmwin == winnr() ) |  let samewin = 1
	else | exe asmwin ." wincmd w"
	endif

	if a:flag == "open"
		if exists('samewin')
			unlet samewin
		endif
	endif

	if !filereadable(g:gdb_file)
		return
	endif

	let ret = libcallnr("/usr/lib/libvimgdb.so", "checkTimeStamp", g:gdb_file)
	if ! ret 
		echohl ErrorMsg
		echo "The Executable is more Recent than the asm file"
		echohl None
		1,$d _
		silent write
	endif

	let var = GetAsmAddress()
	if var == '' 
		if !exists('samewin')
			wincmd p
		endif
		return
	endif

	if (g:gdb_disass)
		silent e!
		call AsmFilter()
		"call input('asmshow ')
		let g:gdb_disass = 0
		call SetGdbStl(0, "disass")
	endif

	let v:errmsg = ""
	exe 'silent! /^' . var

	if v:errmsg != ""
		echo "Disassmbling the Function. Please wait..."
		call system("echo")
		"call system('rm .gtbcom ; /bin/cp .gt_asminit .gtbcom')
		"call system('echo disassemble ' .var. '>> .gtbcom')
		"call system('gdb -batch -x .gtbcom ' .g:gdb_file. '>> ' .expand('%'))

		if (g:g_cygwin)
			let g:gdb_disass = 1
			silent Ide disassembling__wait__
		else
			let g:gdb_sync = 1
			silent Ide disassembling__wait__
			let g:gdb_sync = 0
		endif
			

	endif

	call histdel('search', var)

	exe "syntax match Current '" . var . "'"
	if !exists('samewin') 
		normal zz
		wincmd p
	endif
endfunction
	
function! IfBitSet(val, bit)
	let var = a:val
	let idx = a:bit
	while idx
		let var = var / 2
		let idx = idx - 1
	endwhile
	let var = var % 2
	return var
endfunction

function! SplitStl()

	if &stl == ''
		call MyStlGdb()
	endif


	let stl_val=&stl
	if !exists('g:stl_a')
		let g:stl_a = stl_val
		let g:stl_b = ''
		let g:stl_c = ''
	endif

"********** The second check is not needed for general cases
	if match(stl_val, 'Gdb:') != -1  || match(stl_val, ':] ') != -1
		return 
	endif

	if &ls != 2 
		let g:save_ls = &ls
		let &ls = 2
	endif


	let g:stl_a = substitute(stl_val, '\(.*\)%=\(.*\)', '\1%=', '')
	let g:stl_a = escape(g:stl_a, ' "')
	if g:stl_a != stl_val
		let g:stl_c = substitute(stl_val, '\(.*\)%=\(.*\)', '\2', '')
		let g:stl_c = escape(g:stl_c, ' "')
	endif
endfunction



function! DoGdbData()
	let gwin = bufwinnr('.gt_data')
	let g:g_file_type = ''
	if (&ft == 'php')
		let g:g_file_type = 'php'
	endif
	if gwin == -1
		"let sbsave = &splitbelow
		let &splitbelow = 1
		"vertical split .gt_data
		split .gt_data
		"let &splitbelow = sbsave 
		silent e! 
		wincmd J | res 7 
		"vert res 30
		normal z-
		set nowrap
		call GdbDataEnter()
		silent! wincmd p
		return
	endif
	if gwin == winnr()
		silent e!
		call GdbDataEnter()
		return
	endif

	exe 'silent! ' gwin .' wincmd w'
	silent e!
	call GdbDataEnter()
	wincmd p
endfunction

function! GetWinNrSource()
	let idx = 1
	while idx <= bufnr("$")
		if winbufnr(idx) != -1
			let file = bufname(winbufnr(idx))
			if file == ""
				return idx
			endif
			if file != bufname('.gt_asm') && file != bufname('.gt_data')
				return idx
			endif
		endif
		let idx = idx + 1
	endwhile
	return -1
endfunction


function! DoGdbLine()
	let gwin = GetWinNrSource()
	if( gwin == -1)
		return 
	endif
	if( gwin == winnr())
		syntax clear Current
		cf .gt_fpos
		return
	endif

	exe gwin. ' wincmd w'
	cf .gt_fpos
	call ColorCurrentBrows()
	wincmd p
endfunction

function! DoGdbErrn()
	echohl ErrorMsg
	!cat .gt_ferr
	echohl None
	"echo 'There is an error '
endfunction

function! DoGdbCont()
	if !exists('g:in_continue')
		echo "Terminate the input with a lone 'end' on a line"
		let g:in_continue = 1
	endif
	let var = input('Gdb> ')
	call GdbCall(var)
endfunction

function! CheckForServer()
	if !filereadable('.gt_fpid')
		return 0
	endif
	let gdbpid = system("cat .gt_fpid")
	let cmdline = system("cat /proc/" .gdbpid. "/cmdline")

	if match(cmdline, 'gdb') != 0
		silent !rm -f .gt_fpid
		return 0
	endif
	return 1
endfunction

function! DoGdbQuit()
	echohl  Statusline
	echohl None
	let var_stl = &stl

	if match(var_stl, "Gdb:") != -1
		let g:stl_b = ClearVar(g:stl_b, g:gdb_busy_sign)
		let g:stl_b = ClearVar(g:stl_b, 'Gdb:[^:]*:')
		call SetStl()
	endif

	if exists('g:save_ls')
		let &ls = g:save_ls
		unlet g:save_ls
	endif
	silent! bw! .gt_data
	let g:gdb_running = 0
	let g:gdb_working = 0
	let g:gdb_pid = 0

	silent! bw! .gt_asm
	silent! !rm -f .gt_asm
endfunction

function! DoGdbKill()	
	if !g:gdb_pid
		let g:gdb_pid = system('cat .gt_fpid')
	endif

	silent! exe '!kill ' .g:gdb_pid
	silent! exe '!kill -9 ' .g:gdb_pid
endfunction

function! GdbDataChange(val)
let DATA = 0
let LINE = 1
let CONT = 2
let ERRN = 3
let QUIT = 4
let CLRS = 5
let NRES = 6

	
	if(g:gdb_statusline)
		call SetGdbStl(0, g:gdb_cmd)
	endif

	let g:gdb_working = 0

	if g:gdb_pid == 0
		let g:gdb_pid = system('cat .gt_fpid')
	endif


	if a:val > 64 
		echohl ErrorMsg
		echo 'Unacceptable Return Value: ' .a:val. '... Continuing '
		echohl None
		return
	endif

	if IfBitSet(a:val, LINE)
		call DoGdbLine()
		call AsmColor("show")
	endif

	if g:gdb_disass
		call AsmColor("show")
	endif

	if IfBitSet(a:val, DATA)
		call DoGdbData()
	endif

	
	if IfBitSet(a:val, ERRN)
		call DoGdbData()
	endif

	if IfBitSet(a:val, QUIT)
		call DoGdbQuit()
	endif


	if IfBitSet(a:val, CONT)
		call DoGdbCont()
	endif

	if IfBitSet(a:val, CLRS)
		normal 

	endif

	if IfBitSet(a:val, NRES)
		call DoGdbKill()
		echo "No response..... Killing Gdb"
		call DoGdbQuit()
	endif

	call RunPending()

endfunction
	

function! SendToServer(cmd, var)
	let vv = a:var
	if g:gdb_vimserver
		let vv = '-server '	.v:servername. ' ' .vv
		let ret = libcallnr("/usr/lib/libvimgdb.so", a:cmd, vv)
		"sleep 300m
	elseif &term == 'builtin_gui'
		let ret = libcallnr("/usr/lib/libvimgdb_gtk.so", a:cmd, vv)
	else
		let ret = libcallnr("/usr/lib/libvimgdb.so", a:cmd, vv)
	endif

	return ret
endfunction

function! SetGdbStl(flag, cmd)
	call SplitStl()
	let a_cmd = substitute(a:cmd, '\(\w*\).*', '\1', '')
	let g:stl_b = ClearVar(g:stl_b, g:gdb_busy_sign)
	if a:flag == 1 
    	if a_cmd != ''
			let g:stl_b = ClearVar(g:stl_b, 'Gdb:[^:]*:')
			let g:stl_b = g:stl_b .g:gdb_busy_sign. 'Gdb:'. escape(a_cmd, ' '). ':'
		else
			let g:stl_b = substitute(g:stl_b, 'Gdb:[^:].*:', g:gdb_busy_sign.'&', '')
		endif
	else 
		if g:gdb_cmd != ''
			let g:stl_b = ClearVar(g:stl_b, 'Gdb:[^:]*:')
			let g:stl_b = g:stl_b .'Gdb:'. escape(a_cmd, ' '). ':'
		endif
	endif
	call SetStl()
endfunction


function! RunPending()
	if g:gdb_pending != ''
       		let var = g:gdb_pending
       	 	let g:gdb_pending = ''
		call GdbCall(var)
       	endif
endfunction

function! GdbCall(...)
	"set updatetime=500
	"set nocompatible
	if (strlen('$CYGWIN'))
		let $CYGWIN=""
		let g:g_cygwin = 1
	endif
	if g:gdb_working
		if a:0 < 1 
			echohl moremsg
			echo 'Gdb is busy. Use ":Ide vintr" to interrupt. See ":help vintr"'
			echohl None
			return
		endif
		if g:gdb_pid == 0
			let g:gdb_pid = system('cat .gt_fpid')
		endif
		if a:1 == 'vintr'
			exe 'silent! !kill -INT ' .g:gdb_pid 
		elseif a:1 == 'vterm'
			exe 'silent! !kill -TERM ' .g:gdb_pid
		elseif a:1 == 'vkill'
			exe 'silent! !kill -KILL ' .g:gdb_pid
			let g:gdb_working = 0 
			call DoGdbQuit()
		else
			echohl moremsg
			echo 'Gdb is busy. Use ":Ide vintr" to interrupt. See ":help vintr"'
			echohl None
		endif
		return 
	endif
		
	let var = ''
	let idx = 0
	let vstart = 0
	let add_exe_file = 0
	if (a:0 >= 1) 
		let g:gdb_cmd = a:1
	else
		let g:gdb_cmd = ''
	endif

	if exists('v:servername') && v:servername != '' && !g:gdb_sync
		let g:gdb_vimserver = 1
	else
		let g:gdb_vimserver = 0
	endif

	if(a:0 >= 1 && a:1 == "start")
		let vstart = 1
		let mapleader = 'g'
		if CheckForServer()
			let g:gdb_running = 1
			call RunPending()
			return 
		endif
	endif

	if !exists('g:gdb_file')
		let g:gdb_file = ''
	endif


	if vstart == 1
		if a:0 > 1 
			let g:gdb_file = a:2 
		else
			let g:gdb_file = GetExe('name')
			let add_exe_file = 1
		endif
		let idx=2
	else 
		let idx=1
	endif

		
	while idx <= a:0
		exe "let p=" . "a:" . idx
		if (idx == 1)
			let var= p
		else
			let var = var. ' ' .p 
		endif
		let idx = idx + 1
	endwhile

	if add_exe_file
		let var = var. ' ' .g:gdb_file
	endif


	if(vstart == 1)
		let g:gdb_pid = 0
		let var = "'" . var .  "'"
		let ret = SendToServer('startServer', var)
		let g:gdb_running = 1
	else
		if g:gdb_running == 0
			let g:gdb_pending = var
			call GdbCall('start')
			return
		endif
		let ret = SendToServer('writeServer', var)
	endif

	if !g:gdb_vimserver
		call GdbDataChange(ret)
	else
		call SetGdbStl(1, g:gdb_cmd)
		let g:gdb_working = 1
	endif

	if exists('g:in_continue')
		unlet g:in_continue
	endif

endfunction


function! GdbAttach()
    if g:gdb_file == ''
        let prgname = GetExe('name')
    else
        let prgname = g:gdb_file
    endif

    let pid = system('pidof ' .prgname)
    let pid = substitute(pid, '\(\w*\).*', '\1', '')
    exe 'Ide att ' .pid
endfunction

function! GdbDataEnter()
	set filetype=none
	map	<C-m>	:call GdbCall()<CR>
	"set bufhidden=delete
	set buftype=nowrite
	set noswapfile
	setlocal syntax=none
	hi GdbPrompt ctermfg=DarkCyan guifg=Cyan
	syntax clear GdbPrompt
	hi GdbAddress ctermfg=DarkGreen guifg=Green
	syntax match GdbAddress '<ligesh@lxlinux.com>'
	syn match GdbPrompt 'Gdb>'
	if (g:g_file_type == 'php') 
		silent! g/ =>$/normal J
		silent! g/\[__desc_.*\] => array(3)/.,.+4d
		silent! %s/ string(\(\d\+\))/ (\1)/g
		silent! %s/^string(\(\d\+\))/ (\1)/g
	endif
	$

	syntax region GdbPhpKey matchGroup=GdbDelim start="\[" end="\]"
	syntax match GdbObject '.*object(.*) {'
	syntax match GdbArray '.*array(.*) {'

	hi GdbPhpKey ctermfg=5
	hi GdbObject ctermfg=Green
	hi GdbArray ctermfg=Brown
	hi GdbDelim ctermfg=none

endfunction

function! GdbDataLeave()
	"call SetViminfo()
	silent! unmap <C-m>
endfunction
