"************************* Vafnc ***************

set nocp
set splitright
set splitbelow
set hlsearch
set incsearch
set nosol
set noequalalways
set hidden
set notitle


let g:sourcedir =  expand('<sfile>:p:h:h:h')
exe 'source ' .g:sourcedir. '/vimamp/vim/vbfnc.vim'

set viminfo='20,h,rgdb,n~/.etc/vim/.tmp/.vimamp
hi IncSearch	term=reverse cterm=bold ctermbg=black ctermfg=Red
hi StatusLine	term=reverse cterm=bold ctermbg=black ctermfg=Green
hi StatusLineNc	term=reverse cterm=bold ctermbg=black ctermfg=Blue
hi SpecialKey	term=none cterm=none ctermfg=darkblue guifg=darkblue
hi Directory	term=none cterm=none ctermfg=darkcyan guifg=darkcyan

let g:prev_song = 63999
let g:same_song = 64000
let g:paus_song = 64001
let g:stop_song = 64002
let g:next_song = 64003
let g:act_play  = 1
let g:act_cut	= 2
let g:act_copy  = 3
let g:act_paste = 4
let g:act_del   = 5
let g:act_clr   = 6

let g:song_vol = 0
let g:equal_status = 0
let g:current_song = ''
let g:update_prog = 0
let g:lost_update = 0
let g:song_changed = 0
let g:left_window_title = 'Lyrics'
let g:edit_browse_dir = '.'
let g:add_browse_dir = ''
let g:edit_file = ''
let g:in_edit_window = 0
let g:third_window_flag = 0
let g:plist_position = 0
let g:plist_total = 0
let g:error_song_changed = 0
let g:prev_song_changed_time = 0
let g:VimAmp = 0

let g:win_main = 2
let g:win_misc = 3
let g:win_plist = 5
let g:win_equal = 6


let g:file_lyrics = '~/.etc/.tmp/lyric.txt'
let g:file_main = g:sourcedir. '/vimamp/VimAmp'
let g:file_plist = '~/.etc/VimAmp/Playlist.'
let g:file_equal = g:sourcedir. '/vimamp/Equalizer.'
let g:file_123equ = '~/.etc/VimAmp/mpg.equ'
let g:file_out = '~/.etc/VimAmp/.mpgout'

au  BufEnter Equalizer. call EqualizerGraphEnter()
au  BufRead Equalizer. call EqualizerRead()

au  BufEnter VimAmp call VimAmp()
au  VimEnter * call VimAmpStart()
au  BufEnter VimHelp call HelpEnter()
au  BufENter VimAmp.m3u call M3uEnter()
au  BufWritePost VimAmp.m3u call M3uWritePost()
au  BufEnter Playlist. call JumpListEnter()
au  BufEnter Browser.* call BrowserEnter()
au  BufLeave Browser.* call BrowserLeave()
au  BufDelete Browser.* call BrowserDelete()
au  BufEnter lyric.txt 	call LyricsEnter()
au  BufRead lyric.txt 	call LyricsRead()


au WinEnter * call ThirdWindowEnter()


let g:dull = 'Dark'

function! RestoreEdit()
	mapclear
	call MapExternKey()
	if filereadable(expand('~/.vimamp_vimrc'))
		source ~/.vimamp_vimrc
	endif
	map <Tab>	:call AmpPrgExit()<CR>
endfunction

function! MakeFullScreen()
	res 0
	vert res 0
	hi vertsplit ctermfg=black guifg=black
	let g:in_edit_window = 1
endfunction

function! ThirdWindowEnter()
	if g:update_prog
		return
	endif

	if winnr() == 1
		2 wincmd w
		return
	endif

	if winnr() == 4
		5 wincmd w
		return
	endif


	if winnr() == 2 && bufwinnr('nlist') != 4
		call AmpExit()
	endif

	if winnr() != 3
		return
	endif


	let a = bufname('%') == bufname(g:file_lyrics) || bufname('%') == bufname('VimHelp')
	let a = a || bufname('%') == bufname(g:browser_file)
	let xor = a && !g:third_window_flag || !a && g:third_window_flag

	let g:third_window_flag = 0
	if (xor)
		return
	else
		let g:left_window_title = ''
		call RestoreEdit()
	endif
endfunction


function! SetGDull(arg)
	if a:arg
		let g:bg_color = 'black'
	else
		let g:bg_color = 'white'
	endif
endfunction


call SetGDull(1)


function! LibCallAmp(function, var)
	return libcall('/usr/lib/libvimamp.so', a:function, a:var);
endfunction

function! LibCallNrAmp(function, var)
	return libcallnr('/usr/lib/libvimamp.so', a:function, a:var);
endfunction

function! EqualizerStatus(flag)
	let $eql = match(getline('.'), '\*') - match(getline('.'), '|')

	if ($eql >= 10)
		let tmp = $eql - 10
		let g:equal_status = '+' .tmp
	else 
		let tmp = 10 - $eql
		let g:equal_status = '-' .tmp
	endif
	
	if(a:flag == 'wrt')
		let n = 2
		let a = '1 '

		while n < 13 
			let eql = match(getline(n), '\*') - match(getline(n), '|') - 10
			if(n == 2)
				let a = a . " " . eql*2
			else
				let a = a . "\n" . eql*2
			endif
			let n = n + 1
		endwhile

		call LibCallAmp('writeFile', expand(g:file_123equ). ' ' .a)
	endif

	call ColorCurrentList()
endfunction

