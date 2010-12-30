<?php 


php_main() ;

function fill_zero($n)
{
	$ret = null;
	for($i = 0; $i < 4 - strlen($n); $i++) {
		$ret .= '0';
	}

	$ret .= $n;
	return $ret;

}

function find_file()
{
	for($i = 0; $i < 1000000; $i++) {
		$name = 'skin' . fill_zero($i);
		if (!file_exists($name)) {
			return $name;
		}
	}
}

function getIds($psGet)
{
	$id["setd"] = $psGet->charIDToTypeID("setd");
	$id["Ply "] = $psGet->charIDToTypeID("Ply ");
	$id["Actn"] = $psGet->charIDToTypeID("Actn");
	$id["Aset"] = $psGet->charIDToTypeID("Aset");
	$id["null"] = $psGet->charIDToTypeID("null");
	$id["AdjL"] = $psGet->charIDToTypeID("AdjL");
	$id["Ordn"] = $psGet->charIDToTypeID("Ordn");
	$id["Trgt"] = $psGet->charIDToTypeID("Trgt");
	$id["T   "] = $psGet->charIDToTypeID("T   ");
	$id["Adjs"] = $psGet->charIDToTypeID("Adjs");
	$id["Chnl"] = $psGet->charIDToTypeID("Chnl");
	$id["Chnl"] = $psGet->charIDToTypeID("Chnl");
	$id["Cmps"] = $psGet->charIDToTypeID("Cmps");
	$id["H   "] = $psGet->charIDToTypeID("H   ");
	$id["Strt"] = $psGet->charIDToTypeID("Strt");
	$id["Lght"] = $psGet->charIDToTypeID("Lght");
	$id["Hst2"] = $psGet->charIDToTypeID("Hst2");
	$id["HStr"] = $psGet->charIDToTypeID("HStr");
	return $id;
}



function SetColor($ps, $id, $hue, $sat, $light)
{
	$desc2 = new COM('Photoshop.ActionDescriptor');
	$ref1 = new COM('Photoshop.ActionReference');
	$ref1->PutEnumerated( $id["AdjL"], $id["Ordn"], $id["Trgt"] );
	$desc2->putReference( $id["null"], $ref1 );
	$desc3 = new COM('Photoshop.ActionDescriptor');
	$list1 = new COM('Photoshop.ActionList');
	$desc4 = new COM('Photoshop.ActionDescriptor');
	$desc4->putEnumerated( $id["Chnl"], $id["Chnl"], $id["Cmps"] );

	$desc4->putInteger( $id["H   "], $hue );
	$desc4->putInteger($id["Strt"], $sat );
	$desc4->putInteger($id["Lght"],  $light);
	$list1->PutObject( $id["Hst2"], $desc4 );
	$desc3->putList( $id["Adjs"], $list1 );
	$desc2->putObject( $id["T   "], $id["HStr"], $desc3 );
	$ps->ExecuteAction($id["setd"], $desc2, 3);
}

function PlayAction($ps, $id, $action, $aset)
{
	$desc2 = new COM("Photoshop.ActionDescriptor");
	$ref2 = new COM("Photoshop.ActionReference");
	$ref2->PutName($id['Actn'], $action);
	//$ref2->PutName($id['Aset'], $aset);
	$desc2->PutReference($id['null'], $ref2);
	$ps->ExecuteAction($id['Ply '], $desc2, 3);

}

function setAndSave($ps, $id, $h, $s, $l)
{
	global $argv, $argc;
	SetColor($ps, $id, $h, $s, $l);
	sleep(1);
	$res = PlayAction($ps, $id, $argv[1], 'execaction');
	$name = "skin." . $h . "." . $s . "." . $l;
	rename('skin', $name);
	mkdir('skin');
	file_put_contents("skin.log", "Done... $name... $h:$s:$l ...\n", FILE_APPEND);
	print("Done... $name... $l:$s:$l ...\n");
	sleep(3);
}

function php_main()
{

	global $argv;
	chdir('d:/lxladminpsd/mainskin/');
	$val = 10;
	$ps = new COM('Photoshop.Application');
	$ps->Open("d:/lxladminpsd/mainskin/{$argv[1]}-skin-clean-domainbackup1.psd");
	$ps->Visible = true;

	$id = getIds($ps);

	//Looping Real Colors....
	$l = 0;
	for ($h = 135; $h <= 180; $h = $h+ 5) {
		for($s = -60; $s <= 40; $s = $s + 3) {
			setAndSave($ps, $id, $h, $s, $l);
		}
	}

	// Shades of Gray...
	$h = 0;
	$s = "-90";
	for($l = -25; $l <= 40; $l = $l + 5) {
		setAndSave($ps, $id, $h, $s, $l);
	}


	//$ps->Quit();
	//$ps->Release();

}




