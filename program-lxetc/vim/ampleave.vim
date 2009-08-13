
au  BufLeave Playlist. call JumpListLeave()
au  BufLeave Browser.* call BrowserLeave()
au  BufLeave *.veq call EqualizerGraphLeave()
au  BufLeave lyric.txt	call LyricsLeave()

function! LyricsLeave()
	call system("rm ~/.etc/.tmp/lyric.*")
endfunction

function! EqualizerGraphLeave()
	set hlsearch
	syn clear Freq
	unmap	<Left>
	unmap	<Right>
	unmap	<Up>
	unmap	<Down>
	set wrapscan
	let &ls=g:ls
	unlet g:ls
	let &stl=g:stl
	unlet g:stl
endfunction

function! Unwnated()
	if(!filereadable(expand("~/.etc/VimAmp/VimAmp.m3u")))
		!echo -n > ~/.etc/VimAmp/VimAmp.m3u
		call AmpBrowserList()
		call BrowserEnter()
		echo "PlayList Do not exist. You will be dropped in a browser"
		return
	endif

endfunction


function! MenuLeave()
	let &isk=g:isk
	unlet g:isk
	let &ts=g:ts
	unlet g:ts
	let &ls=g:ls
	unlet g:ls
	unmap <Left>
	unmap <Right>
	unmap <C-m>
	syn clear MenuAll
	syn clear CurrentMenu
endfunction


function! VimAmpLeave()
	syntax off
	unmap gg
	unmap q
	unmap a
	unmap x
	unmap c
	unmap m
	unmap v
	unmap b
	unmap h
	unmap r
	unmap l
	call ReMaps()
	unmap <Up>
	unmap <Down>
	let &ls=g:ls
	unlet g:ls
	let &stl=g:stl
	unlet g:stl
	let &vi=g:vi
	unlet g:vi
endfunction


function! JumpListLeave()
	syn clear pSong
	unmap	
	set shm-=s
	set nonu
	call PlayListLeave()
	call ColorMoveUnMap()
	let &stl=g:stl
	unlet g:stl
endfunction

function! PlayListLeave()
	set noic
	unmap <Del>
	unmap D
endfunction

