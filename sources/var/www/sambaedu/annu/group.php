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
$group = search_ad($config, $filter, "group");
$cns = grouplistmembers($config, $filter);
$erreur_user = array("cn" => "erreur", "sexe" => " ", "fullname" => "membre inconnu ! ", "nom" => "|z");
$people = array();
foreach ($cns as $key => $value) {
    $resultat = search_user($config, $value);
    $people[$key] = ((count($resultat) > 0) ? $resultat : $erreur_user);
}
usort($people, "cmp_nom");

// Affiche les membres du groupe
if (count($people)) {
    $intitule = strtr($filter, "_", " ");
    echo "<U>" . gettext("Groupe") . "</U> : $intitule <font size=\"-2\">" . $group[0]["description"] . "</font><BR>\n";
    echo gettext("Il y a ") . count($people) . " membre";
    if (count($people) > 1)
        echo "s";
    echo gettext(" dans ce groupe") . "<BR>\n";
    for ($loop = 0; $loop < count($people); $loop++) {
        if (is_prof($config, $people[$loop]["cn"])) {
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
        if ($people[$loop]["cn"] != "erreur") {
            // Si on a pas les droits on n'a pas de lien
            if (have_right($config, "Annu_is_admin") || have_right($config, "annu_can_read")) {
                echo "<A href=\"people.php?cn=" . $people[$loop]["cn"] . "\">" . $people[$loop]["fullname"] . "</A>";
            }
            else {
                // si on a les droits sovajon_is_admin on v&#233;rifie si on a la classe ou si les droits étendus du groupe prof sont activés
                $cn_eleve = $people[$loop]["cn"];
                $acl_group_profs_classes = exec("cd /var/se3/Classes; /usr/bin/getfacl . | grep group:Profs >/dev/null && echo 1");

                if ((tstclass($login, $cn_eleve) == 1) and ( have_right($config, "sovajon_is_admin") or ( $acl_group_profs_classes == 1)) and ( $people[$loop]["prof"] != 1)) {
                    echo "<A href=\"people.php?cn=" . $people[$loop]["cn"] . "\">" . $people[$loop]["prenom"] . " " . $people[$loop]["name"] . "</A>";
                }
                else {
                    echo $people[$loop]["prenom"] . " " . $people[$loop]["name"];
                }
            }


            if (is_pp_this_classe($config, $people[$loop]["cn"], $filter)) {
                echo "<strong><font size=\"-2\" color=\"#ff8f00\">&nbsp;&nbsp;(" . gettext("professeur principal") . ")</font></strong>";
                $owner = $people[$loop]["cn"];
            }
        }
        else
            echo $people[$loop]["fullname"];
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

if ((have_right($config, "Annu_is_admin")) && ($filter != "Eleves" && $filter != "Profs" && $filter != "Administration" )) {
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
    if (have_right($config, "se3_is_admin")) {
        // Affichage du menu "Deleguer un droit a un groupe"
        echo "<li><a href=\"add_group_right.php?cn=$filter\">" . gettext("G&#233;rer les droits de ce groupe") . "</a></li>\n";
        // ajout par keyser : affichage supplementaire pour les groupes tpe / idd ...
        if (!preg_match("!Equipe|Cours|Classe|Matiere|Administratifs|admins!", "$filter")) {
            echo "<li><a href=\"refresh_grpclass.php?nom_grp=$filter\">" . gettext("Cr&#233;er ou rafraichir une ressource groupe classe(s)") . "</a></li>\n";
        }
    } // Fin Affichage menu droits
    echo "</ul>\n";
}
else if (have_right($config, "se3_is_admin")) {
    // Affichage du menu "Deleguer un droit a un groupe"
    echo "<br><ul style=\"color: red;\">\n";
    echo "<li><a href=\"pop_group.php?filter=$filter\">" . gettext("Envoyer un Pop Up &#224; ce groupe") . "</a></li>\n";
    echo "<li><a href=\"add_group_right.php?cn=$filter\">" . gettext("G&#233;rer les droits de ce groupe") . "</a></li>\n";
    echo "<li><a href=\"grouplist.php?filter=$filter\" target='_new'>" . gettext("Afficher un listing du groupe") . "</a></li>\n";
    echo "<li><a href=\"create_template_group.php?filter=$filter\">" . gettext("Cr&#233;er un dossier de template pour le groupe") . "</a></li>\n";
    echo "</ul>\n";
}

if (have_right($config, "se3_is_admin")) {
    echo "<ul style=\"color: red;\">\n";
    echo "<li><a href=\"create_template_group.php?filter=$filter\">" . gettext("Cr&#233;er un dossier de template pour le groupe") . "</a></li>\n";
    echo "</ul>\n";
}

// ajout du lien trombinoscope
// Si Annu_is_admin et le repertoire existe on peut  voir les trombinoscopes
//
if (have_right($config, "Annu_is_admin") && is_dir("/var/se3/Docs/trombine")) {
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

if (!have_right($config, "Annu_is_admin")) {
    // Si sovajon_is_admin et prof de la classe ou droits étendus du groupe profs
    $acl_group_profs_classes = exec("cd /var/se3/Classes; /usr/bin/getfacl . | grep group:Profs >/dev/null && echo 1");

    if ((have_right($config, "sovajon_is_admin")) and ( (are_you_in_group($login, $classe) or ( $acl_group_profs_classes == 1)))) {

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
    elseif (have_right($config, "annu_can_read")) {
        // Affiche trombinoscope de la classe
        echo "<li><a href=\"trombin.php?filter=$filter\" target='_new'>" . gettext("Afficher un trombinoscope du groupe") . "</a></li>\n";
        echo "</ul>\n";
    }
}

// Affichage de l'equipe pedagogique associee a la classe

if (preg_match("/Classe/", $filter, $matche)) {
    $filter2 = preg_replace("/Classe_/", "Equipe_", $filter);
    $cns2 = grouplistmembers($config, $filter2);
    $people2 = array();
    $erreur_user = array("cn" => "erreur", "sexe" => " ", "fullname" => "membre inconnu ! ", "nom" => "|z");
    foreach ($cns2 as $key => $value) {
        $resultat = search_user($config, $value);
        $people2[$key] = ((count($resultat) > 0) ? $resultat : $erreur_user);
    }
    usort($people2, "cmp_nom");
    if (count($people2)) {
        // affichage des resultats
        echo "<BR><U>" . gettext("Professeurs de la classe") . "</U> : <a href=\"group.php?filter=$filter2\">$filter2</A><BR>\n";
        for ($loop = 0; $loop < count($people2); $loop++) {
            if (is_prof($config, $people2[$loop]["cn"])) {
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
            if ($people2[$loop]["cn"] != "erreur") {
                // On a un lien sur les profs uniquement si on est annu_can_read ou Annu_is_admin
                if ((have_right($config, "Annu_is_admin")) || (have_right($config, "annu_can_read"))) {
                    echo "<A href=\"people.php?cn=" . $people2[$loop]["cn"] . "\">" . $people2[$loop]["fullname"] . "</A>";
                }
                else {
                    echo $people2[$loop]["fullname"];
                }
                if (is_pp_this_classe($config, $people2[$loop]["cn"], $filter)) {
                    echo "<strong><font size=\"-2\" color=\"#ff8f00\">&nbsp;&nbsp;(professeur principal)</font></strong>";
                    $owner = $people2[$loop]["cn"];
                }
            }
            else
                echo $people2[$loop]["fullname"];
            echo "<BR>\n";
        }
    }
}

// Affichage du rebond sur la classe associee a une equipe pedagogique

if (preg_match("/Equipe/", $filter, $matche)) {
    $filter2 = preg_replace("/Equipe_/", "Classe_", $filter);
    $people2 = grouplistmembers($config, $filter2);
    //$people2 = search_people_groups ($cns2,"(sn=*)","cat");
    if (count($people2)) {
        // affichage des resultats
        echo "<BR>" . gettext("Il y a ") . count($people2) . gettext(" &#233;l&#232;ves dans la ") . "<a href=\"group.php?filter=$filter2\">$filter2</A>" . gettext(" associ&#233;e &#224; cette &#233;quipe.") . "\n";
        echo "<BR>\n";
    }
}

include ("pdp.inc.php");
?>