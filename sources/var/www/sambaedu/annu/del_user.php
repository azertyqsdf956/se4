<?php


   /**
   
   * Supprime les utilisateurs 
   * @Version $Id$ 
   
   * @Projet LCS / SambaEdu 
   
   * @auteurs jLCF jean-luc.chretien@tice.ac-caen.fr
   * @auteurs wawa  olivier.lecluse@crdp.ac-caen.fr
   * @auteurs Equipe Tice academie de Caen
   * @auteurs oluve olivier.le_monnier.ac-caen.fr

   * @Licence Distribue selon les termes de la licence GPL
   
   * @note 
   */

   /**

   * @Repertoire: annu
   * file: del_user.php
   */





include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu',"/var/www/se3/locale");
textdomain ('se3-annu');

// AIde
$_SESSION["pageaide"]="Annuaire";

echo "<h1>".gettext("Annuaire")."</h1>\n";

aff_trailer ("3");
if (have_right($config, "Annu_is_admin")) {
	
	$cn=$_GET['cn'];
	// suppression d'un d'utilisateur
    	if ($cn == "admin" )  {
      		echo "<div class=error_msg>".gettext("Vous ne pouvez pas effacer le compte administrateur !")."</div>";
    	} elseif (!$cn)  {
      		echo "<div class=error_msg>".gettext("Vous devez pr&#233;ciser le login du compte a effacer ! !")."</div>";
    	} else {
        	exec ("/usr/share/se3/sbin/userDel.pl $cn",$AllOutPut,$ReturnValue);
        	if ($ReturnValue == "0") {
          		echo gettext("Le compte")." <strong>$cn</strong> ".gettext(" a &#233;t&#233; effac&#233; avec succ&#232;s !")."<BR>\n";
        	} else {
          		echo "<div class=error_msg>".gettext("Echec, l'utilisateur $cn n'a pas &#233;t&#233; effac&#233; !");
                  	echo gettext("(type d'erreur : ")."$ReturnValue), ".gettext(" veuillez contacter");
                  	echo "<A HREF='mailto:$MelAdminLCS?subject=".gettext("Effacement utilisateur")." $cn'>".gettext("l'administrateur du syst&#232;me")."</A></div><BR>\n";
        	}
    	}
} else {
    	echo "<div class=error_msg>".gettext("Cette fonctionnalit&#233;, n&#233;cessite les droits d'administrateur du serveur SambaEdu !")."</div>";
}
include ("pdp.inc.php");
?>
