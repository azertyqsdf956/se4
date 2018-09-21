<?php


   /**
   
   * Expedie une popup a un groupe
   * @Version $Id$ 
   
   * @Projet LCS / SambaEdu 
   
   * @auteurs Philippe Peter
   * @auteurs Equipe Tice academie de Caen

   * @Licence Distribue selon les termes de la licence GPL
   
   * @note 
   */

   /**

   * @Repertoire: annu
   * file: pop_group.php
   */



 
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu',"/var/www/se3/locale");
textdomain ('se3-annu');



if ((have_right($config, "annu_can_read")) || (have_right($config, "Annu_is_admin")) || (have_right($config, "savajon_is_admin")))  {
	
	$_SESSION["pageaide"]="Annuaire";

	$filter=$_GET['filter'];

	$group=filter_group ("(cn=".$filter.")");
	$cns = search_cns ("(cn=".$filter.")");
	$people = search_people_groups ($cns,"(sn=*)","cat");
  	#$TimeStamp_1=microtime();
  	#############
  	# DEBUG     #
  	#############
  	#echo "<u>debug</u> :Temps de recherche = ".duree($TimeStamp_0,$TimeStamp_1)."&nbsp;s<BR><BR>";
  	#############
  	# Fin DEBUG #
  	#############
	if (count($people)) {
		// affichage des r?sultats
		// Nettoyage des _ dans l'intitul? du groupe
		$intitule =  strtr($filter,"_"," ");
		echo "<H1>".gettext("Pop Up vers")." $intitule <font size=\"-2\">".$group[0]["description"]."</font></H1>\n";
		echo gettext("Il y a ").count($people).gettext(" membre");
     		if ( count($people) >1 ) echo "s";
    		echo gettext(" dans ce groupe")."<BR>\n";

      		// formulaire popup
		echo "<B><H3>".gettext("Message du Pop Up pour")." $intitule:</H3></B><BR>";
		echo "<form action=\"respop_group.php\" method=\"post\">";
		echo "<textarea cols=60 rows=5 name=\"message\" maxlength=\"1200\"></textarea>";
		echo "<br>";
		echo "<br>";
		echo "<input type=hidden name=\"nomgroupe\" value=\"".$filter."\">\n";
		echo "<br>";
		echo "<input type=\"submit\" value=\"".gettext("Envoyer le Pop Up")."\">";
		echo "</form>";
	} else {
		echo " <STRONG>".gettext("Pas de membres")."</STRONG> ".gettext(" dans le groupe")." $filter.<BR>";
        }
}

include ("pdp.inc.php");
?>
