<?php
/**

 * Page qui teste les differents services
 * @Version $Id$
    * @Projet LCS / SambaEdu
   * @Auteurs Equipe Sambaedu
   * @Licence Distribue sous la licence GPL
 * lancement des scripts bash par la technologie asynchrone Ajax.


 */
/**
 *
 * @Repertoire: /
 * file: test.php
 */
$clonage = "";
require_once "config.inc.php";
require ("entete.inc.php");

$prefix = "tests";
require_once ("$prefix/messages/" . $config['lang'] . "/" . $prefix . "_messages.php");

$action = isset($_GET['action']) ? $_GET['action'] : "";

// aide
$_SESSION["pageaide"] = "Informations_syst%C3%A8me#Diagnostic";

// Si pas se3_is_admin
if (!have_right($config, "se3_is_admin"))
    die(gettext("Vous n'avez pas les droits suffisants pour acc&#233;der &#224; cette fonction") . "</BODY></HTML>");


// if ($_GET['action'] == "updatesystem") {
// exec('/usr/bin/sudo /usr/share/se3/scripts/se3_update_system.sh --auto');
// unset($action);
// }
if ((isset($action)) && ($action == "updatesystem")) {
    $info_1 = gettext("Mise &#224; jour syst&#232;me lanc&#233;e, ne fermez pas cette fen&#234;tre avant que le script ne soit termin&#233;. vous recevrez un mail r&#233;capitulatif de tout ce qui sera effectu&#233;...");
    echo $info_1;
    system('sleep 1; /usr/bin/sudo /usr/share/sambaedu/scripts/sambaedu_update_system.sh --auto &');
    unset($action);
}
if ((isset($action)) && ($action == "settime")) {
    exec('/usr/bin/sudo /usr/share/sambaedu/sbin/settime.sh');
}
if ((isset($action)) && ($action == "startsamba")) {
    exec('/usr/bin/sudo /usr/share/sambaedu/scripts/services.sh samba restart');
}
if ((isset($action)) && ($action == "installsambaedu-domain")) {
    $info_1 = gettext("Mise &#224; jour lanc&#233;e, ne fermez pas cette fen&#234;tre avant que le script ne soit termin&#233;. vous recevrez un mail r&#233;capitulatif de tout ce qui sera effectu&#233;...");
    echo $info_1;
    system("/usr/bin/sudo /usr/share/sambaedu/scripts/install_sambaedu-module.sh sambaedu-domain");
}

if ((isset($action)) && ($action == "exim_mod")) {
    $fichier = "/etc/ssmtp/ssmtp.conf";
    $fp = fopen("$fichier", "w+");
    $DEFAUT = "
		root=$dc_root
		mailhub=$dc_smarthost
		rewriteDomain=$dc_readhost
		";
    fwrite($fp, $DEFAUT);
    fclose($fp);
    $action = "mail_test";
}

if ((isset($action)) && ($action == "mail_test")) {
    $dc_root = exec('cat /etc/ssmtp/ssmtp.conf | grep root= | cut -d= -f2');
    $subject = gettext("Test de la configuration de votre serveur Se3");
    $message = gettext("Message envoy&#233; par le serveur Se3");
    mail($dc_root, $subject, $message);
    unset($action);
}
?>
<script type="text/javascript" src="/elements/js/wz_tooltip_new.js"></script>
<script type="text/javascript" src="/tests/js/tests_messages_ajax.php"></script>
<script type="text/javascript" src="/tests/js/gest_messages.js"></script>
<script type="text/javascript" src="/tests/js/tests.js"></script>

<?php
/**
 * ******** Test de la conf du serveur *********************
 */
echo "<H1>" . gettext("Etat du serveur") . "</H1>";
$phpv2 = preg_replace("/[^0-9\.]+/", "", phpversion());
$phpv = $phpv2 - 0;

/**
 * ****************************************************
 */
// =======================================
// Affichage d'un lien de rafraichissement du cadre.
if (file_exists('/etc/sambaedu/temoin_test_refresh.txt')) {
    echo "<div style='position:fixed; top:5px; left:5px; width:20px; height:20px; border:1x solid black;'>\n";
    echo "<a href='" . $_SERVER['PHP_SELF'] . "'><img src='elements/images/rafraichir.png' width='16' height='16' border='0' alt='Rafraichir' /></a>\n";
    echo "</div>\n";
}
// =======================================

$os = exec("cat /etc/debian_version | cut -d. -f1-2");
$vers = exec("dpkg -s sambaedu-web-common|grep Version|cut -d ' ' -f2");
$samba_version = exec("dpkg -s samba|grep Version|cut -d ':' -f3|cut -d '+' -f1");
?>

<center>
    <TABLE border="1" width="80%">
        <TR>
            <TD colspan="3" align="center" class="menuheader">Version SambaEdu</TD>
        </TR>
        <TR>
            <TD>Version OS</TD>
            <TD align="center" colspan="2">
