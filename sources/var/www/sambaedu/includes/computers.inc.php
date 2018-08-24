<?php

/**
 * Librairie de fonctions utilisees dans l'interface d'administration

 * @Projet  SambaEdu

 * @Note: Ce fichier de fonction doit etre appele par un include

 * @Licence Distribue sous la licence GPL
 */

/**
 *
 * file: computers.inc.php
 *
 * @Repertoire: includes/
 */

// =================================================
// Parcs
/*
 * type d'un parc
 *
 */
function type_parc($config, $parc)
{
    if (search_ad($config, $parc, "salle")) {
        return "salle";
    } elseif (search_ad($config, $parc, "parc")) {
        return "parc";
    } else {
        return false;
    }
}

/*
 * ajoute le type de parc dans les attributs des parcs
 * @ parametres : array (parcs)
 * @ parametres : type - auto, salle, parc
 * @ return = : array ( parcs)
 */
function set_type_parc($config, $parcs, $type = "auto")
{
    if (is_array($parcs)) {
        $ret = array();
        foreach ($parcs as $key => $parc) {
            if ($type == "auto") {
                $val = type_parc($config, $parc['cn']);
            } else {
                $val = $type;
            }
            $ret[$key]['type'] = $val;
        }
    } else {
        $ret = false;
    }
    return $ret;
}

/*
 * liste des parcs d'appartenance
 * @ parametres : member - machine à tester, null ou * si tous les parcs"
 * @ parametres : type - "salle", "parc", "all(defaut)"
 * @ return : array[]{dn, cn, description, type}
 */
function list_parc($config, $member, $type = "all")
{
    if ($type == "salle") {
        if ($member == null) {
            return set_type_parc(search_ad($config, "*", "salle"), $type);
        } else {
            return array_search($member, list_parc($config, null, "salle"));
        }
    } elseif ($type == "parc") {
        if ($member == null) {
            $member = "*";
        }
        return set_type_parc(search_ad($config, $member, "parc"), $type);
    } else {
        $ret1 = list_parc($config, $member, "salle");
        $ret2 = list_parc($config, $member, "parc");
        if ($ret1 && $ret2) {
            return array_merge($ret1, $ret2);
        } elseif ($ret1) {
            return $ret1;
        } else {
            return $ret2;
        }
    }
}

/*
 * creation de parc
 *
 */
function create_parc($config, $parc, $description = "", $type = "salle")
{
    if ($type == "salle") {
        $res = ouadd($config, $parc, $config['dn']['computers']);
    } else {
        $res = groupadd($config, $parc, $config['dn']['parcs'], $description);
    }
    return $res;
}

/*
 * suppression de parc
 * Si il s'agit d'une salle il faut commencer par deplacer les machines vers l'ou parent
 */
function delete_parc($config, $parc)
{
    $type = type_parc($config, $parc);
    if ($type == "salle") {
        $machines = list_members_parc($config, $parc, true);
        foreach ($machines as $machine) {
            move_ad($machine['cn'], $machine['cn'] . "," . $config['dn']['computers'], "machine");
        }
    }
    $ret = delete_ad($config, $parc, $type);
    if ($ret) {
        $res = search_delegations($config, $parc);
        if ($res){
            foreach ($res as $delegation){
                groupdel($config, $delegation['cn']);
            }
        }
    }
    return $ret;
}

/*
 * liste le contenu d'un parc
 * @ parametres : parc à lister
 * @ parametre : attrs - booleen enregistrements complets ou liste
 * @ return : liste des machines, enregistrements complets des machines, false
 */
function list_members_parc($config, $parc, $attrs = false)
{
    $type = type_parc($config, $parc);
    $ret = array();
    if ($type == "salle") {
        $res = search_ad($config, "*", "machine", "ou=" . $parc . "," . $config['dn']['computers']);
        if ($attrs) {
            $ret = $res;
        } else {
            foreach ($res as $machine) {
                $ret[] = $machine['cn'];
            }
        }
    } elseif ($type = "parc") {
        $res = search_ad($config, $parc, "group");
        foreach ($res[0]['member'] as $machine) {
            $dn_elements = preg_split('/,/', 1);
            $cn = preg_split('/=/', $dn_elements);
            if ($attrs) {
                $ret[] = search_ad($config, $cn[1], "machine");
            } else {
                $ret[] = $cn[1];
            }
        }
    }
    return $ret;
}

/*
 * ajoute un membre au parc
 * pour les salles il faut déplacer les machines
 */
function add_member_parc($config, $parc, $member)
{
    if (type_parc($config, $parc) == "salle") {
        return move_ad($member, $member . ",ou=" . $parc . "," . $config['dn']['computers'], "machine");
    } else {
        return groupaddmember($config, $member, $parc);
    }
}

/*
 * enlève un membre du parc
 * $res=
 */
function remove_member_parc($config, $parc, $member)
{
    if (type_parc($config, $parc) == "salle") {
        return move_ad($member, $member . "," . $config['dn']['computers'], "machine");
    } else {
        return groupdelmember($config, $member, $parc);
    }
}

/*
 * teste l'appartenance au parc
 *
 */
function is_member_parc($config, $parc, $member)
{
    $res = list_parc($config, $member, "all");
    if ($res) {
        foreach ($res as $value) {
            if ($value['cn'] == $parc)
                return true;
        }
        return false;
    }
}

