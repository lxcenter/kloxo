" ************************ Vmmap **************************

map	<M-d>	:call GdbCall()<CR>
cmap	<M-a>	<C-C>@q
map	<M-b>	:ls<CR>:let nr=input("Buffer: ")<Bar>exe "b " . nr<CR>
map	<M-m> 	:n <C-l><C-d>
nor	<M-f> 	:call ListBuf()<CR>
imap	<M-f> 	<C-o>w
ima	<M-w> 	<C-o>w
ima 	<M-b>	<C-o>b
cma 	<M-f>	<S-Right>
cma 	<M-w>	<S-Right>
cma 	<M-b>	<S-Left>
map	<M-F>	:call CreateBrowser()<CR>
map	<C-M-F>	:call CreateBrowser()<CR>
map	<M-B> 	:b <C-Z>
map	<M-N>	:bN<CR>
map	<M-n>	:bn<CR>
noremap	<M-v> 
map	<M-E>	[I:let nr=input("Search: ")<Bar>exe "normal " . nr ."[\t"<CR>
map	<M-s>	:call GdbCall()<CR>
map	<M-p>	:call GdbCall()<CR>
map	<M-*>	:exe "Ide until " .expand("%:t").":".line('.')<CR>
map	<M-^>	:exe "Ide i line " .expand("%:t").":".line('.')<CR>
map	<M-&>	:exe "Ide b " .expand("%:t").":".line('.')<CR>
map	<M-Q>	:call IdeCall("c")<CR>
map	<M-r>	:marks<CR>:let nr=input("Mark:")<Bar>exe 
		\"normal" ."`" . nr<CR>
inor	<M-e>	<C-e>
inor	<M-y>	<C-y>
imap	<M-E>	<M-e>
imap	<M-Y>	<M-y>
map	<M-t>	:call SpellList('nosingle')<CR>
map	<M-z>	:call ColorCurrent()<CR>
map	<M-Z>	:call JustColor()<CR>
nor <silent>	<C-f>	:let &scr=winheight(0)<CR><C-d>
nor <silent>	<C-b>	:let &scr=winheight(0)<CR><C-u>

"********* Grep for <M-g>
call MapID()
function! ExecAMap()
	if(exists("g:exa"))
		unlet g:exa
		nor	<M-a>	:call GdbFullVar(GetCWord(), '')<CR>
		nor	<M-A>	:call GdbFullVar(expand('<cword>'), '*')<CR>
		vnor	<M-a>	y:call GdbFullVar(@0, '')<CR>
		vnor	<M-A>	y:call GdbFullVar(@0, '*')<CR>
	else
		let g:exa=1
		map	<M-a>	@q
	endif
endfunction
let g:exa=1
call ExecAMap()


function! TagJump()
	let ssic =  &ic
	let &ic = 0
	let v:errmsg = ''
	exe 'silent! tag ' .expand('<cword>')
	if v:errmsg == ''
		silent pop
		exe 'tjump ' .expand('<cword>')
	else
		echohl ErrorMsg
		echo 'Tag "' .expand("<cword>"). '" not found'
		echohl None
	endif
	let &ic = ssic
endfunction



nor	<M-2>	<C-T>
inor	<M-2>	<ESC><C-T>
map	<M-w>	:call TagJump()<CR>
"map	<M-w>	:silent! let g:ssic=&ic\|silent set noic<CR>:exe 'tjump ' . expand("<cword>")\|let &ic=g:ssic<CR>
"map	<C-[>w	g<C-]>
"ima	<M-w>	<ESC><C-]>

" ************* Special for Alt- w
map	<M-e>   :cn<CR>


map	<M-3>   :cN <CR>
ima	<M-3>	<ESC>:cN<CR>
map	<M-1>	:call GdbEnterFunction()<CR>
map	<M-q>	:call GdbScrollCall("n")<CR>
map	<M-!>	:call GdbScrollCall("fs")<CR>

function! GdbScrollCall(cmd)
		let tmp = &scrolloff
		let &scrolloff = 3
		call GdbCall(a:cmd)
		"Big Bug.... Needs a good fix to. Ligesh
		"exe 'normal ' . "\<C-c>"
		let &scrolloff = tmp
endfunction

map	<M-x>	:
ima	<M-x>	:
nor	<M-z>	:call GdbFullVar(expand('<cword>'), '*')<CR>
vnor	<M-A>	y:call GdbFullVar(@0, '*')<CR>
vnor	<M-z>	y:call GdbFullVar(@0, '*')<CR>
map	<M-]>	@s
map	<M-I>	:bN<Bar>ls<CR>
map	<M-K>	:bn<Bar>ls<CR>
map 	<M-O>	:!pwd;ls --color<CR>
map	<M-M>	:Mancmd sys 
"map	<M-g> 	:let efsave=&ef<Bar>let &ef=tempname()<Bar>exe ':
		\!grep -ni -w "<cword>" *.[ch] *.cc *.txt *.py *.cpp >'.&ef<CR>
		\:cf<CR>:exe " \:!rm ".&ef<CR>:let &ef=efsave<Bar>
		\unlet efsave<CR><CR>:cc<CR>

" *********** Map For M-c
call CompileMap(3) 

" ************************ End Vmmap **************************
