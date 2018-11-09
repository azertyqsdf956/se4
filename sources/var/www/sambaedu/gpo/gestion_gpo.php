<?php

   /**
   
   * Gestion des GPO pour clients Windows (page d'import-export)
    
  
   * @Projet  SambaEdu 
   
   * @auteurs  Deins Bonnenfant
   
   * @Licence Distribue selon les termes de la licence GPL
   
   * @note 
   
   */

   /**

   * @Repertoire: registre
   * file: gestion_interface.php

  */	


/*

$action=$_GET['action'];
$cat=$_GET['cat'];
$sscat=$_GET['sscat'];
if (!$cat) { $cat=$HTTP_COOKIE_VARS["Categorie"]; }
if ($cat) {
	setcookie ("Categorie", "", time() - 3600);
	setcookie("Categorie",$cat,time()+3600);
}

if ($cat=="tout") {
	setcookie ("Categorie", "", time() - 3600);
	$cat="";
	$sscat="";
}
*/

require_once "entete.inc.php";
require_once "functions.inc.php";
require_once "ldap.inc.php";
require_once "ihm.inc.php";

if (!have_right($config, "computers_is_admin"))
        die (gettext("Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction")."</BODY></HTML>");
$_SESSION["pageaide"]="Gestion_des_clients_windowsNG#Description_du_processus_de_configuration_du_registre_Windows";

//require ("functions.inc.php");
$testniveau=getintlevel();
/*
if ($action == "delall")
{
	connexion ();
	$deleteSQL = "delete from corresp;";
	mysql_query($deleteSQL);
	mysql_close();
	echo "Suppression cl&#233s ok!\n";
}

if ($action == "delallmod" or $action == "delall")
{
	connexion ();
	$deleteSQL = "delete from modele;";
	mysql_query($deleteSQL);
	mysql_close();
	if ($action != "delall")
		echo "Suppression groupes de cl&#233s ok!\n";
}
*/
if ($testniveau) {
	echo "<h1>".gettext("Administration de l'interface GPO")."</h1>\n";
	echo "<h3>".gettext("Gestion des GPO :")."</h3>";
	echo "<a href=\"gpo-maj.php\">".gettext("Effectuer la mise a jour de la base des GPO")."</a><br>";
	echo "<a href=\"gpo-export.php\">".gettext("Exporter les GPO")."</a><br>";
	echo "<a href=\"gestion_gpo.php?action=delall\" onclick=\"return getconfirm();\">".gettext("Supprimer toutes les GPO ?")."</a><br>";

	if ($testniveau>1) {
		echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"gpo-maj.php\">";
		echo "<BR>".gettext("Incorporer le fichier de GPO suivant  (format .tgz) :");
		echo "<BR><input type=\"file\" name=\"fichier\" size=\"30\">";
		echo "<input type=\"hidden\" name=\"action\" value=\"file\" />";
		echo "<input type=\"submit\" name=\"upload\" value=\"Incorporer \">";

		echo "</form>";
		echo "<a href=\"cle_export.php?action=export\">".gettext("Exporter mes GPO ?")."</a></p></p>";
	}
/*	if ($testniveau>2) {
		echo "<form method=\"post\" enctype=\"multipart/form-data\" action=\"import_reg.php\">";
		echo "<BR>".gettext("Importer un fichier de cl&#233s au format .reg");
		echo "<BR><input type=\"file\" name=\"fichier\" size=\"30\">";
		echo "<input type=\"hidden\" name=\"action\" value=\"file\" />";
		echo "<input type=\"submit\" name=\"upload\" value=\"Incorporer \">";
		echo "</form>";
	}

	echo "<h3>".gettext("Gestion des groupes de cl&#233s :")." </h3>";
    echo "<a href=\"mod_maj.php?action=maj\">".gettext("Effectuer la mise &#224 jour des groupes de cl&#233s ?")."</a><br>";
	echo "<a href=\"affiche_modele.php\">".gettext("Editer les groupes de cl&#233s ?")."</a><br>";
	echo "<a href=\"gestion_interface.php?action=delallmod\" onclick=\"return getconfirm();\">".gettext("Supprimer tous les groupes de cl&#233s?")."</a><br>";
	if ($testniveau>1) {

		echo "<BR>".gettext("Incorporer le fichier de groupes de cl&#233s suivant (format xml) :")." <form method=\"post\" enctype=\"multipart/form-data\" action=\"mod_maj.php\">";
		echo "<BR><input type=\"file\" name=\"fichier\" size=\"30\">";
		echo "<input type=\"hidden\" name=\"action\" value=\"file\" />";
		echo "<input type=\"submit\" name=\"upload\" value=\"Incorporer\">";
		echo "</form>";
		echo "<a href=\"mod_export.php?action=export\">".gettext("Exporter mes groupes de cl&#233s ?")."</a>";
	}
	*/
}

// echo $testniveau;
# pied de page
require_once ("pdp.inc.php");
?>
