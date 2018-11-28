<?php

     /**

 * Affiche les membres d'un groupe
 * @Version 11/2018 - keyser
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL
 * @Repertoire: annu
 * file: add_group_right.php
 */

include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se4-core', "/var/www/sambaedu/locale");
textdomain('se4-core');

$_SESSION["pageaide"]="Annuaire";

$cn=isset($_GET['cn']) ? $_GET['cn'] : (isset($_POST['cn']) ? $_POST['cn'] : "");

$action = $_POST['action'] ?? "";
$delrights = $_POST['delrights'] ?? "";
$newrights = $_POST['newrights'] ?? "";

echo "<h1>".gettext("Annuaire")."</h1>\n";

$filtre = "8_".$cn;
aff_trailer ("$filtre");


if (!have_right($config, "se3_is_admin")) {
    echo "Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction";
    include ("pdp.inc.php");
    die();
}

if($cn=="") {
	echo "<p>ERREUR : Il faut choisir un nom</p>\n";
	include ("pdp.inc.php");
	die();
}

$grp = search_group($config, $cn);
// Ajoute un droit
if ($action == "AddRights") {
        // Inscription des droits dans l'annuaire
        echo "<H3>".gettext("Inscription des droits pour")." <U>$cn</U></H3>";
        echo "<P>".gettext("Vous avez s&#233;lectionn&#233; ") ."". count($newrights)."".gettext(" droit(s)")."<BR>\n";
        for ($loop=0; $loop < count($newrights); $loop++) {
                $right=$newrights[$loop];
                echo gettext("D&#233;l&#233;gation du droit")." <U>$right</U> ".gettext("&#224; l'utilisateur")." $cn<BR>";
                add_right($config, $grp['dn'], $right);
                echo "<BR>";
        }
}

// Supprime un droit
if ( $action == "DelRights" ) {
        // Suppression des droits dans l'annuaire
        echo "<H3>".gettext("Suppression des droits pour")." <U>$cn</U></H3>";
        echo "<P>".gettext("Vous avez s&#233;lectionn&#233; ") ."". count($delrights)." droit(s)<BR>\n";
        for ($loop=0; $loop < count($delrights); $loop++) {
                $right=$delrights[$loop];
                echo gettext("Suppression du droit")." <U>$right</U> ".gettext("pour le groupe")." $cn<BR>";
        remove_right($config, $grp['dn'], $right);
                echo "<BR>";
        }
}

$grp = search_group($config, $cn);
//var_dump($grp);
// Affichage du nom et de la description de l'utilisateur
echo "<H3>".gettext("D&#233;l&#233;gation de droits &#224; ")."". $grp["cn"] ." (<U>$cn</U>)</H3>\n";
echo gettext("S&#233;lectionnez les droits &#224; supprimer (liste de gauche) ou &#224; ajouter (liste de droite)");
echo gettext("et validez &#224; l'aide du bouton correspondant.")."<BR><BR>\n";

// Lecture des droits disponibles
$list_rights = list_rights($config, $config['login']);

$list_possible_rights = $list_rights;
//$list_possible_rights = list_rights($config, $cn, true);
//$list_current_rights = $grp['memberof'];
$list_current_rights= array();
if (isset($grp['memberof'])) {
    foreach ($grp['memberof'] as $right_dn) {
        $list_current_rights[] = ldap_dn2cn($right_dn);
    }
}
//var_dump($list_current_rights);

$list_possible_rights = array_diff($list_rights, $list_current_rights);
        
?>

<FORM method="post" action="../annu/add_group_right.php">
<INPUT TYPE="hidden" VALUE="<?php echo $cn;?>" NAME="cn">
<INPUT TYPE="hidden" NAME="action">
<TABLE BORDER=1 CELLPADDING=3 CELLSPACING=1 RULES=COLS><TR>
<TH align=center><?php echo gettext("Droits actuels "); ?>

