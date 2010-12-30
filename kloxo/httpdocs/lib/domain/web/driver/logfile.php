<?php 
class ApacheLogRegex {

	// The Apache CustomLog string. e.g:
	// %a %t \"%r\" %s %b %T \"%{Referer}i\"
	// %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"
	private $_format;

	// The log format parsed into a regex
	private $_regex_string;

	// The field names of parse log line array.
	private $_regex_fields;

	// Number of fields in each result row.
	private $_num_fields;

	/*
	* __construct(string format)
	*
	* Returns a ApacheLogRegex object that can parse a line from an
	* Apache logfile that was written to with the $format string.
	* The format string is the CustomLog string from the httpd.conf
	* file. Returns null on error.
	*/
	public function __construct() {

		$format = '%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"';
		if(gettype($format) !== 'string') {
			trigger_error( __CLASS__ . '::' . __FUNCTION__    . '(): '
			. 'Paramater #1 expected to be a string but found '
			. gettype($format)
			, E_USER_WARNING);
			return null;
		}
		elseif(strlen(trim($format)) == 0) {
			trigger_error( __CLASS__ . '::' . __FUNCTION__    . '(): '
			. 'Paramater #1 is empty'
			, E_USER_WARNING);
			return null;
		}

		$this->_format = $format;

		$this->_regex_string = '';

		$this->_regex_fields = array();

		$this->_parse_format();

		$this->_num_fields = count($this->_regex_fields);

		if($this->_num_fields == 0) {
			trigger_error( __CLASS__ . '::' . __FUNCTION__    . '(): '
			. 'Unable to parse ANY fields from Log format'
			, E_USER_WARNING);
			return null;
		}

	} // end __construct()

	/*
	* private _parse_format(void)
	*
	* Parse the object $_format variable, which contains the Apache
	* CustomLog string, into a regex that will match a log line
	* created with that CustomLog.
	*/
	private function _parse_format() {

		$this->_format = trim($this->_format);
		$this->_format = preg_replace(
			array('/[ \t]+/', '/^ /', '/ $/'),
			array(' ', '', ''),
			$this->_format
		);
		$regex_elements = array();

		foreach(explode(' ', $this->_format) as $element)
		{
			$quotes = preg_match('/^\\\"/', $element) ? true : false;

			if($quotes)
			{
				$element = preg_replace(
					array('/^\\\"/', '/\\\"$/'),
					'',
					$element
				);
			}

			$this->_regex_fields[]=$this->rename_this_name($element);

			if($quotes)
			{
				if($element == '%r'
				or preg_match('/{Referer}/', $element)
				or preg_match('/{User-Agent}/', $element))
				{
					$x = '\"([^\"\\\\]*(?:\\\\.[^\"\\\\]*)*)\"';
				}
				else
				{
					$x = '\"([^\"]*)\"';
				}
			}
			elseif ( preg_match('/^%.*t$/', $element) )
			{
				$x = '(\[[^\]]+\])';
			}
			else
			{
				$x = '(\S*)';
			}

			$regex_elements[] = $x;
		}

		$this->_regex_string =
		'/^' . implode(' ', $regex_elements ) . '$/';

	} // end function _parse_format()

	/*
	* array parse(string line)
	*
	* Given a $line from an Apache logfile it will parse the line
	* and return a associative array (hash) of all the elements of
	* the line indexed by their format. If the line cannot be parsed
	* then NULL is returned.
	*
	* NOTE: Not entirely happy with the performance of this method.
	*         Takes ~30 secs to process 200k lines.
	*/
	public function parse($line) {

		if(preg_match($this->_regex_string, $line, $matches) !== 1)
			return null;

		$out = array();
		for($n = 0; $n < $this->_num_fields; ++$n)
			$out[$this->_regex_fields[$n]] = $matches[$n + 1];

		return $out;
	}

	/*
	* array parse(string line)
	*
	* Same as parse() but returns a numberic array instead of a hash
	* (associative array) which makes it slightly fast.
	*/
	public function parse_n($line) {
		if(preg_match($this->_regex_string, $line, $matches) !== 1)
			return null;
		return array_slice($matches, 1);
	}

