	nor	<buffer> i	gk
	nor	<buffer> k	gj
	nor	<buffer> <Up>	g<Up>
	nor	<buffer> <Down>	g<Down>
	ino	<buffer> <Up>	<C-o>g<Up>
	ino	<buffer> <Down>	<C-o>g<Down>


" We have this in two places... Must remove one.
function! HtmlIncludeExpr(a)
	let ret = substitute(a:a,'^/','','')
	let ret = substitute(ret,'/$','/content.html', '')
	return ret
endfunction


set  includeexpr=HtmlIncludeExpr(v:fname)
source ~/.etc/vim/closetag.vim


iab <buffer> gll global $gbl, $sgbl, $login, $ghtml;
iab <buffer> lxin include_once "htmllib/lib/include.php";
iab <buffer> gllh global $ghtml;
iab <buffer> cen <center>
iab <buffer> kk [quote]
iab <buffer> nkk [/quote]

iab <buffer> cdd  [code]
iab <buffer> ncdd [/code]

iab <buffer> ncen </center>

iab <buffer> pg <html><head><title>#</title></head><body></body></html> <Up>
iab <buffer> tg target=_blank>
iab <buffer> htp http://www.
iab <buffer> rslr <reseller>
iab <buffer> nrslr </reseller>
iab <buffer> admn <%ifblock:isadmin%>
iab <buffer> lnk <link: ><Left>
iab <buffer> lximg <lximg: ><Left>
iab <buffer> nadmn </%ifblock%>
iab <buffer> cc <?php
iab <buffer> cce <?php echo
iab <buffer> ncc ?>
iab <buffer> rr  print <<<END
iab <buffer> nrr  END;
iab <buffer> li <li>
iab <buffer> nli </li>
iab <buffer> fn <font><Left>
iab <buffer> nfn </font>
iab <buffer> sp &nbsp;
iab <buffer> qstn <lximg: icon/question.gif> 
iab <buffer> ansr <lximg: icon/answer.gif>
iab <buffer> nlnk </link>
iab <buffer> lt &lt;
iab <buffer> gt &gt;
iab <buffer> bb [b]
iab <buffer> nbb [/b]
iab <buffer> qb <b>
iab <buffer> nqb </b>
iab <buffer> br <br>
iab <buffer> hr <hr>
iab <buffer> pp <p>
iab <buffer> npp </p>
iab <buffer> ht <html>
iab <buffer> nht </html>
iab <buffer> hd <head>
iab <buffer> nhd </head>
iab <buffer> ti <title>
iab <buffer> nti </title>
iab <buffer> bd <body topmargin=0 leftmargin=0>
iab <buffer> nbd </body>
iab <buffer> ifr <iframe name=fr1 src= >
iab <buffer> nifr </iframe>
iab <buffer> hdd <p> <font color=red><b>
iab <buffer> hddb <p> <font color=blue><b>
iab <buffer> hex  <font color=#bb3333><b>
iab <buffer> nhex  </b></font>
iab <buffer> nhdd  </b> </font> </p>
iab <buffer> hx  <font color=#bb3333>
iab <buffer> nhx  </font>
iab <buffer> em <em>
iab <buffer> nem </em>
iab <buffer> str <strong>
iab <buffer> ii <i>
iab <buffer> nii </i>
iab <buffer> nstr </strong>
iab <buffer> ppre <pre>
iab <buffer> nppre </pre>
iab <buffer> ul <ul>
iab <buffer> nul </ul>
iab <buffer> ol <ol>
iab <buffer> nol </ol>
iab <buffer> dl <dl>
iab <buffer> ndl </dl>
iab <buffer> dt <dt>
iab <buffer> ndt </dt>
iab <buffer> dd <dd>
iab <buffer> adr <address>
iab <buffer> nadr </address>
iab <buffer> aa <a href=><Left>
iab <buffer> naa </a>
iab <buffer> uu <u>
iab <buffer> nuu </u>
iab <buffer> tr <tr>
iab <buffer> ntr </tr>
iab <buffer> td <td><Left>
iab <buffer> ntd </td>
iab <buffer> tbl <table cellpadding=0 cellspacing=0>
iab <buffer> ntbl </table>
iab <buffer> h1 <h1>
iab <buffer> incl <!--#include virtual="" --> <Left><Left><Left><Left><Left><Left>

iab <buffer> dv	<div>
iab <buffer> ndv </div>
iab <buffer> nh1 </h1>
iab <buffer> h2 <h2>
iab <buffer> nh2 </h2>
iab <buffer> h3 <h3>
iab <buffer> nh3 </h3>
iab <buffer> h4 <h4>
iab <buffer> nh4 </h4>
iab <buffer> h5 <h5>
iab <buffer> nh5 </h5>
iab <buffer> h6 <h6>
iab <buffer> nh6 </h6>
iab <buffer> ig <img src=><Left>
iab <buffer> frm <form method=<?php echo lx_debug_method() ?> action=><Left>
iab <buffer> pfrm print("<form method=" . lx_debug_method() . " action=>");<Left>
iab <buffer> fdb <?php lx_phpdebug() ?>
iab <buffer> pfdb lx_phpdebug();
iab <buffer> nfrm </form>
iab <buffer> fr <form action= method=get><Left><Left><Left><Left><Left><Left><Left>
iab <buffer> nfr </form>
