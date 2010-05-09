<?php 
// LxCenter:
// Changed some demo values.
//
include_once "lib/include.php";


install_main() ;

function install_main()
{
	global $gbl, $login, $ghtml; 
	
	
	try {
		initProgram("admin");
		ob_end_flush();

		create_servername();

		add_client_template();
		add_customer_reseller();
		add_domain_list();

		$login->was();

	} catch (Exception $e) {
		print($e->getMessage());
		print("\\n\n\n\n\n\n\n\n\n\nn\n");
	}
	print("\n");
}


function create_servername()
{
	return;
	$servername = new pserver(null, null, "titan.lxcenter.net");
	$servername->initThisDef();
	$servername->syncserver = $servername->nname;
	$servername->password = crypt('admin');
	$servername->dbaction = "add";
	$servername->write();

	$servername = new pserver(null, null, "athena.lxcenter.net");
	$servername->initThisDef();
	$servername->servername = $servername->nname;
	$servername->password = 'admin';
	$servername->dbaction = "add";
	$servername->write();

	$servername = new pserver(null, null, "apollo.lxcenter.net");
	$servername->initThisDef();
	$servername->servername = $servername->nname;
	$servername->password = 'admin';
	$servername->dbaction = "add";
	$servername->write();
}


function client_priv($priv, $val)
{		
		if($val == 0){
			$v = "Unlimited";
			$priv->domain_num = $v;
			$priv->disk_usage = $v;
			$priv->traffic_usage = $v;
			$priv->mailaccount_num  = $v;
			$priv->client_num  = $v;
			$priv->ssl_flag  = 'on';
			$priv->frontpage_flag  = 'on';
			$priv->domaindb_num = $v;
			$priv->maildisk_usage = $v;
		}else {
			$priv->domain_num = 50;
			$priv->disk_usage = 500;
			$priv->ssl_flag  = 'on';
			$priv->frontpage_flag  = 'on';
			$priv->traffic_usage = 500;
			$priv->maildisk_usage = 500;
			$priv->client_num  = 100;
			$priv->mailaccount_num  = 400;
			$priv->domaindb_num  = 5;
		}
		return $priv;
}

function add_customer_reseller()
{

	global $gbl, $login, $ghtml; 

	
	 $M[] = array("admin"  => array("master", "adelia"),
				  "adelle" => array("agnes", "aileen", "ainsley"),
				  "aileen" => array("ainslie", "aislin", "alaina"),
			      "adolph" => array("walta","walter","wanda"),
				  "aislin"    => array("waneta","ward"),
				  "waneta"    => array("wardah","warner"),
				  "wardah"    => array("warren","warrick"),
				  "warren"    => array("warwick","wasaki",),
				  "warwick"   => array("waseemah","washi"),
				  "waseemah"  => array("washington","watson"),
				  "watson"	  => array("adeline", "aggie", "wattan"),
				  "wattan"    => array("wayde","wayland"),
				  "wayde"     => array("waylon"),
				  "washi" 	  => array("wayne"),
				  "waylon"    => array("webster","wednesday"),

			  );



	 $W[] = array( "alaina" => array("wholesale",  "arding",  "hardwin",  "hardy"), 
	 			   "alina"  => array("harford",  "hargrove"),
	 			   "wayne"  => array("harkin",  "harlan",  "harley",  "harmon", "gatha"),
	 			   "master"  => array("harold",  "harper"), 
				   "washi" => array("harrison",  "harry"),
	 			   "adelle" => array("hartford")
			     );

	$R[] =	array( "admin"    =>  array("reseller"),
				   "hardy"	  =>  array("peter",  "paddy",	 "parker",  "patrick"),
			       "hartford" =>  array("pelham",  "percival"),
			       "gatha"    =>  array("philemon",  "philip"),
			       "wholesale "   =>  array("philo",  "phineas",  "piers"),
			       "harley"   =>  array("preston",  "prince"),
				   "webster"   => array("wells","wenda","wendell")
			     );

	$C[] =	array( "admin"	=> array("customer", "generic customer", "simplehosting", "sample customer", "ondemand"), 
					"master"	 =>  array("shelby"),
					"patrick"=>  array("kerry", "shelagh"),
					"aislin" =>  array("johny", "ercood"),
					"adrian" =>  array("sheldon",  "shell"),
					"agnes"  =>  array("shelley"),
					"harry" =>   array("shelly",  "shelton"),
					"reseller"=> array( "sheree", "sheryl"),
					"philip" =>  array( "shevaun",  "shevon",  "shiloh"),
				   	"prince" =>  array( "shirlee",  "shirley",  "shonda",  "shyla"),
					"wayde" =>   array("percy"),
					"wardah" =>  array("peregrin",  "peyton"),
				 	"harper" =>  array( "sibill"), 
					"wells"  =>  array("sibyl"),
					"washington" => array("wauna"),
					"ainslie" => array("waverly")
					
				 ); 


	$type['M'] = 'master';
	$type['W'] = 'wholesale';
	$type['R'] = 'reseller';
	$type['C'] = 'customer';


	$a =array("M", "W", "R", "C");
				
	foreach($a as $key=>$val){
	
		foreach($$val as $key1=>$val1){

			foreach($val1 as $key2=>$val2){
				foreach($val2 as $val3){

					$name = $val3;
					$parent_name = $key2;

					$client = new Client(null, null, $name);
					$client->initThisDef();
					client_priv($client->priv, 1);


					$res['password'] = crypt('admin');
					$res['email'] = $name."@lxlabs.com";
					$res['status'] = "on";
					$res['cpstatus'] = "on";
					$res['cttype'] = $type[$val];
					$res['parent_clname'] = "client-$parent_name";

					$client->create($res);

					if ($val == 'C') {
						unset($client->priv->client_num);
						unset($client->used->client_num);
					}

					$login->addToList("client", $client);
				}
			}
		}	
	}
}

