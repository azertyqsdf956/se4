<?php

/**

   * Ajoute des groupe dans l'annuaire
   * @Version $Id$
   * @Projet LCS / SambaEdu
   * @Auteurs Equipe Sambaedu
   * @Licence Distribue sous la licence GPL

   * @Repertoire: annu
   * file: aj_ssgroup.php
   */


include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu',"/var/www/se3/locale");
textdomain ('se3-annu');

//Aide
$_SESSION["pageaide"]="Annuaire";
$classe_gr=$equipe_gr=$matiere_gr=$autres_gr="";
echo "<h1>".gettext("Annuaire")."</h1>";

if (have_right($config, "Annu_is_admin")) {
	$cn=((isset($_GET["cn"]))?$_GET["cn"]:"");
	$description=(isset($_GET["description"])? $_GET["description"]:"");
	echo "<form action=\"affichageleve.php\" method=\"post\">";
	echo "<B>".gettext("S&#233;lectionner le(s) groupe(s) dans le(s)quel(s) se situent les personnes &#224; mettre dans le groupe :")." </B><BR><BR>";

?>
<table border="0" cellspacing="10">
<tr>
<td><?php echo gettext("Classes"); ?></td>
<td><?php echo gettext("Equipes"); ?></td>
<td><?php echo gettext("Mati&#232;res"); ?></td>
<td><?php echo gettext("Autres"); ?></td>
</tr>
<tr>
<td valign="top">
<?php
$action='1';
echo "<select name= \"classe_gr[]\" value=\"$classe_gr\" size=\"10\" multiple=\"multiple\">\n";
    $list_classes=search_ad($config, "*", "classe") ;
    for ($loop=0; $loop < count ($list_classes) ; $loop++) {
	echo "<option value=".$list_classes[$loop]["cn"].">".$list_classes[$loop]["cn"];
    }
    echo "</select>";
    echo "</td>";

    echo "<td>\n";
    echo "<select name= \"equipe_gr[]\" value=\"$equipe_gr\" size=\"10\" multiple=\"multiple\">\n";
    $list_equipes=search_ad($config, "*", "equipe") ;
    for ($loop=0; $loop < count ($list_equipes) ; $loop++) {
	echo "<option value=".$list_equipes[$loop]["cn"].">".$list_equipes[$loop]["cn"];
    }
    echo "</select></td>\n";

    echo "<td valign=\"top\">
    <select name=\"autres_gr[]\" value=\"$autres_gr\" size=\"10\" multiple=\"multiple\">";
    $list_autres=search_ad($config, "*", "projet") ;
    for ($loop=0; $loop < count ($list_autres) ; $loop++) {
	echo "<option value=".$list_autres[$loop]["cn"].">".$list_autres[$loop]["cn"];
    }
    $list_autres=search_ad($config, "*", "autre") ;
    for ($loop=0; $loop < count ($list_autres) ; $loop++) {
	echo "<option value=".$list_autres[$loop]["cn"].">".$list_autres[$loop]["cn"];


    }
    echo "<td>\n";
    echo "<select name=\"matiere_gr[]\" value=\"$matiere_gr\" size=\"10\" multiple=\"multiple\">";
    $list_matieres=search_ad($config, "*", "matiere") ;
    for ($loop=0; $loop < count ($list_matieres) ; $loop++) {
	echo "<option value=".$list_matieres[$loop]["cn"].">".$list_matieres[$loop]["cn"];
    }


    echo "</select></td></tr></table>"; ?>
    <input type="submit" value="<?php echo gettext("valider");?>">
    <input type="reset" value="<?php echo gettext("R&#233;initialiser la s&#233;lection");?>">
    <input type="hidden" name="cn" value=<?php echo $cn ?> >
    <input type="hidden" name="description" value=<?php echo $description ?> >
    <input type="hidden" name="action" value=<?php echo $action ?> >
    <?php
    echo "</form></small>";



}//fin is_admin
else echo gettext("Vous n'avez pas les droits n&#233;cessaires pour ouvrir cette page...");
include ("pdp.inc.php");
?>
