<?php
/**
 
 * Page qui teste les differents services
 * @Version $Id$ 
 * 
 * @Projet LCS / SambaEdu 
 * @auteurs Philippe Chadefaux  MrT
 * @Licence Distribue selon les termes de la licence GPL
 * @note 
 * Modifications proposees par Sebastien Tack (MrT)
 * Optimisation du lancement des scripts bash par la technologie asynchrone Ajax.
 * Modification du systeme d'infos bulles.(Nouvelle version de wz-tooltip) Ancienne version incompatible avec ajax
 * Externalisation des messages contenus dans les infos-bulles
 * Fonctions Tip('msg') et UnTip();
 * Nouvelle organisation de l'arborescence.
 * Passage des messages en php
 * Ce script contient les messages et les liens de la page /var/www/se3/tests.php
 */

/**
 *
 * @Repertoire: /tests/messages/fr/
 * file: tests_messages.php
 */

// Ce script contient les messages en francais des infos bulles.

// maj serveur
$tests_msg = array();
$tests_msg['msg_maj_nocx'] = 'Impossible de vérifier les mises à jour';
$tests_msg['msg_maj_ok'] = 'Etat : serveur à jour';
$tests_msg['msg_maj_ko'] = 'Le serveur n\'est pas à jour ! <br />Cliquer ici pour mettre à jour';
$tests_msg['link_maj_ko'] = '../majphp/majtest.php';
$tests_msg['msg_maj_info'] = 'Vérifie si votre serveur est à jour.<br>Si ce n\'est pas le cas, vous pouvez le mettre à jour à partir <a href=' . $tests_msg['link_maj_ko'] . '>d\'ici</a>';

// Controle installation dispos clonage

$tests_msg['link_clonage_ko'] = '../tftp/config_tftp.php';
$tests_msg['msg_clonage_ko'] = 'Cliquer ici pour mettre à jour les dispositifs du paquet se3-clonage';
$tests_msg['msg_clonage_nocx'] = 'Impossible de mettre à jour les dispositifs sans connexion à internet';
$tests_msg['msg_clonage_info'] = 'Les dispositfs du paquet se3-clonage sont indépendants et sont mis à jour depuis la page de configuration. <br><br>A lancer depuis cette <a href=\"' . $tests_msg['link_clonage_ko'] . '\">page</a>';

// ########################### CONNEXIONS ################################################/

// Verification des connexions

$tests_msg['msg_gateway_info'] = 'Test si la passerelle est joignable.<br> Si la réponse est négative, cela peut vouloir dire que votre routeur n\'est pas pingable, ou que celui-ci est mal configuré.<br>La passerelle est le routeur ou machine qui est le passage obligatoire pour aller sur internet. Si celui-ci est en erreur, mais que vous pouvez vous connecter à internet ne pas tenir compte de ce test.';

// Ping internet

$tests_msg['msg_net_info'] = 'Test si une machine sur internet est joignable.<br><br> Si la réponse est négative, vous devez vérifier votre connexion internet.<br><br> - Si la connexion à votre routeur était en erreur, vous devez commencer par corriger la route par defaut puis retester <br><br> - Si vous avez un Slis devant ne pas oublier de laisser internet accessible depuis cette machine<br><br> - Ne pas oublier de déclarer le proxy si vous en avez un, pour accèder à internet.';

// Verifie DNS
$tests_msg['msg_dns_nocx'] = 'Test de la résolution DNS impossible, sans connexion à internet';

$tests_msg['msg_dns_info'] = 'Vérifie si la résolution DNS est correcte<br>Si vous avez une erreur, vous devez vérifier que le fichier /etc/resolv.conf est bien configuré.';
$tests_msg['msg_dns2_info'] = 'Le nom DNS que vous avez donné à votre serveur Se3 ne peut &#234;tre trouvé. Sans un nom correct, vous ne pourrez pas faire la mise à jour des clés des registres. Vous pouvez soit ajouter dans le DNS de votre Slis ou LCS le serveur Se3, soit mettre l\'adresse IP à la place, par exemple http://172.16.0.2:909. Pour cela  <a href=\'../conf_params.php?cat=1\'>modifier le champ urlse3</a>';

// Contact serveur de mise a jour ftp

$tests_msg['msg_ftp_nocx'] = 'Impossible de tester la connexion au FTP des mises à jour, sans connexion à internet';
$tests_msg['msg_ftp_info'] = 'Test une connexion au serveur ftp de mises à jour.<br><br>Si la réponse est négative, et que les précédentes réponses<br /> étaient positives, vérifier d\'abord que le serveur ftp répond bien<br /> à partir d\'un simple navigateur.<br><br>Il se peut que celui-ci soit ne soit pas joignable (panne...!).';

// Verifie l'acces au serveur web pour la maj des cles

$tests_msg['msg_web_nocx'] = 'Impossible de tester la connexion au web, sans connexion à internet';
$tests_msg['msg_web_info'] = 'Teste si une machine sur internet est joignable sur le port 80 (Web).<br><br>Si la réponse est négative, vous devez vérifier votre connexion internet.<br><br>Si vous avez un Slis ou un autre proxy devant ne pas oublier de laisser <br /> internet accessible depuis cette machine et si vous n\'avez pas activé le<br /> proxy transparent, vérifier que dans /etc/profile le proxy est bien renseigné.';

// Verification de la connexion au serveur de temps
// 'Impossible de tester l\'accès au serveur de temps, sans connexion à internet'

