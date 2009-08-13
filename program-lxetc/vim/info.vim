" GNU Info browser
"
" Copyright (c) 2001, 2002 Slavik Gorbanyov <rnd@web-drive.ru>
" All rights reserved.
"
" Redistribution and use, with or without modification, are permitted
" provided that the following conditions are met:
"
" 1. Redistributions must retain the above copyright notice, this list
"    of conditions and the following disclaimer.
"
" 2. The name of the author may not be used to endorse or promote
"    products derived from this script without specific prior written
"    permission.
"
" THIS SCRIPT IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
" OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
" WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
" DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
" INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
" (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
" SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
" HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
" STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
" IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
" POSSIBILITY OF SUCH DAMAGE.
"
" $Id: info.vim,v 1.7 2002/11/30 21:59:05 rnd Exp $

let s:infoCmd = 'info --output=-'
if has('win32')
    let s:infoBufferName = '-Info- '
else
    let s:infoBufferName = 'Info: '
endif
let s:dirPattern = '^\* [^:]*: \(([^)]*)\)'
let s:menuPattern = '^\* \([^:]*\)::'
let s:notePattern = '\*[Nn]ote\%(\s\|$\)'
let s:indexPattern = '^\* [^:]*:\s*\([^.]*\)\.$'
let s:indexPatternHL = '^\* [^:]*:\s\+[^(]'

command! -nargs=* Info	call s:Info(<f-args>)

fun! s:Info(...)
    let file = "(dir)"
    let node = "Top"
    if a:0
	let file = a:1
	if file[0] != '('
	    let file = '('.file.')'
	endif
	if a:0 > 1
	    let node = a:2
	    let arg_idx = 3
	    while arg_idx <= a:0
		exe 'let node = node ." ". a:'.arg_idx
		let arg_idx = arg_idx + 1
	    endwhile
	endif
    endif

    call s:InfoExec(file, node)
    if winheight(2) != -1
	exe 'resize' &helpheight
    endif
endfun

fun! s:InfoExec(file, node, ...)
    " a:0 != 0 means last node requested
    if a:0
	let line = a:1
    else
	let line = 1
    endif
    if a:0 == 0 && exists('b:info_file')
	let last_file = b:info_file
	let last_node = b:info_node
	let last_line = line('.')
    endif
    let bufname = s:infoBufferName.a:file.a:node
    if buflisted(bufname) && a:0 < 2
	if &ft == 'info'
	    silent exe 'b!' escape(bufname, '\ ')
	else 
	    silent exe 'sb' escape(bufname, '\ ')
	endif
    else
    	if &ft == 'info'
	    let command = 'e!'
	else
	    let command = 'new'
	endif
	silent! exe command "+exe'setlocal''modifiable''noswapfile''buftype=nofile''bufhidden=delete'" escape(bufname, '\ ')
	setf info

	let cmd = s:infoCmd." '".a:file.a:node."' 2>/dev/null"

	" handle shell redirection portable
	if $OS !~? 'Windows'
	    let save_shell = &shell
	    set shell=/bin/sh
	endif
	let save_shellredir = &shellredir
    	set shellredir=>
	exe "silent 0r!".cmd
	let &shellredir = save_shellredir
	if $OS !~? 'Windows'
	    let &shell = save_shell
	endif

	call s:InfoBufferInit()
    endif
    let b:info_file = a:file
    let b:info_node = a:node
    if exists('last_file')
	let b:info_last_file = last_file
	let b:info_last_node = last_node
	let b:info_last_line = last_line
    endif
    setlocal nomodifiable
    if s:InfoFirstLine()
	exe line
    else
	echohl ErrorMsg | echo 'Info failed (node not found)' | echohl None
    endif
endfun

