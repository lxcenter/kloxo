<?PHP
/**
  * PHP-Class HN_URL_rewrite
  *
  * @author Horst Nogajski <horst@nogajski.de>
  * @version 3.1
  * @revision $Revision: 1.1.1.1 $
  * @date $Date: 2009/05/08 18:30:18 $
  * $RCSfile: hn_urlrewrite.class.php,v $
  *
  *
  * License: GNU LGPL (http://www.opensource.org/licenses/lgpl-license.html)
  *
  * Download: http://hn273.users.phpclasses.org/browse/package/1844.html
  *
  * If you find it useful, you might rate it on http://www.phpclasses.org/rate.html?package=1844
  * ... and if you have rated the Version 1 or 2, please update your ratings if you find it better now =:)
  *
  **/



/**
  * VERSION 3.1 because of BIGGEST SECURITY HOLE UNDER THE SUN!
  *
  * (shame on me!)
  *
  *
  * Oh no! I've experienced that, if one use rewriting all URLs and there is also
  * an URL which refers to a htacces-password protected script, the class bypasses
  * Apaches protection!
  *
  * So I've added a rewrite_protected_scripts array to configuration.
  * This is used like the registered_scripts array but exclude its scripts from rewriting.
  *
  * If you use the registered_scripts array you don't need it.
  *
  * BUT if you use the DEFAULT MODE (rewriting all URLs) and you _HAVE_
  * HTACCESS-PASSWORD-PROTECTED files, YOU MUST REGISTER THESE SCRIPTS IN THE
  * REWRITE_PROTECTED_SCRIPTS ARRAY!
  *
  * Scripts registered in that array will neither rewritten nor redirected!
  * The class uses header('Location: ...') if a (also faked) request is send to the
  * redirect-script!
  *
  **/

/**
  * CHANGED & NEW FEATURES IN VERSION 3.0
  *
  *
  * This is the last (Major)-Version. ;-)
  *
  * Now it uses a new RegExp-Searchpattern which allows to rewrite all tags
  * at once. It's faster now!
  *
  * It rewrites the URL links (<a href... </a>),
  * the IMG links (<img src),
  * and new: the AREA links in image maps (<map> <area href... </map>)
  *
  * Per default it rewrites all tags, but optionally you can disable each of the
  * tag-groups in configuration with the new config-vars: nourls, nomaps, noimgs.
  *
  * Therefore the 3 string-rewrite-methods are not used anymore and has changed
  * to only one: string-rewrite($string)
  *
  * Errorhandling is done!
  * Now it fixes the minor Errors to guarantee users can reach pages, e.g. at the price
  * of not rewritten URLs. Have a look to the function at the end of the class.
  *
  * Also fixed some minor glitches:
  *	 - ? removed from QUERY_STRING;
  *	 - now it uses is_file() instead of file_exists() by resolving pathes and checking
  *	   if files are available on local system. (file_exists return TRUE on directories also)
  *	   Now it resolves 100% of all pathnames, ;-)
  *	 - Have cleaned up the syntax for better consistency.
  *
  **/

/**
  * CHANGED & NEW FEATURES IN VERSION 2.1
  *
  * Fixed a Bug with registering hostnames, and also added support for rewriting
  * of URLs which refers to extern servers. (If their hostnames are registered in
  * hostnames array). To enbale this, you must set the new config variable
  * 'rewrite_extern_hostnames' to TRUE.
  * NOTE: these extern servers must be configured in same way as the local one:
  * they need have the same rewrite script registered and also the the same
  * separator string.
  *
  **/

/**
  * CHANGED & NEW FEATURES IN VERSION 2.0
  *
  * Per default, the class now rewrites all URL links which refers to
  * the own serverhost and where the extension is 'php', when no array with
  * registered scriptnames is passed.
  *
  * The links to rewrite can either relative URIs, absolute URIs, or complete URLs
  * with scheme, hostname, path.
  *
  * It detect complete URLs with and without 'www.' in hostname.
  * Optionally you can pass an array with additional hostnames e.g. if you have
  * the same site reachable by different top level domains (.com + .de)
  *
  * Relative parent-links (../) in relative and absolute URIs are resolved now!
  *
  * Now, registered scripts are stored in array as values, not as keys anymore!
  * The param names are not stored in array. They will encoded too.
  * So their order doesn't matter anymore.
  *
  * If you don't want use the buffer-methods, you can use three new string-rewriting methods:
  *	 string_rewrite_urls($string)
  *	 string_rewrite_imgs($string)
  *	 string_rewrite_both($string)
  * All methods return the rewritten string, but do not store it in classes scope.
  *
  **/

