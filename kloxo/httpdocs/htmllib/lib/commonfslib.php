<?php 

function shell_recurse_dir($dir, $func, $arglist = null)
{

	$list = lscandir_without_dot($dir);
	if (!$list) return;
	foreach($list as $file) {
		$path = $dir . "/" . $file;
		if (lis_dir($path)) {
			shell_recurse_dir($path, $func, $arglist);
		}
		/// After a successfuul recursion, you have to call the $func on the directory itself. So $func is called whether $path is both directory OR a file.
		$narglist = null;
		$narglist[] = $path;
		foreach((array) $arglist as $a) {
			$narglist[] = $a;
		}
		//dprint("calling with :");
		//dprintr($narglist);
		call_user_func_array($func, $narglist);
	}
}

function lxshell_direct($cmd)
{
	$username = '__system__';
	do_exec_system($username, null, $cmd, $out, $err, $ret, null);
}
function lxfile_disk_free_space($dir)
{
	$dir = expand_real_root($dir);
	lxfile_mkdir($dir);
	$ret = disk_free_space($dir);
	$ret = round($ret/(1024 * 1024), 1);
	log_shell("Disk Space $dir $ret");
	return $ret;
}


function lxshell_unzip_numeric_with_throw($dir, $file, $list = null)
{
	$ret = lxshell_unzip_numeric($dir, $file, $list);
	if ($ret) {
		throw new lxException("could_not_unzip_file", '');
	}
}
function lxshell_unzip_with_throw($username, $dir, $file, $list = null)
{
	$ret = lxshell_unzip($username, $dir, $file, $list);
	if ($ret) {
		throw new lxException("could_not_unzip_file", '');
	}
}

function lxuser_unzip_with_throw($dir, $file, $list = null)
{
}

function lxshell_redirect($file, $cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$start = 2;
	eval($sgbl->arg_getting_string);
	$cmd = getShellCommand($cmd, $arglist);
	$return = null;
	system("$cmd > $file 3</dev/null 4</dev/null 5</dev/null 6</dev/null", $return);
	return $return;
}

function lxshell_directory($dir, $cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$dir = expand_real_root($dir);
	$username = '__system__';

	$start = 2;
	eval($sgbl->arg_getting_string);
	$cmd = getShellCommand($cmd, $arglist);
	do_exec_system($username, $dir, $cmd, $out, $err, $ret, null);
	return $out;

}
function lxshell_output($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$username = '__system__';

	$start = 1;
	eval($sgbl->arg_getting_string);
	$cmd = getShellCommand($cmd, $arglist);
	do_exec_system($username, null, $cmd, $out, $err, $ret, null);
	return $out;
}


function lxshell_user_return($username, $cmd)
{
	global $sgbl;
	$start = 2;
	eval($sgbl->arg_getting_string);
	$cmd = getShellCommand($cmd, $arglist);
	$ret = new_process_cmd($username, null, $cmd);
	return $ret;
}

function lxshell_return($cmd)
{
	global $sgbl;
	$username = '__system__';

	$start = 1;
	eval($sgbl->arg_getting_string);

	$cmd = getShellCommand($cmd, $arglist);
	do_exec_system($username, null, $cmd, $out, $err, $ret, null);
	return $ret;
}

function lxshell_php($cmd)
{
	lxshell_return("__path_php_path", $cmd);
}

function lxshell_input($input, $cmd)
{
	global $sgbl;
	$username = '__system__';

	$start = 2;
	eval($sgbl->arg_getting_string);
	$cmd = getShellCommand($cmd, $arglist);
	do_exec_system($username, null, $cmd, $out, $err, $ret, $input);
	return $ret;
}

/**
 * Deletes filename or empty directory. 
 * 
 * @param $file	(abstract) path to the file or to the directory
 * @return TRUE on success or FALSE on failure.
 *          
 */ 
function lxfile_rm($file)
{
	$file = expand_real_root($file);

	log_filesys("Removing $file");
	if (lxfile_exists($file)) {
		if (lis_dir($file)) {
			return rmdir($file);
		} else {
			return unlink($file);
		}
	}
	else {
		return FALSE;
	}
}


function lxfile_mv($src, $dst)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$src = expand_real_root($src);
	$dst = expand_real_root($dst);
	if (is_dir($dst)) {
		$base = basename($src);
		$dst = $dst . "/$base";
	}

	if ($sgbl->dbg > 0) {
		log_filesys("Moving $src $dst");
	}

	if (lxfile_cp($src, $dst)) {
		lxfile_rm($src);
		return true;
	}
	return false;
	
}

