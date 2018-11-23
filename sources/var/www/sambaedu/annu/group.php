<?php

/**

 * Affiche les membres d'un groupe
 * @Version $Id$
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL
 * @Repertoire: annu
 * file: group.php
 */
$time_start = microtime(1);
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/se3/locale");
textdomain('se3-annu');

$_SESSION["pageaide"] = "Annuaire";
echo "<h1>" . gettext("Annuaire") . "</h1>";

$filter = $_GET['filter'];

aff_trailer("8_$filter");
//recherche des membres du groupe

$group = search_ad($config, $filter, "group", $config['dn']['groups']);
$people = search_people_group($config, $filter);
usort($people, "cmp_nom");

//tableau de profs
$lesprofs = search_ad($config, "*", "memberof", "Profs");
$list_prof_grp = array();
foreach ($lesprofs as $key => $value) {
    $list_prof_grp[$key] = $value["cn"];
}

//tableau des PP si Equipe
if (preg_match("/Equipe/", $filter)) {
    $people2 = search_people_group($config, $filter);
    usort($people2, "cmp_nom");
    //on crée un tableau des PP de la classe
    $liste_pp = list_pp($config, $filter);
    $list_pp_grp = array();
    foreach ($liste_pp as $key => $value) {
        $cn = str_getcsv($value, ",");
        parse_str($cn[0], $output);
        $list_pp_grp[$key] = $output["CN"];
    }
}
//teste les droits
$admin_annu = have_right($config, "Annu_is_admin");
$can_read_annu = have_right($config, "annu_can_read");
$admin_sovajon = have_right($config, "sovajon_is_admin");
$admin_se = have_right($config, "se3_is_admin");


// Affiche les membres du groupe

if (count($people)) {
    $time_start = microtime(1);
    $intitule = strtr($filter, "_", " ");
    echo "<U>" . gettext("Groupe") . "</U> : $intitule <font size=\"-2\">" . $group[0]["description"] . "</font><BR>\n";
    echo gettext("Il y a ") . count($people) . " membre";
    if (count($people) > 1)
        echo "s";
    echo gettext(" dans ce groupe") . "<BR>\n";
    for ($loop = 0; $loop < count($people); $loop++) {

        if (in_array($people[$loop]["cn"], $list_prof_grp)) {

            echo "<img src=\"images/gender_teacher.gif\" alt=\"Professeur\" width=18 height=18 hspace=1 border=0>\n";
        }
        else {
            if (isset($people[$loop]["sexe"]) && $people[$loop]["sexe"] == "F") {
                echo "<img src=\"images/gender_girl.gif\" alt=\"El&egrave;ve\" width=14 height=14 hspace=3 border=0>\n";
            }
            else {
                echo "<img src=\"images/gender_boy.gif\" alt=\"El&egrave;ve\" width=14 height=14 hspace=3 border=0>\n";
            }
        }

        // Si on a pas les droits on n'a pas de lien
        if ($admin_annu || $can_read_annu) {
            echo "<A href=\"people.php?cn=" . $people[$loop]["cn"] . "\">" . $people[$loop]["fullname"] . "</A>";
        }
        else {
            // si on a les droits sovajon_is_admin on v&#233;rifie si on a la classe ou si les droits étendus du groupe prof sont activés
            $cn_eleve = $people[$loop]["cn"];
            $acl_group_profs_classes = exec("cd /var/se3/Classes; /usr/bin/getfacl . | grep group:Profs >/dev/null && echo 1");

            if ((tstclass($login, $cn_eleve) == 1) and ( $admin_sovajon or ( $acl_group_profs_classes == 1)) and ( $people[$loop]["prof"] != 1)) {
                echo "<A href=\"people.php?cn=" . $people[$loop]["cn"] . "\">" . $people[$loop]["prenom"] . " " . $people[$loop]["name"] . "</A>";
            }
            else {
                echo $people[$loop]["prenom"] . " " . $people[$loop]["name"];
            }
        }


           if (preg_match("/Equipe/", $filter) && in_array( $people[$loop]["cn"],$list_pp_grp)) {
               echo "<strong><font size=\"-2\" color=\"#ff8f00\">&nbsp;&nbsp;(" . gettext("professeur principal") . ")</font></strong>";
             $owner = $people[$loop]["cn"];
           }

        echo "<BR>\n";
    }
}
else {
    echo " <STRONG>" . gettext("Pas de membres") . "</STRONG> " . gettext(" dans le groupe") . " $filter.<BR>";
}



//
// Affichage menu admin (se3_is_admin et Annu_is_admin)
// Pour les groupes sauf pour les groupes Eleves Profs Administratifs
//

