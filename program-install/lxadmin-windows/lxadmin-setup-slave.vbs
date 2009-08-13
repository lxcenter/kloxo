''***************************************
''******************************
'' File: LxAdminWindowsInitInstaller.vbs
'' Autor: mahantesh Patil
'' 
'' LxAdmin: Script to Php Downlaod and install LxAdmin
'' 
'' - Installation LxAdmin for windwos wsh & vbs
''**************************************
''***********************************

''constants
''============

Const ZipSrcHttp = "http://download.lxlabs.com/download/windows/7z442.msi"
Const HttpSrcphp = "http://download.lxlabs.com/download/windows/initial-php.zip"
Const HttpSrclxa = "http://download.lxlabs.com/download/program-install.zip"
Const Filename = "php.zip"
const strDest = "c:\\Lxainstall"
Const BasicVersion = 380
Const adTypeBinary = 1
Const adSaveCreateNotExist = 1
Const adSaveCreateOverWrite = 2
Const delay = 5000


''Globale Variables
''===================
Dim WSHShell, ObjFS
Dim tools
Dim HTTP
Dim Stream
Dim HttpSrc


'Initialisierung von WSH.Objekten
''=================================
set WSHShell = WScript.CreateObject("WScript.Shell")
set ObjFS = WScript.CreateObject("Scripting.FileSystemObject")

''Method Invocatation
''=============

Call CreateDir()
call downloadphp(ZipSrcHttp, "7z442.msi")
WSHShell.CurrentDirectory = strDest
WSHShell.Run("msiexec /i 7z442.msi")
Call downloadphp(HttpSrcphp, "initial-php.zip")
Call unziphpFile("initial-php.zip")
If ObjFS.FileExists("program-install.zip") Then
 	ObjFS.deleteFile("program-install.zip")
End If

Call downloadphp(HttpSrclxa, "program-install.zip")
Call unziphpFile("program-install.zip")
Call runPhp()

'' Scriptbeendigung
WScript.Quit


Sub CreateDir()
''Create Home Directory
	sHomeDir = strDest
	If ObjFS.FolderExists(sHomeDir) Then
		MsgBox " Directory '" & sHomeDir & "' already exists ..."
	Else
		Set ObjFolder = objFS.CreateFolder(sHomeDir)
		If (Err.Number <> 0) then
			Msg = "Error creating home directory for user" & vbCrLf & vbCrLf
			sMsg = sMsg & "The error is: " & Err.Description & vbCrLf
			sMsg = sMsg & "The error is: " & Err.Number
			MsgBox sMsg
			Err.Clear
			WScript.Quit
		End If
		Set ObjFolder = Nothing
	End If
End Sub


Sub downloadphp(HttpSrc, sFile)

'' Download via WScript.Object
WSHShell.CurrentDirectory = strDest
set HTTP = WScript.CreateObject("Microsoft.XMLHTTP")
MsgBox "Downloading " & sFile & ""
HTTP.open "GET", HttpSrc , False
HTTP.send
set Stream = createobject("adodb.stream")
Stream.type = adTypeBinary
Stream.open
Stream.write HTTP.responseBody
Stream.savetofile sFile , adSaveCreateOverWrite
set Stream = nothing
set HTTP = nothing

'' Scriptverzögerung
'WScript.Sleep delay
MsgBox " Download " & sFile & " is Done"
End Sub

Sub unziphpFile(sFile)
'Unzip the php file
 
WSHShell.CurrentDirectory= strDest
WSHShell.Run ("c:\Progra~1\7-zip\7z.exe x -y " & sFile),8,1
MsgBox " Unzip " & sFile & " Done"
End Sub


Sub runPhp()
'Running main Lxadmin installation File
 WSHShell.Run ("C:\Lxainstall\initial-php\php.exe  C:\Lxainstall\program-install\lxadmin-windows\wininstall.php"),4,1
 MsgBox " Running Done"
End Sub

