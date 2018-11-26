<?php
require ("config.inc.php");
require_once ("samba-tool.inc.php");
require_once ("ldap.inc.php");
//require_once ("partages.inc.php");
//require_once ("siecle.inc.php");

// require_once ("functions.inc.php");
// c'est qui le patron ?
$config['login'] = "admin";

// Avoir la fiche du gus
$user = search_user($config, "mollef");
echo 'tableau utilisateur';
var_dump($user);

// Groupes d'appartenance et droits (filtrage nécessaire ensuite) - voir page people.php
echo "Groupes d'appartenance et droits";
$memberof = $user['memberof'];
echo "<pre>";
print_r($memberof);
echo "</pre>";
$numbers= count($user['memberof']);
echo "il y en a" . $numbers . " exactement";

//Toutes les fiches sous forme de tableau pour un group donné ici  les profs
$lesprofs = search_ad($config, "*", "memberof", "Profs");
var_dump($lesprofs);

//tableau de profs

$list_prof_grp = array();
foreach ($lesprofs as $key => $value) {
    $list_prof_grp[$key] = $value["cn"];
}

//
//Lister les membres d'un groupe donné
$filter="profs";
$people = search_people_group($config, $filter);








// Lister les droit d'un user
// $res = list_rights($config, "denis.bonnenfant");
// var_dump($res);


// Avoir la liste des utilisateurs avec un droit donné
$right="se3_is_admin";
$mp_all = list_members_rights($config,$right);
var_dump($mp_all);
//

//Autre possibilité sans interet
//$groupe = "no_Trash_user";
//$res= search_ad($config,"*","memberof",$groupe);
//var_dump($res);


//
//
//
////$tmp_tab_no_Trash_user = list_members_rights($config, "no_Trash_user");
//var_dump($tmp_tab_no_Trash_user);


// // 
// As tu le droit de lire ceci ?
// if (have_right($config, "se3_is_admin", "denis.bonnenfant")) print "OK";
// var_dump(list_parc($config, "*", "all"));

// var_dump(type_parc($config, "salle_b8"));
// var_dump(list_members_parc($config, "salle_b8"));
// var_dump(list_parc($config, "*"));


$filter="(cn=profs)";
var_dump(filter_group($config, $filter));
//
//

//

//$res = search_ad($config, "*", "right");
//var_dump($res);
exit();



/*
 *
 * $ret = create_machine($config, "machine-test2", "ou=printers");
 *
 *
 * $res = import_dhcp_reservations($config);
 * foreach($res as $host){
 * if (!search_ad($config, $host['cn'], "machine")){
 * create_machine($config, $host['cn'], "ou=printers");
 * }
 * $ret = set_dhcp_reservation($config, $host['cn'], array(
 * 'networkaddress' => $host['networkaddress'],
 * 'iphostnumber' => $host['iphostnumber']
 * ));
 * if ($ret){
 * print "reservation : ".$host['cn']." ip : ".$host['iphostnumber']."<br>\n";
 * ob_flush();
 * }
 * }
 */


 var_dump(search_ad($config, "(&(objectclass=group)(cn=*))", "filter"));
  print 'list_profs($config, "3_A")';

 /* var_dump(get_dhcp_reservation($config, "tm-a23-len2"));
 * var_dump(export_dhcp_reservations($config));
 * var_dump(type_group($config, "Equipe_CIM2"));
 * var_dump(type_group($config, "CIM2"));
 * var_dump(create_group($config, "CIM3", "essai de classe", "classe"));
 * print 'var_dump(is_eleve($config, "eleve.test"));';
 * var_dump(is_eleve($config, "eleve.test"));
 * var_dump(add_user_group($config, "CIM3", "eleve.test"));
 * print 'list_eleves($config, "CIM3")';
 * var_dump(list_eleves($config, "CIM3"));
 * var_dump(is_my_pp($config, "CIM3", "prof.test"));
 * print 'var_dump(is_eleve($config, "prof.test"));';
 * var_dump(is_eleve($config, "prof.test"));
 *
 * print 'var_dump(add_prof_group($config, "CIM3", "prof.test", true));';
 * var_dump(add_user_group($config, "CIM3", "prof.test", true));
 *
 * var_dump(list_profs($config, "CIM3"));
 * print 'list_pp($config, "CIM3")';
 * var_dump(list_pp($config, "CIM3"));
 * var_dump(is_my_pp($config, "eleve.test", "prof.test"));
 * print 'var_dump(add_prof_group($config, "CIM3", "prof.test", true));';
 * var_dump(add_user_group($config, "CIM3", "prof.test", false));
 * print 'list_pp($config, "CIM3")';
 * var_dump(list_pp($config, "CIM3"));
 * print 'var_dump(is_my_pp($config, "CIM3", "prof.test"));';
 * var_dump(is_my_pp($config, "CIM3", "prof.test"));
 * var_dump(is_my_eleve($config, "prof.test", "eleve.test"));
 * var_dump(is_my_prof($config, "eleve.test", "prof.test"));
 * print 'var_dump(list_classes($config, "eleve.test"));';
 * var_dump(list_classes($config, "eleve.test"));
 * //print 'var_dump(delete_group($config, "CIM3"));';
 */
