<?php

   /**

   * Test une requete sur le web wawadeb
   * @Projet LCS / SambaEdu
   * @Auteurs Equipe Sambaedu
   * @Licence Distribue sous la licence GPLlancement des scripts bash par la technologie asynchrone Ajax.

    * @Licence Distribue sous la licence GPL


   */

   /**

   * @Repertoire: /tests/
   * file: test_web.php
   */



require_once('entete_ajax.inc.php');
   $http=exec("cd /tmp; wget -q --tries=1 --timeout=2 http://sambaedu.org && echo \$? | rm -f /tmp/index.html.1*",$out,$retour);

   if ($retour=="0") {
   	$ok="1";
   } else {
   	$ok="0";
   }
die($ok);
?>
