<?php
/*
 * fonctions de manipultion des GPO pour sambaedu
 * @Projet  SambaEdu

 * @Note: Ce fichier de fonction doit etre appele par un include

 * @Licence Distribue sous la licence GPL
 
 * file: gpo.inc.php
 *
 * @Repertoire: includes/ */
function update_gpo (array $config, string $displayname, string $dir){
    $gpo = search_ad($config, $displayname, "gpo");
    if (count($gpo) > 0){
        $gptini = parse_ini_file($dir . "/GPT.INI");
        
        exec("rsync -a $dir root@se4ad:/var/lib/samba/sysvol/policies");
    }
}

?>