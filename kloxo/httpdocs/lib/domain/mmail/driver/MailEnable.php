<?php
/*
//////////////////////////////////////////////////////////////////////////////////////
Info & Disclaimer:
Original Code in ASP 3.0 By: Mail Enable Pty. Ltd.
phpMailEnable Class By: Dan L. 2004
NO WARRANTY - USE AT YOUR OWN RISK - AUTHOR ASSUMES NO LIABILITY - YOU ACCEPT ALL RISK
//////////////////////////////////////////////////////////////////////////////////////
*/

class MailEnable {

static Function ConvertToMESMTPAddress($MEAddress)
{
	if (!csb($MEAddress, "[SMTP:")) {
		return "[SMTP:"  . $MEAddress . "]";
	} else {
		return $MEAddress;
	}
}
}

class phpMailEnable {
	public $sMsg;
	public $sErr;
	// Update the MailRoot path - YOU MUST CHECK THIS
	public $sMailRoot = "C:\\Program Files\\Mail Enable\\Postoffices";
	public $PostOffice;
	public $MailBox;
	public $Password;
	public $lResult;
	public $sTemp;
	// NOTE: The createMailbox function has NOT been tested with the RedirectAddress value set!!!
	public $RedirectAddress;
	
	/*
	VERSION: 1.01
	DATE UPDATED: 04/11/2004
	DATE CREATED: 04/11/2004
	LOG:
	Dan worked on this class on 04/11/2004
	*** PLease write your log entry here ***
	
	*/
	
	function mkdir_safe($DirName) {
		/*
		This function does some checks to prevent issues
		on non-ntfs & non-fat file systems - this is
		probably not a big deal with Mail Enable
		(because it only runs on windows)
		*/
		if (file_exists($DirName) && !is_dir($DirName)) {
			return false;
		} else {
			if (mkdir($DirName)) {
				return true;
			} else {
				return false;
			}
		}
		
	}
	
