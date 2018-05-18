<?php

/**
 * Fonctions LDAP
 
 * @Version $Id$
 
 * @Projet LCS / SambaEdu
 
 * @Auteurs Equipe Tice academie de Caen
 * @Auteurs jLCF jean-luc.chretien@tice.ac-caen.fr
 
 * @Note: Ce fichier de fonction doit etre appele par un include
 
 * @Licence Distribue sous la licence GPL
 */
/**
 *
 * file: ldap.inc.php
 *
 * @Repertoire: includes/
 */
require_once ("lang.inc.php");
bindtextdomain('sambaedu-core', "/var/www/sambaedu/locale");
textdomain('sambaedu-core');

// pour utiliser bind_ad_gssapi
include_once "functions.inc.php";

// Pour activer/desactiver la modification du givenName (Prenom) lors de la modification dans annu/mod_user_entry.php
$corriger_givenname_si_diff = "n";

// fonctions validées se4
function cmp_fullname($a, $b) {
    
    /**
    
    * Fonctions de comparaison utilisees dans la fonction usort, pour trier le fullname
    
    * @Parametres $a - La premiere entree 	$b - La deuxieme entree a comparer
    
    * @Return < 0 - Si $a est plus petit a $b  > 0 - Si $a est plus grand que $b
    */
    return strcmp($a["fullname"], $b["fullname"]);
}

function cmp_nom($a, $b) {
    
    
    /**
    
    * Fonctions de comparaison utilisees dans la fonction usort, pour trier le name
    
    * @Parametres $a - La premiere entree 	$b - La deuxieme entree a comparer
    
    * @Return < 0 - Si $a est plus petit a $b  > 0 - Si $a est plus grand que $b
    
    */
    return strcmp($a["nom"], $b["nom"]);
}

function cmp_cn($a, $b) {
    
    /**
    
    * Fonctions de comparaison utilisees dans la fonction usort, pour trier le cn (common name)
    
    * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
    
    * @Return  < 0 - Si $a est plus petit a $b   > 0 - Si $a est plus grand que $b
    
    */
    return strcmp($a["cn"], $b["cn"]);
}

function cmp_group($a, $b) {
    
    /**
    
    * Fonctions de comparaison utilisees dans la fonction usort, pour trier les groupes
    
    * @Parametres  $a - La premiere entree 	$b - La deuxieme entree a comparer
    * @Return 	< 0 - Si $a est plus petit a $b  > 0 - Si $a est plus grand que $b
    
    */
    return strcmp($a["group"], $b["group"]);
}

function cmp_cat($a, $b) {
    
    
    /**
    
    * Fonctions de comparaison utilisees dans la fonction usort, pour trier les categories
    
    * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
    * @Return 	< 0 - Si $a est plus petit a $b  > 0 - Si $a est plus grand que $b
    
    */
    return strcmp($a["cat"], $b["cat"]);
}

function cmp_printer($a, $b) {
    
    /**
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le printer-name, insensible a la case
     * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
     * @Return  < 0 - Si $a est plus petit a $b   > 0 - Si $a est plus grand que $b
     */
    return strcasecmp($a["printer-name"], $b["printer-name"]);
}

function cmp_location($a, $b) {
    
    /**
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le printer-location, insensible a la case
     * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
     * @Return  < 0 - Si $a est plus petit a $b   > 0 - Si $a est plus grand que $b
     */
    return strcasecmp($a["printer-location"], $b["printer-location"]);
}

