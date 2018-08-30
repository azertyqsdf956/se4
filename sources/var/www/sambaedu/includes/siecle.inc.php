<?php

/**
 * Fonctions pour l'import sconet/siecle/sts

 * @Projet LCS / SambaEdu

 * @Auteurs Stephane Boireau

 * @Note

 * @Licence Distribue sous la licence GPL
 */

/**
 *
 * file: siecle.inc.php
 *
 * @Repertoire: includes/
 */

// ================================================
// Correspondances de caractères accentués/désaccentués
$liste_caracteres_accentues = "ÂÄÀÁÃÅÇÊËÈÉÎÏÌÍÑÔÖÒÓÕØ¦ÛÜÙÚÝ¾´áàâäãåçéèêëîïìíñôöðòóõø¨ûüùúýÿ¸";
$liste_caracteres_desaccentues = "AAAAAACEEEEIIIINOOOOOOSUUUUYYZaaaaaaceeeeiiiinooooooosuuuuyyz";

// ================================================

/**
 *
 * Fonction de generation de mot de passe recuperee sur TotallyPHP
 * Aucune mention de licence pour ce script...
 *
 * @Parametres
 * @return 1 ou 0
 *        
 *         The letter l (lowercase L) and the number 1
 *         have been removed, as they can be mistaken
 *         for each other.
 */
function createRandomPassword($nb_chars)
{
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double) microtime() * 1000000);
    $i = 0;
    $pass = '';

    // while ($i <= 7) {
    // while ($i <= 5) {
    while ($i <= $nb_chars) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i ++;
    }

    return $pass;
}

// ================================================

/**
 *
 * Fonction qui retourne la date et l'heure
 *
 * @Parametres
 * @return jour/moi/annee heure:mn:seconde
 *        
 */
function date_et_heure()
{
    $instant = getdate();
    $annee = $instant['year'];
    $mois = sprintf("%02d", $instant['mon']);
    $jour = sprintf("%02d", $instant['mday']);
    $heure = sprintf("%02d", $instant['hours']);
    $minute = sprintf("%02d", $instant['minutes']);
    $seconde = sprintf("%02d", $instant['seconds']);

    $retour = "$jour/$mois/$annee $heure:$minute:$seconde";

    return $retour;
}

// ================================================

/**
 *
 * Lit le fichier ssmtp et en retourne le contenu
 *
 * @Parametres
 * @return
 *
 */
function lireSSMTP()
{
    $chemin_ssmtp_conf = "/etc/ssmtp/ssmtp.conf";

    $tabssmtp = array();

    if (file_exists($chemin_ssmtp_conf)) {
        $fich = fopen($chemin_ssmtp_conf, "r");
        if (! $fich) {
            return false;
        } else {
            while (! feof($fich)) {
                $ligne = fgets($fich, 4096);
                if (strstr($ligne, "root=")) {
                    unset($tabtmp);
                    $tabtmp = explode('=', $ligne);
                    $tabssmtp["root"] = trim($tabtmp[1]);
                } elseif (strstr($ligne, "mailhub=")) {
                    unset($tabtmp);
                    $tabtmp = explode('=', $ligne);
                    $tabssmtp["mailhub"] = trim($tabtmp[1]);
                } elseif (strstr($ligne, "rewriteDomain=")) {
                    unset($tabtmp);
                    $tabtmp = explode('=', $ligne);
                    $tabssmtp["rewriteDomain"] = trim($tabtmp[1]);
                }
            }
            fclose($fich);

            return $tabssmtp;
        }
    } else {
        return false;
    }
}

// ================================================

/**
 *
 * Affiche le texte ou l ecrit dans un fichier
 *
 * @Parametres texte
 * @return
 *
 */
function my_echo($texte)
{
    global $echo_file, $dest_mode;

    $destination = $dest_mode;

    if ((! file_exists($echo_file)) || ($echo_file == "")) {
        $destination = "";
    }

    switch ($destination) {
        case "file":
            $fich = fopen($echo_file, "a+");
            fwrite($fich, "$texte");
            fclose($fich);
            break;
        default:
            echo "$texte";
            break;
    }
}

// ================================================

