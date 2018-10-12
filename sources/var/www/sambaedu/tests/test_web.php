<?php

   /**

   * Test une requete sur le web wawadeb
   * @Projet LCS / SambaEdu
   * @Auteurs Equipe Sambaedu
   * @Licence Distribue sous la licence GPL
   * @Repertoire: /tests/
   * file: test_web.php
   */

require_once('entete_ajax.inc.php');
   $http=exec("cd /tmp; wget -q --tries=1 --timeout=2 https://ac-caen.fr && echo \$? | rm -f /tmp/index.html*",$out,$retour);

   if ($retour=="0") {
   	$ok="1";
   } else {
   	$ok="0";
   }
die($ok);
?>