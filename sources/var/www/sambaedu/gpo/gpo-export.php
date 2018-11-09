<?php

/**

 * Gestion des gpo pour clients Windows (export des gpo)


 * @Projet SambaEdu 

 * @auteurs  denis.bonnenfant

 * @Licence Distribue selon les termes de la licence GPL

 * @note 

 */
/**
 *
 * @Repertoire: registre
 * file: gpo-maj.php
 *
 */
require_once "entete.inc.php";
require_once "config.inc.php";
require_once "ldap.inc.php";
require_once "ihm.inc.php";
require_once "gpo.inc.php";
require_once ("lang.inc.php");
bindtextdomain('se3-registre', "/var/www/se3/locale");
textdomain('se3-registre');

echo "<h1>Importation des cl&#233;s</h1>";

// connexion();

if (! have_right($config, "computers_is_admin"))
    die(gettext("Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction") . "</BODY></HTML>");

// Aide
$gpos = gpogetlink($config, $config['ldap_base_dn']);
foreach ($gpos as $gpo) {
    $res = export_gpo($config, $gpo['displayname']);
    echo "GPO " . $gpo['displayname'] . " :<br>\n";
    if ($res)
        echo "Exportation OK<br>\n";
    else
        echo "ERREUR<br>\n";
}

include ("pdp.inc.php");
?>