/**
 *
 * Affiche le tableau à la façon de print_r ou l ecrit dans un fichier
 *
 * @Parametres tableau
 * @return
 *
 */
function my_print_r($tab)
{
    global $echo_file, $dest_mode;

    my_echo("Array<br />(<br />\n");
    my_echo("<blockquote>\n");
    foreach ($tab as $key => $value) {
        if (is_array($value)) {
            my_echo("[$key] =&gt; ");
            my_print_r($value);
        } else {
            my_echo("[$key] =&gt; $value<br />\n");
        }
    }
    my_echo("</blockquote>\n");
    my_echo(")<br />\n");
}

// ================================================

/**
 *
 * remplace les accents
 *
 * @Parametres chaine a traiter
 * @return la chaine sans accents
 *        
 */
function remplace_accents($chaine)
{
    // $retour=strtr(preg_replace("/Æ/","AE",preg_replace("/æ/","ae",preg_replace("/Œ/","OE",preg_replace("/œ/","oe","$chaine"))))," '$liste_caracteres_accentues","__$liste_caracteres_desaccentues");
    $chaine = preg_replace("/Æ/", "AE", "$chaine");
    $chaine = preg_replace("/æ/", "ae", "$chaine");
    $chaine = preg_replace("/œ/", "oe", "$chaine");
    $chaine = preg_replace("/Œ/", "OE", "$chaine");

    $retour = strtr($chaine, array(
        'Á' => 'A',
        'À' => 'A',
        'Â' => 'A',
        'Ä' => 'A',
        'Ã' => 'A',
        'Å' => 'A',
        'Ç' => 'C',
        'É' => 'E',
        'È' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Í' => 'I',
        'Ï' => 'I',
        'Î' => 'I',
        'Ì' => 'I',
        'Ñ' => 'N',
        'Ó' => 'O',
        'Ò' => 'O',
        'Ô' => 'O',
        'Ö' => 'O',
        'Õ' => 'O',
        'Ú' => 'U',
        'Ù' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'á' => 'a',
        'à' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'ã' => 'a',
        'å' => 'a',
        'ç' => 'c',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ñ' => 'n',
        'ó' => 'o',
        'ò' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'õ' => 'o',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ý' => 'y',
        'ÿ' => 'y'
    ));

    return $retour;
}

// ================================================

/**
 *
 * dédoublonnage des espaces dans une chaine
 *
 * @Parametres chaine a traiter
 * @return la chaine sans doublons d'espaces
 *        
 */
function traite_espaces($chaine)
{
    // $chaine=" Bla ble bli blo blu ";
    /*
     * $tab=explode(" ",$chaine);
     *
     * $retour=$tab[0];
     * for($i=1;$i<count($tab);$i++) {
     * if($tab[$i]!="") {
     * $retour.=" ".$tab[$i];
     * }
     * }
     */
    $retour = preg_replace("/ {2,}/", " ", $chaine);
    $retour = trim($retour);
    return $retour;
}

// ================================================

/**
 *
 * remplacement des apostrophes et espaces par des underscore
 *
 * @Parametres chaine a traiter
 * @return la chaine nettoyee
 *        
 */
function apostrophes_espaces_2_underscore($chaine)
{
    // $retour = preg_replace("/'/", "_", preg_replace("/ /", "_", $chaine));
    $chaine = preg_replace("/'/", "_", $chaine);
    $tab = explode(" ", $chaine, 1);
    if (isset($tab[1])) {
        return $tab[0] . "_" . preg_replace("/ /", "-", $tab[1]);
    }
    return $tab[0];
}

// ================================================

/**
 *
 * traitement des chaines accentuees (simpleXML recupere des chaines UTF8, meme si l'entete du XML est ISO)
 *
 * @Parametres chaine a traiter
 * @return la chaine correctement encodee
 *        
 */
