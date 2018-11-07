<?php

/**
 * Menu Clients Windows GPO


 * @Projet SambaEdu 

 * @Auteurs Equipe TICE crdp de Caen

 * @Note: 

 * @Licence Distribue sous la licence GPL
 */

/**
 *
 * file: 70wondoz.inc
 *
 * @Repertoire: includes/menu.d/
 */
require_once ("lang.inc.php");
bindtextdomain('se3-core', "/var/www/se3/locale");
textdomain('se3-core');

// Si le menu fond d'ecran est active, on ajoute la ligne correspondante:
if (isset($config['menu_fond_ecran'])) {
    $liens1 = array(
        array(
            gettext("Clients Windows"),
            "computers_is_admin|parc_can_manage",
            1,
            gettext("Gestion des GPOs"),
            "gpo/gestion_gpo.php",
            "computers_is_admin",
            1,
            gettext("Test des GPOs"),
            "gpo/test_gpo.php",
            "computers_is_admin",
            2,
            gettext("D&#233;ploiement"),
            "deploy/accueil.php",
            "computers_is_admin",
            2,
            gettext("Navigateurs et mails"),
            "mozilla_profiles/profile.php",
            "computers_is_admin",
            2,
            gettext("Fonds d'&#233;cran"),
            "fond_ecran/fonds.php",
            "se3_is_admin",
            2
        )
    );
} else {
    $liens1 = array(
        array(
            gettext("Clients Windows"),
            "computers_is_admin|parc_can_manage",
            1,
            gettext("Gestion des GPOs"),
            "gpo/gestion_gpo.php",
            "computers_is_admin",
            1,
            gettext("Test des GPOs"),
            "gpo/test_gpo.php",
            "computers_is_admin",
            2,
            gettext("D&#233;ploiement"),
            "deploy/accueil.php",
            "computers_is_admin",
            2,
            gettext("Navigateurs et mails"),
            "mozilla_profiles/profile.php",
            "computers_is_admin",
            2
        )
    );
}
$liens = array_merge($liens, $liens1);
?>
