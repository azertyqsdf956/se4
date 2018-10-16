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
$fp = @fsockopen("www.ac-caen.fr","80" , $errno, $errstr, 2);
if (!$fp) {
    $ok="0";
} else {
    $ok="1";
    fclose($fp);
}
die($ok);
?>