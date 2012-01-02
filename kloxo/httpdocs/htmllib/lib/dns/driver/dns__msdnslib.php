<?php

class dns__msdns extends lxDriverClass {


function dbactionAdd()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($sgbl->dbg < 0) {
		throw new lxException("no_dns_on_windows", '');
	}
	$this->createDnsData();
}

function dbactionDelete()
{
	//return;
	$this->deleteZone($this->main->nname);
}

function createDnsData()
{
	dprint("DbActionAdd\n");
    $fdata="";
	$tmp="";

	$this->createPrimaryZone($this->main->nname);
	lfile_put_contents("c:/Windows/System32/drivers/etc/hosts", "127.0.0.1 {$this->main->nname}", FILE_APPEND);
	return;

    if($this->main->ns_rec_a != ''){
		foreach($this->main->ns_rec_a as $value){
			$this->createNSRecord($this->main->nname, $value->nname);
		}
	}

    if($this->main->mx_rec_a != ''){
		foreach($this->main->mx_rec_a as $o){
			$this->createMxRecord($this->main->nname, 'something', $o->param, $o->nname);
		}
	}
    if($this->main->a_rec_a){
		foreach($this->main->a_rec_a as $o){
			$key = $o->nname;
			$value = $o->param;
			if($o->param === null) continue;	


			$this->createARecord($this->main->nname, $key, $value);
		}
	}

	if($this->main->cn_rec_a !=''){ 
		foreach($this->main->cn_rec_a as $o){
			$key = $o->nname;
			$value = $o->param;
			if($o->param === null) continue;	
			$key .= ".{$this->main->nname}.";

			if ($value !== "__base__") {
				$value = "$value.{$this->main->nname}.";
			} else {
				$value = "{$this->main->nname}.";
			}
			$fdata .= $tmp;
		}
	}
}


function createPrimaryZone($zoneName)
{
	print("Create Primary Zone\n");

	$ObjSvc = new lxCOM("winMgmts://./root/MicrosoftDNS");

	$colItems = $ObjSvc->ExecQuery("Select * from MicrosoftDNS_Zone Where Name='$zoneName' and ContainerName='$zoneName'");

	if (mycount($colItems) > 0) {
		//$this->deleteZone($zoneName);

		foreach($colItems as $obj) {
			$obj->delete_();
		}

	}


    $objDNSSet = $ObjSvc->Get("MicrosoftDNS_Zone");

	if ($objDNSSet) {
	// Only for 2003 it appears.
		$objDNSSet->CreateZone($zoneName, "0");
	}
	return true;
}

function updateZonesFiles($zoneName)
{
	$objWMIService = new lxCOM("winmgmts://./root/MicrosoftDNS");
	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_Zone Where Name = '$zoneName'");
	foreach($colItems as $o) {
		$o->UpdateFromDS();
	}

	foreach($colItems as $o) {
		$o->WriteBackZone();
	}
}

function deleteZone($zoneName)
{
	$objWMIService = new lxCOM("winmgmts://./root/MicrosoftDNS");
	$objServer = $objWMIService->Get("MicrosoftDNS_Server.name='.'");

	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_Zone Where Name='$zoneName' and ContainerName='$zoneName'");
	foreach($colItems as $objDNS) {
		//print("$objDNS->ZoneType\n");		
		//print("$objDNS->DnsServerName\n");		
		$objDNS->Delete_();
	}
}


function resolveBase($strDomain, $ownerName)
{
	if ($ownerName === "__base__") {
		$ownerstring = "$strDomain";
	} else {
		$ownerstring = "$ownerName.$strDomain";
	}
	return $ownerstring;
}










//      NS Record

function createNSRecord($strDomain, $recordValue)
{
	$strServer="";	
	$strContainer = $strDomain;
	$strOwner = $strDomain;
	$intRecordClass = '1';
//	$intTTL = '600';
	$intTTL = $this->main->ttl ;

	$objWMIService = new lxCOM("winmgmts://./root/MicrosoftDNS");
	$objItem = $objWMIService->Get("MicrosoftDNS_NSType");

	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_NSType where domainname='$strDomain' and recorddata='$recordValue.'");

	if (mycount($colItems) > 0) {
		print("Already Exists/n");
		throw new lxException("ns_rec_already_exist", '');
	}
	
	$errResult = $objItem->CreateInstanceFromPropertyData($strServer, $strContainer, $strOwner, $intRecordClass, $intTTL, $recordValue);

}