/*
 * recherche les parcs délégués à l'utilisateur courant
 * @ return array(cn, level) ou false
 */
function search_delegate_parcs($config)
{
    $res = search_ad($config, $config['login]'], "delegation");
    if ($res) {
        foreach ($res as $key => $group) {
            $delegation = explode('_', group['cn'], 2);
            $parc[$key]['cn'] = $delegation[1];
            $parc[$key]['level'] = $delegation[0];
        }
        return $parc;
    }
    return false;
}

/*
 * retourne le niveau de délégation de l'utlisateur courant pour une machine ou un parc
 * @parametre : nom du parc ou de la machine
 * @return : "manage", "view", false
 */
function is_delegate($name)
{
    $parcs = search_delegate_parcs($config);
    $ret = false;
    if ($parcs) {
        foreach ($parcs as $parc) {
            if ($parc['cn'] == $name)
                return $parc['level'];
            if (is_member_parc($config, $parc['cn'], $name)) {
                if ($parc['level'] == "manage") {
                    return "manage";
                } else {
                    $ret = "view";
                }
            }
        }
    }
    return $ret;
}
/* cherche les delegations pour un parc
 * 
 */
function search_delegations($parc)
{
    $ou_delegation = "ou=delegations," . $config['dn']['rights'];
    $res = search_ad($config, "*_" . $parc, "group", $ou_delegations);
    return $res;
}

function create_delegation($config, $parc, $name, $level)
{
    $delegation = $level . "_" . $parc;
    $ou_delegation = "ou=delegations," . $config['dn']['rights'];
    $res = search_ad($config, $delegation, "group", $ou_delegations);
    $ret = true;
    if (! $res) {
        $ret = groupadd($config, $delegation, $ou_delegation);
    }
    if ($ret)
        $ret = groupaddmember($config, $delegation, $name);
    return $ret;
}

/*
 * supprime une delegation pour un utilisateur ou groupe
 *
 */
function delete_delegation($config, $parc, $name, $level)
{
    $delegation = $level . "_" . $parc;
    $ou_delegation = "ou=delegations," . $config['dn']['rights'];
    $res = search_ad($config, $delegation, "group", $ou_delegations);
    $ret = groupdelmember($config, $name, $delegation);
    if (count($res[0]['member']) == 0) {
        $ret = groupdel($config, $delegation);
    }
    return $ret;
}

// ----------------------------------------------------
// dhcp
function get_dhcp_reservation($config, $machine)
{
    $res = search_ad($config, $machine, "machine");
    if (isset($res[0]['iphostnumber']) && isset($res[0]['networkaddress'])) {
        return array(
            iphostnumber => $res[0]['iphostnumber'],
            networkaddress => $res[0]['networkaddress']
        );
    } else {
        return false;
    }
}

/*
 * enregistre une reservation dhcp dans AD
 * @parametres : $machine
 * @parametres : $reservation - tableau {ip, mac}
 * @return : true si ok
 */
function set_dhcp_reservation($config, $machine, $reservation)
{
    return modify_ad($config, $machine, "machine", $reservation);
}

/*
 * Importe le fichier des reservations /etc/sambaedu/reservations.inc vers l'AD
 * @return : array machine, ip, mac
 */
function import_dhcp_reservations($config)
{
    $contents = file_get_contents("/etc/sambaedu/reservations.inc");
    $contents = explode("\n", $contents);
    $index = 0;
    $data = array();
    $m = array();
    $record = false;
    foreach ($contents as $line) {
        if (preg_match("/^\s*(|#.*)$/", $line, $m)) {
            // on saute les commentaires
        } elseif (preg_match("/^host (.*)$/", $line, $m) && (! $record)) {
            $index ++;
            $data[$index]['cn'] = $m[0];
        } elseif (preg_match("/^{/", $line)) {
            $record = true;
        } elseif (preg_match("/^\s*hardware ethernet\s*(.*)\s*;$/", $line, $m)) {
            $data[$index]['networkaddress'] = $m[0];
        } elseif (preg_match("/^\s*fixed-address\s*(.*)\s*;$/", $line, $m)) {
            $data[$index]['iphostnumber'] = $m[0];
        } elseif (preg_match("/}*/", $line, $m)) {
            $record = false;
        } else {
            print "Erreur ligne '$line'\n";
            break;
        }
    }
}

function export_dhcp_reservations($config)
{
    $reservations = "/etc/sambaedu/reservations.inc";
    $content = "# reservations exportees autmatiquement de l'annuaire AD\n";
    $machines = search_ad($config, "*", "machines", "all");
    foreach ($machines as $machine) {
        $res = get_dhcp_reservation($config, $machine);
        if ($res) {
            $content .= "host " . $machine['cn'] . "\n";
            $content .= "{\n";
            $content .= "hardware ethernet " . $res['networkaddress'] . "\n";
            $content .= "fixed-address " . $res['iphostnumber'] . "\n";
            $content .= "{\n";
        }
    }
    if (! $handle = fopen($reservations, "w")) {
        die("Erreur d'ecriture des reservtions se4 : $reservations");
    }
    $res = fwrite($handle, $content);
    fclose($handle);
    return true;
}
?>
