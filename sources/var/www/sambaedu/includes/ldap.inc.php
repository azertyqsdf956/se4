<?php

/**
 * Fonctions LDAP

 * @Projet LCS / SambaEdu

 * @Auteurs Equipe Sambaedu

 * @Version $Id: ldap.inc.php  09-11-2018 mrfi $

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
require_once "samba-tool.inc.php";

// Pour activer/desactiver la modification du givenname (Prenom) lors de la modification dans annu/mod_user_entry.php
$corriger_givenname_si_diff = "n";

// fonctions validées se4
function cmp_fullname($a, $b)
{

    /**
     *
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le fullname
     *
     * @Parametres $a - La premiere entree 	$b - La deuxieme entree a comparer
     *
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     */
    return strcmp($a["fullname"], $b["fullname"]);
}

function cmp_nom($a, $b)
{

    /**
     *
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le name
     *
     * @Parametres $a - La premiere entree 	$b - La deuxieme entree a comparer
     *
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     *
     */
    return strcmp($a["nom"], $b["nom"]);
}

function cmp_cn($a, $b)
{

    /**
     *
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le cn (common name)
     *
     * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
     *
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     *
     */
    return strcmp($a["cn"], $b["cn"]);
}

function cmp_group($a, $b)
{

    /**
     *
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier les groupes
     *
     * @Parametres  $a - La premiere entree 	$b - La deuxieme entree a comparer
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     *
     */
    return strcmp($a["group"], $b["group"]);
}

function cmp_cat($a, $b)
{

    /**
     *
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier les categories
     *
     * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     *
     */
    return strcmp($a["cat"], $b["cat"]);
}

function cmp_printer($a, $b)
{

    /**
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le printer-name, insensible a la case
     *
     * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     */
    return strcasecmp($a["printer-name"], $b["printer-name"]);
}

function cmp_location($a, $b)
{

    /**
     * Fonctions de comparaison utilisees dans la fonction usort, pour trier le printer-location, insensible a la case
     *
     * @Parametres  $a - La premiere entree  $b - La deuxieme entree a comparer
     * @return < 0 - Si $a est plus petit a $b > 0 - Si $a est plus grand que $b
     */
    return strcasecmp($a["printer-location"], $b["printer-location"]);
}

/**
 *
 * @param string $dn
 * @return string $cn
 */
function ldap_dn2cn(string $dn)
{
    $rdns = explode(",", ldap_dn2ufn($dn));
    return $rdns[0];
}

function remove_count($arr)
{
    foreach ($arr as $key => $val) {
        if ($key === "count")
            unset($arr[$key]);
        elseif (is_array($val))
            $arr[$key] = remove_count($arr[$key]);
    }
    return $arr;
}

function bind_ad_gssapi($config, &$error = "")
{
    /*
     * établit une connexion avec l'AD en GSSAPI
     */
    $url = "ldap://" . $config['se4ad_name'] . "." . $config['domain'];
    $ds = ldap_connect($url, '389');
    if ($ds) {
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
        $r = ldap_sasl_bind($ds, null, null, 'GSSAPI');
        if (! $r) {
            $error = gettext("Echec de l'Authentification.");
            $ds = false;
        }
    } else {
        $error = gettext("Erreur de connection kerberos au serveur AD");
    }
    return $ds;
}

