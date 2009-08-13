"************************** Vcpp *************************
"imap { {<CR> <BS><CR>}<Up><End>
"imap <buffer> { {<CR>
"ab pr printf("
set dict=~/.etc/vim/cdict
set ts=4
set sw=4
set t_ti=
set t_te=
set fo=croql
"set filetype=c
map <buffer> <F10>i  :call Indent("nv")<CR>
vmap <buffer> <F10>i  :call Indent("visual")<CR>

setlocal cindent

function! Indent(flag)
	let n = line('.')
	let c = col('.')
	"echo n c
	if a:flag == "visual"
		'<,'>!indent -kr -ts4 -l100 -
	else
		%!indent -kr -ts4 -l100 - 
	endif
	exe n
	exe 'normal '. c . '|'
	silent! /indent:
endfunction


"************************** Vcpp *************************

"* ~/.etc/vim/vcpp  *
"* ~/.etc/vim/vfunc *
"* ~/.etc/vim/vimrc *
"* ~/.etc/vim/vmaps *
"* ~/.etc/vim/vmeta *
"* ~/.etc/vim/vwin  *
"* ~/.etc/vim/vtmp  *
"* ~/.etc/vim/vcols *
"* ~/.etc/vim/vcols *
