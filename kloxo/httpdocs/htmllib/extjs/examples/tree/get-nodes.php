<?
// from php manual page

chdir("/usr/local/lxlabs/kloxo/httpdocs/");
include_once "htmllib/lib/include.php"; 

log_ajax($_REQUEST);

$dir = isset($_REQUEST['lib'])&&$_REQUEST['lib'] == 'yui' ? '../../../' : '../../';
$node = isset($_REQUEST['node'])?$_REQUEST['node']:"";
if(strpos($node, '..') !== false){
    die('Nice try buddy.');
}
$nodes = array();
$d = dir($dir.$node);
while($f = $d->read()){
    if($f == '.' || $f == '..' || substr($f, 0, 1) == '.')continue;
    $lastmod = date('M j, Y, g:i a',filemtime($dir.$node.'/'.$f));
    if(is_dir($dir.$node.'/'.$f)){
        $qtip = 'Type: Folder<br />Last Modified: '.$lastmod;
        $nodes[] = array('text'=>$f, 'id'=>$node.'/'.$f/*, 'qtip'=>$qtip*/, 'cls'=>'folder');
    }else{
        $size = "100";
        $qtip = 'Type: JavaScript File<br />Last Modified: '.$lastmod.'<br />Size: '.$size;
        $nodes[] = array('text'=>$f, 'id'=>$node.'/'.$f, 'leaf'=>true/*, 'qtip'=>$qtip, 'qtipTitle'=>$f */, 'cls'=>'file');
    }
}
$d->close();
echo json_encode($nodes);
