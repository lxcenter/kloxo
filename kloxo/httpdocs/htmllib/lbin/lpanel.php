<?php 

chdir("../../");
include_once "htmllib/lib/displayinclude.php";

function lpanel_main()
{
	global $gbl, $login, $ghtml; 

	initProgram();
	init_language();
	print_open_head_tag();
    print_meta_tags();
    print_meta_css();
    print_meta_css_lpanel();

	$gbl->__navigmenu = null;
	$gbl->__navig = null;

    $catched = false;

    $ghtml->print_include_jscript('left_panel');
    $ghtml->print_jscript_source("/htmllib/js/lpanel-tabs.js");

    try {
		$ghtml->tab_vheight();
	} catch (exception $e) {
        print_close_head_tag();
        print("<body>\n");
        print("The Resource List could not gathered....{$e->getMessage()}<br> \n");
        $catched = true;
	}

    if (!$catched) {
        print_close_head_tag();
        print("<body>\n");
    }

    // The div id's tabs1 script markup tree-div tab-content are generated from lpanel-tabs.js
    print("<div class=\"lpanelmain\" id=\"tabs1\">\n");
    print("<div id=\"script\" class=\"lpanelnormal tab-content\">\n");
    print("<br>\n");
    $ghtml->xp_panel($login);
    print("</div>\n");
    print("<div id=\"markup\" class=\"tab-content\">\n");
    print("<div id=\"tree-div\" class=\"lpaneltree\">\n");
    print("</div>\n");
    print("</div>\n");
    print("</div>\n");

    print("</body>\n");
    print("</html>\n");
}


// Called by tab_vheight
function print_ext_tree($object)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$icondir = get_image_path('/button/');
	$icon = "$icondir/{$object->getClass()}_list.gif";

	?>

<script>
	Ext.onReady(function(){
    // shorthand
    var Tree = Ext.tree;

    var tree = new Tree.TreePanel('tree-div', {
        animate:true,
        loader: new Tree.TreeLoader({
            //dataUrl:'get-nodes.php'
            dataUrl:'/ajax.php?frm_action=tree'
        }),
        enableDD:true,
        containerScroll: true
    });

    // set the root node
    var root = new Tree.AsyncTreeNode({
        text: '<?=$object->getId()?>',
		href: '<?=$ghtml->getFullUrl('a=show')?>',
		hrefTarget: 'mainframe',
		icon: '<?=$icon?>',
        draggable:false,
        id:'/'
    });
    tree.setRootNode(root);

    // render the tree
    tree.render();
    root.expand();
});
</script>

<?php
}

lpanel_main();
