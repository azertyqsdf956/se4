<?php


   /**
   
   * Affiche les utilisateurs a partir de l'annuaire
   * @Version $Id$ 
   
   * @Projet LCS / SambaEdu 
   
   * @auteurs jLCF jean-luc.chretien@tice.ac-caen.fr
   * @auteurs oluve olivier.le_monnier@crdp.ac-caen.fr
   * @auteurs wawa  olivier.lecluse@crdp.ac-caen.fr
   * @auteurs Equipe Tice academie de Caen

   * @Licence Distribue selon les termes de la licence GPL
   
   * @note 
   */

   /**

   * @Repertoire: annu
   * file: annu.php
   */



include "entete.inc.php";
require_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu',"/var/www/se3/locale");
textdomain ('se3-annu');

if (have_right($config, "Annu_is_admin"))
        $_SESSION["pageaide"]="Annuaire";
elseif (have_right($config, "sovajon_is_admin"))
        $_SESSION["pageaide"]="L%27interface_prof#Annuaire";
else $_SESSION["pageaide"]="L%27interface_%C3%A9l%C3%A8ve#Acc.C3.A9der_.C3.A0_l.27annuaire";

echo "<h1>".gettext("Annuaire")."</h1>\n";

aff_trailer ("1");

aff_mnu_search(have_right($config, "Annu_is_admin"));
if (have_right($config, "Annu_is_admin")) {
	//echo "<ul><li><b>".gettext("Administration :")."</b></li>";
	echo "<ul><li><b>".gettext("Administration :")."</b>\n";
  	echo "<ul>\n";
	echo "<li><a href=\"delete_right.php\">".gettext("Enlever un droit d'administration.")."</a></li>\n";
//    	echo "<li><a href=\"peoples_desac.php\">".gettext("D&#233;sactiver des comptes.")."</a></li>\n";
//     	echo "<li><a href=\"peoples_desac.php?action=activ\">".gettext("Activer des comptes.")."</a></li>\n";
//     	echo "<li><a href=\"../infos/infomdp.php\">".gettext("Tester les mots de passe.")."</a></li>\n";
     	echo "<li><a href=\"reinit_mdp.php\">".gettext("R&#233;initialiser/Modifier les mots de passe.")."</a></li>\n";
     	if (getintlevel()>=1)
//       		echo "<li><a href=\"remplace.php\">".gettext("Attribution des droits &#224; un rempla&#231;ant.")."</a></li>\n";
    	echo "</ul>\n";
	echo "</li>\n";
	echo "</ul>\n";
	if (isset($_POST['hiddeninput'])) {
        include("listing.inc.php");
	}
}

include ("pdp.inc.php");
?>
