<?php


   /**
   
   * Ouvre un popup d'alert (message se trouvant sur wawadeb)
   * @Version $Id$ 
   * 
   * @Projet LCS / SambaEdu 
   * @auteurs Philippe Chadefaux  MrT
   * @Licence Distribue selon les termes de la licence GPL
   * @note
   * Modifications proposees par Sebastien Tack (MrT)
   * Optimisation du lancement des scripts bash par la technologie asynchrone Ajax.
 
   
   */

   /**

   * @Repertoire: /tests/
   * file: popup_alert.php
   */

require_once "config.inc.php";

require_once('entete_ajax.inc.php');
  //######################### MISES A JOUR ######################################## ##/

		
	// Ajout popup d'alerte
	include("fonc_outils.inc.php");
	
	init_param($config, "url_popup_alert","http://wwdeb.crdp.ac-caen.fr/mediase3/index.php/Alerte_popup.html");
	init_param($config, "tag_popup_alert", 0);
	
	system("cd /tmp; wget -q --tries=1 --timeout=2 ".$config['url_popup_alert']);
   	if (file_exists("/tmp/Alerte_popup.html")) {
        	$lines = file("/tmp/Alerte_popup.html");
	        foreach ($lines as $line_num => $line) {
			$line=trim($line);
			if(preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/","$line",$matche)) {
				// test la persence du tag precedent
				$tag_alerte=$matche[1].$matche[2].$matche[3];
				if ($tag_alerte==$config['tag_popup_alert']) {
					$ok_alert="0";
				} else {	
	                        	$ok_alert="1";
				}	
	                }
	        }
	}												
	@unlink("/tmp/Alerte_popup.html");	   

	$rep = "$ok_alert"; 
	
	if ($ok_alert=="1") {
		set_config("tag_popup_alert",$tag_alerte);
		$rep = "window.open('".$config['url_popup_alert']."','PopUp','width=500,height=350,location=no,status=no,toolbars=no,scrollbars=no,left=100,top=80');";
	}
	
	die($rep);
	
?>
