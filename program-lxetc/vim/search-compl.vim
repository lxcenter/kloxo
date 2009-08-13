" ===========================================================================
" Date:  Fri, 22 Oct 1999 05:19:54 -0400
" From:  Stefan Roemer <roemer@informatik.tu-muenchen.de>
" To:    Douglas L. Potts <pottsdl@frc.com>
" Cc:    Vim users mailing list <vim@vim.org>
" Subject: Re: command & `complete'

if exists('g:SearchCompl_loaded') | finish | endif
let g:SearchCompl_loaded = 1

function! SearchCompl(flag)
" ---------- user customizable: completion style (CTRL-X CTRL-???) ----------
  if !exists("g:ccp_cmd")|let g:ccp_cmd="\<c-x>\<c-n>"|endif
" ---------------------------------------------------------------------------
" Note: this function changes the p and q mark registers and the register l  
" ---------------------------------------------------------------------------
  if !exists('g:scp_pos')|let g:scp_pos=0|endif
  if !exists('g:scp_lst')|let g:scp_lst='@'|endif|let n=g:scp_lst
  if !exists('g:scp_str')|let g:scp_str=''|endif|let s=g:scp_str
  " Store current register l in local var 'l'
  let @l=histget('/',-1)
  " Store current register m in local var 'm'
  let m=@m|let@m=' '
  " Mark current location as q, paste something, 
  exe'norm mq'|put_|exe'norm"lp"mp"mdbx'
  if match(@m,n)
    let s=''|let n=@m
  elseif s[0]=="\<c-p>\<c-n>"[a:flag]
    let s=strpart(s,1,999999)|let@m=n  
  else
    let s=s."\<c-p>\<c-n>"[!a:flag]|let@m=n
  endif
  exe'norm a'.@m.g:ccp_cmd.s."\e^\"ly$u`q"
  let g:scp_lst=n|let g:scp_str=s|let@m=m
  call histdel('/',-1)
endf

"if exists('g:autoload') | finish | endif " used by the autoload generator

" search '/': complete "next" (like <c-n>)
cno <c-n> <c-c>:call SearchCompl(0)<cr>/<c-r>l
" search '/': complete "previous" (like <c-p>)
cno <c-p> <c-c>:call SearchCompl(1)<cr>/<c-r>l
