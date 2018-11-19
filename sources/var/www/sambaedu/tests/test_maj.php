<?php


   /**
   
   * Test les mises a jour se3
   * @Version $Id$ 
   * @Projet LCS / SambaEdu 
   * @auteurs Philippe Chadefaux  MrT
   * @Licence Distribue selon les termes de la licence GPL
   * @note
   * Modifications proposees par Sebastien Tack (MrT)
   * Optimisation du lancement des scripts bash par la technologie asynchrone Ajax.
 
   
   */

   /**

   * @Repertoire: /tests/
   * file: test_maj.php
   */


	require_once('entete_ajax.inc.php');
   

	// Mises a jour de se3

	$se3 = exec('/usr/share/sambaedu/scripts/test_maj.sh 2>&1',$retour,$retourV);
	//print_r($retour);
	if (trim($se3) == "1")
		$ok="1";
	else
		$ok="0";

	die($ok);
?>
