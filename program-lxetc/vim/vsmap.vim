" ************************* Vsmap  ******************


map	s	<F50>
"map	<F50>d	:23sp ~/.etc/.tmp/Browser.<CR><Space>
map	<F50>D	:set nosb<CR>:23sp ~/.etc/.tmp/Browser.<CR><Space>
map	<F50>p	<C-W>p
map	<F50>f	:call ToggleWindow()<CR>
map	<F50>x	<C-w>_<C-w>\|
map	<F50>q	:q<CR>
map	<F50>o	:cn<CR>
map	<F50>i	:cc<CR>
map	<F50>u	:cN<CR>
nor	<F50>k	:call ScrollMapToggle()<CR>
nor	<F50>l	$
nor	<F50>j	:Ide 
"nor	<F50>l  :exe "Ide p/x *" . expand("<cword>")<CR>
"nor	<F50>l  :exe ":Ide p " . expand("<cword>")<CR>
"vnor	<F50>l  y:exe 'Ide p '. @0<CR>
map	<F50>2	:call Toggle("&is")<CR>
map	<F50>3	:call Toggle("&scs")<CR>
map	<F50>;	:
map	<F50>w	:call search("http://")<CR>
map	<F50>W	:call search("http://", 'b')<CR>
map	<F50>m	@r
map	<F50>t	:update<CR>:!<Up><CR><CR>
map	<F50>r	:e<CR>
map	<F50>e	:!
map	<F50>n	<C-^>
map	<F50>g  G
map	<F50>h	0
nor	<F50>[	[[z.
map	<F50>.	:e ~/.etc/vim/vreg<CR><CR>
"vnor	<F50>.	y:call system("echo -n ". @0. "> ~/.etc/.tmp/screen_buffer")<CR>
"vnor	<F50>.	y:silent exe "!echo -n \'" . @0 . "\' > ~/.etc/.tmp/screen_buffer"<CR>
map	<F50>,p :echo "hello"<CR>
map 	<F50>,b ^y$:r!echo 'scale=6; <c-r>"'\|bc<cr>
vnor	<F50>.	y:exe "!echo -n \'" . @0. "\'"
map	<F50>/	:call Savereg()<CR><CR>
map	<F50>vp	:vi .plot<CR><CR>
"map	<F50>vv :n 
map	<F50>vj :%s/
map	<F50>vu :1,.d<CR>
map	<F50>vd :.,$d<CR>
map	<F50>vt :!ctags *<CR><CR>
map	<F50>vn :call ViSameDir()<CR>
map	<F50>vi :exe 'e '. @i<CR>
map	<F50>vo :exe 'e '. @o<CR>
map	<F50>ve :exe 'e '. @e<CR>
map	<F50>vl :let @
map	<F50>vy :!cmak %<CR>
map	<F50>vf	0Y@0
map	<F50>vh	:update<CR>@h
map	<F50>vq	0"qY
map <F50>vq 0y$:call ExportQueryString(@0)<CR>
vmap <F50>vq y:call ExportQueryString(@0)<CR>
map	<F50>vg	:grep 
map	<F50>vv	:tag 
map <F50>vr	f"lvf"jc
map	<F50>vk	:call DialNumber()<CR>
"map	<F50>vc	:!lcd<CR><CR>:14sp ~/.etc/.tmp/dirstack<CR><CR>
map	<F50>vx	:10sp ~/.etc/.tmp/sdirstack<CR>
map	<F50>vx	:tjump 
map	<F50>va	:!vi s.out<CR><CR>
map	<F50>va	:call ExecShellWithDir()<CR><CR>
map	<F50>a	:shell<CR>
map	<F50>vb	:call Toggle("&scb")<CR>
map	<F50>vs	:update <Bar>!/bin/cp % ~/.etc/.tmp/screen_buffer ; screen -X readbuf<CR><CR>
map	<F50>vH	:source ~/.etc/vim/vhtmlab.vim<CR>
map	<F50>vw	/word: 
map	<F50>dd	:diffup <bar> set foldmethod=diff<CR>
map	<F50>dp	:call Toggle('&paste')<CR>
map	<F50>ds	:exe '!cvs status ' .BufNameNoCwd(). ' \| grep -iv sticky'<CR>
map	<F50>dc	:call CvsLastDiff()<CR>
map	<F50>ds :source source-auto.vim<CR>
map	<F50>dh :call PutInLynx()<CR>
map <F50>du :!cv up<CR>
map <F50>dy :!cv ci<CR>


map <F50>df :call SwitchToGdbWindow()<CR>
map	<F50>cr	:call IdeRun()<CR>
" Masking the start of debug stuff. Our aim is to avodi the unnecessary files that r loaded in the beginning,a nd reach the exact location we need to.
map	<F50>cr	:silent call GdbPhpReachHere()<CR>
map	<F50>cF :call Toggle("g:gdb_do_tfs")<CR>
map	<F50>cI :exe "Ide info line " .expand("%:p").":".line('.')<CR>
map	<F50>cM :Ide b main<CR>
map	<F50>cP :exe "Ide print/x *" .expand("<cword>")<CR>
map	<F50>ca :call GdbMyAttach()<CR>
map	<F50>cb	:exe "Ide break " .expand("%:p").":".line('.')<CR>
map	<F50>cc	:Ide cont<CR>
map	<F50>cf :Ide fs<CR>
map	<F50>ci	:Ide up<CR>
map	<F50>ck :Ide down<CR>
map	<F50>cn :Ide next<CR>
map <F50>co :Asmopen<CR>
map	<F50>cp :exe "Ide print/x " .expand("<cword>")<CR>
map	<F50>cs :Ide step<CR>
map	<F50>ct	:Ide bt<CR>
map	<F50>cu	:exe "Ide until " .expand("%:p").":".line('.')<CR>
map	<F50>cw :botright cwindow<CR>
map	<F50>cx :call MyIdestart()<CR>

map <F50>vm :!vi Makefile<CR>
map <F50>vC :r !pwd<CR>
map <F50>vM :call ShellFixVimRun()<CR><CR>

function! IdeRun()
	if (&ft == 'php')
		Ide bstart
		return
	endif

	let arg = GetExe("args")

	:exe 'Ide run '. arg
endfunction

function! MyIdestart()
	if (&ft == 'php')
		let $VIM_GDBCMD = 'dbgclient'
		Idestart gdbtest.lxlabs.com/sample
	else
		Idestart
	endif
endfunction

function! PidOff(name)
	return system('pidof ' .a:name)
endfunction


" ****************  End Vsmap
