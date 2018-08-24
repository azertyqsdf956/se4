<?php

require ("config.inc.php");
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