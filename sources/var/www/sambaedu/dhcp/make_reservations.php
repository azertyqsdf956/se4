<?php
require ("config.inc.php");
require_once ("ldap.inc.php");
$config['login'] = "admin";
   
$res = export_dhcp_reservations($config);
print ($res);
?>