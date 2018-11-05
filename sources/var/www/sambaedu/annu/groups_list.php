<?php

/**
* Affiche les groupes de l'AD correspondant au filtre passé en post

 * @Projet LCS / SambaEdu

 * @Auteurs Equipe Sambaedu

 * @Version $Id: groups_list.php  05-11-2018 keyser $

 * @Note: Ce fichier doit etre appele par un form

 * @Licence Distribue sous la licence GPL
 */
/**
*
* @Repertoire: annu
* file: groups_list.php
*/

include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu',"/var/www/se3/locale");
textdomain ('se3-annu');


$group = isset($_POST['group']) ? $_POST['group'] : "";
$priority_group = isset($_POST['priority_group']) ? $_POST['priority_group'] : "";


if (!isset($_POST['group'])) {
   die('Erreur : ce script ne peut être appelé directement!');
}

//var_dump($priority_group);
//var_dump($group);

echo "<h1>".gettext("Annuaire")."</h1>\n";
$_SESSION["pageaide"]="Annuaire";

if ((have_right($config, "Annu_is_admin")) || (have_right($config, "Annu_can_read")) || (have_right($config, "se3_is_admin"))) {

	aff_trailer ("3");

	if (!$group) {
		$filter = "(cn=*)";
	} else {
		if ($priority_group == "contient") {
	      		$filter = "(cn=*$group*)";
	    	} elseif ($priority_group == "commence") {
	      		$filter = "(|(cn=Classe_$group*)(cn=Cours_$group*)(cn=Equipe_$group*)(cn=Matiere_$group*)(cn=$group*))";
	    	} else {
	      		// $priority_group == "finit"
	      		$filter = "(|(cn=Classe_*$group)(cn=Cours_*$group)(cn=Equipe_*$group)(cn=Matiere_*$group)(cn=*$group))";
    		}
	}

	// Remplacement *** ou ** par *
	$filter=preg_replace("/\*\*\*/","*",$filter);
	$filter=preg_replace("/\*\*/","*",$filter);
	//var_dump($filter);
	#$TimeStamp_0=microtime();
	$groups = filter_group($config, $filter);
        //var_dump($groups);
        #$TimeStamp_1=microtime();
	  #############
	  # DEBUG     #
	  #############
	  #echo "<u>debug</u> :Temps de recherche = ".duree($TimeStamp_0,$TimeStamp_1)."&nbsp;s<BR>";
	  #############
	  # Fin DEBUG #
	  #############
	// affichage de la liste des groupes trouves
	if (count($groups)) {
	    if (count($groups)==1) {
		echo "<p><STRONG>".count($groups)."</STRONG>".gettext(" groupe r&#233;pond &#224; ces crit&#232;res de recherche")."</p>\n";
	    } else {
	      	echo "<p><STRONG>".count($groups)."</STRONG>".gettext(" groupes r&#233;pondent &#224; ces crit&#232;res de recherche")."</p>\n";
	    }
	    echo "<UL>\n";
	    for ($loop=0; $loop < count($groups); $loop++) {
	      	echo "<LI><A href=\"group.php?filter=".$groups[$loop]["cn"]."\">";
	      	//if ($groups[$loop]["type"]=="posixGroup")
        		 echo "<STRONG>".$groups[$loop]["cn"]."</STRONG>";
	      	//else
        		//echo $groups[$loop]["cn"];
      			echo "</A>&nbsp;&nbsp;&nbsp;<font size=\"-2\">".$groups[$loop]["description"]."</font></LI>\n";
            }
    	    echo "</UL>\n";
	} else {
    		echo "<STRONG>".gettext("Pas de r&#233;sultats")."</STRONG> ".gettext("correspondant aux crit&#232;res s&#233;lectionn&#233;s.")."<BR>";
	}
  
} else {
    die(gettext("Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction") . "</BODY></HTML>");
}
	

include ("pdp.inc.php");
?>
