<?php

/**

 * Gestion des comptes orphelins
 * @Version $Id$

 * @Projet LCS / SambaEdu

 * @auteurs jLCF jean-luc.chretien@tice.ac-caen.fr Equipe Tice academie de Caen

 * @Licence Distribue selon les termes de la licence GPL

 * @note
 * @sudo /usr/share/se3/scripts/delHome.pl
 */

/**
 *
 * @Repertoire: annu
 * file: ldap_cleaner.php
 */
include "entete.inc.php";
require_once "ldap.inc.php";
require_once "siecle.inc.php";
include "ihm.inc.php";

if (! have_right($config, "se3_is_admin"))
    die(gettext("Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction") . "</BO
DY></HTML>");

$DEBUG == "false";

function draw_table_result($msg_cat, $type1, $type2, $type3, $mode = "")
{
    $html = "<table style='margin-left: 200px; text-align: left; width: 450px;' border='1' cellpadding='1' cellspacing='1'>\n";
    $html .= "<tbody>\n";
    $html .= "<tr>\n";
    $html .= "<td style='text-align: center; width: 300px; height: 20px; ' colspan='1' rowspan='2'>Utilisateur</td>\n";
    $html .= "<td style='text-align: center; width: 150px; height: 20px; ' colspan='3' rowspan='1'>$msg_cat</td>\n";
    $html .= "</tr>\n";
    $html .= "<tr>\n";
    if ($mode == '') {
        $html .= "<td style='text-align: center; width: 50px; height: 20px; '>$type1</td>\n";
        $html .= "<td style='text-align: center; width: 50px; height: 20px;'>$type2</td>\n";
        $html .= "<td style='text-align: center; width: 50px;  height: 20px;'>$type3</td>\n";
    } else {
        $html .= "<td style='text-align: center; width: 50px; height: 20px; '>$type1<br />";
        $html .= "<a href='#' onclick=\"coche('1');return false;\"><img src='../elements/images/enabled.png' border='0' /></a>";
        $html .= " / ";
        $html .= "<a href='#' onclick=\"decoche('1');return false;\"><img src='../elements/images/disabled.png' border='0' /></a>";
        $html .= "</td>\n";

        $html .= "<td style='text-align: center; width: 50px; height: 20px; '>$type2<br />";
        $html .= "<a href='#' onclick=\"coche('2');return false;\"><img src='../elements/images/enabled.png' border='0' /></a>";
        $html .= " / ";
        $html .= "<a href='#' onclick=\"decoche('2');return false;\"><img src='../elements/images/disabled.png' border='0' /></a>";
        $html .= "</td>\n";

        $html .= "<td style='text-align: center; width: 50px; height: 20px; '>$type3<br />";
        $html .= "<a href='#' onclick=\"coche('3');return false;\"><img src='../elements/images/enabled.png' border='0' /></a>";
        $html .= " / ";
        $html .= "<a href='#' onclick=\"decoche('3');return false;\"><img src='../elements/images/disabled.png' border='0' /></a>";
        $html .= "</td>\n";
    }
    $html .= "</tr>\n";
    $html .= "<tr>\n";
    echo $html;
}

// Fin function draw_table_result

/**
 * Fonction qui affiche les info-bulles
 *
 * @Parametres  msg le n du message a afficher
 * @return l'info-bulle
 */
function msgaide($msg)
{
    return ("&nbsp;<u onmouseover=\"this.T_SHADOWWIDTH=5;this.T_STICKY=1;return escape" . gettext("('" . $msg . "')") . "\"><img name=\"action_image2\"  src=\"../elements/images/system-help.png\"></u>");
}
$msg1 = "Recherche les comptes des utilisateurs qui ne sont plus affect&#233;s &#224; un groupe principal et transfert ces comptes &#224; la corbeille.";
$msg2 = "Visualise la liste des comptes transf&#233;r&#233;s dans la corbeille.";
$msg3 = "Permet de r&#233;activer un ou des comptes sous reserve que les cn et/ou les cnNumber de ces comptes soient encore libre.";
$msg4 = "Efface les r&#233;pertoires homes des utilisateurs situ&#233;s dans la corbeille.";

$msg4bis = "D&#233;place les r&#233;pertoires homes des utilisateurs situ&#233;s dans la corbeille vers le dossier temporaire <br>/home/admin/_Trash_users.<br>";
$msg4bis .= "Cela donne un d&#233;lais avant effacement et laisse la place libre dans /home/ pour un nouveau compte de meme cn.";

$msg5 = "Supprime les comptes de la corbeille !<br/><strong>ATTENTION</strong> : Ne supprimer les comptes de la corbeille que lorsque vous avez effectu&#233; l\'effacement des homes sur l\'ensemble des serveurs qui partagent votre annuaire avec votre LCS ou votre SE3.<br>";
$msg5 .= "Le syst&#232me lance ensuite une recherche des fichiers n\'appartenant plus &#224; personne sur les ressources partag&#233;es de /var/se3 ";
$msg6 = "Ce compte, n\'est pas r&#233;cup&#233;rable car il poss&#232;de un cn ou un cnnumber d&#233;sormais occup&#233;.";
$msg7 = "Programme un scanne des partitions de stockage /home et /var/se3 &#224; 20h00. le scanne recherche les fichiers qui n\'appartiennent plus &#224; personne. Cela arrive lorsqu\'un utilsateur est parti mais qu\'il a laiss&#233; des fichiers en place.";
$msg8 = "Supprime le r&#233;pertoire temporaire <br>/home/admin/_Trash_users dans lequel sont stock&#233;s d\'anciens homes.";

// Messages
$msg_confirm = "Avant de vider la corbeille, assurez-vous d'avoir pr&#233;alablement nettoy&#233; les homes des comptes orphelins sur l'ensemble des serveurs qui partagent votre annuaire avec SE3.<br>";
$msg_confirm .= "<a href=\"ldap_cleaner.php?do=4&phase=1\" target=\"main\">Nettoyage !</a>";

echo "<html>\n";
echo "	<head>\n";
echo "		<title>...::: Interface d'administration Serveur SE3 :::...</title>\n";

// Initialisation variables :
$PHP_SELF = $_SERVER['PHP_SELF'];
// Methode POST
$filtre = $_POST['filtre'];
// $filter_type = $_POST['filter'];
$filter_type = $_POST['filter_type'];
$nbr = $_POST['nbr'];
$cat = $_POST['cat'];
// Methodes POST ou GET
if (isset($_POST['phase']))
    $phase = $_POST['phase'];
elseif (isset($_GET['phase']))
    $phase = $_GET['phase'];

if (isset($_POST['do']))
    $do = $_POST['do'];
elseif (isset($_GET['do']))
    $do = $_GET['do'];

$mode_clean = isset($_POST['mode_clean']) ? $_POST['mode_clean'] : (isset($_GET['mode_clean']) ? $_GET['mode_clean'] : NULL);

// Redirection vers phase suivante, gestion du sablier
// ## DEBUG echo "debug1 do:$do phase:$phase<br>";
// Cas 1 : Transfert des utilisateurs dans la Trash
if ($do == 1 && $phase != 1) {
    // ## DEBUG echo "debug2 do:$do phase:$phase<br>";
    echo "<meta HTTP-EQUIV=\"Refresh\" CONTENT=\"1;url='$PHP_SELF?do=1&phase=1'\">\n";
}
// Cas 2 : Examiner le contenu de la corbeille
if ($do == 2 && $phase != 1) {
    // ## DEBUG echo "debug2 do:$do phase:$phase<br>";
    echo "<meta HTTP-EQUIV=\"Refresh\" CONTENT=\"1;url='$PHP_SELF?do=2&phase=1'\">\n";
}
// Cas 3 : Effacer les homes des comptes orphelins
if ($do == 3 && $phase != 1) {
    // ## DEBUG echo "debug2 do:$do phase:$phase<br>";
    if (isset($mode_clean)) {
        echo "<meta HTTP-EQUIV=\"Refresh\" CONTENT=\"1;url='$PHP_SELF?do=3&phase=1&mode_clean=$mode_clean'\">\n";
    } else {
        echo "<meta HTTP-EQUIV=\"Refresh\" CONTENT=\"1;url='$PHP_SELF?do=3&phase=1'\">\n";
    }
}
// Cas 4 : Vider la corbeille
if ($do == 4 && $phase == 1) {
    // ## DEBUG echo "debug2 do:$do phase:$phase<br>";
    echo "<meta HTTP-EQUIV=\"Refresh\" CONTENT=\"1;url='$PHP_SELF?do=4&phase=2'\">\n";
}
// Cas 10 : Recuperation des utilisateurs de Trash vers People
if ($do == 10 && $phase == 2) {
    // ## DEBUG echo "debug2 $do $phase<br>";
    echo "<meta HTTP-EQUIV=\"Refresh\" CONTENT=\"1;url='$PHP_SELF?do=10&phase=3'\">\n";
}
// Fin traitement des redirections
echo "	</head>\n";
echo "	<body>\n";

// if (have_right($config, "se3_is_admin")) {

// Aide
$_SESSION["pageaide"] = "Annuaire#Nettoyage_des_comptes";

$html = "<div style=\"margin-bottom: 15%\"><H1>Gestion des comptes orphelins</H1>\n";
$html .= "<H3>Op&#233;rations courantes</H3>\n";

if ($do != "1")
    $html .= "<li><a href=\"ldap_cleaner.php?do=1\" target=\"main\">Transfert des comptes orphelins dans la corbeille</a>" . msgaide($msg1) . "</li>\n";
if ($do != "2")
    $html .= "<li><a href=\"ldap_cleaner.php?do=2\" target=\"main\">Examiner le contenu de la corbeille</a>" . msgaide($msg2) . "</li>\n";
$html .= "<li><a href=\"ldap_cleaner.php?do=10\" target=\"main\">R&#233;cup&#233;ration de comptes orphelins depuis la corbeille</a>" . msgaide($msg3) . "</li>\n";
// if ($do !="3") $html .= "<li><a href=\"ldap_cleaner.php?do=3\" target=\"main\">Effacer les homes des comptes orphelins</a>".msgaide($msg4)."</li>\n";
if ($do != "3") {
    $html .= "<li><a href=\"ldap_cleaner.php?do=3\" onclick=\"return getconfirm();\" target=\"main\">Effacer les &#171;homes&#187; des comptes orphelins</a>" . msgaide($msg4) . "ou <a href=\"ldap_cleaner.php?do=3&amp;mode_clean=mv\" target=\"main\">les deplacer vers un dossier temporaire _Trash_users</a>" . msgaide($msg4bis) . "</li>\n";
    $html .= "<li><a href=\"ldap_cleaner.php?do=6\" target=\"main\">Supprimer le dossier temporaire _Trash_users</a>" . msgaide($msg8) . "</li>\n";
}
if ($do != "4")
    $html .= "<li><a href=\"ldap_cleaner.php?do=4\" target=\"main\">Vider la corbeille et purger les fichiers inutiles sur /var/se3</a>" . msgaide($msg5) . "</li><br>\n";
$html .= "<H3>R&#233;solution de probl&#232;me</H3>\n";
$html .= "<li><a href=\"ldap_cleaner.php?do=5\" target=\"main\">Programmer la recherche et la suppression des fichiers utilisateurs obsol&#232;tes sur les partitions de stockage</a>" . msgaide($msg7) . "</li>\n";

$html .= "<p></p>";

echo $html;

// Actions
switch ($do) {
    case 1:
        // Transfert des comptes orphelins dans la corbeille
        if ($phase != 1)
            // Affichage du sablier
            echo "<div align='center'><img src=\"images/wait.gif\" title=\"Patientez...\" align=\"middle\" border=\"0\">&nbsp;Transfert des comptes orphelins dans la corbeille en cours. Veuillez patienter...</div>";
        else {
            // On commence par controler s'il n'y a pas des utilisateurs qui ne sont plus ni dans Profs ni dans Eleves alors qu'ils ont le droit no_Trash_user

            $tmp_tab_no_Trash_user = list_members_rights($config, "no_Trash_user");
            if (count($tmp_tab_no_Trash_user) > 0) {
                echo "<p>Controle des titulaires du droit no_Trash_user avant d'effectuer le transfert des comptes orphelins.</p>\n";
                echo "<blockquote>\n";
                $cpt_retablissement_no_trash = 0;
                $attribut = array(
                    "cn"
                );
                for ($loop = 0; $loop < count($tmp_tab_no_Trash_user); $loop ++) {
                    $cn = $tmp_tab_no_Trash_user[$loop];
                    // echo "\$tmp_tab_no_Trash_user[$loop]=$cn<br />";

                    /*
                     * $grp_no_Trash="";
                     * $tabtmp=get_tab_attribut("groups", "(&(cn=Profs)(member=$cn))", $attribut);
                     * if(count($tabtmp)>0) {
                     * $grp_no_Trash="Profs";
                     * }
                     * else {
                     * $tabtmp=get_tab_attribut("groups", "(&(cn=Eleves)(member=$cn))", $attribut);
                     * if(count($tabtmp)>0) {
                     * $grp_no_Trash="Eleves";
                     * }
                     * }
                     */
                    // S'ils ont ete supprimes de Eleves et Profs, on ne peut plus les reaffecter dans le bon groupe
                    // Par defaut, on les declare Profs (parce qu'il y a plus de chance qu'on mette un Prof en no_Trash_user qu'un eleve) et on alerte.
                    $grp_no_Trash = "Profs";
                    // echo "\$grp_no_Trash=$grp_no_Trash<br />";

                    // if($grp_no_Trash!="") {
                    if (($grp_no_Trash != "") && ($cn != "admin")) {
                        $compte_existe = search_user($config, $cn);
                        if (count($compte_existe) == 0) {
                            // Le compte n'existe plus... et on a oublie de nettoyer no_Trash_user

                            echo "Le compte $cn n'existe plus.<br />Suppression de l'association de $cn au droit no_Trash_user: ";
                        } else {
                            // On controle si le compte est membre du groupe $grp_no_Trash
                            $attribut = array(
                                "member"
                            );
                            $member = search_user("groups", "(&(cn=$grp_no_Trash)(member=$cn))", $attribut);
                            if (have_right($config, $cn, "no_trash_user")) {
                                echo "$cn est deja membre du groupe $grp_no_Trash.";
                            } else {
                                echo "Rétablissement de $cn comme membre du groupe $grp_no_Trash: ";
                                if (add_right($config, $cn, "no_trash_user")) {
                                    echo "<font color='green'>SUCCES</font>";
                                } else {
                                    echo "<font color='red'>ECHEC</font>";
                                }
                                $cpt_retablissement_no_trash ++;
                            }
                        }
                        echo "<br />\n";
                    }
                }
                if ($cpt_retablissement_no_trash > 0) {
                    echo "<p>Un ou des utilisateurs ont été rétablis comme membres du groupe Profs pour éviter une mise à la corbeille.<br />Si ce n'était pas leur groupe d'appartenance initiale, il faudra corriger manuellement dans Annuaire/Acc&#232;s &#224; l'annuaire</p>\n";
                }
                echo "</blockquote>\n";
            }

            // Transfert des comptes orphelins dans la corbeille
            if (trash_users($config))
                echo "Le transfert des  comptes orphelins dans la corbeille s'est déroulé avec succès.<br>";
            else
                echo "<div class=error_msg>Echec du tansfert des  comptes orphelins dans la corbeille !</div>";
        }
        break;
    case 2:
        // Examiner le contenu de la corbeille
        if ($phase != 1)
            // Affichage du sablier
            echo "<div align='center'><img src=\"images/wait.gif\" title=\"Patientez...\" align=\"middle\" border=\"0\">&nbsp;Examen du contenu de la poubelle. Veuillez patienter...</div>";
        else {
            $users = search_ad($config, "*", "user", $config['dn']['trash']);
            echo "<p><img src=\"images/";
            if (count($users) == 0)
                echo "Poubelle_vide.png";
            else
                echo "Poubelle_pleine.png";
            echo "\" alt=\"Corbeille\" width=\"51\" height=\"65\" align=\"middle\" border=\"0\">&nbsp;Il y a <STRONG>" . count($users) . "</STRONG> utilisateur";
            if (count($users) > 0)
                echo "s";
            echo "&nbsp;dans la corbeille.</p>\n";
            echo "<UL>\n";
            for ($loop = 0; $loop < count($users); $loop ++)
                echo "<LI>" . utf8_decode($users[$loop]['cn']) . "</LI>\n";
            echo "</UL>\n";
        }
        break;

    case 3:
        // Nettoyage des repertoires home
        if ($phase != 1)
            // Affichage du sablier
            echo "<div align='center'><img src=\"images/wait.gif\" title=\"Patientez...\" align=\"middle\" border=\"0\">&nbsp;Le nettoyage des r&#233;pertoires &#171;homes&#187; est en cours. Veuillez patienter...</div>";
        else {
            // echo "\$_GET['mode_clean']=".$_GET['mode_clean']."<br />";
            if ($mode_clean == 'mv') {
                echo "<h4>D&#233placement des homes des comptes orhelins en cours...</h4>";
                system("/usr/bin/sudo /usr/share/se3/scripts/clean_homes.sh -m", $ReturnValue);
                // echo "\$ReturnValue=$ReturnValue<br />";
                if ($ReturnValue != "0") {
                    echo "<div class='error_msg'>Une erreur s'est produite !</div>";
                }
                for ($loop = 0; $loop < count($AllOutPut); $loop ++) {
                    // echo "\$AllOutPut[$loop]=".$AllOutPut[$loop]."<br />";
                    echo $AllOutPut[$loop] . " ";
                }
                echo "<p>Termin&#233;.</p>\n";
            } else {
                system("/usr/bin/sudo /usr/share/se3/scripts/clean_homes.sh -d", $ReturnValue);
                if ($ReturnValue == "0") {
                    echo "Le nettoyage des r&#233;pertoires &#171;homes&#187; s'est d&#233;roul&#233; avec succ&#232;s.<br>";
                } else {
                    echo "<div class='error_msg'>Echec du nettoyage des r&#233;pertoires &#171;homes&#187; !</div>";
                }
            }
        }
        break;
    case 4:
        // Vidage de la corbeille
        if ($phase != 1 && $phase != 2)
            // Affichage du message de confirmation
            echo "<div class=error_msg>$msg_confirm</div>";
        elseif ($phase == 1)
            // Affichage du sablier
            echo "<div align='center'><img src=\"images/wait.gif\" title=\"Patientez...\" align=\"middle\" border=\"0\">&nbsp;Vidage de la corbeille en cours. Veuillez patienter...</div>";
        elseif ($phase == 2) {
            // echo "Le nettoyage de la corbeille s'est d&#233;roul&#233; avec succ&#232;s.<br>";
            $users = search_ad($config, "*", "user", $config['dn']['trash']);
            for ($loop = 0; $loop < count($users); $loop ++) {
                $entry = "cn=" . $users[$loop]["cn"] . "," . $dn["trash"];
                exec("/usr/share/se3/sbin/entryDel.pl $entry", $AllOutPut, $ReturnValue);
            }
            $users = search_ad($config, "*", "user", $config['dn']['trash']);
            if (count($users) == 0) {
                echo "Le nettoyage de la corbeille s'est d&#233;roul&#233; avec succ&#232;s.<br><br>";
                echo "Une recherche sur les ressources partag&#233;es pour suppression des fichiers obsol&#232;tes a &#233;t&#233; lanc&#233;e en arri&#232;re plan.<br>";
                echo "Un mail r&#233;capitulatif vous sera envoy&#233;";
                system("/usr/bin/sudo /usr/share/se3/scripts/clean_homes.sh -sv ", $ReturnValue);
                // if ($ReturnValue == "0") {
                // echo "<br>Le nettoyage de /var/se3 s'est d&#233;roul&#233; avec succ&#232;s.<br>";
                // }
                // else {
                // echo "<div class='error_msg'>Echec du nettoyage des r&#233;pertoires &#171;/var/se3&#187; !</div>";
                // }
            } else
                echo "<div class=error_msg>Echec du nettoyage de la corbeille !</div>";
        }
        break;

    case 5:
        // Grand menage !!

        echo "<h4>Grand m&#233;nage : suppression des fichiers obsol&#232;tes sur /home et /var/se3</h4>";
        system("/usr/bin/sudo /usr/share/se3/scripts/clean_homes.sh -sc", $ReturnValue);
        // echo "\$ReturnValue=$ReturnValue<br />";
        if ($ReturnValue != "0") {
            echo "<div class='error_msg'>Une erreur s'est produite???</div>";
        } else {
            echo "<div class='text'>Programmation pour 20h00 effectu&#233;e, un mail r&#233;capitulatif vous sera envoy&#233.</div>";
        }
        break;
        echo "Un mail r&#233;capitulatif vous sera envoy&#233;";

    case 6:
        // Supression de trash_users !!

        echo "<h4>Supression du dossier /home/admin/Trash_users en cours....</h4>";
        system("/usr/bin/sudo /usr/share/se3/scripts/clean_homes.sh -t", $ReturnValue);
        // echo "\$ReturnValue=$ReturnValue<br />";
        if ($ReturnValue != "0") {
            echo "<div class='error_msg'>Une erreur s'est produite !</div>";
        } else {
            echo "<div class='text'>Suppression Ok !</div>";
        }
        break;

    case 10:
        // Recuperation de comptes orphelins
        // Choix d'un filtre de recherche
        if ($phase != 1 && $phase != 2 && $phase != 3) {
            $html = "<p><u>Recherche des comptes orphelins &#224; transf&#233;rer</u> :</p>\n";
            $html .= "<div style='margin-left: 40px'>\n";
            $html .= "<form action='ldap_cleaner.php?do=10' method = 'post'>\n";
            $html .= "Filtre de recherche&nbsp;";
            $html .= "<select name='filter_type'>\n";
            $html .= "<option value='contient'>contient</option>\n";
            $html .= "<option value='commence'>commence par</option>\n";
            $html .= "<option value='finit'>finit par</option>\n";
            $html .= "</select>\n";
            $html .= "<input type='text' name='filtre'>\n";
            $html .= "<input type='hidden' name='phase' Value='1'>\n";
            $html .= "<input type='submit' Value='Rechercher'>\n";
            $html .= "</form></div>\n";
            echo $html;
        } elseif ($phase == 1) {
            // Affichage de la liste des comptes orphelins
            // Interpretation du type de filtre
            if ($filter_type == "contient")
                if ($filtre != "*")
                    $filtre = "*" . $filtre . "*";
            if ($filter_type == "commence")
                $filtre = $filtre . "*";
            if ($filter_type == "finit")
                $filtre = "*" . $filtre;
            // Recherche des utilisateurs repondant au critere
                $users = search_ad($config, $filtre, "user", $config['dn']['trash']);
            echo "<div align='center'><img src=\"images/";
            if (count($users) == 0)
                echo "Poubelle_vide.png";
            else
                echo "Poubelle_pleine.png";
            echo "\" alt=\"Corbeille\" width=\"51\" height=\"65\" align=\"middle\" border=\"0\">&nbsp;Il y a <STRONG>" . count($users) . "</STRONG> utilisateur";
            if (count($users) >= 2)
                echo "s";
            echo "&nbsp;dans la corbeille qui r&#233;pond";
            if (count($users) >= 2)
                echo "ent";
            echo " au <em>filtre</em> de recherche.</div>\n";
            // Affichage de la liste des utilisateurs a recuperer
            if (count($users) > 0) {
                $html = "<form action='ldap_cleaner.php?do=10' method = 'post'>\n";
                // Tableau d'affichage des resultats
                draw_table_result("Nom", "catégorie");
                for ($loop = 0; $loop < count($users); $loop ++) {
                    $html .= "<tr><td style='width: 300px;'>" . utf8_decode($users[$loop]["cn"]) . "</td>\n";
                    $users[$loop]['categorie'] = type_user($config, $users[$loop]["cn"]);
                    $html .= "<td style='text-align: center; width: 50px;'><input type='radio' id='cat_" . $loop . "' name='cat[$loop]' value='" . $users[$loop]['cn'] ."'>".$users[$loop]['categorie']."</td>\n";
                }
                $html .= "</tbody>\n</table>\n";
                $html .= "<input type='hidden' name='phase' Value='2'>\n";
                $html .= "<input type='hidden' name='nbr' Value='$loop'>\n";
                $html .= "<div style='margin-left: 200px'>\n";
                $html .= "<input type='submit' Value='R&#233;cup&#233;rer'>\n";
                $html .= " <input type='reset' Value='R&#233;initialiser'>\n";
                $html .= "</form></div>\n";

            } else
                $html = "<div class='alert_msg'>Pas de transfert &#224; effectuer !</div>\n";
            echo $html;
        } elseif ($phase == 2) {
            // Transfert des comptes de trash -> peoples et positionnement des groupes principaux
            // Transfert des utilisateurs selectionne dans /tmp/list_recup
            for ($loop = 0; $loop < $nbr; $loop ++) {
                if (isset($cat[$loop])) {
                    $tmp = $cat[$loop];
                    exec("echo $tmp >> /tmp/list_recup");
                }
            }
            // Affichage du sablier
            echo "<div align='center'><img src=\"images/wait.gif\" title=\"Patientez...\" align=\"middle\" border=\"0\"> R&#233;cup&#233;ration des comptes orphelins en cours. Veuillez patienter...</div>";
        } elseif ($phase == 3) {
            // Recuperation des utilisateurs selectionnes
            if (file_exists("/tmp/list_recup")) {
                $fd = fopen("/tmp/list_recup", "r");
                draw_table_result("R&#233;cup&#233;ration dans la cat&#233;gorie", "Eleve", "Professeur", "Administratif");
                while (! feof($fd)) {
                    $tmp = fgets($fd, 255);
                    $trash_member = trim($tmp);
                    if ($trash_member != "") {
                        $recup = recup_user($config, $trash_member);
                          // Affichage des utilisateurs recuperes
                        $html = "<tr><td style='width: 300px;'>";
                        if ($recup)
                            $html .= "<a href='people.php?cn=" . $trash_member . "'>";
                        $html .= utf8_decode($trash_member);
                        if ($recup)
                            $html .= "</a>";
                        $html .= "</td>\n";
                        if ($recup) {
                            $categorie = type_user($config, $trash_member);
                            $html .= "<td style='text-align: center; width: 50px;'>";
                            if ($categorie == "Eleves")
                                $html .= "<b>X</b>";
                            else
                                $html .= "&nbsp;";
                            $html .= "</td>\n";
                            $html .= "<td style='text-align: center; width: 50px;'>";
                            if ($categorie == "Profs")
                                $html .= "<b>X</b>";
                            else
                                $html .= "&nbsp;";
                            $html .= "</td>\n";
                            $html .= "<td style='text-align: center; width: 50px;'>";
                            if ($categorie == "Administratifs")
                                $html .= "<b>X</b>";
                            else
                                $html .= "&nbsp;";
                            $html .= "</td></tr>\n";
                        } else
                            $html .= "<td colspan='3' style='color: red; text-align: center; width: 50px;'>Compte irr&#233;cup&#233;rable !</td></tr>\n";
                        echo $html;
                    }
                }
                fclose($fd);
                unlink("/tmp/list_recup");
                echo "</tbody>\n</table>\n";
            } else
                echo "<div class=error_msg>Vous n'avez pas s&#233;lectionn&#233; d'utilisateur(s) &#224; r&#233;cup&#233;rer!</div>";
        }
        break; // Fin case
}
// } else echo "Vous n'avez pas les droits n&#233;cessaires pour cette action...";
include ("pdp.inc.php");
?>
