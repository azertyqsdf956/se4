<?php

   /**
   * Librairie de fonctions utilisees dans l'interface d'administration

   * @Version $Id: functions.inc.php 9186 2016-02-21 01:02:50Z keyser $

   * @Projet  SambaEdu

   * @Note: Ce fichier de fonction doit etre appele par un include

   * @Licence Distribue sous la licence GPL
   */

   /**

   * file: functions.inc.php
   * @Repertoire: includes/
   */

//=================================================

function bind_ad_gssapi($config) {
   /*
    * établit une connexion avec l'AD en GSSAPI
    */
    $error = 0;    
	$url = "ldap://".$config['se4ad_name'].".".$config['domain'];
    $ds = ldap_connect($url, '389');
    if ($ds) {
        ldap_set_option ($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option ($ds, LDAP_OPT_REFERRALS, 0);
        $r = ldap_sasl_bind ($ds, null, null, 'GSSAPI');
        if ( ! $r ) {
       		 $error = gettext("Echec de l'Authentification.");
        }
    }  else {
    	$error = gettext("Erreur de connection kerberos au serveur AD");
    }
    return array ($ds,$r,$error);
}


/**
* Verification du couple login / mot de passe d'un utilisateur

* @Parametres
* @Return  true si le mot de passe est valide, false dans les autres cas
* @Modif pour AD SMB4
*/

function user_valid_passwd ($config, $login, $password ) {
  $url = "ldaps://".$config['domain'];
  $ret = false;
  if ($login==$config['ldap_admin_name']) {
	$login_dn = $config['dn']['admin'];
  } else {
        $login_dn = "cn=" . $login . "," . $config['dn']['people'];
  } 
  $ds = @ldap_connect ( $url, $config['ldap_port'] );
  if ( $ds ) {
    $r = @ldap_bind ( $ds, $login_dn, $password );
    if ( $r ) {
                $ret = true;
    } else $ret = gettext("Echec de l'Authentification de $login_dn.");
    @ldap_unbind ($ds);
    @ldap_close ($ds);
  } else $ret = gettext("Erreur de connection au serveur AD");
  return $ret;
}


function isauth()
{
    /* Teste si une authentification est faite
                - Si non, renvoie ""
                - Si oui, renvoie l'uid de la personne
    */

    $login="";
    session_name("Sambaedu");
    @session_start();
    $login= (isset($_SESSION['login'])?$_SESSION['login']:"");
    return $login;
}

function open_session($config, $login, $passwd,$al)
{
    global $urlauth, $authlink, $secook;
    global  $dbhost, $dbuser, $dbpass, $autologon, $REMOTE_ADDR;
    global $MsgError,$logpath,$defaultintlevel,$smbversion;

    $res=0;
    $loginauto="";

    // Initialisation
    $auth_ldap=0;

    if (($al!=1)&&("$autologon"=="1")){
		$logintstsecu=exec("sudo smbstatus -p | grep \"".$_SERVER['REMOTE_ADDR']."\" | grep -v root | grep -v nobody | grep -v adminse3  | grep -v unattend | wc -l");
		if ("$logintstsecu" == "1") {
			$loginauto=exec("sudo smbstatus -p |gawk '{if ($5==\"(".$_SERVER['REMOTE_ADDR'].")\") if ( ! index(\" root nobody unattend adminse3 \", \" \" $2 \" \")) {print $2;exit}}'");
		}

        # echo $loginauto . " __ smbstatus | grep $REMOTE_ADDR | grep home\  | head -n 1 | gawk -F' ' '{print $2}'";
        //$loginauto=exec("smbstatus | grep \"".$REMOTE_ADDR ."\" | head -n 1 | gawk -F' ' '{print $2}'");
        if ("$loginauto" != "") {
            $auth_ldap=1;
            $login=$loginauto;
        }
        //echo "-->";
    }

    if ($auth_ldap!=1) {
                        // decryptage du mot de passe
                        list ($passwd, $error,$ip_src,$timetotal) = decode_pass($passwd);
                        //echo $passwd;///exit;
                        // Si le decodage ne comporte pas d'erreur
                        if (!$error) {
                                $auth_ldap = user_valid_passwd ( $config, $login ,  $passwd);
                                if (!$auth_ldap) $error=4;
                        }
                        if ($error) {
                                // Log en cas d'echec
                                $fp=fopen($logpath."auth.log","a");
                                if($fp) {
                                        fputs($fp,"[".$MsgError[$error]."] ".date("j/m/y:H:i")."|ip requete : ".$ip_src."|remote ip : ".remote_ip()."|Login : ".$login."|TimeStamp srv : ".time()."|TimeTotal : ".$timetotal."\n");
                                        fclose($fp);
                                }
                        }
    }
    if ($auth_ldap) {
    session_name("Sambaedu");
    @session_start();
    $_SESSION['login']=$login;
    $res=1;
    }
    return $res;
}


//=================================================

/**
* Ferme la session en cours

* @Parametres
* @Return
*/

function close_session()
{
    //Destruction session php Sambaedu
    session_name("Sambaedu");
    @session_start();
    // On detruit toutes les variables de session
    $_SESSION = array();
    // On detruit la session sur le serveur.
    session_destroy();
    // Destruction du cookie de session
    setcookie("Sambaedu", "", time() - 3600, "/", "", 0);
}


/**
* Recherche si $nom est present dans le droit $type

* @Parametres
* @Return
*/



function ldap_get_right_search ($config, $login, $type, $search_filter, $ldap)
{
    $ret="N";
    $typearr=explode("|","$type");
    $i=0;
    while (($ret=="N") and ($i < count($typearr))) {
      $base_search="cn=".$typearr[$i]."," . $config['dn']["rights"];
      $search_attributes=array("cn");
      $result = @ldap_read($ldap, $base_search, $search_filter, $search_attributes);
      if ($result) {
        if (ldap_count_entries ($ldap,$result) == 1) $ret="Y";
        ldap_free_result($result);
      } else {
    	// Analyse pour les membres d'un groupe
    	// jLCF 18 > A quoi sert cette section ?
        $base_search="cn=".$typearr[$i]."," . $config['dn']["groups"];
        $result = @ldap_read($ldap, $base_search, "cn=$login", $search_attributes);
        if ($result) {
          if (ldap_count_entries ($ldap,$result) == 1) $ret="Y";
          ldap_free_result($result);
        }
      }
      $i++;
  }
    return $ret;
}




/**
* Determine si $login a le droit $type

* @Parametres
* @Return
*/


function ldap_get_right($config,$type, $login)
{

    $cn="cn=" . $login . "," . $config['dn']["people"];
    $cn_profs="CN=" . $login . ",ou=Profs,".$config['dn']["people"];
    $cn_eleves="CN=" . $login . ",ou=Eleves,".$config['dn']["people"];
    $cn_administratifs="CN=" . $login . ",ou=Administratifs,".$config['dn']["people"];


    $ret="N";

   // Connect and sasl bind
    list($ds,$r,$error) = bind_ad_gssapi($config);
    if ( $r ) {
       // Recherche du nom exact
       $search_filter = "(|(member=$cn)(member=$cn_profs)(member=$cn_eleves)(member=$cn_administratifs))";
       $ret=ldap_get_right_search ($config, $login, $type, $search_filter,$ds);
       // Recherche sur les GroupsOfNames d'appartenance
       $result1 = @ldap_list ( $ds, $config['dn']["groups"], "member=cn=$login,".$config['dn']["people"], array ("cn") );
       if ($result1) {
                	$info = @ldap_get_entries ( $ds, $result1 );
              		if ( $info["count"]) {
                            $loop=0;
                            while (($loop < $info["count"]) && ($ret=="N")){
                        		$search_filter = "(member=cn=".$info[$loop]["cn"][0].",".$config['dn']["groups"].")";
                        		$ret=ldap_get_right_search ($config, $type, $search_filter, $ds);
                        		$loop++;
                            }
                	}
                	@ldap_free_result ( $result1 );
        }
    	ldap_close ($ds);
    }
//  hack temporaire
    if ("$login" == "administrator") {
        $ret = "Y";
    }
    return $ret;
}


function menuprint($config, $login) {
    global $liens,$menu;
    for ($idmenu=0; $idmenu<count($liens); $idmenu++)
    {
        echo "<div id=\"menu$idmenu\" style=\"position:absolute; left:10px; top:12px; width:200px; z-index:" . $idmenu ." ";
        if ($idmenu!=$menu) {
            echo "; visibility: hidden";
        }
        echo "\">\n";

        echo "
        <table width=\"200\" border=\"0\" cellspacing=\"3\" cellpadding=\"6\">\n";
        $ldapright["se3_is_admin"]=ldap_get_right($config, "se3_is_admin", $login);
        $getintlevel = getintlevel();
        for ($menunbr=1; $menunbr<count($liens); $menunbr++)
        {
        // Test des droits pour affichage
            #if ($menunbr==1) $menutarget="_top";
            #else $menutarget="main";
            $menutarget="main";
            $afftest=$ldapright["se3_is_admin"]=="Y";
            $rightname=$liens[$menunbr][1];
            $level=$liens[$menunbr][2];
            if (($rightname=="") or ($afftest)) $afftest=1==1;
            else {
                //if ($ldapright["$rightname"]=="") $ldapright["$rightname"]=ldap_get_right($config, $rightname,$login);
                if ((!isset($ldapright["$rightname"]))||($ldapright["$rightname"]=="")) { $ldapright["$rightname"]=ldap_get_right($config, $rightname, $login);}
                $afftest=($ldapright["$rightname"]=="Y");
            }
            if ($level > $getintlevel) $afftest=0;
            if ($afftest)
            if (($idmenu==$menunbr)&&($idmenu!=0)) {
                echo "
                <tr>
                    <td class=\"menuheader\">
                        <p style='margin:2px; padding-top:2px; padding-bottom:2px'><a href=\"javascript:;\" onClick=\"P7_autoLayers('menu0');return false\"><img src=\"elements/images/arrow-up.png\" width=\"20\" height=\"12\" border=\"0\" alt=\"Up\"></a>
                        <a href=\"javascript:;\" onClick=\"P7_autoLayers('menu" . $menunbr .  "');return false\">" . $liens[$menunbr][0] . "</a></p>
                    </td>
                    </tr>
                    <tr>
                    <td class=\"menucell\">";
                for ($i=3; $i<count($liens[$menunbr]); $i+=4) {
                    // Test des droits pour affichage
                    $afftest=$ldapright["se3_is_admin"]=="Y";
                    $rightname=$liens[$menunbr][$i+2];
                    $level=$liens[$menunbr][$i+3];
                    if (($rightname=="") or ($afftest)) $afftest=1==1;
                    else {
                        if ((!isset($ldapright["$rightname"]))||($ldapright["$rightname"]=="")) {$ldapright["$rightname"]=ldap_get_right($config, $rightname, $login);}
                        $afftest=($ldapright[$rightname]=="Y");
                    }
                    if ($level > $getintlevel ) $afftest=0;
                    if ($afftest) {
                    	echo "<img src=\"elements/images/typebullet.png\" width=\"30\" height=\"11\" alt=\"\">";
		    	// Traite yala pour ne pas avoir deux target
		    	if (preg_match('#yala#',$liens[$menunbr][$i+1])) {
                        	echo "<a href=\"" . $liens[$menunbr][$i+1] . "\">" . $liens[$menunbr][$i]  . "</a><br>\n";
                   	} else {
                        	echo "<a href=\"" . $liens[$menunbr][$i+1] . "\" TARGET='$menutarget'>" . $liens[$menunbr][$i]  . "</a><br>\n";
		   	}
		   }
                } // for i : bouche d'affichage des entrees de sous-menu
                echo "
                    </td></tr>\n";
            } else
            {
                echo "
                <tr>
                    <td class=\"menuheader\">
                    <p style='margin:2px; padding-top:2px; padding-bottom:2px'><a href=\"javascript:;\" onClick=\"P7_autoLayers('menu" . $menunbr .  "');return false\">
                    <img src=\"elements/images/arrow-down.png\" width=\"20\" height=\"12\" border=\"0\" alt=\"Down\">". $liens[$menunbr][0] ."</a></p>
                    </td></tr>\n";
            }
        } //for menunbr : boucle d'affichage des entrees de menu principales

        echo "
        </table>
</div>\n";
    } // for idmenu : boucle d'affichage des differents calques
} // function menuprint

/**
* Retourne le niveau de l'interface

* @Parametres
* @Return
*/

function getintlevel()
{
    /* Lis le niveau d'interface dans la table session */
    session_name("Sambaedu");
    @session_start();
    $ret=(isset($_SESSION['level'])?$_SESSION['level']:1);
    return $ret;
}


//=================================================

/**
* Change le niveau d'interface dans la table session

* @Parametres
* @Return
*/

function setintlevel($new_level)
{
    session_name("Sambaedu");
    @session_start();
    $_SESSION['level']=$new_level;
}


function mktable ($title, $content)
{
	echo "<H3>$title</H3>";
	echo $content;
}


?>
