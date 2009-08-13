" ===========================================================================
" Date:   Mon, 12 Jul 1999 15:31:21 +0200
" From: Stefan Roemer <roemer@informatik.tu-muenchen.de>
" To: Johannes Zellner <johannes@zellner.org>
" Cc: Vim users mailing list <vim@vim.org>
" Subject: Re: command & `complete'

if exists('g:Cmdline_loaded') | finish | endif
let g:Cmdline_loaded = 1

function! CmdlineCompl(flag)
" ---------------------------------------------------------------------------
    " ---------- user customizable: completion style (CTRL-X CTRL-???) --------
    if !exists("g:ccp_cmd")|let g:ccp_cmd="\<c-x>\<c-n>"|endif
    " -------------------------------------------------------------------------
    " Note: this function changes the p and q mark registers
    " -------------------------------------------------------------------------
    if !exists("g:ccp_pos")|let g:ccp_pos=0|endif
    if !exists("g:ccp_lst")|let g:ccp_lst=0|endif|let n=g:ccp_lst
    if !exists("g:ccp_str")|let g:ccp_str=""|endif|let s=g:ccp_str
    let m=@m|exe'norm mq'|put_|exe'norm"lp^r'."\<c-x>".'x$mppb"mdt'."\<c-x>".'x'
    if match(n,@m)||col("`p")!=g:ccp_pos
      let s=""|let n=@m|let g:ccp_pos=col("`p")
    elseif s[0]=~"\<c-p>\<c-n>"[a:flag]
      let s=strpart(s,1,999999)|let@m=n
    else
      let s=s."\<c-p>\<c-n>"[!a:flag]|let m=n
    endif
    exe"norm a\<c-r>m".g:ccp_cmd.s."\e^\"ly$u`q"
    let g:ccp_lst=n|let g:ccp_str=s|let@m=m
    call histdel(':','^".*')
endf

"if exists('g:autoload') | finish | endif " used by the autoload generator

" search forward
cno <c-n> <c-b>"<cr>:let@l=@:<cr>:call CmdlineCompl(0)<cr>:<c-r>l
" search backwards
cno <c-p> <c-b>"<cr>:let@l=@:<cr>:call CmdlineCompl(1)<cr>:<c-r>l
