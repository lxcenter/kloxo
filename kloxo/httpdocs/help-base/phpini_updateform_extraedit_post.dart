[b] File uploads [/b] : Whether to allow HTTP file uploads.

[b] upload_tmp_dir [/b] : Temporary directory for HTTP uploaded files (will use system default if not specified).

[b] upload_max_filesize [/b] : Maximum allowed size for uploaded files.

[/b] mysql.allow_persistent [/b] :  Allow or prevent persistent links.

[b] Log Errors [/b] : This directive complements the above one.  Any errors that occur during the execution of your script will be logged (typically, to your server's error log, but can be configured in several ways).  Along with setting display_errors to off, this setup gives you the ability to fully understand what may have gone wrong, without exposing any sensitive information to remote users.

[b] output_buffering [/b] :  Enabling output buffering typically results in less writes, and sometimes less packets sent on the wire, which can often lead to better performance. The gain this directive actually yields greatly depends on which Web server you're working with, and what kind of scripts you're using.  Output buffering allows you to send header lines (including cookies) even after you send body content, at the price of slowing PHP's output layer a bit.  You can enable output buffering during runtime by calling the output buffering functions.  You can also enable output buffering for all files by setting this directive to On.  If you wish to limit the size of the buffer to a certain size - you can use a maximum number of bytes instead of 'On', as a value for this directive (e.g., output_buffering=4096).  

[b] register_argc_argv [/b]  : Disables registration of the somewhat redundant $argv and $argc global variables.

[b] magic_quotes_gpc[/b]  : Input data is no longer escaped with slashes so that it can be sent into SQL databases without further manipulation.  Instead, you should use the function addslashes() on each input element you wish to send to a database. 

[b] magic_quotes_runtime [/b] :  Magic quotes for runtime-generated data, e.g. data from SQL, from exec(), etc.


[b] post_max_size [/b]  Maximum size of POST data that PHP will accept.

[b] enable_dl [/b]  : Whether or not to enable the dl() function.  The dl() function does NOT work properly in multithreaded servers, such as IIS or Zeus, and is automatically disabled on them.  

[b] cgi.force_redirect [/b]  : cgi.force_redirect is necessary to provide security running PHP as a CGI under most web servers.  Left undefined, PHP turns this on by default.  You can turn it off here AT YOUR OWN RISK **You CAN safely turn this off for IIS, in fact, you MUST.

[b] mysql.allow_persistent [/b] : Allow or prevent persistent links.

[b] disable_functions [/b] : This directive allows you to disable certain functions for security reasons. It receives a comma-delimited list of function names. This directive is  *NOT* affected by whether Safe Mode is turned On or Off.  

[b] max_execution_time [/b]	:  Maximum execution time of each script, in seconds.

[b] max_input_time [/b]	:  Maximum amount of time each script may spend parsing request data.

[b]	memory_limit [/b]	: Maximum amount of memory a script may consume (8MB).

[b] allow_url_fopen [/b] : Whether to allow the treatment of URLs (like http:// or ftp://) as files.