function add_client_template()
{
	global $gbl, $login, $ghtml; 
	$arr = array(0=>"Unlimited Hosting Plan", 1=>"500MB Hosting Plan");
	foreach($arr as $key=>$val){
		$nname = string_convert($val);
		
		
		$clienttemplate = new Clienttemplate(null, null, $nname);
		$clienttemplate->initThisDef();
		client_priv($clienttemplate->priv, $key);
		$res['nname'] = $nname;
		$res['parent_clname'] = 'client-admin';
		
		$clienttemplate->create($res);
	//	$clienttemplate->priv = $priv;
		$login->addToList("clienttemplate", $clienttemplate);
		}
}


function string_convert($conv)
{
	$temp_name = str_replace(" ", "_", $conv);
	return $temp_name;
}

function add_domain_list()
{

	$list = array(
				  "adelia" => array("testtttttt.com", "hello.org"),
				  
				  "admin" => array("demo.lxcenter.net", "domaindomain.com", "example.com"),
				  
				  "kerry" => array("program.com", "review.org"),

				  "wholesale" => array("demo.lxcenter.com"),
				  
				  "master" => array("master.com", "mmast.com"),
				  
				  "reseller" => array("demo.com", "testingg.com"),

				  "shyla" => array("testing.org", "teeest.com", "aim.com"), 

				  "warwick" => array("test.com"), 

				  "sheree" => array("example.org", "domain.org", "domaint.com"),
			
				  "customer" => array("test.org", "freedomain.com", "paradise.com"),
				  
				  "shevon" => array("test200.org"),
				
				  "shelly" => array("freedomain1.com", "heven.com"),
				  
				  "sibill" => array("test100.org", "freedomain100.com"),

				  "walta" => array("paradise1.com"),
				  
				  "sibyl" => array("test101.org", "freedomain102.com", "paradise11.com")
			  );
	

	foreach($list as $key=>$val) {

		foreach($val as $key1=>$val1) {
		
			add_domain($val1, $key);
		}
	}

}

function add_domain($domain_name, $parent_name)
{
	global $gbl, $login, $ghtml; 
	$user = str_replace(".", "", $domain_name);

	$domain = new Domain(null, null, $domain_name);
	$domain->initThisDef();
	$domain->password = crypt('admin');
	$domain->cpstatus = 'on';

	$domain->parent_clname = "client-$parent_name";
	$domain->username = $user;
	$domain->dbpserverlist = array('localhost');
	
	$web = new Web(null, null, $domain_name);
	$web->initThisDef();
	$web->syncserver = 'localhost';
	$web->username = $user;
	$web->ttype = 'virtual';
	$web->write();
	
	$mmail = new Mmail(null, null, $domain_name);
	$mmail->initThisDef();
	$mmail->syncserver = 'localhost';
	$mmail->write();

	$mailaccount = new Mailaccount(null, null, "test@$domain_name");
	$mailaccount->initThisDef();
	$mailaccount->syncserver = 'localhost';
	$mailaccount->password = crypt('admin');
	$mailaccount->parent_clname = "domain-$domain_name";
	$mailaccount->cpstatus = 'on';
	$mailaccount->write();

	$ftpuser = new ftpuser(null, null, "test@$domain_name");
	$ftpuser->initThisDef();
	$ftpuser->password = crypt('admin');
	$ftpuser->parent_clname = "domain-$domain_name";
	$ftpuser->cpstatus = 'on';
	$ftpuser->write();

	$dns = new Dns(null, null, $domain_name);
	$dns->initThisDef();
	$dns->syncserver = 'localhost';
	$dns->createDefaultTemplate('192.168.1.1', 'dns22.lxcenter.net');
	$dns->write();


	$unname = $user;
	$uuser = new Uuser(null, null, $unname);
	$uuser->initThisDef();
	$uuser->username = $user;
	$uuser->password = crypt('admin');
	$uuser->parent_clname = "domain-$domain_name";
	$uuser->cpstatus = 'on';
	$uuser->syncserver = localhost;
	$uuser->write();
	$uuser->dbaction='clean';
	//$web->addObject('uuser', $uuser);

	$domain->write();
	print("Added domain\n");
}