function addNSRecord()
{
	$rec = $this->main->__t_new_ns_rec_a_list;

	foreach($rec as $r) {
		$this->createNSRecord($this->nname, $r->nname);
	}
}

function delNSRecord($strDomain, $nspref)
{
	$objWMIService = new lxCOM("winmgmts://./root/MicrosoftDNS");

	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_NSType where domainname='$strDomain' and recorddata='$nspref.'");

	foreach($colItems as $objItem) {
		$objItem->delete_();
	}
}


function deleteNSRecord()
{
	$rec = $this->main->__t_delete_ns_rec_a_list;

	foreach($rec as $o) {
		$this->delNSRecord($this->nname, $o->nname);
	}
}






//              Arecord

function createARecord($strDomain, $host, $strIPAddress)
{
	$strContainer = $strDomain;

	if ($host === "__base__") {
		$strOwner = $strDomain;
	} else {
		$strOwner = "$host.$strDomain";
	}

	$intRecordClass = 1;
//	$intTTL = 600;
	$intTTL = $this->main->ttl ;

	$objWMIService = new lxCOM("winmgmts://./root/MicrosoftDNS");

	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_AType where ownername='$strOwner'");

	if (mycount($colItems) > 0) {
		print("Already Exists/n");
		throw new lxException("a_rec_already_exist", '');
	}

	$objItem = $objWMIService->Get("MicrosoftDNS_AType");

	$objServer = $objWMIService->Get("MicrosoftDNS_Server.name='.'");

	print($strContainer);
	$objItem->CreateInstanceFromPropertyData(".", $strContainer, $strOwner, $intRecordClass, $intTTL, $strIPAddress);

	dprint("UPdateing zone files/n");
}

function addARecord()
{
	$rec = $this->main->__t_new_a_rec_a_list;

	foreach($rec as $r) {
		$this->createARecord($this->nname, $r->nname, $r->param);
	}
}

function deleteARecord()
{
	$objWMIService = New lxCOM("winmgmts://./root/MicrosoftDNS");
	$strDomain = $this->main->nname;
	foreach($this->main->__t_delete_a_rec_a_list as $o) {
		$strDomain = $this->resolveBase($this->main->nname, $o->nname);
		$ownerstring = "ownername='$strDomain'";
		$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_AType where $ownerstring");
		foreach ($colItems as $o) {
			$o->delete_();
		}
	}
}






//         CName Record

function createCNameRecord($strDomain, $host, $recordValue)
{
	$strServer="";
	$strContainer = $strDomain;

	$strOwner=$host.".".$strDomain;

	$intRecordClass = '1';
//	$intTTL = '600'; 
	$intTTL = $this->main->ttl ;
	
	$intPreference = $recordValue.".".$strDomain;

	$objWMIService = new COM("winmgmts://./root/MicrosoftDNS");
	$objItem = $objWMIService->Get("MicrosoftDNS_CNAMEType");

	 $colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_CNAMEType where ownername='$strOwner'");
	
	 if (mycount($colItems) > 0) {
		print("Already Exists/n");
		throw new lxException("cn_rec_already_exist", '');
	 }

	$errResult = $objItem->CreateInstanceFromPropertyData($strServer, $strContainer, $strOwner, $intRecordClass, $intTTL, $intPreference);
}

function addCNameRecord()
{
	$rec = $this->main->__t_new_cn_rec_a_list;

	foreach($rec as $r) {
		$this->createCNameRecord($this->nname , $r->nname , $r->param);
	}
}

function deleteCNameRecord($strDomain , $primaryName_Old)
{
	$objWMIService = New COM("winmgmts://./root/MicrosoftDNS");
	$strDomain = $this->main->nname;
	foreach($this->main->__t_delete_cn_rec_a_list as $o) {
		$strDomain = $this->resolveBase($this->main->nname, $o->nname);
		$ownerstring = "ownername='$strDomain'";
		$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_CNAMEType where $ownerstring");
		foreach ($colItems as $o) {
			$o->delete_();
		}
	}
}