<?php
if ($os < "10.0") {
    echo "Stretch";
}
else {
    echo "unstable";
}
echo "<I> <img src=\"../elements/images/debian.png\">($os)</I></TD></TR>\n";
?>
            </TD>
        </TR>
        <TR>
            <TD>Version de Samba</TD>
            <TD align="center" colspan="2">
<?php echo $samba_version; ?>
            </TD>
        </TR>
        <TR>
            <TD>Mise &#224; jour de votre serveur SambaEdu <I>(Version actuelle <?php echo $vers; ?>)</I></TD>
            <TD align="center"><a id=link_maj href="#"><IMG id="check_maj"
                                                            style="border: 0px solid;" SRC="../elements/images/info.png"></a></TD>
            <TD align="center"><a id="help_maj_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></a></TD>
        </TR>

<?php
if (isset($config['clonage']) && $config['clonage']==1) {
    echo '<TR id="ligne_clonage">';
}
else {
    echo '<TR id="ligne_clonage" style="display: none;">';
}
?>
        <TD>Contr&#244;le des mise a jour des dispositifs de
            Se3-clonage</TD>
        <TD align="center"><a id=link_clonage href="#"><IMG id="check_clonage"
                                                            style="border: 0px solid;" SRC="../elements/images/info.png" /></a>
        </TD>
        <TD align="center"><A id="help_clonage_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></A></TD>
        </TR>
        <TR>
            <TD colspan="3" align="center" class="menuheader">Vérification
                des connexions</TD>
        </TR>
        <TR>
            <TD>V&#233;rifie la connexion &#224; la passerelle <I>(
<?php
//// Ping passerelle
$PING_ROUTEUR = `cat /etc/network/interfaces | grep gateway | grep -v broadcast | cut -d" " -f 2`;
$PING_ROUTEUR = trim($PING_ROUTEUR);
echo $PING_ROUTEUR;
?>
                    )</I>
            </TD>
            <TD align="center"><IMG id="check_gateway" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><a id="help_gateway_se3"><img name="action_image2"
                                                             src="../elements/images/system-help.png"></a></TD>
        </TR>

        <TR>
            <TD>V&#233;rification de la connexion &#224; internet</TD>
            <TD align="center"><IMG id="check_internet"
                                    style="border: 0px solid;" SRC="../elements/images/info.png"></TD>
            <TD align="center"><a id="help_net_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></a></TD>
        </TR>
        <TR>
            <TD>V&#233;rification de la r&#233;solution de nom (DNS)</TD>
            <TD align="center"><IMG id="check_dns" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><a id="help_dns_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></a></TD>
        </TR>
        <TR>
            <TD>V&#233;rification du nom DNS du serveur Sambaedu <span
                    id="urlsambaedu" style="font-style: italic;">(<?php $hostName=exec("hostname -f ");echo $hostName ?>)</span></TD>
            <TD align="center"><IMG id="check_dns_se3" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><a id="help_dns2_se3"><img name="action_image2"
                                                          src="../elements/images/system-help.png"></a></TD>
        </TR>

        <TR>
            <TD>V&#233;rifie l'acc&#232;s au web</TD>
            <TD align="center"><IMG id="check_web" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><a id="help_web_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></a></TD>
        </TR>
<!--        <TR>
            <TD>V&#233;rifie la connexion au serveur de temps</TD>
            <TD align="center"><IMG id="check_ntp" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><a id="help_ntp_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></a></TD>
        </TR>-->

        <TR>
            <TD colspan="3" align="center" class="menuheader">Contr&#244;le des
                services</TD>
        </TR>

<?php
$la = date("G:i:s d/m/Y");
?>
        <TR id="ligne_date" style="display: none;">
            <TD>Contr&#244;le la date et l'heure du serveur <I>(date actuelle <?php echo $la; ?>)</I></TD>
            <TD align="center"><A id="link_time"><img id="check_time"
                                                      style="border: 0px solid;" SRC="../elements/images/info.png"></A></TD>
            <TD align="center"><A id="help_time_se3"><img name="action_image2"
                                                          src="../elements/images/system-help.png"></A></TD>
        </TR>


        <TR>
            <TD>Configuration de l'exp&#233;dition des mails</TD>
            <TD align="center"><IMG id="check_mail" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_mail_se3"><img name="action_image2"
                                                          src="../elements/images/system-help.png"></A></TD>
        </TR>
        <TR>
            <TD>Etat du serveur Samba Version: <span id="smb_version"
                                                     style="font-style: italic;">(<?php echo $samba_version ?>)</span></TD>
            <TD align="center"><a id="link_samba" href="#"><IMG id="check_smb"
                                                                style="border: 0px solid;" SRC="../elements/images/info.png"></a></TD>
            <TD align="center"><a id="help_samba_se3"><img
                        src="../elements/images/system-help.png"></a></TD>
        </TR>

        <TR>
            <TD>Controle du SID samba</TD>
            <TD align="center"><IMG id="check_sid" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_sid_se3"><img name="action_image2"
                                                         src="../elements/images/system-help.png"></A></TD>
        </TR>
