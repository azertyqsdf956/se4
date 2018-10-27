<?php

/*
 * fonctions de manipultion des GPO pour sambaedu
 * @Projet SambaEdu
 *
 * @Note: Ce fichier de fonction doit etre appele par un include
 *
 * @Licence Distribue sous la licence GPL
 *
 * file: gpo.inc.php
 *
 * @Repertoire: includes/
 */
/**
 * met à jour une gpo à partir des fichiers dans /var/www/sambaaedu/tmp
 * utilise rsync pour copier les fichiers afin de ne pas écraser des modifs plus récentes de l'utilisateur.
 * @TODO A tester
 * 
 * @param array $config
 * @param string $displayname : nom de la gpo
 * @param string $dir : dossier de la gpo 
 * @return boolean
 */
function update_gpo(array $config, string $displayname, string $dir)
{
    $gpo = search_ad($config, $displayname, "gpo");
    if (count($gpo) > 0) {
        $path = "/var/www/sambaedu/tmp/" . $dir;
        $gptini = parse_ini_file($path . "/GPT.INI");
        $gptini['version'] = $gpo['version'] + 0x10001;
        write_ini_file($gptini, $path . "/GPT.INI");
        exec("rsync -a " . $path . " root@se4ad:/var/lib/samba/sysvol/" . strtoupper($config_domain) . "/policies/" . $gpo['uuid'], $message, $ret);
        if ($ret == 0)
            return true;
        else
            return false;
    } else {
        $uuid = gpocreate($config, $displayname);
        if ($uuid)
            update_gpo($config, $displayname, $dir);
        else
            return false;
    }
}

?>