function traite_utf8($chaine)
{
    // On passe par cette fonction pour pouvoir desactiver rapidement ce traitement s'il ne se revele plus necessaire
    // $retour=$chaine;

    // mb_detect_encoding($chaine . 'a' , 'UTF-8, ISO-8859-1');

    // $retour=utf8_decode($chaine);
    // utf8_decode() va donner de l'iso-8859-1 d'ou probleme sur quelques caracteres

    // $retour=recode_string("utf8..lat9", $chaine);
    // Warning: recode_string(): Illegal recode request 'utf8..lat9' in /var/www/se3/includes/crob_ldap_functions.php on line 277

    // DESACTIVE POUR PASSAGE UTF-8 Voir solution plus propre
    // $retour=recode_string("utf8..iso-8859-15", $chaine);
    return $chaine;
}

/**
 *
 * Active le mode debug
 *
 * @Parametres
 * @return
 *
 */
function fich_debug($texte)
{
    // Passer la variable ci-dessous a 1 pour activer l'ecriture d'infos de debuggage dans /tmp/debug_se3lcs.txt
    // Il conviendra aussi d'ajouter des appels fich_debug($texte) la ou vous en avez besoin;o).
    $debug = 0;

    if ($debug == 1) {
        $fich = fopen("/tmp/debug_se3lcs.txt", "a+");
        fwrite($fich, $texte);
        fclose($fich);
    }
}

// ================================================

/**
 *
 * Cree l'cn a partir du nom prenom et de la politique de login
 *
 * @Parametres
 * @return
 *
 */
