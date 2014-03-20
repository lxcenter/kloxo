<?php 
chdir("../../");

include_once "htmllib/lib/displayinclude.php";

initProgram();
init_language();

print_open_head_tag();
print_meta_tags();
print_meta_css();
print_head_javascript();
print_close_head_tag();

print("<body topmargin=0 leftmargin=0> ");
print("<div id=statusbar  style='background:#f0f0ff;scroll:auto;height:100%;width:100%;border-top:1px solid #aaaacf;margin:0 0 0 0:vertical-align:top;text-align:top'></div> </body> ");

?>
