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

/**
 *
 * Fonction de generation de mot de passe recuperee sur TotallyPHP
 * Aucune mention de licence pour ce script...
 *
 * @Parametres
 * @return string $password
 *        
 *         The letter l (lowercase L) and the number 1
 *         have been removed, as they can be mistaken
 *         for each other.
 */
function createRandomPassword(int $nb_chars, bool $complex = false)
{
    if ($complex) {
        $chars = 'ABCDEFGHIJKLMN_PQRSTUVWXYZ&*$abcdefghijkmnopqrstuvwxyz023456789';
        $nchars = 62;
    } else {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        $nchars = 33;
    }
    srand((double) microtime() * 1000000);
    $i = 0;
    $pass = '';

    while ($i <= $nb_chars) {
        $num = rand() % $nchars;
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
function create_echo_file($timestamp)
{
    $fich = fopen("/var/www/sambaedu/tmp/result." . $timestamp . ".html", "w+");
    fwrite($fich, "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
<style type='text/css'>
body{
    background: url($background) ghostwhite bottom right no-repeat fixed;
}
</style>
<!--head-->
<title>Import de comptes</title>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<!--meta http-equiv='Refresh' CONTENT='120;URL=result.$timestamp.html#menu' /-->
<link type='text/css' rel='stylesheet' href='$stylecss' />
<body>
<h1 style='text-align:center;'>Import de comptes</h1>
        
<div id='decompte' style='float: right; border: 1px solid black;'></div>
        
<script type='text/javascript'>
cpt=120;
compte_a_rebours='y';
        
        
/**
* Decompte le temps mis pour l'import sconet
* @language Javascript
* @Parametres
* @return le decompte qui s'affiche
*/
        
        
function decompte(cpt){
	if(compte_a_rebours=='y'){
		document.getElementById('decompte').innerHTML=cpt;
		if(cpt>0){
			cpt--;
		}
		else{
			document.location='result.$timestamp.html';
		}
        
		setTimeout(\"decompte(\"+cpt+\")\",1000);
	}
	else{
		document.getElementById('decompte').style.display='none';
	}
}
        
decompte(cpt);
</script>\n");
    fclose($fich);
}

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
    $chaine = preg_replace("/'/", " ", $chaine);
    $tab = explode(" ", $chaine, 2);
    if (isset($tab[1])) {
        return $tab[0] . "_" . preg_replace("/ /", "-", $tab[1]);
    }
    return $tab[0];
}

function supprime_espaces($chaine)
{
    return preg_replace("/ /", "", $chaine);
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

    $nom = strtolower($nom);
    $prenom = strtolower($prenom);

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
    $tab = array();
    // $tab1=get_tab_attribut("people","cn='$prenom $nom'",$attribut);
    $nom1 = remplace_accents(traite_espaces($nom));
    $prenom1 = remplace_accents(traite_espaces($prenom));
    $nom2 = supprime_espaces(preg_replace("/'/", "", $nom));
    $prenom2 = supprime_espaces(preg_replace("/'/", "", $prenom));

    $tab = search_ad($config, "(&(objectclass=user)(|(displayname=" . $prenom . " " . $nom . ")(displayname=" . $prenom1 . " " . $nom1 . ")(displayname=" . $prenom2 . " " . $nom2 . ")))", "filter", $config['dn']['people']);

    if (count($tab) == 1) {
        return $tab[0]['cn'];
        return false;
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
function verif_et_corrige_user($config, $cn, $naissance, $sexe, $simulation = "N")
{
    $ret = true;
    $tab = search_user($config, $cn);
    if (count($tab) > 0) {
        if (! isset($tab['sexe'], $tab['naissance'])) {
            $tab['sexe'] = "";
            $tab['naissance'] = "";
        }
        if (($tab['sexe'] != $sexe) || ($tab['date'] != $naissance)) {
            $attributs = array();
            $attributs["physicaldeliveryofficename"] = "$naissance,$sexe";
            my_echo("Correction des attributs: ");

            my_echo("Correction  date de naissance ou sexe de <b>$cn</b>");

            if ($simulation != 'y') {
                if (modify_ad($config, $cn, "user", $attributs, "replace")) {
                    my_echo("<font color='green'>SUCCES</font>");
                } else {
                    my_echo("<font color='red'>ECHEC</font>");
                    $ret = false;
                }
            } else {
                my_echo("<font color='blue'>SIMULATION</font>");
            }
            my_echo("<br />\n");
        }
    }
    return $ret;
}

/**
 *
 * Verifie et corrige le prénom et le nom
 *
 * @Parametres
 *
 * @return
 *
 */
function verif_et_corrige_nom($config, $cn, $prenom, $nom, $simulation = "N")
{
    // Verification/correction du givenName
    // Correction du nom/prenom fournis
    $prenom = remplace_accents(traite_espaces($prenom));

    $prenom = preg_replace("/[^a-z_-]/", "", strtolower("$prenom"));

    $prenom = ucfirst(strtolower($prenom));
    $nom = remplace_accents(traite_espaces($nom));

    $nom = preg_replace("/[^a-z_-]/", "", strtolower("$nom"));

    $nom = ucfirst(strtolower($nom));

    $success = false;
    $tab = search_user($config, $cn);
    if (count($tab) > 0) {
        $attributs = array();
        if (! isset($tab['prenom']))
            $tab['prenom'] = "";
        if ($tab['prenom'] != "$prenom") {
            $attributs["givenName"] = $prenom;
            my_echo("Correction de l'attribut 'givenName': ");
        }
        if (! isset($tab['nom']))
            $tab['nom'] = "";
        if (isset($tab['nom']) && ($tab['nom'] != "$nom")) {
            $attributs["sn"] = $nom;
            my_echo("Correction de l'attribut 'sn': ");
        }
        if (count($attributs) > 0) {
            if ($simulation != 'y') {
                if (modify_ad($config, $cn, "user", $attributs, "replace")) {
                    my_echo("<font color='green'>SUCCES</font>");
                    $success = true;
                } else {
                    my_echo("<font color='red'>ECHEC</font>");
                    $success = false;
                }
            } else {
                my_echo("<font color='blue'>SIMULATION</font>");
                $success = true;
            }
            my_echo("<br />\n");
        }
    }
    return $success;
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
function verif_et_corrige_pseudo($config, $cn, $nom, $prenom, $annuelle = "y", $simulation = "N")
{
    // Verification/correction de l'attribut choisi pour le pseudo
    // Correction du nom/prenom fournis
    $nom = remplace_accents(traite_espaces($nom));
    $prenom = remplace_accents(traite_espaces($prenom));

    $nom = preg_replace("/[^a-z_-]/", "", strtolower("$nom"));
    $prenom = preg_replace("/[^a-z_-]/", "", strtolower("$prenom"));

    $tab = search_user($config, $cn);
    $tmp_pseudo = strtoupper(substr($prenom, 0, 1)) . "." . strtoupper(substr($nom, 0, 1));
    if (count($tab) > 0) {
        // Si le pseudo existe déjà, on ne réinitialise le pseudo que lors d'un import annuel
        if ($annuelle == "y") {
            // my_echo("\$tab[0]=".$tab[0]." et \$prenom=$prenom<br />");
            // $tmp_pseudo=strtolower($prenom).strtoupper(substr($nom,0,1));
            if (! isset($tab['pseudo']))
                $tab['pseudo'] = "";
            if ($tab['pseudo'] != "$tmp_pseudo") {
                $attributs = array();
                $attributs['initials'] = $tmp_pseudo;
                my_echo("Correction de l'attribut 'initials': ");
                if ($simulation != 'y') {
                    if (modify_ad($config, $cn, "user", $attributs, "replace")) {
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
            if (modify_ad($config, $cn, "user", $attributs, "add")) {
                my_echo("<font color='green'>SUCCES</font>");
            } else {
                my_echo("<font color='red'>ECHEC</font>");
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

function import_comptes(array $config, array $args)
{
    global $echo_file, $dest_mode;

    $type_fichier_eleves = $args[0];
    $eleves_file = $args[1];
    $sts_xml_file = $args[2];
    $prefix = $args[3];
    $annuelle = $args[4];
    $simulation = $args[5];
    $timestamp = $args[6];
    $randval = $args[7];
    $temoin_creation_fichiers = $args[8];
    $chrono = $args[9];

    // ===========================================================
    // AJOUTS: 20070914 boireaus
    $creer_equipes_vides = $args[10];
    $creer_cours = $args[11];
    $creer_matieres = $args[12];
    // ===========================================================
    $corriger_gecos_si_diff = $args[13];
    // ===========================================================
    $temoin_f_cn = $args[14];
    // ===========================================================
    $alimenter_groupe_pp = $args[15];
    // ===========================================================

    // Pour effectuer des affichages de debug:

    $debug_import_comptes = "n";

    $racine_www = "/var/www/sambaedu";
    $www_import = "/annu/import_sconet.php";
    $chemin_http_csv = "setup/csv/" . $timestamp . "_" . $randval;
    $dossiercsv = $racine_www . "/" . $chemin_http_csv;
    $echo_file = "$racine_www/tmp/result.$timestamp.html";
    $echo_http_file = "http://admin." . $config['domain'] . "/tmp/result." . $timestamp . ".html";
    $dossier_tmp_import_comptes = "/var/www/sambaedu/tmp";
    $pathscripts = "/usr/share/sambaedu/scripts";
    $user_web = "www-admin";

    $rafraichir_classes = "n";
    if ((isset($args[16])) && ($args[16] == "y")) {
        $rafraichir_classes = "y";
    }

    // AJOUT: 20080610
    $attribut_pseudo = "initials";
    $controler_pseudo = "y";
    $corriger_givenname_si_diff = "y";
    $liste_caracteres_accentues = "ÂÄÀÁÃÅÇÊËÈÉÎÏÌÍÑÔÖÒÓÕØ¦ÛÜÙÚÝ¾´áàâäãåçéèêëîïìíñôöðòóõø¨ûüùúýÿ¸";

    // TODO : créer une session indépendante pour pouvoir suivre le process en asynchrone ?

    // $debug_import_comptes peut être initialisée dans se3orlcs_import_comptes.php
    // $debug_import_comptes="y";

    // Choix de destination des my_echo():
    $dest_mode = "file";
    // On va écrire dans le fichier $echo_file et non dans la page courante... ce qui serait problematique depuis que cette page PHP n'est plus visitee depuis un navigateur.

    // Date et heure...
    $aujourdhui2 = getdate();
    $annee_aujourdhui2 = $aujourdhui2['year'];
    $mois_aujourdhui2 = sprintf("%02d", $aujourdhui2['mon']);
    $jour_aujourdhui2 = sprintf("%02d", $aujourdhui2['mday']);
    $heure_aujourdhui2 = sprintf("%02d", $aujourdhui2['hours']);
    $minute_aujourdhui2 = sprintf("%02d", $aujourdhui2['minutes']);
    $seconde_aujourdhui2 = sprintf("%02d", $aujourdhui2['seconds']);

    $debut_import = "$jour_aujourdhui2/$mois_aujourdhui2/$annee_aujourdhui2 à $heure_aujourdhui2:$minute_aujourdhui2:$seconde_aujourdhui2";
    // Pour ne pas faire de betises en cours d'annee scolaire et se retrouver avec un nom de classe qui change en cours d'annee parce qu'on se serait mis a virer les accents dans les noms de classes:
    if (! isset($config['clean_caract_classe']) || ($config['clean_caract_classe'] == "y")) {
        // On ne passera a 'y' que lors d'un import annuel.
        set_param($config, "clean_caract_classe", "n");
    }
    $nouveaux_comptes = 0;
    $comptes_avec_employeeNumber_mis_a_jour = 0;
    $nb_echecs = 0;

    $tab_nouveaux_comptes = array();
    $tab_comptes_avec_employeeNumber_mis_a_jour = array();

    // listing pour l'impression des comptes
    $listing = array(
        array()
    ); // une ligne par compte ; le deuxieme parametre est, dans l'ordre nom, prenom, classe (ou 'prof'), cn, password

    // my_echo("\$creer_equipes_vides=$creer_equipes_vides<br />");

    my_echo("<div id='div_signalements' style='display:none; color:red;'><strong>Signalements</strong><br /></div>\n");

    my_echo("<a name='menu'></a>");
    my_echo("<h3>Menu</h3>");
    my_echo("<blockquote>\n");
    my_echo("<p>Aller à la section</p>\n");
    my_echo("<table border='0'>\n");
    my_echo("<tr>\n");
    my_echo("<td>- </td>\n");
    my_echo("<td>création des comptes professeurs: </td><td><span id='id_creer_profs' style='display:none;'><a href='#creer_profs'>Clic</a></span></td>\n");
    my_echo("</tr>\n");
    my_echo("<tr>\n");
    my_echo("<td>- </td>\n");
    my_echo("<td>création des comptes élèves: </td><td><span id='id_creer_eleves' style='display:none;'><a href='#creer_eleves'>Clic</a></span></td>\n");
    my_echo("</tr>\n");
    if ($simulation != "y") {
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");
        my_echo("<td>création des classes et des équipes: </td><td><span id='id_creer_classes' style='display:none;'><a href='#creer_classes'>Clic</a></span></td>\n");
        my_echo("</tr>\n");
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");
        // ===========================================================
        // AJOUTS: 20070914 boireaus
        if ($creer_matieres == 'y') {
            my_echo("<td>création des matières: </td><td><span id='id_creer_matieres' style='display:none;'><a href='#creer_matieres'>Clic</a></span></td>\n");
        } else {
            my_echo("<td>création des matières: </td><td><span id='id_creer_matieres' style='color:red;'>non demandée</span></td>\n");
        }
        // ===========================================================
        my_echo("</tr>\n");
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");

        // ===========================================================
        // AJOUTS: 20070914 boireaus
        if ($creer_cours == 'y') {
            my_echo("<td>création des cours: </td><td><span id='id_creer_cours' style='display:none;'><a href='#creer_cours'>Clic</a></span></td>\n");
        } else {
            my_echo("<td>création des cours: </td><td><span id='id_creer_cours' style='color:red;'>non demandée</span></td>\n");
        }
        // ===========================================================
        my_echo("</tr>\n");
    }
    my_echo("<tr>\n");
    my_echo("<td>- </td>\n");
    my_echo("<td>compte rendu final de ");
    if ($simulation == "y") {
        my_echo("simulation");
    } else {
        my_echo("création");
    }
    my_echo(": </td><td><span id='id_fin' style='display:none;'><a href='#fin'>Clic</a></span></td>\n");
    my_echo("</tr>\n");
    my_echo("</table>\n");
    my_echo("</blockquote>\n");

    // exit;

    if ($temoin_creation_fichiers == "oui") {
        my_echo("<h3>Fichiers CSV</h3>");
        my_echo("<blockquote>\n");
        my_echo("<p>Récupérer le fichier:</p>\n");
        my_echo("<table border='0'>\n");
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");
        // my_echo("<td>F_ele.txt: </td><td><span id='id_f_ele_txt' style='display:none;'><a href='$dossiercsv/f_ele.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("<td>F_ele.txt: </td><td><span id='id_f_ele_txt' style='display:none;'><a href='/$chemin_http_csv/f_ele.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("</tr>\n");
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");
        // my_echo("<td>F_div.txt: </td><td><span id='id_f_div_txt' style='display:none;'><a href='$dossiercsv/f_div.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("<td>F_div.txt: </td><td><span id='id_f_div_txt' style='display:none;'><a href='/$chemin_http_csv/f_div.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("</tr>\n");
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");
        // my_echo("<td>F_men.txt: </td><td><span id='id_f_men_txt' style='display:none;'><a href='$dossiercsv/f_men.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("<td>F_men.txt: </td><td><span id='id_f_men_txt' style='display:none;'><a href='/$chemin_http_csv/f_men.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("</tr>\n");
        my_echo("<tr>\n");
        my_echo("<td>- </td>\n");
        // my_echo("<td>F_wind.txt: </td><td><span id='id_f_wind_txt' style='display:none;'><a href='$dossiercsv/f_wind.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("<td>F_wind.txt: </td><td><span id='id_f_wind_txt' style='display:none;'><a href='/$chemin_http_csv/f_wind.txt' target='_blank'>Clic</a></span></td>\n");
        my_echo("</tr>\n");
        my_echo("</table>\n");
        // my_echo("<p>Supprimer les fichiers générés: <span id='id_suppr_txt' style='display:none;'><a href='".$_SERVER['PHP_SELF']."?nettoyage=oui&amp;dossier=".$timestamp."_".$randval."' target='_blank'>Clic</a></span></p>\n");
        my_echo("<p>Supprimer les fichiers générés: <span id='id_suppr_txt' style='display:none;'><a href='$www_import?nettoyage=oui&amp;dossier=" . $timestamp . "_" . $randval . "' target='_blank'>Clic</a></span></p>\n");
        my_echo("</blockquote>\n");
    }

    // Nom du groupe professeurs principaux
    $nom_groupe_pp = "Profs_Principaux";

    $tab_no_Trash_prof = array();
    $tab_no_Trash_eleve = array();

    // Suppression des anciens groupes si l'importation est annuelle:
    // if(isset($_POST['annuelle'])) {
    if ($annuelle == "y") {

        $tmp_tab_no_Trash_user = list_members_rights($config, "no_Trash_user");
        if (count($tmp_tab_no_Trash_user) > 0) {
            $cpt_trash_ele = 0;
            $cpt_trash_prof = 0;

            my_echo("<p>Quelques comptes doivent être préservés de la Corbeille (<i>dispositif no_Trash_user</i>)&nbsp;:<br />\n");

            for ($loop = 0; $loop < count($tmp_tab_no_Trash_user); $loop ++) {
                // my_echo("\$tmp_tab_no_Trash_user[$loop]=$tmp_tab_no_Trash_user[$loop]<br />");
                if ($loop > 0) {
                    my_echo(", ");
                }
                my_echo("$tmp_tab_no_Trash_user[$loop]");
                if (is_prof($config, $tmp_tab_no_Trash_user[$loop])) {
                    my_echo("(<i>prof</i>)");
                    $tab_no_Trash_prof[$cpt_trash_prof] = $tmp_tab_no_Trash_user[$loop];
                    $cpt_trash_prof ++;
                } elseif (is_prof($config, $tmp_tab_no_Trash_user[$loop])) {
                    my_echo("(<i>élève</i>)");
                    $tab_no_Trash_eleve[$cpt_trash_ele] = $tmp_tab_no_Trash_user[$loop];
                    $cpt_trash_ele ++;
                }
            }
        }

        for ($loop = 0; $loop < count($tab_no_Trash_prof); $loop ++) {
            my_echo("\$tab_no_Trash_prof[$loop]=$tab_no_Trash_prof[$loop]<br />");
        }

        for ($loop = 0; $loop < count($tab_no_Trash_eleve); $loop ++) {
            my_echo("\$tab_no_Trash_eleve[$loop]=$tab_no_Trash_eleve[$loop]<br />");
        }

        if ($simulation != "y") {
            my_echo("<h3>Importation annuelle");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h3>\n");
            my_echo("<blockquote>\n");

            // ==========================================================
            // On profite d'une mise a jour annuelle pour passer en mode sans accents sur les caracteres dans les noms de classes (pour eviter des blagues dans la creation de dossiers de classes,...)
            set_param($config, "clean_caract_classe", "y");
            // ==========================================================

            /*
             * if (file_exists($sts_xml_file)) {
             * unset($attribut);
             * $attribut = array(
             * "member"
             * );
             * $tab = get_tab_attribut("groups", "cn=Profs", $attribut);
             * if (count($tab) > 0) {
             * my_echo("<p>On vide le groupe Profs.<br />\n");
             *
             * my_echo("Suppression de l'appartenance au groupe de: \n");
             * for ($i = 0; $i < count($tab); $i ++) {
             * if ($i == 0) {
             * $sep = "";
             * } else {
             * $sep = ", ";
             * }
             * my_echo($sep);
             *
             * unset($attr);
             * $attr = array();
             * $attr["member"] = $tab[$i];
             * if (modify_attribut("cn=Profs", "groups", $attr, "del")) {
             * my_echo($tab[$i]);
             * } else {
             * my_echo("<font color='red'>" . $tab[$i] . "</font>");
             * }
             * }
             * my_echo("</p>\n");
             * } else {
             * my_echo("<p>Le groupe Profs est déjà vide.</p>\n");
             * }
             * if ($chrono == 'y') {
             * my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
             * }
             * }
             *
             * if (file_exists($eleves_file)) {
             * unset($attribut);
             * $attribut = array(
             * "member"
             * );
             * $tab = get_tab_attribut("groups", "cn=Eleves", $attribut);
             * if (count($tab) > 0) {
             * my_echo("<p>On vide le groupe Eleves.<br />\n");
             *
             * my_echo("Suppression de l'appartenance au groupe de: \n");
             * for ($i = 0; $i < count($tab); $i ++) {
             * if ($i == 0) {
             * $sep = "";
             * } else {
             * $sep = ", ";
             * }
             * my_echo($sep);
             *
             * unset($attr);
             * $attr = array();
             * $attr["member"] = $tab[$i];
             * if (modify_attribut("cn=Eleves", "groups", $attr, "del")) {
             * my_echo($tab[$i]);
             * } else {
             * my_echo("<font color='red'>" . $tab[$i] . "</font>");
             * }
             * }
             * my_echo("</p>\n");
             * } else {
             * my_echo("<p>Le groupe Eleves est déjà vide.</p>\n");
             * }
             * if ($chrono == 'y') {
             * my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
             * }
             * }
             */
            my_echo("<p>Suppression des groupes Classes, Equipes, Cours, PP et Matieres.</p>\n");

            // Recherche des classes,...
            $tab = search_ad($config, "(|(cn=Classe_*)(cn=Equipe_*)(cn=Cours_*)(cn=Matiere_*)(cn=PP_*))", "filter", $config['dn']['groups']);
            if (count($tab) > 0) {
                my_echo("<table border='0'>\n");
                foreach ($tab as $groupe) {
                    my_echo("<tr>");
                    my_echo("<td>");
                    my_echo("Suppression de " . $groupe['cn'] . ": ");
                    my_echo("</td>");
                    my_echo("<td>");
                    if (groupdel($config, $groupe['cn'])) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                    }
                    // my_echo("<br />\n");
                    my_echo("</td>");
                    my_echo("</tr>");
                }
                // my_echo("</p>\n");
                my_echo("</table>\n");
            }

            // Groupe Professeurs_Principaux
            /*
             * inutile
             * $attribut = array(
             * "cn"
             * );
             * $tabtmp = get_tab_attribut("groups", "cn=$nom_groupe_pp", $attribut);
             * if (count($tabtmp) > 0) {
             * unset($attribut);
             * $attribut = array(
             * "member"
             * );
             * $tab_mem_pp = get_tab_attribut("groups", "cn=$nom_groupe_pp", $attribut);
             * if (count($tab_mem_pp) > 0) {
             * my_echo("<p>On vide le groupe $nom_groupe_pp<br />\n");
             *
             * my_echo("Suppression de l'appartenance au groupe de: \n");
             * for ($i = 0; $i < count($tab_mem_pp); $i ++) {
             * if ($i == 0) {
             * $sep = "";
             * } else {
             * $sep = ", ";
             * }
             * my_echo($sep);
             *
             * unset($attr);
             * $attr = array();
             * $attr["member"] = $tab_mem_pp[$i];
             * if (modify_attribut("cn=$nom_groupe_pp", "groups", $attr, "del")) {
             * my_echo($tab_mem_pp[$i]);
             * } else {
             * my_echo("<font color='red'>" . $tab_mem_pp[$i] . "</font>");
             * }
             * }
             * my_echo("</p>\n");
             * } else {
             * my_echo("<p>Le groupe $nom_groupe_pp est vide.</p>\n");
             * }
             * }
             */
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");
            // return true;
        } else {
            my_echo("<h3>Importation annuelle");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h3>\n");
            my_echo("<blockquote>\n");
            my_echo("<p><b>Simulation</b> de la suppression des groupes Classes, Equipes, Cours et Matieres.</p>\n");
            my_echo("<p>Les groupes suivants seraient supprimés: ");
            // Recherche des classes,...
            $tab = search_ad($config, "(|(cn=Classe_*)(cn=Equipe_*)(cn=Cours_*)(cn=Matiere_*)(cn=PP_*))", "filter", $config['dn']['groups']);
            if (count($tab) > 0) {
                my_echo("$tab[0]['cn']");
                for ($i = 1; $i < count($tab); $i ++) {
                    my_echo(", $tab[$i]['cn']");
                }
            } else {
                my_echo("AUCUN GROUPE TROUVE");
            }
            my_echo("</p>");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
        }

        // Vider les fonds d'ecran pour que les eleves ne restent pas avec les noms de classes de l'annee precedente
        // my_echo("<p>On vide les fonds d'écran pour que les élèves ne restent pas avec les noms de classes de l'année précédente.</p>\n");
        // exec("/usr/bin/sudo $pathscripts/genere_fond.sh variable_bidon supprimer");
        if ($chrono == 'y') {
            my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
        }
    }

    // exit;

    // 20130115
    // Initialisation:
    $uaj = "";
    $uaj_tronque = "";
    $tab_eleve_autre_etab = array();

    // Partie ELEVES:
    // $type_fichier_eleves=isset($_POST['type_fichier_eleves']) ? $_POST['type_fichier_eleves'] : "csv";
    if ($type_fichier_eleves == "csv") {
        // $eleves_csv_file = isset($_FILES["eleves_csv_file"]) ? $_FILES["eleves_csv_file"] : NULL;

        // $eleves_csv_file = isset($_FILES["eleves_file"]) ? $_FILES["eleves_file"] : NULL;
        // $fp=fopen($eleves_csv_file['tmp_name'],"r");

        $fp = fopen($eleves_file, "r");
        if ($fp) {
            // my_echo("<h2>Section eleves</h2>\n");
            // my_echo("<h3>Section eleves</h3>\n");
            my_echo("<h3>Section élèves");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h3>\n");
            my_echo("<blockquote>\n");
            // my_echo("<h3>Lecture du fichier...</h3>\n");
            my_echo("<h4>Lecture du fichier élèves...</h4>\n");
            my_echo("<blockquote>\n");
            unset($ligne);
            $ligne = array();
            while (! feof($fp)) {
                // $ligne[]=fgets($fp,4096);
                // Suppression des guillemets s'il jamais il y en a dans le CSV
                // $ligne[]=ereg_replace('"','',fgets($fp,4096));
                $ligne[] = preg_replace('/"/', '', fgets($fp, 4096));
            }
            fclose($fp);

            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            // controle du contenu du fichier:
            if (stristr($ligne[0], "<?xml ")) {
                my_echo("<p style='color:red;'>ERREUR: Le fichier élèves fourni a l'air d'être de type XML et non CSV.</p>\n");
                my_echo("<script type='text/javascript'>
	compte_a_rebours='n';
</script>\n");
                my_echo("<div style='position:absolute; top: 50px; left: 300px; width: 400px; border: 1px solid black; background-color: red;'><div align='center'>ERREUR: Le fichier eleves fourni a l'air d'etre de type XML et non CSV.</div></div>");
                my_echo("</body>\n</html>\n");

                // Renseignement du temoin de mise a jour terminee.
                set_param($config, 'imprt_cmpts_en_cours', 'n');
                return false;
            }

            // my_echo("<h3>Affichage...</h3>\n");
            // my_echo("<h4>Affichage...</h4>\n");
            my_echo("<h4>Affichage...");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            my_echo("<p>Les lignes qui suivent sont le contenu du fichier fourni.<br />Ces lignes ne sont là qu'à des fins de débuggage.<p>\n");
            my_echo("<table border='0'>\n");
            $cpt = 0;
            while ($cpt < count($ligne)) {
                my_echo("<tr valign='top'>\n");
                my_echo("<td style='color: blue;'>$cpt</td><td>" . htmlentities($ligne[$cpt]) . "</td>\n");
                my_echo("</tr>\n");
                $cpt ++;
            }
            my_echo("</table>\n");
            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");
            my_echo("</blockquote>\n");

            my_echo("<a name='analyse'></a>\n");
            // my_echo("<h2>Analyse</h2>\n");
            // my_echo("<h3>Analyse</h3>\n");
            my_echo("<h3>Analyse");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h3>\n");
            my_echo("<blockquote>\n");
            // my_echo("<h3>Reperage des champs</h3>\n");
            // my_echo("<h4>Reperage des champs</h4>\n");
            my_echo("<h4>Repérage des champs");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");

            $champ = array(
                "Nom",
                "Prénom 1",
                "Date de naissance",
                "N° Interne",
                "Sexe",
                "Division"
            );
            // Analyse:
            // Reperage des champs souhaites:
            // $tabtmp=explode(";",$ligne[0]);
            $tabtmp = explode(";", trim($ligne[0]));
            for ($j = 0; $j < count($champ); $j ++) {
                $index[$j] = "-1";
                for ($i = 0; $i < count($tabtmp); $i ++) {
                    if ($tabtmp[$i] == $champ[$j]) {
                        my_echo("Champ '<font color='blue'>$champ[$j]</font>' repéré en colonne/position <font color='blue'>$i</font><br />\n");
                        $index[$j] = $i;
                    }
                }
                if ($index[$j] == "-1") {
                    my_echo("<p><font color='red'>ERREUR: Le champ '<font color='blue'>$champ[$j]</font>' n'a pas été trouvé.</font></p>\n");
                    my_echo("</blockquote>");
                    // my_echo("<p><a href='".$_SERVER['PHP_SELF']."'>Retour</a>.</p>\n");
                    my_echo("<p><a href='$www_import'>Retour</a>.</p>\n");
                    my_echo("<script type='text/javascript'>
	compte_a_rebours='n';
</script>\n");
                    my_echo("</blockquote></div></body></html>");
                    return false;
                }
            }
            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            my_echo("<h3>Remplissage des tableaux");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h3>\n");
            my_echo("<blockquote>\n");
            $cpt = 1;
            $tabnumero = array();
            $eleve = array();
            $temoin_format_num_interne = "";
            while ($cpt < count($ligne)) {
                if ($ligne[$cpt] != "") {
                    // $tabtmp=explode(";",$ligne[$cpt]);
                    $tabtmp = explode(";", trim($ligne[$cpt]));

                    // Si la division/classe n'est pas vide
                    if (isset($tabtmp[$index[5]])) {
                        if ($tabtmp[$index[5]] != "") {
                            if (strlen($tabtmp[$index[3]]) == 11) {
                                $numero = substr($tabtmp[$index[3]], 0, strlen($tabtmp[$index[3]]) - 6);
                            } else {
                                $temoin_format_num_interne = "non_standard";
                                if (strlen($tabtmp[$index[3]]) == 4) {
                                    $numero = "0" . $tabtmp[$index[3]];
                                } else {
                                    $numero = $tabtmp[$index[3]];
                                }
                            }

                            $temoin = 0;
                            for ($i = 0; $i < count($tabnumero); $i ++) {
                                if ($tabnumero[$i] == $numero) {
                                    $temoin = 1;
                                }
                            }
                            if ($temoin == 0) {
                                $tabnumero[] = $numero;
                                $eleve[$numero] = array();
                                $eleve[$numero]["numero"] = $numero;

                                $eleve[$numero]["nom"] = preg_replace("/[^A-Za-zÆæ¼½" . $liste_caracteres_accentues . "_ -]/", "", $tabtmp[$index[0]]);
                                $eleve[$numero]["prenom"] = preg_replace("/[^A-Za-zÆæ¼½" . $liste_caracteres_accentues . "_ -]/", "", $tabtmp[$index[1]]);

                                // =============================================
                                // On ne retient que le premier prénom: 20071101
                                $tab_tmp_prenom = explode(" ", $eleve[$numero]["prenom"]);
                                $eleve[$numero]["prenom"] = $tab_tmp_prenom[0];
                                // =============================================

                                unset($tmpdate);
                                $tmpdate = explode("/", $tabtmp[$index[2]]);
                                $eleve[$numero]["date"] = $tmpdate[2] . $tmpdate[1] . $tmpdate[0];
                                $eleve[$numero]["sexe"] = $tabtmp[$index[4]];

                                $eleve[$numero]["division"] = strtr(trim($tabtmp[$index[5]]), "/", "_");
                                if ($config['clean_caract_classe'] == "y") {
                                    $eleve[$numero]["division"] = preg_replace("/[^a-zA-Z0-9_ -]/", "", remplace_accents($eleve[$numero]["division"]));
                                }
                            }
                        }
                    }
                }
                $cpt ++;
            }
            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");
            // A CE STADE, LE TABLEAU $eleves N'EST REMPLI QUE POUR DES DIVISIONS NON VIDES (seuls les eleves affecte dans des classes sont retenus).

            my_echo("<a name='csv_eleves'></a>\n");
            my_echo("<h4>Affichage d'un CSV des élèves");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            if ($temoin_format_num_interne != "") {
                my_echo("<p style='color:red;'>ATTENTION: Le format des numéros internes des élèves n'a pas l'air standard.<br />Un préfixe 0 a dû être ajouté pour corriger.<br />Veillez à contrôler que vos numéros internes ont bien été analysés malgré tout.</p>\n");
            }
            // my_echo("");
            // if($temoin_creation_fichiers!="non") {$fich=fopen("$dossiercsv/se3/f_ele.txt","w+");}
            if ($temoin_creation_fichiers != "non") {
                $fich = fopen("$dossiercsv/f_ele.txt", "w+");
            } else {
                $fich = FALSE;
            }
            $tab_classe = array();
            $cpt_classe = - 1;
            for ($k = 0; $k < count($tabnumero); $k ++) {
                $temoin_erreur_eleve = "n";

                $numero = $tabnumero[$k];
                $chaine = "";
                $chaine .= $eleve[$numero]["numero"];
                $chaine .= "|";
                $chaine .= remplace_accents($eleve[$numero]["nom"]);
                $chaine .= "|";
                $chaine .= remplace_accents($eleve[$numero]["prenom"]);
                $chaine .= "|";
                $chaine .= $eleve[$numero]["date"];
                $chaine .= "|";
                $chaine .= $eleve[$numero]["sexe"];
                $chaine .= "|";
                $chaine .= $eleve[$numero]["division"];
                if ($fich) {
                    // fwrite($fich,$chaine."\n");
                    fwrite($fich, html_entity_decode($chaine) . "\n");
                }
                my_echo($chaine . "<br />\n");
            }
            if ($fich) {
                fclose($fich);
            }

            // my_echo("disk_total_space($dossiercsv)=".disk_total_space($dossiercsv)."<br />");
            if ($temoin_creation_fichiers != "non") {
                my_echo("<script type='text/javascript'>
	document.getElementById('id_f_ele_txt').style.display='';
</script>");
            }

            my_echo("</blockquote>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");
        } else {
            // my_echo("<p>ERREUR lors de l'ouverture du fichier ".$eleves_csv_file['name']." (<i>".$eleves_csv_file['tmp_name']."</i>).</p>\n");
            my_echo("<p>ERREUR lors de l'ouverture du fichier '$eleves_file'.</p>\n");
        }
    } else {
        // *****************************
        // C'est un fichier Eleves...XML
        // *****************************

        // Pour avoir acces aux erreurs XML:
        libxml_use_internal_errors(true);

        $ele_xml = simplexml_load_file($eleves_file);
        if ($ele_xml) {
            $nom_racine = $ele_xml->getName();
            if (strtoupper($nom_racine) != 'BEE_ELEVES') {
                my_echo("<p style='color:red;'>ERREUR: Le fichier XML fourni n'a pas l'air d'être un fichier XML Elèves.<br />Sa racine devrait être 'BEE_ELEVES'.</p>\n");
                my_echo("<script type='text/javascript'>
	compte_a_rebours='n';
</script>\n");
                my_echo("<div style='position:absolute; top: 50px; left: 300px; width: 400px; border: 1px solid black; background-color: red;'><div align='center'>ERREUR: Le fichier XML fourni n'a pas l'air d'être un fichier XML Elèves.<br />Sa racine devrait être 'BEE_ELEVES'.</div></div>");
                my_echo("</body>\n</html>\n");

                // Renseignement du temoin de mise a jour terminee.
                set_param($config, "imprt_cmpts_en_cours", "n");
                // On a fourni un fichier, mais invalide, donc ABANDON
                return false;
            } else {
                my_echo("<h3>Section élèves");
                if ($chrono == 'y') {
                    my_echo(" (<i>" . date_et_heure() . "</i>)");
                }
                my_echo("</h3>\n");
                my_echo("<blockquote>\n");

                // 20130115
                $elenoet = "";
                $uaj = "";
                $uaj_tronque = "";
                $annee_scolaire = "";
                $date_export = "";
                $objet_parametres = ($ele_xml->PARAMETRES);
                foreach ($objet_parametres->children() as $key => $value) {
                    if (strtoupper($key) == 'UAJ') {
                        $uaj = trim($value);
                        $uaj_tronque = substr(substr($uaj, 0, strlen($uaj) - 1), 1);
                    } elseif (strtoupper($key) == 'ANNEE_SCOLAIRE') {
                        $annee_scolaire = trim($value) . "-" . (trim($value) + 1);
                    } elseif (strtoupper($key) == 'HORODATAGE') {
                        $date_export = trim($value);
                    }
                }
                my_echo("<p>");
                if ($uaj != "") {
                    my_echo("Fichier XML élèves de l'établissement $uaj ($uaj_tronque) ");
                }
                if ($annee_scolaire != "") {
                    my_echo("pour l'année scolaire $annee_scolaire ");
                }
                if ($date_export != "") {
                    my_echo("exporté le $date_export");
                    init_param($config, 'xml_ele_last_import', $date_export);
                }
                my_echo("</p>");

                my_echo("<h4>Analyse du fichier pour extraire les informations élèves...");
                if ($chrono == 'y') {
                    my_echo(" (<i>" . date_et_heure() . "</i>)");
                }
                my_echo("</h4>\n");
                my_echo("<blockquote>\n");

                $eleves = array();
                // $indice_from_eleve_id[ELEVE_ID]=INDICE_$i_DANS_LE_TABLEAU_$eleves
                $indice_from_eleve_id = array();
                $indice_from_elenoet = array();

                // Compteur eleve:
                $i = - 1;

                $tab_champs_eleve = array(
                    "ID_NATIONAL",
                    "ELENOET",
                    "ID_ELEVE_ETAB",
                    "NOM",
                    "NOM_USAGE",
                    "NOM_DE_FAMILLE",
                    "PRENOM",
                    "DATE_NAISS",
                    "DOUBLEMENT",
                    "DATE_SORTIE",
                    "CODE_REGIME",
                    "DATE_ENTREE",
                    "CODE_MOTIF_SORTIE",
                    "CODE_SEXE"
                );

                // Inutile pour SE3
                $avec_scolarite_an_dernier = "n";
                $tab_champs_scol_an_dernier = array(
                    "CODE_STRUCTURE",
                    "CODE_RNE",
                    "SIGLE",
                    "DENOM_PRINC",
                    "DENOM_COMPL",
                    "LIGNE1_ADRESSE",
                    "LIGNE2_ADRESSE",
                    "LIGNE3_ADRESSE",
                    "LIGNE4_ADRESSE",
                    "BOITE_POSTALE",
                    "MEL",
                    "TELEPHONE",
                    "LL_COMMUNE_INSEE"
                );

                // PARTIE <ELEVES>
                my_echo("<p>Parcours de la section ELEVES<br />\n");

                $objet_eleves = ($ele_xml->DONNEES->ELEVES);
                foreach ($objet_eleves->children() as $eleve) {
                    $i ++;
                    // my_echo("<p><b>Eleve $i</b><br />");

                    $eleves[$i] = array();

                    foreach ($eleve->attributes() as $key => $value) {
                        // my_echo("$key=".$value."<br />");

                        $eleves[$i][strtolower($key)] = trim(traite_utf8($value));
                        if (strtoupper($key) == 'ELEVE_ID') {
                            $indice_from_eleve_id["$value"] = $i;
                        } elseif (strtoupper($key) == 'ELENOET') {
                            $indice_from_elenoet["$value"] = $i;
                        }
                    }

                    foreach ($eleve->children() as $key => $value) {
                        if (in_array(strtoupper($key), $tab_champs_eleve)) {
                            $eleves[$i][strtolower($key)] = trim(traite_utf8($value));
                            // my_echo("\$eleve->$key=".$value."<br />");
                        }

                        if (($avec_scolarite_an_dernier == 'y') && (strtoupper($key) == 'SCOLARITE_AN_DERNIER')) {
                            $eleves[$i]["scolarite_an_dernier"] = array();

                            foreach ($eleve->SCOLARITE_AN_DERNIER->children() as $key2 => $value2) {
                                // my_echo("\$eleve->SCOLARITE_AN_DERNIER->$key2=$value2<br />");
                                if (in_array(strtoupper($key2), $tab_champs_scol_an_dernier)) {
                                    $eleves[$i]["scolarite_an_dernier"][strtolower($key2)] = trim(traite_utf8($value2));
                                }
                            }
                        }
                    }

                    // 20130115
                    // Est-ce que l'elenoet enregistre est bien un elenoet de l'etablissement ou un eleve importe d'un autre etablissement?
                    if (($uaj_tronque != "") && (isset($eleves[$i]['elenoet'])) && (isset($eleves[$i]['id_eleve_etab'])) && (! preg_match("/" . $elenoet . $uaj_tronque . "/", $eleves[$i]['id_eleve_etab']))) {
                        my_echo("<p style='color:red'>L'élève " . $eleves[$i]['nom'] . " " . $eleves[$i]['prenom'] . " a étè importé d'un autre établissement (<em>" . $eleves[$i]['id_eleve_etab'] . "-&gt;" . preg_replace("/[0]*" . $eleves[$i]['elenoet'] . "/", "", $eleves[$i]['id_eleve_etab']) . "</em>).<br />Son elenoet (<em>" . $eleves[$i]['elenoet'] . "</em>) est celui qu'il avait dans son ancien établissement.<br />Cet elenoet n'est pas encore valide<br />Vous devrez créer le compte à la main en attendant que Sconet/Siècle soit nettoyé/mis à jour.</p>\n");
                        $tab_eleve_autre_etab[] = $eleves[$i]['nom'] . "|" . $eleves[$i]['prenom'] . "|" . $eleves[$i]['code_sexe'] . "|" . $eleves[$i]['date_naiss'];
                        unset($eleves[$i]['elenoet']);
                    }

                    if ($debug_import_comptes == 'y') {
                        my_echo("<pre style='color:green;'><b>Tableau \$eleves[$i]&nbsp;:</b>");
                        my_print_r($eleves[$i]);
                        my_echo("</pre>");
                    }
                }
                my_echo("Fin de la section ELEVES<br />\n");

                // ++++++++++++++++++++++++++++++++++++++

                $tab_champs_opt = array(
                    "NUM_OPTION",
                    "CODE_MODALITE_ELECT",
                    "CODE_MATIERE"
                );

                my_echo("<p>Parcours de la section OPTIONS<br />\n");
                // PARTIE <OPTIONS>
                $objet_options = ($ele_xml->DONNEES->OPTIONS);
                foreach ($objet_options->children() as $option) {
                    // $option est un <OPTION ELEVE_ID="145778" ELENOET="2643">
                    // my_echo("<p><b>Option</b><br />");

                    // $i est l'indice de l'eleve dans le tableau $eleves
                    unset($i);

                    $chaine_option = "OPTION";
                    foreach ($option->attributes() as $key => $value) {
                        // my_echo("$key=".$value."<br />");

                        $chaine_option .= " $key='$value'";

                        // Recherche de la valeur de $i dans $eleves[$i] d'apres l'ELEVE_ID ou l'ELENOET
                        if ((strtoupper($key) == 'ELEVE_ID') && (isset($indice_from_eleve_id["$value"]))) {
                            $i = $indice_from_eleve_id["$value"];
                            break;
                        } elseif ((strtoupper($key) == 'ELENOET') && (isset($indice_from_elenoet["$value"]))) {
                            $i = $indice_from_elenoet["$value"];
                            break;
                        }
                    }

                    if (! isset($i)) {
                        my_echo("<span style='color:red;'>ERREUR&nbsp;: Echec de l'association de l'option &lt;$chaine_option&gt; avec un élève</span>.<br />");
                    } else {
                        $eleves[$i]["options"] = array();
                        $j = 0;
                        // foreach($option->OPTIONS_ELEVE->children() as $key => $value) {

                        // $option fait reference a un eleve
                        // Les enfants sont des OPTIONS_ELEVE
                        foreach ($option->children() as $options_eleve) {
                            foreach ($options_eleve->children() as $key => $value) {
                                // Les enfants indiquent NUM_OPTION, CODE_MODALITE_ELECT, CODE_MATIERE
                                if (in_array(strtoupper($key), $tab_champs_opt)) {
                                    $eleves[$i]["options"][$j][strtolower($key)] = trim(traite_utf8($value));
                                    // my_echo("\$eleve->$key=".$value."<br />";
                                    // my_echo("\$eleves[$i][\"options\"][$j][".strtolower($key)."]=".$value."<br />");
                                }
                            }
                            $j ++;
                        }

                        if ($debug_import_comptes == 'y') {
                            my_echo("<pre style='color:green;'><b>Tableau \$eleves[$i]&nbsp;:</b>");
                            my_print_r($eleves[$i]);
                            my_echo("</pre>");
                        }
                    }
                }
                my_echo("Fin de la section OPTIONS<br />\n");

                // ++++++++++++++++++++++++++++++++++++++

                // TYPE_STRUCTURE vaut D pour la classe et G pour un groupe
                $tab_champs_struct = array(
                    "CODE_STRUCTURE",
                    "TYPE_STRUCTURE"
                );

                my_echo("<p>Parcours de la section STRUCTURES<br />\n");
                // PARTIE <OPTIONS>
                $objet_structures = ($ele_xml->DONNEES->STRUCTURES);
                foreach ($objet_structures->children() as $structures_eleve) {
                    // my_echo("<p><b>Structure</b><br />");

                    // $i est l'indice de l'eleve dans le tableau $eleves
                    unset($i);

                    $chaine_structures_eleve = "STRUCTURES_ELEVE";
                    foreach ($structures_eleve->attributes() as $key => $value) {
                        // my_echo("$key=".$value."<br />");

                        $chaine_structures_eleve .= " $key='$value'";

                        // Recherche de la valeur de $i dans $eleves[$i] d'apres l'ELEVE_ID ou l'ELENOET
                        if ((strtoupper($key) == 'ELEVE_ID') && (isset($indice_from_eleve_id["$value"]))) {
                            $i = $indice_from_eleve_id["$value"];
                            break;
                        } elseif ((strtoupper($key) == 'ELENOET') && (isset($indice_from_elenoet["$value"]))) {
                            $i = $indice_from_elenoet["$value"];
                            break;
                        }
                    }

                    if (! isset($i)) {
                        my_echo("<span style='color:red;'>ERREUR&nbsp;: Echec de l'association de &lt;$chaine_structures_eleve&gt; avec un élève</span>.<br />");
                    } else {
                        $eleves[$i]["structures"] = array();
                        $j = 0;
                        // foreach($objet_structures->STRUCTURES_ELEVE->children() as $structure) {
                        foreach ($structures_eleve->children() as $structure) {
                            $eleves[$i]["structures"][$j] = array();
                            foreach ($structure->children() as $key => $value) {
                                if (in_array(strtoupper($key), $tab_champs_struct)) {
                                    // my_echo("\$structure->$key=".$value."<br />");

                                    $eleves[$i]["structures"][$j][strtolower($key)] = strtr(trim(traite_utf8($value)), "/", "_");
                                    if ($config['clean_caract_classe'] == "y") {
                                        $eleves[$i]["structures"][$j][strtolower($key)] = preg_replace("/[^a-zA-Z0-9_ -]/", "", remplace_accents($eleves[$i]["structures"][$j][strtolower($key)]));
                                    }
                                }
                            }
                            $j ++;
                        }

                        if ($debug_import_comptes == 'y') {
                            my_echo("<pre style='color:green;'><b>Tableau \$eleves[$i]&nbsp;:</b>)");
                            my_print_r($eleves[$i]);
                            my_echo("</pre>");
                        }
                    }
                }
                my_echo("Fin de la section STRUCTURES</p>\n");

                // ++++++++++++++++++++++++++++++++++++++

                // Generer un tableau des membres des groupes:
                // $structure[$i]["nom"] -> 5LATIN-, 3 A2DEC3,...
                // $structure[$i]["eleve"][] -> ELENOET

                my_echo("<p>Terminé.</p>\n");
                if ($chrono == 'y') {
                    my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
                }
                my_echo("</blockquote>\n");

                // ===================================================================

                $tab_groups = array();
                $tab_groups_member = array();

                my_echo("<h4>Affichage (d'une partie) des données ELEVES extraites:");
                if ($chrono == 'y') {
                    my_echo(" (<i>" . date_et_heure() . "</i>)");
                }
                my_echo("</h4>\n");
                my_echo("<blockquote>\n");
                my_echo(count($eleves) . " élèves dans le fichier.");
                my_echo("<table border='1'>\n");
                my_echo("<tr>\n");
                // my_echo("<th style='color: blue;'>&nbsp;</th>\n");
                my_echo("<th style='color: blue;'>&nbsp;</th>\n");
                my_echo("<th>Elenoet</th>\n");
                my_echo("<th>Nom</th>\n");
                my_echo("<th>Prénom</th>\n");
                my_echo("<th>Sexe</th>\n");
                my_echo("<th>Date de naissance</th>\n");
                my_echo("<th>Division</th>\n");
                my_echo("</tr>\n");
                $i = 0;
                while ($i < count($eleves)) {
                    // Pour tenir compte de la modif Sconet de l'été 2016
                    if (isset($eleves[$i]['nom_usage'])) {
                        $eleves[$i]['nom'] = $eleves[$i]['nom_usage'];
                    } elseif (isset($eleves[$i]['nom_de_famille'])) {
                        $eleves[$i]['nom'] = $eleves[$i]['nom_de_famille'];
                    }

                    my_echo("<tr>\n");
                    // my_echo("<td style='color: blue;'>$cpt</td>\n");
                    // my_echo("<td style='color: blue;'>&nbsp;</td>\n");
                    my_echo("<td style='color: blue;'>$i</td>\n");
                    my_echo("<td>" . $eleves[$i]["elenoet"] . "</td>\n");
                    my_echo("<td>" . $eleves[$i]["nom"] . "</td>\n");

                    // =============================================
                    // On ne retient que le premier prénom: 20071101
                    $tab_tmp_prenom = explode(" ", $eleves[$i]["prenom"]);
                    $eleves[$i]["prenom"] = $tab_tmp_prenom[0];
                    // =============================================

                    my_echo("<td>" . $eleves[$i]["prenom"] . "</td>\n");
                    my_echo("<td>" . $eleves[$i]["code_sexe"] . "</td>\n");
                    my_echo("<td>" . $eleves[$i]["date_naiss"] . "</td>\n");
                    /*
                     * if(isset($eleves[$i]["structures"])) {
                     * my_echo("<td>".$eleves[$i]["structures"][0]["code_structure"]."</td>\n");
                     * }
                     * else{
                     * my_echo("<td>&nbsp;</td>\n");
                     * }
                     */
                    $temoin_div_trouvee = "";
                    if (isset($eleves[$i]["structures"])) {
                        if (count($eleves[$i]["structures"]) > 0) {
                            for ($j = 0; $j < count($eleves[$i]["structures"]); $j ++) {
                                if ($eleves[$i]["structures"][$j]["type_structure"] == "D") {

                                    // Normalement, un eleve n'est que dans une classe, mais au cas oe:
                                    if ($temoin_div_trouvee != "oui") {
                                        my_echo("<td>" . $eleves[$i]["structures"][$j]["code_structure"] . "</td>");
                                        $eleves[$i]["classe"] = $eleves[$i]["structures"][$j]["code_structure"];
                                    }

                                    $temoin_div_trouvee = "oui";
                                    // break;
                                } elseif ($eleves[$i]["structures"][$j]["type_structure"] == "G") {
                                    if (! in_array($eleves[$i]["structures"][$j]["code_structure"], $tab_groups)) {
                                        $tab_groups[] = $eleves[$i]["structures"][$j]["code_structure"];
                                        $tab_groups_member[$eleves[$i]["structures"][$j]["code_structure"]] = array();
                                    }

                                    // 20130115
                                    // if(!in_array($eleves[$i]['eleve_id'], $tab_groups_member[$eleves[$i]["structures"][$j]["code_structure"]])) {
                                    if ((! in_array($eleves[$i]['eleve_id'], $tab_groups_member[$eleves[$i]["structures"][$j]["code_structure"]])) && ($eleves[$i]['elenoet'])) {
                                        // $tab_groups_member[$eleves[$i]["structures"][$j]["code_structure"]][]=$eleves[$i]['eleve_id'];
                                        $tab_groups_member[$eleves[$i]["structures"][$j]["code_structure"]][] = $eleves[$i]['elenoet'];
                                    }
                                }
                            }

                            /*
                             * if($temoin_div_trouvee=="") {
                             * echo "&nbsp;";
                             * }
                             * else{
                             * my_echo("<td>".$eleves[$i]["structures"][$j]["code_structure"]."</td>");
                             * $eleves[$i]["classe"]=$eleves[$i]["structures"][$j]["code_structure"];
                             * }
                             */
                        } else {
                            my_echo("<td>&nbsp;</td>\n");
                        }
                    } else {
                        my_echo("<td>&nbsp;</td>\n");
                    }

                    my_echo("</tr>\n");
                    // flush();
                    $i ++;
                }

                my_echo("</table>\n");

                if ($debug_import_comptes == 'y') {
                    my_echo("DEBUG_ELEVES_1<br /><pre style='color:green'><b>eleves</b><br />\n");
                    my_print_r($eleves);
                    my_echo("</pre><br />DEBUG_ELEVES_2<br />\n");

                    my_echo("DEBUG_TAB_GROUPS_1<br /><pre style='color:green'><b>tab_groups</b><br />\n");
                    my_print_r($tab_groups);
                    my_echo("</pre><br />DEBUG_TAB_GROUPS_2<br />\n");

                    my_echo("DEBUG_TAB_GROUPS_MEMBER_1<br /><pre style='color:green'><b>tab_groups_member</b><br />\n");
                    my_print_r($tab_groups_member);
                    my_echo("</pre><br />DEBUG_TAB_GROUPS_MEMBER_2<br />\n");
                }

                // my_echo("___ ... ___");
                my_echo("</blockquote>\n");
                if ($chrono == 'y') {
                    my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
                }
                my_echo("</blockquote>\n");

                // Avec le fichier XML, on a rempli un tableau $eleves (au pluriel)
                // Remplissage du tableau $eleve (au singulier) calque sur celui du fichier CSV.
                if ($temoin_creation_fichiers != "non") {
                    $fich = fopen("$dossiercsv/f_ele.txt", "w+");
                } else {
                    $fich = FALSE;
                }
                $eleve = array();
                $tabnumero = array();
                $tab_division = array();
                $i = 0;
                while ($i < count($eleves)) {
                    // if(isset($eleves[$i]["structures"][0]["code_structure"])) {
                    // if(isset($eleves[$i]["structures"])) {
                    if (isset($eleves[$i]["classe"])) {
                        // 20130115
                        if (isset($eleves[$i]["elenoet"])) {
                            // $numero=$eleves[$i]["elenoet"];
                            $numero = sprintf("%05d", $eleves[$i]["elenoet"]);
                            $tabnumero[] = "$numero";
                            $eleve[$numero] = array();
                            $eleve[$numero]["numero"] = "$numero";
                            $eleve[$numero]["nom"] = $eleves[$i]["nom"];
                            // my_echo("\$eleve[$numero][\"nom\"]=".$eleves[$i]["nom"]."<br />\n");
                            // my_echo("<p>\$eleve[$numero][\"nom\"]=".$eleve[$numero]["nom"]." ");
                            $eleve[$numero]["prenom"] = $eleves[$i]["prenom"];
                            // my_echo("\$eleve[$numero][\"prenom\"]=".$eleve[$numero]["prenom"]." ");
                            $tmpdate = explode("/", $eleves[$i]["date_naiss"]);
                            $eleve[$numero]["date"] = $tmpdate[2] . $tmpdate[1] . $tmpdate[0];
                            if ($eleves[$i]["code_sexe"] == 1) {
                                $eleve[$numero]["sexe"] = "M";
                            } else {
                                $eleve[$numero]["sexe"] = "F";
                            }

                            // $eleve[$numero]["division"]=$eleves[$i]["structures"][0]["code_structure"];
                            $eleve[$numero]["division"] = $eleves[$i]["classe"];

                            // my_echo(" en ".$eleve[$numero]["division"]."<br />");
                            // my_echo("\$eleve[$numero][\"division\"]=".$eleve[$numero]["division"]."<br />");

                            $chaine = "";
                            $chaine .= $eleve[$numero]["numero"];
                            $chaine .= "|";
                            $chaine .= remplace_accents($eleve[$numero]["nom"]);
                            $chaine .= "|";
                            $chaine .= remplace_accents($eleve[$numero]["prenom"]);
                            $chaine .= "|";
                            $chaine .= $eleve[$numero]["date"];
                            $chaine .= "|";
                            $chaine .= $eleve[$numero]["sexe"];
                            $chaine .= "|";
                            $chaine .= $eleve[$numero]["division"];
                            if ($fich) {
                                // fwrite($fich,$chaine."\n");
                                fwrite($fich, html_entity_decode($chaine) . "\n");
                            }

                            // my_echo("Parcours des divisions existantes: ");
                            $temoin_new_div = "oui";
                            for ($k = 0; $k < count($tab_division); $k ++) {
                                // my_echo($tab_division[$k]["nom"]." (<i>$k</i>) ");
                                if ($eleve[$numero]["division"] == $tab_division[$k]["nom"]) {
                                    $temoin_new_div = "non";
                                    // my_echo(" (<font color='green'><i>BINGO</i></font>) ");
                                    break;
                                }
                            }
                            if ($temoin_new_div == "oui") {
                                // $k++;
                                $tab_division[$k] = array();
                                // $tab_division[$k]["nom"]=ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($eleve[$numero]["division"])));
                                $tab_division[$k]["nom"] = $eleve[$numero]["division"];
                                $tab_division[$k]["option"] = array();
                                // my_echo("<br />Nouvelle classe: \$tab_division[$k][\"nom\"]=".$tab_division[$k]["nom"]."<br />");
                            }

                            // Et pour les options, on conserve $eleves? NON
                            // $eleves[$i]["options"][$j]
                            if (isset($eleves[$i]["options"])) {
                                $eleve[$numero]["options"] = array();
                                for ($j = 0; $j < count($eleves[$i]["options"]); $j ++) {
                                    $eleve[$numero]["options"][$j] = array();
                                    $eleve[$numero]["options"][$j]["code_matiere"] = $eleves[$i]["options"][$j]["code_matiere"];
                                    // Les autres champs ne sont pas tres utiles...

                                    // my_echo("Option suivie: \$eleve[$numero][\"options\"][$j][\"code_matiere\"]=".$eleve[$numero]["options"][$j]["code_matiere"]."<br />");

                                    // TESTER SI L'OPTION EST DEJA DANS LA LISTE DES OPTIONS DE LA CLASSE.
                                    // my_echo("Options existantes: ");
                                    $temoin_nouvelle_option = "oui";
                                    for ($n = 0; $n < count($tab_division[$k]["option"]); $n ++) {
                                        // my_echo($tab_division[$k]["option"][$n]["code_matiere"]." (<i>$k - $n</i>)");
                                        if ($tab_division[$k]["option"][$n]["code_matiere"] == $eleve[$numero]["options"][$j]["code_matiere"]) {
                                            $temoin_nouvelle_option = "non";
                                            // my_echo(" (<font color='green'><i>BINGO</i></font>) ");
                                            break;
                                        }
                                    }
                                    // my_echo("<br />");
                                    if ($temoin_nouvelle_option == "oui") {
                                        // $n++;
                                        $tab_division[$k]["option"][$n] = array();
                                        $tab_division[$k]["option"][$n]["code_matiere"] = $eleve[$numero]["options"][$j]["code_matiere"];
                                        $tab_division[$k]["option"][$n]["eleve"] = array();
                                        // my_echo("Nouvelle option: \$tab_division[$k][\"option\"][$n][\"code_matiere\"]=".$tab_division[$k]["option"][$n]["code_matiere"]."<br />");
                                    }
                                    // my_echo("<br />");
                                    $tab_division[$k]["option"][$n]["eleve"][] = $eleve[$numero]["numero"];

                                    // my_echo("<p>Membres actuels de l'option ".$tab_division[$k]["option"][$n]["code_matiere"]." de ".$tab_division[$k]["nom"].": ");
                                    // for($m=0;$m<count($tab_division[$k]["option"][$n]["eleve"]);$m++) {
                                    // my_echo($tab_division[$k]["option"][$n]["eleve"][$m]." ");
                                    // }
                                    // my_echo(" ($m)</p>");
                                }
                            }
                        }
                    }
                    $i ++;
                }
                if ($fich) {
                    fclose($fich);
                }
                if ($temoin_creation_fichiers != "non") {
                    my_echo("<script type='text/javascript'>
		document.getElementById('id_f_ele_txt').style.display='';
	</script>");
                }
                // my_echo("disk_total_space($dossiercsv)=".disk_total_space($dossiercsv)."<br />");

                // // Affichage pour debug:
                // for($k=0;$k<count($tab_division);$k++) {
                // my_echo("<p>\$tab_division[$k][\"nom\"]=<b>".$tab_division[$k]["nom"]."</b></p>");
                // for($n=0;$n<count($tab_division[$k]["option"]);$n++) {
                // my_echo("<p>\$tab_division[$k][\"option\"][$n][\"code_matiere\"]=".$tab_division[$k]["option"][$n]["code_matiere"]."<br />");
                // //my_echo("<ul>");
                // my_echo("Eleves: ");
                // my_echo($tab_division[$k]["option"][$n]["eleve"][0]);
                // for($i=1;$i<count($tab_division[$k]["option"][$n]["eleve"]);$i++) {
                // //my_echo("<li></li>");
                // my_echo(", ".$tab_division[$k]["option"][$n]["eleve"][$i]);
                // }
                // //my_echo("</ul>");
                // my_echo("</p>");
                // }
                // my_echo("<hr />");
                // }

                if ($debug_import_comptes == 'y') {
                    my_echo("DEBUG_ELEVE_1<br /><pre style='color:green'><b>eleve</b><br />\n");
                    my_print_r($eleve);
                    my_echo("</pre><br />DEBUG_ELEVE_2<br />\n");
                }
            }
        } else {
            // $eleves_xml_file
            // my_echo("<p>ERREUR lors de l'ouverture du fichier ".$eleves_xml_file['name']." (<i>".$eleves_xml_file['tmp_name']."</i>).</p>\n");

            my_echo("<script type='text/javascript'>
	document.getElementById('div_signalements').style.display='';
	document.getElementById('div_signalements').innerHTML=document.getElementById('div_signalements').innerHTML+'<br /><a href=\'#erreur_eleves_file\'>Erreur</a> lors de l\'ouverture du fichier <b>$eleves_file</b>';
</script>\n");

            my_echo("<p style='color:red;'><a name='erreur_eleves_file'></a>ERREUR lors de l'ouverture du fichier '$eleves_file'</p>\n");

            my_echo("<div style='color:red;'>");
            foreach (libxml_get_errors() as $xml_error) {
                my_echo($xml_error->message . "<br />");
            }
            my_echo("</div>");

            libxml_clear_errors();
        }
    }

    // my_echo("<p>Fin provisoire...</p>");
    // exit;

    // =========================================================================
    // =========================================================================
    // On passe au fichier STS_EDT
    // =========================================================================
    // =========================================================================

    // *******************************************************************
    // *******************************************************************
    // A FAIRE: METTRE UN if(file_exists($sts_xml_file))
    // *******************************************************************
    // *******************************************************************

    // Lecture du XML de STS...
    $temoin_au_moins_un_prof_princ = "";

    // Pour avoir acces aux erreurs XML:
    libxml_use_internal_errors(true);

    $sts_xml = simplexml_load_file($sts_xml_file);
    if ($sts_xml) {
        my_echo("<h3>Section professeurs, matières, groupes,...");
        if ($chrono == 'y') {
            my_echo(" (<i>" . date_et_heure() . "</i>)");
        }
        my_echo("</h3>\n");
        my_echo("<blockquote>\n");

        $nom_racine = $sts_xml->getName();
        if (strtoupper($nom_racine) != 'STS_EDT') {
            // echo "<p style='color:red'>ABANDON&nbsp;: Le fichier n'est pas de type STS_EDT.</p>\n";
            my_echo("<p style='color:red;'>ERREUR: Le fichier STS/Emploi-du-temps fourni n'a pas l'air d'être de type STS_EDT.</p>\n");

            my_echo("<script type='text/javascript'>
		compte_a_rebours='n';
	</script>\n");
            my_echo("<div style='position:absolute; top: 50px; left: 300px; width: 400px; border: 1px solid black; background-color: red;'><div align='center'>ERREUR: Le fichier STS/Emploi-du-temps fourni n'a pas l'air d'être de type STS_EDT.</div></div>");
            my_echo("</body>\n</html>\n");

            // Renseignement du temoin de mise a jour terminee.
            set_param($config, "imprt_cmpts_en_cours", "n");
            // On a fourni un fichier, mais invalide, donc ABANDON
            return false;
        } else {

            if ($debug_import_comptes == 'y') {
                my_echo("<p style='font-weight:bold;>Affichage du contenu du XML STS_EDT</p>");
                my_echo("<pre style='color:blue;'>");
                my_print_r($sts_xml);
                my_echo("</pre>");
            }

            my_echo("<h4>Analyse du fichier pour extraire les informations de l'établissement</h4\n");
            my_echo("<blockquote>\n");

            $tab_champs_uaj = array(
                "SIGLE",
                "DENOM_PRINC",
                "DENOM_COMPL",
                "CODE_NATURE",
                "CODE_CATEGORIE",
                "ADRESSE",
                "COMMUNE",
                "CODE_POSTAL",
                "BOITE_POSTALE",
                "CEDEX",
                "TELEPHONE",
                "STATUT",
                "ETABLISSEMENT_SENSIBLE"
            );

            // PARTIE <PARAMETRES>
            my_echo("<p>Parcours de la section PARAMETRES<br />\n");

            // RNE
            $etablissement = array();
            foreach ($sts_xml->PARAMETRES->UAJ->attributes() as $key => $value) {
                if (strtoupper($key) == 'CODE') {
                    $etablissement["code"] = trim(traite_utf8($value));
                    break;
                }
            }

            // Academie
            $etablissement["academie"] = array();
            foreach ($sts_xml->PARAMETRES->UAJ->ACADEMIE->children() as $key => $value) {
                $etablissement["academie"][strtolower($key)] = trim(traite_utf8($value));
            }

            // Champs de l'etablissement (sigle, denom_princ, adresse,...)
            foreach ($sts_xml->PARAMETRES->UAJ->children() as $key => $value) {
                if (in_array(strtoupper($key), $tab_champs_uaj)) {
                    $etablissement[strtolower($key)] = trim(traite_utf8($value));
                }
            }

            // Annee
            foreach ($sts_xml->PARAMETRES->ANNEE_SCOLAIRE->attributes() as $key => $value) {
                if (strtoupper($key) == 'ANNEE') {
                    $etablissement["annee"] = array();
                    $etablissement["annee"]["annee"] = trim(traite_utf8($value));
                    break;
                }
            }

            // Dates de debut et fin d'annee
            foreach ($sts_xml->PARAMETRES->ANNEE_SCOLAIRE->children() as $key => $value) {
                $etablissement["annee"][strtolower($key)] = trim(traite_utf8($value));
            }

            my_echo("Fin de la section PARAMETRES<br />\n");

            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            // ==============================================================================

            my_echo("<h5>Affichage des données PARAMETRES établissement extraites:");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");
            my_echo("<table border='1'>\n");
            my_echo("<tr>\n");
            // my_echo("<th style='color: blue;'>&nbsp;</th>\n");
            my_echo("<th>Code</th>\n");
            my_echo("<th>Code académie</th>\n");
            my_echo("<th>Libelle académie</th>\n");
            my_echo("<th>Sigle</th>\n");
            my_echo("<th>Denom_princ</th>\n");
            my_echo("<th>Denom_compl</th>\n");
            my_echo("<th>Code_nature</th>\n");
            my_echo("<th>Code_categorie</th>\n");
            my_echo("<th>Adresse</th>\n");
            my_echo("<th>Code_postal</th>\n");
            my_echo("<th>Boite_postale</th>\n");
            my_echo("<th>Cedex</th>\n");
            my_echo("<th>Telephone</th>\n");
            my_echo("<th>Statut</th>\n");
            my_echo("<th>Etablissement_sensible</th>\n");
            my_echo("<th>Annee</th>\n");
            my_echo("<th>Date_debut</th>\n");
            my_echo("<th>Date_fin</th>\n");
            my_echo("</tr>\n");
            // $cpt=0;
            // while($cpt<count($etablissement)) {
            my_echo("<tr>\n");
            // my_echo("<td style='color: blue;'>$cpt</td>\n");
            // my_echo("<td style='color: blue;'>&nbsp;</td>\n");
            my_echo("<td>" . $etablissement["code"] . "</td>\n");
            my_echo("<td>" . $etablissement["academie"]["code"] . "</td>\n");
            my_echo("<td>" . $etablissement["academie"]["libelle"] . "</td>\n");
            my_echo("<td>" . $etablissement["sigle"] . "</td>\n");
            my_echo("<td>" . $etablissement["denom_princ"] . "</td>\n");
            my_echo("<td>" . $etablissement["denom_compl"] . "</td>\n");
            my_echo("<td>" . $etablissement["code_nature"] . "</td>\n");
            my_echo("<td>" . $etablissement["code_categorie"] . "</td>\n");
            my_echo("<td>" . $etablissement["adresse"] . "</td>\n");
            my_echo("<td>" . $etablissement["code_postal"] . "</td>\n");
            my_echo("<td>" . $etablissement["boite_postale"] . "</td>\n");
            my_echo("<td>" . $etablissement["cedex"] . "</td>\n");
            my_echo("<td>" . $etablissement["telephone"] . "</td>\n");
            my_echo("<td>" . $etablissement["statut"] . "</td>\n");
            my_echo("<td>" . $etablissement["etablissement_sensible"] . "</td>\n");
            my_echo("<td>" . $etablissement["annee"]["annee"] . "</td>\n");
            my_echo("<td>" . $etablissement["annee"]["date_debut"] . "</td>\n");
            my_echo("<td>" . $etablissement["annee"]["date_fin"] . "</td>\n");
            my_echo("</tr>\n");
            // $cpt++;
            // }
            my_echo("</table>\n");
            my_echo("</blockquote>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            if ($debug_import_comptes == 'y') {
                my_echo("DEBUG_ETAB_1<br /><pre style='color:green'><b>etablissement</b><br />\n");
                my_print_r($etablissement);
                my_echo("</pre><br />\nDEBUG_ETAB_2<br />\n");
            }

            // ==============================================================================

            $tab_champs_matiere = array(
                "CODE_GESTION",
                "LIBELLE_COURT",
                "LIBELLE_LONG",
                "LIBELLE_EDITION"
            );

            my_echo("<h4>Matières");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            // my_echo("<h3>Analyse du fichier pour extraire les matieres...</h3>\n");
            // my_echo("<h4>Analyse du fichier pour extraire les matieres...</h4>\n");
            // my_echo("<h5>Analyse du fichier pour extraire les matieres...</h5>\n");
            my_echo("<h5>Analyse du fichier pour extraire les matières...");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");

            $matiere = array();
            $i = 0;

            foreach ($sts_xml->NOMENCLATURES->MATIERES->children() as $objet_matiere) {

                foreach ($objet_matiere->attributes() as $key => $value) {
                    if (strtoupper($key) == 'CODE') {
                        $matiere[$i]["code"] = trim(traite_utf8($value));
                        break;
                    }
                }

                // Champs de la matiere
                foreach ($objet_matiere->children() as $key => $value) {
                    if (in_array(strtoupper($key), $tab_champs_matiere)) {
                        if (strtoupper($key) == 'CODE_GESTION') {
                            // $matiere[$i][strtolower($key)]=trim(ereg_replace("[^a-zA-Z0-9&_. -]","",html_entity_decode($value)));
                            $matiere[$i][strtolower($key)] = trim(preg_replace("/[^a-zA-Z0-9&_. -]/", "", html_entity_decode(traite_utf8($value))));
                        } elseif (strtoupper($key) == 'LIBELLE_COURT') {
                            // $matiere[$i][strtolower($key)]=trim(ereg_replace("[^A-Za-zÆæ¼½".$liste_caracteres_accentues."0-9&_. -]","",html_entity_decode($value)));
                            $matiere[$i][strtolower($key)] = trim(preg_replace("/[^A-Za-zÆæ¼½" . $liste_caracteres_accentues . "0-9&_. -]/", "", html_entity_decode(traite_utf8($value))));
                        } else {
                            $matiere[$i][strtolower($key)] = trim(traite_utf8($value));
                        }
                    }
                }

                $i ++;
            }

            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            my_echo("<h5>Affichage des données MATIERES extraites:");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");
            my_echo("<table border='1'>\n");
            my_echo("<tr>\n");
            my_echo("<th style='color: blue;'>&nbsp;</th>\n");
            my_echo("<th>Code</th>\n");
            my_echo("<th>Code_gestion</th>\n");
            my_echo("<th>Libelle_court</th>\n");
            my_echo("<th>Libelle_long</th>\n");
            my_echo("<th>Libelle_edition</th>\n");
            my_echo("</tr>\n");
            $cpt = 0;
            while ($cpt < count($matiere)) {
                my_echo("<tr>\n");
                my_echo("<td style='color: blue;'>$cpt</td>\n");
                my_echo("<td>" . $matiere[$cpt]["code"] . "</td>\n");
                my_echo("<td>" . htmlentities($matiere[$cpt]["code_gestion"]) . "</td>\n");
                my_echo("<td>" . htmlentities($matiere[$cpt]["libelle_court"]) . "</td>\n");
                my_echo("<td>" . htmlentities($matiere[$cpt]["libelle_long"]) . "</td>\n");
                my_echo("<td>" . htmlentities($matiere[$cpt]["libelle_edition"]) . "</td>\n");
                my_echo("</tr>\n");
                $cpt ++;
            }
            my_echo("</table>\n");
            my_echo("</blockquote>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            if ($debug_import_comptes == 'y') {
                my_echo("DEBUG_MATIERE_1<br /><pre style='color:green'><b>matiere</b><br />\n");
                my_print_r($matiere);
                my_echo("</pre><br />\nDEBUG_MATIERE_2<br />\n");
            }

            function get_nom_matiere($code)
            {
                global $matiere;

                $retour = $code;
                for ($i = 0; $i < count($matiere); $i ++) {
                    if ($matiere[$i]["code"] == "$code") {
                        $retour = $matiere[$i]["code_gestion"];
                        break;
                    }
                }
                return $retour;
            }

            function get_nom_prof($code)
            {
                global $prof;

                $retour = $code;
                for ($i = 0; $i < count($prof); $i ++) {
                    if ($prof[$i]["id"] == "$code") {
                        $retour = $prof[$i]["nom_usage"];
                        break;
                    }
                }
                return $retour;
            }

            my_echo("<h4>Personnels");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            my_echo("<h5>Analyse du fichier pour extraire les professeurs,...");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");

            $tab_champs_personnels = array(
                "NOM_USAGE",
                "NOM_PATRONYMIQUE",
                "PRENOM",
                "SEXE",
                "CIVILITE",
                "DATE_NAISSANCE",
                "GRADE",
                "FONCTION"
            );

            $prof = array();
            $i = 0;

            foreach ($sts_xml->DONNEES->INDIVIDUS->children() as $individu) {
                $prof[$i] = array();

                // my_echo("<span style='color:orange'>\$individu->NOM_USAGE=".$individu->NOM_USAGE."</span><br />");

                foreach ($individu->attributes() as $key => $value) {
                    $prof[$i][strtolower($key)] = trim(traite_utf8($value));
                }

                // Champs de l'individu
                // $temoin_prof_princ=0;
                // $temoin_discipline=0;
                foreach ($individu->children() as $key => $value) {
                    if (in_array(strtoupper($key), $tab_champs_personnels)) {
                        if (strtoupper($key) == 'SEXE') {
                            // $prof[$i]["sexe"]=trim(ereg_replace("[^1-2]","",$value));
                            $prof[$i]["sexe"] = trim(preg_replace("/[^1-2]/", "", $value));
                        } elseif (strtoupper($key) == 'CIVILITE') {
                            // $prof[$i]["civilite"]=trim(ereg_replace("[^1-3]","",$value));
                            $prof[$i]["civilite"] = trim(preg_replace("/[^1-3]/", "", $value));
                        } elseif ((strtoupper($key) == 'NOM_USAGE') || (strtoupper($key) == 'NOM_PATRONYMIQUE') || (strtoupper($key) == 'PRENOM') || (strtoupper($key) == 'NOM_USAGE')) {
                            // $prof[$i][strtolower($key)]=trim(ereg_replace("[^A-Za-zÆæ¼½".$liste_caracteres_accentues." -]","",$value));
                            $prof[$i][strtolower($key)] = trim(preg_replace("/[^A-Za-zÆæ¼½" . $liste_caracteres_accentues . " -]/", "", traite_utf8($value)));
                            // my_echo("\$prof[$i][".strtolower($key)."]=".$prof[$i][strtolower($key)]."<br />";
                        } else {
                            $prof[$i][strtolower($key)] = trim(traite_utf8($value));
                        }
                    }

                    /*
                     * //my_echo("$key<br />";
                     * if(strtoupper($key)=='PROFS_PRINC') {
                     * //if($key=='PROFS_PRINC') {
                     * $temoin_prof_princ++;
                     * //my_echo("\$temoin_prof_princ=$temoin_prof_princ<br />";
                     * }
                     * if(strtoupper($key)=='DISCIPLINES') {
                     * $temoin_discipline++;
                     * //my_echo("\$temoin_discipline=$temoin_discipline<br />";
                     * }
                     */
                }

                if (isset($individu->PROFS_PRINC)) {
                    // if($temoin_prof_princ>0) {
                    $j = 0;
                    foreach ($individu->PROFS_PRINC->children() as $prof_princ) {
                        // $prof[$i]["prof_princ"]=array();
                        foreach ($prof_princ->children() as $key => $value) {
                            // $prof[$i]["prof_princ"][$j][strtolower($key)]=trim(traite_utf8($value));
                            // Traitement des accents et slashes dans les noms de divisions
                            // $prof[$i]["prof_princ"][$j][strtolower($key)]=preg_replace("/[^a-zA-Z0-9_ -]/", "",strtr(remplace_accents(trim(traite_utf8($value))),"/","_"));

                            $prof[$i]["prof_princ"][$j][strtolower($key)] = strtr(trim(traite_utf8($value)), "/", "_");
                            if ($config['clean_caract_classe'] == "y") {
                                $prof[$i]["prof_princ"][$j][strtolower($key)] = preg_replace("/[^a-zA-Z0-9_ -]/", "", remplace_accents($prof[$i]["prof_princ"][$j][strtolower($key)]));
                            }

                            $temoin_au_moins_un_prof_princ = "oui";
                        }
                        $j ++;
                    }
                }

                // if($temoin_discipline>0) {
                if (isset($individu->DISCIPLINES)) {
                    $j = 0;
                    foreach ($individu->DISCIPLINES->children() as $discipline) {
                        foreach ($discipline->attributes() as $key => $value) {
                            if (strtoupper($key) == 'CODE') {
                                $prof[$i]["disciplines"][$j]["code"] = trim(traite_utf8($value));
                                break;
                            }
                        }

                        foreach ($discipline->children() as $key => $value) {
                            $prof[$i]["disciplines"][$j][strtolower($key)] = trim(traite_utf8($value));
                        }
                        $j ++;
                    }
                }
                $i ++;
            }

            my_echo("<p>$i personnels.</p>");

            if ($debug_import_comptes == 'y') {
                my_echo("DEBUG_PROF_1<br /><pre style='color:green'><b>prof</b><br />\n");
                my_print_r($prof);
                my_echo("</pre><br />DEBUG_PROF_2<br />\n");
            }
            my_echo("</blockquote>\n");
            my_echo("</blockquote>\n");

            my_echo("<h4>Structures");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            my_echo("<h5>Analyse du fichier pour extraire les divisions et associations profs/matières,...");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");
            $divisions = array();
            $i = 0;

            foreach ($sts_xml->DONNEES->STRUCTURE->DIVISIONS->children() as $objet_division) {
                $divisions[$i] = array();

                foreach ($objet_division->attributes() as $key => $value) {
                    if (strtoupper($key) == 'CODE') {
                        // $divisions[$i]['code']=trim(traite_utf8($value));
                        // $divisions[$i]['code']=trim(remplace_accents(traite_utf8($value)));
                        // Traitement des accents et slashes dans les noms de divisions
                        // $divisions[$i]['code']=preg_replace("/[^a-zA-Z0-9_ -]/", "",strtr(remplace_accents(trim(traite_utf8($value))),"/","_"));

                        $divisions[$i]['code'] = strtr(trim(traite_utf8($value)), "/", "_");
                        if ($config['clean_caract_classe'] == "y") {
                            $divisions[$i]['code'] = preg_replace("/[^a-zA-Z0-9_ -]/", "", remplace_accents($divisions[$i]['code']));
                        }

                        // my_echo("<p>\$divisions[$i]['code']=".$divisions[$i]['code']."<br />");
                        break;
                    }
                }

                // Champs de la division
                $j = 0;

                foreach ($objet_division->SERVICES->children() as $service) {
                    foreach ($service->attributes() as $key => $value) {
                        $divisions[$i]["services"][$j][strtolower($key)] = trim(traite_utf8($value));
                        // my_echo("\$divisions[$i][\"services\"][$j][".strtolower($key)."]=trim(traite_utf8($value))<br />");
                    }

                    $k = 0;
                    foreach ($service->ENSEIGNANTS->children() as $enseignant) {

                        foreach ($enseignant->attributes() as $key => $value) {
                            // <ENSEIGNANT ID="8949" TYPE="epp">
                            // $divisions[$i]["services"][$j]["enseignants"][$k][strtolower($key)]=trim(traite_utf8($value));
                            if (strtoupper($key) == "ID") {
                                $divisions[$i]["services"][$j]["enseignants"][$k]["id"] = trim(traite_utf8($value));
                                break;
                            }
                        }
                        $k ++;
                    }
                    $j ++;
                }
                $i ++;
            }
            my_echo("$i divisions.<br />\n");
            my_echo("</blockquote>\n");

            if ($debug_import_comptes == 'y') {
                my_echo("DEBUG_DIV_1<br /><pre style='color:green'><b>divisions</b><br />\n");
                my_print_r($divisions);
                my_echo("</pre><br />DEBUG_DIV_2<br />\n");
            }
            my_echo("</blockquote>\n");

            // ====================================================

            $tab_champs_groupe = array(
                "LIBELLE_LONG"
            );

            my_echo("<h4>Groupes");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            my_echo("<h5>Analyse du fichier pour extraire les groupes...");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");
            $groupes = array();
            $i = 0;

            foreach ($sts_xml->DONNEES->STRUCTURE->GROUPES->children() as $objet_groupe) {
                $groupes[$i] = array();

                foreach ($objet_groupe->attributes() as $key => $value) {
                    if (strtoupper($key) == 'CODE') {
                        $groupes[$i]['code'] = trim(traite_utf8($value));
                        // my_echo("<p>\$groupes[$i]['code']=".$groupes[$i]['code']."<br />");
                        break;
                    }
                }

                // Champs enfants du groupe
                foreach ($objet_groupe->children() as $key => $value) {
                    if (in_array(strtoupper($key), $tab_champs_groupe)) {
                        $groupes[$i][strtolower($key)] = trim(traite_utf8($value));
                    }
                }

                if ((! isset($groupes[$i]['libelle_long'])) || ($groupes[$i]['libelle_long'] == '')) {
                    $groupes[$i]['libelle_long'] = $groupes[$i]['code'];
                }

                $j = 0;
                foreach ($objet_groupe->DIVISIONS_APPARTENANCE->children() as $objet_division_apartenance) {
                    foreach ($objet_division_apartenance->attributes() as $key => $value) {
                        $groupes[$i]["divisions"][$j][strtolower($key)] = strtr(trim(traite_utf8($value)), "/", "_");
                        if ($config['clean_caract_classe'] == "y") {
                            $groupes[$i]["divisions"][$j][strtolower($key)] = preg_replace("/[^a-zA-Z0-9_ -]/", "", remplace_accents($groupes[$i]["divisions"][$j][strtolower($key)]));
                        }
                    }
                    $j ++;
                }

                $j = 0;
                foreach ($objet_groupe->SERVICES->children() as $service) {
                    foreach ($service->attributes() as $key => $value) {
                        $groupes[$i]["service"][$j][strtolower($key)] = trim(traite_utf8($value));
                        // Remarque: Pour les divisions, c'est ["services"] au lieu de ["service"]
                        // $divisions[$i]["services"][$j][strtolower($key)]=trim(traite_utf8($value));
                    }

                    $k = 0;
                    foreach ($service->ENSEIGNANTS->children() as $enseignant) {

                        foreach ($enseignant->attributes() as $key => $value) {
                            // <ENSEIGNANT ID="8949" TYPE="epp">
                            // $divisions[$i]["services"][$j]["enseignants"][$k][strtolower($key)]=trim(traite_utf8($value));
                            if (strtoupper($key) == "ID") {
                                $groupes[$i]["service"][$j]["enseignant"][$k]["id"] = trim(traite_utf8($value));
                                break;
                            }
                        }
                        $k ++;
                    }
                    $j ++;
                }
                $i ++;
            }
            my_echo("$i groupes.<br />\n");

            my_echo("<p>Terminé.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            if ($debug_import_comptes == 'y') {
                my_echo("DEBUG_GRP_1<br /><pre style='color:green'><b>groupes</b><br />\n");
                my_print_r($groupes);
                my_echo("</pre><br />DEBUG_GRP_2<br />\n");
            }
            my_echo("</blockquote>\n");

            /*
             * my_echo("DEBUG_PROF_1<br /><pre style='color:green'><b>prof</b><br />\n");
             * my_print_r($prof);
             * my_echo("</pre><br />DEBUG_PROF_2<br />\n");
             *
             * my_echo("DEBUG_DIV_1<br /><pre style='color:green'><b>divisions</b><br />\n");
             * my_print_r($divisions);
             * my_echo("</pre><br />DEBUG_DIV_2<br />\n");
             *
             * my_echo("DEBUG_GRP_1<br /><pre style='color:green'><b>groupes</b><br />\n");
             * my_print_r($groupes);
             * my_echo("</pre><br />DEBUG_GRP_2<br />\n");
             */

            my_echo("<h5>Affichage des données PROFS,... extraites:");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");
            my_echo("<table border='1'>\n");
            my_echo("<tr>\n");
            my_echo("<th style='color: blue;'>&nbsp;</th>\n");
            my_echo("<th>Id</th>\n");
            my_echo("<th>Type</th>\n");
            my_echo("<th>Sexe</th>\n");
            my_echo("<th>Civilite</th>\n");
            my_echo("<th>Nom_usage</th>\n");
            my_echo("<th>Nom_patronymique</th>\n");
            my_echo("<th>Prenom</th>\n");
            my_echo("<th>Date_naissance</th>\n");
            my_echo("<th>Grade</th>\n");
            my_echo("<th>Fonction</th>\n");
            my_echo("<th>Disciplines</th>\n");
            my_echo("</tr>\n");
            $cpt = 0;
            while ($cpt < count($prof)) {
                my_echo("<tr>\n");
                my_echo("<td style='color: blue;'>$cpt</td>\n");
                my_echo("<td>" . $prof[$cpt]["id"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["type"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["sexe"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["civilite"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["nom_usage"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["nom_patronymique"] . "</td>\n");

                // =============================================
                // On ne retient que le premier prénom: 20071101
                $tab_tmp_prenom = explode(" ", $prof[$cpt]["prenom"]);
                $prof[$cpt]["prenom"] = $tab_tmp_prenom[0];
                // =============================================

                my_echo("<td>" . $prof[$cpt]["prenom"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["date_naissance"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["grade"] . "</td>\n");
                my_echo("<td>" . $prof[$cpt]["fonction"] . "</td>\n");

                my_echo("<td align='center'>\n");

                if ($prof[$cpt]["fonction"] == "ENS") {
                    my_echo("<table border='1'>\n");
                    my_echo("<tr>\n");
                    my_echo("<th>Code</th>\n");
                    my_echo("<th>Libelle_court</th>\n");
                    my_echo("<th>Nb_heures</th>\n");
                    my_echo("</tr>\n");
                    for ($j = 0; $j < count($prof[$cpt]["disciplines"]); $j ++) {
                        my_echo("<tr>\n");
                        my_echo("<td>" . $prof[$cpt]["disciplines"][$j]["code"] . "</td>\n");
                        my_echo("<td>" . $prof[$cpt]["disciplines"][$j]["libelle_court"] . "</td>\n");
                        my_echo("<td>" . $prof[$cpt]["disciplines"][$j]["nb_heures"] . "</td>\n");
                        my_echo("</tr>\n");
                    }
                    my_echo("</table>\n");
                }

                my_echo("</td>\n");
                my_echo("</tr>\n");
                $cpt ++;
            }
            my_echo("</table>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            if ($debug_import_comptes == 'y') {
                my_echo("DEBUG_PROFbis_1<br /><pre style='color:green'><b>prof</b><br />\n");
                my_print_r($prof);
                my_echo("</pre><br />DEBUG_PROFbis_2<br />\n");
            }

            $temoin_au_moins_une_matiere = "";
            $temoin_au_moins_un_prof = "";
            // Affichage des infos Enseignements et divisions:
            // my_echo("<a name='divisions'></a><h3>Affichage des divisions</h3>\n");
            // my_echo("<a name='divisions'></a><h5>Affichage des divisions</h5>\n");
            my_echo("<a name='divisions'></a><h5>Affichage des divisions");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h5>\n");
            my_echo("<blockquote>\n");
            for ($i = 0; $i < count($divisions); $i ++) {
                // my_echo("<p>\$divisions[$i][\"code\"]=".$divisions[$i]["code"]."<br />\n");
                // my_echo("<h4>Classe de ".$divisions[$i]["code"]."</h4>\n");
                // my_echo("<h6>Classe de ".$divisions[$i]["code"]."</h6>\n");
                my_echo("<h6>Classe de " . $divisions[$i]["code"]);
                if ($chrono == 'y') {
                    my_echo(" (<i>" . date_et_heure() . "</i>)");
                }
                my_echo("</h6>\n");
                my_echo("<ul>\n");
                for ($j = 0; $j < count($divisions[$i]["services"]); $j ++) {
                    // my_echo("\$divisions[$i][\"services\"][$j][\"code_matiere\"]=".$divisions[$i]["services"][$j]["code_matiere"]."<br />\n");
                    my_echo("<li>\n");
                    for ($m = 0; $m < count($matiere); $m ++) {
                        if ($matiere[$m]["code"] == $divisions[$i]["services"][$j]["code_matiere"]) {
                            // my_echo("\$matiere[$m][\"code_gestion\"]=".$matiere[$m]["code_gestion"]."<br />\n");
                            my_echo("Matière: " . $matiere[$m]["code_gestion"] . "<br />\n");
                            $temoin_au_moins_une_matiere = "oui";
                        }
                    }
                    my_echo("<ul>\n");
                    for ($k = 0; $k < count($divisions[$i]["services"][$j]["enseignants"]); $k ++) {
                        // $divisions[$i]["services"][$j]["enseignants"][$k]["id"]
                        for ($m = 0; $m < count($prof); $m ++) {
                            if ($prof[$m]["id"] == $divisions[$i]["services"][$j]["enseignants"][$k]["id"]) {
                                // my_echo($prof[$m]["nom_usage"]." ".$prof[$m]["prenom"]."|");
                                my_echo("<li>\n");
                                my_echo("Enseignant: " . $prof[$m]["nom_usage"] . " " . $prof[$m]["prenom"]);
                                my_echo("</li>\n");
                                $temoin_au_moins_un_prof = "oui";
                            }
                        }
                    }
                    my_echo("</ul>\n");
                    // my_echo("<br />\n");
                    my_echo("</li>\n");
                }
                my_echo("</ul>\n");
                // my_echo("</p>\n");
            }
            my_echo("</blockquote>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");
            my_echo("</blockquote>\n");
            my_echo("<h3>Génération des CSV");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h3>\n");
            my_echo("<blockquote>\n");
            my_echo("<a name='se3'></a><h4>Génération du CSV (F_WIND.txt) des profs");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            $cpt = 0;
            // if($temoin_creation_fichiers!="non") {$fich=fopen("$dossiercsv/se3/f_wind.txt","w+");}
            if ($temoin_creation_fichiers != "non") {
                $fich = fopen("$dossiercsv/f_wind.txt", "w+");
            } else {
                $fich = FALSE;
            }
            while ($cpt < count($prof)) {
                if ($prof[$cpt]["fonction"] == "ENS") {
                    $date = str_replace("-", "", $prof[$cpt]["date_naissance"]);
                    $chaine = "P" . $prof[$cpt]["id"] . "|" . $prof[$cpt]["nom_usage"] . "|" . $prof[$cpt]["prenom"] . "|" . $date . "|" . $prof[$cpt]["sexe"];
                    if ($fich) {
                        // fwrite($fich,$chaine."\n");
                        fwrite($fich, html_entity_decode($chaine) . "\n");
                    }
                    my_echo($chaine . "<br />\n");
                }
                $cpt ++;
            }
            if ($temoin_creation_fichiers != "non") {
                fclose($fich);
            }

            // my_echo("disk_total_space($dossiercsv)=".disk_total_space($dossiercsv)."<br />");
            if ($temoin_creation_fichiers != "non") {
                my_echo("<script type='text/javascript'>
	document.getElementById('id_f_wind_txt').style.display='';
	</script>");
            }

            my_echo("<p>Vous pouvez copier/coller ces lignes dans un fichier texte pour effectuer l'import des comptes profs.</p>\n");
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            // my_echo("<a name='f_div'></a><h2>Generation d'un CSV du F_DIV pour SambaEdu3</h2>\n");
            // my_echo("<a name='f_div'></a><h3>Generation d'un CSV du F_DIV pour SambaEdu3</h3>\n");
            // my_echo("<a name='f_div'></a><h4>Generation d'un CSV du F_DIV pour SambaEdu3</h4>\n");
            my_echo("<a name='f_div'></a><h4>Génération d'un CSV du F_DIV");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            // if($temoin_creation_fichiers!="non") {$fich=fopen("$dossiercsv/se3/f_div.txt","w+");}
            if ($temoin_creation_fichiers != "non") {
                $fich = fopen("$dossiercsv/f_div.txt", "w+");
            } else {
                $fich = FALSE;
            }
            for ($i = 0; $i < count($divisions); $i ++) {
                $numind_pp = "";
                for ($m = 0; $m < count($prof); $m ++) {
                    if (isset($prof[$m]["prof_princ"])) {
                        for ($n = 0; $n < count($prof[$m]["prof_princ"]); $n ++) {
                            if ($prof[$m]["prof_princ"][$n]["code_structure"] == $divisions[$i]["code"]) {
                                $numind_pp = "P" . $prof[$m]["id"];
                            }
                        }
                    }
                }
                $chaine = $divisions[$i]["code"] . "|" . $divisions[$i]["code"] . "|" . $numind_pp;
                if ($fich) {
                    // fwrite($fich,$chaine."\n");
                    fwrite($fich, html_entity_decode($chaine) . "\n");
                }
                my_echo($chaine . "<br />\n");
            }
            if ($temoin_creation_fichiers != "non") {
                fclose($fich);
            }

            // my_echo("disk_total_space($dossiercsv)=".disk_total_space($dossiercsv)."<br />");

            if ($temoin_creation_fichiers != "non") {
                my_echo("<script type='text/javascript'>
		document.getElementById('id_f_div_txt').style.display='';
	</script>");
            }

            if ($temoin_au_moins_un_prof_princ != "oui") {
                my_echo("<p>Il semble que votre fichier ne comporte pas l'information suivante:<br />Qui sont les profs principaux?<br />Cela n'empêche cependant pas l'import du CSV.</p>\n");
            }
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");

            // my_echo("<a name='f_men'></a><h2>Generation d'un CSV du F_MEN pour SambaEdu3</h2>\n");
            // my_echo("<a name='f_men'></a><h3>Generation d'un CSV du F_MEN pour SambaEdu3</h3>\n");
            // my_echo("<a name='f_men'></a><h4>Generation d'un CSV du F_MEN pour SambaEdu3</h4>\n");
            my_echo("<a name='f_men'></a><h4>Génération d'un CSV du F_MEN");
            if ($chrono == 'y') {
                my_echo(" (<i>" . date_et_heure() . "</i>)");
            }
            my_echo("</h4>\n");
            my_echo("<blockquote>\n");
            if (($temoin_au_moins_une_matiere == "") || ($temoin_au_moins_un_prof == "")) {
                my_echo("<p>Votre fichier ne comporte pas suffisamment d'informations pour générer ce CSV.<br />Il faut que les emplois du temps soient remontés vers STS pour que le fichier XML permette de générer ce CSV.</p>\n");
            } else {
                unset($tab_chaine);
                $tab_chaine = array();

                // if($temoin_creation_fichiers!="non") {$fich=fopen("$dossiercsv/se3/f_men.txt","w+");}
                if ($temoin_creation_fichiers != "non") {
                    $fich = fopen("$dossiercsv/f_men.txt", "w+");
                } else {
                    $fich = FALSE;
                }
                for ($i = 0; $i < count($divisions); $i ++) {
                    // $divisions[$i]["services"][$j]["code_matiere"]
                    $classe = $divisions[$i]["code"];
                    for ($j = 0; $j < count($divisions[$i]["services"]); $j ++) {
                        $mat = "";
                        for ($m = 0; $m < count($matiere); $m ++) {
                            if ($matiere[$m]["code"] == $divisions[$i]["services"][$j]["code_matiere"]) {
                                $mat = $matiere[$m]["code_gestion"];
                            }
                        }
                        if ($mat != "") {

                            if (isset($divisions[$i]["services"][$j]["enseignants"])) {
                                for ($k = 0; $k < count($divisions[$i]["services"][$j]["enseignants"]); $k ++) {
                                    $chaine = $mat . "|" . $classe . "|P" . $divisions[$i]["services"][$j]["enseignants"][$k]["id"];
                                    if ($fich) {
                                        // fwrite($fich,$chaine."\n");
                                        fwrite($fich, html_entity_decode($chaine) . "\n");
                                    }
                                    my_echo($chaine . "<br />\n");
                                    $tab_chaine[] = $chaine;
                                }
                            } else {
                                $chaine = $mat . "|" . $classe . "|aucun";
                                $tab_chaine[] = $chaine;
                            }
                        }
                    }
                }

                // if($_POST['se3_groupes']=='yes') {
                // PROBLEME: On cree des groupes avec tous les membres de la classe...
                // my_echo("<hr width='200' />\n");
                for ($i = 0; $i < count($groupes); $i ++) {
                    unset($matimn);
                    $grocod = $groupes[$i]["code"];
                    // my_echo("<p>Groupe $i: \$grocod=$grocod<br />\n");
                    for ($m = 0; $m < count($matiere); $m ++) {
                        // my_echo("\$matiere[$m][\"code\"]=".$matiere[$m]["code"]." et \$groupes[$i][\"code_matiere\"]=".$groupes[$i]["code_matiere"]."<br />\n");
                        // my_echo("\$matiere[$m][\"code\"]=".$matiere[$m]["code"]." et \$groupes[$i][\"service\"][0][\"code_matiere\"]=".$groupes[$i]["service"][0]["code_matiere"]."<br />\n");
                        // +++++++++++++++++++++++++
                        // +++++++++++++++++++++++++
                        // PB: si on a un meme groupe/regroupement pour plusieurs matieres, on ne recupere que le premier
                        // A FAIRE: Revoir le dispositif pour creer dans ce cas des groupes <NOM_GROUPE>_<MATIERE> ou <MATIERE>_<NOM_GROUPE>
                        // +++++++++++++++++++++++++
                        // +++++++++++++++++++++++++
                        // if(isset($groupes[$i]["code_matiere"])) {
                        // if($matiere[$m]["code"]==$groupes[$i]["code_matiere"]) {
                        if (isset($groupes[$i]["service"][0]["code_matiere"])) {
                            if ($matiere[$m]["code"] == $groupes[$i]["service"][0]["code_matiere"]) {
                                // $matimn=$programme[$k]["code_matiere"];
                                $matimn = $matiere[$m]["code_gestion"];
                                // my_echo("<b>Trouve: matiere ne$m: \$matimn=$matimn</b><br />\n");
                            }
                        }
                    }
                    // $groupes[$i]["enseignant"][$m]["id"]
                    // $groupes[$i]["divisions"][$j]["code"]
                    if ((isset($matimn)) && ($matimn != "")) {
                        for ($j = 0; $j < count($groupes[$i]["divisions"]); $j ++) {
                            $elstco = $groupes[$i]["divisions"][$j]["code"];
                            // my_echo("\$elstco=$elstco<br />\n");
                            if (! isset($groupes[$i]["enseignant"])) {
                                $chaine = $matimn . "|" . $elstco . "|";
                                $tab_chaine[] = $chaine;
                            } else {
                                if (count($groupes[$i]["enseignant"]) == 0) {
                                    // $chaine="$matimn;;$elstco");
                                    $chaine = $matimn . "|" . $elstco . "|";
                                    /*
                                     * if($fich) {
                                     * fwrite($fich,html_entity_decode($chaine)."\n");
                                     * }
                                     * my_echo($chaine."<br />\n");
                                     */
                                    $tab_chaine[] = $chaine;
                                } else {
                                    for ($m = 0; $m < count($groupes[$i]["enseignant"]); $m ++) {
                                        $numind = $groupes[$i]["enseignant"][$m]["id"];
                                        // my_echo("$matimn;P$numind;$elstco<br />\n");
                                        // $chaine="$matimn;P$numind;$elstco";
                                        $chaine = $matimn . "|" . $elstco . "|P" . $numind;
                                        /*
                                         * if($fich) {
                                         * fwrite($fich,html_entity_decode($chaine)."\n");
                                         * }
                                         * my_echo($chaine."<br />\n");
                                         */
                                        $tab_chaine[] = $chaine;
                                    }
                                }
                            }
                            // my_echo($grocod.";".$groupes[$i]["divisions"][$j]["code"]."<br />\n");
                        }
                    }
                }
                // }

                $tab2_chaine = array_unique($tab_chaine);
                // for($i=0;$i<count($tab2_chaine);$i++) {
                for ($i = 0; $i < count($tab_chaine); $i ++) {
                    if (isset($tab2_chaine[$i])) {
                        if ($tab2_chaine[$i] != "") {
                            if ($fich) {
                                fwrite($fich, html_entity_decode($tab2_chaine[$i]) . "\n");
                            }
                            my_echo($tab2_chaine[$i] . "<br />\n");
                        }
                    }
                }
                if ($fich) {
                    fclose($fich);
                }
                if ($temoin_creation_fichiers != "non") {
                    // my_echo("disk_total_space($dossiercsv)=".disk_total_space($dossiercsv)."<br />");
                    my_echo("<script type='text/javascript'>
		document.getElementById('id_f_men_txt').style.display='';
	</script>");
                }
            }
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
            my_echo("</blockquote>\n");
        }
    } else {
        // my_echo("<p>ERREUR lors de l'ouverture du fichier ".$sts_xml_file['name']." (<i>".$sts_xml_file['tmp_name']."</i>).</p>\n");

        my_echo("<script type='text/javascript'>
document.getElementById('div_signalements').style.display='';
document.getElementById('div_signalements').innerHTML=document.getElementById('div_signalements').innerHTML+'<br /><a href=\'#erreur_sts_file\'>Erreur</a> lors de l\'ouverture du fichier <b>$sts_xml_file</b>';
</script>\n");

        my_echo("<p style='color:red'><a name='erreur_sts_file'></a>ERREUR lors de l'ouverture du fichier '$sts_xml_file'.</p>\n");

        my_echo("<div style='color:red;'>");
        foreach (libxml_get_errors() as $xml_error) {
            my_echo($xml_error->message . "<br />");
        }
        my_echo("</div>");

        libxml_clear_errors();
    }

    if ($temoin_creation_fichiers != "non") {
        my_echo("<script type='text/javascript'>
	document.getElementById('id_suppr_txt').style.display='';
</script>");
    }

    // =========================================================

    // Creation d'une sauvegarde:
    // Probleme avec l'emplacement dans lequel www-se3 peut ecrire...
    // if($fich=fopen("/var/se3/save/sauvegarde_ldap.sh","w+")) {

    /*
     * if($fich=fopen("/var/remote_adm/sauvegarde_ldap.sh","w+")) {
     * fwrite($fich,'#!/bin/bash
     * date=$(date +%Y%m%d-%H%M%S)
     * #dossier_svg="/var/se3/save/sauvegarde_ldap_avant_import"
     * dossier_svg="/var/remote_adm/sauvegarde_ldap_avant_import"
     * mkdir -p $dossier_svg
     *
     * BASEDN=$(cat /etc/ldap/ldap.conf | grep "^BASE" | tr "\t" " " | sed -e "s/ \{2,\}/ /g" | cut -d" " -f2)
     * ROOTDN=$(cat /etc/ldap/slapd.conf | grep "^rootdn" | tr "\t" " " | cut -d\'"\' -f2)
     * PASSDN=$(cat /etc/ldap.secret)
     *
     * #source /etc/ssmtp/ssmtp.conf
     *
     * echo "Erreur lors de la sauvegarde de precaution effectuee avant import.
     * Le $date" > /tmp/erreur_svg_prealable_ldap_${date}.txt
     * # Le fichier d erreur est genere quoi qu il arrive, mais il n est expedie qu en cas de probleme de sauvegarde
     * /usr/bin/ldapsearch -xLLL -D $ROOTDN -w $PASSDN > $dossier_svg/ldap_${date}.ldif || mail root -s "Erreur sauvegarde LDAP" < /tmp/erreur_svg_prealable_ldap_${date}.txt
     * rm -f /tmp/erreur_svg_prealable_ldap_${date}.txt
     * ');
     * fclose($fich);
     * exec("/bin/bash /var/se3/save/sauvegarde_ldap.sh",$retour);
     * }
     */

    // exec("/usr/bin/sudo $pathscripts/sauvegarde_ldap_avant_import.sh", $retour);

    // =========================================================

    if ($chrono == 'y') {
        my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
    }

    my_echo("</blockquote>\n");

    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    $infos_corrections_gecos = "";

    my_echo("<a name='profs_se3'></a>\n");
    my_echo("<a name='creer_profs'></a>\n");
    my_echo("<h3>Création des comptes professeurs");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }
    my_echo("</h3>\n");
    my_echo("<script type='text/javascript'>
	document.getElementById('id_creer_profs').style.display='';
</script>");
    my_echo("<blockquote>\n");
    if ((! isset($prof)) || (count($prof) == 0)) {} else {
        $cpt = 0;
        while ($cpt < count($prof)) {
            if ($prof[$cpt]["fonction"] == "ENS") {
                // Pour chaque prof:
                // $chaine="P".$prof[$cpt]["id"]."|".$prof[$cpt]["nom_usage"]."|".$prof[$cpt]["prenom"]."|".$date."|".$prof[$cpt]["sexe"]
                // Temoin d'echec de creation du compte prof
                $temoin_erreur_prof = "";
                $date = str_replace("-", "", $prof[$cpt]["date_naissance"]);
                $employeeNumber = "P" . $prof[$cpt]["id"];
                if ($tab = verif_employeeNumber($config, $employeeNumber)) {
                    $cn = $tab['cn'];
                    my_echo("<p>cn existant pour employeeNumber=$employeeNumber: $cn<br />\n");

                    if ($tab['branch'] == "people") {
                        // ================================
                        // Verification/correction du GECOS
                        if ($corriger_gecos_si_diff == 'y') {
                            $prenom = remplace_accents(traite_espaces($prof[$cpt]["prenom"]));
                            if ($prof[$cpt]["sexe"] == 1) {
                                $sexe = "M";
                            } else {
                                $sexe = "F";
                            }
                            $naissance = $date;
                            verif_et_corrige_user($config, $cn, $naissance, $sexe, $simulation);
                        }
                        // ================================

                        // ================================
                        // Verification/correction du givenName
                        if ($corriger_givenname_si_diff == 'y') {
                            $nom = remplace_accents(traite_espaces($prof[$cpt]["nom_usage"]));
                            $prenom = strtolower(remplace_accents(traite_espaces($prof[$cpt]["prenom"])));
                            // my_echo("Test de la correction du givenName: verif_et_corrige_givenname($cn,$prenom)<br />\n");
                            verif_et_corrige_nom($config, $cn, $prenom, $nom, $simulation);
                        }
                        // ================================

                        // ================================
                        // Verification/correction du pseudo
                        // if($annuelle=="y") {
                        if ($controler_pseudo == 'y') {
                            $nom = remplace_accents(traite_espaces($prof[$cpt]["nom_usage"]));
                            $prenom = strtolower(remplace_accents(traite_espaces($prof[$cpt]["prenom"])));
                            verif_et_corrige_pseudo($config, $cn, $nom, $prenom, $annuelle, $simulation);
                        }
                        // }
                        // ================================
                    } elseif ($tab['branch'] == "trash") {
                        // On restaure le compte de Trash puisqu'il y est avec le meme employeeNumber
                        my_echo("Restauration du compte depuis la branche Trash: \n");
                        if (recup_from_trash($confg, $cn)) {
                            my_echo("<font color='green'>SUCCES</font>");
                        } else {
                            my_echo("<font color='red'>ECHEC</font>");
                            $nb_echecs ++;
                        }
                        my_echo(".<br />\n");
                    }
                } else {
                    my_echo("<p>Pas encore d'cn pour employeeNumber=$employeeNumber<br />\n");

                    // $prenom=remplace_accents($prof[$cpt]["prenom"]);
                    // $nom=remplace_accents($prof[$cpt]["nom_usage"]);
                    // $prenom=remplace_accents(traite_espaces($prof[$cpt]["prenom"]));
                    // $nom=remplace_accents(traite_espaces($prof[$cpt]["nom_usage"]));
                    $prenom = remplace_accents(traite_espaces($prof[$cpt]["prenom"]));
                    $nom = remplace_accents(traite_espaces($prof[$cpt]["nom_usage"]));
                    if ($cn = verif_nom_prenom($config, $nom, $prenom)) {
                        my_echo("$nom $prenom est dans l'annuaire sans employeeNumber: $cn<br />\n");
                        my_echo("Mise à jour avec l'employeeNumber $employeeNumber: \n");
                        // $comptes_avec_employeeNumber_mis_a_jour++;

                        if ($simulation != "y") {
                            $attributs = array();
                            $attributs["title"] = $employeeNumber;
                            if (modify_ad($config, $cn, "user", $attributs, "add")) {
                                my_echo("<font color='green'>SUCCES</font>");
                                $comptes_avec_employeeNumber_mis_a_jour ++;
                                $tab_comptes_avec_employeeNumber_mis_a_jour[] = $cn;
                            } else {
                                my_echo("<font color='red'>ECHEC</font>");
                                $nb_echecs ++;
                            }
                            my_echo(".<br />\n");
                        } else {
                            my_echo("<font color='blue'>SIMULATION</font>");
                            $comptes_avec_employeeNumber_mis_a_jour ++;
                            $tab_comptes_avec_employeeNumber_mis_a_jour[] = $cn;
                        }
                    } else {
                        my_echo("Il n'y a pas de $nom $prenom dans l'annuaire sans employeeNumber<br />\n");
                        my_echo("C'est donc un <b>nouveau compte</b>.<br />\n");
                        // $nouveaux_comptes++;

                        if ($temoin_f_cn == 'y') {
                            // On cherche une ligne correspondant a l'employeeNumber dans le F_cn.TXT
                            if ($cn = get_cn_from_f_cn_file($employeeNumber)) {
                                // On controle si ce login est deja employe

                                $verif1 = search_ad($config, $cn, "user", $config['dn']['people']);
                                $verif2 = search_ad($config, $cn, "user", $config['dn']['trash']);
                                if (count($verif1) > 0) {
                                    // Le login propose est deja dans l'annuaire
                                    my_echo("Le login proposé <span style='color:red;'>$cn</span> est déjà dans l'annuaire (<i>branche People</i>).<br />\n");
                                    $cn = "";
                                } elseif (count($verif2) > 0) {
                                    // Le login propose est deja dans l'annuaire
                                    my_echo("Le login proposé <span style='color:red;'>$cn</span> est déjà dans l'annuaire (<i>branche Trash</i>).<br />\n");
                                    $cn = "";
                                } else {
                                    my_echo("Ajout du professeur $prenom $nom (<i style='color:magenta;'>$cn</i>): ");
                                }
                            }

                            if ($cn == '') {
                                // Creation d'un cn:
                                if (! $cn = creer_cn($config, $nom, $prenom)) {
                                    $temoin_erreur_prof = "o";
                                    my_echo("<font color='red'>ECHEC: Problème lors de la création de l'cn...</font><br />\n");
                                    if ("$error" != "") {
                                        my_echo("<font color='red'>$error</font><br />\n");
                                    }
                                    $nb_echecs ++;
                                } else {
                                    my_echo("Ajout du professeur $prenom $nom (<i>$cn</i>): ");
                                }
                            }

                            if (($cn != '') && ($temoin_erreur_prof != "o")) {
                                if ($prof[$cpt]["sexe"] == 1) {
                                    $sexe = "M";
                                } else {
                                    $sexe = "F";
                                }
                                $naissance = $date;

                                switch ($pwdPolicy) {
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

                                if ($simulation != "y") {
                                    if (create_user($config, $cn, $prenom, $nom, $password, $naissance, $sexe, "Profs", $employeeNumber)) {
                                        my_echo("<font color='green'>SUCCES</font>");
                                        $tab_nouveaux_comptes[] = $cn;
                                        $listing[$nouveaux_comptes]['nom'] = "$nom";
                                        $listing[$nouveaux_comptes]['pre'] = "$prenom";
                                        $listing[$nouveaux_comptes]['cla'] = "prof";
                                        $listing[$nouveaux_comptes]['cn'] = "$cn";
                                        $listing[$nouveaux_comptes]['pwd'] = "$password";
                                        $nouveaux_comptes ++;
                                    } else {
                                        my_echo("<font color='red'>ECHEC</font>");
                                        $nb_echecs ++;
                                        $temoin_erreur_prof = "o";
                                    }
                                } else {
                                    my_echo("<font color='blue'>SIMULATION</font>");
                                    $nouveaux_comptes ++;
                                    $tab_nouveaux_comptes[] = $cn;
                                }
                                my_echo("<br />\n");
                            }
                        } else {
                            // On n'a pas de F_cn.TXT pour imposer des logins

                            // Creation d'un cn:
                            if (! $cn = creer_cn($config, $nom, $prenom)) {
                                $temoin_erreur_prof = "o";
                                my_echo("<font color='red'>ECHEC: Problème lors de la création de l'cn...</font><br />\n");
                                if ("$error" != "") {
                                    my_echo("<font color='red'>$error</font><br />\n");
                                }
                                $nb_echecs ++;
                            } else {
                                // $sexe=$prof[$cpt]["sexe"];
                                if ($prof[$cpt]["sexe"] == 1) {
                                    $sexe = "M";
                                } else {
                                    $sexe = "F";
                                }
                                $naissance = $date;

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
                                my_echo("Ajout du professeur $prenom $nom (<i>$cn</i>): ");

                                if ($simulation != "y") {
                                    if (create_user($config, $cn, $prenom, $nom, $password, $naissance, $sexe, "Profs", $employeeNumber)) {
                                        my_echo("<font color='green'>SUCCES</font>");
                                        $tab_nouveaux_comptes[] = $cn;
                                        $listing[$nouveaux_comptes]['nom'] = "$nom";
                                        $listing[$nouveaux_comptes]['pre'] = "$prenom";
                                        $listing[$nouveaux_comptes]['cla'] = "prof";
                                        $listing[$nouveaux_comptes]['cn'] = "$cn";
                                        $listing[$nouveaux_comptes]['pwd'] = "$password";
                                        $nouveaux_comptes ++;
                                    } else {
                                        my_echo("<font color='red'>ECHEC</font>");
                                        $nb_echecs ++;
                                        $temoin_erreur_prof = "o";
                                    }
                                } else {
                                    my_echo("<font color='blue'>SIMULATION</font>");
                                    $nouveaux_comptes ++;
                                    $tab_nouveaux_comptes[] = $cn;
                                }
                                my_echo("<br />\n");
                            }
                        }
                    }
                }
                if ($chrono == 'y') {
                    my_echo("Fin: " . date_et_heure() . "<br />\n");
                }
            }
            $cpt ++;
        }
    }

    if (count($tab_no_Trash_prof) > 0) {
        my_echo("<h3>Comptes à préserver de la corbeille (Profs)");
        if ($chrono == 'y') {
            my_echo(" (<i>" . date_et_heure() . "</i>)");
        }
        my_echo("</h3>\n");

        my_echo("<blockquote>\n");
        for ($loop = 0; $loop < count($tab_no_Trash_prof); $loop ++) {
            $cn = $tab_no_Trash_prof[$loop];
            my_echo("\$cn=$cn<br />");
            if ($cn != "") {
                my_echo("<p>Contrôle du membre $cn titulaire du droit no_Trash_user: <br />");

                // Le membre de no_Trash_user existe-t-il encore dans People:
                // Si oui, on controle s'il est dans Profs... si necessaire on l'y met
                // Sinon, on le supprime de no_Trash_user
                $compte_existe = search_ad($config, $cn, "user");
                if (count($compte_existe) > 0) {

                    // On controle si le compte est membre du groupe Profs
                    if (type_user($config, $cn) == "Profs") {
                        my_echo("$cn est déjà membre du groupe Profs.<br />\n");
                    } else {
                        my_echo("$cn n'est plus membre du groupe Profs.<br />Retablissement de l'appartenance de $cn au groupe Profs: ");
                        if ($simulation != "y") {
                            if (groupaddmember($config, $cn, "Profs")) {
                                my_echo("<font color='green'>SUCCES</font>");
                            } else {
                                my_echo("<font color='red'>ECHEC</font>");
                                $nb_echecs ++;
                            }
                        } else {
                            my_echo("<font color='blue'>SIMULATION</font>");
                        }
                        my_echo(".<br />\n");
                    }
                }
            }
        }
        my_echo("</blockquote>\n");
    }

    if ($chrono == 'y') {
        my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
    }
    my_echo("</blockquote>\n");

    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    my_echo("<a name='creer_eleves'></a>\n");
    my_echo("<a name='eleves_se3'></a>\n");
    my_echo("<h3>Création des comptes élèves");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }
    my_echo("</h3>\n");
    my_echo("<script type='text/javascript'>
	document.getElementById('id_creer_eleves').style.display='';
</script>");
    my_echo("<blockquote>\n");
    $tab_classe = array();
    $cpt_classe = - 1;
    if (isset($tabnumero)) {
        for ($k = 0; $k < count($tabnumero); $k ++) {
            $temoin_erreur_eleve = "n";

            $numero = $tabnumero[$k];

            // La classe existe-t-elle?
            $div = $eleve[$numero]["division"];
            $div = apostrophes_espaces_2_underscore(remplace_accents($div));
            $cn_classe = search_ad($config, $prefix . $div, "classe");
            if (count($cn_classe) == 0) {
                // La classe n'existe pas dans l'annuaire.

                // LE TEST CI-DESSOUS NE CONVIENT PLUS AVEC UN TABLEAU A PLUSIEURS DIMENSIONS... A CORRIGER
                // if(!in_array($div,$tab_classe)) {

                $temoin_classe = "";
                for ($i = 0; $i < count($tab_classe); $i ++) {
                    if ($tab_classe[$i]["nom"] == $div) {
                        $temoin_classe = "y";
                    }
                }

                if ($temoin_classe != "y") {
                    // On ajoute la classe a creer.
                    $cpt_classe ++;
                    my_echo("<p>Nouvelle classe: $div</p>\n");
                    $tab_classe[$cpt_classe] = array();
                    $tab_classe[$cpt_classe]["nom"] = $div;
                    $tab_classe[$cpt_classe]["creer_classe"] = "y";
                    $tab_classe[$cpt_classe]["eleves"] = array();
                }
            } else {
                // La classe existe deja dans l'annuaire.

                $temoin_classe = "";
                for ($i = 0; $i < count($tab_classe); $i ++) {
                    if ($tab_classe[$i]["nom"] == $div) {
                        $temoin_classe = "y";
                    }
                }

                if ($temoin_classe != "y") {
                    // On ajoute la classe a creer.
                    $cpt_classe ++;
                    my_echo("<p>Classe existante: $div</p>\n");
                    $tab_classe[$cpt_classe] = array();
                    $tab_classe[$cpt_classe]["nom"] = $div;
                    $tab_classe[$cpt_classe]["creer_classe"] = "n";
                    $tab_classe[$cpt_classe]["eleves"] = array();
                }
            }

            // Pour chaque eleve:
            $employeeNumber = $eleve[$numero]["numero"];
            $tab = verif_employeeNumber($config, $employeeNumber);
            if ($tab) {
                $cn = $tab['cn'];
                my_echo("<p>cn existant pour employeeNumber=$employeeNumber: $cn<br />\n");

                if ($tab['branch'] == "people") {
                    // ================================
                    // Verification/correction du GECOS
                    if ($corriger_gecos_si_diff == 'y') {
                        $nom = remplace_accents(traite_espaces($eleve[$numero]["nom"]));
                        $prenom = remplace_accents(traite_espaces($eleve[$numero]["prenom"]));
                        $sexe = $eleve[$numero]["sexe"];
                        $naissance = $eleve[$numero]["date"];
                        verif_et_corrige_user($config, $cn, $naissance, $sexe, $simulation);
                    }
                    // ================================

                    // ================================
                    // Verification/correction du givenName
                    if ($corriger_givenname_si_diff == 'y') {
                        $nom = remplace_accents(traite_espaces($eleve[$numero]["nom"]));
                        $prenom = strtolower(remplace_accents(traite_espaces($eleve[$numero]["prenom"])));
                        // my_echo("Test de la correction du givenName: verif_et_corrige_givenname($cn,$prenom)<br />\n");
                        verif_et_corrige_nom($config, $cn, $prenom, $nom, $simulation);
                    }
                    // ================================

                    // ================================
                    // Verification/correction du pseudo
                    // if($annuelle=="y") {
                    if ($controler_pseudo == 'y') {
                        $nom = remplace_accents(traite_espaces($eleve[$numero]["nom"]));
                        $prenom = strtolower(remplace_accents(traite_espaces($eleve[$numero]["prenom"])));
                        verif_et_corrige_pseudo($config, $cn, $nom, $prenom, $annuelle, $simulation);
                    }
                    // }
                    // ================================
                } elseif ($tab['branch'] == "trash") {
                    // On restaure le compte de Trash puisqu'il y est avec le meme employeeNumber
                    my_echo("Restauration du compte depuis la branche Trash: \n");
                    if (recup_from_trash($config, $cn)) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                        $nb_echecs ++;
                    }
                    my_echo(".<br />\n");
                }
            } else {
                my_echo("<p>Pas encore de cn pour employeeNumber=$employeeNumber<br />\n");

                // $prenom=remplace_accents($eleve[$numero]["prenom"]);
                // $nom=remplace_accents($eleve[$numero]["nom"]);
                // $prenom=remplace_accents(traite_espaces($eleve[$numero]["prenom"]));
                // $nom=remplace_accents(traite_espaces($eleve[$numero]["nom"]));
                $prenom = remplace_accents(traite_espaces($eleve[$numero]["prenom"]));
                $nom = remplace_accents(traite_espaces($eleve[$numero]["nom"]));
                if ($cn = verif_nom_prenom($config, $nom, $prenom)) {
                    my_echo("$nom $prenom est dans l'annuaire sans employeeNumber: $cn<br />\n");
                    my_echo("Mise à jour avec l'employeeNumber $employeeNumber: \n");
                    // $comptes_avec_employeeNumber_mis_a_jour++;

                    if ($simulation != "y") {
                        $attributs = array();
                        $attributs["title"] = $employeeNumber;
                        if (modify_ad($config, $cn, "user", $attributs, "modify")) {
                            my_echo("<font color='green'>SUCCES</font>");
                            $comptes_avec_employeeNumber_mis_a_jour ++;
                            $tab_comptes_avec_employeeNumber_mis_a_jour[] = $cn;
                        } else {
                            my_echo("<font color='red'>ECHEC</font>");
                            $nb_echecs ++;
                        }
                    } else {
                        my_echo("<font color='blue'>SIMULATION</font>");
                        $comptes_avec_employeeNumber_mis_a_jour ++;
                        $tab_comptes_avec_employeeNumber_mis_a_jour[] = $cn;
                    }
                    my_echo(".<br />\n");
                } else {
                    my_echo("Il n'y a pas de $nom $prenom dans l'annuaire sans employeeNumber<br />\n");
                    my_echo("C'est donc un <b>nouveau compte</b>.<br />\n");
                    // $nouveaux_comptes++;

                    $cn = "";
                    if ($temoin_f_cn == 'y') {
                        // On cherche une ligne correspondant a l'employeeNumber dans le F_cn.TXT
                        if ($cn = get_cn_from_f_cn_file($employeeNumber)) {
                            // On controle si ce login est deja employe

                            $verif1 = search_ad($config, $cn, "user", $config['dn']['people']);
                            $verif2 = search_ad($config, $cn, "user", $config['dn']['trash']);
                            if (count($verif1) > 0) {
                                // Le login propose est deja dans l'annuaire
                                my_echo("Le login proposé <span style='color:red;'>$cn</span> est déjà dans l'annuaire (<i>branche People</i>).<br />\n");
                                $cn = "";
                            } elseif (count($verif2) > 0) {
                                // Le login propose est deja dans l'annuaire
                                my_echo("Le login proposé <span style='color:red;'>$cn</span> est déjà dans l'annuaire (<i>branche Trash</i>).<br />\n");
                                $cn = "";
                            } else {
                                my_echo("Ajout de l'élève $prenom $nom (<i style='color:magenta;'>$cn</i>): ");
                            }
                        }

                        if ($cn == '') {
                            // Creation d'un cn:
                            $cn = creer_cn($config, $nom, $prenom);
                            if (! $cn) {
                                $temoin_erreur_eleve = "o";
                                my_echo("<font color='red'>ECHEC: Problème lors de la création du cn...</font><br />\n");
                                if ("$error" != "") {
                                    my_echo("<font color='red'>$error</font><br />\n");
                                }
                                $nb_echecs ++;
                            } else {
                                my_echo("Ajout de l'élève $prenom $nom (<i>$cn</i>): ");
                            }
                        }

                        if (($cn != '') && ($temoin_erreur_eleve != "o")) {
                            $sexe = $eleve[$numero]["sexe"];
                            $naissance = $eleve[$numero]["date"];
                            $ele_div = $eleve[$numero]['division'];

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

                            if ($simulation != "y") {
                                // DBG system ("echo 'add_suser : $cn,$nom,$prenom,$sexe,$naissance,$password,$employeeNumber' >> /tmp/comptes.log");
                                if (create_user($config, $cn, $prenom, $nom, $password, $naissance, $sexe, "Eleves", $employeeNumber)) {
                                    my_echo("<font color='green'>SUCCES</font>");
                                    $tab_nouveaux_comptes[] = $cn;
                                    $listing[$nouveaux_comptes]['nom'] = "$nom";
                                    $listing[$nouveaux_comptes]['pre'] = "$prenom";
                                    $listing[$nouveaux_comptes]['cla'] = "$ele_div";
                                    $listing[$nouveaux_comptes]['cn'] = "$cn";
                                    $listing[$nouveaux_comptes]['pwd'] = "$password";
                                    $nouveaux_comptes ++;
                                } else {
                                    my_echo("<font color='red'>ECHEC</font>");
                                    $temoin_erreur_eleve = "o";
                                    $nb_echecs ++;
                                }
                            } else {
                                my_echo("<font color='blue'>SIMULATION</font>");
                                $nouveaux_comptes ++;
                                $tab_nouveaux_comptes[] = $cn;
                            }
                            my_echo("<br />\n");
                        }
                    } else {
                        // Pas de F_cn.TXT fourni pour imposer des logins.

                        // Creation d'un cn:
                        if (! $cn = creer_cn($config, $nom, $prenom)) {
                            $temoin_erreur_eleve = "o";
                            my_echo("<font color='red'>ECHEC: Problème lors de la création de du cn...</font><br />\n");
                            if ("$error" != "") {
                                my_echo("<font color='red'>$error</font><br />\n");
                            }
                            $nb_echecs ++;
                        } else {
                            $sexe = $eleve[$numero]["sexe"];
                            $naissance = $eleve[$numero]["date"];
                            $ele_div = $eleve[$numero]["division"];

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

                            my_echo("Ajout de l'élève $prenom $nom (<i>$cn</i>): ");
                            if ($simulation != "y") {
                                // DBG system ("echo 'add_suser : $cn,$nom,$prenom,$sexe,$naissance,$password,$employeeNumber' >> /tmp/comptes.log");
                                /*
                                 * if(strtolower($nom)=="andro") {
                                 * $f_tmp=fopen("/tmp/debug_accents.txt","a+");
                                 * fwrite($f_tmp,"useradd($config, $nom,$prenom,$sexe,$naissance,$password,$employeeNumber)\n");
                                 * fclose($f_tmp);
                                 * }
                                 */

                                if (create_user($config, $cn, $prenom, $nom, $password, $naissance, $sexe, "Eleves", $employeeNumber)) {
                                    my_echo("<font color='green'>SUCCES</font>");
                                    $tab_nouveaux_comptes[] = $cn;
                                    $listing[$nouveaux_comptes]['nom'] = "$nom";
                                    $listing[$nouveaux_comptes]['pre'] = "$prenom";
                                    $listing[$nouveaux_comptes]['cla'] = "$ele_div";
                                    $listing[$nouveaux_comptes]['cn'] = "$cn";
                                    $listing[$nouveaux_comptes]['pwd'] = "$password";
                                    $nouveaux_comptes ++;
                                } else {
                                    my_echo("<font color='red'>ECHEC</font>");
                                    $temoin_erreur_eleve = "o";
                                    $nb_echecs ++;
                                }
                            } else {
                                my_echo("<font color='blue'>SIMULATION</font>");
                                $nouveaux_comptes ++;
                                $tab_nouveaux_comptes[] = $cn;
                            }
                            my_echo("<br />\n");
                        }
                    }
                }
            }
            if ($chrono == 'y') {
                my_echo("Fin: " . date_et_heure() . "<br />\n");
            }
            // Ajout de l'eleve au tableau de la classe:

            if ($temoin_erreur_eleve != "o") {
                my_echo("Ajout de $cn au tableau de la classe $div.<br />\n");
                // $tab_classe[$cpt_classe]["eleves"][]=$cn;
                // PROBLEME: Avec l'import XML, les eleves ne sont jamais tries par classes... et ce n'est le cas dans l'import CSV que si on a fait le tri dans ce sens
                // Recherche de l'indice dans tab_classe
                $ind_classe = - 1;
                for ($i = 0; $i < count($tab_classe); $i ++) {
                    if ($tab_classe[$i]["nom"] == $div) {
                        $ind_classe = $i;
                    }
                }
                if ($ind_classe != - 1) {
                    $tab_classe[$ind_classe]["eleves"][] = $cn;
                }
                if (($simulation != "y")) {
                    if (add_user_group($config, $prefix . $div, $cn)) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                        $nb_echecs ++;
                    }
                } else {
                    my_echo("<font color='blue'>SIMULATION</font>");
                }
                my_echo(".<br />\n");
            }
            my_echo("</p>\n");
        }
    }
    if (count($tab_no_Trash_eleve) > 0) {
        my_echo("<h3>Comptes à préserver de la corbeille (Eleves)");
        if ($chrono == 'y') {
            my_echo(" (<i>" . date_et_heure() . "</i>)");
        }
        my_echo("</h3>\n");

        my_echo("<blockquote>\n");
        for ($loop = 0; $loop < count($tab_no_Trash_eleve); $loop ++) {
            $cn = $tab_no_Trash_eleve[$loop];
            my_echo("\$cn=$cn<br />");
            if ($cn != "") {
                my_echo("<p>Contrôle du membre $cn titulaire du droit no_Trash_user: <br />");

                // Le membre de no_Trash_user existe-t-il encore dans People:
                // Si oui, on controle s'il est dans Eleves... si necessaire on l'y met
                // Sinon, on le supprime de no_Trash_user
                $compte_existe = search_user($config, $cn);
                if (count($compte_existe) > 0) {

                    // On controle si le compte est membre du groupe Eleves
                    if (type_user($config, $cn) == "Eleves") {
                        my_echo("$cn est déjà membre du groupe Eleves.<br />\n");
                    } else {
                        my_echo("$cn n'est plus membre du groupe Eleves.<br />Retablissement de l'appartenance de $cn au groupe Eleves: ");
                        if ($simulation != "y") {
                            if (groupaddmember($config, $cn, "Eleves")) {
                                my_echo("<font color='green'>SUCCES</font>");
                            } else {
                                my_echo("<font color='red'>ECHEC</font>");
                                $nb_echecs ++;
                            }
                        } else {
                            my_echo("<font color='blue'>SIMULATION</font>");
                        }
                        my_echo(".<br />\n");
                    }
                }
            }
        }
        my_echo("</blockquote>\n");
    }

    if ($chrono == 'y') {
        my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
    }
    my_echo("</blockquote>\n");

    if ($simulation == "y") {
        my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

        my_echo("<a name='fin'></a>\n");
        // my_echo("<h3>Rapport final de simulation</h3>");
        my_echo("<h3>Rapport final de simulation");
        if ($chrono == 'y') {
            my_echo(" (<i>" . date_et_heure() . "</i>)");
        }
        my_echo("</h3>\n");
        my_echo("<blockquote>\n");
        my_echo("<script type='text/javascript'>
	document.getElementById('id_fin').style.display='';
</script>");

        my_echo("<p>Fin de la simulation!</p>\n");

        $chaine = "";
        if ($nouveaux_comptes == 0) {
            // my_echo("<p>Aucun nouveau compte ne serait cree.</p>\n");
            $chaine .= "<p>Aucun nouveau compte ne serait créé.</p>\n";
        } elseif ($nouveaux_comptes == 1) {
            // my_echo("<p>$nouveaux_comptes nouveau compte serait cree: $tab_nouveaux_comptes[0]</p>\n");
            $chaine .= "<p>$nouveaux_comptes nouveau compte serait créé: $tab_nouveaux_comptes[0]</p>\n";
        } else {
            /*
             * my_echo("<p>$nouveaux_comptes nouveaux comptes seraient crees: ");
             * my_echo($tab_nouveaux_comptes[0]);
             * for($i=1;$i<count($tab_nouveaux_comptes);$i++) {my_echo(", $tab_nouveaux_comptes[$i]");}
             * my_echo("</p>\n");
             * my_echo("<p><i>Attention:</i> Si un nom de compte est en doublon dans les nouveaux comptes, c'est un bug de la simulation.<br />Le probleme ne se produira pas en mode creation.</p>\n");
             */
            $chaine .= $tab_nouveaux_comptes[0];
            for ($i = 1; $i < count($tab_nouveaux_comptes); $i ++) {
                $chaine .= ", $tab_nouveaux_comptes[$i]";
            }
            $chaine .= "</p>\n";
            $chaine .= "<p><i>Attention:</i> Si un nom de compte est en doublon dans les nouveaux comptes, c'est un bug de la simulation.<br />Le problème ne se produira pas en mode création.</p>\n";
        }

        if ($comptes_avec_employeeNumber_mis_a_jour == 0) {
            // my_echo("<p>Aucun compte existant sans employeeNumber n'aurait ete recupere/corrige.</p>\n");
            $chaine .= "<p>Aucun compte existant sans employeeNumber n'aurait été récupéré/corrigé.</p>\n";
        } elseif ($comptes_avec_employeeNumber_mis_a_jour == 1) {
            // my_echo("<p>$comptes_avec_employeeNumber_mis_a_jour compte existant sans employeeNumber aurait ete recupere/corrige (<i>son employeeNumber serait maintenant renseigne</i>): $tab_comptes_avec_employeeNumber_mis_a_jour[0]</p>\n");
            $chaine .= "<p>$comptes_avec_employeeNumber_mis_a_jour compte existant sans employeeNumber aurait été récupéré/corrigé (<i>son employeeNumber serait maintenant renseigné</i>): $tab_comptes_avec_employeeNumber_mis_a_jour[0]</p>\n";
        } else {
            /*
             * my_echo("<p>$comptes_avec_employeeNumber_mis_a_jour comptes existants sans employeeNumber auraient ete recuperes/corriges (<i>leur employeeNumber serait maintenant renseigne</i>): ");
             * my_echo("$tab_comptes_avec_employeeNumber_mis_a_jour[0]");
             * for($i=1;$i<count($tab_comptes_avec_employeeNumber_mis_a_jour);$i++) {my_echo(", $tab_comptes_avec_employeeNumber_mis_a_jour[$i]");}
             * my_echo("</p>\n");
             */
            $chaine .= "<p>$comptes_avec_employeeNumber_mis_a_jour comptes existants sans employeeNumber auraient été récupérés/corrigés (<i>leur employeeNumber serait maintenant renseigné</i>): ";
            $chaine .= "$tab_comptes_avec_employeeNumber_mis_a_jour[0]";
            for ($i = 1; $i < count($tab_comptes_avec_employeeNumber_mis_a_jour); $i ++) {
                $chaine .= ", $tab_comptes_avec_employeeNumber_mis_a_jour[$i]";
            }
            $chaine .= "</p>\n";
        }

        $chaine .= "<p>On ne simule pas la création des groupes... pour le moment.</p>\n";

        my_echo($chaine);

        // Envoi par mail de $chaine et $echo_http_file
        // Envoi par mail de $chaine et $echo_http_file
        // Controler les champs affectes...
        if (isset($config["admin_mail"])) {
            $adressedestination = $config["admin_mail"];
            $sujet = $config['domain'] . " Rapport de ";
            if ($simulation == "y") {
                $sujet .= "simulation de ";
            }
            $sujet .= "création de comptes";
            $message = "Import du $debut_import\n";
            $message .= "$chaine\n";
            $message .= "\n";

            if ($rafraichir_classes == "y") {
                if ($nouveaux_comptes > 0) {
                    $message .= "Rafraichissement des classes lancé/effectué.\n";
                } else {
                    $message .= "Pas de nouveau compte, donc pas de rafraichissement des classes lancé.\n";
                }
            } else {
                $message .= "Pas de rafraichissement des classes demandé.\n";
            }
            $message .= "\n";

            $message .= "Vous pouvez consulter le rapport détaillé à l'adresse $echo_http_file\n";
            $entete = "From: " . $config["admin_mail"];
            mail("$adressedestination", "$sujet", "$message", "$entete") or my_echo("<p style='color:red;'><b>ERREUR</b> lors de l'envoi du rapport par mail.</p>\n");
        } else
            my_echo("<p style='color:red;'><b>MAIL:</b> La configuration mail ne permet pas d'expédier le rapport.<br />Consultez/renseignez le menu Informations système/Actions sur le serveur/Configurer l'expédition des mails.</p>\n");

        if ($chrono == 'y') {
            my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
        }

        my_echo("<p><a href='" . $www_import . "'>Retour</a>.</p>\n");
        my_echo("<script type='text/javascript'>
	compte_a_rebours='n';
</script>\n");
        my_echo("</blockquote>\n");

        my_echo("</body>\n</html>\n");

        // Renseignement du témoin de mise à jour terminée.
        set_param($config, "imprt_cmpts_en_cours", "n");
        return true;
    }

    // Creation des groupes
    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    my_echo("<a name='creer_classes'></a>\n");
    my_echo("<a name='classes_se3'></a>\n");
    my_echo("<h3>Création des groupes Classes et Equipes");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }
    my_echo("</h3>\n");
    my_echo("<script type='text/javascript'>
	document.getElementById('id_creer_classes').style.display='';
</script>");
    my_echo("<blockquote>\n");
    // Les groupes classes pour commencer:
    for ($i = 0; $i < count($tab_classe); $i ++) {
        $div = $tab_classe[$i]["nom"];
        $temoin_classe = "";
        my_echo("$div <p>");
        if ($tab_classe[$i]["creer_classe"] == "y") {
            my_echo("Création du groupe classe Classe_" . $prefix . "$div: ");
            if (create_group($config, $prefix . "$div", $div, "classe")) {
                my_echo("<font color='green'>SUCCES</font>");
            } else {
                my_echo("<font color='red'>ECHEC</font>");
                $temoin_classe = "PROBLEME";
                $nb_echecs ++;
            }
            my_echo("<br />\n");
            if ($chrono == 'y') {
                my_echo("Fin: " . date_et_heure() . "<br />\n");
            }
        }
        if ("$temoin_classe" == "") {
            my_echo("Ajout de membres au groupe Classe_" . $prefix . "$div: ");
            for ($j = 0; $j < count($tab_classe[$i]["eleves"]); $j ++) {
                $cn = $tab_classe[$i]["eleves"][$j];
                if (add_user_group($config, $prefix . "$div", $cn)) {
                    my_echo("<b>$cn</b> ");
                } else {
                    my_echo("<font color='red'>$cn</font> ");
                    $nb_echecs ++;
                }
                my_echo(" (<i>" . count($tab_classe[$i]["eleves"]) . "</i>)\n");
            }
            if ($chrono == 'y') {
                my_echo("<br />Fin: " . date_et_heure() . "<br />\n");
            }
        }
        my_echo("</p>\n");
        $ind = - 1;
        $temoin_equipe = "";

        if ($creer_equipes_vides == "y") {
            $temoin_equipe = "Remplissage des Equipes non demandé.";
        }

        // my_echo("<p>\$temoin_equipe=$temoin_equipe</p>");

        if (! isset($divisions)) {
            my_echo("<p>Le tableau \$division n'est pas rempli, ni même initialisé.</p>");
        } else {
            if ($temoin_equipe == "") {
                // Recherche de l'indice de la classe dans $divisions
                my_echo("<font color='yellow'>$div</font> ");
                for ($m = 0; $m < count($divisions); $m ++) {
                    $tmp_classe = apostrophes_espaces_2_underscore(remplace_accents($divisions[$m]["code"]));
                    if ($tmp_classe == $div) {
                        $ind = $m;
                    }
                }
                // my_echo("ind=$ind<br />");

                // Prof principal
                unset($tab_pp);
                $tab_pp = array();
                for ($m = 0; $m < count($prof); $m ++) {
                    if (isset($prof[$m]["prof_princ"])) {
                        for ($n = 0; $n < count($prof[$m]["prof_princ"]); $n ++) {
                            $tmp_div = $prof[$m]["prof_princ"][$n]["code_structure"];
                            // $tmp_div=ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($tmp_div)));
                            $tmp_div = apostrophes_espaces_2_underscore(remplace_accents($tmp_div));
                            if ($tmp_div == $div) {
                                $employeeNumber = "P" . $prof[$m]["id"];
                                $tabtmp = search_ad($config, $employeeNumber, "employeenumber");
                                if (count($tabtmp) != 0) {
                                    $cn = $tabtmp[0]['cn'];
                                    if (! in_array($cn, $tab_pp)) {
                                        $tab_pp[] = $cn;
                                    }
                                }
                            }
                        }
                    }
                }
                sort($tab_pp);

                // Membres de l'equipe
                unset($tab_equipe);
                $tab_equipe = array();
                my_echo("Ajout de membres à l'équipe Equipe_" . $prefix . "$div: ");
                for ($j = 0; $j < count($divisions[$ind]["services"]); $j ++) {
                    for ($k = 0; $k < count($divisions[$ind]["services"][$j]["enseignants"]); $k ++) {
                        // Recuperer le login correspondant au NUMIND
                        $employeeNumber = "P" . $divisions[$ind]["services"][$j]["enseignants"][$k]["id"];
                        if (! in_array($employeeNumber, $tab_equipe)) {
                            $tab_equipe[] = $employeeNumber;
                        }
                    }
                }

                if (isset($groupes)) {
                    // Rechercher les groupes associes a la classe pour affecter les collegues dans l'equipe
                    // $groupes[$i]["divisions"][$j]["code"] -> 3 A1
                    // $groupes[$i]["code_matiere"] -> 070800
                    // $groupes[$i]["enseignant"][$m]["id"] -> 38101
                    for ($n = 0; $n < count($groupes); $n ++) {
                        for ($j = 0; $j < count($groupes[$n]["divisions"]); $j ++) {
                            $grp_div = $groupes[$n]["divisions"][$j]["code"];
                            // $grp_div=ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($grp_div)));
                            $grp_div = apostrophes_espaces_2_underscore(remplace_accents($grp_div));
                            if ($grp_div == $div) {
                                if (isset($groupes[$n]["service"][0]["enseignant"])) {
                                    for ($p = 0; $p < count($groupes[$n]["service"]); $p ++) {
                                        for ($m = 0; $m < count($groupes[$n]["service"][$p]["enseignant"]); $m ++) {
                                            $employeeNumber = "P" . $groupes[$n]["service"][$p]["enseignant"][$m]["id"];
                                            if (! in_array($employeeNumber, $tab_equipe)) {
                                                $tab_equipe[] = $employeeNumber;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                for ($n = 0; $n < count($tab_equipe); $n ++) {
                    $employeeNumber = $tab_equipe[$n];
                    $pp = false;
                    $tabtmp = search_ad($config, $employeeNumber, "employeenumber");
                    if (in_array($cn, $tab_pp)) {
                        $pp = true;
                    }
                    if (count($tabtmp) != 0) {
                        $cn = $tabtmp[0]['cn'];
                        if (add_user_group($config, $prefix . "$div", $cn, $pp)) {
                            my_echo("<b>$cn</b> ");
                            if ($pp) {
                                my_echo(" : <font color='red'>prof principal</font>");
                            }
                        } else {
                            my_echo("<font color='red'>$cn</font> ");
                            $nb_echecs ++;
                        }
                    }
                }
                my_echo("<br />\n");
                if ($chrono == 'y') {
                    my_echo("Fin: " . date_et_heure() . "<br />\n");
                }

                my_echo("</p>\n");
            }
        }
    }

    if ($chrono == 'y') {
        my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
    }
    my_echo("</blockquote>\n");

    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    my_echo("<a name='creer_matieres'></a>\n");
    // my_echo("<h2>Creation des groupes Matieres</h2>\n");
    // my_echo("<h3>Creation des groupes Matieres</h3>\n");
    my_echo("<h3>Création des groupes Matières");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }

    // ===========================================================
    if ($creer_matieres == 'y') {
        my_echo("</h3>\n");
        my_echo("<script type='text/javascript'>
		document.getElementById('id_creer_matieres').style.display='';
</script>");
        my_echo("<blockquote>\n");

        if (! isset($matiere)) {
            my_echo("<p>Le tableau \$matiere n'est pas rempli, ni même initialisé.</p>\n");
        } else {
            for ($i = 0; $i < count($matiere); $i ++) {
                my_echo("<p>\n");
                $temoin_matiere = "";
                // $matiere[$i]["code_gestion"]
                $id_mat = $matiere[$i]["code"];
                // $code_gestion=$matiere[$i]["code_gestion"];
                // En principe les caracteres speciaux ont-ete filtres:
                // $matiere[$i]["code_gestion"]=trim(ereg_replace("[^a-zA-Z0-9&_. -]","",html_entity_decode($tabtmp[2])));
                $mat = $matiere[$i]["code_gestion"];
                $description = remplace_accents($matiere[$i]["libelle_long"]);
                // Faudrait-il enlever d'autres caracteres?

                // Le groupe Matiere existe-t-il?
                $tabtmp = search_ad($config, "Matiere_" . $prefix . "$mat", "group");
                if (count($tabtmp) == 0) {
                    $cn = "Matiere_" . $prefix . "$mat";

                    my_echo("Création de la matière Matiere_" . $prefix . "$mat: ");
                    if (create_group($config, $prefix . "$mat", $description, "matiere")) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                        $temoin_matiere = "PROBLEME";
                        $nb_echecs ++;
                    }
                    my_echo("<br />\n");
                    if ($chrono == 'y') {
                        my_echo("Fin: " . date_et_heure() . "<br />\n");
                    }
                }

                unset($tab_matiere);
                $tab_matiere = array();
                if ($temoin_matiere == "") {
                    my_echo("Ajout de membres à la matière Matiere_" . $prefix . "$mat: ");
                    for ($n = 0; $n < count($divisions); $n ++) {
                        for ($j = 0; $j < count($divisions[$n]["services"]); $j ++) {
                            if ($divisions[$n]["services"][$j]["code_matiere"] == $id_mat) {
                                if (isset($divisions[$n]["services"][$j]["enseignants"])) {
                                    for ($k = 0; $k < count($divisions[$n]["services"][$j]["enseignants"]); $k ++) {
                                        // Recuperer le login correspondant au NUMIND
                                        $employeeNumber = "P" . $divisions[$n]["services"][$j]["enseignants"][$k]["id"];
                                        // my_echo("\$employeeNumber=$employeeNumber<br />");
                                        if (! in_array($employeeNumber, $tab_matiere)) {
                                            $tab_matiere[] = $employeeNumber;
                                        }
                                    }
                                } else {
                                    my_echo("Pas d'enseignants pour" . $id_mat . "?");
                                }
                            }
                        }
                    }

                    // Rechercher les groupes associes a la matiere pour affecter les collegues dans l'equipe
                    // $groupes[$i]["divisions"][$j]["code"] -> 3 A1
                    // $groupes[$i]["code_matiere"] -> 070800
                    // $groupes[$i]["enseignant"][$m]["id"] -> 38101
                    for ($n = 0; $n < count($groupes); $n ++) {
                        if (isset($groupes[$n]["service"][0]["code_matiere"])) {
                            for ($p = 0; $p < count($groupes[$n]["service"]); $p ++) {
                                $grp_id_mat = $groupes[$n]["service"][$p]["code_matiere"];
                                if ($grp_id_mat == $id_mat) {
                                    for ($j = 0; $j < count($groupes[$n]["divisions"]); $j ++) {
                                        if (isset($groupes[$n]["service"][$p]["enseignant"])) {
                                            for ($m = 0; $m < count($groupes[$n]["service"][$p]["enseignant"]); $m ++) {
                                                $employeeNumber = "P" . $groupes[$n]["service"][$p]["enseignant"][$m]["id"];
                                                if (! in_array($employeeNumber, $tab_matiere)) {
                                                    $tab_matiere[] = $employeeNumber;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    for ($n = 0; $n < count($tab_matiere); $n ++) {
                        $employeeNumber = $tab_matiere[$n];
                        $tabtmp = search_ad($config, $employeeNumber, "employeenumber");
                        if (count($tabtmp) != 0) {
                            $cn = $tabtmp[0]['cn'];
                            // my_echo("\$cn=$cn<br />");
                            // Le prof est-il deja membre de la matiere?
                            if (add_user_group($config, "Matiere_" . $prefix . "$mat", $cn)) {
                                my_echo("<b>$cn</b> ");
                            } else {
                                my_echo("<font color='red'>$cn</font> ");
                                $nb_echecs ++;
                            }
                        }
                    }
                    my_echo("<br />\n");
                    if ($chrono == 'y') {
                        my_echo("Fin: " . date_et_heure() . "<br />\n");
                    }
                }
                my_echo("</p>\n");
            }
            if ($chrono == 'y') {
                my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
            }
        }
    } else {
        my_echo("</h3>\n");
        my_echo("<blockquote>\n");
        my_echo("<p>Création des Matières non demandée.</p>\n");
    }

    my_echo("</blockquote>\n");

    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    my_echo("<a name='creer_cours'></a>\n");
    // my_echo("<h2>Creation des groupes Cours</h2>\n");
    // my_echo("<h3>Creation des groupes Cours</h3>\n");
    my_echo("<h3>Création des groupes Cours");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }

    // ===========================================================
    // AJOUTS: 20070914 boireaus
    if ($creer_cours == 'y') {

        my_echo("</h3>\n");
        my_echo("<script type='text/javascript'>\n");
        my_echo("document.getElementById('id_creer_cours').style.display='';\n");
        my_echo("</script>\n");
        my_echo("<blockquote>\n");
        // Le, il faudrait faire un traitement different selon que l'import eleve se fait par CSV ou XML

        // $divisions[$i]["code"] 3 A2
        // $divisions[$i]["services"][$j]["code_matiere"] 020700
        // $divisions[$i]["services"][$j]["enseignants"][$k]["id"] 38764

        for ($i = 0; $i < count($divisions); $i ++) {
            $div = $divisions[$i]["code"];
            // $div=ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($div)));
            $div = apostrophes_espaces_2_underscore(remplace_accents($div));

            // Dans le cas de l'import XML, on recupere la liste des options suivies par les eleves
            $ind_div = "";
            if ($type_fichier_eleves == "xml") {
                // Identifier $k tel que $tab_division[$k]["nom"]==$div
                for ($k = 0; $k < count($tab_division); $k ++) {
                    if (apostrophes_espaces_2_underscore(remplace_accents($tab_division[$k]["nom"])) == $div) {
                        $ind_div = $k;
                        break;
                    }
                }
            }

            $temoin_cours = "";
            // On parcours toutes les matieres...
            for ($j = 0; $j < count($divisions[$i]["services"]); $j ++) {
                $id_mat = $divisions[$i]["services"][$j]["code_matiere"];

                // Recherche du nom court de la matiere:
                for ($n = 0; $n < count($matiere); $n ++) {
                    if ($matiere[$n]["code"] == $id_mat) {
                        $mat = $matiere[$n]["code_gestion"];
                    }
                }

                // La matiere est-elle optionnelle dans la classe?
                $temoin_matiere_optionnelle = "non";
                $ind_mat = "";
                if (($type_fichier_eleves == "xml") && ($ind_div != "")) {
                    for ($k = 0; $k < count($tab_division[$ind_div]["option"]); $k ++) {
                        // $tab_division[$k]["option"][$n]["code_matiere"]
                        if ($tab_division[$ind_div]["option"][$k]["code_matiere"] == $id_mat) {
                            $temoin_matiere_optionnelle = "oui";
                            $ind_mat = $k;
                            break;
                        }
                    }
                }

                // Recuperer tous les profs de la matiere dans la classe
                // ... les trier
                unset($tab_prof_cn);
                $tab_prof_cn = array();
                // On pourrait aussi parcourir l'annuaire... avec le filtre cn=Equipe_".$prefix."$div... peut-etre serait-ce plus rapide...
                for ($k = 0; $k < count($divisions[$i]["services"][$j]["enseignants"]); $k ++) {
                    // Recuperation de l'cn correspondant a l'employeeNumber
                    $employeeNumber = "P" . $divisions[$i]["services"][$j]["enseignants"][$k]["id"];
                    $tabtmp = search_ad($config, $employeeNumber, "employeenumber");
                    if (count($tabtmp) != 0) {
                        $cn = $tabtmp[0]['cn'];
                        if (! in_array($cn, $tab_prof_cn)) {
                            $tab_prof_cn[] = $cn;
                        }
                    }
                }
                sort($tab_prof_cn);

                // Recuperer tous les membres de la classe si la matiere n'a pas ete detectee comme optionnelle dans la classe
                // ... les trier
                unset($tab_eleve_cn);
                $tab_eleve_cn = array();
                if ($temoin_matiere_optionnelle != "oui") {
                    // my_echo("Recherche: get_tab_attribut(\"groups\", \"cn=Classe_".$prefix."$div\", $attribut)<br />");
                    $tabtmp = search_ad($config, $prefix . $div, "classe");
                    if (count($tabtmp) != 0) {
                        // my_echo("count(\$tabtmp)=".count($tabtmp)."<br />");
                        for ($k = 0; $k < count($tabtmp); $k ++) {
                            // my_echo("\$tabtmp[$k]=".$tabtmp[$k]."<br />");
                            // Normalement, chaque eleve n'est inscrit qu'une fois dans la classe, mais bon...
                            foreach ($tabtmp[$k]['member'] as $dn) {
                                $cn = ldap_dn2cn($dn);
                                if (! in_array($cn, $tab_eleve_cn)) {
                                    // my_echo("Ajout a \$tab_eleve_cn<br />");
                                    $tab_eleve_cn[] = $cn;
                                }
                            }
                        }
                    }
                } else {
                    // Faire une boucle sur $eleve[$numero]["options"][$j]["code_matiere"] apres avoir identifie le numero... en faisant une recherche sur les member de "cn=Classe_".$prefix."$div"
                    // Ou: remplir un etage de plus de $tab_division[$k]["option"]
                    // $tab_division[$ind_div]["option"][$ind_mat]["eleve"][]
                    // my_echo("<p>Matiere optionnelle pour $mat en $div:<br />");
                    for ($k = 0; $k < count($tab_division[$ind_div]["option"][$ind_mat]["eleve"]); $k ++) {
                        $tabtmp = search_ad($config, $tab_division[$ind_div]["option"][$ind_mat]["eleve"][$k], "employeenumber");

                        if (count($tabtmp) != 0) {
                            if (! in_array($tabtmp[0]['cn'], $tab_eleve_cn)) {
                                // my_echo("Ajout a \$tab_eleve_cn<br />");
                                $tab_eleve_cn[] = $tabtmp[0]['cn'];
                            }
                        }
                    }
                }

                // Creation du groupe
                // Le groupe Cours existe-t-il?
                my_echo("<p>\n");
                $tabtmp = search_ad($config, "Cours_" . $prefix . $mat . "_" . $div, "group");
                if (count($tabtmp) == 0) {
                    // Ou recuperer un nom long du fichier de STS...
                    $description = "$mat / $div";

                    // my_echo("<p>Creation du groupe Cours_".$prefix.$mat."_".$div.": ");
                    my_echo("Création du groupe Cours_" . $prefix . $mat . "_" . $div . ": ");
                    if (create_group($config, $prefix . $mat . "_" . $div, $description, "cours")) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                        $temoin_cours = "PROBLEME";
                        $nb_echecs ++;
                    }
                    my_echo("<br />\n");
                    if ($chrono == 'y') {
                        my_echo("Fin: " . date_et_heure() . "<br />\n");
                    }
                }

                if ($temoin_cours == "") {
                    // Ajout des membres
                    my_echo("Ajout de membres au groupe Cours_" . $prefix . $mat . "_" . $div . ": ");
                    // Ajout des profs
                    for ($n = 0; $n < count($tab_prof_cn); $n ++) {
                        $cn = $tab_prof_cn[$n];
                        if (add_user_group($config, "Cours_" . $prefix . $mat . "_" . $div, $cn)) {
                            my_echo("<b>$cn</b> ");
                        } else {
                            my_echo("<font color='red'>$cn</font> ");
                            $nb_echecs ++;
                        }
                    }

                    // Ajout des eleves
                    for ($n = 0; $n < count($tab_eleve_cn); $n ++) {
                        $cn = $tab_eleve_cn[$n];
                        if (add_user_group($config, "Cours_" . $prefix . $mat . "_" . $div, $cn)) {
                            my_echo("<b>$cn</b> ");
                        } else {
                            my_echo("<font color='red'>$cn</font> ");
                            $nb_echecs ++;
                        }
                    }
                    my_echo(" (<i>" . count($tab_prof_cn) . "+" . count($tab_eleve_cn) . "</i>)\n");
                    my_echo("<br />\n");
                    if ($chrono == 'y') {
                        my_echo("Fin: " . date_et_heure() . "<br />\n");
                    }
                }
                my_echo("</p>\n");
            }
        }
        if ($chrono == 'y') {
            my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
        }

        // Dans le cas de l'import XML eleves, on a $eleve[$numero]["options"][$j]["code_matiere"]

        // Rechercher les groupes
        // $groupes[$i]["code"] -> 3 A1TEC1 ou 3AGL1-1
        // $groupes[$i]["divisions"][$j]["code"] -> 3 A1
        // $groupes[$i]["code_matiere"] -> 070800
        // $groupes[$i]["enseignant"][$m]["id"] -> 38101

        $nom_groupe_a_debugger = "3 ALL2";

        function my_echo_double_sortie($chaine, $balise = "p")
        {
            $debug = "n";
            if ($debug == "y") {
                $retour = "<$balise style='color:red'>" . $chaine . "</$balise>\n";
                echo $retour;
                my_echo($retour);
            }
        }

        my_echo_double_sortie("count(\$groupes)=" . count($groupes));

        for ($i = 0; $i < count($groupes); $i ++) {
            if (isset($groupes[$i]["service"])) {
                for ($p = 0; $p < count($groupes[$i]["service"]); $p ++) {
                    $temoin_grp = "";
                    $grp_mat = "";

                    // my_echo("<p>\$grp=\$groupes[$i][\"code\"]=".$grp."<br />");

                    if (isset($groupes[$i]["service"][$p]["code_matiere"])) {
                        $grp_id_mat = $groupes[$i]["service"][$p]["code_matiere"];
                        // my_echo("\$grp_id_mat=\$groupes[$i][\"code_matiere\"]=".$grp_id_mat."<br />");
                        // Recherche du nom court de matiere
                        for ($n = 0; $n < count($matiere); $n ++) {
                            if ($matiere[$n]["code"] == $grp_id_mat) {
                                $grp_mat = $matiere[$n]["code_gestion"];
                            }
                        }
                    }

                    $grp = $groupes[$i]["code"];
                    if (count($groupes[$i]["service"]) > 1) {
                        if ($grp_mat != "") {
                            $grp = $grp . "_" . $grp_mat;
                        } else {
                            $grp = $grp . "_" . $p;
                        }
                    }
                    $grp = apostrophes_espaces_2_underscore(remplace_accents($grp));

                    // my_echo("\$grp_mat=".$grp_mat."<br />");

                    // Recuperation des profs associes a ce groupe
                    unset($tab_prof_cn);
                    $tab_prof_cn = array();
                    if (isset($groupes[$i]["service"][$p]["enseignant"])) {
                        for ($m = 0; $m < count($groupes[$i]["service"][$p]["enseignant"]); $m ++) {
                            $employeeNumber = "P" . $groupes[$i]["service"][$p]["enseignant"][$m]["id"];
                            $tabtmp = search_ad($config, $employeeNumber, "employeenumber");
                            if (count($tabtmp) != 0) {
                                $cn = $tabtmp[0]['cn'];
                                if (! in_array($cn, $tab_prof_cn)) {
                                    $tab_prof_cn[] = $cn;
                                }
                            }
                        }
                    }

                    if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                        my_echo_double_sortie("\$groupes[$i][\"code\"]=" . $groupes[$i]["code"]);
                    }

                    // Recuperation des eleves associes aux classes de ce groupe
                    unset($tab_eleve_cn);
                    $tab_eleve_cn = array();
                    $chaine_div = "";
                    for ($j = 0; $j < count($groupes[$i]["divisions"]); $j ++) {
                        $div = $groupes[$i]["divisions"][$j]["code"];
                        // $div=ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($div)));
                        $div = apostrophes_espaces_2_underscore(remplace_accents($div));

                        if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                            my_echo_double_sortie("Classe associee " . $div);
                        }

                        // my_echo("\$div=".$div."<br />");

                        // $tab_division[$ind_div]["option"][$k]["code_matiere"]

                        // Dans le cas de l'import XML, on recupere la liste des options suivies par les eleves
                        $ind_div = "";
                        if ($type_fichier_eleves == "xml") {
                            // Identifier $k tel que $tab_division[$k]["nom"]==$div
                            for ($k = 0; $k < count($tab_division); $k ++) {
                                // my_echo("\$tab_division[$k][\"nom\"]=".$tab_division[$k]["nom"]."<br />");
                                // if(ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($tab_division[$k]["nom"])))==$div) {
                                if (apostrophes_espaces_2_underscore(remplace_accents($tab_division[$k]["nom"])) == $div) {
                                    $ind_div = $k;

                                    if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                        my_echo_double_sortie("\$ind_div=" . $ind_div);
                                    }

                                    break;
                                }
                            }
                        }
                        // my_echo("\$ind_div=".$ind_div."<br />");

                        // La matiere est-elle optionnelle dans la classe?
                        $temoin_groupe_apparaissant_dans_Eleves_xml = "non";
                        $temoin_matiere_optionnelle = "non";
                        $ind_mat = "";
                        if (($type_fichier_eleves == "xml") && ($ind_div != "")) {
                            for ($k = 0; $k < count($tab_division[$ind_div]["option"]); $k ++) {

                                if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                    my_echo_double_sortie("\$tab_division[$ind_div][\"option\"][$k][\"code_matiere\"]=" . $tab_division[$ind_div]["option"][$k]["code_matiere"] . " et \$grp_id_mat=$grp_id_mat");
                                }

                                // if(in_array($groupes[$i]["code"], $tab_groups)) {
                                if ((in_array($groupes[$i]["code"], $tab_groups)) || (in_array(apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"])), $tab_groups))) {
                                    // Les inscriptions des eleves ont ete inscrites dans le ElevesSansAdresses.xml
                                    $temoin_groupe_apparaissant_dans_Eleves_xml = "oui";
                                    $temoin_matiere_optionnelle = "oui";
                                    $ind_mat = $k;

                                    if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                        my_echo_double_sortie("Matière optionnelle apparaissant dans ElevesSansAdresses avec \$ind_mat=$ind_mat");
                                    }

                                    break;
                                } elseif ($tab_division[$ind_div]["option"][$k]["code_matiere"] == $grp_id_mat) {
                                    $temoin_matiere_optionnelle = "oui";
                                    $ind_mat = $k;

                                    if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                        my_echo_double_sortie("Matière optionnelle avec \$ind_mat=$ind_mat");
                                    }

                                    break;
                                }
                            }
                        }
                        // my_echo("\$ind_mat=".$ind_mat."<br />");

                        if ($chaine_div == "") {
                            $chaine_div = $div;
                        } else {
                            $chaine_div .= " / " . $div;
                        }

                        if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                            my_echo_double_sortie("\$temoin_matiere_optionnelle=" . $temoin_matiere_optionnelle);
                        }

                        if ($temoin_matiere_optionnelle != "oui") {
                            $tabtmp = grouplistmembers($config, "Classe_" . $prefix . "$div");
                            if (count($tabtmp) != 0) {
                                for ($k = 0; $k < count($tabtmp); $k ++) {
                                    // Normalement, chaque eleve n'est inscrit qu'une fois dans la classe, mais bon...
                                    if (! in_array($tabtmp[$k], $tab_eleve_cn)) {
                                        $tab_eleve_cn[] = $tabtmp[$k];
                                    }
                                }
                            }
                        } else {

                            if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                my_echo_double_sortie("\$temoin_groupe_apparaissant_dans_Eleves_xml=" . $temoin_groupe_apparaissant_dans_Eleves_xml);
                            }

                            // my_echo("<p>Matiere optionnelle pour $grp:<br />");
                            if ($temoin_groupe_apparaissant_dans_Eleves_xml != "oui") {
                                for ($k = 0; $k < count($tab_division[$ind_div]["option"][$ind_mat]["eleve"]); $k ++) {
                                    $tabtmp = search_ad($config, $tab_division[$ind_div]["option"][$ind_mat]["eleve"][$k], "employeenumber");

                                    if (count($tabtmp) != 0) {
                                        if (! in_array($tabtmp[0]['cn'], $tab_eleve_cn)) {
                                            $tab_eleve_cn[] = $tabtmp[0]['cn'];
                                        }
                                    }
                                }
                            } else {
                                for ($k = 0; $k < count($tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"]))]); $k ++) {
                                    $tabtmp = search_ad($config, $tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"]))][$k], "employeenumber");

                                    if (count($tabtmp) != 0) {
                                        if (! in_array($tabtmp[0]['cn'], $tab_eleve_cn)) {
                                            $tab_eleve_cn[] = $tabtmp[0]['cn'];
                                        }
                                    }
                                }
                            }

                            if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                my_echo_double_sortie(print_r($tab_eleve_cn), "pre");
                            }
                        }
                    }

                    // Creation du groupe
                    // Le groupe Cours existe-t-il?
                    my_echo("<p>\n");
                    $tabtmp = search_ad($config, "Cours_" . $prefix . $grp, "group");
                    if (count($tabtmp) == 0) {
                        // Ou recuperer un nom long du fichier de STS...
                        $description = "$grp_mat / $chaine_div";

                        // my_echo("<p>Creation du groupe Cours_".$prefix.$mat."_".$div.": ");
                        my_echo("Création du groupe Cours_" . $prefix . $grp . " : ");
                        if (create_group($config, $prefix . "$grp", $description, "cours")) {
                            my_echo("<font color='green'>SUCCES</font>");
                        } else {
                            my_echo("<font color='red'>ECHEC</font>");
                            $temoin_cours = "PROBLEME";
                            $nb_echecs ++;
                        }
                        my_echo("<br />\n");
                        if ($chrono == 'y') {
                            my_echo("Fin: " . date_et_heure() . "<br />\n");
                        }
                    }

                    if ($temoin_cours == "") {
                        // Ajout des membres
                        my_echo("Ajout de membres au groupe Cours_" . $prefix . "$grp: ");
                        // Ajout des profs
                        for ($n = 0; $n < count($tab_prof_cn); $n ++) {
                            $cn = $tab_prof_cn[$n];
                            if (add_user_group($config, "Cours_" . $prefix . $grp, $cn)) {
                                my_echo("<b>$cn</b> ");
                            } else {
                                my_echo("<font color='red'>$cn</font> ");
                                $nb_echecs ++;
                            }
                        }

                        // Ajout des eleves
                        for ($n = 0; $n < count($tab_eleve_cn); $n ++) {
                            $cn = $tab_eleve_cn[$n];
                            if (add_user_group($config, "Cours_" . $prefix . $grp, $cn)) {
                                my_echo("<b>$cn</b> ");
                            } else {
                                my_echo("<font color='red'>$cn</font> ");
                                $nb_echecs ++;
                            }
                        }
                        my_echo(" (<i>" . count($tab_prof_cn) . "+" . count($tab_eleve_cn) . "</i>)\n");
                        my_echo("<br />\n");
                        if ($chrono == 'y') {
                            my_echo("Fin: " . date_et_heure() . "<br />\n");
                        }
                    }
                    my_echo("</p>\n");
                }
            } else {
                // =============================================================================================
                // Pas de section "<SERVICE " dans ce groupe
                $grp = $groupes[$i]["code"];
                $grp = apostrophes_espaces_2_underscore(remplace_accents($grp));
                $temoin_grp = "";
                $grp_mat = "";

                // Faute de section SERVICE, on ne recupere pas le grp_id_mat
                // On n'a pas de $grp_id_mat faute de section SERVICE donc pas moyen d'identifier la matiere associee au groupe et de chercher si cette matiere a ete reperee comme optionnelle

                if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                    my_echo_double_sortie("\$groupes[$i][\"code\"]=" . $groupes[$i]["code"]);
                }

                // my_echo("<p>\$grp=\$groupes[$i][\"code\"]=".$grp."<br />");

                // Recuperation des profs associes a ce groupe
                // Impossible faute de section SERVICE
                unset($tab_prof_cn);
                $tab_prof_cn = array();

                // Recuperation des eleves associes aux classes de ce groupe
                unset($tab_eleve_cn);
                $tab_eleve_cn = array();
                $chaine_div = "";
                for ($j = 0; $j < count($groupes[$i]["divisions"]); $j ++) {
                    $div = $groupes[$i]["divisions"][$j]["code"];
                    // $div=ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($div)));
                    $div = apostrophes_espaces_2_underscore(remplace_accents($div));

                    // my_echo("\$div=".$div."<br />");

                    // $tab_division[$ind_div]["option"][$k]["code_matiere"]

                    if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                        my_echo_double_sortie("Classe associee " . $div);
                    }

                    // Dans le cas de l'import XML, on recupere la liste des options suivies par les eleves
                    $ind_div = "";
                    if ($type_fichier_eleves == "xml") {
                        // Identifier $k tel que $tab_division[$k]["nom"]==$div
                        for ($k = 0; $k < count($tab_division); $k ++) {
                            // my_echo("\$tab_division[$k][\"nom\"]=".$tab_division[$k]["nom"]."<br />");
                            // if(ereg_replace("'","_",ereg_replace(" ","_",remplace_accents($tab_division[$k]["nom"])))==$div) {
                            if (apostrophes_espaces_2_underscore(remplace_accents($tab_division[$k]["nom"])) == $div) {
                                $ind_div = $k;

                                if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                    my_echo_double_sortie("\$ind_div=" . $ind_div);
                                }

                                break;
                            }
                        }
                    }
                    // my_echo("\$ind_div=".$ind_div."<br />");

                    // La matiere est-elle optionnelle dans la classe?
                    $temoin_groupe_apparaissant_dans_Eleves_xml = "non";
                    $temoin_matiere_optionnelle = "non";
                    $ind_mat = "";

                    // if(in_array($groupes[$i]["code"], $tab_groups)) {
                    if (is_array($tab_groups)) {
                        if ((in_array($groupes[$i]["code"], $tab_groups)) || (in_array(apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"])), $tab_groups))) {
                            // Les inscriptions des eleves ont ete inscrites dans le ElevesSansAdresses.xml
                            $temoin_groupe_apparaissant_dans_Eleves_xml = "oui";
                            $temoin_matiere_optionnelle = "oui";
                            $ind_mat = $k;

                            if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                my_echo_double_sortie("Matiere optionnelle apparaissant dans ElevesSansAdresses avec \$ind_mat=$ind_mat");
                            }
                        }
                    }

                    // my_echo("\$ind_mat=".$ind_mat."<br />");

                    if ($chaine_div == "") {
                        $chaine_div = $div;
                    } else {
                        $chaine_div .= " / " . $div;
                    }

                    if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                        my_echo_double_sortie("\$temoin_matiere_optionnelle=" . $temoin_matiere_optionnelle);
                    }

                    if ($temoin_matiere_optionnelle != "oui") {
                        $tabtmp = search_ad($config, $prefix . "$div", "classe");
                        if (count($tabtmp) != 0) {
                            for ($k = 0; $k < count($tabtmp); $k ++) {
                                // Normalement, chaque eleve n'est inscrit qu'une fois dans la classe, mais bon...
                                if (! in_array($tabtmp[$k]['cn'], $tab_eleve_cn)) {
                                    $tab_eleve_cn[] = $tabtmp[$k]['cn'];
                                }
                            }
                        }
                    } else {
                        //
                        if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                            my_echo_double_sortie("\$temoin_groupe_apparaissant_dans_Eleves_xml=" . $temoin_groupe_apparaissant_dans_Eleves_xml);
                        }

                        // my_echo("<p>Matiere optionnelle pour $grp:<br />");

                        if ($temoin_groupe_apparaissant_dans_Eleves_xml != "oui") {
                            for ($k = 0; $k < count($tab_division[$ind_div]["option"][$ind_mat]["eleve"]); $k ++) {
                                // my_echo("Recherche: get_tab_attribut(\"groups\", \"cn=Classe_".$prefix."$div\", $attribut)<br />");
                                $tabtmp = search_ad($config, $tab_division[$ind_div]["option"][$ind_mat]["eleve"][$k], "employeenumber");

                                if (count($tabtmp) != 0) {
                                    if (! in_array($tabtmp[0]['cn'], $tab_eleve_cn)) {
                                        // my_echo("Ajout a \$tab_eleve_cn<br />");
                                        $tab_eleve_cn[] = $tabtmp[0]['cn'];
                                    }
                                }
                            }
                        } else {

                            if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                my_echo_double_sortie("count(\$tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents(\$groupes[$i]['code']))])=count(\$tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents(" . $groupes[$i]['code'] . "))])=count(\$tab_groups_member[" . apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"])) . "])=" . count($tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"]))]));
                            }

                            for ($k = 0; $k < count($tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"]))]); $k ++) {
                                // Recuperer l'cn correspondant a l'elenoet/employeeNumber stocke
                                // $tabtmp=search_people("employeeNumber=".$tab_groups_member[$groupes[$i]["code"]][$k]);

                                if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                                    my_echo_double_sortie("\$tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents(\$groupes[$i]['code']))][$k]=\$tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents(" . $groupes[$i]['code'] . "))][$k]=\$tab_groups_member[" . apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]['code'])) . "][$k]=" . $tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]['code']))][$k]);
                                }
                                $tabtmp = search_ad($config, $tab_groups_member[apostrophes_espaces_2_underscore(remplace_accents($groupes[$i]["code"]))][$k], "employeenumber");

                                if (count($tabtmp) != 0) {
                                    if (! in_array($tabtmp[0]['cn'], $tab_eleve_cn)) {
                                        // my_echo("Ajout a \$tab_eleve_cn<br />");
                                        $tab_eleve_cn[] = $tabtmp[0]['cn'];
                                    }
                                }
                            }
                        }
                        //
                        if ($groupes[$i]["code"] == $nom_groupe_a_debugger) {
                            my_echo_double_sortie(print_r($tab_eleve_cn), "pre");
                        }
                    }
                }

                // Creation du groupe
                // Le groupe Cours existe-t-il?
                my_echo("<p>\n");
                $tabtmp = search_ad($config, "cn=Cours_" . $prefix . "$grp", "group");
                if (count($tabtmp) == 0) {
                    // Ou recuperer un nom long du fichier de STS...
                    $description = "$grp_mat / $chaine_div";

                    // my_echo("<p>Creation du groupe Cours_".$prefix.$mat."_".$div.": ");
                    my_echo("Création du groupe Cours_" . $prefix . "$grp: ");
                    if (create_group($config, $prefix . "$grp", $description, "cours")) {
                        my_echo("<font color='green'>SUCCES</font>");
                    } else {
                        my_echo("<font color='red'>ECHEC</font>");
                        $temoin_cours = "PROBLEME";
                        $nb_echecs ++;
                    }
                    my_echo("<br />\n");
                    if ($chrono == 'y') {
                        my_echo("Fin: " . date_et_heure() . "<br />\n");
                    }
                }

                my_echo("<p>\n");

                if ($temoin_cours == "") {
                    // Ajout des membres
                    my_echo("Ajout de membres au groupe Cours_" . $prefix . "$grp: ");
                    // Ajout des profs
                    for ($n = 0; $n < count($tab_prof_cn); $n ++) {
                        $cn = $tab_prof_cn[$n];
                        if (add_user_group($config, "Cours_" . $prefix . $grp, $cn)) {
                            my_echo("<b>$cn</b> ");
                        } else {
                            my_echo("<font color='red'>$cn</font> ");
                            $nb_echecs ++;
                        }
                    }

                    // Ajout des eleves
                    for ($n = 0; $n < count($tab_eleve_cn); $n ++) {
                        $cn = $tab_eleve_cn[$n];
                        if (add_user_group($config, "Cours_" . $prefix . $grp, $cn)) {
                            my_echo("<b>$cn</b> ");
                        } else {
                            my_echo("<font color='red'>$cn</font> ");
                            $nb_echecs ++;
                        }
                    }
                    my_echo(" (<i>" . count($tab_prof_cn) . "+" . count($tab_eleve_cn) . "</i>)\n");
                    my_echo("<br />\n");
                    if ($chrono == 'y') {
                        my_echo("Fin: " . date_et_heure() . "<br />\n");
                    }
                }
                my_echo("</p>\n");
            }
        }
        if ($chrono == 'y') {
            my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
        }
    } else {
        my_echo("</h3>\n");
        my_echo("<blockquote>\n");
        my_echo("<p>Création des Cours non demandée.</p>\n");
    }

    my_echo("</blockquote>\n");

    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    my_echo("<a name='creer_groupe_pp'></a>\n");
    // my_echo("<h2>Creation des groupes Cours</h2>\n");
    // my_echo("<h3>Creation des groupes Cours</h3>\n");
    my_echo("<h3>Création d'un groupe Professeurs Principaux");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }

    if ($alimenter_groupe_pp == 'y') {
        my_echo("</h3>\n");

        // Prof principal
        unset($tab_pp);
        $tab_pp = array();
        for ($m = 0; $m < count($prof); $m ++) {
            if (isset($prof[$m]["prof_princ"])) {
                $employeeNumber = "P" . $prof[$m]["id"];
                $tabtmp = search_ad($config, $employeeNumber, "employeenumber");
                if (count($tabtmp) != 0) {
                    $cn = $tabtmp[0]['cn'];
                    if (! in_array($cn, $tab_pp)) {
                        $tab_pp[] = $cn;
                    }
                }
            }
        }
        sort($tab_pp);

        // Vider et re-alimenter le groupe
        // Initialisation des membres du groupe Professeurs_Principaux
        $tab_mem_pp = array();

        $tabtmp = search_ad($config, $nom_groupe_pp, "group");
        if (count($tabtmp) == 0) {
            $description = "Professeurs Principaux";

            my_echo("<p>Création du groupe $nom_groupe_pp: ");
            if (groupadd($config, $nom_groupe_pp, $description, $config['groups_rdn'])) {
                my_echo("<font color='green'>SUCCES</font>");
            } else {
                my_echo("<font color='red'>ECHEC</font>");
                // $temoin_cours="PROBLEME";
                $nb_echecs ++;
            }
            my_echo("<br />\n");
            if ($chrono == 'y') {
                my_echo("Fin: " . date_et_heure() . "<br />\n");
            }
        } else {
            // Liste des comptes presents dans le Groupe_Professeurs_Principaux.
            $tab_mem_pp = grouplistmembers($config, $nom_groupe_pp);
        }
        /*
         * Inutile car c'est géré lors de la création des classes
         * if (count($tab_pp) == 0) {
         * my_echo("Aucun professeur principal n'a été trouvé<br />\n");
         * } else {
         * // Ajout de membres au groupe d'apres $tab_pp
         * my_echo("Ajout de membres au groupe $nom_groupe_pp: ");
         *
         * for ($n = 0; $n < count($tab_pp); $n ++) {
         * $cn = $tab_pp[$n];
         * if (in_array($cn, $tab_mem_pp)) {
         * // Rien a faire, deja present
         * my_echo("$cn ");
         * } else {
         *
         * $attribut = array(
         * "cn"
         * );
         * $tabtmp = get_tab_attribut("groups", "(&(cn=$nom_groupe_pp)(member=CN=" . $cn . "," . $dn["people"] . "))", $attribut);
         * if (count($tabtmp) == 0) {
         * unset($attribut);
         * $attribut = array();
         * $attribut["member"] = $cn;
         * if (modify_attribut("cn=$nom_groupe_pp", "groups", $attribut, "add")) {
         * my_echo("<b>$cn</b> ");
         * } else {
         * my_echo("<font color='red'>$cn</font> ");
         * $nb_echecs ++;
         * }
         * } else {
         * my_echo("$cn ");
         * }
         * }
         * }
         * my_echo(" (<i>" . count($tab_pp) . "</i>)\n");
         *
         * $temoin_membres_pp_a_virer = "n";
         * for ($n = 0; $n < count($tab_mem_pp); $n ++) {
         * $cn = $tab_mem_pp[$n];
         * if (! in_array($cn, $tab_pp)) {
         * if ($temoin_membres_pp_a_virer == "n") {
         * my_echo("<br />\n");
         * my_echo("Sortie du groupe $nom_groupe_pp de: ");
         * }
         *
         * unset($attribut);
         * $attribut = array();
         * $attribut["member"] = $cn;
         * if (modify_attribut("cn=$nom_groupe_pp", "groups", $attribut, "del")) {
         * my_echo("$cn ");
         * } else {
         * my_echo("<font color='red'>$cn</font> ");
         * $nb_echecs ++;
         * }
         * }
         * }
         * my_echo("<br />\n");
         * }
         * if ($chrono == 'y') {
         * my_echo("Fin: " . date_et_heure() . "<br />\n");
         * }
         */
    } else {
        my_echo("</h3>\n");
        my_echo("<blockquote>\n");
        my_echo("<p>Création/alimentation du groupe Profs Principaux non demandée.</p>\n");
    }
    my_echo("</blockquote>\n");

    my_echo("<p>Retour au <a href='#menu'>menu</a>.</p>\n");

    my_echo("<a name='fin'></a>\n");
    // my_echo("<h3>Rapport final de creation</h3>");
    my_echo("<h3>Rapport final de création");
    if ($chrono == 'y') {
        my_echo(" (<i>" . date_et_heure() . "</i>)");
    }
    my_echo("</h3>\n");
    my_echo("<blockquote>\n");
    my_echo("<p>Terminé!</p>\n");
    my_echo("<script type='text/javascript'>
	document.getElementById('id_fin').style.display='';
</script>");

    $chaine = "";
    if ($nouveaux_comptes == 0) {
        $chaine .= "<p>Aucun nouveau compte n'a été créé.</p>\n";
        // my_echo("<p>Aucun nouveau compte n'a ete cree.</p>\n");
    } elseif ($nouveaux_comptes == 1) {
        // my_echo("<p>$nouveaux_comptes nouveau compte a ete cree: $tab_nouveaux_comptes[0]</p>\n");
        $chaine .= "<p>$nouveaux_comptes nouveau compte a été créé: $tab_nouveaux_comptes[0]</p>\n";
    } else {
        /*
         * my_echo("<p>$nouveaux_comptes nouveaux comptes ont ete crees: \n");
         * my_echo($tab_nouveaux_comptes[0]);
         * for($i=1;$i<count($tab_nouveaux_comptes);$i++) {
         * my_echo(", $tab_nouveaux_comptes[$i]");
         * }
         * my_echo("</p>\n");
         */
        $chaine .= "<p>$nouveaux_comptes nouveaux comptes ont été créés: \n";
        $chaine .= $tab_nouveaux_comptes[0];
        for ($i = 1; $i < count($tab_nouveaux_comptes); $i ++) {
            $chaine .= ", $tab_nouveaux_comptes[$i]";
        }
        $chaine .= "</p>\n";
    }

    if ($rafraichir_classes == "y") {
        if ($nouveaux_comptes == 0) {
            $chaine .= "<p>On ne lance pas de rafraichissement des classes.</p>\n";
        } else {
            $chaine .= "<p>Lancement du rafraichissement des classes dans quelques instants.</p>\n";
        }
    }

    if ($comptes_avec_employeeNumber_mis_a_jour == 0) {
        // my_echo("<p>Aucun compte existant sans employeeNumber n'a ete recupere/corrige.</p>\n");
        $chaine .= "<p>Aucun compte existant sans employeeNumber n'a été récupéré/corrigé.</p>\n";
    } elseif ($comptes_avec_employeeNumber_mis_a_jour == 1) {
        // my_echo("<p>$comptes_avec_employeeNumber_mis_a_jour compte existant sans employeeNumber a ete recupere/corrige (<i>son employeeNumber est maintenant renseigne</i>): $tab_comptes_avec_employeeNumber_mis_a_jour[0]</p>\n");
        $chaine .= "<p>$comptes_avec_employeeNumber_mis_a_jour compte existant sans employeeNumber a été récupéré/corrigé (<i>son employeeNumber est maintenant renseigné</i>): $tab_comptes_avec_employeeNumber_mis_a_jour[0]</p>\n";
    } else {
        /*
         * my_echo("<p>$comptes_avec_employeeNumber_mis_a_jour comptes existants sans employeeNumber ont ete recuperes/corriges (<i>leur employeeNumber est maintenant renseigne</i>): \n");
         * my_echo("$tab_comptes_avec_employeeNumber_mis_a_jour[0]");
         * for($i=1;$i<count($tab_comptes_avec_employeeNumber_mis_a_jour);$i++) {my_echo(", $tab_comptes_avec_employeeNumber_mis_a_jour[$i]");}
         * my_echo("</p>\n");
         */
        $chaine .= "<p>$comptes_avec_employeeNumber_mis_a_jour comptes existants sans employeeNumber ont été récupérés/corrigés (<i>leur employeeNumber est maintenant renseigné</i>): \n";
        $chaine .= "$tab_comptes_avec_employeeNumber_mis_a_jour[0]";
        for ($i = 1; $i < count($tab_comptes_avec_employeeNumber_mis_a_jour); $i ++) {
            $chaine .= ", $tab_comptes_avec_employeeNumber_mis_a_jour[$i]";
        }
        $chaine .= "</p>\n";
    }

    my_echo($infos_corrections_gecos);

    $chaine .= $infos_corrections_gecos;

    if (count($tab_eleve_autre_etab) > 0) {
        $tmp_txt = "Un ou des élèves n'ont pas été enregistrés dans l'annuaire parce qu'importés d'un autre établissement avec un identifiant non encore mis à jour.\n";
        // my_echo("<p>".$tmp_txt."</p><ul>");
        // $chaine.=$tmp_txt;
        $chaine .= "<p style='color:red'>" . $tmp_txt . "</p><ul>";
        for ($loop = 0; $loop < count($tab_eleve_autre_etab); $loop ++) {
            $tmp_tab = explode("|", $tab_eleve_autre_etab[$loop]);
            if ($servertype == "SE3") {
                $tmp_txt = "<a href='../annu/add_user.php?nom=" . remplace_accents($tmp_tab[0]) . "&amp;prenom=" . remplace_accents($tmp_tab[1]) . "&amp;sexe=" . ($tmp_tab[2] != "1" ? "F" : "M") . "&amp;naissance=" . formate_date_aaaammjj($tmp_tab[3]) . "' target='_blank'>" . $tmp_tab[0] . " " . $tmp_tab[1] . " (" . ($tmp_tab[2] != "1" ? "fille" : "gar&cedil;on") . ") n&eacute;" . ($tmp_tab[2] != "1" ? "e" : "") . " le " . $tmp_tab[3] . "</a>";
            } else {
                $tmp_txt = $tmp_tab[0] . " " . $tmp_tab[1] . " (" . ($tmp_tab[2] != "1" ? "fille" : "garçon") . ") n°" . ($tmp_tab[2] != "1" ? "e" : "") . " le " . $tmp_tab[3];
            }
            // my_echo("<li>".$tmp_txt."</li>");
            // $chaine.=$tmp_txt."\n";
            $chaine .= "<li>" . $tmp_txt . "</li>";
        }
        $tmp_txt = "Vous devrez les créer à la main en attendant que Sconet/Sts soit à jour (<em>généralement fin septembre</em>).";
        // my_echo("</ul><p>$tmp_txt</p>");
        // $chaine.=$tmp_txt;
        $chaine .= "</ul><p>$tmp_txt</p>";
    }

    if ($nb_echecs == 0) {
        // my_echo("<p>Aucune operation tentee n'a echoue.</p>\n");
        $chaine .= "<p>Aucune opération tentée n'a échoué.</p>\n";
    } elseif ($nb_echecs == 1) {
        // my_echo("<p style='color:red;'>$nb_echecs operation tentee a echoue.</p>\n");
        $chaine .= "<p style='color:red;'>$nb_echecs opération tentée a échoué.</p>\n";
    } else {
        // my_echo("<p style='color:red;'>$nb_echecs operations tentees ont echoue.</p>\n");
        $chaine .= "<p style='color:red;'>$nb_echecs opérations tentées ont échoué.</p>\n";
    }
    my_echo($chaine);

    /*
     * // Envoi par mail de $chaine et $echo_http_file
     *
     * // Recuperer les adresses,... dans le /etc/ssmtp/ssmtp.conf
     * unset($tabssmtp);
     * my_echo("<p>Avant lireSSMTP();</p>");
     * $tabssmtp=lireSSMTP();
     * my_echo("<p>Apres lireSSMTP();</p>");
     * my_echo("<p>\$tabssmtp[\"root\"]=".$tabssmtp["root"]."</p>");
     * // Controler les champs affectes...
     * if(isset($tabssmtp["root"])) {
     * $adressedestination=$tabssmtp["root"];
     * $sujet="[$domain] Rapport de ";
     * if($simulation=="y") {$sujet.="simulation de ";}
     * $sujet.="creation de comptes";
     * $message="Import du $debut_import\n";
     * $message.="$chaine\n";
     * $message.="\n";
     * $message.="Vous pouvez consulter le rapport detaille a l'adresse $echo_http_file\n";
     * $entete="From: ".$tabssmtp["root"];
     * my_echo("<p>Avant mail.</p>");
     * mail("$adressedestination", "$sujet", "$message", "$entete") or my_echo("<p style='color:red;'><b>ERREUR</b> lors de l'envoi du rapport par mail.</p>\n");
     * my_echo("<p>Apres mail.</p>");
     * }
     * else{
     * my_echo("<p>\$tabssmtp[\"root\"] doit etre vide.</p>");
     * my_echo("<p style='color:red;'><b>MAIL:</b> La configuration mail ne permet pas d'expedier le rapport.<br />Consultez/renseignez le menu Informations systeme/Actions sur le serveur/Configurer l'expedition des mails.</p>\n");
     * }
     */

    // my_echo("<p>Avant maj params.</p>");

    // Renseignement du temoin de mise a jour terminee.
    set_param($config, "imprt_cmpts_en_cours", "n");
    // my_echo("<p>Apres maj params.</p>");

    if ($chrono == 'y') {
        my_echo("<p>Fin de l'opération: " . date_et_heure() . "</p>\n");
    }

    my_echo("<p><a href='" . $www_import . "'>Retour</a>.</p>\n");

    my_echo("</blockquote>\n");
    my_echo("<script type='text/javascript'>
	compte_a_rebours='n';
</script>\n");
    // my_echo("</body>\n</html>\n");

    // }

    // Dans la version PHP4-CLI, envoyer le rapport par mail.
    // Envoyer le contenu de la page aussi?

    // Peut-etre forcer une sauvegarde de l'annuaire avant de proceder a une operation qui n'est pas une simulation.
    // Ou placer le fichier de sauvegarde?
    // Probleme de l'encombrement a terme.
    // }

    // SUPPRIMER LES FICHIERS CSV/XML en fin d'import.

    if (file_exists($eleves_file)) {
        unlink($eleves_file);
    }

    if (file_exists($sts_xml_file)) {
        unlink($sts_xml_file);
    }

    if (file_exists("$dossier_tmp_import_comptes/import_comptes.sh")) {
        unlink("$dossier_tmp_import_comptes/import_comptes.sh");
    }

    if (file_exists("/tmp/debug_se3lcs.txt")) {
        // Il faut pouvoir ecrire dans le fichier depuis /var/www/se3/annu/import_sconet.php sans sudo... donc www-se3 doit etre proprio ou avoir les droits...
        exec("chown $user_web /tmp/debug_se3lcs.txt");
    }

    // Lien pour la recuperation du mailing
    if (count($listing, COUNT_RECURSIVE) > 1) {
        $serial_listing = rawurlencode(serialize($listing));

        my_echo("<form id='postlisting' action='../annu/listing.php' method='post' style='display:none;'>");
        my_echo("<input type='hidden' name='hiddeninput' value='$serial_listing' />");
        my_echo("</form><p>");

        $lien = "<a href=\"#\" onclick=\"document.getElementById('postlisting').submit(); return false;\">T&#233;l&#233;charger le listing des utilisateurs import&#233;s...</a>";

        my_echo("<table><tr><td><img src='../elements/images/pdffile.png'></td><td>");
        my_echo($lien);
        my_echo("<br /><span style='color:red;'>Attention, les donn&#233;es ne seront pas conserv&#233;es en quittant cette page ! Enregistrez le fichier PDF...</span></td></tr></table></p>");
    }

    if ($rafraichir_classes == "y") {
        if ($nouveaux_comptes > 0) {
            my_echo("<h2>Lancement effectif du rafraichissement des classes...</h2>\n<p>\n");
            exec("/bin/bash " . $pathscripts . "/se3_creer_tous_les_dossiers_de_classes.sh", $retour);
            for ($s = 0; $s < count($retour); $s ++) {
                // my_echo(" \$retour[$s]=$retour[$s]<br />\n");
                my_echo($retour[$s] . "<br />\n");
            }
            my_echo("<br />Fin du rafraichissement des classes.</p>\n");
        }
    }

    // Envoi par mail de $chaine et $echo_http_file
    // Controler les champs affectes...
    if (isset($config["admin_mail"])) {
        $adressedestination = $config["admin_mail"];
        $sujet = $config['domain'] . " Rapport de ";
        if ($simulation == "y") {
            $sujet .= "simulation de ";
        }
        $sujet .= "création de comptes";
        $message = "Import du $debut_import\n";
        $message .= "$chaine\n";
        $message .= "\n";

        if ($rafraichir_classes == "y") {
            if ($nouveaux_comptes > 0) {
                $message .= "Rafraichissement des classes lancé/effectué.\n";
            } else {
                $message .= "Pas de nouveau compte, donc pas de rafraichissement des classes lancé.\n";
            }
        } else {
            $message .= "Pas de rafraichissement des classes demandé.\n";
        }
        $message .= "\n";

        $message .= "Vous pouvez consulter le rapport détaillé à l'adresse $echo_http_file\n";
        $entete = "From: " . $config["admin_mail"];
        mail("$adressedestination", "$sujet", "$message", "$entete") or my_echo("<p style='color:red;'><b>ERREUR</b> lors de l'envoi du rapport par mail.</p>\n");
    } else
        my_echo("<p style='color:red;'><b>MAIL:</b> La configuration mail ne permet pas d'expédier le rapport.<br />Consultez/renseignez le menu Informations système/Actions sur le serveur/Configurer l'expédition des mails.</p>\n");

    my_echo("</body>\n</html>\n");
    return true;
}

?>
