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
function get_gpo_template_info(string $dir)
{
    $path = "/var/www/sambaedu/gpo/templates/" . $dir;
    $gptini = parse_ini_file($path . "/GPT.INI");
    return $gptini['version'];
}

/**
 * met à jour une gpo à partir des fichiers dans /var/www/sambaaedu/tmp
 *
 * utilise smbclient pour copier les fichiers.
 *
 * @param array $config
 * @param string $displayname
 *            : nom de la gpo
 * @param string $dir
 *            : dossier de la gpo
 * @return boolean
 */
function import_gpo(array $config, string $displayname, string $dir)
{
    $gpo = search_ad($config, $displayname, "gpo");
    if (count($gpo) > 0) {

        $path = "/var/www/sambaedu/gpo/templates/" . $dir;
        $gptini = parse_ini_file($path . "/GPT.INI");
        $gptini['version'] = $gpo['version'] + 0x10001;
        write_ini_file($gptini, $path . "/GPT.INI");
        // exec("rsync -a " . $path . " root@se4ad:/var/lib/samba/sysvol/" . strtoupper($config['domain']) . "/policies/" . $gpo['uuid'], $message, $ret);
        $command = "'cd " . strtoupper($config['domain']) . "/policies/" . $gpo['uuid'] . ";lcd " . $path . ";mput *'";
        exec("smbclient //se4ad/var/lib/samba/sysvol -k -c" . $command, $message, $ret);
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

function export_gpo(array $config, string $displayname)
{
    $gpo = search_ad($config, $displayname, "gpo");
    if (count($gpo) > 0) {
        $path = "/var/www/sambaedu/tmp/";
        $res = gpofetch($config, $gpo['uuid'], $path);
        if ($res) {
            exec("mv " . $path . "/" . $gpo['uuid'] . " " . $path . "/" . $displayname );
            return true;
        } else
            return false;
    } else {
        return false;
    }
}

?>