function lxfile_exists($file)
{
	return lfile_exists($file);
}

function lxfile_real($file)
{
	$size = lxfile_size($file);
	return ($size != 0);
}

function lxfile_nonzero($file)
{
	return lxfile_real($file);
}

function lxfile_mkdir($dir)
{
	$dir = expand_real_root($dir);
	if (lxfile_exists($dir)) {
		return true;
	}
	//dprint("Making directory... $dir\n");
	if (WindowsOs()) {
		$dir = preg_replace("/\//", "\\", $dir);
	}
	log_shell("Making directory $dir");

	$ret = mkdir($dir, 0755, true);
	if (!$ret) {
		debugBacktrace();
	}
}

function lxfile_cp($src, $dst)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$src = expand_real_root($src);
	$dst = expand_real_root($dst);
	if (is_dir($dst)) {
		$dst = $dst . "/" . basename($src);
	}
	log_filesys("Copying $src $dst");
	return system("cp $src $dst");

}

function lxfile_stat($file, $duflag)
{
	$file = expand_real_root($file);
	$list =  lstat($file);
	if (($duflag && is_dir($file)) || $file === ".trash") {
		$list['size'] = lxfile_dirsize($file, true);
	} else {
		$list['size'] = lxfile_size($file);
	}

	get_file_type($file, $list);
	return $list;

}

function get_file_type($file, &$stat)
{


	if (is_link($file)) {
		$dst = readlink($file);

		if (($dst[0] !== '/')) {
			$dst = dirname($file) . "/" . $dst;
		}
		if (!lxfile_exists($dst)) {
			$stat['ttype'] = "brokenlink";
			$stat['linkto'] = $dst;
			return;
		}
		if (is_dir($dst)) {
			$stat['ttype'] = "dirlink";
			$stat['linkto'] = $dst;
			return;
		}
		$stat['ttype'] = "filelink";
		$stat['linkto'] = $dst;
		return;
	}

	if (is_dir($file)) {
		$stat['ttype'] =  "directory";
		return;
	}
	$list = pathinfo($file);

	$ext = null;
	if (isset($list['extension']))  {
		$ext = $list['extension'];
	}

	if ($ext === "zip") {
		$stat['ttype'] =  "zip";
		return;
	}

	if ($ext === "tgz" || $ext === "tar.gz" || $ext === "gz") {
		$stat['ttype'] = "tgz";
		return;
	}

	if ($ext === "tar") {
		$stat['ttype'] = "tgz";
		return;
	}
	$stat['ttype'] = "file";

}

function lxfile_dstat($dir, $duflag)
{
	$dir = expand_real_root($dir);
	$list = lscandir_without_dot($dir);
	$ret = null;
	foreach($list as $l) {
		$stat = lstat("$dir/$l");
		get_file_type("$dir/$l", $stat);

		remove_unnecessary_stat($stat);

		if (($duflag && is_dir("$dir/$l") || $l === ".trash")) {
			$stat['size'] = lxfile_dirsize("$dir/$l", true);
		} else {
			$stat['size'] = lxfile_size("$dir/$l");
		}
		$stat['name'] = "$dir/$l";
		$ret[] = $stat;
	}
	//dprintr($ret);
	return $ret;
}


function lxfile_getfile($file, $bytes = null)
{

	$file = expand_real_root($file);
	$stat = stat($file);
	if ($stat['size'] > 5* 1000 * 1000) {
		dprint("File size too high. Taking only the last 200 lines\n");
		$lines = 200;
	}

	if ($lines === 'download') {
		throw new lxException('cannot_download_here', '');
		$lines = null;
	}
	if (!$lines) {
		$data = file_get_contents($file);
	} else {
		$data = lxfile_tail($file, $bytes);
	}
	return $data;
}

function lxfile_tail($file, $getsize)
{
	$size = lfilesize($file);
	$fp = lfopen($file, "r");
	if (!$fp) {
		return null;
	}
	fseek($fp, $size - $getsize);

	$ret = null;
	while(!feof($fp)) {
		$ret .= fread($fp, 1024);
	}
	return $ret;
	
}

function lxfile_touch($file)
{
	$file = expand_real_root($file);
	$ret = touch($file);
	return $ret;
}

