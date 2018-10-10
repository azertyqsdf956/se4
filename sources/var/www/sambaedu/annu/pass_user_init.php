<?php

/**

 * Reinitialise les mots de passe
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
 *
 * @Repertoire: annu
 * file: pass_user_init.php
 */
include "entete.inc.php";
include_once "ldap.inc.php";
include_once "siecle.inc.php";
include_once "samba-tool.inc.php";

include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/se3/locale");
textdomain('se3-annu');

if ((have_right($config, "annu_can_read")) || (have_right($config, "Annu_is_admin")) || (have_right($config, "sovajon_is_admin"))) {

    // Aide
    $_SESSION["pageaide"] = "Annuaire";

    if (! isset($_SESSION['comptes_crees'])) {
        $_SESSION['comptes_crees'] = array(
            array()
        ); // un sous-tableau par compte ; le deuxième tableau est, dans l'ordre nom, prenom, classe (?? en fait, non) (ou 'prof'), cn, password
        array_splice($_SESSION['comptes_crees'], 0, 1);
    }

    echo "<h1>" . gettext("Annuaire") . "</h1>\n";

    $cn_init = $_GET['cn'];

    // Recherche d'utilisateurs dans la branche people
    $info = search_user($config, $cn_init, true);
    if (count($info > 0)) {
        $prenom = $info["prenom"];
        $nom = $info["nom"];
        $date_naiss = $info["naissance"] ?? "";
        $classes = list_classes($config, $info['cn']);
        $classe = $classes[0] ?? "";
        

        echo "<a href='people.php?cn=" . $cn_init . "' title=\"Retour à la fiche de l'utilisateur" . $nom . " " . $prenom . "\">" . $nom . " " . $prenom . "</a>: ";

        switch ($config['pwdPolicy']) {
            case 0: // date de naissance
                $userpwd = $date_naiss;
                echo gettext("Mot de passe réinitialisé à la date de naissance : ");
                break;
            case 1: // semi-aleatoire
                $userpwd = createRandomPassword(8, false);
                echo gettext("Mot de passe r&#233;initialis&#233; &#224; : ");
                break;
            case 2: // aleatoire
                $userpwd = createRandomPassword(8, true);
                break;
                echo gettext("Mot de passe r&#233;initialis&#233; &#224; : ");
        }

        echo "<h2>" . $userpwd . "</h2><br> Il devra être changé à la prochaine connexion<br>";
        usersetpassword($config, $info['cn'], $userpwd, true);

        // ajouter vérification de doublon en cas de modifs successives pour un même cn.
        $doublon = false;
        foreach ($_SESSION['comptes_crees'] as &$key) {
            if ($key['cn'] == $cn_init) { // doublon : mise à jour pwd
                $doublon = true;
                $key['pwd'] = $userpwd;
                break;
            }
        }
        if (! $doublon) {
            $nouveau = array(
                'nom' => "$nom",
                'pre' => "$prenom",
                'cn' => "$cn_init",
                'pwd' => "$userpwd",
                'cla' => "$classe"
            );
            $_SESSION['comptes_crees'][] = $nouveau;
        }
        $doublon = false;
    } else {
        $error = gettext("Pas de compte ?");
    }
    // Lien pour la récupération du mailing
    if (count($_SESSION['comptes_crees'], COUNT_RECURSIVE) > 1) {
        $serial_listing = serialize($_SESSION['comptes_crees']);

        $lien = "<a href=\"#\" onclick=\"document.getElementById('postlisting').submit(); return false;\" target=\"_blank\">T&#233;l&#233;charger le listing des mots de passe modifi&#233;s...</a>";

        echo ("<table><tr><td><img src='../elements/images/pdffile.png'></td><td>");
        echo ($lien);
        echo ("<form id='postlisting' action='../annu/listing.php' method='post''>");
        echo ("<input type='hidden' name='hiddeninput' value='$serial_listing' />");
        echo ("<input type='checkbox' name='purge_csv_data' value='y' checked='checked' /> Purger le fichier temporaire apr&#232;s t&#233;l&#233;chargement du fichier");
        echo ("<br />Il n'est peut-&#234;tre pas tr&#232;s prudent de conserver inutilement ces donn&#233;es sur le serveur");
        echo ("</form></td></tr></table>");
    }
}

include ("pdp.inc.php");
?>

