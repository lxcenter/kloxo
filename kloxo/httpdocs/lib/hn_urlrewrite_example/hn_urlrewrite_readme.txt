PHP-Class:  hn_urlRewrite
Author:     Horst Nogajski
Contact:    h.nogajski@web.de
Version:    3.1
Released:   19-Sep-2004

----------------------------------


Content of this readme:

   Preface
1) What's new in Version 2.x / 3.x
2) What the class does
3) What the class needs
4) How to configure the webserver
5) How to rewrite your URLs
6) How the redirect script works
7) Encoding
8) Filelist, examples
9) ToDo's


----------------------------------


   Preface

My first intention for writing this class (version 1), was the wish for having
a little tool which allows easy rewriting some (defined) URL's, so that they are
more friendly for search engines and a bit unfriendlier for hackers.

When rewriting it to version 2, I've thought that it would be more comfortable
to let the class rewrite all (php) URL's without to have them register into an array.

But this causes more trouble than good and comfortable results. It goes slightly
a bit out of hand. (see Apaches htaccess protection in point 1) )

Therefore the next version, 3.2, will be configured per default to rewrite only
registered URLs.

But first I want to think carefully about some points:
- adding rewrite for other filetypes than php? (e.g. images: gif, jpeg, png)
- adding more tags to RegExp-SearchPattern: <link|<table|<tr|<td?
- also (optionally) encoding the path in URLs, not only the params?
- adding an extension which scans the whole side and parse all htaccess-files
  to automatically avoid the security hole? And then, what's about directory-directives
  written in apaches httpd.conf? Can we fetch them?
- when rewriting all URL's, including images etc., every request forces a call to
  the rewrite-script. How much is the slow down?

If you have any suggestion to this or other points of HN URL Rewrite, please mail me.

---


1) What's new in Version 2.x / 3.x

ATTENTION! SECURITY HOLE IN version 2.x & 3.0:
I've experienced that, if one use rewriting all URLs and there is also
an URL which refers to a htaccess-password protected script, the class
bypasses Apaches protection!

So I've added a rewrite_protected_scripts array to configuration.
This is used like the registered_scripts array but exclude its scripts from rewriting.

If you use the registered_scripts array you don't need it.

BUT if you use the DEFAULT MODE (rewriting all URLs) and you _HAVE_
HTACCESS-PASSWORD-PROTECTED files, YOU MUST REGISTER THESE SCRIPTS IN THE
REWRITE_PROTECTED_SCRIPTS ARRAY!

-

Since 3.0 it uses a new RegExp-Searchpattern which allows to rewrite all tags
at once. It's much faster now!

-

Per default, the class rewrites all URLs which refers to the own serverhost
and where the extension is 'php', if no array with registered scriptnames is defined.

It rewrites the URL links (<a href... </a>),
the IMG links (<img src),
and NEW in 3.0: the AREA links in image maps (<map> <area href... </map>),

-

The links to rewrite can either relative URIs, absolute URIs, or complete URLs
with scheme, hostname, path. Relative parent-links (../) in relative and absolute
URIs are also resolved (since 2.0)!

-

Per default it rewrites all tags, but optionally you can disable each of the
tag-groups in configuration with new boolean config-vars: nourls, nomaps, noimgs.

Therefore in 3.0 the three string-rewriting methods from 2.0 has changed to only one:
	string-rewrite($string)
It return the rewritten string, but do not store it in classes scope.

-

The class detect complete URLs with and without 'www.' in hostname.
Optionally you can define an array with additional hostnames e.g. if you have
the same site reachable by different top level domains (.com + .de)

-

Since 2.0, registered scripts are stored in array as values, not as keys anymore!
The param names are not stored in array. They will encoded too.
So their count and order doesn't matter anymore.

-

Errorhandling is done! (3.0)
Now it fixes the minor Errors to guarantee users can reach pages, e.g. at the price
of not rewritten URLs. Have a look to the function at the end of the class.

-

(3.0) Also fixed some minor glitches:
- ? removed from QUERY_STRING.
- now it uses is_file() instead of file_exists() by resolving pathes and checking
  if files are available on local system. (file_exists return TRUE on directories also).
  Now it resolves 100% of all pathnames, ;-)
