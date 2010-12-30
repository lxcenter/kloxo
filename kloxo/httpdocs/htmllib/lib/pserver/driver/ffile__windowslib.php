<?php 

include_once "htmllib/lib/pserver/driver/ffile__commonlib.php";

class ffile__windows extends lxDriverClass {

function dbactionUpdate($subaction)
{

	if_demo_throw_exception('ffile');

	if ($this->main->isOn('readonly')) {
		throw new lxexception('file_manager_is_readonly', '');
	}

	switch($subaction) {

		case "edit":
			{
				lfile_put_contents($this->main->getFullPath(), $this->main->content);
				break;

			}

		case "upload":
			{

				$filename = $this->main->getFullPath() . "/{$this->main->upload_file_name}";
				dprintr($this->main->upload_overwrite_f);
				if (!$this->main->isOn('upload_overwrite_f'))  {
					if (lfile_exists($filename)) {
						throw new lxexception('file_exists_upload', 'upload_name_f');
					}
				}
				getFromFileserv($this->main->__var_upload_tmp_server, $this->main->__var_upload_filepass, $filename);
				break;
			}

		case "rename":
			{
				$directory = dirname($this->main->fullpath);
				$new = $directory . "/" . $this->main->newname;
				if (lfile_exists($new)) {
					throw new lxexception('file_exists_rename', '');
				}
				lxfile_mv_rec($this->main->fullpath, $new);
				//lxfile_unix_chown($new, $this->main->__username_o);
				break;
			}

		case "paste":
			{
				if ($this->main->paste_list) { 
					// Hack... SPecifically checking for pasteaction. Should just use it directly in the command.
					$arglist[] = $this->main->__username_o;
					foreach($this->main->paste_list as &$_tl) {
						$_tl = $this->main->root . $_tl;
					}
					//$arglist = array_merge($arglist, $this->main->paste_list);
					$arglist[] = $this->main->paste_list;
					$arglist[] = $this->main->fullpath;

					if ($this->main->pasteaction === 'copy') {
						foreach($this->main->paste_list as $p) {
							lxfile_cp_rec($p, $this->main->fullpath);
						}
					} else {
						foreach($this->main->paste_list as $p) {
							lxfile_mv_rec($p, $this->main->fullpath);
						}
					}
					//lxfile_unix_chown_rec($this->main->fullpath, $this->main->__username_o);

				}
				break;
			}
		case "perm":
			{
				throw new lxexception('no_perm_setting', '');
				break;
			}

		case "newdir":
			{
				$i = 1;
				$rpath = $this->main->fullpath;
				$name =  "/". $this->main->newfolder_f;
				$path =  $rpath . $name;
				if (lxfile_exists($path)) {
					throw new lxexception('file_exists', '');
				}
				lxfile_mkdir($path);
				break;
			}


		case "zip_file":
			{
				foreach($this->main->zip_file_list as &$_t_f) {
					$_t_f = coreFfile::removeLeadingSlash($_t_f);
					$_t_f = basename($_t_f);
					$_t_f = "\"$_t_f\"";
				}
				$list = implode(" ", $this->main->zip_file_list);
				$oldir = getcwd();
				$fullpath = expand_real_root($this->main->fullpath);
				do_exec_system($this->main->__username_o, $fullpath, "c:/Progra~1/7-Zip/7z a NewArchive.zip $list", $out, $err, $ret, null);
				break;
			}

		case "filedelete":
			{
				foreach($this->main->filedelete_list as $f) {
					ffile__common::moveToTrash($this->main->root, $f);
				}
				break;
			}

		case "restore_trash":
			{

				foreach($this->main->restore_trash_list as $f) {
					ffile__common::restoreFromTrash($this->main->root, $f);
				}
				break;
			}

		case "clear_trash":
			{

				foreach($this->main->clear_trash_list as $f) {
					ffile__common::clearFromTrash($this->main->root, $f);
				}
				break;
			}

		case "zipextract":
			{

				$fulzippath = $this->main->root . $this->main->zip_extract_dir_f;
				if (!lxfile_exists(null, $fulzippath)) {
					lxfile_mkdir($fulzippath);
				} else  {
					$zipdir = new Ffile("localhost", "localhost", $this->main->root, $this->main->zip_extract_dir_f, $this->main->__username_o);
					$zipdir->get();
					if (!$zipdir->is_dir()) {
						throw new lxexception ("file_exists_but_not_dir", 'unzippath', $this->main->zip_extract_dir_f);
					}
				}

				$command = "c:/Progra~1/7-zip/7z x -y";

				$dir = expand_real_root($fulzippath);
				$file = expand_real_root($this->main->getFullPath());


				if (!csa($file, ":")) {
					$fullpath = getcwd() . "/$file";
				} else {
					$fullpath = $file;
				}


				$oldir = getcwd();
				$fullpath = expand_real_root($fullpath);
				do_exec_system($this->main->__username_o, $dir, "$command $fullpath", $out, $err, $ret, null);
				break;
			}
	}
}




}