	function createMailbox($tmpPostOffice, $tmpMailBox, $tmpPassword, $tmpRedirectAddress = "") {
		/*
		Call this function to create a mailbox
		IMPORTANT: tmpPostOffice MUST BE EQUAL TO A POSTOFFICE THAT ALREADY EXISTS
		(this function will not create the Postoffice for you)
		*/
		//Set the form variables
		$this->PostOffice = $tmpPostOffice;
		$this->MailBox = $tmpMailBox;
		$this->Password = $tmpPassword;
		$this->RedirectAddress = $tmpRedirectAddress;

		// START: Error Checking Code 
		if (strlen($this->PostOffice)==0) {
			//We have an error
			$this->sErr = "You must supply a PostOffice";
		}
	
		if (strlen($this->MailBox)==0) {
			//We have an error
			$this->sErr = "You must supply a MailBox";
		}
	
		if (strlen($this->Password)==0) {
			//We have an error
			$this->sErr = "You must supply a Password";
		}
		// END: Error Checking Code 
		if (strlen($this->sErr) > 0) {return $this->sErr;}
		
		//Continue - Create Account/Mailbox
		if (strlen($this->sErr) < 1) {
			// *** Start adding MailBox here ***
			//Create the MailEnable COM Objects
			try {
				$oMailBox = new COM("MEAOPO.MailBox");
			} catch (exception $e) {
				return "no mailserver";
			}
				
			$oAUTHLogin = new COM("MEAOAU.Login");
    
			$oMailBox->PostOffice = $this->PostOffice;
			$oMailBox->MailBox = $this->MailBox;
			$oMailBox->Limit = -1;
			$oMailBox->RedirectAddress = $this->RedirectAddress;
			$oMailBox->RedirectStatus = 0;
			$oMailBox->Status = 1;
			try {
				$lResult = $oMailBox->AddMailBox();
			} catch (exception $e) {
				return;
			}
			// Free up system resources
			unset($oMailBox);
			
			if ($lResult == 0) {
				$sErr = "<BR>Could not add MailBox. Make sure it doesn't already exist.";
				return $sErr;
			}
		
			//when we create a MailBox we also create a pop logon
			$oAUTHLogin->Account = $this->PostOffice;
			$oAUTHLogin->Description = "";
			$oAUTHLogin->Password = $this->Password;
			$oAUTHLogin->Rights = "USER";
			$oAUTHLogin->Status = 1;
			$oAUTHLogin->UserName = $this->MailBox . "@" . $this->PostOffice;
			
			$lResult = $oAUTHLogin->AddLogin();
			
			// Free up system resources
			unset($oAUTHLogin);
			
			if ($lResult == 0) {
				$sErr = "<BR>Could not add Password.";
				return $sErr;
			}

			// is the post office there?
			if (!is_dir("$this->sMailRoot\\$this->PostOffice")) {
				$this->mkdir_safe("$this->sMailRoot\\$this->PostOffice");
			}
	
			//is the mailroot there?
			if (!is_dir("$this->sMailRoot\\$this->PostOffice\\mailroot")) {
				$this->mkdir_safe("$this->sMailRoot\\$this->PostOffice\\mailroot");
			}
	
			if (!is_dir("$this->sMailRoot\\$this->PostOffice\\mailroot\\$this->MailBox")) {
				$this->mkdir_safe("$this->sMailRoot\\$this->PostOffice\\mailroot\\$this->MailBox");
			}
	
			//also create the inbox
			if (!is_dir("$this->sMailRoot\\$this->PostOffice\\mailroot\\$this->MailBox\\inbox")) {
				$this->mkdir_safe("$this->sMailRoot\\$this->PostOffice\\mailroot\\$this->MailBox\\inbox");
			}
	
			$oSMTPDomain = new COM("MEAOSM.Domain");
			$oAddressMap = new COM("MEAOAM.AddressMap");

			//make sure the result view is specified as the default in the scope item properties
	
			//this is a host, so find the accounts and list them
			$oSMTPDomain->AccountName = $this->PostOffice;
			$oSMTPDomain->DomainName = "";
			$oSMTPDomain->Status = -1;
			$oSMTPDomain->DomainRedirectionStatus = -1;
			$oSMTPDomain->DomainRedirectionHosts = "";
	
			if ($oSMTPDomain->FindFirstDomain() == 1) { 
				do {
					$this->sTemp = "[SMTP:" . $this->MailBox . "@" . $oSMTPDomain->DomainName . "]";
					
					$this->sMsg = $this->sMsg . "<BR>Added email address: " . $this->MailBox . "@" . $oSMTPDomain->DomainName;
					
					//add this to the file
					$oAddressMap->Account = $this->PostOffice;
					//destination is this MailBox
					$oAddressMap->DestinationAddress = "[SF:" & $this->PostOffice & "/" & $this->MailBox & "]";
					$oAddressMap->Scope = "";
					$oAddressMap->SourceAddress = $this->sTemp;
					$this->lResult = $oAddressMap->AddAddressMap();
					
					if ($this->lResult == 0) {
						$this->sErr = $this->sErr . "<BR>Could not add address mapping.";
					}

					$oSMTPDomain->AccountName = $this->PostOffice;
					$oSMTPDomain->DomainName = "";
					$oSMTPDomain->Status = -1;
					$oSMTPDomain->DomainRedirectionStatus = -1;
					$oSMTPDomain->DomainRedirectionHosts = "";
				
				} while ($oSMTPDomain->FindNextDomain() == 1);
			} else {
				$this->sErr = $this->sErr . "<BR>There are no domains for post office:" & $this->PostOffice;
			}

			// Free up system resources
			unset($oSMTPDomain);
			unset($oAddressMap);

			if (strlen($this->sErr) < 1) {$this->sMsg = "Success";}
		}
	} //End of function createMailbox(*)
} //End of Class phpMailEnable














