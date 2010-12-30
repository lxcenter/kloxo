<?php 


class cron__windows extends lxDriverClass {

	//class cron__windows 


function getweekdayNum()
{
	$list = array("monday", "tuesday","wednsday","thursday","friday","saturday","sunday");
	$days=("tuesday,friday,wednsday,thursday,saturday,sunday,monday");
	$weekdays=$this->main->weekday;

	$weekdays=$this->main->weekday;	
	$acount=$substr_count($weekdays,",");
	$weekdays1=$explode(',',$weekdays);

	if (array_search("--all--", $weekdays1)!== FALSE)
	{
		$ret= 1 | 2 | 4 | 8 | 16 | 32 | 64 ;

	}
	else
	{

		$ret=3;

		for($i = 0; $i <= $acount; $i++)
		{
			$test=array_search($weekdays1[$i],$list);
			print_r($test. "\n");
			{
				$j= pow(2,$test);

				if($ret==3)
				{
					$ret=$j;

				}
				else
				{
					$ret=$ret | $j;
				}


			}
		}
	}
	return $ret;
}


function getDdate()
{

	$test=" ";
	$Dcount=" ";
	$Date=" ";
	$Date1=" ";
	$Date= $this->main->ddate;

	$Dcount=$substr_count($Date,",");
	$Date1 =$explode(',', $Date);

	$test=array_search("--all--",$Date1);
	if($test) {
		$ret=" " ;
			print("mahantesh");
	} else {

		$ret=3;
		for($i = 0; $i <= $Dcount; $i++) {
			$test=($Date1[$i]);
			$test=$test- 1;
			print_r($test. "\n");
				$j= pow(2,$test);

				if($ret==3) {
					$ret=$j;

				} else {
					$ret=$ret | $j;
				}


		}
	}

	return $ret;
}



function convertArray($val)
{
	$res=" ";
	if ($this->main->checkifAll($val[0])) {
		$res = "*";

	} //else //{
			//$res = "$v";

	//}

		
	
	return $res;
}

function dbactionAdd()
{
	$montharray = array("", "JAN", "FEB" , "MAR" , "APR" , "MAY" , "JUN" , "JUL" , "AUG" , "SEP" , "OCT" , "NOV" , "DEC");

	$weekdayarray = array("", "MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN");


	$ret=" ";
    $strDdate=" ";

	//dprintr($this->main->command);
	$month = $this->main->month[0];
	$ddate = $this->main->ddate[0];

	if ($month === '--all--') {
		$rmonth = "*";
	} else {
		$rmonth = $montharray[$month];
	}

	$username = $this->main->__var_user_list['nname'];
	$password = $this->main->__var_user_list['realpass'];

	if ($username === null)
		$username="system";
	
//	dprint("Userpass: " . $username . " " .  $password . "\n");

	$strCmd=$this->main->command;
	if($this->main->weekday==" ") {
		$strweekday="";
	} else {
	}
/*
	print("<hr>User name: ");
	dprintr($this->main->username);
	print("<br>Password: ");
//	dprintr($this->main->password);
	print("<hr>task name: ");
	dprintr($this->main->nname);
	print("<br>argument: ");
	dprintr($this->main->argument);
	print("<br>hour: ");
	dprintr($this->main->hour);
	print("<br>ddate: ");
	dprintr($this->main->ddate);
	print("<br>weekday: ");
	dprintr($this->main->weekday);
	print("<br>month: ");
	dprintr($this->main->month);
	print("<hr>");
*/
	$taskname = $this->getTaskName();
	$app = explode("_", substr($this->main->nname, 16));
	$application=$app[1]." ".$this->main->argument;

	print("<br>task name:  $taskname <br>application:  $application <hr> ");

	$all=1;

	if(strcmp($this->main->hour[0] , "--all--"))
	{
		$hour=$this->main->hour[0];
		$exe="schtasks /create /sc hourly /mo $hour /tn $taskname /tr $application /ru $username /rp $password";
		exec("$exe");
		$all=0;
	}

	if(strcmp($this->main->ddate[0] , "--all--"))
	{
		$date=$this->main->ddate[0];
		$exe="schtasks /create /sc monthly /d $date /tn $taskname /tr $application /ru $username /rp $password";
		exec("$exe");
		$all=0;
	} 

	if(strcmp($this->main->weekday[0] , "--all--"))
	{
		$weekday=$this->main->weekday[0];
		$rweekday = $weekdayarray[$weekday];
		$exe="schtasks /create /sc weekly /d $rweekday /tn $taskname /tr $application /ru $username /rp $password";
		exec("$exe");
		$all=0;
	}
	
	if(strcmp($this->main->month[0] , "--all--"))
	{
		$month=$this->main->month[0];
		$exe="schtasks /create /sc monthly /mo $month /tn $taskname /tr $application /ru $username /rp $password";
		exec("$exe");
		$all=0;
	}

	if ($all)
	{
		$exe="schtasks /create /sc hourly /mo 1 /tn $taskname /tr $application /ru $username /rp $password";
		exec("$exe");
	}
	
//	print("<hr>$exe <hr> ");
	print("Job sucessfully Created\n");
}


function getTaskName()
{
	$taskname=substr($this->main->nname, 22);
	$taskname=fix_nname_to_be_variable($taskname);
	return $taskname;
}


function dbactionDelete()
{

	$taskname = $this->getTaskName();
	dprint("<hr> $taskname <hr> ");

	$exe="schtasks /delete /tn ".$taskname." /f";
	dprint("$exe <hr> ");
	exec("$exe");
	dprint("Job is deleted");
}
}
    
