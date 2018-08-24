<?php


   /**
   
   * Desactive des comptes 
   * @Version $Id$ 
   
   * @Projet LCS / SambaEdu 
   
   * @auteurs Sandrine Dangreville ( academie de creteil )

   * @Licence Distribue selon les termes de la licence GPL
   
   * @note 
   */

   /**

   * @Repertoire: annu
   * file: desac_user_entry.php
   */




  include "entete.inc.php";
  include "ldap.inc.php";
  include "ihm.inc.php";

  require_once ("lang.inc.php");
  bindtextdomain('se3-annu',"/var/www/se3/locale");
  textdomain ('se3-annu');

  echo "<title>".gettext("Activation / Desactivation des comptes")."</title><body>";

  // Aide
  $_SESSION["pageaide"]="Annuaire";
  
  echo "<h1>".gettext("Annuaire")."</h1>\n";
  $act=$_GET['action'];
  $cn=$_GET['cn'];
  
  if (have_right($config, "Annu_is_admin")) {
	if ($cn) {
		echo $cn."&nbsp;";
		userDesactive($cn,$act);
		echo "<br>";
	} else { 
		echo gettext("Aucun utilisateur s&#233;lectionn&#233;"); 
	}
} else {
    	echo "<div class=error_msg>".gettext("Cette fonctionnalit&#233;, n&#233;cessite les droits d'administrateur du serveur SambaEdu !")."</div>";
}

  include ("pdp.inc.php");
?>
 
