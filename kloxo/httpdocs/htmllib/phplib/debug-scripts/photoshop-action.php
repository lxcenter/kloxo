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

	$desc4->putInteger( $id["H   "], $hue);
	$desc4->putInteger($id["Strt"], $sat);
	$desc4->putInteger($id["Lght"],  $light);
	$list1->PutObject( $id["Hst2"], $desc4);
	$desc3->putList( $id["Adjs"], $list1);
	$desc2->putObject( $id["T   "], $id["HStr"], $desc3);
	try { 
		$ps->ExecuteAction($id["setd"], $desc2, 3);
	} catch (exception $e) {
		$id3 = $ps->charIDToTypeID( "Mk  " );
		$desc2 = new COM('Photoshop.ActionDescriptor');
		$id4 = $ps->charIDToTypeID( "null" );
		$ref1 = new COM('Photoshop.ActionReference');
		$id5 = $ps->charIDToTypeID( "AdjL" );
		$ref1->putClass($id5);
		$desc2->putReference($id4, $ref1);
		$id6 = $ps->charIDToTypeID( "Usng" );
		$desc3 = new COM('Photoshop.ActionDescriptor');
		$id7 = $ps->charIDToTypeID( "Type" );
		$desc4 = new COM("Photoshop.ActionDescriptor");
		$id8 = $ps->charIDToTypeID( "Clrz" );
		$desc4->putBoolean($id8, false );
		$id9 = $ps->charIDToTypeID( "HStr" );
		$desc3->putObject($id7, $id9, $desc4 );
		$id10 = $ps->charIDToTypeID( "AdjL" );
		$desc2->putObject($id6, $id10, $desc3);

		try {
			$ps->executeAction($id3, $desc2);
		} catch (exception $e) {
			$idm3 = $ps->charIDToTypeID( "CnvM" );
			$descm2 = new COM("Photoshop.ActionDescriptor");
			$idm4 = $ps->charIDToTypeID( "T   " );
			$idm5 = $ps->charIDToTypeID( "RGBM" );
			$descm2->putClass($idm4, $idm5 );
			$dialogmod_no = 3;
			$ps->executeAction($idm3, $descm2, $dialogmod_no);
			$ps->executeAction($id3, $desc2, $dialogmod_no);
		}

		
		SetColor($ps, $id, $hue, $sat, $light);
	}


}

function saveAsGif($ps, $file)
{
	/*
	$gifSaveOptions = new COM("Photoshop.GIFSaveOptions");
	//$gifSaveOptions->colors = 256;
	$gifSaveOptions->preserveExactColors = 1;
	$gifSaveOptions->transparency = 1;
	$gifSaveOptions->interlaced = 0;

         //Save the file and close it
	//$ps->activeDocument->flatten();
	$ps->activeDocument->saveAs($file, $gifSaveOptions, true);
	*/

	SaveGif($ps, $file);

}



function SaveGif($ps, $dir)
{
	$id14 = $ps->charIDToTypeID( "save" );
	$desc6 = new COM("Photoshop.ActionDescriptor");
	$id15 = $ps->charIDToTypeID( "As  " );
	$desc7 = new COM("Photoshop.ActionDescriptor");
	$id16 = $ps->charIDToTypeID( "Intr" );
	$desc7->putBoolean( $id16, false );
	$id17 = $ps->charIDToTypeID( "GFFr" );
	$desc6->putObject( $id15, $id17, $desc7 );
	$id18 = $ps->charIDToTypeID( "In  " );
	$desc6->putPath( $id18, $dir);
	$dialog = 3;
	$ps->executeAction( $id14, $desc6, $dialog);
}






function setAndSave($ps, $basefile, $id, $h, $s, $l)
{
	global $argv, $argc;
	SetColor($ps, $id, $h, $s, $l);
	$skindir = "skin.$h.$s.$l";
	if (!file_exists($skindir)) {
		mkdir($skindir);
	}
	$basefile = basename($basefile, ".psd");
	$file = "d:\\lxlabs-skin\\$skindir";
	//$file = "d:\\lxlabs-skin\\$skindir";
	saveAsGif($ps, $file);
}

function php_main()
{

	global $argv;

	//$ps->Open("d:/lxlabs-skin/{$argv[1]}-skin-clean-domainbackup1.psd");
	chdir("d:/lxlabs-skin/");
	$val = 10;
	$dir = "d:\\lxlabs-skin\\base_psd";
	$ps = new COM('Photoshop.Application');
	$list = scandir($dir);
	$ps->Visible = true;

	$id = getIds($ps);

	//Looping Real Colors....

	//change_color_and_save($ps, $id, $dir, "top_line_dark1.psd");
	foreach($list as $file) {
		if (!strstr($file, ".psd")) {
			continue;
		}
		change_color_and_save($ps, $id, $dir, $file);
	}
}


function change_color_and_save($ps, $id, $dir, $file)
{

	$ps->Open("$dir\\$file");

	$ps->Visible = true;
	$l = 0;
	$s = 0;
	for ($h = -180; $h <= 180; $h = $h + 1) {
		setAndSave($ps, $file, $id, $h, $s, $l);
	}
	$s = -20;
	$l = -20;
	for ($h = -180; $h <= 180; $h = $h + 1) {
		setAndSave($ps, $file, $id, $h, $s, $l);
	}

	closeFile($ps);
}


function closeFile($ps)
{
	$id14 = $ps->charIDToTypeID("Cls ");
	$desc5 = new COM("Photoshop.ActionDescriptor");
	$id15 = $ps->charIDToTypeID("Svng");
	$id16 = $ps->charIDToTypeID("YsN ");

	$id17 = $ps->charIDToTypeID("N   ");

	$desc5->putEnumerated($id15, $id16, $id17 );

	$ps->executeAction($id14, $desc5);
}
	//$ps->Quit();
	//$ps->Release();


