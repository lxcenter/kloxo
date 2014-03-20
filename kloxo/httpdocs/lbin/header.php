<?php
chdir("..");
include_once "htmllib/lib/displayinclude.php";
include_once "lib/default-header.php";

function header_main()
{
	global $gbl, $sgbl, $login, $ghtml;

	initProgram();
	init_language();

    print_open_head_tag();
    print_meta_tags();
    print_meta_css();

	if ($login->isDefaultSkin()) {
        print("<!-- Default Theme -->\n");
        print_header_default();
	} else {
        print_close_head_tag();
        print("<!-- Feather Theme -->\n");
        print_header_feather();
    }

    print("</body>\n</html>\n");
}


function print_one_link($name)
{
	global $gdata;
	$s = $gdata[$name];
	$desc = $s[0];
	$url = $s[1];
	$img = $s[2];
	$target = null;
	if (!csa($url, "javascript")) {
		$onclickstring = "onClick=\"top.mainframe.location='$url';\";";
	} else {
		$onclickstring = "onClick=\"$url\"";
	}
	print("<td>\n");
	print("<span title='$desc' OnMouseOver=\"style.cursor='pointer'\" $onclickstring><img src=/img/skin/kloxo/feather/default/images/$img></span>");
	print("</td>\n");
}

function print_logout()
{
	print("<td OnMouseOver=\"style.cursor='pointer'\" onClick=\"javascript:top.mainframe.logOut();\">\n");
	print("<span title=Logout><img width=15 height=14 src=/img/skin/kloxo/feather/default/images/logout.png> Logout </span>");
	print("</td>\n");
}

function print_header_feather()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$lightskincolor = $login->getLightSkinColor();

    print("<body>\n");
    print("<div id=statusbar  style='background:#$lightskincolor;scroll:auto;height:26;width:100%;border-bottom:4px solid #b1cfed;margin:2 2 2 2:vertical-align:top;text-align:top'>\n");

    $alist[] = "a=show";
    $alist = $login->createShowAlist($alist);

    $gbl->__c_object = $login;
    print("<table cellpadding=0 cellspacing=0>\n<tr>\n");
    $count = 0;
    $icount = 0;

    foreach($alist as $k => $v) {
	    if (csa($k, "__title")) { $count++ ; continue; }
	    $icount++;
	        if ($icount > 8) { continue; }
	        $v = $ghtml->getFullUrl($v);
	        $ghtml->print_div_button_on_header(null, true, $k, $v);
    }

    print("<td nowrap style='width:40px'></td>\n");
    $v = "a=list&c=ndskshortcut";
    $v = $ghtml->getFullUrl($v);
    $ghtml->print_div_button_on_header(null, true, 0, $v);
    $ghtml->print_toolbar();

    print("<td width=100%></td>\n");
    $v =  $ghtml->getFullUrl("a=list&c=ssessionlist");
    $ghtml->print_div_button_on_header(null, true, $k, $v);
    $v =  create_simpleObject(array('url' => "javascript:top.mainframe.logOut()", 'purl' => '&a=updateform&sa=logout', 'target' => null));
    $ghtml->print_div_button_on_header(null, true, $k, $v);
    print("</tr>\n</table>\n");
    print("</div>\n");
}

header_main();
