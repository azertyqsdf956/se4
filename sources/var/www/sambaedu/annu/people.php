<?php

/**

 * Affiche les utilisateur a partir de l'annuaire
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
 * file: people.php
 */
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/se3/locale");
textdomain('se3-annu');

// Aide
$_SESSION["pageaide"] = "Annuaire#Voir_ma_fiche";

echo "<h1>" . gettext("Annuaire") . "</h1>\n";

$cn = isset($_GET['cn']) ? $_GET['cn'] : "";

if ($cn == '') {
    echo "<p style='color:red;'>ERREUR: cn non choisi.</p>";
    include ("pdp.inc.php");
    die();
}

aff_trailer("3");
// $TimeStamp_0=microtime();
// correctif provisoire
if (isset($user)) {
    $user_tmp = $user;
    // fin correctif
}

$user = search_user($config, $cn, true);
$dngroups = $user['memberof'];

echo "<a href='people.php?cn=" . $user["cn"] . "' title=\"Rafraichir la page\"><H3>" . $user["fullname"] . "</H3></a>\n";

if ((have_right($config, "Annu_is_admin")) && (isset($_GET['create_home'])) && ($_GET['create_home'] == 'y')) {
    echo "<p><b>Cr&#233;ation du dossier personnel de " . $user["cn"] . "&nbsp;: </b>";
    exec("sudo /usr/share/se3/shares/shares.avail/mkhome.sh " . $user["cn"], $ReturnValue2);
    if (count($ReturnValue2) == 0) {
        echo "<span style='color:green'>OK</span></p>";
    } else {
        echo "</p><pre style='color:red'>";
        foreach ($ReturnValue2 as $key => $value) {
            echo "$value";
        }
    }
    echo "</pre>";
}

echo "<table width=\"80%\"><tr><td>";
if (isset($user["description"]))
    echo "<p>" . $user["description"] . "</p>";
if (count($dngroups)) {
    echo "<U>Membre des groupes</U> :<BR><UL>\n";
    for ($loop = 0; $loop < count($dngroups); $loop ++) {
        $group = search_ad($config, $dngroups[$loop], "group");
        // Si les bons droits on place un lien sur les groupes
        echo "<LI>";
        if ((have_right($config, "annu_can_read")) or (have_right($config, "Annu_is_admin")) or (have_right($config, "sovajon_is_admin"))) {
            echo "<A href=\"group.php?filter=" . $group[0]['cn'] . "\">";
        }
        echo $group[0]['cn'];
        if ((have_right($config, "annu_can_read")) or (have_right($config, "Annu_is_admin")) or (have_right($config, "sovajon_is_admin"))) {
            echo "</A>";
        }
        echo "<font size=\"-2\"> " . $group[0]["description"];
        if (is_my_pp($config, $group[0]['cn'], $config['login']))
            echo "<strong><font color=\"#ff8f00\">&nbsp;(" . gettext("professeur principal") . ")</font></strong>";
        echo "</font></LI>\n";
        echo "</font></li>";

        if (have_right($config, "Annu_is_admin")) {
            ?>
        			&nbsp;&nbsp;&nbsp;&nbsp;
<a
	href="del_user_group_direct.php?cn=<?php echo $user["cn"]?>&cn=<?php echo $group[0]["cn"] ?>"
	onclick="return getconfirm();"><font size="2"><?php echo gettext("retirer du groupe"); ?></a>
</font>
<br>
<?php
            // R&#233;cup&#233;ration de tous les groupes de l'utilisateur
            if (isset($groups[0]['cn'])) {
                // A quoi sert ce $cn ???
                if (! isset($cn)) {
                    $cn = "";
                }
                $cn = $cn . "&cn" . $loop . "=" . $group[0]['cn'];
            }
        }

        // fin modif
    }
    echo "</UL>";
}
// echo "<br>Pages perso : <a href=\"../~".$user["cn"]."/\"><tt>".$baseurl."~".$user["cn"]."</tt></a><br>\n";
// echo "Adresse m&#232;l : <a href=\"mailto:".$user["email"]."\"><tt>mailto:".$user["email"]."</a></tt><br>\n";
// modif propos&#233;e par MC Marques
if (have_right($config, "Annu_is_admin")) {
    ?>
<ul style="color: red;">
	<li><a href="add_user_group.php?cn=<?php echo $user["cn"] ?>"><?php echo gettext("Ajouter &agrave; des groupes"); ?></a><br>
	
	<li><a href="del_group_user.php?cn=<?php echo $user["cn"] ?>"><?php echo gettext("Enlever de certains groupes"); ?></a><br>

</ul>
<?php
}
// fin modifs