//         Mx Record

function createMxRecord($strDomain, $recordType, $recordValue, $MXpref)
{
	$strContainer = $strDomain;
	$strOwner = $strDomain;
	$intTTL = $this->main->ttl ;
	$intPreference = $MXpref;
	$intRecordClass = '1';
	$strMailExchanger = $recordValue;
	$strServer = "";
	 
	$objWMIService = new COM("winmgmts://./root/MicrosoftDNS");
	
	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_MXType where domainname='$strDomain' and preference='$MXpref'");

	if (mycount($colItems) > 0) {
		print("Already Exists/n");
		throw new lxException("mx_rec_already_exist", '');
	}

	$objItem = $objWMIService->Get("MicrosoftDNS_MXType");

	$errResult = $objItem->CreateInstanceFromPropertyData($strServer, $strContainer, $strOwner, $intReCordClass, $intTTL, $intPreference, $strMailExchanger);

}

function addMxRecord()
{
	/*
	$objWMIService = new COM("winmgmts://./root/MicrosoftDNS");
	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_MXType where mailExchange='$MailExchange_Old' and preference=$Preference_Old");
	foreach($colItems as $objItem) {
		$objItem->delete_();
	}
*/
	//uPDateZonesFiles strDomain
	$rec = $this->main->__t_new_mx_rec_a_list;

	foreach($rec as $r) {
		$this->createMxRecord($this->nname,'MX' ,$r->param ,$r->nname);
	}
}

function delMxRecord($strDomain, $mxpref)
{
	$objWMIService = new lxCOM("winmgmts://./root/MicrosoftDNS");

	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_MXType where domainname='$strDomain' and preference='$mxpref'");

	foreach($colItems as $objItem) {
/*
		print("$objItem->OwnerName"."\n");		
		print("$objItem->ContainerName"."\n");		
		print("$objItem->DnsserverName"."\n");		
		print("$objItem->DomainName"."\n");		
		print("$objItem->MFhost"."\n");		
		print("$objItem->RecordData"."\n");		
		print("$objItem->RecordClass"."\n");		
		print("$objItem->TextRepresentation"."\n");		
		print("$objItem->Timestamp"."\n");
*/
		$objItem->delete_();
	}
}

function deleteMxRecord()
{
	$rec = $this->main->__t_delete_mx_rec_a_list;

	foreach($rec as $o) {
		$this->delMxRecord($this->nname, $o->nname);
	}
}





//         Change Parameter
function changeParameter()
{

	$objWMIService = new COM("winmgmts:{impersonationLevel=impersonate}!//./root/MicrosoftDNS");

	$zoneName=$this->main->nname;

	$colItems = $objWMIService->ExecQuery("Select * from MicrosoftDNS_SOAType Where DomainName='$zoneName' and ContainerName='$zoneName'");

	foreach($colItems as $objItem) {
		/*
		print("$objItem->ContainerName"."\n");		
		print("$objItem->DnsserverName"."\n");		
		print("$objItem->RefreshInterval"."\n");
		print("$objItem->MinimumTTL"."\n");
		*/

		$objItem->MinimumTTL= $this->main->ttl;
		$objItem->Put_();
	}
}




//         db action

function dbactionUpdate($subaction)
{

	switch($subaction) {

		case "full_update":
			$this->createDnsData();
			break;
		case "add_ns_rec_a":
			$this->addNSRecord();
			break;

		case "delete_ns_rec_a":
			$this->deleteNSRecord();
			break;

		case "add_a_rec_a":
			$this->addARecord();
			break;

		case "delete_a_rec_a":
			$this->deleteARecord();
			break;


		case "add_cn_rec_a":
			$this->addCNameRecord();
			break;

		case "delete_cn_rec_a":
			$this->deleteCNameRecord();
			break;

		case "add_mx_rec_a":
			$this->addMxRecord();
				break;

		case "delete_mx_rec_a":
				$this->deleteMxRecord();
				break;

		case "parameter":
				$this->changeParameter();
				break;

	}


}

}

