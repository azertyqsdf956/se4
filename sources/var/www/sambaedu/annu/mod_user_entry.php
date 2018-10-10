<?php

/**

 * Modifie l'entree d'un utilisateur
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
 * file: mod_user_entry.php
 */
require "entete.inc.php";
require_once 'ldap.inc.php';
require "ihm.inc.php";
require "jlcipher.inc.php";

require_once "siecle.inc.php";

// HTMLPurifier
require_once ("traitement_data.inc.php");

$login = isauth();
// if ($login != "") {

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/se3/locale");
textdomain('se3-annu');

header_crypto_html(gettext("Modification parametres utilisateur"), "../");

// Aide
@session_start();
$_SESSION["pageaide"] = "Annuaire#Modifier_mon_compte";

echo "<h1>" . gettext("Annuaire") . "</h1>\n";

aff_trailer("4");

$isadmin = have_right($config, "Annu_is_admin");

$cn = isset($_GET['cn']) ? $_GET['cn'] : (isset($_POST['cn']) ? $_POST['cn'] : NULL);

if (! isset($cn)) {
    echo "<p style='color:red'>Erreur&nbsp;: Aucun utilisateur n'a &#233;t&#233; choisi.</p>\n", include ("pdp.inc.php");
    die();
}

// debug_var();
$corriger_givenname_si_diff = "Y";
$user_entry = isset($_POST['user_entry']) ? $_POST['user_entry'] : '';
$nom = isset($_POST['nom']) ? $_POST['nom'] : '';
$prenom = isset($_POST['prenom']) ? $_POST['prenom'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$userpwd = isset($_POST['userpwd']) ? $_POST['userpwd'] : '';
$mail = isset($_POST['mail']) ? $_POST['mail'] : '';
$string_auth = isset($_POST['string_auth']) ? $_POST['string_auth'] : '';

$naissance = isset($_POST['naissance']) ? $_POST['naissance'] : '';
$employeeNumber = isset($_POST['employeeNumber']) ? $_POST['employeeNumber'] : '';

if (have_right($config, "se3 is_admin") or ((is_my_eleve($config, $login, $cn)) and (have_right($config, "sovajon_is_admin")))) {
    // Recuperation des entrees de l'utilisateur a modifier
    $user = search_user($config, $cn);

    // decodage du mot de passe
    if ((isset($user_entry)) && ($user_entry) && ($string_auth != '')) {
        // decryptage des mdp
        exec("/usr/bin/python /var/www/sambaedu/includes/decode.py '" . $string_auth . " '", $Res);
        $userpwd = $Res[0];
    }
    $info_employeeNumber = "";
    if ($employeeNumber != '') {
        $tmp_tab = verif_employeeNumber($config, $employeeNumber);
        if (count($tmp_tab) > 0) {
            if ($tmp_tab['cn'] != $cn) {
                $info_employeeNumber = "Le num&#233;ro <b>$employeeNumber</b> est d&#233;j&#225; attribu&#233; &#225; <a href='" . $_SERVER['PHP_SELF'] . "?cn=" . $tmp_tab[0] . "'>" . $tmp_tab[0] . "</a> dans la branche <b>" . $tmp_tab[- 1] . "</b><br />";
            }
        }
    }

    $employeeNumber0 = $employeeNumber;
    $employeeNumber = preg_replace("/[^0-9A-Za-z]/", "", $employeeNumber);

    if ($employeeNumber != $employeeNumber0) {
        $info_employeeNumber .= "Un ou des caract&#232;res non valides ont &#233;t&#233; saisis dans le num&#233;ro '<b>$employeeNumber0</b>'";
    }

    if (! isset($user_entry)  || ! verifEntree($nom) || ! verifEntree($prenom) || ! verifDescription($description) || ($userpwd && ! verifPwd($userpwd)) || (($naissance != '') && (! verifDateNaissance($naissance))) || ($info_employeeNumber != "")) {
        // Quand la migration givenName<-Prenom et seeAlso<-pseudo sera effectuee, on pourra modifier ci-dessous:
        // $user[0]["prenom"]=getprenom($user[0]["fullname"],$user[0]["nom"]);
        ?>
<form name="auth" action="mod_user_entry.php" method="post"
	onSubmit="encrypt(document.auth)">
	<table align="center" border="0" width="90%">
		<tbody>
			<tr>
				<td width="27%">Login :&nbsp;</td>
				<td width="73%" colspan="2"><tt>
						<strong><?php echo $user["cn"]?></strong>
					</tt></td>
			</tr>
			<tr>
				<td width="27%"><?php echo gettext("Prénom"); ?> :&nbsp;</td>
				<td width="73%" colspan="2"><input type="text" name="prenom"
					value="<?php echo $user["prenom"]?>" size="20"></td>
			</tr>
			<tr>
				<td><?php echo gettext("Nom"); ?>&nbsp;:&nbsp;</td>
				<td colspan="2"><input type="text" name="nom"
					value="<?php echo $user["nom"]?>" size="20"></td>
			</tr>

			<?php
        if ($isadmin) {
            if (isset($user['naissance'])) {
                $naissance = $user['naissance'];
            } else {
                $naissance = "";
            }

            if (isset($user["employeenumber"])) {
                $employeeNumber = $user["employeenumber"];
            }
            $user['sexe'] = $user['sexe'] ?? "M";
            ?>

			<tr>
				<td><?php echo gettext("Date de naissance"); ?>&nbsp;:&nbsp;</td>
				<td colspan="2"><input type="text" name="naissance"
					value="<?php echo $naissance?>" size="20"></td>
			</tr>

			<tr>
				<td valign='top'><?php echo gettext("Numero"); ?>&nbsp;:&nbsp;</td>
				<td valign='top'><input type="text" name="employeeNumber"
					value="<?php echo $employeeNumber?>" size="20"></td>
				<td><font color="orange"> <u><?php echo gettext("Attention"); ?></u> :<?php echo gettext(" Le num&#233;ro correspond &#225; l'attribut 'employeeNumber' dans l'annuaire LDAP.<br />C'est ce num&#233;ro qui est utilis&#233; lors d'un import des comptes pour d&#233;terminer si le compte existe d&#233;j&#225; ou non.<br />Ne le changez pas sans bonne raison."); ?>
					</font></td>
			</tr>

			<tr>
				<td><?php echo gettext("Adresse m&#232;l"); ?>&nbsp;:&nbsp;</td>
				<td colspan="2"><input type="text" name="mail"
					value="<?php echo $user["email"]?>" size="20"></td>
			</tr>

				<?php } ?>
			<tr>
				<td><?php echo gettext("Mot de passe"); ?>:&nbsp;</td>
				<td><input type="password" value="" name="dummy" size='20'
					maxlength='20'> <input type="hidden" name="string_auth" value=""></td>
				<td><font color="orange"> <u><?php echo gettext("Attention"); ?></u> : <?php echo gettext("Si vous laissez ce champ vide,&nbsp;c'est l'ancien mot de passe qui sera conserv&#233;."); ?>
					</font></td>
			</tr>
			<tr>
				<td></td>
				<td align="left"><input type="hidden" name="cn"
					value="<?php echo $cn ?>"> <input type="hidden" name="user_entry"
					value="true"> <input type="submit"
					value="<?php echo gettext("Lancer la requ&#234;te"); ?>"></td>
			</tr>
		</tbody>
	</table>
</form>
<?php
        crypto_nav("../");
        if ((isset($user_entry)) && ($user_entry)) {
            // verification des saisies
            // nom prenom
            if (! verifEntree($nom) || ! verifEntree($prenom)) {
                echo "<div class=\"error_msg\">" . gettext("Les champs nom et prenom, doivent comporter au minimum 3 caract&#232;res alphab&#233;tiques.") . "</div><br />\n";
            }
            // profil
            if ($description && ! verifDescription($description)) {
                echo "<div class=\"error_msg\">" . gettext("Veuillez reformuler le champ description.") . "</div><br />\n";
            }
            // Date de naissance
            if ($naissance != '' && ! verifDateNaissance($naissance)) {
                echo "<div class=\"error_msg\">" . gettext("La date de naissance que vous avez saisie, n'est pas conforme.") . "</div><br />\n";
            }

            // mot de passe
            if ($userpwd && ! verifPwd($userpwd)) {
                echo "<div class='error_msg'>";
                echo gettext("Vous devez proposer un mot de passe d'une longueur comprise entre 4 et 8 caract&#232;res
					alphanum&#233;riques avec obligatoirement un des caract&#232;res sp&#233;ciaux suivants");
                echo " ($char_spec) </div><br />\n";
            }

            if ($info_employeeNumber != "") {
                echo "<div class=\"error_msg\">" . gettext("$info_employeeNumber.") . "</div><br />\n";
            }

            // fin verification des saisies
        }

        echo "<p><a href='people.php?cn=" . $cn . "'>Retour sans modification vers la fiche de $cn</a></p>\n";
    } else {

        // Positionnement des entrees a modifier
        // $entry["sn"] = stripslashes ( utf8_encode($nom) );
        // $entry["cn"] = stripslashes ( utf8_encode($prenom)." ".utf8_encode($nom) );
        $entry["sn"] = stripslashes(ucfirst(strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$nom")))), "'ÂÄÀÁÃÄÅÇÊËÈÉÎÏÌÍÑÔÖÒÓÕ¦ÛÜÙÚÝ¾´áàâäãåçéèêëîïìíñôöðòóõ¨ûüùúýÿ¸", "_AAAAAAACEEEEIIIINOOOOOSUUUUYYZaaaaaaceeeeiiiinoooooosuuuuyyz"))));
        $entry["displayname"] = stripslashes(ucfirst(strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$prenom")))), "'ÂÄÀÁÃÄÅÇÊËÈÉÎÏÌÍÑÔÖÒÓÕ¦ÛÜÙÚÝ¾´áàâäãåçéèêëîïìíñôöðòóõ¨ûüùúýÿ¸", "_AAAAAAACEEEEIIIINOOOOOSUUUUYYZaaaaaaceeeeiiiinoooooosuuuuyyz"))) . " " . ucfirst(strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$nom")))), "'ÂÄÀÁÃÄÅÇÊËÈÉÎÏÌÍÑÔÖÒÓÕ¦ÛÜÙÚÝ¾´áàâäãåçéèêëîïìíñôöðòóõ¨ûüùúýÿ¸", "_AAAAAAACEEEEIIIINOOOOOSUUUUYYZaaaaaaceeeeiiiinoooooosuuuuyyz"))));

        // ======================================
        if ($corriger_givenname_si_diff == "y") {
            // Le givenName est destiné à prendre pour valeur le Prenom de l'utilisateur
            $entry["givenname"] = ucfirst(strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$prenom")))), "'ÂÄÀÁÃÄÅÇÊËÈÉÎÏÌÍÑÔÖÒÓÕ¦ÛÜÙÚÝ¾´áàâäãåçéèêëîïìíñôöðòóõ¨ûüùúýÿ¸", "_AAAAAAACEEEEIIIINOOOOOSUUUUYYZaaaaaaceeeeiiiinoooooosuuuuyyz")));
        }

        if ($isadmin) {
            if ($mail != "")
                $entry["mail"] = $mail;
            if ($description && verifDescription($description))
                $entry["description"] = utf8_encode(stripslashes($description));

            if ($naissance != '' && verifDateNaissance($naissance)) {
                if (isset($user["sexe"])) {
                    $entry['physicaldeliveryofficename'] = $naissance . "," . $user['sexe'];
                }
            }

            if ($employeeNumber != "") {
                $entry["title"] = $employeeNumber;
            }
        }
        // Modification des entrees
        $res = modify_ad($config, $user['cn'], "user", $entry);
        if ($res) {
            echo "<strong>" . gettext("Les entr&#233;es ont &#233;t&#233; modifi&#233;es avec succ&#232;s.") . "</strong><br />\n";
        } else {
            echo "<strong>" . gettext("Echec de la modification, veuillez contacter") . " </strong><A HREF='mailto:$MelAdminLCS?subject=PB modification entrees utilisateur'>" . gettext("l'administrateur du syst&#232;me") . "</A><br />\n";
        }

        // Fin modification des entrees
        // Changement du mot de passe
        if ($userpwd && verifPwd($userpwd)) {
            usersetpassword($config, $cn, $userpwd);
        }

        echo "<p><a href='" . $_SERVER['PHP_SELF'] . "?cn=" . $cn . "'>Retour vers la modification des informations $cn</a></p>\n";
        echo "<p><a href='people.php?cn=" . $cn . "'>Retour vers la fiche de $cn</a></p>\n";
    }
} else {
    echo "<div class=error_msg>" . gettext("Cette fonctionnalit&#233; n&#233;cessite des droits d'administration SambaEdu !") . "</div>";
}

include ("pdp.inc.php");
?>