function search_ad($config, $name, $type = "dn", $branch = "all", $attrs = array())
{

    /**
     * Recherche des objets dans l'AD
     *
     * @Parametres $name - le nom de l'objet (cn|ou|dn) "*" pour tout
     * @Parametres $type - le type d'objets à chercher : dn, user, computer, group, classe, equipe, projet, parc, rights
     * @Parametre $brnch - la branche de recherche "all" pour tout (sans effet pour dn)
     * @return Retourne un tableau avec les objets et leurs attributs utiles
     */

    // Initialisation
    $info = array();
    // correspondance des attributs: ldapAD => php
    $map = array(
        "sn" => "nom",
        "displayname" => "fullname",
        "givenname" => "prenom",
        "initials" => "pseudo",
        "mail" => "email",
        "telephonenumber" => "tel",
        "title" => "employeenumber"
    );

    // LDAP attributs
    switch ($type) {
        case "user":
            $filter = "(&(objectclass=user)(cn=" . $name . "))";
            $ldap_attrs = array(
                "cn", // login
                "displayname", // Prenom Nom
                "sn", // Nom
                "givenname", // Pseudo -> Prenom
                "mail", // Mail
                "telephonenumber", // Num telephone
                "description",
                "physicaldeliveryofficename", // Date de naissance,Sexe (F/M)
                "title", // numero unique siecle
                "initials", // pseudo
                "useraccountcontrol", // état du compte actif : 512, desactivé 514
                "memberof" // groupes
            );
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
            break;
        case "employeenumber": // recherche par le n° Siecle/STS
            $filter = "(&(objectclass=user)(|(title=" . $name . ")(title=" . sprintf("%05d", $name) . ")(title=" . preg_replace("/^0*/", "", $name) . ")))";
            $ldap_attrs = array(
                "cn" // login
            );
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
            break;
        case "filter": // user ou group
            $filter = $name;
            $ldap_attrs = array(
                "cn",
                "displayname",
                "description" //
            );
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
            break;
        case "member": // cherche si user ou group est dans la ou $branch (profs eleves administratifs)
            $filter = "(&(cn=" . $name . ")(objectclass=user))";
            $ldap_attrs = array(
                "cn" // login
            );
            $branch = "ou=" . $branch . "," . $config['dn']['people'];
            break;
        case "memberof": // cherche si user ou group est membre du groupe branch
            $filter = "(&(cn=" . $name . ")(objectclass=user)(memberof=cn=" . $branch . "*))";
            $ldap_attrs = array(
                "cn", // login
                "displayname", // Prenom Nom
                "sn", // Nom
                "physicaldeliveryofficename"
            );
            $branch = $config['dn']['people'];
            break;
        case "pp": // cherche si user ou group est pp de la classe $branch
            if ($name == "*")
                $filter = "(&(objectclass=group)(cn=PP_*))";
            else
                $filter = "(&(objectclass=group)(cn=PP_*)(|(cn=PP_" . $name . ")(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn", // login
                "member"
            );
            $branch = $config['dn']['groups'];
            break;

        case "dn":
            $ldap_attrs = array();
            $filter = "(dn=" . $name . ")";
            $branch = $config['ldap_base_dn'];
            break;
        case "machine":
            $ldap_attrs = array(
                "cn", // nom d'origine
                "displayname", // Nom netbios avec $
                "dnsHostname", // FDQN
                "location", // Emplacement
                "description", // Description de la machine
                "iphostnumber", // adresse ip reservée
                "networkaddress", // adresse mac
                "memberof", // appartenance aux groupes
                "netbootinitialization", // action IPXE
                "netbootmachinefilepath" // action programmée
            );
            $filter = "(&(objectclass=computer)(|(cn=" . $name . ")(iphostnumber=" . $name . ")(networkaddress=" . $name . ")(dn=" . $name . ")))";
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
            break;
        case "salle":
            $filter = "(&(objectclass=organizationalunit)(ou=" . $name . "))";
            $branch = $config['dn']['computers'];
            $ldap_attrs = array(
                "ou",
                "description"
            );
            break;
        case "parc":
            $filter = "(&(objectclass=group)(cn=" . $name . "))";
            $branch = $config['dn']['parcs'];
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            break;
        case "group":
            if ($name == "*")
                $filter = "(objectclass=group)";
            else
                $filter = "(&(objectclass=group)(|(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
            break;
        case "classe":
            if ($name == "*")
                $filter = "(&(objectclass=group)(cn=Classe_*))";
            else
                $filter = "(&(objectclass=group)(|(cn=Classe_" . $name . ")(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            $branch = $config['classes_rdn'] . "," . $config['dn']['groups'];
            break;
        case "equipe":
            if ($name == "*")
                $filter = "(&(objectclass=group)(cn=Equipe_*))";
            else
                $filter = "(&(objectclass=group)(cn=Equipe_*)(|(cn=Equipe_" . $name . ")(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            $branch = $config['equipes_rdn'] . "," . $config['dn']['groups'];
            break;
        case "matiere":
            if ($name == "*")
                $filter = "(&(objectclass=group)(cn=Matiere_*))";
            else
                $filter = "(&(objectclass=group)(cn=Matiere_*)(|(cn=Matiere_" . $name . ")(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            $branch = $config['matieres_rdn'] . "," . $config['dn']['groups'];
            break;
        case "projet":
            if ($name == "*")
                $filter = "(&(objectclass=group)(cn=Projet*))";
            else
                $filter = "(&(objectclass=group)(cn=Projet_*)(|(cn=Projet_" . $name . ")(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            $branch = $config['projets_rdn'] . "," . $config['dn']['groups'];
            break;
        case "autre":
            if ($name == "*")
                $filter = "(&(objectclass=group)(cn=*))";
            else
                $filter = "(&(objectclass=group)(|(cn=" . $name . ")(cn=" . $name . ")(dn=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            $branch = $config['other_groups_rdn'] . "," . $config['dn']['groups'];
            break;

        case "imprimante":
            break;
        case "delegation":
            if ($name == "*")
                $filter = "(objectclass=group)";
            else
                $filter = "(&(objectclass=group)(|(member=" . $name . ")(member=cn=" . $name . "*)))";
            $ldap_attrs = array(
                "cn"
            );
            $branch = $config['dn']['delegations'];
            break;
        case "right":
            $filter = "(&(objectclass=group)(|(cn=" . $name . ")(member=cn=" . $name . "*)(member=" . $name . ")))";
            $branch = $config['dn']['rights'];
            $ldap_attrs = array(
                "cn",
                "description",
                "member"
            );
            break;
        case "type":
            $filter = "(|(objectclass=group)(objectclass=person)(objectclass=organizationalunit))";
            $ldap_attrs = array(
                "objectClass"
            );
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
            break;
        case "gpo":
            /*
             * liste les gpo, directeent en ldap pour eviter d'avoir à parser les resultats de samba-tool
             */
            if ($name == "*")
                $filter = "(objectclass=grouppolicycontainer)";
            else
                $filter = "(&(objectclass=grouppolicycontainer)(|(cn=" . $name . ")(displayname=" . $name . ")))";
            $ldap_attrs = array(
                "cn",
                "displayname",
                "gpcfilesyspath",
                "versionnumber",
                "flags"
            );
            $branch = "CN=Policies,CN=System," . $config['ldap_base_dn'];
            break;
        default:
            $filter = "(cn=" . $name . ")";
            $ldap_attrs = array(
                "cn"
            );
            if ($branch == "all") {
                $branch = $config['ldap_base_dn'];
            }
    }
    $ldap_attrs = array_merge($ldap_attrs, $attrs);
    $ret = array();
    $ds = bind_ad_gssapi($config);
    if ($ds) {
        // var_dump($branch);
        // var_dump($filter);
        // var_dump($ldap_attrs);
        $result = ldap_search($ds, $branch, $filter, $ldap_attrs);
        // $res = var_dump(ldap_get_entries($ds, $result));
        // ldap_sort($ds, $result, "cn");
        if ($result) {
            $info = ldap_get_entries($ds, $result);
            // var_dump($info);
            foreach ($info as $key1 => $entry) {
                if (is_array($entry)) {
                    foreach ($entry as $key2 => $attr) {
                        if ("$key2" == "dn") {
                            $ret[$key1][$key2] = $attr;
                        }

                        if (is_array($attr)) {
                            if (isset($map[$key2])) {
                                $key = $map[$key2];
                            } else {
                                $key = $key2;
                            }
                            if ("$key" == "physicaldeliveryofficename") {
                                if (isset($attr[0])) {
                                    $tmp = preg_split('/,/', $attr[0], 4);
                                    $ret[$key1]['sexe'] = (isset($tmp[1]) ? $tmp[1] : "");
                                    $ret[$key1]['date'] = (isset($tmp[0]) ? $tmp[0] : "");
                                }
                            } elseif ((is_array($attr)) && (("$key" == "member") || ($key == 'memberof'))) {
                                foreach ($attr as $key3 => $gdn) {
                                    if ("$key3" != 'count') {
                                        $ret[$key1][$key][] = $gdn;
                                    }
                                }
                            } elseif ("$key" == "objectclass") {
                                foreach ($attr as $obj) {
                                    if (("$obj" == "user") || ("$obj" == "computer") || ("$obj" == "organizationalunit")) {
                                        $ret[$key] = $obj;
                                    }
                                }
                            } elseif (isset($attr[0])) {
                                $ret[$key1][$key] = utf8_decode($attr[0]);
                            }
                        }
                    }
                }
            }
            @ldap_free_result($result);
        } else {
            $ret = false;
        }
    }
    @ldap_close($ds);
    // print "search_ad(" . $name . ", $type):";
    // var_dump($ret);
    return $ret;
}