function creer_cn($config, $nom, $prenom)
{
    global $error;
    $error = "";

    fich_debug("======================\n");
    fich_debug("creer_cn:\n");
    fich_debug("\$nom=$nom\n");
    fich_debug("\$prenom=$prenom\n");

    /*
     * # Il faudrait ameliorer la fonction pour gerer les "Le goff Martin" qui devraient donner "Le_goff-Martin"
     * # Actuellement, on passe tous les espaces a _
     */
    // crob_init(); Ne sert a rien !!!!
    // $nom = preg_replace("/[^a-z_ -]/", "", strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$nom")))), "'$liste_caracteres_accentues", "_$liste_caracteres_desaccentues")));
    // $prenom = preg_replace("/[^a-z_ -]/", "", strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$prenom")))), "'$liste_caracteres_accentues", "_$liste_caracteres_desaccentues")));
    $nom = apostrophes_espaces_2_underscore(remplace_accents($nom));
    $prenom = apostrophes_espaces_2_underscore(remplace_accents($prenom));

    $nom = ucfirst(strtolower($nom));
    $prenom = ucfirst(strtolower($prenom));

    // Filtrer certains caracteres:
    // $nom = strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$nom")))), " '$liste_caracteres_accentues", "__$liste_caracteres_desaccentues"));
    // $prenom = strtolower(strtr(preg_replace("/Æ/", "AE", preg_replace("/æ/", "ae", preg_replace("/¼/", "OE", preg_replace("/½/", "oe", "$prenom")))), " '$liste_caracteres_accentues", "__$liste_caracteres_desaccentues"));

    fich_debug("Apr&#232;s filtrage...\n");
    fich_debug("\$nom=$nom\n");
    fich_debug("\$prenom=$prenom\n");

    /*
     * # Valeurs de l'cnPolicy
     * # 0: prenom.nom
     * # 1: prenom.nom tronque a 19
     * # 2: pnom tronque a 19
     * # 3: pnom tronque a 8
     * # 4: nomp tronque a 8
     * # 5: nomprenom tronque a 18
     */

    switch ($config['cnPolicy']) {
        case 0:
            $cn = $prenom . "." . $nom;
            break;
        case 1:
            $cn = $prenom . "." . $nom;
            $cn = substr($cn, 0, 19);
            break;
        case 2:
            $ini_prenom = substr($prenom, 0, 1);
            $cn = $ini_prenom . $nom;
            $cn = substr($cn, 0, 19);
            break;
        case 3:
            $ini_prenom = substr($prenom, 0, 1);
            $cn = $ini_prenom . $nom;
            $cn = substr($cn, 0, 8);
            break;
        case 4:
            $debut_nom = substr($nom, 0, 7);
            $ini_prenom = substr($prenom, 0, 1);
            $cn = $debut_nom . $ini_prenom;
            break;
        case 5:
            $cn = $nom . $prenom;
            $cn = substr($cn, 0, 18);
            break;
        default:
            $ERREUR = "oui";
    }

    fich_debug("\$cn=$cn\n");
    if (isset($ERREUR)) {
        fich_debug("\$ERREUR=$ERREUR\n");
    }

    // Pour faire disparaitre les caracteres speciaux restants:
    $cn = strtolower(preg_replace("/[^a-z_.-]/", "", $cn));

    // Pour eviter les _ en fin d'UID... pb avec des connexions machine de M$7
    $cn = preg_replace("/_*$/", "", $cn);

    fich_debug("Apr&#232;s filtrage...\n");
    fich_debug("\$cn=$cn\n");

    $test_caract1 = substr($cn, 0, 1);
    // if(strlen(preg_replace("/[a-z]/","",$test_caract1))!=0){
    if ($cn == '') {
        $error = "Le login obtenu avec le nom '" . $nom . "' et le prenom '" . $prenom . "' en cnPolicy '" . $config['cnPolicy'] . "' est vide.";
    } elseif (strlen(preg_replace("/[a-z]/", "", $test_caract1)) != 0) {
        $error = "Le premier caract&#232;re de l'cn n'est pas une lettre.";
    } else {
        // Debut de du cn... pour les doublons...
        $prefcn = substr($cn, 0, strlen($cn) - 1);
        $prefcn2 = substr($cn, 0, strlen($cn) - 2);
        // Ou renseigner un cn_initial ou cn_souche
        $cn_souche = $cn;

        // $tab_logins_non_permis=array('prof', 'progs', 'docs', 'classes', 'homes', 'admhomes', 'admse3');
        $tab_logins_non_permis = array(
            'prof',
            'progs',
            'docs',
            'classes',
            'homes',
            'admhomes',
            'netlogon',
            'profiles'
        );
        if (in_array($cn_souche, $tab_logins_non_permis)) {
            $cpt = 1;
            $cn_souche = substr($cn, 0, strlen($cn) - strlen($cpt)) . $cpt;
        }

        $ok_cn = false;

        $cpt = 2;
        while ((! $ok_cn) && ($cpt < 100)) {
            if (count(search_ad($config, $cn, "user", $config['dn']['people']))) {
                $cn = substr($cn_souche, 0, strlen($cn_souche) - strlen($cpt)) . $cpt;
                if ($cn == "adminse3") {
                    $cn = "adminse4";
                }
                fich_debug("Doublons... \$cn=$cn\n");
                $cpt ++;
            }

            // Vérification que l'cn n'était pas en Trash
            if (count(search_ad($config, $cn, "user", $config['dn']['trash']))) {
                $ok_cn = false;
                $error = "L'cn <b style='color:red;'>$cn</b> existe dans la branche Trash.";
            } else {
                $ok_cn = true;
            }
        }
    }

    if ($error != "") {
        echo "error=$error<br />\n";
        fich_debug("\$error=$error\n");
        return false;
    } elseif ($cpt >= 100) {
        $error = "Il y a au moins 100 cn en doublon...<br />On en est &#224; $cn<br />Etes-vous s&#251;r qu'il n'y a pas des personnes qui ont quitt&#233; l'&#233;tablissement?";
        echo "error=$error<br />\n";
        fich_debug("\$error=$error\n");
        return false;
    } else {
        // Retourner $cn
        return $cn;
    }
}

// ================================================

/**
 *
 * Tester si l'employeeNumber est dans l'annuaire ou non...
 *
 * @Parametres
 * @return
 *
 */
function verif_employeeNumber($config, $employeeNumber)
{
    // Tester si l'employeeNumber est dans l'annuaire ou non...
    $tab1 = search_ad($config, $employeeNumber, "employeenumber", $config['dn']['people']);
    $tab2 = search_ad($config, $employeeNumber, "employeenumber", $config['dn']['trash']);

    if (count($tab1) > 0) {
        $tab1[0]['branch'] = "people";
        return $tab1[0];
    } elseif (count($tab2) > 0) {
        $tab1[0]['branch'] = "trash";
        return $tab2[0];
    } else {
        return false;
    }
}

// ================================================

