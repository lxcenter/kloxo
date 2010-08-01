<?php 

include_once "htmllib/lib/pserver/driver/ffile__commonlib.php";

class ffile__linux extends lxDriverClass {

function dbactionUpdate($subaction)
{

	global $gbl, $sgbl, $login, $ghtml; 
	if_demo_throw_exception('ffile');

	$this->aux = new Ffile__common(null, null, $this->nname);
	$this->aux->main = $this->main;

	if ($this->main->isOn('readonly')) {
		throw new lxexception('file_manager_is_readonly', '');
	}

	$chownug = "{$this->main->__username_o}:{$this->main->__username_o}";

	switch($subaction) {

		case "fancyedit":
		case "edit":
			lxuser_put_contents($chownug, $this->main->getFullPath(), $this->main->content);
			lxuser_return($chownug, "dos2unix", $this->main->getFullPath());
			lxuser_chmod($chownug, $this->main->getFullPath(), "0644");
			break;

		case "upload_s":
			$filename = $this->aux->uploadDirect();
			lxuser_chmod($chownug, $filename, "0644");
			lxfile_generic_chown($filename, $chownug);
			break;

		case "rename":
			$this->aux->reName();
			//lxfile_unix_chown($new, $this->main->__username_o);
			break;

		case "paste":
			$this->aux->filePaste();
			break;

		case "perm":
			$arg = null;
			$perm = $this->main->newperm;
			$perm = 0 . $perm ;
			if ($this->main->isOn('recursive_f')) {
				new_process_chmod_rec($this->main->__username_o, $this->main->fullpath, $perm);
			} else {
				lxfile_unix_chmod($this->main->fullpath, "$perm");
			}
			break;

		case "newdir":
			$path = $this->aux->newDir();
			lxfile_unix_chown($path, $this->main->__username_o);
			lxfile_unix_chown($path, $chownug);
			break;

		case "content":
			if ($this->main->is_image()) {
				$this->aux->resizeImage();
			} else {
				throw new lxexception('cannot_save_content', '');
			}
			break;

		case "thumbnail":
			$this->aux->createThumbnail();
			break;

		case "convert_image":
			$this->aux->convertImage();
			break;

		case "zip_file":
			$zipfile = $this->aux->zipFile();
			//lxfile_unix_chown($zipfile, $this->main->__username_o);
			//lxfile_unix_chown($zipfile, $chownug);
			break;

		case "filedelete":
			$this->aux->moveAllToTrash();
			break;

		case "filerealdelete":
			$this->aux->fileRealDelete();
			break;

		case "restore_trash":
			$this->aux->restoreTrash();
			break;

		case "clear_trash":
			$this->aux->clearTrash();
			break;

		case "download_from_http":
			$fullpath = $this->aux->downloadFromHttp();
			lxfile_unix_chown($fullpath, $this->main->__username_o);
			break;

		case "download_from_ftp":
			$fullpath = $this->aux->downloadFromFtp();
			lxfile_unix_chown($fullpath, $this->main->__username_o);
			break;

		case "zipextract":
			$dir = $this->aux->zipExtract();
			if ($sgbl->isKloxo() && $this->main->__username_o !== 'root') {
				//lxfile_unix_chown_rec($dir, "{$this->main->__username_o}");
				//lxfile_unix_chown_rec($dir, $chownug);
			}
			break;
	}
}


}