if (($admin_annu) && ($filter != "Eleves" && $filter != "Profs" && $filter != "Administration" )) {
    echo "<br><ul style=\"color: red;\">\n";

    // Affichage du menu "Ajouter des membres" si le groupe est de type Equipe_ ou Classe
    if (preg_match("/Equipe_/", $filter) || preg_match("/Classe_/", $filter)) {
        echo "<li><a href=\"add_list_users_group.php?cn=$filter\">" . gettext("Ajouter des membres") . "</a></li>\n";
    }

    // keyser ajout MC Marques
    // Affichage du menu "Ajouter des membres" si le groupe n'est de type Equipe_ ou Classe
    // pour ajouter dans un sous-groupe
    if (!preg_match("/Equipe_/", $filter) && !preg_match("/Classe_/", $filter)) {
        echo "<li><a href=\"aj_ssgroup.php?cn=$filter\">" . gettext("Ajouter des membres") . "</a></li>\n";
    }
    // fin ajout
    //Lien pour supprimer des membres
    if (count($people)) {
        echo "<li><a href=\"del_user_group.php?cn=$filter\">" . gettext("Enlever des membres") . "</a></li>\n";
    }

    // Lien pour supprimer le groupe
    echo "<li><a href=\"del_group.php?cn=$filter\" onclick= \"return getconfirm();\">" . gettext("Supprimer ce groupe") . "</a></li>\n";

    // Lien pour modifier la description du groupe
    echo "<li><a href=\"mod_group_descrip.php?cn=$filter\">" . gettext("Modifier la description de ce groupe") . "</a></li>\n";
    // Affiche un listing du groupe
    echo "<li><a href=\"grouplist.php?filter=$filter\" target='_new'>" . gettext("Afficher un listing du groupe") . "</a></li>\n";

    // Envoyer un popup a ce groupe
    echo "  <li><a href=\"pop_group.php?filter=$filter\">" . gettext("Envoyer un Pop Up &#224; ce groupe") . "</a></li>\n";

    // Affichage menu gestion des droits
    // si la personne est admin uniquement
    if ($admin_se) {
        // Affichage du menu "Deleguer un droit a un groupe"
        echo "<li><a href=\"add_group_right.php?cn=$filter\">" . gettext("G&#233;rer les droits de ce groupe") . "</a></li>\n";
        // ajout par keyser : affichage supplementaire pour les groupes tpe / idd ...
        if (!preg_match("!Equipe|Cours|Classe|Matiere|Administratifs|admins!", "$filter")) {
            echo "<li><a href=\"refresh_grpclass.php?nom_grp=$filter\">" . gettext("Cr&#233;er ou rafraichir une ressource groupe classe(s)") . "</a></li>\n";
        }
    } // Fin Affichage menu droits
    echo "</ul>\n";
}
else if ($admin_se) {
    // Affichage du menu "Deleguer un droit a un groupe"
    echo "<br><ul style=\"color: red;\">\n";
    echo "<li><a href=\"pop_group.php?filter=$filter\">" . gettext("Envoyer un Pop Up &#224; ce groupe") . "</a></li>\n";
    echo "<li><a href=\"add_group_right.php?cn=$filter\">" . gettext("G&#233;rer les droits de ce groupe") . "</a></li>\n";
    echo "<li><a href=\"grouplist.php?filter=$filter\" target='_new'>" . gettext("Afficher un listing du groupe") . "</a></li>\n";
    echo "<li><a href=\"create_template_group.php?filter=$filter\">" . gettext("Cr&#233;er un dossier de template pour le groupe") . "</a></li>\n";
    echo "</ul>\n";
}

if ($admin_se) {
    echo "<ul style=\"color: red;\">\n";
    echo "<li><a href=\"create_template_group.php?filter=$filter\">" . gettext("Cr&#233;er un dossier de template pour le groupe") . "</a></li>\n";
    echo "</ul>\n";
}

// ajout du lien trombinoscope
// Si Annu_is_admin et le repertoire existe on peut  voir les trombinoscopes
//
if ($admin_annu && is_dir("/var/se4/Docs/trombine")) {
    echo "<ul style=\"color: red;\">\n";
    echo "<li><a href=\"trombin.php?filter=$filter\" target='_new'>" . gettext("Afficher un trombinoscope du groupe") . "</a></li>\n";
    echo "</ul>\n";
}