function people_get_variables($config, $cn, $mode = false)
{
    
    /**
     * Retourne un tableau avec les variables d'un utilisateur (a partir de l'annuaire LDAP)
     *
     * @Parametres $uid - L'uid de l'utilisateur
     * @Parametres $mode : - true => recherche  - de l'ensemble des parametres utilisateur - des groupes d'appartenance - false => recherche  - de quelques parametres utilisateur
     *
     *
     * @return Un tableau contenant les informations sur l'utilisateur (uid)
     *        
     */
    $error = "";
    
    $ret_group = array();
    $ret_people = array();
    
    // LDAP attribute
    $ldap_people_attr = array(
        "cn", // login
        "displayname", // Prenom Nom
        "sn", // Nom
        "givenname", // Pseudo -> Prenom
        "mailaddress", // Mail
        "telephonenumber", // Num telephone
        "description",
        "physicaldeliveryoffice", // Date de naissance,Sexe (F/M)
        "jobtitle", // numero unique siecle
        "initials", // pseudo
    );
    $ldap_group_attr = array(
        "cn",
        "description"
    );
    
    list ($ds, $r, $error) = bind_ad_gssapi($config);
    if ($r) {
        $result = @ldap_read($ds, "cn=" . $cn . "," . $config['dn']["people"], "(objectclass=person)", $ldap_people_attr);
        if ($result) {
            $info = @ldap_get_entries($ds, $result);
            if ($info["count"]) {
                
                // Traitement du champ pdo pour extraction de date de naissance, sexe
                if (isset($info[0]["physicaldeliveryoffice"][0])) {
                    $gecos = $info[0]["physicaldeliveryoffice"][0];
                    $tmp = preg_split("/,/", $info[0]["physicaldeliveryoffice"][0], 4);
                }
                $ret_people = array(
                    "cn" => $info[0]["cn"][0],
                    "nom" => stripslashes(utf8_decode($info[0]["sn"][0])),
                    "fullname" => stripslashes(utf8_decode($info[0]["displayname"][0])),
                    "prenom" => (isset($info[0]["givenname"][0])) ? utf8_decode($info[0]["givenname"][0]) : "",
                    "pseudo" => (isset($info[0]["initials"][0]) ? utf8_decode($info[0]["initials"][0]) : ""),
                    "email" => $info[0]["mail"][0],
                    "tel" => (isset($info[0]["telephonenumber"][0]) ? $info[0]["telephonenumber"][0] : ""),
                    "description" => (isset($info[0]["description"][0]) ? utf8_decode($info[0]["description"][0]) : ""),
                    "sexe" => (isset($tmp[1]) ? $tmp[1] : ""),
                    "date" => (isset($tmp[0]) ? $tmp[0] : ""),
                    "employeeNumber" => (isset($info[0]["jobtitle"][0]) ? $info[0]["jobtitle"][0] : "")
                );
                @ldap_free_result($result);
                if ($mode) {
                    // Recherche des groupes d'appartenance dans la branche Groups
                    // Recherche des groupes d'appartenance dans la branche Groups
                    $filter = "(&(objectclass=group)(member=cn=$cn," . $config['dn']["people"] . "))";
                    $result = @ldap_list($ds, $config['dn']["groups"], $filter, $ldap_group_attr);
                    if ($result) {
                        $info = @ldap_get_entries($ds, $result);
                        if ($info["count"]) {
                            for ($loop = 0; $loop < $info["count"]; $loop++) {
                                 $ret_group[$loop] = array(
                                    "cn" => $info[$loop]["cn"][0],
                                    "description" => utf8_decode($info[$loop]["description"][0]),
                                );
                            }
                            
                            usort($ret_group, "cmp_cn");
                        }
                        
                        @ldap_free_result($result);
                    }
                 }
                 
            }
        } else {
            $error = gettext("Echec du bind anonyme");
        }
        
        @ldap_close($ds);
    } else {
        $error = gettext("Erreur de connection au serveur LDAP");
    }
    
    return array(
        $ret_people,
        $ret_group
    );
}

function search_people($config, $filter)
{
    
    /**
     * Recherche d'utilisateurs dans la branche people
     *
     * @Parametres $filter - Un filtre de recherche permettant l'extraction de l'annuaire des utilisateurs
     * @return Un tableau contenant les utilisateurs repondant au filtre de recherche ($filter)
     *        
     */
    $error = "";
    
    // Initialisation:
    $ret = array();
    
    // LDAP attributes
    $ldap_search_people_attr = array(
        "cn", // login
        "displayName", // Nom complet
        "sn" // Nom
    );
    
    list ($ds, $r, $error) = bind_ad_gssapi($config);
    if ($r) {
        // Recherche dans la branche people
        $result = @ldap_search($ds, $config['dn']["people"], $filter, $ldap_search_people_attr);
        if ($result) {
            $info = @ldap_get_entries($ds, $result);
            if ($info["count"]) {
                for ($loop = 0; $loop < $info["count"]; $loop ++) {
                    $ret[$loop] = array(
                        "cn" => $info[$loop]["cn"][0],
                        "displayname" => utf8_decode($info[$loop]["displayname"][0]),
                        "sn" => utf8_decode($info[$loop]["sn"][0])
                    );
                }
            }
            
            @ldap_free_result($result);
        } else {
            $error = gettext("Erreur de lecture dans l'annuaire LDAP");
        }
    } else {
        $error = gettext("Echec du bind anonyme");
    }
    
    @ldap_close($ds);
    
    // Tri du tableau par ordre alphabetique
    if (count($ret)) {
        usort($ret, "cmp_nom");
    }
    return $ret;
}

