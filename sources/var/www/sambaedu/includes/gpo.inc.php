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
    return $gptini['Version'];
}

function list_gpo_templates()
{
    $path = "/var/www/sambaedu/gpo/templates/";
    // $dir = opendir($path);
    $gpos = scandir($path);
    $templates = array();
    foreach ($gpos as $gpo) {
        if (($gpo != ".") && ($gpo != "..")) {
            $templates[] = array(
                'displayname' => $gpo,
                'version' => get_gpo_template_info($gpo)
            );
        }
    }
    return $templates;
}

/**
 * met à jour une gpo à partir des fichiers dans /var/www/sambaaedu/gpo/templates
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
    $message = "";
    $ret = 0;
    $gpo = search_ad($config, $displayname, "gpo");
    if (count($gpo) > 0) {

        $path = "/var/www/sambaedu/gpo/templates/" . $dir;
        $version = $gpo[0]['versionnumber'] + 0x10001;
        $content = "[General]\r\nVersion=" . $version . "\r\ndisplayName=" . $displayname . "\r\n";
        $handle = fopen($path . "/GPT.INI", "w");
        fwrite($handle, $content);
        fclose($handle);

        $command = "'cd " . $config['domain'] . "/Policies/" . $gpo[0]['cn'] . ";lcd " . $path . ";prompt OFF;mput *'";
        exec("smbclient //se4ad/sysvol -k -c" . $command, $message, $ret);
        if ($ret == 0)
            return true;
        else
            return false;
    } else {
        $uuid = gpocreate($config, $displayname);
        if ($uuid) {
            import_gpo($config, $displayname, $dir);
            gposetlink($config, $config['ldap_base_dn'], $uuid);
        } else
            return false;
    }
}

function export_gpo(array $config, string $displayname)
{
    $gpo = search_ad($config, $displayname, "gpo");
    if (count($gpo) > 0) {
        $path = "/var/www/sambaedu/tmp/";
        $res = gpofetch($config, $gpo[0]['cn'], $path);
        if ($res) {
            exec("mv " . $path . "policy/" . $gpo[0]['cn'] . " \"/var/www/sambaedu/gpo/templates/" . $displayname . "\"");
            return true;
        } else
            return false;
    } else {
        return false;
    }
}

?>