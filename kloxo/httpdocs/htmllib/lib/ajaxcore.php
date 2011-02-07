<?php
ob_start();

initProgram();
init_language();
check_if_disabled_and_exit();

$gbl->__inside_ajax = true;

// We need to convert the tree format to frm_o_o.
if ($ghtml->frm_action === 'tree') {
	convert_tree_to_frm_o();
}

createPrincipleObject();

$cgi_action = "__ajax_desc_{$ghtml->frm_action}";

//sleep(6);
$ret = $cgi_action();
while(@ob_end_clean());
print(json_encode($ret));
flush();

function convert_tree_to_frm_o()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$cid = $ghtml->node;

	if (!csa($cid, "&")) {
		return null;
	}
	$cid = trim($cid, "/&");

	$dlist = explode("&", $cid);
	$i = 0;
	$ghtml->__title_function = false;
	$ghtml->__resource_class = false;
	foreach((array)$dlist as $d) {
		//if (csa($d, "_s_vv_p_")) {
		if (csb($d, "__title_")) {
			$ghtml->__title_function = $d;
			continue;
		}
		if (csb($d, "__resource_")) {
			$ghtml->__resource_class = $d;
			continue;
		}

		$ghtml->__resource_class = false;

		if (csa($d, "-")) {
			list($class, $name) = getClassAndName($d);
			$frmo[$i]['class'] = $class;
			$frmo[$i]['nname'] = $name;
		} else {
			$frmo[$i]['class'] = $d;
		}
		$i++;
	}
	$ghtml->__http_vars['frm_o_o'] = $frmo;
}

function __ajax_desc_tree()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$object = $gbl->__c_object;
	$icondir = get_image_path('/button/');
	$rclist = $object->getResourceChildList();
	$cid = htmlspecialchars($ghtml->node);
	$cid = str_replace('&amp;', '&', $cid);
	if ($object->hasFileResource()) {
		$u = "a=show&k[class]=ffile&k[nname]=/";
		$u = $ghtml->getFullUrl($u);
		$v = createClName('ffile', '/');
		$ret[] = array('text'=> "File", 'icon' => "{$icondir}/ffile_show.gif", 'hrefTarget' => 'mainframe', 'href' => $u, 'id'=> "{$cid}&{$v}");
	}


	if ($ghtml->__resource_class) {
		$c = strfrom($ghtml->__resource_class, "__resource_");
		if (cse($c, "_l")) {
			$clname = $object->getChildNameFromDes($c);
			$list = $object->getList($clname);
			foreach($list as $o) {
				$u = "a=show&k[class]={$o->getClass()}&k[nname]={$o->nname}";
				$u = $ghtml->getFullUrl($u);
				$ret[] = array('text' => basename($o->nname), 'icon' => "$icondir/{$o->getClass()}_list.gif", 'hrefTarget' => 'mainframe', 'href' => $u, 'id'=> "{$cid}&{$o->getClName()}");
			}

		} else if (cse($c, "_o")) {
			$clname = $object->getChildNameFromDes($c);
			$o = $object->getObject($clname);
			$u = "a=show&o={$o->getClass()}";
			$u = $ghtml->getFullUrl($u);
			$ret[] = array('text' => $o->getClass(), 'icon' => "{$icondir}/{$o->getClass()}_show.gif", 'hrefTarget' => 'mainframe', 'href' => $u, 'id'=> "{$cid}&{$o->getClass()}");
		}
		return $ret;
	}

	if ($ghtml->__title_function) {
		$t = $ghtml->__title_function;
		$alist = $object->createShowAlist($alist);
		foreach($alist as $k => $v) {
			if (csb($k, "__title")) {
				if ($k !== $t) {
					if ($insidetitle) {
						$insidetitle = false;
						break;
					}
					continue;
				} 
				$insidetitle = true;
				continue;
			}
			if ($insidetitle) {
				$url = $ghtml->getFullUrl($v);
				if ($ghtml->is_special_url($url)) { continue; }
				$urlinfo = $ghtml->getUrlInfo($url);
				$ret[] = array('text' => $urlinfo['description']['desc'], 'icon' => $urlinfo['image'], 'hrefTarget' => 'mainframe', 'leaf' => true, 'href' => $url, 'id'=> "&end");
			}
		}

		return $ret;
	}

	if ($object->hasFunctions()) {
		$alist = $object->createShowAlist($alist);
		foreach($alist as $k => $v) {
			if (!csb($k, "__title")) {
				continue;
			}
			$title = strfrom($k, "__title_");
			if ($title === 'mailaccount') { continue; }
			if ($title === 'custom') { continue; }
			$icon = "{$icondir}/__title_$title.gif";
			if (!lxfile_exists("__path_program_htmlbase/$icon")) { 
				//lfile_put_contents("title.img", "$title.gif\n", FILE_APPEND);
				$icon = null;
			}
			$ret[] = array('text' => $v, 'icon' => $icon, 'hrefTarget' => '', 'href' => null, 'id'=> "{$cid}&$k");
		}
	}

	foreach($rclist as $c) {
		$clname = $object->getChildNameFromDes($c);
		$desc = get_description($clname);
		$desc = get_plural($desc);
		$url = $ghtml->getFullUrl("a=list&c=$clname");
		$ret[] = array('text' => $desc, 'icon' => "$icondir/{$clname}_list.gif", 'hrefTarget' => 'mainframe', 'href' => $url, 'id'=> "{$cid}&__resource_$c");
	}

	return $ret;
}