<!--        <TR>
            <TD>Etat de la base MySQL</TD>
            <TD align="center"><IMG id="check_mysql" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_mysql_se3"><img name="action_image2"
                                                           src="../elements/images/system-help.png"></A></TD>
        </TR>-->

        <TR id="ligne_dhcp" style="display: none;">

            <TD>Etat du serveur DHCP</TD>
            <TD align="center"><IMG id="check_dhcp" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_dhcp_se3"><img name="action_image2"
                                                          src="../elements/images/system-help.png"></A></TD>
        </TR>


        <TR>
            <TD>Onduleur</TD>
            <TD align="center"><A id="link_ondul"><IMG id="check_ondul"
                                                       style="border: 0px solid;" SRC="../elements/images/info.png"></A></TD>
            <TD align="center"><A id="help_ondul_se3"><img name="action_image2"
                                                           src="../elements/images/system-help.png"></A></TD>
        </TR>
        <TR>
            <TD colspan="3" align="center" class="menuheader">Etat des disques</TD>
        </TR>

        <TR>
            <TD>Partition : / <span id="space_disk1"></span><br></TD>
            <TD align="center"><IMG id="check_disk1" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_disk1"><img
                        src="../elements/images/system-help.png" /></A></TD>
        </TR>
        <TR>
            <TD>Partition : /var/sambaedu <span id="space_disk2"></span></TD>
            <TD align="center"><IMG id="check_disk2" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_disk2"><img
                        src="../elements/images/system-help.png"></A></TD>
        </TR>
        <TR>
            <TD>Partition : /home <span id="space_disk3"></span><br></TD>
            <TD align="center"><IMG id="check_disk3" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_disk3"><img
                        src="../elements/images/system-help.png"></A></TD>
        </TR>
        <TR>
            <TD>Partition : /var <span id="space_disk4"></span><br></TD>
            <TD align="center"><IMG id="check_disk4" style="border: 0px solid;"
                                    SRC="../elements/images/info.png"></TD>
            <TD align="center"><A id="help_disk4"><img
                        src="../elements/images/system-help.png"></A></TD>
        </TR>

        <TR>
            <TD colspan="3" align="center" class="menuheader">S&#233;curit&#233;</TD>
        </TR>
        <TR>
            <TD>Mises &#224; jour de s&#233;curit&#233; Debian</TD>
            <TD align="center"><A id="link_secu"><IMG id="check_secu"
                                                      style="border: 0px solid;" SRC="../elements/images/info.png"></A></TD>
            <TD align="center"><A id="help_secu_se3"><img name="action_image2"
                                                          src="../elements/images/system-help.png"></A></TD>
        </TR>
<!--        <TR>
            <TD colspan="3" align="center" class="menuheader">Clients</TD>
        </TR>-->
        <TR>
<!--            <TD>V&#233;rifie le compte d'int&#233;gration des clients</TD>-->
<!--            <TD align="center"><A id="link_client"><IMG id="check_client"
                                                        style="border: 0px solid;" SRC="../elements/images/info.png"></A></TD>-->
<!--            <TD align="center"><A id="help_client_se3"><img name="action_image2"
                                                            src="../elements/images/system-help.png"></A></TD>-->
        </TR>

    </TABLE>
    <TR>  <TD align="center"><A id="help_client_se3"></A><A id="help_mysql_se3"></A></TD></TR>
</center>



<!-- //Menu pour mail. -->
<div id="mail_menu" style="width: 100%; display: none;">
    <table width=100%>
        <tr>
            <td colspan=2 align=center bgcolor=#6699CC><font face=Verdana size=-1
                                                             color=#000000><b>Menu</b></font></td>
        </tr>
        <tr>
            <td><IMG width=15 height=15 SRC=../elements/images/command.png></td>
            <td onmouseover=chng(this, 0) onmouseout=chng(this, 1)><a
                    href=conf_smtp.php><font face=Verdana size=-1 color=#000000>Tester
                    envoi</font></a></td>
        </tr>
        <tr>
            <td><IMG width=15 height=15 SRC=../elements/images/comment.gif></td>
            <td onmouseover=chng(this, 0) onmouseout=chng(this, 1)><a
                    href=../conf_smtp.php><font face=Verdana size=-1 color=#000000>Configurer</font></a></td>
        </tr>
    </table>
</div>


<?php
echo "</center>";
require ("pdp2.inc.php");
// } // fin de pas se3_is_admin
?>
