" ************************ Vzmap *******************
"map	ZZ	<nop>
"map	zz	<nop>
map	zq	:q<CR>
map	zm	:Mancmd cc <cword><CR>
map	z`	:call Compile("make")<CR><CR>
map	z1 	:cn<CR>
map	z2	:cc<CR>
map	z3	:cN<CR>
map	zx 	:x<CR>
map	zw 	:update<CR>
nor	ze 	zc
map	zs 	:!
map	za	:!.ea<CR>
"map	zd	:bd<CR>
map	zr	:e!<CR>
map	zv	:update<CR>:so ~/.etc/vim/vimrc.vim<CR>
map	ZA	:!e%:r<CR>
map	ZM	:Mancmd sys 
map	ZQ	:q!<CR>
map	z<Tab>	:ls<CR>:let nr=input("Buffer: ")<Bar>exe "b " . nr<CR>
" ************************ End Vzmap *******************