echo gettext("Adresse m&#233;l") . " : <a href=\"mailto:" . $user["email"] . "\"><tt>" . $user["email"] . "</a></tt><br>\n";

// Affichage Menu people_admin
if (have_right($config, "Annu_is_admin")) {
    echo "
	<br>
	<u>" . gettext("Autres actions possibles") . "</u>&nbsp;: <br />
	<ul style=\"color: red;\">
		<li><a href=\"mod_user_entry.php?cn=" . $user['cn'] . "\">" . gettext('Modifier le compte') . "</a></li>
		<li><a href=\"pass_user_init.php?cn=" . $user['cn'] . "\">" . gettext('Réinitialiser le mot de passe') . "</a></li>";

    if ($user['useraccountcontrol'] == 512) {
        echo "
		<li><a href=\"desac_user_entry.php?cn=" . $user["cn"] . "\" onclick= return getconfirm()>" . gettext("D&#233;sactiver ce compte") . " </a><br />";
        if (file_exists('/home/' . $user["cn"])) {
            echo "
		<li><span style='color:black'>Le dossier personnel existe.</span></li>";
        } else {
            echo "
		<li>Le dossier personnel n'existe pas.<br /><a href='people.php?cn=" . $user["cn"] . "&amp;create_home=y'>Cr&#233;er le dossier personnel maintenant</a><br />(<em>sinon, il sera cr&#233;&#233; lors de la premiere connexion de l'utilisateur</em>)</li>";
        }
    } else {
        // si compte desactive
        echo "
		<li><a href=\"desac_user_entry.php?cn=" . $user["cn"] . "&action=activ\" >" . gettext("Activer ce compte") . " </a><br>\n";
        if (! file_exists('/home/' . $user["cn"])) {
            echo "
		<li>Le dossier personnel n'existe pas.<br /><a href='people.php?cn=" . $user["cn"] . "&amp;create_home=y'>Cr&#233;er le dossier personnel maintenant</a><br />(<em>sinon, il sera cr&#233;&#233; lors de la premiere connexion de l'utilisateur</em>)</li>";
        }
    }
    ?><li><a href="del_user.php?cn=<?php echo $user["cn"] ?>"
	onclick="return getconfirm();"><?php echo gettext("Supprimer le compte"); ?></a><br>
<li><a href="del_nt_profile.php?cn=<?php echo $user["cn"] ?>&action=del"
	onclick="return getconfirm();"><?php echo gettext("Reg&#233;n&#233;rer le profil errant Windows"); ?></a><br>
		<?php
/*
    exec("/usr/share/se3/sbin/getUserProfileInfo.pl $user[cn]", $AllOutPut, $ReturnValue);

    if ($AllOutPut[0] == "lock") {
        echo "
		<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=unlock\">" . gettext("D&#233;verrouiller le profil Windows...") . "</a><br>\n";
    } else {
        echo "
		<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=lock\">" . gettext("Verrouiller le profil Windows...") . "</a><br>\n";
    }
*/    
    ?>

		


<li><a href="pop_user.php?cn=<?php echo $user["cn"] ?>"><?php echo gettext("Envoyer un Pop Up"); ?></a><br>

	<!--li><a href="html/AdminUserBdd.html">Ouvrir la base de donne&eacute;es</a><br-->
	<!--<li><a href="html/AdminUserWeb.html">Activer l'espace <em>Web</em></a>-->
		<?php
} // Fin affichage menu people_admin

if (have_right($config, "se3_is_admin")) {
    echo "<li><a href=\"add_user_right.php?cn=" . $user["cn"] . "\">" . gettext("G&#233;rer les droits") . "</a><br>";
    echo "<li><a href=\"../parcs/show_histo.php?selectionne=3&amp;user=$cn\">" . gettext("Voir les connexions") . "</a><br>"; // Ajout leb
}
echo "</ul>";

// Test de l'appartenance a la classe pour le droit sovajon_is_admin
// Afin d'eviter les doublons si le mec est admin_is_admin il ne peut pas
// voir cette partie puisqu'il peut la voir par ailleurs

// si les droits étendus du groupe profs sont activés, le test sur la classe n'est pas nécessaire
$acl_group_profs_classes = exec("cd /var/se3/Classes; /usr/bin/getfacl . | grep group:Profs >/dev/null && echo 1");