/**
 *
 * Tester si un cn existe ou non dans l'annuaire pour $nom et $prenom sans employeeNumber ... ce qui correspondrait a un compte cree a la main.
 *
 * @Parametres
 * @return
 *
 */
function verif_nom_prenom($config, $nom, $prenom)
{
    // Tester si un cn existe ou non dans l'annuaire pour $nom et $prenom sans employeeNumber...
    // ... ce qui correspondrait a un compte cree a la main.
    $trouve = 0;

    // On fait une recherche avec éventuellement les accents dans les nom/prénom... et on en fait si nécessaire une deuxième sans les accents
    $attribut = array(
        "cn"
    );
    $tab1 = array();
    // $tab1=get_tab_attribut("people","cn='$prenom $nom'",$attribut);
    $tab1 = search_ad($config, "(&(objectclass=user)(displayname=" . $prenom . " " . $nom . "))", "filter", $config['dn']['people']);

    if (count($tab1) > 0) {
        for ($i = 0; $i < count($tab1); $i ++) {
            $tab2 = search_ad($config, $tab1[$i]['cn'], "user");
            if (count($tab2) == 0) {
                $trouve ++;
                $cn = $tab1[$i]['cn'];
                // echo "<p>cn=$cn</p>";
            }
        }

        // On ne cherche a traiter que le cas d'une seule correspondance.
        // S'il y en a plus, on ne pourra pas identifier...
        if ($trouve == 1) {
            return $cn;
        } else {
            return false;
        }
    } else {
        // On fait en sorte de ne pas avoir d'accents dans la branche People de l'annuaire
        $nom = remplace_accents(traite_espaces($nom));
        $prenom = remplace_accents(traite_espaces($prenom));
        $tab1 = search_ad($config, "(&(objectclass=user)(displayname=" . $prenom . " " . $nom . "))", "filter", $config['dn']['people']);

        if (count($tab1) > 0) {
            for ($i = 0; $i < count($tab1); $i ++) {
                $tab2 = search_ad($config, $tab1[$i]['cn'], "user");
                if (count($tab2) == 0) {
                    $trouve ++;
                    $cn = $tab1[$i]['cn'];
                    // echo "<p>cn=$cn</p>";
                }
            }

            // On ne cherche a traiter que le cas d'une seule correspondance.
            // S'il y en a plus, on ne pourra pas identifier...
            if ($trouve == 1) {
                return $cn;
            } else {
                return false;
            }
        }
    }
}

// ================================================

/**
 *
 * Verifie et corrige le nom et date de naissance
 *
 * @Parametres
 *
 * @return
 *
 */
function verif_et_corrige_user($cn, $naissance, $sexe, $simulation = "N")
{
    $tab = search_user($config, $cn);
    if (count($tab) > 0) {
        if (($tab['sexe'] != $sexe) || ($tab['date'] != $naissance)) {
            $attributs = array();
            $attributs["physicaldeliveryoffice"] = "$naissance,$sexe";
            my_echo("Correction des attributs: ");

            $infos_corrections_gecos .= "Correction  date de naissance ou sexe de <b>$cn</b><br />\n";

            if ($simulation != 'y') {
                if (modify_ad($config, $cn, $attributs, "replace")) {
                    my_echo("<font color='green'>SUCCES</font>");
                } else {
                    my_echo("<font color='red'>ECHEC</font>");
                    $nb_echecs ++;
                }
            } else {
                my_echo("<font color='blue'>SIMULATION</font>");
            }
            my_echo("<br />\n");
        }
    }
}

/**
 *
 * Verifie et corrige le prénom
 *
 * @Parametres
 *
 * @return
 *
 */