- Have cleaned up syntax for better consistency.


---


2) What the class does

This class can emulate a page request URL rewriting and redirection
specified within the same PHP script.

It can a PHP script handle a request with an URI like this

    /home/name.php?p1=a&p2=b&xy=123

when it is requested a page with an URI like this:

    /goto/home/name/xyz/NQw/NQi/RwSy=/index.html

or, optionally with strong encoding, to with an URI like this

    /goto/home/name/xyz/Sm9obZjhsz6s/amBzb29zdC5jb20=/MTIU2NzgY1g==/index.html

The request redirection emulation is done by rewriting some request variables
that contain request paths, like SCRIPT_FILENAME, SCRIPT_NAME, PHP_SELF,
REQUEST_URI, PATH_TRANSLATED, and QUERY_STRING.

It can parse and change the output of the current page script to rewrite
the URLs links (also in image maps) or images in the page with absolute URLs,
relative URIs and URLs that contain scheme and host (http://www.somehost.com)

There are two methods to do rewriting:
First is by using PHP buffering support. Optionally, the class
can compress the processed page output to serve the page in less time if
the user browser supports compression.
Second is, if you cannot (or don't) want use the buffering support,
using a string-rewrite method.

---


3) What the class needs:

Per default, all URL links with file-ending .php which refers
to the own server will be rewritten.

Optionally you can register scriptnames in an array, if you
want rewrite only specific URL's. The URL must be defined in
absolute style: '/dir1/subdir/filename.php', without scheme and host!

---


4) How to configure the webserver

In the root directory you need a htaccess file with an entry like:

 	<Files goto>
 		ForceType application/x-httpd-php
  	</Files>

where 'goto' is the name of the rewrite-script, which resides also
in the root dir. In class configuration you have to register this
scriptname also in constant named: URL_REWRITE_SCRIPT, but here
you must add the leading slash: define('URL_REWRITE_SCRIPT','/got');

This forces apache to call the rewriter script for every request,
beginning with /goto.

---


5) How to rewrite your URLs

After a class instance is build,

	$rewrite =&  new hn_rewrite();

you can use the buffer-methods to catch a whole page at once:

	$rewrite->buffer_start();

	... all your html output here ...

	$rewrite->buffer_end();
	$rewrite->send_page();      // optionally it can gzip compress the output, when passing TRUE to the function!
or: $string = $rewrite->page;   // if you don't want direct output

OR

you can use the string rewrite method, if you allready have the
html-output present as string:

	$string = $rewrite->string_rewrite($string);

The method return the rewritten string, but do not store it
in classes scope, so you cannot use the send_page()-method here.
(If you want use it, you can do with an extra step:
	$rewrite->page = $string;
	$rewrite->send_page(TRUE);      // optionally it can gzip compress the output, when passing TRUE to the function!
 But keep in mind that the string must contain the complete HTML-page,
 with doctype, html, header and body tags.)

---


6) How the redirect script works

The redirect script calls one method of the class:

		$rewrite->redirect($param);

		[ $param is the pathinfo from the request as it comes from apache,
         (stored in $_SERVER['PATH_INFO']) ]

The request redirection emulation is done by rewriting some request variables
that contain request paths, like SCRIPT_FILENAME, SCRIPT_NAME, PHP_SELF,
REQUEST_URI, PATH_TRANSLATED, and QUERY_STRING.

Also this method writes the decoded params to the superglobal array $_GET,
and changes the current working directory to the requested scripts path.

---


7) Encoding

In standard use, the class base64_encodes all params, but optionally it
can use a strong encoding which makes decoding for hackers impossible.
It also can use the params in plain text, but this is not recommended!
Because then you have to take care about that there are no slashes (/)
in your param names or param values! This would fool the redirection script,
so that it cannot resolve the correct path!

---


8) Filelist

hn_urlrewrite.class.php
goto
example0.php
hn_urlrewrite_example/example1.php
hn_urlrewrite_example/example2.php
hn_urlrewrite_example/sub1/example3.php
hn_urlrewrite_readme.txt

Copy all (example)-files _with_ specified subdirs to your
webservers root directory and configure the webserver as
described above!

---


9) ToDo's

- See Preface!

---


Enjoy!
