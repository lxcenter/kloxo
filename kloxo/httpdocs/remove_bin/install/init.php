<?php 

function init_main($admin_pass)
{
	global $gbl, $sgbl, $login, $ghtml; 

	
	try {
		add_admin($admin_pass);
		initProgram("admin");

		create_servername();
		//create_default_template();


		$login->was();
		createDnsTemplate();
		Ticket::createWelcomeTicket();
		/*
		if (lxfile_exists("__path_program_etc/license.txt")) {
			decodeAndStoreLicense();
			$login->license_o->write();
			$login->write();
		}
	*/


	} catch (Exception $e) {
		print($e->getMessage());
		print("\\n\n\n\n\n\n\n\n\n\nn\n");
	}
	print("\n");
}


function createDnsTemplate()
{
	global $gbl, $sgbl, $login, $ghtml; 
	if ($sgbl->dbg < 1) {
		return;
	}

	$ip = getOneIPForLocalhost('something');
	$dnstemp = new DnsTemplate(null, null, 'test');
	$dnstemp->nameserver_f = "ns.lxlabs.com";
	$dnstemp->webipaddress = $ip;
	$dnstemp->mmailipaddress = $ip;
	$dnstemp->parent_clname = $login->getClName();
	$dnstemp->shared = "on";
	$dnstemp->dbaction = 'add';
	$dnstemp->postAdd();
	$dnstemp->was();
	create_default_template($dnstemp);
}

function create_default_template($dns)
{

	global $gbl, $sgbl, $login, $ghtml; 

	$temp = new Domaintemplate(null, null, "test");
	$temp->initThisDef();
	$temp->dbaction = 'add';
	$temp->ipaddress = $dns->webipaddress;
	$lispriv = new Listpriv(null, null, 'test');
	$listpriv->webpserver_sing = "localhost";
	$listpriv->mmailpserver_sing = "localhost";
	$listpriv->dnspserver_sing = "localhost";
	$listpriv->secdnspserver_sing = "--Disabled--";
	$temp->listpriv = $listpriv;
	$temp->dnstemplate = $dns->nname;
	// The listpriv used to get to initialized here....
	$temp->parent_clname = 'client-admin';
	$temp->description = "The Default Template created Only in debug Mode";
	$temp->write();
}