	/*
	* array names()
	*
	* Returns a list of field names that were extracted from the
	* data. Such as '%a', '%t' and '%r' from the above example.
	*/
	public function names() {
		return $this->_regex_fields;
	}

	/*
	* string regex()
	*
	* Returns a copy of the regex that will be used to parse the
	* log file.
	*/
	public function regex() {
		return $this->_regex_string;
	}

	/*
	* string rename_this_name(string field)
	*
	* This method renames the keys that will be used to in returned
	* hash. The initial field name is passed in and the method
	* returns a new one.
	*
	* Returns the origional name if there is a problem.
	*/
	public function rename_this_name($field) {

		static $orig_val_default = array('s', 'U', 'T', 'D', 'r');

		// Names appened with 'X'  are non-CLF (Common Log Format) or
		   // non-canonical. Comments show apache versions.
		static $trans_names = array (
			'%'    => '',
			'a' => 'Remote-IP',
			'A' => 'Local-IP',
			'B' => 'Bytes-Sent-X',
			'b' => 'Bytes-Sent',
			'c' => 'Connection-Status', // <= 1.3
			'C' => 'Cookie', // >= 2.0
			'D'    => 'Time-Taken-MS',
			'e' => 'Env-Var',
			'f' => 'Filename',
			'h' => 'Remote-Host',
			'H' => 'Request-Protocol',
			'i' => 'Request-Header',
			'I'    => 'Bytes-Recieved', // >= 2.0
			'l' => 'Remote-Logname',
			'm' => 'Request-Method',
			'n' => 'Note',
			'o' => 'Reply-Header',
			'O'    => 'Bytes-Sent', // >= 2.0
			'p' => 'Port',
			'P' => 'Process-Id', // {format} >= 2.0
			'q' => 'Query-String',
			'r' => 'Request',
			's' => 'Status',
			't' => 'Time',
			'T' => 'Time-Taken-S',
			'u'    => 'Remote-User',
			'U' => 'Request-Path',
			'v' => 'Server-Name',
			'V' => 'Server-Name-X',
			'X'    => 'Connection-Status', // >= 2.0
		);

		foreach($trans_names as $find => $name) {
			$pattern =
			"/^%([!\d,]+)*([<>])?(?:\\{([^\\}]*)\\})?$find$/";

			if(preg_match($pattern, $field, $matches)) {

				if(!empty($matches[2])
					and $matches[2] === '<'
					and !in_array($find, $orig_val_default, true)
				)
				$chooser = "Origional-";
				elseif (!empty($matches[2])
					and $matches[2] === '>'
					and in_array($find, $orig_val_default, true)
				)
				$chooser = "Final-";
				else $chooser = '';

				$name = "{$chooser}"
				.(!empty($matches[3]) ? "$matches[3]" : $name)
				.(!empty($matches[1]) ? "($matches[1])" : '');

				break;
			}

		}
		if(empty($name))
			return $field;

		return $name;

	} // end rename_this_name()

	/*
	* int logtime_to_timestamp(string TIME)
	*
	* Take a standard Appache log time string and converts it to
	* unix timestamp.
	*/
public function logtime_to_timestamp($time)
{

	static $months = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

	$time_format = '/\[([\d]{2})\/([\w]{3})\/([\d]{4}):([\d]{2}):([\d]{2}):([\d]{2}) ([\+\-])([\d]{2})([\d]{2})\]/';

	$m = array();    //matches
	if(!preg_match($time_format, $time, $m) || count($m) != 10)
		return null;

	return @ mktime( $m[4], $m[5], $m[6], 1 + array_search($m[2], $months), $m[1], $m[3]) ;
	// This shoudl be the other way round. If the Difference is +, then to get the greeenwich time, you have to reduce the amount. This guy is fucking braindamaged.

		//+ ($m[8] * 3600 + $m[9] * 60) * ($m[7] == '-' ? -1 : 1);

} // end method logtime_to_timestamp

} // end class ApacheLogRegex