/**
  * Tabsize: 4
  **/



	// CONFIGURATION


	// set to absolute url of your (apache registered) redirect-script, but without scheme and host!
	if(!defined('URL_REWRITE_SCRIPT'))
	{
		define('URL_REWRITE_SCRIPT','/sitepreview/domain.com');
	}




	if(!isset($GLOBALS['_HNURLRW_']) || !is_array($GLOBALS['_HNURLRW_']))
	{
		$GLOBALS['_HNURLRW_'] = array();
	}




	// string: separates path from params in rewritten URL
	if(!isset($GLOBALS['_HNURLRW_']['separator']))
	{
		$GLOBALS['_HNURLRW_']['separator'] = '--';
		$GLOBALS['_HNURLRW_']['separator'] = '';
	}




	// boolean: set to TRUE to disable rewriting for url tags (<a href)
	if(!isset($GLOBALS['_HNURLRW_']['nourls']))
	{
		$GLOBALS['_HNURLRW_']['nourls'] = FALSE;
	}

	// boolean: set to TRUE to disable rewriting for area tags within imagemaps (<map> <area href)
	if(!isset($GLOBALS['_HNURLRW_']['nomaps']))
	{
		$GLOBALS['_HNURLRW_']['nomaps'] = FALSE;
	}

	// boolean: set to TRUE to disable rewriting for img tags (<img src)
	if(!isset($GLOBALS['_HNURLRW_']['noimgs']))
	{
		$GLOBALS['_HNURLRW_']['noimgs'] = FALSE;
	}




	// boolean: switch encoding on/off
	// NOTE: if you do not use encoding you have to ensure that there are no slashes
	//	   within your param names or param values! If there are (only) one, it can
	//	   fool the redirection script!
	//	   Therefor the default is TRUE.
	if(!isset($GLOBALS['_HNURLRW_']['encode']))
	{
		$GLOBALS['_HNURLRW_']['encode'] = false;
	}

	// boolean: use strong encoding with alpha-key-array, (implies that encode is also TRUE)
	if(!isset($GLOBALS['_HNURLRW_']['strong_encode']))
	{
		$GLOBALS['_HNURLRW_']['strong_encode'] = FALSE;
	}

	// array: with chars, this is used for strong encoding
	if(!isset($GLOBALS['_HNURLRW_']['CodingKey']))
	{
		$GLOBALS['_HNURLRW_']['CodingKey'] = array('F','G','R','U','L','B','K','N','T','W','E');
	}




	// string: virtual filename, is added to rewritten URL links
	if(!isset($GLOBALS['_HNURLRW_']['add_indexhtml']))
	{
		$GLOBALS['_HNURLRW_']['add_indexhtml'] = 'index.html';
		$GLOBALS['_HNURLRW_']['add_indexhtml'] = false;
	}

	// string: virtual filename, is added to rewritten image links
	if(!isset($GLOBALS['_HNURLRW_']['add_pictjpg']))
	{
		$GLOBALS['_HNURLRW_']['add_pictjpg'] = 'pict.jpg';
		$GLOBALS['_HNURLRW_']['add_pictjpg'] = false;
	}




	// array: list of fileextensions which are parsed by the PHP-Interpreter
	if(!isset($GLOBALS['_HNURLRW_']['valid_php_fileextensions']))
	{
		$GLOBALS['_HNURLRW_']['valid_php_fileextensions'] = array('php');
		$GLOBALS['_HNURLRW_']['valid_php_fileextensions'] = array('gif');
	}




	// array: list of additional hostnames for the site
	// NOTE:  write complete with 'http://www.domain.com' but without trailing slash!
	if(!isset($GLOBALS['_HNURLRW_']['hostnames']))
	{
		$GLOBALS['_HNURLRW_']['hostnames'] = array();
	}

	// boolean: If you have registered hostnames for rewriting which refers to extern servers, set this to TRUE.
	// Otherwise the class checks if the file is present in filesystem!
	if(!isset($GLOBALS['_HNURLRW_']['rewrite_extern_hostnames']))
	{
		$GLOBALS['_HNURLRW_']['rewrite_extern_hostnames'] = FALSE;
	}




	// boolean:FALSE or array()
	// if you don't want register specific scripts, this mut be FALSE.
	// otherwise it must be an array with scriptnames:
	// the scripts must defined as absolute URIs:
	// e.g. '/hn_urlrewrite_example/example1.php'
	// NOTE: this array can be overwritten by an array directly passed
	// to the class when instantiating!
	// (passing an empty array suppresses rewriting!)
	if(!isset($GLOBALS['_HNURLRW_']['registered_scripts']))
	{
		$GLOBALS['_HNURLRW_']['registered_scripts'] = FALSE;
	}



	// array()
    //
    // NOTE: IF YOU USE htaccess password protected DIRECTORIES AND YOU
    // WANT TO REWRITE ALL URLS, YOU MUST REGISTER THE PROTECTED SCRIPTS
    // IN THIS ARRAY! OTHERWISE EVERYONE CAN ACCESS YOUR HTACCESS-PROTECTED
    // FILES BY ONLY CLICKING ON THE REWRITTEN LINK!
    //
	// the scripts must defined as absolute URIs:
	// e.g. '/protected/folder/file.php'
	if(!isset($GLOBALS['_HNURLRW_']['rewrite_protected_scripts']) || !is_array($GLOBALS['_HNURLRW_']['rewrite_protected_scripts']))
	{
		$GLOBALS['_HNURLRW_']['rewrite_protected_scripts'] = array();
	}
	//$GLOBALS['_HNURLRW_']['rewrite_protected_scripts'][] = '/protected/folder/file.php';
	//$GLOBALS['_HNURLRW_']['rewrite_protected_scripts'][] = '/other/folder/filename.php';




