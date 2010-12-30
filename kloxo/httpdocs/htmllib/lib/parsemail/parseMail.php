<?php
    // parse an incoming mail
    // Version 0.5, 2005/03/16
    // Copyright (c) Frank Rust, TU Braunschweig (f.rust@tu-bs.de)
    //
    // This code is free software; you can redistribute it and/or modify
    // it under the terms of the GNU General Public License as published by
    // the Free Software Foundation; either version 2 of the License, or
    // (at your option) any later version.
    // 
    // This code is distributed in the hope that it will be useful,
    // but WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    // GNU General Public License for more details.
    // 
    // Since this is a very short Program the GNU General Public License
    // is not included. Please find it on the website of the Open Software
    // Foundation at
    //     http://www.fsf.org/licensing/licenses/lgpl.txt
    // or write to the Free Software Foundation, Inc., 59 Temple Place, 
    // Suite 330, Boston, MA  02111-1307  USA
    
    class parseMail {
		var $from="";
		var $to="";
		var $subject="";
		var $received="";
		var $date="";
		var $message_id="";
		var $content_type="";
		var $part =array();
		
		// decode a mail header
		function parseMail($text="") {
			$start=0;
			$lastheader="";
			$message_id = null;

			while (true) {
				$end=strpos($text,"\n",$start);
				$line=substr($text,$start,$end-$start);
				$start=$end+1;
				if ($line=="") break; // end of headers!
				if (substr($line,0,1)=="\t") {
					$$last.="\n".$line;
				}
				if (preg_match("/^(From:)\s*(.*)$/",$line,$matches)) {
					$last="from";
					$$last=$matches[2];
				}
				if (preg_match("/^(Received:)\s*(.*)$/",$line,$matches)) {
					$last="received";
					$$last=$matches[2];
				}
				if (preg_match("/^(To:)\s*(.*)$/",$line,$matches)) {
					$last="to";
					$$last=$matches[2];
				}
				if (preg_match("/^(Subject:)\s*(.*)$/",$line,$matches)) {
					$last="subject";
					$$last=$matches[2];
				}
				if (preg_match("/^(Date:)\s*(.*)$/",$line,$matches)) {
					$last="date";
					$$last=$matches[2];
				}
				if (preg_match("/^(Content-Type:)\s*(.*)$/",$line,$matches)) {
					$last="content_type";
					$$last=$matches[2];
				}
				if (preg_match("/^(Message-Id:)\s*(.*)$/",$line,$matches)) {
					$last="message_id";
					$$last=$matches[2];
				}
			}
			$this->from=$from;
			$this->received=$received;
			$this->to=$to;
			$this->subject=$subject;
			$this->date=$date;
			$this->content_type=$content_type;
			$this->message_id=$message_id;
			
			if (preg_match("/^multipart\/mixed;/",$content_type)) {
				$b=strpos($content_type,"boundary=");
				$boundary=substr($content_type,$b+strlen("boundary="));
				$boundary=substr($boundary,1,strlen($boundary)-2);
				$this->multipartSplit($boundary,substr($text,$start));
				
			} else {
				$this->part[0]['Content-Type']=$content_type;
				$this->part[0]['content']=substr($text,$start);
			}
		}
		// decode a multipart header 
		function multipartHeaders($partid,$mailbody) {
			$text=substr($mailbody,$this->part[$partid]['start'],
			             $this->part[$partid]['ende']-$this->part[$partid]['start']);

			$start=0;
			$lastheader="";
			while (true) {
				$end=strpos($text,"\n",$start);
				$line=substr($text,$start,$end-$start);
				$start=$end+1;
				if ($line=="") break; // end of headers!
				if (substr($line,0,1)=="\t") {
					$$last.="\n".$line;
				}
				if (preg_match("/^(Content-Type:)\s*(.*)$/",$line,$matches)) {
					$last="c_t";
					$$last=$matches[2];
				}
				if (preg_match("/^(Content-Transfer-Encoding:)\s*(.*)$/",$line,$matches)) {
					$last="c_t_e";
					$$last=$matches[2];
				}
				if (preg_match("/^(Content-Description:)\s*(.*)$/",$line,$matches)) {
					$last="c_desc";
					$$last=$matches[2];
				}
				if (preg_match("/^(Content-Disposition:)\s*(.*)$/",$line,$matches)) {
					$last="c_disp";
					$$last=$matches[2];
				}
			}
			if ($c_t_e=="base64") {
				$this->part[$partid]['content']=base64_decode(substr($text,$start));
				$c_t_e="8bit";
			} else {
				$this->part[$partid]['content']=substr($text,$start);	
			}
			$this->part[$partid]['Content-Type']=$c_t;
			$this->part[$partid]['Content-Transfer-Encoding']=$c_t_e;
			$this->part[$partid]['Content-Description']=$c_desc;
			$this->part[$partid]['Content-Disposition']=$c_disp;
			unset($this->part[$partid]['start']);
			unset($this->part[$partid]['ende']);
		}
		// we have a multipart message body
        // split the parts 
		function multipartSplit($boundary,$text) {
			$start=0;
			$b_len=strlen("--".$boundary);
			$partcount=0;
			while (true) { // should have an emergency exit...
				$end=strpos($text,"--".$boundary,$start);
				if (substr($text,$end+$b_len,1)=="\n") {
					// '\n' => part boundary
					$this->part[$partcount]['start']=$end+$b_len+1;
					if ($partcount) { 
						$this->part[$partcount-1]['ende']=$end-1;
						$this->multipartHeaders($partcount-1,$text);
					}
					$start=$end+$b_len+1;
					$partcount++;
				} else {
					// '--' => end boundary
					$this->part[$partcount-1]['ende']=$end-1;				
					$this->multipartHeaders($partcount-1,$text);
					break;
				}
			}	
		}
    } 
  
?>
