<?php 
function get_plural($word)
	{
		return lerOrLar($word);

	}
	
	function lerOrLar($word){   // -ler and -lar are plural affixes in turkish.
		$lar=array('a','ý','o','u'); // If last syliable contains these vowels, we will add -lar.
		$ler=array('e','i','ö','ü'); // If last syliable contains these vowels, we will add -ler.   

		$lastLetters[]=substr($word, -1, 1);
		$lastLetters[]=substr($word, -2, 1);
		$lastLetters[]=substr($word, -3, 1); // We ge last three letters.
		
		if(array_intersect($lastLetters, $lar)) return $word.'lar';
		else return $word.'ler';
	}

