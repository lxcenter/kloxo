<?php 

function lxshell_getzipcontent($path)
{
	return lxshell_output("c:/Progra~1/7-zip/7z.exe", "l", $path);
}

function lxfile_adduser($path, $v)
{
}

function lxfile_dirsize($path)
{
	$path = expand_real_root($path);
	$t = 10000;
	return round($t/(1024), 1);
}

function lxfile_symlink($src, $dst)
{
	log_filesys("Trying on Windows Linking $src to $dst");
	return;
	//symlink($src, $dst);
}


function lxshell_unzip($username, $dir, $file, $filelist = null)
{
	$dir = expand_real_root($dir);
	$file = expand_real_root($file);


	$command = "c:/Progra~1/7-zip/7z.exe x -y";

	if (!csa($file, ":")) {
		$fullpath = getcwd() . "/$file";
	} else {
		$fullpath = $file;
	}


	$oldir = getcwd();
	do_exec_system("__system__", $dir, "$command \"$fullpath\"", $out, $err, $ret, null);
}


function lxfile_rm_rec($file)
{
	$obj = new COM("Scripting.FilesystemObject");
	$file = expand_real_root($file);
	$file = remove_extra_slash($file);
	$list = explode("/", $file);
	if (count($list) <= 2) {
		throw new lxException('recursive_removal_low_level_directories_not_allowed', '');
	}
	if (lxfile_exists($file)) {
		if (is_dir($file)) {
			$obj->deleteFolder($file);
		} else {
			$obj->deleteFile($file);
		}
	}
}

function lxfile_generic_chmod($file, $mod) { 
}
function lxfile_generic_chmod_rec($file, $mod) { }

function lxfile_generic_chown($file, $mod) { 
	return true;
}

function lxfile_generic_chown_rec($file, $mod) { 
}
function lxfile_unix_chmod($file, $mod) 
{
	
	throw new lxException('unix_chmod_not_allowed_in_windows', '');
}

function lxfile_unix_chown($file, $mod)
{
	throw new lxException('unix_chown_not_allowed_in_windows', '');
}

function lxfile_unix_chown_rec($file, $mod)
{
	throw new lxException('unix_chown_rec_not_allowed_in_windows', '');
}

function lxfile_unix_chmod_rec($file, $mod)
{
	throw new lxException('unix_chmod_rec_not_allowed_in_windows', '');
}



function lxfile_mv_rec($dirsource, $dirdest)
{
	$obj = new COM("Scripting.FilesystemObject");
	$username = "__system__";
	$dirdest = expand_real_root($dirdest);
	$dirsource = expand_real_root($dirsource);
	if (lfile_exists($dirdest)) {
		$dirdest = $dirdest . "/" . basename($dirsource);
	}
	log_filesys("MvFolder $dirsource $dirdest");
	if (is_dir($dirsource)) {
		$obj->MoveFolder($dirsource, $dirdest);
	} else {
		$obj->MoveFile($dirsource, $dirdest);
	}
}

function lxfile_cp_rec($dirsource, $dirdest)
{ 
	
	dprint("<b> I am here </b> ");
	$obj = new COM("Scripting.FilesystemObject");
	$username = "__system__";
	$dirdest = expand_real_root($dirdest);
	$dirsource = expand_real_root($dirsource);
	if (lfile_exists($dirdest) && is_dir($dirdest)) {
		$dirdest = $dirdest . "/" . basename($dirsource);
	}
	if (is_dir($dirsource)) {
		log_filesys("copyFolder $dirsource $dirdest");
		$obj->CopyFolder($dirsource, $dirdest);
	} else {
		log_filesys("copyFile $dirsource $dirdest");
		$obj->CopyFile($dirsource, $dirdest);
	}
} 


function lxshell_background($cmd)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$username = '__system__';
	$start = 1;
        $arglist = array();
        for ($i = $start; $i < func_num_args(); $i++) {
                if (isset($transforming_func)) {
                        $arglist[] = $transforming_func(func_get_arg($i));
                } else {
                        $arglist[] = func_get_arg($i);
                }
        }


	$cmd = getShellCommand($cmd, $arglist);
	$sh = new COM("Wscript.shell");
	$cmdobject = $sh->Run($cmd, 1);
	return true;
}

function do_exec_system($username, $dir, $cmd, &$out, &$err, &$ret, $input) 
{

	global $gbl, $sgbl, $login, $ghtml; 
	global $global_shell_out, $global_shell_error, $global_shell_ret;
	dprint("<hr> $dir <br> $cmd <hr> ");

	$path = "$sgbl->__path_lxmisc";

	$fename = tempnam($sgbl->__path_tmp, "system_errr");

	$execcmd = null;
	/*
	if ($username !== '__system__') {
		$execcmd = "$path -u $username";
	}
*/

	os_set_path();

	$sh = new COM("Wscript.shell");


	if ($dir) {
		if (!csa($dir, ':')) {
			$dir = getcwd() . "/$dir";
		}
		$sh->currentDirectory = $dir;
	}

	$out = null;
	$ret = 0;
	$err = null;
	dprint("\n ** mmmmmm $dir $cmd **\n");

	
	$cmdobject = $sh->Exec($cmd);
	if ($input) {
		$cmdobject->StdIn->Write($input);
	}
	$out = $cmdobject->StdOut->ReadAll();
	$err = $cmdobject->StdErr->ReadAll();
	$ret = 0;

	$sh->currentDirectory = $sgbl->__path_program_htmlbase;
	

/*
	function ReadAllFromAny($ret)
    {
		if (!($ret->StdOut->AtEndOfStream)){
  	      $Ret=$ret->StdOut->ReadAll();
		  return $Ret;
		}
		if (!($ret->StdErr->AtEndOfStream)){
			$Ret="STDERR: ".$ret->StdErr->ReadAll();
		    return $Ret;
		}
        return -1;
	}*/



	if ($ret) {
		log_shell_error("$err: [($username:$dir) $cmd]");
	}
	log_shell("$ret: $err [($username:$dir) $cmd]");
	$global_shell_ret = $ret;
	$global_shell_out = $out;
	$global_shell_error = $err;


}
