"************************	Vmaps ***********************************

ca	pp	2>&1 \| less
ca	cw	<cword>
ca	cwo	<cWORD>
ca	re	marks
ca	lcf	!ecd -f<CR>
ca	lc	Lc
ca	lf	Lf
ca	gd	Gdb


imap <C-?> <BS>
cmap <C-?> <BS>

"nor [[ ?^\s*{<CR>
"nor ]] :call EndofFunc()<CR>


function! EndofFunc()
	let l = getline('.')
	if (match(l, '^\s*{') == -1)
		call search('^\s*{', 'b')
		let l = getline('.')
	endif
	let l = substitute(l, '{.*', '}', '')
	call search('^' . l)
endfunction
	
   
cno	<Tab>	<C-d><C-l>
cno	<C-A>	<Home>
cno	<C-E>	<End>
cmap	<C-g>	<C-c>
ino	<C-K>	<C-X><C-K>
imap <M-i>   <Up>
imap <M-k>   <Down>
ino	<C-O>	<C-X><C-L>
ino	<C-F>	<C-X><C-F>
ino	<C-e>	<End>
ino	<C-a>	<Home>
cmap 	<C-g>	<C-c>
cab info Info
cab cvsdiff CvsDiff
"nor	<C-f>	<C-f><C-e><C-e>
"nor	<C-b>	<C-b><C-y><C-y>

"***********BrainDamage 
silent! unmap ,mh
silent! unmap ,h1
silent! unmap ,h2
silent! unmap ,h3
silent! unmap ,h4
silent! unmap ,h5
silent! unmap ,h6
silent! unmap ,hb
silent! unmap ,he
silent! unmap ,hi
silent! unmap ,hu
silent! unmap ,hs
silent! unmap ,ht
silent! unmap ,hx



map	<F2>	:call BrowserLeaveLock()<CR><CR>
map	<F1>	:Mancmd c <cword><CR><CR>
map	<F6>	:bd<CR>
map	<F12>	:call Makesess()<CR>
map	<F8>	:call Toggle("ls")<CR>
map	<F11>	:call Toggle("a")<CR>
map	S	:shell<CR>

nor <C-w>j	<C-w>h
nor <C-w><M-j>	<C-w>h
nor <C-w>J	<C-w>H
nor <C-w><C-j>	<C-w>h
nor <C-w><C-i>	<C-w>k
nor <C-w>i	<C-w>k
nor <C-w><M-i>	<C-w>k
nor <C-w>I	<C-w>K
nor <C-w><C-k>	<C-w>j
nor <C-w><M-k>	<C-w>j
nor <C-w><C-w>	<C-w>p
nor <C-w>k	<C-w>j
nor <C-w>K	<C-w>J
nor <C-w>o	:call SetScrollOther()<CR>
nor <C-w><C-o>	:call SetScrollOther()<CR>

nor <M-v>j	<C-w>h
nor <M-v>J	<C-w>H
nor <M-v><M-j>	<C-w>h
nor <M-v><M-i>	<C-w>k
nor <M-v><M-l>	<C-w>l
nor <M-v>i	<C-w>k
nor <M-v>I	<C-w>K
nor <M-v><M-k>	<C-w>j
nor <M-v><M-w>	<C-w>p
nor <M-v>k	<C-w>j
nor <M-v>K	<C-w>J
nor <M-v>o	:call SetScrollOther()<CR>
nor <M-v><M-o>	:call SetScrollOther()<CR>
nor <M-v><M-n>	<C-w>n
map <C-d><C-d> :wq<CR>


function! WindowMap()
	nor	i	<C-w>k
	nor k	<C-w>j
	nor	j	<C-w>h
	nor	l	<C-w>l
	nor	w	<C-w>p
	nor f	<C-w>>
	nor d	<C-w>-
	nor e	<C-w>+
	nor s	<C-w><
	nor gs	:call WindowMapToggle()<CR>
endfunction

function! WindowUnMap()
	unmap f
	unmap d
	unmap w
	nor e ge
	call ReMaps()
	map s <F50>
endfunction



map	Y	y$
map	<C-q>	<C-^>
imap	<C-q>	<C-o><C-^>
map	<t_k5>	:e!<CR>
map	K	:call Manual('prg', expand('<cword>'))<CR>

cmap	<C-b>	<Left>
cmap	<C-p>	<Up>
cmap	<C-n>	<Down>
cmap	<M-e>	vi ~/.etc/
cmap	<M-E>	~/.etc/
cmap	<M-h>	~/
imap	<M-h>	~/

nor	<C-O>	<C-]>
nor	<C-I> 	<C-O> 
nor	<C-K>	<C-I>
nor	<C-S>	:update<CR>
ino	<C-S>	<C-O>:update<CR>
nor <C-i> V=


map	zpu	<S-Up>
map	zpv	<C-Up>
map	zpw	<M-Up>
map	zpd	<S-Down>
map	zpe	<C-Down>
map	zpf	<M-Down>
set	<S-Left>=[d

map [6~ <PageDown>
map [5~ <PageUp>
map [3~ <Del>
map [2~ <Ins>
map  [20~  <F9>
nor <F9> :Tlist<CR>
map	<Del>	<C-e>
map	<Insert>	<C-y>

"map	Od 	<C-Left>
"imap	Od	 <C-Left>
map	zpl	<M-Left>
"cmap	zpl	<M-Left>
set	<S-Right>=[c
"map	Oc	<C-Right>
"imap	Oc	<C-Right>
map	zpr	<M-Right>
"cmap	zpr	<M-Right>
map	zpt	<S-Tab>
map!	<M-I>	<Up>
map	<M-J>	<M-Left>
map!	<M-J>	<S-Left>
map	<M-j>	<S-Left>
map!	<M-j>	<Left>
map	<M-i>	<M-Up>
map	<M-k>	<M-Down>
cmap	<M-k>	<Down>
cmap	<M-i>	<Up>
map!	<M-K>	<Down>
map	<M-l>	<S-Right>
map!	<M-l>	<Right>
map	<M-L>	<M-Right>
map!	<M-L>	<S-Right>

map	<S-Up>    	7i
map	<S-Down>	7k

"map	<M-Up>   :set nomore<CR>:cN<Bar>cl<Bar>set more<CR>
"map	<M-Down>  :set nomore<CR>:cn<Bar>cl<Bar>set more<CR>
"nor	<M-Left> :set nomore<CR>:ju<Bar>set more<CR>
"nor	<M-Right> :set nomore<CR><Tab>:ju<Bar>set more<CR>
nor	<M-Up>   <C-o>
nor	<M-Down> <C-i>

"*****************************	Vmaps **************************************

"* ~/.etc/vim/vcpp  *
"* ~/.etc/vim/vreg  *
"* ~/.etc/vim/vfunc *
"* ~/.etc/vim/vimrc *
"* ~/.etc/vim/vmaps *
"* ~/.etc/vim/vmeta *
"* ~/.etc/vim/vwin  *
"* ~/.etc/vim/vtmp  *
"* ~/.etc/vim/vcols *
