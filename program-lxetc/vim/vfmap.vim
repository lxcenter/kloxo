" *************************** Vfmap *******************

"Needed to make it work in windows.. dunno why though.
map  [21~ <F10>
map	<F10>1	:call CompileMap(1)<CR>
map	<F10>2	:call CompileMap(2)<CR>
map	<F10>3	:call CompileMap(3)<CR>
map	<F10>4	:call CompileMap(4)<CR>
map	<F10>5	:call CompileMap(5)<CR>
map	<F10>6	:call CompileMap(6)<CR>
map	<F10>7	:call CompileMap(7)<CR>
map	<F10>8	:call CompileMap(8)<CR>
map	<F10>a	:call ExecAMap()<CR>
map	<F10>W	:call WindowMapToggle()<CR>
map	<F10>T	:unlet g:win_old_height<CR>
map	<F10>w	:call Toggle("&wrap")<CR>
map	<F10>f	:call Toggle("&foldenable")<CR>
map	<F10>e	:call ScrollMapToggle()<CR>
map	<F10>h	:call HexvalMapToggle()<CR>
map	<F10>s	:if exists("syntax_on") \| syntax off \| else \| syntax enable \| endif<CR>
map	<F10>g	:call Toggle("grep")<CR>
map	<F10>y	:call Toggle("sync")<CR>
map	<F10>P	:call Toggle("&paste")<CR>
"map	<F10>P	:call Print()<CR>
map	<F10>d	:call Delete()<CR>
map	<F10>p	yy<C-o>:!efindsng -whole "0" ~/prn/lirics.txt<CR><CR>
map	<F10>r	:call Restore("r")<CR>
map	<F10>k	:call Restore("k")<CR>
map	<F10>n	:!amn<CR>
map	<F10>z	:call system("amz")<CR>
map	<F10>x	:call system("amx")<CR>
map	<F10>c	:call system("amc")<CR>
map	<F10>v	:call system("amv")<CR>
map	<F10>b	:call system("amb")<CR>
map	<F10>=	:call system("auv +5")<CR>
map	<F10>-	:call system("auv -5")<CR>
map	<F10>0	:!vimamp<CR><CR>
map	<F10><space>	:call SpaceMap()<CR>

" ***************************  End Vfmap *******************