function verif_et_corrige_prenom($cn, $prenom, $simulation = "N")
{
    // Verification/correction du givenName
    // Correction du nom/prenom fournis
    $prenom = remplace_accents(traite_espaces($prenom));

    $prenom = preg_replace("/[^a-z_-]/", "", strtolower("$prenom"));

    // FAUT-IL LA MAJUSCULE?
    $prenom = ucfirst(strtolower($prenom));

    $tab = search_user($config, $cn);
    if (count($tab) > 0) {
        if ($tab['givenName'] != "$prenom") {
            $attributs = array();
            $attributs["givenName"] = $prenom;
            my_echo("Correction de l'attribut 'givenName': ");
            if ($simulation != 'y') {
                if (modify_ad($config, $cn, $attributs, "replace")) {
                    my_echo("<font color='green'>SUCCES</font>");
                } else {
                    my_echo("<font color='red'>ECHEC</font>");
                    $nb_echecs ++;
                }
            } else {
                my_echo("<font color='blue'>SIMULATION</font>");
            }
            my_echo("<br />\n");
        }
    }
}

/**
 *
 * Verifie et corrige le pseudo
 *
 * @Parametres
 *
 * @return
 *
 */
function verif_et_corrige_pseudo($cn, $nom, $prenom, $annuelle = "y", $simulation = "N")
{
    // Verification/correction de l'attribut choisi pour le pseudo
    // Correction du nom/prenom fournis
    $nom = remplace_accents(traite_espaces($nom));
    $prenom = remplace_accents(traite_espaces($prenom));

    $nom = preg_replace("/[^a-z_-]/", "", strtolower("$nom"));
    $prenom = preg_replace("/[^a-z_-]/", "", strtolower("$prenom"));

    $tab = search_user($config, $cn);
    $tmp_pseudo = strtolower($prenom) . strtoupper(substr($nom, 0, 1));
    if (count($tab) > 0) {
        // Si le pseudo existe déjà, on ne réinitialise le pseudo que lors d'un import annuel
        if ($annuelle == "y") {
            // my_echo("\$tab[0]=".$tab[0]." et \$prenom=$prenom<br />");
            // $tmp_pseudo=strtolower($prenom).strtoupper(substr($nom,0,1));
            if ($tab['initials'] != "$tmp_pseudo") {
                $attributs = array();
                $attributs['initials'] = $tmp_pseudo;
                my_echo("Correction de l'attribut 'initials': ");
                if ($simulation != 'y') {
                    if (modify_ad($config, $cn, $attributs, "replace")) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                        $nb_echecs ++;
                    }
                } else {
                    my_echo("<font color='blue'>SIMULATION</font>");
                }
                my_echo("<br />\n");
            }
        }
    } else {
        // L'attribut pseudo n'existait pas:
        unset($attributs);
        $attributs = array();
        $attributs['initials'] = $tmp_pseudo;
        my_echo("Renseignement de l'attribut 'initials': ");
        if ($simulation != 'y') {
            if (modify_ad($config, $cn, $attributs, "replace")) {
                my_echo("<font color='green'>SUCCES</font>");
            } else {
                my_echo("<font color='red'>ECHEC</font>");
                $nb_echecs ++;
            }
        } else {
            my_echo("<font color='blue'>SIMULATION</font>");
        }
        my_echo("<br />\n");
    }
}

function get_cn_from_f_cn_file($employeeNumber)
{
    global $dossier_tmp_import_comptes;

    if (! file_exists("$dossier_tmp_import_comptes/f_cn.txt")) {
        return false;
    } else {
        $ftmp = fopen("$dossier_tmp_import_comptes/f_cn.txt", "r");
        while (! feof($ftmp)) {
            $ligne = trim(fgets($ftmp, 4096));

            if ($tab = explode(";", $ligne)) {
                if ("$tab[0]" == "$employeeNumber") {
                    // On controle le login
                    if (strlen(preg_replace("/[A-Za-z0-9._\-]/", "", $tab[1])) == 0) {
                        return $tab[1];
                    } else {
                        return false;
                    }
                    break;
                }
            }
        }
    }
}

// Les temps sont durs, il faut faire les poubelles pour en recuperer des choses...
function recup_from_trash($config, $cn)
{
    $user = search_ad($config, $cn, "user", $config['dn']['trash']);

    $recup = false;
    $f = fopen("/tmp/recup_from_trash.txt", "a+");
    foreach ($user[0] as $key => $value) {
        fwrite($f, "\$user[0]['$key']=$value\n");
    }
    fwrite($f, "=======================\n");
    fclose($f);
    // Ajout dans la branche people
    if (move_ad($config, $user[0]['cn'], "cn=" . $user[0]['cn'] . "," . $config['dn']['people'], "user")) {
        $f = fopen("/tmp/recup_from_trash.txt", "a+");
        fwrite($f, "\ldap_add OK\n");
        fwrite($f, "=======================\n");
        $recup = true;
    } else {
        $recup = false;
    }

    return $recup;
}

