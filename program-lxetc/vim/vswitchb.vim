
let g:iswitchb_var = ''
let g:iswitchb_prev_var = ''
let g:iswitchb_prev_stl = ''

function! IswitchLastSearch()
	let g:iswitchb_prev_var = g:ISW_PR_VR
	let g:iswitchb_var = g:ISW_VR
	call IswitchbUpdateBuffer()
endfunction

function! IswitchbSaveVar()
	let g:ISW_VR = g:iswitchb_var
	let g:ISW_PR_VR = g:iswitchb_prev_var
endfunction

function! IswitchbCommandMap()

	map <buffer> <M-i>    <Up>
	map <buffer> <M-k>     <Down>
	unmap gi
	unmap gk
	nor <buffer> <Left> :call IswitchLastSearch()<CR>
	"nor <buffer> 0 :call IswitchbCommandChar("0")<CR>
	nor <buffer> 1 :call IswitchbCommandChar("1")<CR>
	nor <buffer> 2 :call IswitchbCommandChar("2")<CR>
	nor <buffer> 3 :call IswitchbCommandChar("3")<CR>
	nor <buffer> 4 :call IswitchbCommandChar("4")<CR>
	nor <buffer> 5 :call IswitchbCommandChar("5")<CR>
	nor <buffer> 6 :call IswitchbCommandChar("6")<CR>
	nor <buffer> 7 :call IswitchbCommandChar("7")<CR>
	nor <buffer> 8 :call IswitchbCommandChar("8")<CR>
	nor <buffer> 9 :call IswitchbCommandChar("9")<CR>
	nor <buffer> a :call IswitchbCommandChar("a")<CR>
	nor <buffer> . :call IswitchbCommandChar(".")<CR>
	nor <buffer> b :call IswitchbCommandChar("b")<CR>
	nor <buffer> c :call IswitchbCommandChar("c")<CR>
	nor <buffer> d :call IswitchbCommandChar("d")<CR>
	nor <buffer> e :call IswitchbCommandChar("e")<CR>
	nor <buffer> f :call IswitchbCommandChar("f")<CR>
	nor <buffer> g :call IswitchbCommandChar("g")<CR>
	nor <buffer> h :call IswitchbCommandChar("h")<CR>
	nor <buffer> i :call IswitchbCommandChar("i")<CR>
	nor <buffer> j :call IswitchbCommandChar("j")<CR>
	nor <buffer> k :call IswitchbCommandChar("k")<CR>
	nor <buffer> l :call IswitchbCommandChar("l")<CR>
	nor <buffer> m :call IswitchbCommandChar("m")<CR>
	nor <buffer> n :call IswitchbCommandChar("n")<CR>
	nor <buffer> o :call IswitchbCommandChar("o")<CR>
	nor <buffer> p :call IswitchbCommandChar("p")<CR>
	nor <buffer> q :call IswitchbCommandChar("q")<CR>
	nor <buffer> r :call IswitchbCommandChar("r")<CR>
	nor <buffer> s :call IswitchbCommandChar("s")<CR>
	nor <buffer> t :call IswitchbCommandChar("t")<CR>
	nor <buffer> u :call IswitchbCommandChar("u")<CR>
	nor <buffer> v :call IswitchbCommandChar("v")<CR>
	nor <buffer> / :call IswitchbCommandChar("/")<CR>
	nor <buffer> w :call IswitchbCommandChar("w")<CR>
	nor <buffer> x :call IswitchbCommandChar("x")<CR>
	nor <buffer> y :call IswitchbCommandChar("y")<CR>
	nor <buffer> z :call IswitchbCommandChar("z")<CR>
	nor <buffer> . :call IswitchbCommandChar(".")<CR>
	nor <buffer> 1 :call IswitchbCommandChar("1")<CR>
	nor <buffer> <Space>  :call IswitchbBreak()<CR>
	nor <buffer> 2 :call IswitchbCommandChar("2")<CR>
	nor <buffer> 3 :call IswitchbCommandChar("3")<CR>
	nor <buffer> 4 :call IswitchbCommandChar("4")<CR>
	nor <buffer> 5 :call IswitchbCommandChar("5")<CR>
	nor <buffer> 6 :call IswitchbCommandChar("6")<CR>
	nor <buffer> 7 :call IswitchbCommandChar("7")<CR>
	nor <buffer> 8 :call IswitchbCommandChar("8")<CR>
	nor <buffer> 9 :call IswitchbCommandChar("9")<CR>
	"nor <buffer> 0 :call IswitchbCommandChar("0")<CR>
	nor <buffer> <C-h> :call IswitchbDelChar()<CR>
endfunction

function! IswitchbBreak()
	let g:iswitchb_prev_var = g:iswitchb_var
	let g:iswitchb_var = ''
	let &l:stl = "[" . g:iswitchb_var . "]-------------" . g:iswitchb_prev_var
endfunction

function! IswitchbDelChar()
	if (g:iswitchb_var == '')
		return
	endif
	let g:iswitchb_var = substitute(g:iswitchb_var , '.$', '', '')
	let g:iswitchb_var = substitute(g:iswitchb_var , '\\$', '', '')
	e!
	call IswitchbUpdateBuffer()
endfunction

function! IswitchbUpdateBuffer()
	let &l:stl = "[" . g:iswitchb_var . "]-------------" . g:iswitchb_prev_var
	if g:iswitchb_prev_var != ''
		silent! exe 'g!+' .g:iswitchb_prev_var. '+d _'
		call histdel("/", -1)
	endif
	if g:iswitchb_var != ''
		silent! exe 'g!+' .g:iswitchb_var. '+d _'
		call histdel("/", -1)
		syn clear IswitchbPat
		exe "syn match IswitchbPat '" . g:iswitchb_var. "'"
	endif
	1
	call ColorCurrent{g:fvar}()
endfunction

function! IswitchbInit()
	if (g:iswitchb_prev_stl != '')
		return
	endif
	let g:iswitchb_prev_stl = &stl
	set stl="----------"
	let g:iswitchb_var = ''
	call IswitchbCommandMap()
	hi IswitchbPat ctermfg=red
endfunction

function! IswitchbLeave()
	let &stl = g:iswitchb_prev_stl
	let g:iswitchb_prev_stl = ''
	call ReMaps()
	call IswitchbSaveVar()
endfunction

function! IswitchbCommandChar(char)
	if (a:char == '.')
		let g:iswitchb_var = g:iswitchb_var . '\' . a:char
	else
		let g:iswitchb_var = g:iswitchb_var . a:char
	endif
	call IswitchbUpdateBuffer()
endfunction