// var_dump(delete_group($config, "CIM3"));
// print 'var_dump(list_classes($config, "*"));';
// var_dump(list_classes($config, "*"));

// print 'list_pp($config, "prof.tes")';
// var_dump(list_pp($config, "prof.test"));

/*
 * $prenom = "Aurelien";
 * $nom = "Sezettre";
 * $prenom = remplace_accents(traite_espaces($prenom));
 * $nom = remplace_accents(traite_espaces($nom));
 * $cn = verif_nom_prenom($config, $nom, $prenom);
 * if ($cn){
 * verif_et_corrige_user($config, $cn, "12101998", "M", "n");
 * verif_et_corrige_nom($config, $cn, $prenom, "n");
 * verif_et_corrige_pseudo($config, $cn, $nom, $prenom, "y", "n");
 * }
 * $cn = creer_cn($config, $nom, $prenom);
 * //var_dump($config, "denis.bonnenfant", "user");
 * //$password = createRandomPassword(8, 1);
 * //create_user($config, $cn, $prenom, $nom, $password, "12101998", "M", "eleves", "12858");
 *
 */

// $cn = verif_nom_prenom($config, "l obry", "bernadette");
// echo $cn;

/*
 * $user = search_user($config, "denis.bonnenfant");
 * $en = $user['employeenumber'];
 * $attr = array( 'title'=>"")
 * $res = modify_ad($config)
 */

/*
 *
 * $args = array(
 * 'xml',
 * '/var/www/sambaedu/tmp/fichier_eleves',
 * '/var/www/sambaedu/tmp/fichier_sts',
 * '',
 * 'n',
 * 'y',
 * '0.39990900_1536320029',
 * '766078603',
 * 'non',
 * 'n',
 * 'n',
 * 'n',
 * 'y',
 * 'n',
 * 'n',
 * 'y',
 * 'n'
 * );
 * create_echo_file($args[6]);
 * $res = import_comptes($config, $args);
 * echo $res;
 *
 */

/*
 * $users = search_ad($config, "*", "user");
 * foreach ($users as $user){
 * verif_et_corrige_mail($config, $user['cn']);
 * }
 */

// $users = search_ad($config,"*", "user", $config['dn']['trash']);
// print count($users);
/*
 * foreach ($users as $user){
 * $type = type_user($config, $user['cn']);
 * move_ad($config, $user['cn'], "cn=".$user['cn'].",ou=".$type.",".$config['dn']['trash'], "user" );
 * groupdelmember($config, $user['cn'], $type);
 * }
 */

/*
 * $users =search_ad($config, "*", "user", $config['dn']['people'], array('whencreated'));
 * $i = 0;
 * foreach($users as $user){
 * $date = preg_replace("/\.0Z/", "", $user['whencreated']);
 * if ($date > 20180711150050){
 * print $user['prenom'].",".$user['nom'].",".$user['email'].",".createRandomPassword(8, 1)."<br>";
 * $i++;
 * }
 * }
 */
/*require "windows.inc.php";

  
 $res = update_xml_unattend($config, __DIR__ . "/ipxe/Win10/unattend.xml", "toto-pc");
header("Content-type: text/plain");
echo $res;
*/
/*
  
 $res = create_machine($config, "w10-samedi", $config['computers_rdn'], "test");

$res = search_machine($config, "c2:19:88:2c:10:44", true);
*/

  
// Positionner un pass
//$res = usersetpassword($config, "eleve3.test", "azerty0", true);

  //tester un pass
//  $res = user_valid_passwd($config, "eleve3.test", "azerty0");
//$classes = list_classes($config, "eleve3.test");
//var_dump($res);

?>
