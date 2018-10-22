<?php
/**

 * Ajoute des groupe dans l'annuaire
 * @Version $Id$
 * @Projet LCS / SambaEdu
 * @Auteurs Equipe Sambaedu
 * @Licence Distribue sous la licence GPL

 * @Repertoire: annu
 * file: add_group.php

 */
include "entete.inc.php";
include_once "ldap.inc.php";
include "ihm.inc.php";

$add_group = "";
foreach ($_POST as $cle => $val) {
    $$cle = $val;
}

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/se3/locale");
textdomain('se3-annu');

echo "<h1>" . gettext("Annuaire") . "</h1>\n";
$_SESSION["pageaide"] = "Annuaire";
aff_trailer("6");

if (have_right($config, "Annu_is_admin")) {
    // Ajout d'un groupe d'utilisateurs
    if ((!$add_group) || ( ($add_group) && ( (!$description || !verifDescription($description) ) || (!$intitule || !verifIntituleGrp($intitule)) ) )) {
        ?>
        <form action="add_group.php" method="post">
            <table border="0">
                <tbody>
                    <tr>
                        <td><?php echo gettext("Pr&#233;fix :") ?></td>
                        <td valign="top"><input type="text" name="prefix" size="2">&nbsp;<font color="orange"><u><?php echo gettext("Exemple"); ?></u> : <b>LP, LT</b></font></td>
                    </tr>
                    <tr>
                        <td><?php echo gettext("Cat&#233;gorie :"); ?></td>
                        <td valign="top">
                            <select name="categorie">
                                <option><?php echo gettext("Classe"); ?></option>
                                <option><?php echo gettext("Cours"); ?></option>
                                <option><?php echo gettext("Equipe"); ?></option>
                                <option><?php echo gettext("Matiere"); ?></option>
                                <option><?php echo gettext("Projet"); ?></option>
                                <option><?php echo gettext("Autre"); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo gettext("Intitul&#233; :"); ?></td>
                        <td valign="top"><input type="text" name="intitule" size="20"></td>
                    </tr>
                    <tr>
                        <td><?php echo gettext("Description :"); ?></td>
                        <td valign="top"><input type="text" name="description" size="40"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td >
                            <input type="hidden" name="add_group" value="true">
                            <input type="submit" value=<?php print(gettext("Lancer la requ&#234;te")); ?>>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>


        <?php
        // Message d'erreurs de saisie
        if ($add_group && (!$intitule || !$description)) {
            echo "<div class=error_msg>" . gettext("Vous devez saisir un nom de groupe et une description !") . "</div><br>\n";
        }
        elseif ($add_group && !verifDescription($description)) {
            echo "<div class=error_msg>" . gettext("Le champ description comporte des caract&#232;res interdits !") . "</div><br>\n";
        }
        elseif ($add_group && !verifIntituleGrp($intitule)) {
            echo "<div class=error_msg>" . gettext("Le champ intitul&#233; ne doit pas commencer ou se terminer par l'expresssion : Classe, Equipe ou Matiere !") . "</div><br>\n";
        }
    }
    else {
        $intitule = enleveaccents($intitule);
        // Construction du cn du nouveau groupe
        if ($prefix)
            $prefix = $prefix . "_";
        if ($categorie == "Autre") {
            $categorie = "";
            $typeGroupe = "other_group";
        }
        else {
            $typeGroupe = strtolower($categorie);
            $categorie = $categorie . "_";
        }
        $cn = $categorie . $prefix . $intitule;

        // Verification de l'existance du groupe
        $groups = filter_group($config, "(cn=$cn)");

        if (count($groups)) {
            echo "<div class='error_msg'>" . gettext("Attention le groupe") . " <font color='#0080ff'> <a href='group.php?filter=$cn' style='color:#0080ff' target='_blank'>$cn</a></font>" . gettext(" est d&#233;ja pr&#233;sent dans la base, veuillez choisir un autre nom !") . "</div><BR>\n";
        }
        else {
            // Ajout du groupe
            $description = stripslashes($description);

            if (create_group($config, $prefix . $intitule, $description, $typeGroupe)) {
                if ($categorie == "Classe_") {
                    echo "<div class=error_msg>" . gettext("Le groupe") . " <a href='add_list_users_group.php?cn=" . ucfirst($cn) . "' title=\"Ajouter des membres au groupe\"> " . ucfirst($cn) . " </a> " . gettext(" a &#233;t&#233; ajout&#233; avec succ&#232;s.") . "</div><br>\n";
                }
                else {
                    echo "<div class=error_msg>" . gettext("Le groupe") . " <a href='aj_ssgroup.php?cn=" . ucfirst($cn) . "' title=\"Ajouter des membres au groupe\"> " . ucfirst($cn) . " </a> " . gettext(" a &#233;t&#233; ajout&#233; avec succ&#232;s.") . "</div><br>\n";
                }
            }
            else {
                echo "<div class=error_msg>" . gettext("Echec, le groupe") . " <font color='#0080ff'>" . ucfirst($cn) . "</font>" . gettext(" n'a pas &#233;t&#233; cr&#233;&#233; !") . "\n";

                echo "&nbsp;" . gettext("Veuillez contacter") . "</div> " . gettext("l'administrateur du syst&#232;me") . "</A><BR>\n";
            }
        }
    }
}
else {
    echo "<div class=error_msg>" . gettext("Cette fonctionnalit&#233;, n&#233;cessite les droits d'administrateur du serveur LCS !") . "</div>";
}

include ("pdp.inc.php");
?>
