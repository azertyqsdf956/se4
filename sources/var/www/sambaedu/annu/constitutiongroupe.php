<?php

/**

 * Ajoute des groupe dans l'annuaire
 * @Version $Id$
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL
 * @Repertoire: annu
 * file: constitutiongroupe.php
 */
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('sambaedu-core', "/var/www/sambaedu/locale");
textdomain('sambaedu-core');


if (have_right($config, "Annu_is_admin")) {

    $eleves = $_POST['eleves'];
    $grp = $_POST['cn'];
    $CREER_REP = (isset($_POST['CREER_REP']) ? $_POST['CREER_REP'] : "");

    // Aide
    $_SESSION["pageaide"] = "Annuaire";

    echo "<h1>" . gettext("Annuaire") . "</h1>";

    // Ajout des membres au groupe

    echo "<H4>" . gettext("Ajout des membres au groupe :") . " <A href=\"/annu/group.php?filter=$grp\">$grp</A></H4>\n";
    for ($loop = 0; $loop < count($eleves); $loop++) {
        $nomComplet = search_ad($config, $eleves[$loop], "user");
        echo gettext("Ajout de l'utilisateur ") . "&nbsp;" . $nomComplet[0]["fullname"] . "&nbsp;";
        if (groupaddmember($config, $eleves[$loop], $grp) == 1) {
            echo "<strong>" . gettext("R&#233;ussi") . "</strong><BR>";
        }
        else {
            echo "</strong><font color=\"orange\">" . gettext("Echec") . "</font></strong><BR>";
            $err++;
        }
    }

    // Creation de la ressource groupe classe si besoin

    if ($CREER_REP == "o") {
        exec("sudo /usr/share/se3/scripts/creer_grpclass.sh $cn");
        echo "<BR><BR>";
        echo "<P><B>" . gettext("Cr&#233;ation d'une ressources Groupe Classe(s) ordonnanc&#233;e :") . "</B> <BR><P>";
        echo gettext("Le r&#233;pertoire ") . " <B>Classe_grp_$cn</B> " . gettext("sera cr&#233;&#233; d'ici quelques instants dans ") . " /var/se3/Classe...</B> ";
    }

    include ("pdp.inc.php");
}//fin is_admin
?>
