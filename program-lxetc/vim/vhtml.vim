"
"  this is an initialisation file for vi.  put it in you home
"  directory and call it '.exrc' 
"
"                          HTML - Editor
"
"   iab .... Abbreviations.   (to be used in insert mode)
"
"   if   xy  produces <XY>,
"       nxy  produces </XY>
"

set cms=<!--%s-->
let g:spell_option = ''

map <F50>vp :%s/^\(..\)/<p>  \1/ <Bar> %s/\(..\)$/\1  <\/p><CR>

imap <C-d> <c-]>
set ts=4
call ArticleEnter()
set path+=/home/lxlabs.com/html,/home/lxlabs.com

source ~/.etc/vim/vhtmlab.vim



"
"   map .... Macros.   (to be used in command mode)
"
"  ctrl-x ctrl-l : the line under the cursur ends up as the text for
"                  a hyperlink, the cursor is places on the HREF, to let
"                  you insert the URL.
"
"map <C-x><C-h> o</A>kO">I<A HREF="
"
"  ctrl-x ctrl-w : the word under the cursur ends up as the text for
"                  a hyperlink, the cursor is places on the HREF, to let
"                  you insert the URL. (this only works with the cursor
"                  at the *end* of the word)
"
"map <C-x><C-j> a</A>bbbi<A HREF="#">F#s
"
"  ctrl-x number : the line under the cursur is turned into a Heading
"                  ctrl-x 1  --->   H1  biggest Heading
"                  ctrl-x 2  --->   H2  next smaller Heading
"
"map 1 I<H1>A</H1>j
"map 2 I<H2>A</H2>j
"map 3 I<H3>A</H3>j
"map 4 I<H4>A</H4>j
"map 5 I<H5>A</H5>j
"map 6 I<H6>A</H6>j
""
" some extra commands for HTML editing
"nnor ,mh wbgueyei<<ESC>ea></<ESC>pa><ESC>bba
"nnor ,h1 _i<h1><ESC>A</h1><></C>>ESC>
"nnor ,h2 _i<h2><ESC>A</h2><ESC>
"nnor ,h3 _i<h3><ESC>A</h3><ESC>
"nnor ,h4 _i<h4><ESC>A</h4><ESC>
"nnor ,h5 _i<h5><ESC>A</h5><ESC>
"nnor ,h6 _i<h6><ESC>A</h6><ESC>
"nnor ,hb wbi<b><ESC>ea</b><ESC>bb
"nnor ,he wbi<em><ESC>ea</em><ESC>bb
"nnor ,hi wbi<i><ESC>ea</i><ESC>bb
"nnor ,hu wbi<u><ESC>ea</i><ESC>bb
"nnor ,hs wbi<strong><ESC>ea</strong><ESC>bb
"nnor ,ht wbi<tt><ESC>ea</tt><ESC>bb
"nnor ,hx wbF<df>f<df>
"

