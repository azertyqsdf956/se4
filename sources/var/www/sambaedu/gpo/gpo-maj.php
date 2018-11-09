<?php

/**

 * Gestion des gpo pour clients Windows (mise a jour des gpo)


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
$gpos = list_gpo_templates();
foreach ($gpos as $gpo) {
    $res = import_gpo($config, $gpo['displayname'], $gpo['displayname']);
    echo "GPO " . $gpo['displayname'] . " version " . $gpo['version'] . ":<br>\n";
    if ($res)
        echo "importation OK<br>\n";
    else
        echo "ERREUR<br>\n";
}

include ("pdp.inc.php");
?>