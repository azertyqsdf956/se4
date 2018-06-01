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
        $config['dn']['admin'] = $config['ldap_admin_name'] . "," . $config['ldap_base_dn'];
        $config['dn']['people'] = $config['people_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['groups'] = $config['groups_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['rights'] = $config['rights_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['printers'] = $config['printers_rdn'] . "," . $config['ldap_base_dn'];
        $config['dn']['trash'] = $config['trash_rdn'] . "," . $config['ldap_base_dn'];
    } else {
        $conf_file = "/etc/sambaedu/sambaedu.conf.d/$module.conf";
        $config = parse_ini_file($conf_file);
    }
    return ($config);
}

/*
 * fonction pour récupérer la conf de se3
 * Obsolète, présente pour assurer la transition
 * @Parametres : aucun
 * @return array["parametre"]
 */
function get_config_se3()
{
    // Paramètres de la base de données
    $dbhost = "127.0.0.1";
    $dbname = "se4";
    $dbuser = "root";
    $dbpass = "philou";
    
    $srv_id = 1;
    
    // Paramètres fixes
    
    $secook = 0;
    $Pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $Pool .= "abcdefghijklmnopqrstuvwxyz";
    $Pool .= "1234567890";
    $SessLen = 20;
    // Model caracteres speciaux pour les mots de passe
    $char_spec = "&_#@£%§:!?*$";
    
    $ldap_login_attr = "cn";
    
    // Récupération des paramètres depuis la base de donnée
    
    $authlink = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die("Impossible de se connecter à la base $dbname.");
    @mysqli_set_charset('utf8');
    $result = mysqli_query($authlink, "SELECT * from params where srv_id=0 OR srv_id=$srv_id") or die(mysqli_error($authlink));
    ;
    if ($result) {
        while ($r = mysqli_fetch_array($result))
            $config[$r["name"]] = $r["value"];
        return ($config);
    } else {
        return FALSE;
    }
    ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
}

/*
 * fonction pour récupérer la conf dans un tableau persistant 120s
 * @Parametres : [$force = true] pour forçer la lecture
 * @return : $config = tableau des parametres
 */
function get_config($force = false)
{
    while (apc_fetch('config_lock')) {
        sleep(1);
    }
    if (($force) || ! ($config = apc_fetch('config'))) {
        apc_add('config_lock', 1, 60);
        
        unset($config);
        $config = get_config_se4('all');
        if (! $config) {
            $config = get_config_se3();
        }
        
        apc_add('config', $config, 120);
        apc_delete('config_lock');
        if (! $config) {
            die("Erreur de lecture de la configuration se4 ou se3");
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
        if (! ("$key" == "dn")) {
            $content .= $key . " = \"" . $value . "\"\n";
        }
    }
    // write it into file
    if ($module == "sambaedu") {
        $conf_file = "/etc/sambaedu/sambaedu.conf";
    } else {
        $conf_file = "/etc/sambaedu/sambaedu.conf.d/$module.conf";
    }
    apc_add('config_lock', 1, 60);
    if (! $handle = fopen($conf_file, "w")) {
        apc_delete('config_lock');
        die("Erreur d'ecriture de la configuration se4 : $module $param $value");
    }
    $res = fwrite($handle, $content);
    fclose($handle);
    apc_delete('config_lock');
    if (! $res)
        die("Erreur d'ecriture de la configuration se4 : $module $param $value");
    
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
function init_config($nom, $valeur, $module = "sambaedu")
{
    if (! isset($config[$nom])) {
        set_config($nom, $valeur, $module);
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
$config = get_config();
// if ($config["version"] == "se3") {
// compatibilité avec se3
foreach ($config as $key => $value) {
    $$key = $value;
}
$adminDn = "$ldap_admin_name,$ldap_base_dn";

// Declaration des «branches» de l'annuaire LCS/SE3 dans un tableau
$dn = array();
$dn['admin'] = $config['ldap_admin_name'] . "," . $config['ldap_base_dn'];
$dn["people"] = "$people_rdn,$ldap_base_dn";
$dn["groups"] = "$groups_rdn,$ldap_base_dn";
$dn["rights"] = "$rights_rdn,$ldap_base_dn";
$dn["parcs"] = "$parcs_rdn,$ldap_base_dn";
$dn["computers"] = "$computers_rdn,$ldap_base_dn";
$dn["printers"] = "$printers_rdn,$ldap_base_dn";
$dn["trash"] = "$trash_rdn,$ldap_base_dn";
// }

?>