/**
 * Modifie un ojet dans l'AD
 *
 * @param array $config
 * @param string $name
 *            le nom de l'objet (cn)
 * @param string $type
 *            le type d'objet à chercher : user, computer, group, classe, equipe, projet, parc, rights
 * @param array $attrs
 *            tableau associatif des attributs à changer (format ldapmodify)
 * @param string $mode
 *            type d'opération
 * @return boolean // Pour del aussi, il faut fournir la bonne valeur de l'attribut pour que cela fonctionne
 *         // On peut ajouter, modifier, supprimer plusieurs attributs a la fois.
 */
function modify_ad(array $config, string $name, string $type, array $attrs, string $mode = "replace")
{
    $res = search_ad($config, $name, $type);
    if (count($res) == 1) {
        $dn = $res[0]['dn'];
        $ds = bind_ad_gssapi($config);
        if ($ds) {
            switch ($mode) {
                case "add":
                    $result = ldap_mod_add($ds, $dn, $attrs);
                    break;
                case "del":
                    $result = ldap_mod_del($ds, $dn, $attrs);
                    break;
                case "replace":
                    $result = ldap_mod_replace($ds, $dn, $attrs);
                    break;
            }
            @ldap_close($ds);
            return $result;
        }
    }
    return false;
}

function delete_ad($config, $name, $type)
{

    /**
     * supprime un objet dans l'AD
     *
     * @Parametres $name - le nom de l'objet (cn ou ou)
     * @parametres $type - type d'objet recherché
     * @return true ou false
     */
    $res = search_ad($config, $name, $type);
    if ($res) {
        $dn = $res[0][dn];

        $ds = bind_ad_gssapi($config);
        if ($ds) {
            $ret = ldap_delete($ds, $dn);
            @ldap_close($ds);
            return $ret;
        }
    }
    return false;
}

function move_ad($config, $name, $new_dn, $type)
{
    /**
     * deplace ou renomme un objet dans l'AD
     *
     * @Parametres $name - le nom de l'objet (cn ou ou)
     * @parametres $new_dn - nouveau dn complet
     * @return true ou false
     */
    $res = search_ad($config, $name, $type);
    if ($res) {
        $dn = $res[0]['dn'];
        $new_dn_elements = preg_split('/,/', $new_dn, 2);
        $ds = bind_ad_gssapi($config);
        if ($ds) {
            $ret = ldap_rename($ds, $dn, $new_dn_elements[0], $new_dn_elements[1], true);
            @ldap_close($ds);
            return $ret;
        }
    }
    return false;
}

function trash_user($config, $user)
{
    $type = type_user($config, $user);
    if (! empty($type)) {
        $ou = "ou=" . $type . ",";
        groupdelmember($config, $user, $type);
    }
    $ret = move_ad($config, $user, "cn=" . $user . "," . $ou . $config['dn']['trash'], "user");
    $attrs = array();
    $attrs['useraccountcontrol'] = 514;
    modify_ad($config, $user, "user", $attrs);
    return $ret;
}

function recup_user($config, $user)
{
    $categorie = type_user($config, $user);
    $ret = move_ad($config, $user, "cn=" . $user . ",ou=" . $categorie . "," . $config['dn']['people'], "user");
    if ($ret) {
        $attrs = array();
        $attrs['useraccountcontrol'] = 512;
        modify_ad($config, $user, "user", $attrs);
        groupaddmember($config, $user, $categorie);
    }
    return $ret;
}

function trash_users($config)
{
    $users = search_ad($config, "*", "user", $config['dn']['people']);
    $ret = true;
    foreach ($users as $user) {
        $trash = true;
        if (have_right($config, "no_trash_user", $user['cn'])) {
            $trash = false;
        } else {
            foreach ($user['memberof'] as $groupdn) {
                if (preg_match("/cn=(Classe_|Equipe_|Cours_|Administratifs|Domain Admins).*/i", $groupdn))
                    $trash = false;
            }
        }
        if ($trash) {
            print "trash : " . $user['cn'] . "<br>\n";
            $ret = trash_user($config, $user['cn']);
        }
    }
    return $ret;
}

/*
 * retourne le type d'un objet ad
 *
 */
function type_ad($config, $name)
{
    $res = search_ad($config, $name, "type");
    if ($res) {
        if (in_array("person", $res[0]))
            return "user";
        elseif (in_array("group", $res[0]))
            return "group";
        elseif (in_array("organizationalunit", $res[0]))
            return "ou";
    }
    return false;
}

function filter_user($config, $filter)
{

    /**
     * Recherche d'utilisateurs dans la branche people
     *
     * @Parametres $filter - Un filtre de recherche permettant l'extraction de l'annuaire des utilisateurs
     * @return Un tableau contenant les utilisateurs repondant au filtre de recherche ($filter)
     *
     */
    $ret = search_ad($config, $filter, "filter");

    // Tri du tableau par ordre alphabetique
    if (count($ret)) {
        usort($ret, "cmp_nom");
    }
    return $ret;
}

function filter_group($config, $filter)
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
    $groups = search_ad($config, "(&(objectclass=group)" . $filter . ")", "filter", $config['dn']['groups']);
    if (count($groups))
        usort($groups, "cmp_cn");

    return $groups;
}