function! EqualizerHiSyntax()
	if(g:dull == 'Dark')
		hi Hash	ctermfg=DarkGreen guifg=DarkGreen

		if g:bg_color == 'black'
			hi Freq ctermfg=DarkRed guifg=DarkRed
			hi Pre ctermfg=DarkCyan guifg=DarkCyan
			hi Star ctermfg=DarkGreen guifg=DarkGreen
		else
			hi Freq ctermfg=DarkGreen guifg=DarkGreen
			hi Pre ctermfg=DarkBlue guifg=DarkBlue
			hi Star ctermfg=DarkRed guifg=DarkRed
		endif

		hi Mark ctermfg=None  

	else
		hi Freq ctermfg=Red guifg=Red
		hi Hash	ctermfg=none  
		hi Pre ctermfg=Cyan guifg=Cyan
		hi Mark ctermfg=Gray guifg=Gray
		hi Dot ctermfg=None  
	endif
endfunction

function! EqualizerEnterHi()
	if g:bg_color == 'black'
		hi Pre ctermfg=DarkCyan guifg=DarkCyan
	else
		hi Pre ctermfg=DarkBlue guifg=DarkBlue
	endif
	syn clear
	syn match Freq '^\s\d.* '
	syn match Mark "-10.*"
	syn match Hash "#"
	syn match Dot "\."
	syn match Star "Õ"
	syn match Star "Ô"
	syn match Star "*"
	syn match Pre  "Pre.*"
endfunction

