<?php

$realpath = dirname(__FILE__);
include_once "$realpath/windows_common.php";
include_once "$realpath/../install_common.php";


Lxunins();
function Lxunins()
{
	global $argv;
	$opt = parse_opt($argv);
	$stdin = $opt['package-type'];
	switch($stdin)
	{
				print("uninstalling perl\n\n");
				$wsh=new COM("WScript.Shell");
				$wsh->run("msiexec /uninstall ActivePerl-5.8.7.815-MSWin32-x86-211909.msi /qn",1,True);
				$wsf=new COM("Scripting.FileSystemObject");
				$wsf->DeleteFolder("C:\hhh\Perl");
				break;
			}
		case "python":
			{
				print("uninstalling python\n\n");
				$wsh=new COM("WScript.Shell");
				$wsh->run("msiexec /uninstall ActivePython-2.4.2.10-win32-x86.msi /qn",1,True);
				$wsf= new COM("Scripting.FileSystemObject");
				$wsf->DeleteFolder("c:\hhh\python");
				break;
			}
		case "php":
			{
				print("uninstalling php\n\n");
				$wsf=new COM("Scripting.FileSystemObject");
				$wsf->DeleteFolder("C:\hhh\php");
				print("done\n");
				break;
			}
		case "lxhttpd":
			{
				print("uninstalling lxhttpd\n\n");
				$wsf=new COM("Scripting.FileSystemObject");
				$wsf->DeleteFolder("c:\hhh\lxhttpd");
				break;
			}
		case "msde":
			{
				print("uninstalling msde\n\n");
				$wsh=new COM("WScript.Shell");
				$wsh->CurrentDirectory="c:\hhh\MSDERelA";
				$wsh->run("setup.exe \x \qn",0,1);
				$wsh->CurrentDirectory="c:";
				$wsf=new COM("Scripting.FileSystemObject");
				$wsf->DeleteFolder("C:\hhh\MSDERelA");
				break;
			}
		default: 
			{
				print("there is no package by this name\n\n");
			}
	}
}