fun! s:InfoBufferInit()

    " remove all insert mode abbreviations
    iabc <buffer>

    if has("syntax") && exists("g:syntax_on")
	syn case match
	syn match infoMenuTitle		/^\* Menu:/hs=s+2,he=e-1
	syn match infoTitle		/^[A-Z][0-9A-Za-z `',/&]\{,43}\([a-z']\|[A-Z]\{2}\)$/
	syn match infoTitle		/^[-=*]\{,45}$/
	syn match infoString		/`[^`]*'/
	exec 'syn match infoLink	/'.s:menuPattern.'/hs=s+2'
	exec 'syn match infoLink	/'.s:dirPattern.'/hs=s+2'
	exec 'syn match infoLink	/'.s:indexPatternHL.'/hs=s+2,he=e-2'
	syn region infoLink		start=/\*[Nn]ote/ end=/\(::\|[.,]\)/

	if !exists("g:did_info_syntax_inits")
	    let g:did_info_syntax_inits = 1
	    hi def link infoMenuTitle	Title
	    hi def link infoTitle	Comment
	    hi def link infoLink	Directory
	    hi def link infoString	String
	endif
    endif

    " FIXME: <h> is move cursor left
    noremap <buffer> h		:call <SID>Help()<cr>
    noremap <buffer> <CR>	:call <SID>FollowLink()<cr>
    noremap <buffer> <C-]>	:call <SID>FollowLink()<cr>
    " FIXME: <l> is move cursor right
"    noremap <buffer> l		:call <SID>LastNode()<cr>
    noremap <buffer> ;		:call <SID>LastNode()<cr>
    noremap <buffer> <C-T>	:call <SID>LastNode()<cr>
    noremap <buffer> <C-S>	/
    " FIXME: <n> is go to next match
"    noremap <buffer> n		:call <SID>NextNode()<cr>
    noremap <buffer> .		:call <SID>NextNode()<cr>
    noremap <buffer> p		:call <SID>PrevNode()<cr>
    noremap <buffer> >		:call <SID>NextNode()<cr>
    noremap <buffer> <		:call <SID>PrevNode()<cr>
    noremap <buffer> u		:call <SID>UpNode()<cr>
    noremap <buffer> t		:call <SID>TopNode()<cr>
    noremap <buffer> d		:call <SID>DirNode()<cr>
    noremap <buffer> s		:call <SID>Search()<cr>
    noremap <buffer> <TAB>	:call <SID>NextRef()<cr>
    nnoremap <buffer> q		:q!<cr>
    noremap <buffer> <Space>	<C-F>
    noremap <buffer> <Backspace> <C-B>

    runtime info-local.vim
endfun

fun! s:Help()
    echohl Title
    echo 'Info browser keys'
    echo '-----------------'
    echohl None
    echo '<Space>		Scroll forward (page down).'
    echo '<Backspace>	Scroll backward (page up).'
    echo '<Tab>		Move cursor to next hyperlink within this node.'
    echo '<Enter>,<C-]>	Follow hyperlink under cursor.'
    echo ';,<C-T>		Return to last seen node.'
    echo '.,>		Move to the "next" node of this node.'
    echo 'p,<		Move to the "previous" node of this node.'
    echo 'u		Move "up" from this node.'
    echo 'd		Move to "directory" node.'
    echo 't		Move to the Top node.'
    echo '<C-S>		Search forward within current node only.'
    echo 's		Search forward through all nodes for a specified string.'
    echo 'q		Quit browser.'
    echohl SpecialKey
    echo 'Note: "," means "or"'
    echohl None
endfun

fun! s:InfoFirstLine()
    let b:info_next_node = ''
    let b:info_prev_node = ''
    let b:info_up_node = ''
    let line = getline(1)
    let node_offset = matchend(line, '^File: [^, 	]*')
    if node_offset == -1
	return 0
    endif
    let file = strpart(line, 6, node_offset-6)
    if file == 'dir'
	return 1
    endif
"    let file = substitute(file, '\(.*\)\.info\(\.gz\)\=', '\1', '')
    let b:info_next_node = s:GetSubmatch( line, '\s\+Next: \([^,]*\),')
    let b:info_prev_node = s:GetSubmatch( line, '\s\+Prev: \([^,]*\),')
    let b:info_up_node = s:GetSubmatch( line, '\s\+Up: \(.*\)')
    return 1
endfun

fun! s:GetSubmatch(string, pattern)
    let matched = matchstr(a:string, a:pattern)
    if matched != ''
	let matched = substitute(matched, a:pattern, '\1', '')
    endif
    return matched
endfun

fun! s:NextNode()
    if exists('b:info_next_node') && b:info_next_node != ''
	\ && match(b:info_next_node, '(.*)') == -1
	call s:InfoExec(b:info_file, b:info_next_node)
	return 1
    else
	echohl ErrorMsg | echo 'This is the last node' | echohl None
    endif
endfun

fun! s:TopNode()
    if b:info_node == 'Top'
	if b:info_file == '(dir)'
	    echohl ErrorMsg | echo 'Already at top node' | echohl None
	    return
	endif
	let file = '(dir)'
	let node = b:info_node
    else
	let file = b:info_file
	let node = 'Top'
    endif
    call s:InfoExec(file, node)
endfun

fun! s:DirNode()
    call s:InfoExec('(dir)', 'Top')
endfun

fun! s:LastNode()
    if !exists('b:info_last_node')
	echohl ErrorMsg | echo 'No last node' | echohl None
	return
    endif
    call s:InfoExec(b:info_last_file, b:info_last_node, b:info_last_line)
endfun

fun! s:FollowLink()
    let current_line = getline('.')
    let link = matchstr(current_line, s:notePattern)
    if link == ''
	let link = matchstr(current_line, s:dirPattern)
	if link == ''
	    let link = matchstr(current_line, s:menuPattern)
	    if link == ''
		let link = matchstr(current_line, s:indexPattern)
		if link == ''
		    echohl ErrorMsg | echo 'No link under cursor' | echohl None
		    return
		endif
		let successPattern = s:indexPattern
	    else
		let successPattern = s:menuPattern
	    endif
	    let file = b:info_file
	    let node = substitute(link, successPattern, '\1', '')
	else
	    let successPattern = s:dirPattern
	    let file = substitute(link, successPattern, '\1', '')
	    let node = 'Top'
	endif
    else
	" we got a `*note' link.
	let successPattern = s:notePattern
	let current_line = current_line.' '.getline(line('.') + 1)

	if exists('g:info_debug')
	    echo 'current_line:' current_line
	endif
	
	let link_pattern = '\*[Nn]ote [^:.]\+: \([^.,]\+\)\%([,.]\|$\)'
	let link = matchstr(current_line, link_pattern)
	if link == ''
	    let link_pattern = '\*[Nn]ote \([^:]\+\)\%(::\)'
	    let link = matchstr(current_line, link_pattern)
	    if link == ''
		echohl ErrorMsg | echo 'No link under cursor' | echohl None
		return
	    endif
	endif
	let node = substitute(link, link_pattern, '\1', '')
	let successPattern = link_pattern

	let link_pattern = '^\(([^)]*)\)\=\s*\(.*\)'
	let link = matchstr(node, link_pattern)
	let file = substitute(link, link_pattern, '\1', '')
	let node = substitute(link, link_pattern, '\2', '')
	if file == ''
	    let file = b:info_file
	endif
	if node == ''
	    let node = 'Top'
	endif
    endif
    let link_start_pos = match(current_line, successPattern)
    let link_end_pos = matchend(current_line, successPattern)
    let cursor_pos = col('.')
    if cursor_pos <= link_start_pos || cursor_pos > link_end_pos
	echohl ErrorMsg | echo 'No link under cursor' | echohl None
	return
    endif
    if exists('g:info_debug')
	echo 'Link:' strpart(current_line, link_start_pos, link_end_pos - link_start_pos)
    	echo 'File:' file 'Node:' node
    endif
    call s:InfoExec(file, node)
endfun

fun! s:NextRef()
    let link_pos = search('\('.s:dirPattern.'\|'.s:menuPattern.'\|'.s:notePattern.'\|'.s:indexPatternHL.'\)', 'w')
    if link_pos == 0
	echohl ErrorMsg | echo 'No hyperlinks' | echohl None
    else
	echo
    endif
endfun

fun! s:PrevNode()
    if exists('b:info_prev_node') && b:info_prev_node != ''
	\ && match(b:info_prev_node, '(.*)') == -1
	call s:InfoExec(b:info_file, b:info_prev_node)
	return 1
    else
	echohl ErrorMsg | echo 'This is the first node' | echohl None
    endif
endfun

fun! s:UpNode()
    if exists('b:info_up_node') && b:info_up_node != ''
	\ && match(b:info_up_node, '(.*)') == -1
	call s:InfoExec(b:info_file, b:info_up_node)
	return 1
    else
	echohl ErrorMsg | echo 'This is the top node' | echohl None
    endif
endfun

" FIXME: there is no way to correctly abort searching.
" <CTRL-C> messes up the command window and stops at empty buffer.
fun! s:Search()
    if !exists('s:info_search_string')
	let s:info_search_string = ''
    endif
    let new_search = input('Search all nodes: ', s:info_search_string)
    if new_search == ''
	return
    endif
    let s:info_search_string = new_search
    let start_file = b:info_file
    let start_node = b:info_node
    let start_line = line('.')
    while search(s:info_search_string, 'W') == 0
	if !exists('b:info_next_node') || b:info_next_node == ''
	    \ || match(b:info_next_node, '(.*)') != -1
	    silent! exe 'bwipe' escape(s:infoBufferName.start_file.start_node, '\ ')
	    silent! call s:InfoExec(start_file, start_node, start_line, 'force')
	    echohl ErrorMsg | echo "\rSearch pattern not found" | echohl None
	    return
	endif
	echo "\rSearching ... ".b:info_file b:info_next_node
	let file = b:info_file
	let node = b:info_next_node
	silent bwipe
	silent call s:InfoExec(file, node, 2)
    endwhile
endfun
