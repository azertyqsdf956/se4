<?php

/**
 * manipulation des parametres de conf

 * @Projet LCS / SambaEdu

 * @Auteurs Equipe SambaEdu

 * @Note Ce fichier de fonction doit être appelé par un include dans entete.inc.php
 * @Note Ce fichier est complete a l'installation

 * @Licence Distribué sous la licence GPL
 */

/**
 *
 * file: config.php
 *
 * @Repertoire: includes/
 */
function isauth()
{
    /*
     * Teste si une authentification est faite
     * - Si non, renvoie ""
     * - Si oui, renvoie l'uid de la personne
     */
    $login = "";
    session_name("Sambaedu");
    @session_start();
    $login = (isset($_SESSION['login']) ? $_SESSION['login'] : "");
    return $login;
}

/*
 * fonction pour récupérer la conf de se4 ou des modules de façon recursive dans /etc/sambaedu/
 * @Parametres : "nom du module", "sambaedu" ou "all"
 * @return array["parametre"]
 */
function get_config_se4($module = "sambaedu")
{
    $config = array();
    if ($module == "all") {
        $config = get_config_se4("sambaedu");
        if ($handle = opendir('/etc/sambaedu/sambaedu.conf.d')) {
            unset($module);
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $module = preg_replace("/\.conf/", "", $entry);
                    if (isset($module)) {
                        $config = array_merge($config, get_config_se4($module));
                    }
                }
            }
            closedir($handle);
        }
        return ($config);
    } elseif ($module == "sambaedu") {
        $conf_file = "/etc/sambaedu/sambaedu.conf";
        $config = parse_ini_file($conf_file);
        $config['dn']['admin'] = "cn=" . $config['ldap_admin_name'] . "," . $config['admin_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['people'] = $config['people_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['groups'] = $config['groups_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['rights'] = $config['rights_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['equipements'] = $config['equipements_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['delegations'] = $config['delegations_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['trash'] = $config['trash_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['parcs'] = $config['parcs_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['computers'] = $config['computers_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['autres'] = $config['other_groups_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['projets'] = $config['projets_rdn'] . "," . $config['ldap_base_dn'];
    } else {
        $conf_file = "/etc/sambaedu/sambaedu.conf.d/$module.conf";
        $config = parse_ini_file($conf_file);
    }
    $config['login'] = isauth();
    return ($config);
}

/*
 * fonction pour récupérer la conf dans un tableau persistant 120s
 * @Parametres : [$force = true] pour forçer la lecture
 * @return : $config = tableau des parametres
 */
function get_config($force = false)
{
//    while (apc_fetch('config_lock')) {
//        sleep(1);
//    }
   if (($force) || ! ($config = apc_fetch('config'))) {
        apc_add('config_lock', 1, 60);

        unset($config);
        $config = get_config_se4('all');

        apc_add('config', $config, 120);
        apc_delete('config_lock');
        if (! $config) {
            die("Erreur de lecture de la configuration se4");
        }
    }
    return ($config);
}

/*
 * fonction pour executer une action système en cas de changement d'un parametre de conf
 * exécute un sudo script shell
 * @Parametres : parametre
 * @Parametres :
 * @Parametres : module ( defaut = "base" )
 * @return : $ret
 */
function set_config_action($param)
{
    exec("sudo -c '/usr/share/sambaedu/scripts/config_action.sh $param'", $ret);
    return ($ret);
}

/*
 * fonction pour écrire la conf dans les fichiers de conf
 * retourne le tableau $config et met à jour le cache
 * @Parametres : parametre a fixer
 * @Parametres : valeur
 * @Parametres : module ( defaut = "sambaedu" )
 * @return : $config ou false
 */
function set_config($param, $value = "", $module = "sambaedu")
{
    while (apc_fetch('config_lock')) {
        sleep(1);
    }

    unset($config);
    $config = get_config_se4($module);
    if ($value == "") {
        if (isset($config['param']))
            unset($config['param']);
    } else {
        $config[$param] = $value;
    }
    $content = "";
    foreach ($config as $key => $value) {
        if (! (("$key" == "dn") || ("$key" == "login"))) {
            $content .= $key . " = \"" . $value . "\"\n";
        }
    }
    // write it into file
    if ($module == "sambaedu") {
        $conf_file = "/etc/sambaedu/sambaedu.conf";
        $conf_file_tmp = "/etc/sambaedu/sambaedu.conf.tmp";
    } else {
        $conf_file = "/etc/sambaedu/sambaedu.conf.d/$module.conf";
        $conf_file_tmp = "/etc/sambaedu/sambaedu.conf.d/$module.conf.tmp";
    }
    apc_add('config_lock', 1, 60);
    //on teste l'ecriture dans le fichier temporaire
    if (! $handle1 = fopen($conf_file_tmp, "w")) {
        apc_delete('config_lock');
        die("Erreur d'ecriture de la configuration se4 : $module $param $value");
    }
    $res_test = fwrite($handle1, $content);
    fclose($handle1);
        if (! $res_test) {
            apc_delete('config_lock');
            die("Erreur d'ecriture de la configuration se4 : $module $param $value");
        }

        else {
            //test ecriture reussi
            if (! $handle = fopen($conf_file, "w")) {
                apc_delete('config_lock');
                die("Erreur d'ecriture de la configuration se4 : $module $param $value");
            }
            $res = fwrite($handle, $content);
            fclose($handle);
            @unlink($conf_file_tmp);
            apc_delete('config_lock');
            if (! $res)
                die("Erreur d'ecriture de la configuration se4 : $module $param $value");
        }

    return (get_config(true));
}

/**
 * initilise la valeur d'un parametre si il n'existe pas dans la conf
 *
 * @Parametres : parametre a initaliser
 * @Parametres : valeur
 * @Parametres : module ( defaut = "sambaedu" )
 * @return : array() $config
 *
 *
 */
function init_param(&$config, $nom, $valeur, $module = "sambaedu")
{
    if (! isset($config[$nom])) {
        set_config($nom, $valeur, $module);
    }
}

function set_param(&$config, $nom, $valeur, $module = "sambaedu")
{
    set_config($nom, $valeur, $module);
    return $valeur;
}

/**
 *
 * @param unknown $config
 * @param unknown $nom
 * @return unknown|string
 */
function get_param($config, $nom)
{
    if (isset($config[$nom])) {
        return $config[$nom];
    } else {
        return "";
    }
}

$urlauth = "/auth.php";

// Gettext

// chdir($path_to_wwwse3);
// putenv("LANG=$lang");
// putenv("LANGUAGE=$lang");
setlocale(LC_ALL, "C");
bindtextdomain("messages", "./locale");
textdomain("messages");

// Paramètres LDAP
$config = get_config(true);
?>
