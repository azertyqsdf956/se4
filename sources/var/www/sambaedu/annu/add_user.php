<?php

/**

 * Ajoute des utilisateurs dans l'annuaire
 * @Version $Id$ 

 * @Projet LCS / SambaEdu 

 * @auteurs jLCF jean-luc.chretien@.ac-caen.fr
 * @auteurs oluve olivier.le_monnier@crdp.ac-caen.fr
 * @auteurs wawa  olivier.lecluse@crdp.ac-caen.fr
 * @auteurs Equipe Tice academie de Caen

 * @Licence Distribue selon les termes de la licence GPL

 * @note Modifie par Adrien CRESPIN -- Lycee Suzanne Valadon
 * @note SE4
 */

/**
 *
 * @Repertoire: annu
 * file: add_user.php
 */
require "config.inc.php";
require_once "ldap.inc.php";
require_once "functions.inc.php";
require_once ("samba-tool.inc.php");
require_once ("siecle.inc.php");
// HTMLPurifier
// require_once ("traitement_data.inc.php");

$login = isauth();
if ($login == "")
    header("Location:$urlauth");

require "ihm.inc.php";
require "jlcipher.inc.php";

require_once ("lang.inc.php");
bindtextdomain('se3-annu', "/var/www/sambaedu/locale");
textdomain('se3-annu');

// require "crob_ldap_functions.php";

header_crypto_html("Creation utilisateur", "../");
echo "<h1>" . gettext("Annuaire") . "</h1>\n";

@session_start();
$_SESSION["pageaide"] = "Annuaire";
aff_trailer("7");

if (! isset($_SESSION['comptes_crees'])) {
    $_SESSION['comptes_crees'] = array(
        array()
    ); // un sous-tableau par compte ; le deuxième tavbleau est, dans l'ordre nom, prenom, classe (?? en fait, non) (ou 'prof'), cn, password
    array_splice($_SESSION['comptes_crees'], 0, 1);
}

$nom = isset($_POST['nom']) ? $_POST['nom'] : (isset($_GET['nom']) ? $_GET['nom'] : "");
$prenom = isset($_POST['prenom']) ? $_POST['prenom'] : (isset($_GET['prenom']) ? $_GET['prenom'] : "");
$naissance = isset($_POST['naissance']) ? $_POST['naissance'] : (isset($_GET['naissance']) ? $_GET['naissance'] : "");
$password = isset($_POST['userpw']) ? $_POST['userpw'] : "";
$sexe = isset($_POST['sexe']) ? $_POST['sexe'] : (isset($_GET['sexe']) ? $_GET['sexe'] : "");
$categorie = isset($_POST['categorie']) ? $_POST['categorie'] : "";
$add_user = isset($_POST['add_user']) ? $_POST['add_user'] : "";
$string_auth = isset($_POST['string_auth']) ? $_POST['string_auth'] : "";
$string_auth1 = isset($_POST['string_auth1']) ? $_POST['string_auth1'] : "";
$dummy = isset($_POST['dummy']) ? $_POST['dummy'] : "";
$dummy1 = isset($_POST['dummy1']) ? $_POST['dummy1'] : "";

