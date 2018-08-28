<?php

require ("config.inc.php");
require_once ("samba-tool.inc.php");
require_once ("ldap.inc.php");

//require_once ("functions.inc.php");
$config['login'] = "admin";
//$res = search_user($config, "denis.bonnenfant");
//var_dump($res);
//$res = list_rights($config, "denis.bonnenfant");
//var_dump($res);
//if (have_right($config, "se3_is_admin", "denis.bonnenfant")) print "OK";
//var_dump(list_parc($config, "*", "all"));

//var_dump(type_parc($config, "salle_b8"));
//var_dump(list_members_parc($config, "salle_b8"));
//var_dump(list_parc($config, "*"));

 
/* $res = import_dhcp_reservations($config);
foreach($res as $host){
    $ret = set_dhcp_reservation($config, $host['cn'], array(
        'networkaddress' => $host['networkaddress'],
        'iphostnumber' => $host['iphostnumber']
        ));
}
*/
var_dump(search_ad($config, "tm-a23-len2", "machine"));
var_dump(get_dhcp_reservation($config, "tm-a23-len2"));
var_dump(export_dhcp_reservations($config));
var_dump(type_group($config, "Equipe_CIM2"));
var_dump(type_group($config, "CIM2"));
var_dump(create_group($config, "CIM3", "essai de classe", "classe"));
print 'var_dump(is_eleve($config, "eleve.test"));';
var_dump(is_eleve($config, "eleve.test"));
var_dump(add_user_group($config, "CIM3", "eleve.test"));
print 'list_eleves($config, "CIM3")';
var_dump(list_eleves($config, "CIM3"));
var_dump(is_my_pp($config, "CIM3", "prof.test"));
print 'var_dump(is_eleve($config, "prof.test"));';
var_dump(is_eleve($config, "prof.test"));

print 'var_dump(add_prof_group($config, "CIM3", "prof.test", true));';
var_dump(add_user_group($config, "CIM3", "prof.test", true));
print 'list_profs($config, "CIM3")';
var_dump(list_profs($config, "CIM3"));
print 'list_pp($config, "CIM3")';
var_dump(list_pp($config, "CIM3"));
var_dump(is_my_pp($config, "eleve.test", "prof.test"));
print 'var_dump(add_prof_group($config, "CIM3", "prof.test", true));';
var_dump(add_user_group($config, "CIM3", "prof.test", false));
print 'list_pp($config, "CIM3")';
var_dump(list_pp($config, "CIM3"));
print 'var_dump(is_my_pp($config, "CIM3", "prof.test"));';
var_dump(is_my_pp($config, "CIM3", "prof.test"));
var_dump(is_my_eleve($config, "prof.test", "eleve.test"));
var_dump(is_my_prof($config, "eleve.test", "prof.test"));
print 'var_dump(list_classes($config, "eleve.test"));';
var_dump(list_classes($config, "eleve.test"));
print 'var_dump(delete_group($config, "CIM3"));';
var_dump(delete_group($config, "CIM3"));