<u onmouseover="this.T_SHADOWWIDTH=5;this.T_STICKY=1;return escape<?php echo gettext("('Les droits indiqu&#233;s dans cette liste sont les droits effectifs.<br>Tous les membres de ce groupe disposeront de ces droits.')"); ?>"><img name="action_image2"  src="../elements/images/system-help.png" alt="Help"></u>
<TH align="center"><?php echo gettext("Droits disponibles"); ?>
<u onmouseover="this.T_SHADOWWIDTH=5;this.T_STICKY=1;return escape<?php echo gettext("('<b>se3_is_admin</b> Donne le droit d\'administration sur tout le syst&#232;me. Ce droit l\'emporte sur tous les autres.<BR><b>Annu_is_admin</b> Donne tous les droits sur l\'annuaire (Ajouter, supprimer, modifier des utilisateurs ou des groupes).<BR><b>sovajon_is_admin</b> D&#233;l&#233;gue le droit de changer les mots de passe &#224; un professeur. Il faut que celui-ci soit professeur de la classe.<BR><b>system_is_admin</b> Donne le droit de visualiser les informations syst&#232;me du serveur.<BR><b>computers_is_admin</b> Permet de g&#233;rer les machines clientes (Cr&#233;er ou supprimer des machines des parcs, &#233;tat des machines clientes...)<BR><b>printers_is_admin</b> Gestion des files d\'impression des imprimantes.<BR><b>echange_can_administrate</b> Permet de g&#233;rer les r&#233;pertoires _echanges dans les r&#233;pertoires classes.<BR><b>inventaire_can_read</B> Permet de consulter l\'inventaire<BR><b>annu_can_read</b> Permet de consulter l\'annuaire. Par d&#233;faut les membres du groupe Profs ont ce droit.<BR><b>maintenance_can_write</b> Permet de d&#233;clarer une panne sur une machine dans l\'interface de maintenance.<BR><b>parc_can_view</b> Permet de voir les parcs.<BR><b>parc_can_manage</b> Permet de d&#233;l&#233;guer la gestion d\'un parc &#224; une personne.<BR><b>smbweb_is_open</b> Donne le droit d\'acc&#232;s depuis l\'interface smbwebclient du Slis ou du Lcs (optionnel).')"); ?>"><img name="action_image2"  src="../elements/images/system-help.png" alt="Help"></u>

</TH></TR>
<TR><TD VALIGN="TOP">

<?php

if   ( count($list_current_rights)>15) $size=15; else $size=count($list_current_rights);
if ( $size>0) {
        echo "<SELECT NAME=\"delrights[]\" SIZE=\"$size\" multiple=\"multiple\">";
        for ($loop=0; $loop < count($list_current_rights); $loop++) {
                echo "<option value=".$list_current_rights[$loop].">".$list_current_rights[$loop]."\n";
        }
        ?>

        </SELECT><BR><BR>
        <input type="submit" value="Retirer ces droits" onClick="this.form.action.value ='DelRights';return true;">
        <?php
} else {
        echo "<U>$cn</U> ".gettext("n'a aucun droit propre");
}
?>
</TD><TD VALIGN="TOP">
<?php
if   ( count($list_possible_rights)>15) $size=15; else $size=count($list_possible_rights);
if ( $size>0) {
        echo "<SELECT NAME=\"newrights[]\" SIZE=\"$size\" multiple=\"multiple\">";
        foreach ($list_possible_rights as $right) {
                echo "<option value=".$right.">".$right."\n";
        }
        ?>
        </SELECT><BR><BR>
        <input type="submit" value="<?php echo gettext("Ajouter ces droits"); ?>" onClick="this.form.action.value ='AddRights';return true;">
        <?php
} else {
        echo "<U>$cn</U>".gettext(" a tous les droits");
}
?>
</TD></TR></TABLE>
</FORM>
<?php

include ("pdp.inc.php");
?>
