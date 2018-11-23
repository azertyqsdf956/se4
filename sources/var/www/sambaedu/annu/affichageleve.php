<?php

/**

 * Affiche les membres de groupes sélectionnés
 * @Version $Id$
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL
 * @Repertoire: annu
 * file: affichageleve.php
 */
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";
require_once ("lang.inc.php");
bindtextdomain('sambaedu-core', "/var/www/sambaedu/locale");
textdomain('sambaedu-core');

// Aide
$_SESSION["pageaide"] = "Annuaire";

echo "<h1>" . gettext("Annuaire") . "</h1>";

//debug_var();
if (have_right($config, "Annu_is_admin")) {
    $cn = isset($_POST["cn"]) ? $_POST["cn"] : (isset($_GET["cn"]) ? $_GET["cn"] : "");
    $description = ((isset($_POST["description"])) ? $_POST["description"] : "");
    $action = ((isset($_POST["action"])) ? $_POST["action"] : 1);
    $classe_gr = ((isset($_POST["classe_gr"])) ? $_POST["classe_gr"] : array());
    $equipe_gr = ((isset($_POST["equipe_gr"])) ? $_POST["equipe_gr"] : array());
    $autres_gr = ((isset($_POST["autres_gr"])) ? $_POST["autres_gr"] : array());
    $matiere_gr = ((isset($_POST["matiere_gr"])) ? $_POST["matiere_gr"] : array());
    aff_trailer("1");
    for ($loop = 0; $loop < count($classe_gr); $loop++) {
        $filter[$loop] = $classe_gr[$loop];
    }
    $index = $loop;
    for ($loop = 0; $loop < count($equipe_gr); $loop++) {
        $filter[$index + $loop] = $equipe_gr[$loop];
    }
    $index = $index + $loop;
    for ($loop = 0; $loop < count($autres_gr); $loop++) {
        $filter[$index + $loop] = $autres_gr[$loop];
    }
    $index = $index + $loop;
    for ($loop = 0; $loop < count($matiere_gr); $loop++) {
        $filter[$index + $loop] = $matiere_gr[$loop];
    }
    if ($action != '1') {
        // Message d'erreurs de saisie
        if ($cn == "" || $description == "") {
            echo "<div class=error_msg>" . gettext("Vous devez saisir un nom de groupe et une description !") . "</div><br>\n";
            exit();
        }
        elseif (!verifDescription($description)) {
            echo "<div class=error_msg>" . gettext("Le champ description comporte des caract&#232;res interdits !") . "</div><br>\n";
            exit();
        }
        elseif (!verifIntituleGrp($intitule)) {
            echo "<div class=error_msg>" . gettext("Le champ intitul&#233; ne doit pas commencer ou se terminer par l'expresssion : Classe, Equipe ou Matiere !") . "</div><br>\n";
            exit();
        }
        elseif ($filter == "") {
            echo "<div class=error_msg>" . gettext("Vous devez s&#233;lectionner au moins un groupe!") . "</div><br>\n";
            exit();
        }

        // Verification de l'existence du groupe
        $groups = filter_group($config, "(cn=$cn)");
        if (count($groups)) {
            echo "<div class='error_msg'>" . gettext("Attention le groupe <font color='#0080ff'><a href='group.php?filter=$cn' style='color:#0080ff' target='_blank'>$cn</a></font> est d&#233;ja pr&#233;sent dans la base, veuillez choisir un autre nom !") . "</div><BR>\n";
            exit();
        }
        else {
            // Ajout du groupe
            $intitule = enleveaccents($intitule);
            exec("/usr/share/se3/sbin/groupAdd.pl \"1\" $cn \"$description\"", $AllOutPut, $ReturnValue);
            if ($ReturnValue == "0") {
                echo "<div class=error_msg>" . gettext("Le groupe <font color='#0080ff'><a href='group.php?filter=$cn' style='color:#0080ff' target='_blank'>$cn</a></font> a &#233;t&#233; ajout&#233; avec succ&#232;s.") . "</div><br>\n";
            }
            else {
                echo "<div class=error_msg>" . gettext("Echec, le groupe <font color='#0080ff'><a href='group.php?filter=$cn' style='color:#0080ff' target='_blank'>$cn</a></font> n'a pas &#233;t&#233; cr&#233;&#233; !") . "\n";
                if ($ReturnValue)
                    echo "(type d'erreur : $ReturnValue),&nbsp;";
                echo "&nbsp;" . gettext("Veuillez contacter</div> <A HREF='mailto:$MelAdminLCS?subject=PB creation groupe'>l'administrateur du syst&#232;me</A>") . "<BR>\n";
                exit();
            }
        }
    }

    if ((!isset($cn)) || ($cn == "")) {
        echo "<p style='color:red'>Le groupe n'est pas choisi&nbsp;???</p>";
        echo "<p><a href='annu.php'>Retour à l'accueil Annuaire</a></p>";
        include ("pdp.inc.php");
        die();
    }

    echo "<B>" . gettext("S&#233;lectionner les personnes &#224; mettre dans le groupe <a href='group.php?filter=$cn' target='_blank'>$cn</a> :") . "</B><BR>\n";
    echo "<form action=\"constitutiongroupe.php\" method=\"post\">\n";
    echo "<table border=\"0\" cellspacing=\"10\">\n";
    echo "<TR>\n";
    for ($loop = 0; $loop < count($filter); $loop++) {
        echo "<TD>$filter[$loop]</TD>\n";
    }
    echo "</TR>\n";
    echo "<TR>\n";
    for ($filt = 0; $filt < count($filter); $filt++) {
        $gus = array();
        echo "<td valign=\"top\">";
        $gus = search_people_group($config, $filter[$filt]);
        usort($gus, "cmp_nom");
        echo "<select name=\"eleves[]\" size=\"10\"  multiple=multiple>\n";
        for ($loop = 0; $loop < count($gus); $loop++) {
            echo "<option value=" . $gus[$loop]["cn"] . ">" . $gus[$loop]["fullname"] . "</option>\n";
        }
        echo "</select>\n</td>\n";
    }
    echo "</TR>\n";
    echo "</table>\n";
    echo "<BR><BR>\n";
    $CREER_REP = (isset($_POST['CREER_REP']) ? $_POST['CREER_REP'] : "");
    echo "<input type=\"hidden\" name=\"cn\" value=\"$cn\">
      	      <input type=\"hidden\" name=CREER_REP value=\"$CREER_REP\">
      	      <input type=\"submit\" value=\"" . gettext("valider") . "\">
      	      <input type=\"reset\" value=\"" . gettext("R&#233;initialiser la s&#233;lection") . "\">\n";
    echo "</form>\n";
}//fin is_admin
else
    echo gettext("Vous n'avez pas les droits n&#233;cessaires pour ouvrir cette page...");
include ("pdp.inc.php");
?>

