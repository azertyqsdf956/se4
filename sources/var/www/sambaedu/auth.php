<?php

/**

 * Page pour l'authentification
 * @Version $Id$

 * @Projet LCS / SambaEdu

 * @auteurs  jLCF >:>  jean-luc.chretien@tice.ac-caen.fr
 * @auteurs  oluve  olivier.le_monnier@crdp.ac-caen.fr
 * @auteurs Olivier LECLUSE

 * @Licence Distribue selon les termes de la licence GPL

 * @note

 */

/**
 *
 * @Repertoire: /
 * file: auth.php
 *
 */

// Initialisation:
$error = 0;

require_once 'config.inc.php';
require 'jlcipher.inc.php';
require_once  'functions.inc.php';
require_once 'ldap.inc.php';
require 'test_dates.inc.php';

require_once 'lang.inc.php';
bindtextdomain('se4-core', "/var/www/sambaedu/locale");
textdomain('se4-core');

// Pas de fichier entête donc on place ici HTMLPurifier
require_once ("includes/traitement_data.inc.php");
// $config['autologon'] = 0;
// section auth.php de SE3 V 2.1.6495
if (! isset($_GET["request"]))
    $_GET["request"] = "";
if ((! isset($_GET["al"]) || ($_GET["al"] != 0)) && (isset($_POST["login"]) && isset($_POST["dummy"]) || isset($config['autologon']))) {
    if (open_session($config, $_POST["login"], $_POST["string_auth"], $_GET["al"])) {
        if (isset($_GET["request"]) && ($_GET["request"] != '')) {
            header("Location:" . rawurldecode($_GET["request"]));
            exit();
        } else {
            header("Location:index.php");
            exit();
        }
    } else {
        if (isset($_GET["request"]) && ($_GET["request"] != '')) {
            if ($_POST["login"] == "") {
                header("Location:auth.php?al=0&error=2&request=" . rawurlencode($_GET["request"]));
            } else {
                header("Location:auth.php?al=0&error=1&request=" . rawurlencode($_GET["request"]));
            }
        } else {
            if ($_POST["login"] == "") {
                header("Location:auth.php?al=0&error=2");
            } else {
                header("Location:auth.php?al=0&error=1");
            }
        }
    }
} else {
    header_crypto_html("Authentification SE3", "");
    $texte = "";
    $texte .= gettext("<P>Afin de pouvoir rentrer dans l'interface <EM>SambaEdu</EM>, vous devez indiquer votre identifiant et votre mot de passe sur le r&#233;seau.\n");
    $texte .= "<form name = 'auth' action='auth.php?al=1&request=" . rawurlencode($_GET["request"]) . "' method='post' onSubmit = 'encrypt(document.auth)'>\n";
    $texte .= "<table><tr><td>\n";
    $texte .= gettext("Identifiant") . " :</td><td><INPUT TYPE='text' NAME='login' SIZE='20' MAXLENGTH='30'><BR>\n";
    $texte .= "</td></tr><tr><td>\n";
    $texte .= gettext("Mot de passe") . " :</td><td><INPUT TYPE='password' NAME='dummy' SIZE='20' MAXLENGTH='20'><BR>\n";
    $texte .= "</td></tr><tr align=\"right\"><td></td><td>\n";
    $texte .= "<input type='hidden' name='string_auth' value=''>";
    $texte .= "<input type='hidden' name='time' value=''>";
    $texte .= "<input type='hidden' name='client_ip' value='" . remote_ip() . "'>";
    $texte .= "<input type='hidden' name='timestamp' value='" . time() . "'>";
    $texte .= "<INPUT TYPE='submit' VALUE='" . gettext("Valider") . "'><BR>\n";
    $texte .= "</td></tr></table>\n";
    $texte .= "</form>\n";

    mktable("<STRONG>" . gettext("Authentification...") . "</STRONG>", $texte);
    crypto_nav("");
    if ($error == 1) {
        echo "<div class='alert_msg'>" . gettext("Erreur d'authentification !") . "</div>";
    }

    // Test de l'ecart entre la date du serveur et la date du client
    // S'il y a plus de 200 secondes d'ecart, on affiche une alert() javascript:
    test_et_alerte_dates();

    include ("includes/pdp.inc.php");
}
?>