function search_user($config, $cn)
{

    /**
     * Retourne un tableau avec les attributs d'un utilisateur (a partir de l'annuaire LDAP)
     *
     * @Parametres $cn de l'utilisateur
     *
     * @return Un tableau contenant les informations sur l'utilisateur (cn)
     *         les groupes sont dans le tableau $res['memberof']
     *
     */
    $ret = search_ad($config, $cn, "user");
    if (count($ret) > 0) {
        return $ret[0];
    } else
        return array();
}

function create_user(array $config, string $cn, string $prenom, string $nom, string $userpwd, string $naissance, string $sexe, string $categorie, string $employeenumber)
{
    if (count(verif_employeenumber($config, $employeenumber)) == 0) {
        // Il faut determiner le login (attribut cn : use-username-as-cn) en fonction du nom prenom de l'uidpolicy...
        // Si $cn existe déja dans l'AD (doublon) il faut en fabriquer un autre
        if ($cn == "")
            $cn = creer_cn($config, $nom, $prenom);
        if (! recup_user($config, $cn))
            $res = useradd($config, $cn, $prenom, $nom, $userpwd, $naissance, $sexe, $categorie, $employeenumber);
        return $res;
    }
    return false;
}

function search_machine($config, $cn, $ip = false)
{

    /**
     * Retourne un tableau avec les attributs d'un ordinateur (a partir de l'annuaire LDAP)
     *
     * @Parametres cn,dn ou displayname, recherche de l'ip depuis le dns ou le dhcp en option
     *
     * @return Un tableau contenant les informations sur la machine (cn ou dn ou dsiplayname)
     *         les dn groupes sont dans le tableau $res['memberof']
     *
     */
    $ret = search_ad($config, $cn, "machine");
    if (count($ret) > 0) {
        $ret = $ret[0];
        if ($ip && ! isset($ret['iphostnumber']) && isset($ret['dnsHostName'])) {
            $ret['iphostnumber'] = gethostbyname($ret['dnsHostName']);
            if ($ret['iphostnumber'] == $ret['dnsHostName']) {
                // pas de dns, on tente le dhcp
                $lease = get_dhcp_lease($config, $cn);
                if (isset($lease)) {
                    $ret['iphostnumber'] = $lease['ip'];
                    $ret['networkaddress'] = $lease['mac'];
                }
            }
        }
    }
    return $ret;
}

function create_machine($config, $name, $ou, $description = "reservation dhcp uniquement")
{
    if (! search_ad($config, $name, "machine")) {
        // Prépare les données
        $info =array();
        $info["cn"] = "$name";
        $info["objectclass"] = array(
            "top",
            "computer"
        );
        $info["samaccountname"] = $name . "$";
        // $info["samaccounttype"] = 0x30000001;
        $info["description"] = $description;
        $info["useraccountcontrol"] = 0x1000;

        // Ajout
        $ds = bind_ad_gssapi($config);
        $ret = ldap_add($ds, "cn=" . $name . "," . $ou . "," . $config['ldap_base_dn'], $info);
//        $err = ldap_error($ds);
        ldap_close($ds);

        return $ret;
    }
}

/**
 * retourne le groupe principal d'un utilisateur
 *
 * @param array $config
 * @param string $user
 * @return string
 */
function type_user(array $config, string $user)
{
    $res = search_user($config, $user);
    if (count($res) > 0) {
        $match = array();
        $groupsdn = $res['memberof'];
        foreach ($groupsdn as $groupdn) {
            if (preg_match("/cn=(Eleves|Profs|Administratifs).*/i", $groupdn, $match)) {
                return $match[1];
            }
        }
        if (preg_match("/ou=(Eleves|Profs|Administratifs).*/i", $res['dn'], $match)) {
            return $match[1];
        }
    }
    return "";
}

/**
 * Recherche si l'utilisateur courant a au moins un des droits type1|type2..
 * vrai dans tous les cas si l'utilisateur a le droit se3_is_admin
 *
 * @Parametres right
 * @return true ou false
 */
function have_right($config, $type, $user = "login")
{
    if ($user == "login")
        $user = $config['login'];
    $typearr = explode("|", "$type");
    $typearr[] = "se3_is_admin";
    foreach ($typearr as $right) {
        if (in_array($right, list_rights($config, $user))) {
            return true;
        }
    }
    return false;
}

/**
 * retourne tous les droits dont dispose $name (user ou groupe)
 * recherche recursive pour les groupes d'appartenance
 *
 * @Parametres : name - utilisateur ou groupe
 * @Parametres : inverse - retourne les drois que l'utisateur n'a pas
 * @return array des droits
 */
function list_rights($config, $name, $inverse = false)
{
    $rights = array();
    if ("$name" == "all") {
        $res = search_ad($config, "*", "right");
        foreach ($res as $right) {
            $rights[] = $right['cn'];
        }
        return $rights;
    }
    $user = search_user($config, $name);
    $ret = search_ad($config, $name, "right");
    $groups = $user['memberof'];
    foreach ($groups as $groupdn) {
        $ret1 = search_ad($config, $groupdn, "right");
        $ret = array_merge($ret1, $ret);
        if ($ret) {
            foreach ($ret as $group) {
                $right = $group['cn'];
                if (! in_array($right, $rights)) {
                    $rights[] = $right;
                }
            }
        }
        if (count($rights) == 0)
            return array();
        if ($inverse) {
            $not_rights = array();
            foreach (list_rights($config, "all") as $r) {
                if (! in_array($r, $rights))
                    $not_rights[] = $r;
            }
            return $not_rights;
        }
        return $rights;
    }
}

function list_members_rights($config, $right)
{
    $res = grouplistmembers($config, $right);
    return $res;
}

/*
 * donne le droit à l'utilisateur ou a au groupe
 * si il l'a dejà par héritage d'un groupe on ne fait rien
 */
function add_right($config, $name, $right)
{
    if (! in_array($right, list_rights($config, $name))) {
        return groupaddmember($config, $name, $right);
    }
    return true;
}

