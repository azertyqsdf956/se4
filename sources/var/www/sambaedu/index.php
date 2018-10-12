<?php

   /**

   * Page d'accueil redirige vers auth ou blank ou test
   * @Version $Id$

   * @Projet LCS / SambaEdu

   * @auteurs Olivier Lecluse "wawa"
   * @auteurs  jLCF >:>  jean-luc.chretien@tice.ac-caen.fr
   * @auteurs   oluve  olivier.le_monnier@crdp.ac-caen.fr
   * @auteurs Equipe Tice academie de Caen

   * @Licence Distribue selon les termes de la licence GPL

   * @note

   */

   /**

   * @Repertoire: /
   * file: index.php
   */

session_name("Sambaedu");
@session_start();

require_once 'config.inc.php';
require_once 'ldap.inc.php';
//require 'functions.inc.php';

$login=isauth();
if ($login =="" ) {
    header("Location:$urlauth");
    exit;
}

$registred=2;
set_config("registred",2); ///A quoi ça sert ? mrfi
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>Interface SE3</title>
</head>
<!--FRAMESET COLS="220,*" BORDER="0"-->
<FRAMESET COLS="227,*">
<FRAME SRC="menu.php" NAME="menu" frameborder="0" /><!--/FRAME-->

<?php if (have_right($config, "se3_is_admin"))  {

    if (isset($config['affiche_etat']) && $config['affiche_etat']==1) {
     if ($config['registred'] <= 1)  { ?>
	<FRAME SRC="blank.php" NAME="main" frameborder="0" /><!--/FRAME-->
<?php
     } else {
?>
	<FRAME SRC="test.php" NAME="main" frameborder="0" /><!--/FRAME-->
<?php   }
    } else { ?>
	<FRAME SRC="blank.php" NAME="main" frameborder="0" /><!--/FRAME-->
 <?php }
   } else { ?>
   <FRAME SRC="individuel.php?uid=$login" NAME="main" frameborder="0" /><!--/FRAME-->
<?php } ?>
</FRAMESET>
</html>

