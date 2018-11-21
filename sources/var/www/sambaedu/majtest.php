<?php


 /**
 * Page qui teste les differents services
 * @Version dernière modif 11-2018 - keyser
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL
 * lancement des scripts bash par la technologie asynchrone Ajax.
 */
/**
 *
 * @Repertoire: /
 * file: majtest.php
 */
//require_once "config.inc.php";
require("entete.inc.php");

//aide
//$_SESSION["pageaide"]="Prise_en_main#Mettre_.C3.A0_jour_le_serveur";
//if (have_right($config, "Annu_is_admin")) {
if (!have_right($config,"se3_is_admin"))
	die ("<HTML><BODY>".gettext("Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction")."</BODY></HTML>");
//$action="";
//$action=$_GET['action'];
//	
//if ($action == "majsambaedu") {
//	$info_1 = gettext("Mise &#224; jour lanc&#233;e, ne fermez pas cette fen&#234;tre avant que le script ne soit termin&#233;. vous recevrez un mail r&#233;capitulatif de tout ce qui sera effectu&#233;...");
//	echo $info_1;
//	ob_implicit_flush(true); 
//	ob_end_flush();
//	system('sleep 1; /usr/bin/sudo -H /usr/share/sambaedu/scripts/install_sambaedu-module.sh sambaedu &');
//}
//else {
    echo "<H1>Mise  &#224; jour du serveur SambaÉdu</H1>\n";
    echo "<br><br>";
    echo "<center>";
    echo "<TABLE border=\"1\" width=\"80%\">";
    // Modules disponibles
//    echo "<TR><TD colspan=\"4\" align=\"center\" class=\"menuheader\" height=\"30\">\n";
//    echo gettext("Etat des paquets");
//    echo "</TD></TR>";
    echo "<TR><TD align=\"center\" class=\"menuheader\" height=\"30\">\n";
    echo gettext("Nom du paquet &#224; mettre  &#224; jour");
    echo "</TD><TD align=\"center\" class=\"menuheader\" height=\"30\">".gettext("Version install&#233;e")."</TD><TD align=\"center\" class=\"menuheader\" height=\"30\">".gettext("Version disponible")."</TD></TR>";

    
    // paquets sambaedu
    // On teste si on a bien la derniere version
    exec("dpkg -l | grep sambaedu | cut -d \" \" -f3", $liste_package);
    foreach ($liste_package as $se_package) {
        $se_version_install = exec("apt-cache policy $se_package | grep \"Install\" | cut -d\":\" -f2");
        $se_version_dispo = exec("apt-cache policy $se_package | grep \"Candidat\" | cut -d\":\" -f2");
        if ("$se_version_install" != "$se_version_dispo") {
            echo "<TR><TD>".gettext("Paquet $se_package")."</TD>";
            echo "<TD align=\"center\">$se_version_install</TD>";
            echo "<TD align=\"center\"><b>$se_version_dispo</b></TD>";
            echo "</TR>";
        }
   
    };
    
 

    
    echo "</table>";
    echo "<BR><BR>";
        
        
	echo "Vous devez lancer la mise à jour dans une console à l'aide de la commande \"apt-get install sambaedu*\"";
        //consulter la liste des changements en consultant <a href='http://wwdeb.crdp.ac-caen.fr/mediase3/index.php/Mises_%C3%A0_jour' TARGET='_blank' >cette page</a> \n"
	echo "<BR><BR>";
        //echo "<FORM action=\"majtest.php?action=majse3 \"method=\"post\"><CENTER><INPUT type='submit' VALUE='Lancer la mise &#224; jour'></CENTER></FORM>\n";
        

//}
# pied de page
include ("pdp.inc.php");

?>