/*
 * retire le droit à l'utilisateur
 * non récursif : si le droit est hérité il ne sera pas retiré.
 */
function remove_right($config, $name, $right)
{
    if (in_array(search_ad($right, $config, $name, "right"))) {
        return groupdelmember($config, $name, $right);
    }
    return false;
}

// Parcs
/*
 * type d'un parc
 *
 */
function type_parc($config, $parc)
{
    if (search_ad($config, $parc, "salle")) {
        return "salle";
    } elseif (search_ad($config, $parc, "parc")) {
        return "parc";
    } else {
        return false;
    }
}

/*
 * ajoute le type de parc dans les attributs des parcs
 * @ parametres : array (parcs)
 * @ parametres : type - auto, salle, parc
 * @ return = : array ( parcs)
 */
function set_type_parc($config, $parcs)
{
    if (is_array($parcs)) {
        $ret = array();
        foreach ($parcs as $key => $parc) {
            // if (isset($parc['ou'])) {
            if (type_parc($config, $parc) == "salle") {
                $val = "salle";
                $name = $parc['ou'];
            } else {
                $val = "parc";
                $name = $parc['cn'];
            }
            $ret[$key]['name'] = $name;
            $ret[$key]['type'] = $val;
            $ret[$key]['dn'] = $parc['dn'];
            $ret[$key]['description'] = $parc['description'];
        }
    } else {
        $ret = false;
    }
    return $ret;
}

/*
 * liste des parcs d'appartenance
 * @ parametres : member - machine à tester, null ou * si tous les parcs"
 * @ parametres : type - "salle", "parc", "all(defaut)"
 * @ return : array[]{dn, cn, description, type}
 */
function list_parc($config, $member, $type = "all")
{
    if ($type == "salle") {
        $ret = set_type_parc($config, search_ad($config, $member, "salle"));
        return $ret;
    } elseif (($type == "parc") || ($type == "all")) {
        $ret = set_type_parc($config, search_ad($config, $member, "parc"));
        return $ret;
    }
}

/*
 * creation de parc
 *
 */
function create_parc($config, $parc, $description = "", $type = "salle")
{
    if ($type == "salle") {
        $res = ouadd($config, $parc, $config['dn']['computers']);
    }
    $res = groupadd($config, $parc, $config['dn']['parcs'], $description);
    return $res;
}

/*
 * suppression de parc
 * Si il s'agit d'une salle il faut commencer par deplacer les machines vers l'ou parent
 */
function delete_parc($config, $parc)
{
    $type = type_parc($config, $parc);
    if ($type == "salle") {
        $machines = list_members_parc($config, $parc, true);
        foreach ($machines as $machine) {
            move_ad($machine['cn'], $machine['cn'] . "," . $config['dn']['computers'], "machine");
        }
    }
    $ret = delete_ad($config, $parc, $type);
    if ($ret) {
        $res = search_delegations($config, $parc);
        if ($res) {
            foreach ($res as $delegation) {
                groupdel($config, $delegation['cn']);
            }
        }
    }
    return $ret;
}

/*
 * liste le contenu d'un parc
 * @ parametres : parc à lister
 * @ parametre : attrs - booleen enregistrements complets ou liste
 * @ return : liste des machines, enregistrements complets des machines, false
 */
function list_members_parc($config, $parc, $attrs = false)
{
    $type = type_parc($config, $parc);
    $ret = array();
    if ("$type" == "salle") {
        $res = search_ad($config, "*", "machine", "ou=" . $parc . "," . $config['dn']['computers']);
        if ($attrs) {
            $ret = $res;
        } else {
            foreach ($res as $machine) {
                $ret[] = $machine['cn'];
            }
        }
    } elseif ($type = "parc") {
        $res = search_ad($config, $parc, "group");
        foreach ($res[0]['member'] as $machine) {
            $dn_elements = preg_split('/,/', 1);
            $cn = preg_split('/=/', $dn_elements);
            if ($attrs) {
                $ret[] = search_ad($config, $cn[1], "machine");
            } else {
                $ret[] = $cn[1];
            }
        }
    }
    return $ret;
}

/*
 * ajoute un membre au parc
 * pour les salles il faut déplacer les machines
 */
function add_member_parc($config, $parc, $member)
{
    if (type_parc($config, $parc) == "salle") {
        return move_ad($member, $member . ",ou=" . $parc . "," . $config['dn']['computers'], "machine");
    } else {
        return groupaddmember($config, $member, $parc);
    }
}

/*
 * enlève un membre du parc
 * $res=
 */
function remove_member_parc($config, $parc, $member)
{
    if (type_parc($config, $parc) == "salle") {
        return move_ad($config, $member, $member . "," . $config['dn']['computers'], "machine");
    } else {
        return groupdelmember($config, $member, $parc);
    }
}

/*
 * teste l'appartenance au parc
 *
 */
function is_member_parc($config, $parc, $member)
{
    $res = list_parc($config, $member, "all");
    if ($res) {
        foreach ($res as $value) {
            if ($value['cn'] == $parc)
                return true;
        }
        return false;
    }
}

/*
 * teste si l'utilisateur loggé a les droits sur le parc
 *
 */
function have_delegation($config, $parc, $right)
{
    if (type_delegation($config, $parc) == $right)
        return true;
    return false;
}

/*
 * recherche les parcs délégués à l'utilisateur ou groupe,
 * recursive sur les groupes d'appartenance
 * @ return array(user, parc, level) ou false
 */
function list_delegations($config, $name = "login", $recurse = true)
{
    $parc =array();
    if ($name == "login")
        $name = $config['login'] ?? "";
    if (! empty($name)) {
        $user = search_user($config, $name);
        $cn = $user['cn'] ?? "";
        if (! empty($cn)) {
            if ($recurse) {
                $ret = list_delegations($config, $name, false);
                $user = search_user($config, $name);
                foreach ($user['memberof'] as $groupdn) {
                    $ret1 = list_delegations($config, $groupdn, false);
                    $ret = array_merge($ret1, $ret);
                    return $ret;
                }
            } else {

                $res = search_ad($config, $name, "delegation");
                if ($res) {
                    foreach ($res as $key => $group) {
                        $delegation = explode('_', $group['cn'], 2);
                        $parc[$key]['user'] = $cn;
                        $parc[$key]['cn'] = $delegation[1];
                        $parc[$key]['level'] = $delegation[0];
                    }
                    return $parc;
                }
            }
        }
    }
    return array();
}