if (! have_right($config, "Annu_is_admin")) {
    if ((is_my_eleve($config, $config['login'], $user["cn"])) or ($acl_group_profs_classes == 1) and (have_right($config, "sovajon_is_admin")) and ($login != $user["cn"])) {
        // On teste si $user[cn] n'est pas un prof
        if (is_eleve($config, $user["cn"])) {
            echo "<br>\n";
            echo "<ul style=\"color: red;\">\n";

            echo "<li><a href=\"pass_user_init.php?cn=" . $user["cn"] . "\">" . gettext("R&#233;initialiser le mot de passe") . "</a><br>";
            echo "<li><a href=\"mod_user_entry.php?cn=" . $user["cn"] . "\">" . gettext("Modifier le compte de mon &#233;l&#232;ve ...") . "</a><br>\n";
            $test_desac = search_ad($config, $user["cn"], "user");
            if ($test_desac[0]['useraccountcontrol'] == 512) {
                // si compte active
                echo "<li><a href=\"desac_user_entry.php?cn=" . $user["cn"] . "\" onclick= return getconfirm()>" . gettext("D&#233;sactiver ce compte") . " </a><br>\n";
            } else {
                // si compte desactive
                echo "<li><a href=\"desac_user_entry.php?cn=" . $user["cn"] . "&action=activ\" >" . gettext("Activer ce compte") . " </a><br>\n";
            }

            echo "<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=del\">" . gettext("Reg&#233;n&#233;rer le profil Windows de mon &#233;l&#232;ve...") . "</a><br>\n";

            exec("/usr/share/se3/sbin/getUserProfileInfo.pl $user[cn]", $AllOutPut, $ReturnValue);
            if ($AllOutPut[0] == "lock") {
                echo "<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=unlock\">" . gettext("D&#233;verrouiller le profil Windows...") . "</a><br>\n";
            } else {
                echo "<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=lock\">" . gettext("Verrouiller le profil Windows...") . "</a><br>\n";
            }
            echo "</ul>\n";
        } // Fin test si prof
    }
}

// test du cas ou on veut modifier son propre compte
if ($login == $user["cn"]) {
    echo "<br>\n";
    echo "<ul style=\"color: red;\">\n";
    echo "<li><A HREF=\"../parcs/show_histo.php?selectionne=3&user=" . $user["cn"] . "\">" . gettext("Voir mes connexions") . "</A>";
    echo "<li><A HREF=\"../infos/du.php?wrep=/home/$login&cn=$login\">" . gettext("Espace occup&#233; par mon Home") . "</A>";
    echo "<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=del\">" . gettext("Regenerer mon profil Windows...") . "</a><br>\n";
    exec("/usr/share/se3/sbin/getUserProfileInfo.pl $user[cn]", $AllOutPut, $ReturnValue);
    if ($AllOutPut[0] == "lock") {
        echo "<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=unlock\">" . gettext("D&#233;verrouiller mon profil Windows...") . "</a><br>\n";
    } else {
        echo "<li><a href=\"del_nt_profile.php?cn=" . $user["cn"] . "&action=lock\">" . gettext("Verrouiller mon profil Windows...") . "</a><br>\n";
    }

    if (have_right($config, "fond_can_change")) {
        echo "<li><a href=\"../fond_ecran/fond_perso.php\">" . gettext("Personnaliser mon fond d'&#233;cran") . "</a><br>\n";
    }

    echo "</ul>\n";
}

// modif proposee par Laurent COOPER
if ((is_prof($config, $user["cn"])) && file_exists("/var/www/se3/annu/teacher.php")) {
    echo " \n <ul style=\"color:red;\"> \n <li> <a href=\"teacher.php\"> Mes services </a> </li> </ul>\n";
}

// Affichage des photos si presence du trombinoscope
$tab_type = array(
    "gif",
    "png",
    "jpg",
    "jepg"
);
echo "</td><td align=\"left\" valign=\"top\">";
for ($j = 0; $j < count($tab_type); $j ++) {
    $photo = "/var/se3/Docs/trombine/" . $user["cn"] . "." . $tab_type[$j];
    // Supprime le 0 devant s'il existe
    $employeenumber = $user['employeenumber'] ?? "";
    $employeeNumber_gepi = preg_replace('/^[0]/', '', $employeenumber);
    if (! isset($rep_trombine)) {
        $rep_trombine = "";
    } // A quoi sert le $rep_trombine ; Il a l'air de n'etre jamais initialise
    $photo_employeeNumber = "$rep_trombine" . "$employeeNumber_gepi" . "." . $tab_type[$j];

    if (file_exists("$photo")) {
        echo "<IMG src=\"trombine/" . $user["cn"] . "." . $tab_type[$j] . " \" width=\"70\" height=\"90\" alt=\"$employeeNumber_gepi\">";
    } elseif (file_exists("/var/se3/Docs/trombine/$photo_employeeNumber")) {
        echo "<IMG src=\"trombine/" . $employeeNumber_gepi . "." . $tab_type[$j] . " \" width=\"70\" height=\"90\" alt=\"$employeeNumber_gepi\">";
    }
}

echo "</td></tr></table>";

include ("pdp.inc.php");
?>