function __ajax_desc_list()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$buttonpath = get_image_path("/button");

	$object = $gbl->__c_object;
	$description = $ghtml->getActionDetails("/display.php?{$ghtml->get_get_from_current_post(null)}", null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
	$img = "<img src=$image>";
	do_list_class($object, $ghtml->frm_o_cname);
	$v = ob_get_clean();
	$v = "{$img}{$v}";
	$ret = array('ajax_dismiss' => true, 'ajax_form_name' => null, 'ajax_need_var' => $gbl->__ajax_need_var,  'allbutton' => $gbl->__ajax_allbutton, 'lx__form' => $v);
	return $ret;
}


function __ajax_desc_addform()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$object = $gbl->__c_object;
	$ghtml->print_message();
	$buttonpath = get_image_path("/button");
	$description = $ghtml->getActionDetails("/display.php?{$ghtml->get_get_from_current_post(null)}", null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
	$img = "<img src={$image}>";
	$class = $ghtml->frm_o_cname;
	$dttype = $ghtml->frm_dttype;
	do_addform($object, $class, $dttype);
	$gbl->unsetSessionV('__tmp_redirect_var');
	$gbl->c_session->was();
	$v = ob_get_clean();
	$v = "{$img}{$v}";
	$ret = array('ajax_form_name' => $gbl->__ajax_form_name, 'ajax_need_var' => $gbl->__ajax_need_var,  'allbutton' => $gbl->__ajax_allbutton, 'lx__form' => $v);
	return $ret;
}

function __ajax_desc_updateform()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$object = $gbl->__c_object;
	$ghtml->print_message();
	$buttonpath = get_image_path("/button");
	$description = $ghtml->getActionDetails("/display.php?{$ghtml->get_get_from_current_post(null)}", null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
	$img = "<img src=$image>";
	do_updateform($object, $ghtml->frm_subaction);
	$v = ob_get_clean();
	$v = "{$img}{$v}";
	$ret = array('ajax_form_name' => $gbl->__ajax_form_name, 'ajax_need_var' => $gbl->__ajax_need_var,  'allbutton' => $gbl->__ajax_allbutton, 'lx__form' => $v);
	return $ret;
}

function __ajax_desc_add()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$gbl->__ajax_refresh = true;
	return __ajax_desc_update();
}

function __ajax_desc_update()
{
	global $gbl, $sgbl, $login, $ghtml; 
	$caction = $ghtml->frm_action;
	$cgi_action = "__ac_desc_{$ghtml->frm_action}";

	try {
		$var = $cgi_action($gbl->__c_object);
		$gbl->unsetSessionV('__tmp_redirect_var');
		$login->was();
		$v = ob_get_clean();
		$ret = array('message' => $v, 'returnvalue' => 'success', 'refresh' => $gbl->__ajax_refresh);
	} catch (exception $e) {
		log_ajax("Caught Execption {$e->getMessage()}");
		$gbl->setSessionV('__tmp_redirect_var', $ghtml->__http_vars);
		$gbl->c_session->write();
		$v = ob_get_clean();
		if (is_array($e->variable)) {
			$evlist = implode(",", $e->variable);
		} else {
			$evlist = $e->variable;
		}
		$post = $ghtml->getCurrentInheritVar();
		if (strtolower($post['frm_action']) === 'update') {
			$post['frm_action'] = 'updateform';
		} else {
			$post['frm_action'] = 'addform';
		}
		if ($ghtml->frm_dttype) {
			$post['frm_dttype'] = $ghtml->frm_dttype;
		}
		if ($ghtml->frm_subaction) {
			$post['frm_subaction'] = $ghtml->frm_subaction;
		}
		$url = $ghtml->get_get_from_post(null, $post);
		$ret = array('returnvalue' => 'failure', 'refresh' => false, 'url' => "{$url}&frm_ev_list={$evlist}&frm_emessage={$e->getMessage()}&frm_m_emessage_data=$e->value");

	}
	return $ret;
}