/*
 * retourne le niveau de délégation de l'utlisateur courant pour une machine ou un parc
 * @parametre : nom du parc ou de la machine
 * @return : "manage", "view", false
 */
function type_delegation($config, $name, $user = "login")
{
    $parcs = list_delegations($config, $user);
    $ret = false;
    if ($parcs) {
        foreach ($parcs as $parc) {
            if ($parc['cn'] == $name)
                return $parc['level'];
            if (is_member_parc($config, $parc['cn'], $name)) {
                if ($parc['level'] == "manage") {
                    return "manage";
                } else {
                    $ret = "view";
                }
            }
        }
    }
    return $ret;
}

/*
 * cherche les delegations pour un parc
 *
 */
function search_delegations(array $config, string $parc)
{
    $ou_delegations = "ou=delegations," . $config['ldap_base_dn'];
    $res = search_ad($config, "*_" . $parc, "group", $ou_delegations);
    return $res;
}

function create_delegation($config, $parc, $name, $level)
{
    $delegation = $level . "_" . $parc;
    $ou_delegations = "ou=delegations," . $config['ldap_base_dn'];
    $res = search_ad($config, $delegation, "group", $ou_delegations);
    $ret = true;
    if (! $res) {
        $ret = groupadd($config, $delegation, $ou_delegations);
    }
    if ($ret)
        $ret = groupaddmember($config, $delegation, $name);
    return $ret;
}

/*
 * supprime une delegation pour un utilisateur ou groupe
 *
 */
function delete_delegation($config, $parc, $name, $level)
{
    $delegation = $level . "_" . $parc;
    $ou_delegations = "ou=delegations," . $config['ldap_base_dn'];
    $res = search_ad($config, $delegation, "group", $ou_delegations);
    $ret = groupdelmember($config, $name, $delegation);
    if (count($res[0]['member']) == 0) {
        $ret = groupdel($config, $delegation);
    }
    return $ret;
}

// ----------------------------------------------------
// dhcp
function get_dhcp_reservation($config, $machine)
{
    $res = search_ad($config, $machine, "machine");
    if (isset($res[0]['iphostnumber']) && isset($res[0]['networkaddress'])) {
        return array(
            'cn' => $res[0]['cn'],
            'iphostnumber' => $res[0]['iphostnumber'],
            'networkaddress' => $res[0]['networkaddress']
        );
    } else {
        return false;
    }
}

/*
 * enregistre une reservation dhcp dans AD
 * @parametres : $machine
 * @parametres : $reservation - tableau {ip, mac}
 * @return : true si ok
 */
function set_dhcp_reservation($config, $machine, $reservation)
{
    return modify_ad($config, $machine, "machine", $reservation);
}

/*
 * enregistre une reservation dhcp dans AD
 * @parametres : $machine
 * @parametres : $reservation - tableau {ip, mac}
 * @return : true si ok
 */
function delete_dhcp_reservation($config, $machine)
{
    $del = array();
    $reservation = get_dhcp_reservation($config, $machine);
    $del['iphostnumber'] = $reservation['iphostnumber'];
    $del['networkaddress'] = $reservation['networkaddress'];

    return modify_ad($config, $machine, "machine", $del, "delete");
}

/*
 * Importe le fichier des reservations /etc/sambaedu/reservations.inc vers l'AD
 * @return : array machine, ip, mac
 */
function import_dhcp_reservations($config)
{
    $contents = file_get_contents("/etc/sambaedu/reservations.inc.se3");
    $contents = explode("\n", $contents);
    $index = 0;
    $data = array();
    $m = array();
    $record = false;
    foreach ($contents as $line) {
        if (preg_match("/^\s*(|#.*)$/", $line, $m)) {
            // on saute les commentaires
        } elseif (preg_match("/^host (.*)$/", $line, $m) && (! $record)) {
            $index ++;
            $data[$index]['cn'] = strtolower($m[1]);
        } elseif (preg_match("/^{/", $line)) {
            $record = true;
        } elseif (preg_match("/^\s*hardware ethernet\s*([0-9a-fA-F:]*)\s*;$/", $line, $m)) {
            $data[$index]['networkaddress'] = strtolower($m[1]);
        } elseif (preg_match("/^\s*fixed-address\s*([0-9\.]*)\s*;$/", $line, $m)) {
            $data[$index]['iphostnumber'] = $m[1];
        } elseif (preg_match("/}*/", $line, $m)) {
            $record = false;
        } else {
            print "Erreur ligne '$line'\n";
            break;
        }
    }
    return $data;
}

function export_dhcp_reservations($config)
{
    $reservations = "/etc/sambaedu/reservations.inc";
    $content = "# reservations exportees autmatiquement de l'annuaire AD\n";
    $content .= "# le " . date(DATE_RSS) . "\n";
    $machines = search_ad($config, "*", "machine", "all");
    foreach ($machines as $machine) {

        $res = (isset($machine['networkaddress']) && isset($machine['iphostnumber']));
        if ($res) {
            $content .= "host " . $machine['cn'] . "\n";
            $content .= "{\n";
            $content .= "hardware ethernet " . $machine['networkaddress'] . ";\n";
            $content .= "fixed-address " . $machine['iphostnumber'] . ";\n";
            $content .= "}\n";
        }
    }
    if (! $handle = fopen($reservations, "w")) {
        die("Erreur d'ecriture des reservtions se4 : $reservations");
    }
    $res = fwrite($handle, $content);
    fclose($handle);
    return true;
}

/**
 * importe les leases dhcp.
 * Seul le dernier enregistrement actif d'une ip est conservé.
 *
 * @param array $config
 * @return array(array(ip, name, mac))
 */
