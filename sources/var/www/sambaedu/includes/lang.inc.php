<?php


   /**
   * Gestion de la langue 
  
   * @Version $Id lang.inc.php 1923 2007-03-10 14:25:26Z crob $
   
   * @Projet LCS / SambaEdu 
   
   * @Auteurs Laurent COOPER
   * @auteurs Philippe Chadefaux (Modified for Se3)
   
   * @Note This script is part of the SLIS Project initiated by the CARMI-Internet (Academie de Grenoble - France 38)

   * @Licence Distribue sous la licence GPL
   */

   /**

   * file: lang.inc.php
   * @Repertoire: includes/ 
   */  
  



require_once("config.inc.php");

if (isset($config['lang'])) {
   if($config['lang'] !="auto") {
	putenv('LANG=$config["lang"]');
	putenv('LANGUAGE=$config["lang"]');
	@setlocale('LC_ALL', $config['lang']);
   } else {

	// put here the langage for wich the interface is translated. fr_FR, or fr ...
   	$Interface_Lang=array('en_GB','fr_FR','de_DE','es');

   	// Get browser accepted language and set to english if none.

   	$Server_Lang=@preg_split("/,/",(($_SERVER["HTTP_ACCEPT_LANGUAGE"] == '') ? 'en_US' :
   	$_SERVER["HTTP_ACCEPT_LANGUAGE"]));
   	// Determinate the score for each language. In case the browser returns no score, decalate in order
   	//fr-fr also allow fr to be chosen...
   	$sorting_param=0.01;
   	foreach ($Server_Lang as $part) {
		$part=trim($part);
		if(preg_match("/;/", $part)) {
			$config['lang']=@preg_split("/;/",$part);
			$score=@preg_split("/=/",$config['lang'][1]);
			$lang_scores[$config['lang'][0]]=$score[1];
			if (preg_match("/-/",$config['lang'][0])) {
				$noct=@preg_split("/-/",$config['lang'][0]);
				$lang_scores[$noct[0]]=$score[1]-$sorting_param;
			}
		} else {
			$lang_scores[$part]=1-$sorting_param;
			if (preg_match("/-/",$part)) {
				$noct=@preg_split("/-/",$part);
				$lang_scores[$noct[0]]=1-$sorting_param;
			}
			$sorting_param = $sorting_param +0.01;
		}
	}

   	// Now search for the language available with the highest score.

   	$curlscore=0;
   	$curlang=NULL;
   	foreach($Interface_Lang as $ilang) {
		$tmp=preg_replace("/\_/","-",$ilang);
		$allang=strtolower($tmp);
		$noct=@preg_split("/-/",$allang);

		$testvals=array($lang_scores[$allang],$lang_scores[$noct[0]]);
		$found=FALSE;
		foreach($testvals as $tval) {
			if(!$found && isset($tval)) {
				if ($curlscore<$tval) {
					$curlscore=$tval;
					$curlang=$ilang;
					$found=TRUE;}
				}
			}
		}

   	if (! isset($curlang)) {
		$curlang='en_US';
	}

   	if (preg_match("/\_/",$curlang)){
		$lang_tmp=@preg_split("/\_/",$curlang);
		$langage=$lang_tmp[0];
	}
   	else    {
		$langage=$curlang;
	}
	/*
	putenv("LANG=$curlang");
	putenv("LANGUAGE=$curlang");
	@setlocale('LC_ALL', $curlang);
	*/
	putenv("LANG=$langage");
	putenv("LANGUAGE=$langage");
	@setlocale('LC_ALL', $langage);
   }
}

?>
