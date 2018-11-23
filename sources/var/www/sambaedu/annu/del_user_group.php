<?php
/**

 * Supprime les utilisateurs des groupes
 * @Version $Id$
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL
 * @Repertoire: annu
 * file: del_user_group.php
 */
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/se3/locale");
textdomain('se3-annu');

//Aide
$_SESSION["pageaide"] = "Annuaire";

$cn = isset($_POST["cn"]) ? $_POST["cn"] : (isset($_GET["cn"]) ? $_GET["cn"] : "");
$group_del_user = ((isset($_POST["group_del_user"])) ? $_POST['group_del_user'] : "");
$members = ((isset($_POST["members"])) ? $_POST['members'] : '');

echo "<h1>" . gettext("Annuaire") . "</h1>";

if (have_right($config, "Annu_is_admin")) {

    $filter = "8_" . $cn;
    aff_trailer("$filter");
    if ($cn != "Eleves" && $cn != "Profs" && $cn != "Administratifs") {
         $people = search_people_group($config, $cn);
        echo "<h4>" . gettext("Modification des membres du groupe") . " $cn</h4>\n";
        if (!$group_del_user || ( $group_del_user && !count($members) )) {
            ?>
            <form action="del_user_group.php" method="post">
                <p><?php echo gettext("S&#233;lectionnez les membres &#224; supprimer :"); ?></p>
                <p><select size="15" name="<?php echo "members[]"; ?>" multiple="multiple">
                        <?php
                        for ($loop = 0; $loop < count($people); $loop++) {
                            echo "<option value=" . $people[$loop]["cn"] . ">" . $people[$loop]["fullname"];
                        }
                        ?>
                    </select></p>
                <input type="hidden" name="cn" value="<?php echo $cn ?>">
                <input type="hidden" name="group_del_user" value="true">
                <input type="reset" value="<?php echo gettext("R&#233;initialiser la s&#233;lection"); ?>">
                <input type="submit" value="<?php echo gettext("Valider"); ?>">
                </p>
            </form>
            <?php
            // Affichage message d'erreur
            if ($group_del_user && !count($members)) {
                echo "<div class=error_msg>" . gettext("Vous devez s&#233;lectionner au moins un membre &#224; supprimer !") . "</div>\n";
            }
        }
        else {
            // suppression des utilisateurs selectionnes
            for ($loop = 0; $loop < count($members); $loop++) {
                 $ReturnCode = groupdelmember($config, $members[$loop], $cn);
            }

            // Compte rendu de suppression
            if ($ReturnCode) {
                echo "<div class=error_msg>" . gettext("Les membres s&#233;lectionn&#233;s ont &#233;t&#233; supprim&#233; du groupe ") . "<font color='#0080ff'><A href='group.php?filter=$cn'>$cn</A></font>" . gettext(" avec succ&#232;s.") . "</div><br>\n";
            }
            else {
                echo "<div class=error_msg>" . gettext("Echec, les membres s&#233;lectionn&#233;s n'ont pas &#233;t&#233; supprim&#233; du groupe") . "<font color='#0080ff'>$cn</font>";
            }
        }
    }
    else {
        echo "<div class=error_msg>" . gettext("La suppression d'un utilisateur de son  groupe principal (Eleves, Profs, Administratifs) n'est pas autoris&#233;e !") . "</div>";
    }
}
else {
    echo "<div class=error_msg>" . gettext("Cette application, n&#233;cessite les droits d'administrateur du serveur !") . "</div>";
}

include ("pdp.inc.php");
?>