function import_dhcp_leases($config)
{
    $contents = file_get_contents("/var/lib/dhcp/dhcpd.leases");
    $contents = explode("\n", $contents);
    $current = 0;
    $data = $m = array();
    foreach ($contents as $line) {
        switch ($current) {
            case 0:
                if (preg_match("/^\s*(|#.*)$/", $line, $m)) {} else if (preg_match("/^lease (.*) {/", $line, $m)) {
                    $current = $m[1];
                } else if (preg_match("/^server-duid/", $line)) {
                    // ignore
                } else {
                    print "Failed parsing '$line'\n";
                }
                break;
            default:
                if (preg_match("/^\s*([a-z\-]+) (.*);$/", $line, $m)) {
                    $data[$current][$m[1]] = $m[2];
                } elseif (preg_match("/}/", $line, $m)) {
                    $current = 0;
                } else {
                    print "Failed parsing '$line'\n";
                }
        }
    }
    $ret = array();
    foreach ($data as $ip => $d) {
        if ($d['binding'] == "state active") {
            $ret[] = array(
                'name' => strtolower($d['name']),
                'ip' => $ip,
                'mac' => strtolower($d['mac'])
            );
        }
    }
    return $ret;
}

function get_dhcp_lease($config, $name)
{
    foreach (import_dhcp_leases($config) as $data) {
        if (($data['ip'] == $name) || ($data['name'] == $name) || ($data['mac'] == $name))
            return $data;
    }
}

// ----------- gestion des classes------------------------------
function is_eleve($config, $name)
{
    if (count(search_ad($config, $name, "member", "Eleves")) > 0)
        return true;
    else
        return false;
}

function is_prof($config, $name)
{
    if (count(search_ad($config, $name, "member", "Profs")) > 0)
        return true;
    else
        return false;
}

function is_pp(array $config, string $name)
{}

/**
 * liste les classes pour une classe, un eleve, un prof, ou tous
 *
 * @param array $config
 * @param string $name
 *            : eleve, prof, "*"
 * @return array $classe : le nom court des classes
 */
function list_classes(array $config, string $name)
{
    $ret = array();
    $classes = search_ad($config, $name, "classe");
    if (count($classes) == 0) {
        $classes = search_ad($config, $name, "equipe");
    }
    if (is_array($classes)) {
        foreach ($classes as $val) {
            $res = type_group($config, $val['cn']);
            $ret[] = $res['nom'];
        }
    }
    return $ret;
}

/**
 * liste les profs pour une classe, un eleve ou tous
 *
 * @param array $config
 * @param string $name
 *            : classe ou eleve ou "*"
 */
function list_profs($config, $name)
{
    $classes = list_classes($config, $name);
    $profs = array();
    foreach ($classes as $classe) {
        $equipe = search_ad($config, $classe, "equipe");
        // une seule equipe ?
        foreach ($equipe[0]['member'] as $prof) {
            $profs[] = $prof;
        }
    }
    return $profs;
}

/**
 * retourne la liste des eleves du prof, ou de la classe ou tous
 *
 * @param array $config
 * @param string $name
 *            : prof, classe, "*"
 * @return array [eleves]
 */
function list_eleves($config, $name)
{
    $classes = list_classes($config, $name);
    $eleves = array();
    foreach ($classes as $classe) {
        $res = search_ad($config, $classe, "classe");
        // une seul resultat ?
        foreach ($res[0]['member'] as $eleve) {
            $eleves[] = $eleve;
        }
    }
    return $eleves;
}

/**
 * retourne la liste des profs principaux de l'eleve, de la classe ou tous
 *
 * @param array $config
 * @param string $name
 *            : eleve, classe, "*"
 * @return array(pp)
 */
function list_pp($config, $name)
{
    $classes = list_classes($config, $name);
    $pps = array();
    foreach ($classes as $classe) {
        $res = search_ad($config, $classe, "pp");
        // une seule equipe ?
        if (isset($res[0]['member'])) {
            foreach ($res[0]['member'] as $pp) {
                $pps[] = $pp;
            }
        }
    }
    return $pps;
}

/**
 * teste si $prof est prof pour $name (eleve ou classe)
 *
 * @param array $config
 * @param string $name
 *            eleve, classe
 * @param string $prof
 *            prof
 * @return boolean
 */
function is_my_prof($config, $name, $prof)
{
    $profs = list_profs($config, $name);
    $dn = search_user($config, $prof);
    return in_array($dn['dn'], $profs);
}

/**
 * teste si $eleve est eleve pour $name (prof ou classe)
 *
 * @param array $config
 * @param string $name
 *            : prof, classe
 * @param string $eleve
 *            : eleve
 * @return boolean
 */
function is_my_eleve($config, $name, $eleve)
{
    $eleves = list_eleves($config, $name);
    $dn = search_user($config, $eleve);
    return in_array($dn['dn'], $eleves);
}

/**
 * teste si $prof est prof principal pour $name (eleve ou classe)
 *
 * @param array $config
 * @param string $name
 *            eleve, classe
 * @param string $prof
 *            prof
 * @return boolean
 */
function is_my_pp($config, $eleve, $prof)
{
    $pps = list_pp($config, $eleve);
    $dn = search_user($config, $prof);
    if (isset($dn['dn']))
        return in_array($dn['dn'], $pps);
    else
        return false;
}

/**
 * retourne le type de groupe
 *
 * @param array $config
 * @param string $name
 *            nom complet du groupe (Classe_TRUC) ou court (TRUC)
 * @return array nom et type du groupe. si les conventions de nommage et de placement ne sont pas vérifiées , retourne type=>"groupe"
 */
function type_group(array $config, string $name)
{
    $groups = search_ad($config, "*" . $name, "group");
    if (count($groups) > 0) {
        foreach ($groups as $group) {
            if (strcasecmp("Classe_" . $name, $group['cn']) || strcasecmp($name, $group['cn'])) {
                $m = array();
                $cn = explode("_", $group['cn'], 2);
                if (preg_match("/ou=(.*),ou=.*/i", $group['dn'], $m)) {
                    if ((strcasecmp($m[1], $cn[0]))) {
                        return array(
                            'nom' => $cn[1],
                            'type' => strtolower($cn[0])
                        );
                    } else {
                        return array(
                            'nom' => $name,
                            'type' => "groupe"
                        );
                    }
                }
            }
        }
    }
    return false;
}