function! EqualizerGraphEnter()

	if g:update_prog
		return
	endif

	set ic noswf nobackup nowritebackup
	call EqualizerEnterHi()
	call VimAmpMap()
	map g	:q<CR>
	call AmpSetStl()
	vert res 27
	res 12

	set nohlsearch
	"call ColorCurrentList()
	set nowrapscan
	nor <silent> <Left>	:silent! s/-\*/*-<CR>0/\|<CR>:call EqualizerStatus("wrt")<CR>
	nor <silent> <Right>	:silent! s/\*-/-*<CR>0/\|<CR>:call EqualizerStatus("wrt")<CR>
	nor	<silent> <Up>	k0/\|<CR>:call EqualizerStatus("now")<CR>
	nor	<silent> <Down>	j$?\|<CR>:call EqualizerStatus("now")<CR>
	map	i	<Up>
	map	k	<Down>
	if g:dull == 'Dark'
		map	j	<Left>
		map	l	<Right>
	endif
	0
	/|
	nor T l
	exe 'normal 0' .match(getline('.'), '|'). 'T'
	call EqualizerStatus('nowrt')
endfunction


function! GetVolume()
	let vol = libcall('/usr/lib/snd_ctl.so', 'get_volume', '')
	return vol
endfunction

function! GetCurrentSong()
	let g:current_song = LibCallAmp('readFileSmall', expand(g:file_out))
	return g:current_song
endfunction

com! -range -nargs=1 EditPlayListCmd call EditPlaylist(<f-args>, <line1>, <line2>)

function! EditPlaylist(action, from, ton)
	let act = 0

	if g:plist_position >= a:from && g:plist_position <= a:ton
		let g:plist_position = 0
	else
		if g:plist_position > a:from
			let g:plist_position = (g:plist_position - a:ton + a:from - 1)
		endif
	endif

	if a:action == 'clr'
		let act = g:act_clr 
		%d
		let g:plist_position = 0

	elseif a:action == 'del'
		let act = g:act_del 
		exe a:from. ',' .a:ton. 'd '
		
	elseif a:action ==  'cut'
		let act = g:act_cut 
		exe a:from. ',' .a:ton. ' d m'

	elseif a:action == 'paste'
		let act = g:act_paste 
		nor T p
		normal  "mT

	elseif a:action == 'copy'
		let act = g:act_copy
		nor T y
		exe a:from. ',' .a:ton. ' T m'
	endif

	let g:plist_total = line('$')
	call SendMpg(act, a:from, a:ton)
	call ColorCurrentList()
endfunction
	
function! CSong(flag)
	if a:flag == 'fresh'
		let sval = getline(GetCSongPos())
		let sval = escape(sval, "$'~")
	else
		let sval = a:flag
	endif

	syn clear pSong
	exe "syn match pSong \"^" .sval. "$\""
endfunction

function! GetCSongPos()
	let sval = substitute(g:current_song, '^\(\d\+\):.*', '\1', '')
	if sval
		return sval
	endif
	return 0
endfunction

function! GetCSong()
	let sval = substitute(g:current_song, ' (\d\+:\d\+)$', '', 'g')
	return sval
endfunction

function! JumplistHi()
	if (g:dull == 'Dark')
		if g:bg_color == 'black'
			hi pSong term=none ctermfg=DarkCyan guifg=DarkCyan
		else
			hi pSong term=none ctermfg=DarkBlue guifg=DarkBlue
		endif
			
		hi Current cterm=none ctermfg=DarkGreen guifg=DarkGreen
	else
		hi pSong term=none ctermfg=Cyan guifg=Cyan
		hi Current cterm=none ctermfg=Green guifg=Green
	endif
endfunction

function! JumpListEnter()
	if g:update_prog
		return
	endif
	set nowrap
	call VimAmpMap()
	call AmpSetStl()
	set ic
	set ic noswf nobackup nowritebackup
	set modifiable
	vert res 27
	let g:list = "/bin/ls -AFd *"
	set shm+=s
	map	<C-m>	:call SendActionPlay(line('.'), 1)<CR>
	nor 	<Left>	<Left>
	nor	j	h
	call JumplistHi()
	0
	exe GetCSongPos()
	call CSong('fresh')
	

	call PlayListEnter()
	call ColorMoveMap("Brows")
	call ColorCurrentList()
endfunction

function! M3uEnter()
	mapclear
	set nowrap
	set modifiable
	map	<silent> m	:w!<CR>:call VimAmpView()<CR>
	map	<silent> `	m
	call ReMaps()
endfunction

function! PlayListEnter()
	set autoread
	set ic
	set modifiable
	set wrapscan
	map	l	:call OpenAmpBrowser()<CR>
	nor	<Del>	:EditPlayListCmd del<CR>
	nor	D	:EditPlayListCmd clr<CR>
	nor	<C-x>	:EditPlayListCmd cut<CR>
	nor	<C-p>	:EditPlayListCmd paste<CR>
	nor	<C-v>	:EditPlayListCmd paste<CR>
	nor	<C-c>	:EditPlayListCmd copy<CR>
endfunction

function! LyricsRead()
	call LyricsHiSyntax()
endfunction

function! EqualizerRead()
	call EqualizerHiSyntax()
	call EqualizerLoad()
endfunction

function! EqualizerLoad()
	let string = LibCallAmp('readFileSmall', expand(g:file_123equ))
	if string == ''
		let string = '1 0 0 0 0 0 0 0 0 0 0 0' 
	endif
	let string = substitute(string, '\s*\w\s*\(.*\)', '\1', '')
	let string = LibCallAmp('subsNewline', string)
	let idx = 2
	while idx <= 12
		let val = substitute(string, '\s*\([0-9\-]*\).*', '\1', '')
		let string = substitute(string, '\s*[0-9\-]*\s*\(.*\)', '\1', '')
		let val = val/2 + 10
		if val < 1
			let val = 1
		endif
		exe idx
		let base = match(getline('.'), '|')
		let val = val + base
		nor T l
		nor R r
		exe 'normal 0' .val. 'TR*'
		let idx = idx + 1
	endwhile
	call EqualizerStatus('wrt')
endfunction

function! UpdateProgBar()

	let n = winnr()

	if (bufname('%') == bufname(g:file_main))
		let l = line('.')
		let same_file = 1
	else
		let same_file = 0
		let g:update_prog = 1
		call JumpToWin(g:win_main)
	endif
	5
	silent! s/>>=/=>>/


	while (g:lost_update)
		silent! s/>>=/=>>/
		let g:lost_update = g:lost_update - 1
	endwhile


	if same_file
		exe l
	else
		exe n. 'wincmd w'
	endif
	let g:update_prog = 0
endfunction


function! VimAmpHiSyntax()
	if(g:dull == 'Dark')
		hi Amptitle term=none ctermfg=DarkMagenta guifg=DarkCyan
		hi Name cterm=none ctermfg=DarkCyan guifg=DarkCyan
		hi Namea cterm=none ctermfg=None  
		hi Namel cterm=none ctermfg=None 
		hi Keys ctermfg=None  
		hi user2 ctermfg=DarkCyan guifg=DarkCyan

		if g:bg_color == 'black'
			hi user3 ctermfg=DarkYellow guifg=DarkYellow
		else
			hi user3 ctermfg=DarkMagenta guifg=DarkMagenta
		endif

		hi ProgBar ctermfg=none  
		hi ProgWid ctermfg=DarkYellow guifg=Yellow
	else
		hi user3	term=none ctermfg=Cyan guifg=Cyan guifg=Cyan
		hi Name cterm=none ctermfg=Cyan guifg=Cyan
		hi Namea cterm=none ctermfg=DarkCyan guifg=Cyan
		hi Namel cterm=none ctermfg=Green guifg=Green
		hi StatusLine ctermfg=Red guifg=Red
		hi Keys ctermfg=DarkGreen guifg=DarkGreen
		hi user2 ctermfg=DarkCyan guifg=Cyan
		hi user3 ctermfg=Yellow guifg=Yellow
		hi StatusLineNc ctermfg=black guifg=black
		hi ProgBar ctermfg=none  
		hi ProgWid ctermfg=Yellow guifg=Yellow
		hi Amptitle term=none ctermfg=Magenta guifg=Magenta
		hi Url term=none ctermfg=White guifg=White

	endif
	if g:bg_color == 'black'
		hi VertSplit cterm=none ctermbg=none ctermfg=DarkCyan guibg=black guifg=DarkCyan
		hi user2 ctermfg=DarkCyan guifg=DarkCyan
	else
		hi VertSplit cterm=none ctermbg=none ctermfg=DarkBlue guibg=white guifg=DarkBlue
		hi user2 ctermfg=DarkBlue guifg=DarkBlue
	endif
	call MenuHiSyntax()

endfunction

	
function! LiricsAgain()
	if strlen(g:m_song) == 0 
		return
	endif
	call system("echo \"" .g:m_band. " - " .g:m_song. "\" \|" . "eplist")
	if(filereadable(expand("~/.etc/.tmp/lyric.txt")))
		vi ~/.etc/.tmp/lyric.txt
		let &stl = "Lyrics%=%2*" .g:m_band. " - " .g:m_song
	else
		echo "Sorry This is also not Available"
		normal m
	endif
endfunction

function! ManualLirics()
	let g:m_band = input("Band Name:(Optional) ")
	let g:m_song = input("Song Name: ")
	if (strlen(g:m_song) == 0)
		return
	endif
	call system("echo \"" . g:m_band . " - " .g:m_song. "\" \|" . "eplist")
endfunction

function! SweepSearch()
	call system("cd /usr/lirics_dbase ; echo \"" .GetCSong(). "\" | eplist -b ")

	if(filereadable(expand("~/.etc/.tmp/lyric.txt")))
		return 1
	else 
		return 0
	endif
endfunction

function! NormalLyrics()
	call system("cd /usr/lirics_dbase ; echo \"" .GetCSong(). "\" | eplist")

	if(filereadable(expand("~/.etc/.tmp/lyric.txt")))
		return 1
	else
		return 0
	endif
endfunction

function! GetLyrics()
	"if(bufname("%") == bufname("lyric.txt")) |  bd | endif
	echohl directory

	if g:current_song == ''
		call system('/bin/cp /usr/share/vimamp/doc/AddMesg' .g:file_lyrics)
		return
	endif
		

	if (!isdirectory("/usr/lirics_dbase"))
		echo "Sorry. Lyrics Database Not Available"
		return
	endif

	call system("rm ~/.etc/.tmp/lyric.*")

	if NormalLyrics() == 0 
		if SweepSearch() == 0
		call system('echo " "  > ~/.etc/.tmp/lyric.txt')
		call system('echo "Lyrics Not Available" >> ~/.etc/.tmp/lyric.txt')
		endif
	endif

	echohl None
endfunction

function! LyricsHiSyntax()
	if g:bg_color == 'black'
		hi LyricsSong cterm=none ctermfg=DarkCyan guifg=DarkCyan
	else
		hi LyricsSong cterm=none ctermfg=DarkBlue guifg=DarkBlue
	endif
	syn match LyricsSong '@SONG:.*'
	syn match LyricsSong 'Lyrics Not.*'
endfunction

function! LyricsEnterHi()
	if g:bg_color == 'black'
		hi LyricsSong cterm=none ctermfg=DarkCyan guifg=DarkCyan
	else
		hi LyricsSong cterm=none ctermfg=DarkBlue guifg=DarkBlue
	endif
endfunction

function! LyricsEnter()
	if g:update_prog
		return
	endif

	set autoread
	let g:left_window_title = 'Lyrics'
	call LyricsEnterHi()

	set nonu
	call VimAmpMap()
	nor	<Left>	<Left>
	nor	<Right>	<Right>
	nor	<Up>	<Up>
	nor	<Down>	<Down>
		
endfunction


function! Hide()
	if g:update_prog
		return
	endif
	if g:bg_color == 'black'
		hi Pre ctermfg=Darkblue guifg=Darkblue
		hi pSong ctermfg=darkblue guifg=darkblue
		hi LyricsSong cterm=none ctermfg=Darkblue guifg=Darkblue
		hi MenuAll	cterm=none ctermfg=Darkblue guifg=Darkblue
		hi Menu 	term=none ctermfg=Darkblue guifg=Darkblue
		hi CurrentMenu   term=none ctermfg=Darkblue guifg=Darkblue
	else
		hi Pre ctermfg=darkcyan guifg=darkcyan
		hi pSong ctermfg=darkcyan guifg=darkcyan
		hi LyricsSong cterm=none ctermfg=darkcyan guifg=darkcyan
		hi MenuAll	cterm=none ctermfg=darkcyan guifg=darkcyan
		hi Menu 	term=none ctermfg=darkcyan guifg=darkcyan
		hi CurrentMenu   term=none ctermfg=darkcyan guifg=darkcyan
		
	endif
endfunction

au BufLeave * call Hide()

function! SendMpg(act, from, to)
	if ! LibCallNrAmp('checkIfRunning', 'vimamp')
		call LibCallNrAmp('execFile', 'lxmpg ' .$VIM_TTY)
		echo "Player Crashed"
		echo "Player Crashed"
		return 
	endif

	call LibCallNrAmp('writeFile', expand('~/.etc/VimAmp/.mpg_fifo'). ' ' .a:act. ' ' .a:from. ' ' .a:to)
endfunction


function! OpenHelp()
	let g:third_window_flag = ThirdWindowIfExtern()
	call JumpToWin(g:win_misc)
	vi ~/.etc/VimAmp/VimHelp
endfunction

function! HelpEnter()
	if g:update_prog
		return
	endif
	let g:left_window_title = 'Help'
	map	q	:call AmpHelpExit()<CR>
	call HelpSyntax()
endfunction

function! AmpHelpExit()
	if g:edit_file == ''
		let n = bufnr(g:file_lyrics)
	else
		let g:left_window_title = 'Edit Window'
		let n = bufnr(g:edit_file)
	endif

	exe 'b' .n
	bd ~/.etc/VimAmp/VimHelp
	call JumpToWin(g:win_main)
endfunction 

function! HelpSyntax()
	nor	<Up>   <Up>
	nor	<Down> <Down>
	nor	<Left> <Left>
	nor	<Right> <Right>
	syntax match Heading ".*Commands:" 
	hi Heading ctermfg=DarkCyan guifg=DarkCyan
	syntax keyword Namea Amplifier
	syntax match Namel "lxlabs"
	hi Name cterm=none ctermfg=Cyan guifg=Cyan
	hi Namea cterm=none ctermfg=DarkCyan guifg=DarkCyan
	hi Namel cterm=none ctermfg=Green guifg=Green
endfunction
	
function! CLF()
	exe "1," . bufnr("$") . "bd!"
endfunction
	
function! VimAmpView()
	call JumpToWin(g:win_main)
endfunction

function! OpenEqualizer(arg)
	if bufwinnr(g:file_equal) != -1
		call JumpToWin(g:win_equal)
		return
	endif

	call JumpToWin(g:win_plist)

	exe a:arg. g:file_equal
	call EqualizerHiSyntax()
	call EqualizerGraphEnter()
	call EqualizerStatus('now')
endfunction

function! FixWindowSize()
	call JumpToWin(g:win_plist)
	res 0
	call JumpToWin(g:win_equal)
	res 12
	2
	nor T k
	normal T
	call JumpToWin(g:win_main)
	res 0
	res 7
	if g:edit_file != ''
		call JumpToWin(g:win_misc)
		vi
	endif
endfunction

function! OpenAllWin()
	exe 'vi ' .g:file_main
	set modifiable
	set noreadonly
	call VimAmpHiSyntax()
	vsplit ~/.etc/VimAmp/nlist
	set ic noswf nobackup nowritebackup
	exe 'split ' .g:file_plist
	call JumplistHi()
	set ic noswf nobackup nowritebackup
	res 0
	res +1
	call OpenEqualizer("12sp")
	set noreadonly
	6 wincmd w
	call EqualizerLoad()
	set ic noswf nobackup nowritebackup
	call GetLyrics()
	1 wincmd w

	set nosplitbelow
	sp ~/.etc/VimAmp/nothing
	set splitbelow
	set ic noswf nobackup nowritebackup
	2 wincmd w
	res 0
	set splitbelow

	if g:edit_file == ''
		23sp ~/.etc/.tmp/lyric.txt
	else
		let g:left_window_title = 'Edit Window'
		exe '23sp ' .g:edit_file
		call RestoreEdit()
	endif

	2 wincmd w
	call VimAmp()
endfunction


function! JumpToWin(number)
	exe a:number. 'wincmd w'
endfunction


function! JumpTo(name)
	let n = bufwinnr(a:name)
	exe n. 'wincmd w'
endfunction

function! SwitchWindows()
	if winnr() == 3
		5 wincmd w
	elseif winnr() == 6
		2 wincmd w
	elseif winnr() == 5
		if bufwinnr(g:file_equal) == -1
			1 wincmd w
		else
			wincmd w
		endif
	else
		wincmd w
	endif
endfunction


function! LoadPLaylist()
	let n = winnr()
	call JumpToWin(g:win_plist)
	e!
	call CSong(getline(g:plist_position))
	let g:plist_total = line('$') * !!getfsize(expand('~/.etc/VimAmp/Playlist.'))
	call AmpSetStl()
	$
	call ColorCurrentBrows()
	exe n. 'wincmd w'
endfunction


function! AmpExit()
	call LibCallNrAmp('killByName', 'T vimamp')
	sleep 10m
	call LibCallNrAmp('killByName', 'K vimamp')
	call delete(expand('~/.etc/VimAmp/.mpgout'))
	qa!
endfunction

function! UpdateCurrentSong(arg)

	if a:arg == 'map'
		if CheckFastChange()
			return
		endif
	endif

	sleep 100m

	if !g:in_edit_window
		call SongChanged(a:arg)
		return
	endif

	let g:song_changed = 1
	call GetCurrentSong()
	call AmpSetStl()
endfunction

function! MapExternKey()

	map	<F10>z	:call SendMpg(g:act_play, g:prev_song, g:prev_song)<CR>:call UpdateCurrentSong('nm')<CR>
	map	<F10>x	:call SendMpg(g:act_play, g:same_song, g:same_song)<CR>:call UpdateCurrentSong('nm')<CR>
	map	<F10>c	:call SendMpg(g:act_play, g:paus_song, g:paus_song)<CR>
	map	<F10>v	:call SendMpg(g:act_play, g:stop_song, g:stop_song)<CR>
	map	<F10>b	:call SendMpg(g:act_play, g:next_song, g:next_song)<CR>:call UpdateCurrentSong('nm')<CR>
	map	<F10>=	:call ChangeVolume('+')<CR>
	map	<F10>-	:call ChangeVolume('-')<CR>
	map <F3>  :call MakeFullScreen()<CR>
	map <F2>  :call MakeFullScreen()<CR>


	map <silent> <M-Y> :call UpdateCurrentSong('map')<CR>
	map! <M-Y> <nop>
	map!  <M-L> <nop>
	map  <M-L> <nop>
	map  <M-U> <nop>
	map!  <M-U> <nop>

endfunction

function! MapTriggerKeys()
	map <M-Y> :call SongChanged('map')<CR>
	imap <M-Y> <C-\><C-n>:call SongChanged('map')<CR>
	cmap <M-Y> <nop>

	map <silent> <M-L> :call LoadPLaylist()<CR>
	map!  <M-L> <nop>

	map <silent>	<M-U> :call UpdateProgBar()<CR>
	map!  <M-U> <nop>
endfunction

function! OpenLyrics()

	let g:third_window_flag = ThirdWindowIfExtern()
	call JumpToWin(g:win_misc)

	if bufwinnr(g:file_lyrics) != -1
		return
	endif
	call GetLyrics()

	vi ~/.etc/.tmp/lyric.txt
endfunction
		
function! SendActionPlay(play_a, flag)
	call SendMpg(g:act_play, a:play_a, a:play_a)
	if a:flag
		sleep 100m
		call SongChanged('nm')
	endif
endfunction

function! VimAmpMap()
	mapclear
	"call MapNope()
	map	;	:
	call MapTriggerKeys()
	map <silent> <Tab> :call SwitchWindows()<CR>
	map	l	:call OpenAmpBrowser()<CR>
	map	<silent> m	:call VimAmpView()<CR>
	map	`	m
	map 	h	:call OpenHelp()<CR>
	map	<F1>	h
	map	e	:call JumpToWin(g:win_plist)<CR>
	map	g	:call OpenEqualizer('12sp')<CR>
	map 	j 	e
	map	r	:call OpenLyrics()<CR>
	map	R	:call ManualLirics()<CR>
	map	s	:call LiricsAgain()<CR>
	nor	Q	:call AmpExit()<CR>
	map	q	Q
	map	z	:call SendActionPlay(g:prev_song, 1)<CR>
	map	x	:call SendActionPlay(g:same_song, 1)<CR>
	map	c	:call SendActionPlay(g:paus_song, 0)<CR>
	map	v	:call SendActionPlay(g:stop_song, 0)<CR>
	map	b	:call SendActionPlay(g:next_song, 1)<CR>
	map	p	:call OpenPrg()<CR>
	"nor <Space> V
	vmap z  <Nop>
	vmap x  <Nop>
	vmap c  <Nop>
	vnor v  V
	vmap b  <Nop>
	vmap g  <Nop>
	vmap r  <Nop>
	vmap s  <Nop>
	map K	<Nop>
endfunction

function! ChangeVolume(flag)
	let g:song_vol = libcall('/usr/lib/snd_ctl.so', 'change_volume', a:flag. '2')
	call AmpSetStl()
endfunction

function! CheckAndStartPlayer()
	if LibCallNrAmp('checkIfRunning', 'vimamp')
		echo 'Player already running'
		return 0
	endif

	call LibCallNrAmp('execFile', 'lxmpg ' .$VIM_TTY)
	sleep 100m
	if ! LibCallNrAmp('checkIfRunning', 'vimamp')
		echo "Can't execute the player"
		exit
	endif
	return 1
endfunction
	
function! VimAmpStart()
	set nocp
	syntax on
	let g:song_vol = GetVolume()
	if (g:VimAmp)
		exit
	else
		let g:VimAmp = 1
	endif
	call VimAmpHiSyntax()
	call MenuHiSyntax()
	call AmpSetStl()


	if g:bg_color == 'black'
		hi normal ctermfg=gray guifg=gray ctermbg=black guibg=black
		hi nonText ctermfg=black guifg=black
	else
		hi nonText ctermfg=white guifg=white
		hi normal ctermfg=black guifg=black ctermbg=white guibg=white
	endif
	"set shell=/bin/sh
	call CheckAndStartPlayer()

	if argc() > 0
		let g:edit_file = argv(0)
	endif
	call OpenAllWin()
endfunction


function! SyntaxMainCSong()
	if g:dull == 'Dark'
		hi mSong ctermfg=DarkGreen guifg=DarkGreen
	else
		hi mSong ctermfg=Green guifg=Green
	endif

	if g:current_song == ''
		return
	endif

	silent! syntax mSong clear
	let sval = escape(g:current_song, "$'~")
	exe 'syn match mSong "'. sval. '"'
endfunction

function! CheckFastChange()
	let time = localtime()
	if time - g:prev_song_changed_time < 4
		let g:prev_song_changed_time = time
		let g:error_song_changed = g:error_song_changed + 1
		if g:error_song_changed > 6
			echo 'Songs Changing too fast....'
			return 1
		endif
	else
		let g:prev_song_changed_time = time
		let g:error_song_changed = 0
	endif
	return 0
endfunction

function! SongChanged(arg)

	if a:arg == 'map'
		if CheckFastChange()
			return
		endif
	endif


	let g:update_prog = 1
	let g:song_changed = 0

	if (g:dull != 'Dark')
		set title
		let &titlestring = 'VimAmp: ' .g:current_song
	endif

	let val = winnr()
	call GetCurrentSong()

	let n = bufwinnr(g:file_lyrics)
	if (n != -1) 
		call GetLyrics()
		exe n. 'wincmd w '
		e!
		2
		nor T k
		normal T
		call LyricsHiSyntax()
	endif

	call JumpToWin(g:win_plist)
	let g:plist_position = GetCSongPos()
	let g:plist_total = line('$') * !!getfsize(expand('~/.etc/VimAmp/Playlist.'))
	call CSong('fresh')

	call JumpToWin(g:win_main)
	5
	s/>>/==/
	s/|==/|>>/
	call setline(3, g:current_song)
	call SyntaxMainCSong()
	6
	call MenuCurrentSyntax()

	exe val. 'wincmd w '
	let g:update_prog = 0
endfunction

function! StatusDelim()
	if bufname('%') == bufname('nothing')
		return ''
	elseif bufname('%') == bufname(g:file_main)
		return '------------------------------'
	elseif bufname('%') == bufname(g:file_lyrics)
		return ''
	elseif bufname('%') == bufname('nlist')
		return ''
	elseif bufname('%') == bufname(g:file_plist)
		return ''
	elseif bufname('%') == bufname(g:file_equal)
		return ''
	elseif bufname('%') == bufname(g:file_lyrics)
		return ''
	elseif bufname('%') == bufname(g:file_plist)
		return '---'
	elseif bufname('%') == bufname(g:file_equal)
		return '-------------'
	elseif bufname('%') == bufname(g:browser_file)
		return ''
	elseif bufname('%') == bufname('VimHelp')
		return ''
	else
		if (&modified)
			return '[' .bufnr('%'). ']' . ' ' .expand('%'). ' '. '[+]' . ' ' .line('.')
		else
			return '[' .bufnr('%'). ']' .' ' .expand('%') . ' ' . line('.')
		endif
	endif
	
endfunction

function! StatusLeft()
	if bufname('%') == bufname('nothing')
		if g:in_edit_window
			return g:current_song
		else
			"return 'Vimamp http://www.vimamp.com'
			return ''
		endif
	elseif bufname('%') == bufname(g:file_main)
		return '-------------'
	elseif bufname('%') == bufname('nlist')
		return '¯¯¯¯¯¯¯¯¯' .g:plist_position. ' (' .g:plist_total. ') '
	elseif bufname('%') == bufname(g:file_equal)
		return ''
	elseif bufname('%') == bufname(g:file_plist)
		return '__________________'
	elseif bufname('%') == bufname(g:file_lyrics)
		return '' 
	elseif bufname('%') == bufname(g:browser_file)
		return ''
	endif

	return ''
endfunction

function! StatusRight()
	if bufname('%') == bufname('nothing')
		return 'vol [' .g:song_vol. '] '
	elseif bufname('%') == bufname(g:file_main)
		return g:left_window_title
	elseif bufname('%') == bufname('nlist')
		return 'Playlist'
	elseif bufname('%') == bufname(g:file_equal)
		return '[' .g:equal_status .  ']'
	elseif bufname('%') == bufname(g:file_plist)
		return 'Equalizer'
	elseif bufname('%') == bufname(g:browser_file)
		return '[' .getcwd(). ']'
	elseif bufname('%') == bufname(g:file_lyrics)
		return ''
	endif
	return ''
endfunction

function! StatusRightDelim()
	if bufname('%') == bufname('nothing')
		return ''
	elseif bufname('%') == bufname(g:file_main)
		return '-----------------------------'
	elseif bufname('%') == bufname('nlist')
		return ''
	elseif bufname('%') == bufname(g:file_equal)
		return '             '
	elseif bufname('%') == bufname(g:file_plist)
		return ''
	elseif bufname('%') == bufname(g:browser_file)
		return ''
	elseif bufname('%') == bufname(g:file_lyrics)
		return ''
	endif
	return ""
endfunction


function! MapNope()
   	let idx = 65

	while idx < 123
		if idx == 9 || idx == 12 || idx == 13 || idx == 58
			continue
		endif
		exe 'map ' .nr2char(idx). '  <nop>'
		let idx = idx + 1
	endwhile
endfunction

function! AmpSetStl()
	set stl=%2*%{StatusDelim()}%2*%{StatusLeft()}%2*%=%3*%{StatusRight()}%2*%{StatusRightDelim()}
endfunction

function! VimAmp()
	if g:update_prog
		return
	endif

	set viminfo='20,h,rgdb,n~/.etc/vim/.tmp/.vimamp
	set winminheight=0
	set winminwidth=0

	let g:in_edit_window = 0

	if g:bg_color == 'black'
		hi VertSplit cterm=none ctermbg=none ctermfg=DarkCyan guibg=black guifg=DarkCyan
		hi user2 ctermfg=DarkCyan guifg=DarkCyan
	else
		hi VertSplit cterm=none ctermbg=none ctermfg=DarkBlue guibg=white guifg=DarkBlue
		hi user2 ctermfg=DarkBlue guifg=DarkBlue
	endif

	set nowrap
	set nonu
	set nohlsearch
	set noswapfile
	let g:song_vol = GetVolume()
	call AmpSetStl()
	0
	6
	res 7
	
	nor T	<C-y>
	normal TTTTTTTTTTTTTTTTTTTT
	set modifiable
	set ic noswf nobackup nowritebackup
	call VimAmpMap()
	map	w	:source ~/.etc/vim/vafnc.vim<CR>:call VimAmp()<CR>
	nor	<Left>	<Left>

	map	<silent> <Up>	:call ChangeVolume('+')<CR>
	map 	i	<Up>

	map	<silent> <Down>	:call ChangeVolume('-')<CR>
	map	k	<Down>

	map <silent> <M-X>	<C-l>:call FixWindowSize()<CR>
	call MenuEnter()
	call SyntaxMainCSong()
endfunction

function! ThirdWindowIfExtern()
	let file =  bufname(winbufnr(3))

	if file == bufname(g:file_lyrics) || file == bufname('VimHelp')
		return 0
	endif

	return 1
endfunction


function! OpenAmpBrowser()
	let g:old_cwd = getcwd()

	let g:browse_start_dir = g:add_browse_dir

	let g:third_window_flag = ThirdWindowIfExtern()

	call JumpToWin(g:win_misc)

	let g:browser_func = 'Amp'
	vi ~/.etc/.tmp/Browser.
endfunction

function! AmpBrowserExit()
	let g:browser_help_on = 0
	if g:edit_file == ''
		let n = bufnr(g:file_lyrics)
	else
		let g:left_window_title = 'Edit Window'
		let n = bufnr(g:edit_file)
	endif

	exe 'b' .n
	bd ~/.etc/.tmp/Browser.
	call JumpToWin(g:win_main)
	call JumpToWin(g:win_plist)
	$
endfunction



function! BrowserEnterCAmEdit()
	call BrowserEnterCAmp()
	let g:left_window_title = 'Edit Window'
endfunction


function! BrowserSyntaxCAmEdit()
endfunction

function! BrowserActionCAmEdit()
	call BrowserActionC()
endfunction

function! BrowserLeaveCAmEdit()
	call RestoreEdit()
	map	<Tab>	:call AmpPrgExit()<CR>
	let g:edit_browse_dir = getcwd()
endfunction

function! BrowserEnterCAmp()
	map 	s	:call SelectAdd(getline("."))<CR>
	map	S	:call SelectClear()<CR>
	map	r	:call RecurseAdd(getline('.'))<CR>
	map	q	:call AmpBrowserExit()<CR>
	map	`	:call AmpBrowserExit()<CR>
	map	e	:call AmpBrowserExit()<CR>
	map	<Tab>	q
	map	x	:call AmpBrowserSyntax()<CR>
	let g:left_window_title = 'Browser'
	call BrowserSyntaxAmp()
endfunction

function! BrowserSyntaxAmp()
	syn match Rest	".*[^/]$"
	syn match Song   "^.*\.[mM][pP]3"
	hi Song cterm=none ctermfg=none  
	hi Rest cterm=none ctermfg=none 
	syn match directory "[^ ].*\/"
	syn match Heading ".*Directory of: .*"
	if( g:dull == 'Dark')
		hi Heading cterm=none ctermfg=DarkCyan guifg=DarkCyan
		hi Current cterm=none ctermfg=DarkGreen guifg=DarkGreen
	else
		hi Heading cterm=none ctermfg=Cyan guifg=Cyan
		hi Current cterm=none ctermfg=Green guifg=Green
	endif
endfunction

function! BrowserActionCAmp()
	if (exists('g:browser_select_all'))
		let dirval = g:browser_select_all
	else 
		let dirval = getcwd() . "/" .  getline(".")
		let dirval = substitute(dirval, '\*$', '', '')
		if !filereadable(dirval)
			return
		endif
	endif

	let dirval = escape(dirval, " $&%'-()")

	call system("/bin/ls -d " . dirval  . " >> ~/.etc/VimAmp/VimAmp.m3u")

	call LibCallNrAmp('killByName', '1 vimamp')
	call AmpBrowserExit()
endfunction

function! BrowserLeaveCAmp()
	let g:add_browse_dir = getcwd()
	exe 'lcd ' .g:old_cwd
	nor <Left>	<Left>
endfunction

function! MenuHiSyntax()
	hi CurrentMenu ctermfg=DarkRed guifg=DarkRed
	if g:bg_color == 'black'
		hi MenuAll ctermfg=DarkCyan guifg=DarkCyan
	else
		hi MenuAll ctermfg=DarkBlue guifg=DarkBlue
	endif

endfunction

function! MenuEnterHilight()
	hi CurrentMenu ctermfg=DarkRed guifg=DarkRed
	if g:bg_color == 'black'
		hi MenuAll ctermfg=DarkCyan guifg=DarkCyan
	else
		hi MenuAll ctermfg=DarkBlue guifg=DarkBlue
	endif
	syn clear
	syntax match Namel "lxlabs"
	syn match ProgWid '>>'
	syn match Amptitle 'VimAmp'
	syn match Url	'http://.*'
	syn match MenuAll "Hlp"
	syn match MenuAll "«"
	syn match MenuAll "|>"
	syn match MenuAll "||"
	syn match MenuAll "¤"
	syn match MenuAll "»"
	syn match MenuAll "Lyr"
	syn match MenuAll "Qt"
	syn match MenuAll "qt"
	syn match MenuAll "Eq"
	syn match MenuAll "PL"
	syn match MenuAll "Add"
endfunction


function! MenuEnter()
	let &ts = 4
	let g:isk = &isk
	set isk=1-255,^32,^9

	call MenuEnterHilight()
	call MenuCurrentSyntax()

	map	<Left>	:call ProcessMenu("L")<CR>
	map	<Right>	:call ProcessMenu("R")<CR>
	map <silent>	<C-m>	:call ProcessMenuCmd(expand('<cWORD>'))<CR>
endfunction

function! MenuCurrentSyntax()
	syntax clear CurrentMenu
	exe "syn match CurrentMenu \"\\\<" . expand("<cWORD>") . "\\\>\""
endfunction

function! ShowMenuTip()
   	 if expand('<cWORD>') == '«'
		echo 'Prev Song (z)'
		
	elseif expand('<cWORD>') == '|>' 
		echo 'Play (x)'
		
	elseif expand('<cWORD>') == '||' 
		echo 'Pause  (c)'
		
	elseif expand('<cWORD>') == '¤'
		echo 'Stop  (v)'
		
	elseif expand('<cWORD>') == '»'
		echo 'Next Song  (b)'

	elseif expand('<cWORD>') == 'Add'
		echo 'Add to PlayList (l)'

	elseif expand('<cWORD>') == 'Qt'
		echo 'Quit (q)'

	elseif expand('<cWORD>') == 'Hlp'
		echo 'Help (F1)'

	elseif expand('<cWORD>') == 'Eq'
		echo 'Equalizer (g)'

	elseif expand('<cWORD>') == 'Lyr'
		echo 'Lyrics (r)'
	endif
endfunction

function! ProcessMenu(flag)

	if (a:flag == "R")
		nor T W
		normal T
	elseif (a:flag == "L")
		nor T B
		normal T
	endif


	let n = 6
	if(line('.') == n - 1)
		exe n
		nor T $
		normal T
	endif

	if(line('.') == n + 1)
		exe n
		nor T 0
		normal T
	endif

	call MenuCurrentSyntax()
	call ShowMenuTip()
	map	<C-m>	:call ProcessMenuCmd(expand("<cWORD>"))<CR>
	
endfunction

function! OpenPrg()

	let g:third_window_flag = !ThirdWindowIfExtern()

	call JumpToWin(g:win_misc)
	let g:VimAmp = 0
	let g:left_window_title = 'Edit Window'


	if g:edit_file == ''
		let g:old_cwd = ''
		map	<Tab>	:call AmpBrowserExit()<CR>
		let g:browse_start_dir = g:edit_browse_dir
		let g:browser_func = 'AmEdit'
		vi ~/.etc/.tmp/Browser.
	else
		call RestoreEdit()
		map	<Tab>	:call AmpPrgExit()<CR>
		exe 'vi ' .g:edit_file
	endif

endfunction

function! AmpPrgExit()
	call JumpToWin(g:win_main)

	if g:song_changed
		call SongChanged('nomap')
	endif

	call JumpToWin(g:win_plist)
endfunction

function! ProcessMenuCmd(val)

   	if(a:val=="«")
		call SendActionPlay(g:prev_song, 1)
		
	elseif(a:val=="|>")
		call SendActionPlay(g:same_song, 1)
		
	elseif(a:val=="||")
		call SendActionPlay(g:paus_song, 0)
		
	elseif(a:val=="¤")
		call SendActionPlay(g:stop_song, 0)
		
	elseif(a:val=="»")
		call SendActionPlay(g:next_song, 1)
		
		
	elseif(a:val=="PL")
		let n = bufwinnr(g:file_plist)
		exe n. 'wincmd w'
		
	elseif(a:val=="Eq")
		call OpenEqualizer('12sp')
	
	elseif a:val == 'Prg'
		call OpenPrg()

	elseif(a:val=="Lyr")
		call OpenLyrics()
	
	elseif(a:val=="Add")
		call OpenAmpBrowser()
	
	elseif(a:val=="Qt")
		call AmpExit()
		
	elseif(a:val=="Hlp")
		call OpenHelp()
	endif

endfunction