// ====================================================
function formate_date_aaaammjj($date)
{
    $tab_date = explode("/", $date);

    $retour = "";

    if (isset($tab_date[2])) {
        $retour .= sprintf("%04d", $tab_date[2]) . sprintf("%02d", $tab_date[1]) . sprintf("%02d", $tab_date[0]);
    } else {
        $retour .= $date;
    }

    return $retour;
}

/**
 * Cette méthode prend une chaîne de caractères et s'assure qu'elle est bien retournée en UTF-8
 * Attention, certain encodages sont très similaire et ne peuve pas être théoriquement distingué sur une chaine de caractere.
 * Si vous connaissez déjà l'encodage de votre chaine de départ, il est préférable de le préciser
 *
 * @param string $str
 *            La chaine à encoder
 * @param string $encoding
 *            L'encodage de départ
 * @return string La chaine en utf8
 * @throws Exception si la chaine n'a pas pu être encodée correctement
 */
function ensure_utf8($str, $from_encoding = null)
{
    if ($str === null || $str === '') {
        return $str;
    } else if ($from_encoding == null && detect_utf8($str)) {
        return $str;
    }

    if ($from_encoding != null) {
        $encoding = $from_encoding;
    } else {
        $encoding = detect_encoding($str);
    }
    $result = null;
    if ($encoding !== false && $encoding != null) {
        if (function_exists('mb_convert_encoding')) {
            $result = mb_convert_encoding($str, 'UTF-8', $encoding);
        }
    }
    if ($result === null || ! detect_utf8($result)) {
        throw new Exception('Impossible de convertir la chaine vers l\'utf8');
    }
    return $result;
}

/**
 * Cette méthode prend une chaîne de caractères et teste si elle ne contient que
 * de l'ASCII 7 bits ou si elle contient au moins une suite d'octets codant un
 * caractère en UTF8
 *
 * @param string $str
 *            La chaine à tester
 * @return boolean
 */
function detect_utf8($str)
{
    // Inspiré de http://w3.org/International/questions/qa-forms-utf-8.html
    //
    // on s'assure de bien opérer sur une chaîne de caractère
    $str = (string) $str;
    // La chaîne ne comporte que des octets <= 7F ?
    $full_ascii = true;
    $i = 0;
    while ($full_ascii && $i < strlen($str)) {
        $full_ascii = $full_ascii && (ord($str[$i]) <= 0x7F);
        $i ++;
    }
    // Si oui c'est de l'utf8 sinon on cherche si la chaîne contient
    // au moins une suite d'octets valide en UTF8
    if ($full_ascii)
        return true;
    else
        return preg_match('#[\xC2-\xDF][\x80-\xBF]#', $str) || // non-overlong 2-byte
        preg_match('#\xE0[\xA0-\xBF][\x80-\xBF]#', $str) || // excluding overlongs
        preg_match('#[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}#', $str) || // straight 3-byte
        preg_match('#\xED[\x80-\x9F][\x80-\xBF]#', $str) | // excluding surrogates
        preg_match('#\xF0[\x90-\xBF][\x80-\xBF]{2}#', $str) || // planes 1-3
        preg_match('#[\xF1-\xF3][\x80-\xBF]{3}#', $str) || // planes 4-15
        preg_match('# \xF4[\x80-\x8F][\x80-\xBF]{2}#', $str); // plane 16
}

/**
 * Cette méthode prend une chaîne de caractères et teste si elle est bien encodée en UTF-8
 *
 * @param string $str
 *            La chaine à tester
 * @return boolean
 */