function search_machines($config, $filter, $branch)
{
    
    /**
     * Recherche de machines dans l'ou $branch
     *
     * @Parametres $filter - Un filtre de recherche permettant l'extraction de l'annuaire des machines
     * @Parametres $branch - L'ou correspondant a l'ou contenant les machines
     *
     * @return Retourne un tableau avec les machines
     */
    
    // Initialisation
    $computers = array();
    
    // LDAP attributs
    if ("$branch" == "computers")
        $ldap_computer_attr = array(
            "cn", // nom d'origine
            "displayname", // Nom netbios avec $
            "dnshostname", // FDQN
            "location", // Emplacement
            "description", // Description de la machine
            "iphostnumber"
        );
    else
        $ldap_computer_attr = array(
            "cn"
        );
    
    list ($ds, $r, $error) = bind_ad_gssapi($config);
    if ($r) {
        $result = ldap_list($ds, $config['dn'][$branch], $filter, $ldap_computer_attr);
        ldap_sort($ds, $result, "cn");
        if ($result) {
            $info = ldap_get_entries($ds, $result);
            if ($info["count"]) {
                for ($loop = 0; $loop < $info["count"]; $loop ++) {
                    $computers[$loop]["cn"] = $info[$loop]["cn"][0];
                    if ("$branch" == "computers") {
                        $computers[$loop]["displayname"] = (isset($info[$loop]["displayname"][0]) ? $info[$loop]["displayname"][0] : "");
                        if (isset($info[$loop]["dnshostname"][0])) {
                            $computers[$loop]["dnshostname"] = $info[$loop]["dnshostname"][0];
                        }
                        if (isset($info[$loop]["location"][0])) {
                            $computers[$loop]["location"] = $info[$loop]["location"][0];
                        }
                        if (isset($info[$loop]["description"][0])) {
                            $computers[$loop]["description"] = utf8_decode($info[$loop]["description"][0]);
                        }
                        if (isset($info[$loop]["iphostnumber"][0])) {
                            $computers[$loop]["ipHostNumber"] = utf8_decode($info[$loop]["iphostnumber"][0]);
                        }
                    }
                }
            }
            @ldap_free_result($result);
        }
    }
    @ldap_close($ds);
    return $computers;
}

function search_groups($config, $filter)
{
    
    /**
     * Recherche une liste de groupes repondants aux criteres fixes par la variable $filter.
     * Les filtres sont les memes que pour ldapsearch.
     * Par exemple (&(cnMember=wawa)(cnMember=toto)) recherche le groupe contenant les utilisateurs wawa et toto.
     *
     * @Parametres $filter - Un filtre de recherche permettant l'extraction de l'annuaire des utilisateurs
     *
     *
     * @return Retourne un tableau $groups avec le cn et la description de chaque groupe
     */
    $groups = array();
    
    // LDAP attributs
    $ldap_group_attr = array(
        "objectclass",
        "cn",
        "member",
        "gidnumber",
        "description" // Description du groupe
    );
    
    list ($ds, $r, $dn) = bind_ad_gssapi($config); // $ds = @ldap_connect($ldap_server, $ldap_port);
    
    if ($r) {
        $result = ldap_search($ds, $config['dn']["groups"], $filter, $ldap_group_attr);
        if ($result) {
            $info = ldap_get_entries($ds, $result);
            if ($info["count"]) {
                for ($loop = 0; $loop < $info["count"]; $loop ++) {
                    $groups[$loop]["cn"] = $info[$loop]["cn"][0];
                    $groups[$loop]["gidnumber"] = (isset($info[$loop]["gidnumber"][0]) ? $info[$loop]["gidnumber"][0] : "");
                    if (isset($info[$loop]["description"][0])) {
                        $groups[$loop]["description"] = utf8_decode($info[$loop]["description"][0]);
                    }
                    // Recherche de posixGroup ou group
                    for ($i = 0; $i < $info[$loop]["objectclass"]["count"]; $i ++) {
                        if ($info[$loop]["objectclass"][$i] != "top")
                            $type = $info[$loop]["objectclass"][$i];
                    }
                    $groups[$loop]["type"] = $type;
                }
            }
            
            @ldap_free_result($result);
        }
    }
    
    @ldap_close($ds);
    
    if (count($groups))
        usort($groups, "cmp_cn");
    
    return $groups;
}

?>