// Si le groupe est classe
if (preg_match("/Classe_/i", "$filter")) {
    $classe = preg_replace("/Classe_/i", "", $filter);
    $classe = "Equipe_" . $classe;
}
// Si le groupe est cours
if (preg_match("/Cours_/i", "$filter")) {
    $classe = $filter;
    $classe = preg_replace("/Classe_/i", "", $filter);
}

if (!$admin_annu) {
    // Si sovajon_is_admin et prof de la classe ou droits étendus du groupe profs
    $acl_group_profs_classes = exec("cd /var/se4/Classes; /usr/bin/getfacl . | grep group:Profs >/dev/null && echo 1");

    if (($admin_sovajon) and ( (are_you_in_group($login, $classe) or ( $acl_group_profs_classes == 1)))) {

        // Affiche trombinoscope de la classe
        echo "<ul style=\"color: red;\">\n";
        echo "<li><a href=\"trombin.php?filter=$filter\" target='_new'>" . gettext("Afficher un trombinoscope du groupe") . "</a></li>\n";

        // Affiche un listing du groupe si on a une Classe
        // que pour les eleves

        if (preg_match("/Classe_/i", "$filter")) {
            // if ((preg_match("/Classe_/i", "$filter")) || (preg_match("/Cours_/i", "$filter"))) {
            echo "<li><a href=\"grouplist.php?filter=$filter\" target='_new'>" . gettext("Afficher un listing du groupe") . "</a></li>\n";
        }
        echo "</ul>\n";
    }
    elseif ($can_read_annu) {
        // Affiche trombinoscope de la classe
        echo "<li><a href=\"trombin.php?filter=$filter\" target='_new'>" . gettext("Afficher un trombinoscope du groupe") . "</a></li>\n";
        echo "</ul>\n";
    }
}

// Affichage de l'equipe pedagogique associee a la classe

if (preg_match("/Classe/", $filter, $matche)) {
    $filter2 = preg_replace("/Classe_/", "Equipe_", $filter);
    $people2 = search_people_group($config, $filter2);
    usort($people2, "cmp_nom");
    //on crée un tableau des PP de la classe
    $liste_pp = list_pp($config, $filter);
    $list_pp_grp = array();
    foreach ($liste_pp as $key => $value) {
        $cn = str_getcsv($value, ",");
        parse_str($cn[0], $output);
        $list_pp_grp[$key] = $output["CN"];
    }

    if (count($people2)) {
        // affichage des resultats
        echo "<BR><U>" . gettext("Professeurs de la classe") . "</U> : <a href=\"group.php?filter=$filter2\">$filter2</A><BR>\n";
        for ($loop = 0; $loop < count($people2); $loop++) {
            if (in_array($people2[$loop]["cn"], $list_prof_grp)) {
                echo "<img src=\"images/gender_teacher.gif\" alt=\"Professeur\" width=18 height=18 hspace=1 border=0>\n";
            }
            else {
                if (isset($people2[$loop]["sexe"]) && $people2[$loop]["sexe"] == "F") {
                    echo "<img src=\"images/gender_girl.gif\" alt=\"El&egrave;ve\" width=14 height=14 hspace=3 border=0>\n";
                }
                else {
                    echo "<img src=\"images/gender_boy.gif\" alt=\"El&egrave;ve\" width=14 height=14 hspace=3 border=0>\n";
                }
            }

            // On a un lien sur les profs uniquement si on est annu_can_read ou Annu_is_admin
            if (($admin_annu) || ($can_read_annu)) {
                echo "<A href=\"people.php?cn=" . $people2[$loop]["cn"] . "\">" . $people2[$loop]["fullname"] . "</A>";
            }
            else {
                echo $people2[$loop]["fullname"];
            }
            if (in_array($people2[$loop]["cn"], $list_pp_grp)) {
                echo "<strong><font size=\"-2\" color=\"#ff8f00\">&nbsp;&nbsp;(professeur principal)</font></strong>";
                $owner = $people2[$loop]["cn"];
            }
            echo "<BR>\n";
        }
    }
}

// Affichage du rebond sur la classe associee a une equipe pedagogique

if (preg_match("/Equipe/", $filter, $matche)) {
    $filter2 = preg_replace("/Equipe_/", "Classe_", $filter);
    $people2 = search_people_group($config, $filter2);
    if (count($people2)) {
        // affichage des resultats
        echo "<BR>" . gettext("Il y a ") . count($people2) . gettext(" &#233;l&#232;ves dans la ") . "<a href=\"group.php?filter=$filter2\">$filter2</A>" . gettext(" associ&#233;e &#224; cette &#233;quipe.") . "\n";
        echo "<BR>\n";
    }
}

include ("pdp.inc.php");
?>