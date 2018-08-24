<?php


   /**
   
   * Expedie une popup a un utilisateur 
   * @Version $Id$ 
   
   * @Projet LCS / SambaEdu 
   
   * @auteurs Philippe Peter
   * @auteurs Equipe Tice academie de Caen

   * @Licence Distribue selon les termes de la licence GPL
   
   * @note 
   */

   /**

   * @Repertoire: annu
   * file: respop_user.php
   */




include "entete.inc.php";
require_once ("lang.inc.php");
include "ihm.inc.php"; 
bindtextdomain('se3-annu',"/var/www/se3/locale");
textdomain ('se3-annu');


if ((have_right($config, "annu_can_read")) || (have_right($config, "Annu_is_admin")) || (have_right($config, "savajon_is_admin")))  {
	
	$message=$_POST['message'];
	$cn=$_POST['cn'];

	// Si le message contient un texte
	if(isset($message)) {
		$file = fopen("/tmp/popup.txt","w+");
		fwrite($file,($message));
		fclose($file);
	}

	if (($tri=="") OR (($tri != 0) AND ($tri != 2)) ) $tri=2; // tri par ip par defaut
	// modif du tri
	// /usr/bin/smbstatus -S| awk 'NF>6 {print $2,$5,$6}'|sort -u +2
	// le +POS de la fin donne le rang de la variable de tri (0,1,2...)
	if ("$smbversion" == "samba3") {
		exec ("/usr/bin/smbstatus -b | grep -v root | grep -v nobody | awk 'NF>4 {print $2,$4,$5}' | sort -u",$out); 
	} elseif ($tri == 0) {
	 	exec ("/usr/bin/smbstatus -S | grep -v root | grep -v nobody | awk 'NF>6 {print $2,$5,$6}' | sort -u",$out); 
	} else  { 
		exec ("/usr/bin/smbstatus -S | grep -v root | grep -v nobody | awk 'NF>6 {print $2,$5,$6}' | sort -u +2",$out); 
	}
	echo "<H1>".gettext("Envoi du Pop Up")."</H1>\n";

	// Aide
	$_SESSION["pageaide"]="Annuaire";
	

	for ($i = 0; $i < count($out) ; $i++) {
	  	$test=explode(" ",$out[$i]);
	    	$test[2]=strtr($test[2],"()","  ");
	    	$test[2]=trim($test[2]);
	    	$cntest=$test[0];

		if ("$cn" == "$cntest") {
	    		exec ("cat /tmp/popup.txt|smbclient -U 'Administrateur Samba Edu 3' -M $test[1]");
    			echo "<H3>".gettext("Envoi du Pop Up &#224; ")." $cn ".gettext(" effectu&#233;.")."<br></H3>";
    			echo "<small><b>".$cn."</b> ".gettext("a une session ouverte sur")."<b> $test[1] ($test[2])</b>";
       		}
	}

}
?>
