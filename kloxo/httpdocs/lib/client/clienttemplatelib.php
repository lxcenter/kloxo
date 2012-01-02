<?php 

class ClientTemplate extends ClienttemplateBase {



 
function createShowPropertyList(&$alist)
{
	$alist['property'][] = "a=show";
	$alist['property'][] = "a=updateForm&sa=ipaddress";
	$alist['property'][] = "a=updateForm&sa=description";
	$alist['property'][] = "a=updateForm&sa=disable_per";
	$alist['property'][] = "a=updateform&sa=dnstemplatelist";
}


function createShowUpdateform()
{

	if (check_if_many_server()) {
		$uflist['pserver_s'] = null;
	}
	$uflist['limit'] = null;
	return $uflist;
}

function createShowAlist(&$alist, $subaction = null)
{
	
	global $gbl, $sgbl, $login, $ghtml; 


	return $alist;


}

}