function check_utf8($str)
{
    // Longueur maximale de la chaîne pour éviter un stack overflow
    // dans le test à base d'expression régulière
    $long_max = 1000;
    if (substr(PHP_OS, 0, 3) == 'WIN')
        $long_max = 300; // dans le cas de Window$
    if (mb_strlen($str) < $long_max) {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        $preg_match_result = 1 == preg_match('%^(?:
		[\x09\x0A\x0D\x20-\x7E]				# ASCII
		| [\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
		|  \xE0[\xA0-\xBF][\x80-\xBF]			# excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}		# straight 3-byte
		|  \xED[\x80-\x9F][\x80-\xBF]			# excluding surrogates
		|  \xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
		|  \xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
	)*$%xs', $str);
    } else {
        $preg_match_result = FALSE;
    }
    if ($preg_match_result) {
        return true;
    } else {
        // le test preg renvoie faux, et on va vérifier avec d'autres fonctions
        $result = true;
        $test_done = false;
        if (function_exists('mb_check_encoding')) {
            $test_done = true;
            $result = $result && @mb_check_encoding($str, 'UTF-8');
        }

        if (function_exists('mb_detect_encoding')) {
            $test_done = true;
            $result = $result && @mb_detect_encoding($str, 'UTF-8', true);
        }
        if (function_exists('iconv')) {
            $test_done = true;
            $result = $result && ($str === (@iconv('UTF-8', 'UTF-8//IGNORE', $str)));
        }
        if (function_exists('mb_convert_encoding') && ! $test_done) {
            $test_done = true;
            $result = $result && ($str === @mb_convert_encoding(@mb_convert_encoding($str, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'));
        }
        return ($test_done && $result);
    }
}

/**
 * Cette méthode prend une chaîne de caractères et détecte son encodage
 *
 * @param string $str
 *            La chaine à tester
 * @return l'encodage ou false si indétectable
 */
function detect_encoding($str)
{
    // on commence par vérifier si c'est de l'utf8
    if (detect_utf8($str)) {
        return 'UTF-8';
    }

    // on va commencer par tester ces encodages
    static $encoding_list = array(
        'UTF-8',
        'ISO-8859-15',
        'windows-1251'
    );
    foreach ($encoding_list as $item) {
        if (function_exists('iconv')) {
            $sample = @iconv($item, $item, $str);
            if (md5($sample) == md5($str)) {
                return $item;
            }
        } else if (function_exists('mb_detect_encoding')) {
            if (@mb_detect_encoding($str, $item, true)) {
                return $item;
            }
        }
    }

    // la méthode précédente n'a rien donnée
    if (function_exists('mb_detect_encoding')) {
        return mb_detect_encoding($str);
    } else {
        return false;
    }
}

/**
 * Cette méthode prend une chaîne de caractères et s'assure qu'elle est bien retournée en ASCII
 * Attention, certain encodages sont très similaire et ne peuve pas être théoriquement distingué sur une chaine de caractere.
 * Si vous connaissez déjà l'encodage de votre chaine de départ, il est préférable de le préciser
 *
 * @param string $chaine
 *            La chaine à encoder
 * @param string $encoding
 *            L'encodage de départ
 * @return string La chaine en ascii
 */
function ensure_ascii($chaine, $encoding = '')
{
    if ($chaine == null || $chaine == '') {
        return $chaine;
    }

    $chaine = ensure_utf8($chaine, $encoding);
    $str = null;
    if (function_exists('iconv')) {
        // test : est-ce que iconv est bien implémenté sur ce système ?
        $test = 'c\'est un bel ete' === iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", 'c\'est un bel été');
        if ($test) {
            // on utilise iconv pour la conversion
            $str = @iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $chaine);
        }
    }
    if ($str === null) {
        // on utilise pas iconv pour la conversion
        $translit = array(
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'Ã' => 'A',
            'Å' => 'A',
            'Ç' => 'C',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ï' => 'I',
            'Î' => 'I',
            'Ì' => 'I',
            'Ñ' => 'N',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Ö' => 'O',
            'Õ' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'ç' => 'c',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ñ' => 'n',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y'
        );
        $str = strtr($chaine, $translit);
    }
    if (function_exists('mb_convert_encoding')) {
        $str = @mb_convert_encoding($str, 'ASCII', 'UTF-8');
    }
    return $str;
}
?>