if (have_right($config, "Annu_is_admin")) {
    if ($add_user && ($string_auth || $string_auth1)) {
        exec("/usr/bin/python /var/www/sambaedu/includes/decode.py '$string_auth'", $Res);
        $naissance = $Res[0];
        exec("/usr/bin/python /var/www/sambaedu/includes/decode.py '$string_auth1'", $Res1);
        if (isset($Res1[0])) {
            $password = $Res1[0];
        } else {
            $password = false;
        }
    }
    // Ajout d'un utilisateur
    if ((! isset($_POST['add_user'])) || (! $nom || ! $prenom) || // absence de nom ou de prenom
    ($password && ! verifPwd($password)) || // mot de passe invalide
    ($password && $password != ensure_ascii($password)) || // mot de passe invalide
    ($naissance && ! verifDateNaissance($naissance)) || // date de naissance invalide
    (($naissance && verifDateNaissance($naissance)) && ($password && ! verifPwd($password)))) // date de naissance mais password invalide
                                                                                               // || ($userpwd && !verifPwd($userpwd) ) // password invalide
    {
        ?>
<form name="auth" action="add_user.php" method="post"
	onSubmit="encrypt(document.auth)">
	<table border="0">
		<tbody>
			<tr>
				<td><?php echo gettext("Nom :"); ?></td>
				<td colspan="2" valign="top"><input type="sn" name="nom"
					value="<?php echo $nom ?>" size="20"></td>

			</tr>
			<tr>
				<td><?php echo gettext("Pr&#233;nom :"); ?></td>
				<td colspan="2" valign="top"><input type="cn" name="prenom"
					value="<?php echo $prenom ?>" size="20"></td>

			</tr>
			<tr>
				<td><?php echo gettext("Date de naissance :"); ?></td>
				<td><input type="texte" name="dummy"
					value="<?php echo $naissance ?>" size="8"> <input type="hidden"
					name="string_auth" value=""></td>
				<td><font color="#FF9900">
                  &nbsp;<?php echo gettext("(YYYYMMDD) ce champ est optionnel."); ?>
                </font></td>
			</tr>
			<tr>
				<td><?php echo gettext("Mot de passe :"); ?></td>
				<td><input type="password" value="" name="dummy1" size='8'
					maxlength='8'> <input type="hidden" name="string_auth1" value=""></td>
				<td><font color="#FF9900">
                  &nbsp;<?php echo gettext("ce champ est optionnel"); ?>
                </font></td>
			</tr>
			<tr>
				<td colspan="3" valign="top">
		<?php
        echo '<blockquote><font color="#FF9900">';
        echo gettext('Si le champ mot de passe est laiss&#233; vide, un mot de passe sera cr&#233;&#233; selon la politique de mot de passe par d&#233;faut qui est d&#233;finie &#224; : ');
        switch ($config['pwdPolicy']) {
            case 0: // date de naissance
                echo gettext("date de naissance (YYYYMMDD)");
                echo gettext("<br />Si ni la date de naissance ni le mot de passe ne sont renseign&#233;es, un mot de passe semi-al&#233;atoire sera g&#233;n&#233;r&#233;");
                break;
            case 1: // semi-aleatoire
                echo gettext("semi-al&#233;atoire (8 car.)");
                break;
            case 2: // aleatoire
                echo gettext("al&#233;atoire (8 car.)");
                break;
        }
        echo '</font></blockquote>';

        ?>
              </td>
			
			
			<tr>
				<td><?php echo gettext("Sexe :"); ?></td>
				<td colspan="2"><img src="images/gender_girl.gif" alt="F&#233;minin"
					width="14" height="14" hspace="4" border="0">
                <?php
        echo "<input type=\"radio\" name=\"sexe\" value=\"F\"";
        if (($sexe == "F") || (! $add_user))
            echo " checked";
        echo ">&nbsp;\n";
        ?>
                <img src="images/gender_boy.gif" alt="Masculin" width=14
					height=14 hspace=4 border=0>
                <?php
        echo "<input type=\"radio\" name=\"sexe\" value=\"M\"";
        if ($sexe == "M")
            echo " checked";
        echo ">&nbsp;\n";
        ?>
              </td>
			</tr>
			<tr>
				<td><?php echo gettext("Cat&#233;gorie"); ?></td>
				<td colspan="2" valign="top"><select name="categorie">
                  <?php
        echo "<option value=\"Eleves\"";
        if ($categorie == "Eleves")
            echo "SELECTED";
        echo ">" . gettext("El&#232;ves") . "</option>\n";
        echo "<option value=\"Profs\"";
        if ($categorie == "Profs")
            echo "SELECTED";
        echo ">" . gettext("Profs") . "</option>\n";
        echo "<option value=\"Administratifs\"";
        if ($categorie == "Administratifs")
            echo "SELECTED";
        echo ">" . gettext("Administratifs") . "</option>\n";
        ?>
                </select></td>
			</tr>

			<tr>
				<td><label for='checkbox_create_home'><?php echo gettext("Cr&#233;er le dossier personnel imm&#233;diatement"); ?></label></td>
				<td colspan="2" valign="top"><input type='checkbox'
					id='checkbox_create_home' name='create_home' value='y' /></td>
			</tr>

			<tr>
				<td></td>
				<td></td>
				<td><input type="hidden" name="add_user" value="true"> <input
					type="submit"
					value="<?php echo gettext("Lancer la requ&#234;te"); ?>"></td>
			</tr>
		</tbody>
	</table>
</form>
<?php
        crypto_nav("../");
        if ($add_user) {
            if ((! $nom) || (! $prenom)) {
                echo "<div class=error_msg>" . gettext("Vous devez obligatoirement renseigner les champs : nom, pr&#233;nom !") . "</div><br>\n";
            } elseif (! $naissance && ! $password) {
                echo "<div class='error_msg'>";
                echo gettext("Vous devez obligatoirement renseigner un des deux champs mot de passe ou date de naissance.");
                echo "</div><BR>\n";
            } else {
                if ((($password) && ! verifPwd($password)) || (($password) && ($password != ensure_ascii($password)))) {
                    echo "<div class='error_msg'>";
                    echo gettext("Vous devez proposer un mot de passe d'une longueur comprise entre 4 et 8 caract&#232;res
                    alphanum&#233;riques sans accents avec obligatoirement un des caract&#232;res sp&#233;ciaux suivants") . "&nbsp;" . $char_spec . "&nbsp;" . gettext("ou &#224; d&#233;faut laisser le champ mot de passe vide et dans ce cas un mot de passe sera cr&#233;&#233;.") . "
                  </div><BR>\n";
                }
                if (($naissance) && ! verifDateNaissance($naissance)) {
                    echo "<div class='error_msg'>";
                    echo gettext("Le champ date de naissance doit &#234;tre obligatoirement au format Ann&#233;eMoisJour (YYYYMMDD).");
                    echo "</div><BR>\n";
                }
            }
        }
    } else {
        // Verification si ce nouvel utilisateur n'existe pas deja
        $prenom = stripslashes($prenom);
        $nom = stripslashes($nom);
        // suppression des apostrophes - tant pis pour la noblesse
        $prenom = str_replace("'", "", $prenom);
        $nom = str_replace("'", "", $nom);

        // On vire les accents
        $prenom = ensure_ascii($prenom);
        $nom = ensure_ascii($nom);
        // Du coup, l'utf8_encode qui suit est inutile...

        $cn = utf8_encode($prenom . " " . $nom);
        $people_exist = verif_nom_prenom($config, $nom, $prenom);

        if (count($people_exist) > 0) {
            echo "<div class='error_msg'>";
            echo gettext("Echec de création : L'utilisateur") . " <font color=\"black\"> $prenom $nom</font>" . gettext(" est probablement déjà dans l'annuaire:");
            for ($loop = 0; $loop < count($people_exist); $loop ++) {
                echo " <a href='people.php?cn=" . $people_exist[$loop]["cn"] . "' title=\"Voir le compte.\" target='_blank'>" . $people_exist[$loop]["cn"] . "</a> ";
            }
            if ($people_exist[$loop]["trash"])
                echo "branche trash";
            echo "</div><BR>\n";
        } else {
            switch ($config['pwdPolicy']) {
                case 0: // date de naissance
                    $password = $naissance;
                    break;
                case 1: // semi-aleatoire
                    $password = createRandomPassword(8, false);
                    break;
                case 2: // aleatoire
                    $password = createRandomPassword(8, true);
                    break;
            }
            // Creation du nouvel utilisateur
            $ret = create_user($config, "", $prenom, $nom, $password, $naissance, $sexe, $categorie, "");
            // Compte rendu de creation
            if ($ret) {
                if ($sexe == "M") {
                    echo gettext("L'utilisateur ") . " <strong>$prenom $nom</strong> " . gettext(" a &#233;t&#233; cr&#233;&#233; avec succ&#232;s.") . "<BR>";
                } else {
                    echo gettext("L'utilisateur ") . " <strong>$prenom $nom</strong> " . gettext(" a &#233;t&#233; cr&#233;&#233;e avec succ&#232;s.") . "<BR>";
                }
                $users = search_ad($config, $ret, "user");
                if (count($users)) {
                    echo gettext("Son identifiant est ") . "<STRONG><a href='people.php?cn=" . $users[0]["cn"] . "' title=\"Modifier le compte.\">" . $users[0]["cn"] . "</a></STRONG><BR>\n";
                    echo gettext("Son mot de passe est ") . "<STRONG>" . $password . "</STRONG><BR>\n";
                    $nouveau = array(
                        'nom' => "$nom",
                        'pre' => "$prenom",
                        'cn' => $users[0]["cn"],
                        'pwd' => "$password"
                    );
                    $_SESSION['comptes_crees'][] = $nouveau;
                    echo "<LI><A HREF=\"add_user_group.php?cn=" . $users[0]["cn"] . "\">" . gettext("Ajouter &#224; des groupes...") . "</A>\n";
                }

                if ((isset($_POST['create_home'])) && ($_POST['create_home'] == 'y')) {
                    echo "<p><b>Cr&#233;ation du dossier personnel de " . $users[0]["cn"] . "</b><br />";
                    exec("sudo /usr/share/sambaedu/shares/shares.avail/mkhome.sh " . $users[0]["cn"], $ReturnValue2);
                    echo "<pre style='color:red'>";
                    foreach ($ReturnValue2 as $key => $value) {
                        echo "$value";
                    }
                    echo "</pre>\n";
                }
            } else {
                echo "<div class='error_msg'>" . gettext("Erreur lors de la cr&#233;ation du nouvel utilisateur") . " $prenom $nom
								</font>," . gettext(" veuillez contacter") . "
				<a href='mailto:admin@" . $config['domain'] . "?subject=PB creation nouvel utilisateur Sambaedu'>" . gettext("l'administrateur du syst&#232;me") . "</a></div><br />\n";
                echo "<p><br /></p>\n";
            }
        }
    }

    include("listing.inc.php");
} else {
    echo "<div class=error_msg>" . gettext("Cette application, n&#233;cessite les droits d'administrateur du serveur SambaEdu !") . "</div>";
}

include ("pdp.inc.php");
?>