$tests_msg['msg_ntp_ko'] = 'Le serveur de temps est injoignable.';
$tests_msg['msg_ntp_nocx'] = 'Impossible de tester le serveur de temps, sans connexion à internet';
$tests_msg['msg_ntp_info'] = 'Si le serveur de temps que vous avez indiqué  n\'est pas joingnable et si votre connexion internet semble correcte,<br><b> vérifier :</b><br><br> - Si vous avez un Slis de bien avoir comme serveur de temps le Slis lui m&#234;me (par exmple 172.16.0.1).<br> - Que votre proxy (routeur...etc) laisse passer en sorti, les connexions vers le port 123 UDP.<br><br>La modification s\'effectue <a href=../conf_params.php?cat=1>ici</a>';

$tests_msg['msg_time_info'] = 'Vérifie si votre serveur est à l\'heure par rapport au serveur de temps.<br>Cette différence doit rester inférieure à 60 sec';
$tests_msg['msg_time_ko'] = 'Cliquer ici pour mettre à l\'heure votre serveur';
$tests_msg['link_time_ko'] = '../test.php?action=settime';

// ######################## CONTROLE LES SERVICES ##################################//
// 'Cliquer ici pour mettre à l\'heure votre serveur'
// 'Vérifie si votre serveur est à l\'heure par rapport au serveur")." $ntpserv.<br>".gettext("La différence est actuellement de $voir sec. Cette différence doit rester inférieure à 60 sec'
// 'Cliquer ici pour configurer l\'expédition de mail'
// <a href=\"../conf_smtp.php\"><IMG style=\"border: 0px solid ;\" SRC=\"../elements/images/warning.png\"></a>

$tests_msg['msg_mail_info'] = 'Vérifie si votre serveur est configuré pour vous expédier des mails en cas de problème.<BR>Si ce n\'est pas le cas vous devez <a href=../conf_smtp.php>renseigner les informations permettant d\'envoyer des mails</a>';

// Test le serveur smb
// 'Cliquer ici pour essayer de relancer samba'
// <a href=\"../test.php?action=startsamba\"><IMG style=\"border: 0px solid ;\" SRC=\"../elements/images/critical.png\"></a>
$tests_msg['msg_samba_ko'] = 'Cliquer ici pour essayer de relancer samba';
$tests_msg['link_samba_ko'] = '../test.php?action=startsamba';
$tests_msg['msg_samba_info'] = 'Teste une connexion au domaine.<br /> Si celui-ci est en Echec, vérifiez qu\'il est bien démarré. Pour le démarrer /etc/init.d/samba start';

$tests_msg['msg_sid_ko'] = 'Attention : des sid différents sont déclarés dans l\'annuaire, mysql et le secrets.tdb';
$tests_msg['msg_sid_info'] = 'Teste la présence d\'éventuels doublons de SID.<br><br>Lancez la commande <b>/usr/share/se3/scripts/correctSID.sh</b> pour identifier et résoudre le problème de SID.';

// Test la base MySQL

$tests_msg['msg_mysql_info'] = 'Teste l\'intégrité de votre base MySQL, par rapport à ce qu\'elle devrait avoir.<br><br>Si cela est en erreur, lancer la commande <b>/usr/share/se3/sbin/testMySQL -v</b> afin de connaitre la cause du problème.';

// Controle si le dhcp tourne si celui-ci a ete installe
$tests_msg['msg_dhcp_ok'] = 'Serveur DHCP actif';
$tests_msg['msg_dhcp_ko'] = 'Serveur DHCP inactif';
$tests_msg['msg_dhcp_info'] = 'Test l\'état du serveur DHCP.<br> Pour l\'activer ou le désactiver aller sur <a href=dhcp/config.php>la page suivante</a>.';

// Test la presence d'un onduleur
// 'Etat de l\'onduleur'
$tests_msg['link_ondul_ok'] = '../cgi-bin/nut/upsstats.cgi';
$tests_msg['link_ondul_ko'] = '../ups/ups.php';
$tests_msg['msg_ondul_ok'] = 'Etat de l\'onduleur';
$tests_msg['msg_ondul_ko'] = 'Configurer un onduleur';
$tests_msg['msg_ondul_ko_info'] = 'Test la présence et l\'état d\'un onduleur<BR><BR>Il n\'y a pas d\'onduleur détecté sur ce serveur.<br>Cela peut provoquer la perte des données. On vous conseille d\'en installer un.';

// ################################### DISQUES #########################################################//
// Disques
//

// Securite
$tests_msg['link_secu_ko'] = '../test.php?action=updatesystem';
$tests_msg['msg_secu_ko'] = 'Cliquez sur ce bouton pour lancer la mise à jour système via l\'interface. Vous pouvez aussi effectuer la mise à jour en ligne de commande en lancant le script <b>se3_update_system.sh</b>';
$tests_msg['msg_secu_nocx'] = 'Impossible de tester les mises à jour de sécurité Debian, sans connexion à internet';
$tests_msg['msg_secu_info'] = 'Teste si ce serveur est bien à jour par rapport au serveur de sécurité de Debian.<br><br>Pour mettre à jour votre serveur, utilisez l\'interface ou lancez le script <b>se3_update_system.sh</b> dans une console<br><br>Attention, cela entraine aussi la mise à jour des paquets Se3';

// Clients
$tests_msg['msg_client_ko'] = 'Le mot de passe samba du compte adminse3 correspond pas avec le contenu de se3db, voir l\'aide pour pour corriger le problème';
$tests_msg['link_client_ko'] = '../test.php?action=setadminse3smbpass';

// $tests_msg['msg_client_info']='Vérifie que le mot de passe samba du compte adminse3 .<br><br>Si ce n\'est pas le cas, vous ne pourrez pas intégrer de nouvelles machines.<br><br>Dans ce cas pour reforcer ce mot de passe, taper la commande : <br><br><b>smbpasswd adminse3</b><br><br>Puis taper le mot de passe qui correspond à celui de la BDD.';

?>