/**
 * crée le groupe du type dommé
 *
 * @param array $config
 * @param string $name
 *            nom court sans le prefixe (Classe_, Equipe_)
 *            : nom du groupe
 * @param string $description
 * @param string $type
 *            classe (classe+equipe), matiere, cours, projet, groupe
 * @return boolean
 */
function create_group(array $config, string $name, string $description, string $type = "groupe")
{
    $res = type_group($config, $name);
    if (is_array($res) && ($type != $res['type'])) {
        // on ne cree pas de groupe du meme nom qu'une classe !
        return false;
    }
    if (($type == "classe") || ($type == "equipe")) {
        $classe = "Classe_" . $name;
        $ou = $config['classes_rdn'] . "," . $config['groups_rdn'];
        $res = groupadd($config, $classe, $ou, $description);
        $equipe = "Equipe_" . $name;
        $ou = $config['equipes_rdn'] . "," . $config['groups_rdn'];
        $res = groupadd($config, $equipe, $ou, "Equipe pédagogique de " . $description);
        $pp = "PP_" . $name;
        $ou = $config['equipes_rdn'] . "," . $config['groups_rdn'];
        $res = groupadd($config, $pp, $ou, "Profs principaux de " . $description);
    } elseif ($type == "other_group" || $type == "projet") {
        $classe = ucfirst($name);
        $ou = $config[$type . "s_rdn"] . "," . $config['groups_rdn'];
        // On crée l'OU si elle n'existe pas
        $ouName = explode("=", $config[$type . "s_rdn"]);
        if (! ouexist($config, $ouName[1], "Groups")) {
            ouadd($config, $ouName[1], "Groups");
        }
        $res = groupadd($config, $classe, $ou, $description);
    } else {
        $classe = ucfirst($type) . "_" . $name;
        $ou = $config[$type . "s_rdn"] . "," . $config['groups_rdn'];
        $res = groupadd($config, $classe, $ou, $description);
    }
    return $res;
}

/**
 * ajoute un eleve au groupe
 *
 * @param array $config
 * @param string $classe
 *            nom du groupe (forme courte admise)
 * @param string $user
 * @param string $type
 *            classe, cours, projet
 * @param bool $update
 *            : mettre à jour les partages (eleve) ou prof principal (prof)
 * @return boolean
 */
function add_user_group(array $config, string $classe, string $user, bool $update = false)
{
    $res = type_group($config, $classe);
    $type = $res['type'];
    $nom = $res['nom'];
    if ($type == "classe") {
        if (is_eleve($config, $user)) {
            $oldclasses = list_classes($config, $user);
            $res = groupaddmember($config, $user, "Classe_" . $nom);
            // un élève ne peut être que dans une classe !
            if ($res)
                foreach ($oldclasses as $oldclasse) {
                    groupdelmember($config, $user, $oldclasse);
                }
            if ($update) {
                $res = update_eleve($config, $user);
            }
        } elseif (is_prof($config, $user)) {
            $pp = $update;
            $res = groupaddmember($config, $user, "Equipe_" . $nom);
            if ($res) {
                $my_pp = is_my_pp($config, $classe, $user);
                if ((! $my_pp) && $pp) {
                    $res = groupaddmember($config, $user, "PP_" . $nom);
                    $res = groupaddmember($config, $user, "Profs_Principaux");
                    print "ajout";
                } elseif ($my_pp && (! $pp)) {
                    $res = groupdelmember($config, $user, "PP_" . $nom);
                    print "suppression";
                    // si il ne reste plus aucune classe ou il est pp
                    if (count(list_pp($config, $user)) == 0)
                        $res = groupdelmember($config, $user, "Profs_Principaux");
                }
            }
        }
    } else {
        $classe = ucfirst($type) . "_" . $nom;
        $res = groupaddmember($config, $user, $classe);
    }
    return $res;
}

/**
 * enlève un utilisateur d'un groupe
 *
 * @param array $config
 * @param string $classe
 * @param string $eleve
 * @param bool $update
 * @return boolean
 */
function remove_user_group(array $config, string $classe, string $user, bool $update = false)
{
    $res = type_group($config, $classe);
    $type = $res['type'];
    $nom = $res['nom'];
    if ($type == "classe") {
        if (is_eleve($config, $user)) {
            $res = groupdelmember($config, $user, "Classe_" . $nom);
        } elseif (is_prof($config, $user)) {
            $res = groupdelmember($config, $user, "Equipe_" . $nom);
            $res = groupdelmember($config, $user, "PP_" . $nom);
        }
    } else {
        $classe = ucfirst($type) . "_" . $nom;
        $res = groupaddmember($config, $user, $classe);
    }
    return $res;
}

function delete_group($config, $name)
{
    $res = type_group($config, $name);

    if (is_array($res)) {
        $type = $res['type'];
        $classe = $res['nom'];
    }

    if (($type == "classe") || ($type == "equipe")) {
        $name = "Classe_" . $classe;
        $res = groupdel($config, $name);
        $equipe = "Equipe_" . $classe;
        $res = groupdel($config, $equipe);
        $pp = "PP_" . $classe;
        $res = groupdel($config, $pp);
    } else {
        $res = groupdel($config, $name);
    }
    return $res;
}
/*
 * test si $name (un cn) est prof principal  de $classe
 * retourne un boolean
 */
function is_pp_this_classe($config, string $name, string $classe) {
    $lespp = array();
    $lespp = list_pp($config, $classe);
    return preg_grep("/$name/i", $lespp);
}

/*
 * retourne un tableau [cn,Prenom NoM,Nom,sexe,Ddn, dn],
 * trié par Nom croissant
 * des membres du groupe $groupe
 */

function search_people_group($config,string $groupe) {
    $res= search_ad($config,"*","memberof",$groupe);
    usort($res,"cmp_nom");
    return $res;
}
?>