if(!defined('HN_REWRITECLASS'))
{
	define('HN_REWRITECLASS','loaded');



class hn_urlRewrite
{

	/////////////////////////////////////////////
	//	PARAMS

	// PUBLIC
	var $separator					= '--';
	var $registered_scripts			= FALSE;
	var $rewrite_protected_scripts  = array();
	var $hostnames					= array();
	var $rewrite_extern_hostnames	= FALSE;
	var $valid_php_fileextensions	= array('php');
	var $noimgs						= FALSE;
	var $nomaps						= FALSE;
	var $nourls						= FALSE;
	var $encode						= TRUE;
	var $strong_encode				= FALSE;
	var $CodingKey					= array('F','G','R','U','L','B','K','N','T','W','E');
	var $add_indexhtml				= 'index.html';
	var $add_pictjpg				= 'pict.jpg';

	// PRIVATE
	var $host						= '';
	var $path;
	var $compare;
	var $script;
	var $querystring;
	var $page;
	var $DOCUMENT_ROOT;


	/////////////////////////////////////////////
	//	CONSTRUCTOR

	function hn_urlRewrite($registered_scripts=FALSE)
	{
		// store DOCUMENT_ROOT without optionally trailing slash!
		$this->DOCUMENT_ROOT = (strrpos($_SERVER['DOCUMENT_ROOT'],'/')==strlen($_SERVER['DOCUMENT_ROOT'])-1) ? $_SERVER['DOCUMENT_ROOT'] = substr($_SERVER['DOCUMENT_ROOT'], 0, strlen($_SERVER['DOCUMENT_ROOT'])-1) : $_SERVER['DOCUMENT_ROOT'];

		if(is_array($GLOBALS['_HNURLRW_']['rewrite_protected_scripts']))
		{
			$this->rewrite_protected_scripts = $GLOBALS['_HNURLRW_']['rewrite_protected_scripts'];
		}

		if(is_array($registered_scripts))
		{
			$this->compare		 = TRUE;
			$this->registered_scripts = $registered_scripts;
		}
		elseif(is_array($GLOBALS['_HNURLRW_']['registered_scripts']))
		{
			$this->compare		 = TRUE;
			$this->registered_scripts = $GLOBALS['_HNURLRW_']['registered_scripts'];
		}
		else
		{
			$this->compare		 = FALSE;
			$this->registered_scripts	 = null;
		}
		if($this->compare && !is_array($this->registered_scripts))
		{
			$this->_ErrorHandler('no_array');
		}
		if($this->compare && count($this->registered_scripts)<1)
		{
			$this->_ErrorHandler('no_array_values');
		}


		if(is_string($GLOBALS['_HNURLRW_']['separator']))
		{
			$this->separator = $GLOBALS['_HNURLRW_']['separator'];
		}
		// slashes are not allowed in separator! (pathdivider)
		$this->separator = str_replace('/','',$this->separator);
		if($this->separator===FALSE || trim($this->separator)=='')
		{
			$this->_ErrorHandler('no_separator');
		}


		// build an array with valid hostnames
		if(is_array($GLOBALS['_HNURLRW_']['hostnames']))
		{
			foreach($GLOBALS['_HNURLRW_']['hostnames'] as $k=>$v)
			{
				if(strstr($$v,'http://www.')!==FALSE)
				{
					$GLOBALS['_HNURLRW_']['hostnames'][$k] = str_replace('http://www.','http://',$v);
				}
			}
			$this->hostnames = array_merge($this->hostnames,$GLOBALS['_HNURLRW_']['hostnames']);
		}
		$host = 'http://'.$_SERVER['HTTP_HOST'];
		$this->hostnames[] = $host;
		if(strstr($host,'http://www.')!==FALSE)
		{
			$this->hostnames[] = str_replace('http://www.','http://',$host);
		}


		// do other configuration
		$valid = array('nourls','nomaps','noimgs','encode','strong_encode','rewrite_extern_hostnames','valid_php_fileextensions','CodingKey','add_indexhtml','add_pictjpg');
		foreach($valid as $v)
		{
			if(isset($GLOBALS['_HNURLRW_'][$v]))
			{
				$this->{$v} = $GLOBALS['_HNURLRW_'][$v];
			}
		}
	}



	/////////////////////////////////////////////
	//	REDIRECTION - PART | PUBLIC

	function redirect($path=FALSE)
	{
		$this->path = $path;
		if($this->path===FALSE)
		{
			$this->_ErrorHandler('no_path');
		}
		if(!$this->_parse_path())
		{
			$this->_ErrorHandler('no_file');
		}

		// WE WANT NOT BYPASS APACHES HTACCESS PASSWORD PROTECTION!
        // NORMALLY PROTECTED SCRIPTNAMES WILL NOT REWRITTEN, BUT A HACKER
        // THAT KNOWS THIS CLASS MAY TRY TO SEND A FAKED REQUEST TO THE REWRITE-
        // SCRIPT. THIS CHECK AVOIDS SUCH COMPROMISING.
		if(in_array($this->script,$this->rewrite_protected_scripts) || preg_match('/.*\.htaccess$/',$this->script))
		{
			header('Location: '.$this->script);
			exit(0);
		}


		// Tweaking some params
		$_SERVER['SCRIPT_FILENAME']	= $this->DOCUMENT_ROOT.$this->script;
		$_SERVER['SCRIPT_NAME']		= $this->script;
		$_SERVER['PHP_SELF']		= $this->script;
		$_SERVER['REQUEST_URI']		= $this->script;
		$_SERVER['PATH_TRANSLATED']	= realpath($_SERVER['SCRIPT_FILENAME']);
		$_SERVER['QUERY_STRING']	= $this->querystring;
		// also the older ones, if present
		if(isset($SCRIPT_FILENAME))
		{
			$SCRIPT_FILENAME	= $_SERVER['SCRIPT_FILENAME'];
		}
		if(isset($SCRIPT_NAME))
		{
			$SCRIPT_NAME		= $_SERVER['SCRIPT_NAME'];
		}
		if(isset($PHP_SELF))
		{
			$PHP_SELF			= $_SERVER['PHP_SELF'];
		}
		if(isset($REQUEST_URI))
		{
			$REQUEST_URI		= $_SERVER['REQUEST_URI'];
		}
		if(isset($PATH_TRANSLATED))
		{
			$PATH_TRANSLATED	= $_SERVER['PATH_TRANSLATED'];
		}
		if(isset($QUERY_STRING))
		{
			$QUERY_STRING		= $_SERVER['QUERY_STRING'];
		}

		if(!file_exists($_SERVER['SCRIPT_FILENAME']))
		{
			$this->_ErrorHandler('no_file');
		}

		// if the scriptfile is available, we change working directory to it's dir and return filepath to redirection_script
		chdir(dirname($_SERVER['SCRIPT_FILENAME']));
		return $_SERVER['SCRIPT_FILENAME'];
	}

		////////////////////////////////////////////////////
		// REDIRECTION | PRIVATE

		function _parse_path()
		{
			// get scriptname-part and params-part
			$temp = explode('/'.$this->separator.'/', $this->path);

			// check if file exists
			$script = $this->_file_available($temp[0]);
			// if no file is found, we stop!
			if($script===FALSE)
			{
				return FALSE;
			}

			if($this->compare)
			{
				// compare against registered scripts
				$ok = FALSE;
				$check = array('','.php','index.php','/index.php');
				foreach($check as $k=>$v)
				{
					if(in_array($temp[0].$v,$this->registered_scripts))
					{
						$script = $temp[0].$v;
						$ok = TRUE;
						break;
					}
				}
				// if no valid entry is found, we stop!
				if(!$ok)
				{
					return FALSE;
				}
			}

			// write to module_global variable
			$this->script = $script;


			// parse params-part
			if(isset($temp[1]))
			{
				$params = array();
				$temp = str_replace(array('/'.$this->add_indexhtml,'/'.$this->add_pictjpg),'',$temp[1]);
				$temp = explode('/',$temp);
				foreach($temp as $p)
				{
					$p = $this->encode ? $this->_decode($p,$this->strong_encode) : $p;
					$params[] = explode('=',$p);
				}
			}
			else
			{
				$params = array();
			}

			// add params to the GET-Array
			foreach($params as $v) $_GET[strip_tags($v[0])] = strip_tags($v[1]);

			// build querystring
			if(count($params)>0)
			{
				$this->querystring = "{$params[0][0]}={$params[0][1]}";
			}
			if(count($params)>1)
			{
				for($i=1;$i<count($params);$i++)
				{
					$this->querystring .= "&{$params[$i][0]}={$params[$i][1]}";
				}
			}

			return TRUE;
		}


	/////////////////////////////////////////////////////////
	//	PAGE-REWRITING - PART with OUTPUT-BUFFERING | PUBLIC

	function buffer_start($HeadersAgainstCaching=FALSE)
	{
		// Output-Buffering starten
		ob_start();
		ob_implicit_flush(0);

		// Header gegen uebereifrige Caches senden
		if(!headers_sent() && $HeadersAgainstCaching)
		{
			header ("expires: Sun, 06 Jan 2002 01:00:00 GMT");					// Datum in der Vergangenheit
			header ("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");		   // immer geaendert
			header ("pragma: no-cache");										  // HTTP/1.0
			header ("Cache-Control: no-cache, must-revalidate");				  // HTTP/1.1
		}
	}

	function buffer_end()
	{
		// Output-Buffering beenden
		$page = ob_get_contents();
		ob_end_clean();
		$this->page = $this->_rewrite_page($page);
	}


	function send_page($compressed=FALSE)
	{
		if($compressed && ereg('gzip',$_SERVER["HTTP_ACCEPT_ENCODING"]))
		{
			// Browser kann komprimierte Daten verarbeiten:
			header ("Content-Encoding: gzip");								// Content-Encoding senden (damit der Browser was merkt)
			echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";						// hmm, gzip-start?
			$Size = strlen($this->page);									// Groesse bestimmen
			$Crc = crc32($this->page);										// Checksumme bestimmen
			$this->page = gzcompress($this->page, 9);						// Komprimieren
			$this->page = substr($this->page, 0, strlen($this->page) - 4);	// letzte 4 Bytes abschneiden
			echo $this->page;												// komprimiertes Zeugs ausgeben
			gzip_PrintFourChars($Crc);										 // Checksumme ausgeben
			gzip_PrintFourChars($Size);										// Groesse ausgeben
		}
		else
		{
			// Unkomprimierte Daten senden:
			echo $this->page;
		}
	}


	/////////////////////////////////////////////////////////
	//	STRING-REWRITING - PART | PUBLIC (without OUTPUT-BUFFERING)

	function string_rewrite($string)
	{
		return $this->_rewrite_page($string);
	}


		///////////////////////////////////////////////
		//	REWRITING | PRIVATE

		function _rewrite_page($domain, $page)
		{
			$a = array();
			$newpage = '';
			//				1		  2		3		 4			 <5>		  6		 7
			$pattern = '=^(.*?)(<area|<a|<img)(.*?)(href\=|src\=)["|\'](.*?)["|\']([^>]*?)(>.*$|>.*?</a>.*$)=msi';
			while(preg_match($pattern, $page, $a)) {
				if(count($a)>1) {
					$page = $a[7];
					$newpage .= $a[1].$a[2].$a[3].$a[4].'"';
					switch($a[2])
					{
						case '<img':
							$newpage .= $this->noimgs ? $a[5].'"'.$a[6] : $this->_rewrite_url($domain, $a[5],TRUE).'"'.$a[6];
							break;
						case '<area':
							$newpage .= $this->nomaps ? $a[5].'"'.$a[6] : $this->_rewrite_url($domain, $a[5]).'"'.$a[6];
							break;
						case '<a':
							$newpage .= $this->nourls ? $a[5].'"'.$a[6] : $this->_rewrite_url($domain, $a[5]).'"'.$a[6];
							break;
					}
				}
			}
			return $newpage.$page;
		}


		function _rewrite_url($domain, $url,$img=FALSE)
		{
            // Quick-Check

			$domain = "$domain/";

			if (csa($url, "sitepreview")) {
				return $url;
			}
			$url = str_replace("../", "", $url);

			if (csa($url, "http://$domain")) {
				$url = preg_replace("+http://$domain+", "/sitepreview/$domain/", $url);
				return $url;
			}
			if (csa($url, "http://www.$domain")) {
				$url = preg_replace("+http://www.$domain+", "/sitepreview/$domain/", $url);
				return $url;
			}

			return "/sitepreview/$domain/$url";

			$ok		   = FALSE;
			$temp		 = explode('?',$url);
			$this->host	= '';

			// give up on (none registered) extern URL, mailto or javascript
			if(!$this->_is_localURL($url) || substr($url,0,11)=='javascript:' || substr($url,0,7)=='mailto:') {
				return $url;
			}

			// check / fix URL
			$temp[0] = $this->_fix_url($temp[0],$ok);
			if(!$ok) {
				return isset($temp[1]) ? $temp[0].'?'.$temp[1] : $temp[0];
			}


            // Oncemore-Checking
            if(in_array($temp[0],$this->rewrite_protected_scripts)) {
                return $url;
            }


			// build new URL
			$temp[0] = str_replace('.php','',$temp[0]);
			$newurl  = $this->host.URL_REWRITE_SCRIPT.$this->_checkTrailingSlash($temp[0]).$this->separator;
			$this->_checkTrailingSlash($newurl);

			// rewrite params
			$params = isset($temp[1]) ? explode('&',$temp[1]) : array();
			foreach($params as $param)
			{
				if($this->encode) {
					$newurl .= $this->_encode($param,$this->strong_encode).'/';
				} else {
					$newurl .= $param.'/';
				}
			}

			$this->_checkTrailingSlash($newurl);
			return $img ? $newurl.$this->add_pictjpg : $newurl.$this->add_indexhtml;
		}


		function _fix_url($url,&$ok)
		{
			// here, we always have a local URL!
			if(substr($url,0,4)=='http')
			{
				// we have full URL and host-part is already stored in this->host
				$newurl = str_replace($this->host,'',$url);

				// compare
				$path = $this->_compare_url($newurl,'',$ok);
				if($ok)
				{
					return $path;
				}

				return $url;
			}
			else
			{
				// we have absolute or relative URI
				if(preg_match('/^.*\.\.\/.*$/',$url))
				{
					// we have relative Parentlinks in URL
					$UpLinks = $this->_calculate_UpLinks($url);
					if($UpLinks!==FALSE)
					{
						// ParentLinks are succesfully resolved
						$url = $UpLinks;
					}
					else
					{
						// ParentLinks are NOT succesfully resolved
						return $url;
					}
				}

				// absolute path, that's OK!
				if(substr($url,0,1)=='/')
				{
					return $this->_compare_url($url,'',$ok);
				}

				// we have a relative path, concatenate it
				$newurl = $this->_checkTrailingSlash($this->_nobacks(dirname($_SERVER['PHP_SELF']))).$url;
				return $this->_compare_url($url,$newurl,$ok);
			}
		}


		function _compare_url($url,$newurl,&$ok)
		{
			// here, we always have an absolute URI
			$comp = $newurl!='' ? $newurl : $url;


			$res = $this->_file_available($comp);
			// if file does not exists
			if($res===FALSE)
			{
				return $url;
			}

			// no special array is defined, so we have to check extension and if file_exists
			if(!$this->compare)
			{
				if($this->rewrite_extern_hostnames && $this->host!='' && strtolower($this->host)!=strtolower('http://'.$_SERVER['HTTP_HOST']))
				{
					// file from extern server: we only check for file-extension and once more, we only can trust in god! ;-)
					foreach($this->valid_php_fileextensions as $ext) {
						//if(substr($res,strlen($res)-strlen($ext)) == $ext) {
							$ok = TRUE;
							return $res;
						//}
					}
					// if we have no match
					return $url;
				} else {
					// file is local and exists, we check extension
					$temp = pathinfo($this->DOCUMENT_ROOT.$res);
					//if(!in_array(strtolower($temp['extension']),$this->valid_php_fileextensions)) {
						//return $url;
					//}

					$ok = TRUE;
					return $res;
				}
			} else {
				$check = array('','.php','index.php','/index.php');
				foreach($check as $k=>$v)
				{
					if(in_array($comp.$v,$this->registered_scripts))
					{
						$ok = TRUE;
						return $comp.$v;
					}
				}
				return $url;
			}
		}



		function _file_available($url)
		{
			// we cannot check on extern servers ;-) (takes to much time) - so we trust in god!
			if($this->rewrite_extern_hostnames && $this->host!='' && strtolower($this->host)!=strtolower('http://'.$_SERVER['HTTP_HOST']))
			{
				return $url;
			}

			// if the url refers to local server we check for file
			$ext = array('','.php','index.php','/index.php');
			foreach($ext as $k=>$v)
			{
				$file = $this->DOCUMENT_ROOT.$url.$v;
				//if(file_exists($file)) return $url.$v;
				if(is_file($file))
				{
					return $url.$v;
				}
			}
			return FALSE;
		}


		function _is_localURL($url)
		{
			// absolute or relative URI
			if(!preg_match('/^http.*$/',$url))
			{
				return TRUE;
			}

			// comparing against local hostnames
			$temp = parse_url($url);
			$url = $temp['scheme'].'://'.$temp['host'];
			if(isset($temp['port']))
			{
				$url .= ':'.$temp['port'];
			}
			foreach($this->hostnames as $v)
			{
				if(strtolower($v)==strtolower($url))
				{
					$this->host = $v;
					return TRUE;
				}
			}
			return FALSE;
		}


		function _calculate_UpLinks($url)
		{
			// make it an absolute url
			if(substr($url,0,1)!='/')
			{
				$url = $this->_checkTrailingSlash($this->_nobacks(dirname($_SERVER['PHP_SELF']))).$url;
			}

			// strip out root and filename
			$file = '';
			if(strrpos($url,'/') != strlen($url))
			{
				$file = basename($url);
			}
			if(substr($url,0,1) == '/')
			{
				$url = substr($url,1);
			}
			$url			  = $this->_nobacks(dirname($url));
			$pathsegments	 = explode('/',$url);
			$uplinks		 = 0;
			foreach($pathsegments as $ps)
			{
				if($ps == "..")
				{
					$uplinks++;
				}
			}
			$k				= count($pathsegments);
			$newurl			= '';

			// No Uplinks in string!
			if($uplinks <= 0)
			{
				return FALSE;
			}

			// Error: Bad Uplink! The given PathString has an Uplink wich refers higher than Root.
			if($pathsegments[0] == "..")
			{
				return FALSE;
			}

			// Error: Bad Uplinks! The given PathString contains to many UpLinks.
			if(($uplinks * 2) > $k)
			{
				return FALSE;
			}


			Do
			{
				// Error: Bad Uplink! The given PathString has Uplinks wich refers higher than Root!
				if($k < 0)
				{
					return FALSE;
				}


				$ups = 0;
				$finished = False;
				Do
				{
					if($pathsegments[$k - $ups -1] == "..")
					{
						$ups++;
					}
					else
					{
						$finished = TRUE;
					}
				} While(!$finished);

				$k = $k - (2 * $ups);
				if($k > -1)
				{
					if(isset($pathsegments[$k-1]) && $pathsegments[$k-1] != "..")
					{
						$newurl = "/".$pathsegments[$k-1].$newurl;
						$k--;
					}
				}
			} while($k > 0);

			return $newurl.'/'.$file;
		}


		function _checkTrailingSlash(&$path)
		{
			if(substr($path,strlen($path)-1,1)!='/')
			{
				$path .= '/';
			}
			return $path;
		}

		function _noTrailingSlash(&$path)
		{
			if(substr($path,strlen($path)-1,1)=='/')
			{
				$path = substr($path,0,strlen($path)-1);
			}
			return $path;
		}

		function _noBacks($PathStr)
		{
			return str_replace("\\","/",$PathStr);
		}




	///////////////////////////////////////////////
	//	EN-/Decode | PRIVATE

	function _encode($txt,$strong=FALSE)
	{
		return $strong ? $this->_strong_encoded($txt) : base64_encode($txt);
	}


	function _decode($txt,$strong=FALSE)
	{
		return $strong ? $this->_strong_decoded($txt) : base64_decode($txt);
	}


	function _strong_encoded($ses)
	{
		// strong_encoding with $alpha_array found at php-manual
		$alpha_array = $this->CodingKey;
		$sesencoded = $ses;
		$num = mt_rand(3,9);
		for($i=1;$i<=$num;$i++)
		{
			$sesencoded = base64_encode($sesencoded);
		}
		$sesencoded = $sesencoded."+".$alpha_array[$num];
		$sesencoded = base64_encode($sesencoded);
		return $sesencoded;
	}

	function _strong_decoded($str)
	{
		$alpha_array = $this->CodingKey;
		$decoded = base64_decode($str);
		list($decoded,$letter) = split("\+",$decoded);
		for($i=0;$i<count($alpha_array);$i++)
		{
			if($alpha_array[$i] == $letter)
			break;
		}
		for($j=1;$j<=$i;$j++)
		{
		  $decoded = base64_decode($decoded);
		}
		return $decoded;
	}


	///////////////////////////////////////////////
	//	ERRORHANDLING | PRIVATE

	function _ErrorHandler($id='')
	{
		switch($id)
		{
			// general:
				case 'no_separator':
					$this->separator = '--';
					return;
					break;

				case 'no_array':
					$this->registered_scripts = array();
					return;
					break;

				case 'no_array_values':
					return;
					break;


			// redirect part:
				case 'no_file':
					header("Location: /404");	// DIRTY FIX! - Don't know, what other makes sense here?
					exit(1);
					break;

				case 'no_path':
					$this->path = '/';		   // ALSO A DIRTY FIX!
					return;
					break;


			default:
				return;
				break;
		}
	}


} // END CLASS

} // END if defined

?>
