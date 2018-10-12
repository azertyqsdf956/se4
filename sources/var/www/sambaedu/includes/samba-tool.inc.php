<?php
/**
 * Librairie de fonctions utilisees dans l'interface d'administration

 * @Version $Id: samba-tool.inc.php  2018-28-09  jlcf $

 * @Projet  SambaEdu

 * @Note: Ce fichier de fonction doit etre appele par un include

 * @Licence Distribue sous la licence GPL
 */

/**
 * file: samba-tool.inc.php
 *
 * @Repertoire: includes/
 */

// =============================================================
// Ensemble de fonctions destinées à remplacer les scripts sudo perl
// pour les opérations d'écritures dans l'AD SambaEdu

/*
 * function useradd ($prenom, $nom, $userpwd, $naissance, $sexe, $categorie, $employeeNumber) : Return $cn if succes.
 *
 * function userdel ($cn) : Return true if userdel succes false if userdel fail
 *
 * function groupadd ($cn, $inou, $description) : Return true if group is create false in other cases
 *
 * function groupdel ($cn) : Return true if group is delete false in other cases
 *
 * function groupaddmember ( $cn, $ingroup) : Return true if cn is add in ingroup false in other cases
 *
 * function groupdelmember ($cn, $ingroup) : Return true if cn is remove of ingroup false in other cases
 *
 * A faire si nécessaire :
 * function grouplist ($filter)
 * function groupaddlistmembers ( $cnlist, $ingroup)
 *
 */
require_once ("siecle.inc.php");

/*
 * Fonctions de siecle.inc.php utilisées dans samba-tool.inc.php
 * useradd() -> creer_cn($nom,$prenom) //modifiée
 * -> verif_employeeNumber($employeeNumber) // modifiée
 */
function sambatool($config, $command, &$message)
{
    $RET = 0;
    exec("/usr/bin/samba-tool $command -k yes -H ldap://" . $config['se4ad_name'], $message, $RET);
    return $RET;
}

function userexist($config, $cn)
{
    /*
     * Return true if user exist false if not exist
     */
    $message = array();
    $command = "user list";
    $RES = sambatool($config, $command, $message);
    if ($RES == 0) {
        if (in_array($cn, $message))
            return true;
        else
            return false;
    } else {
        return false;
    }
}

function useradd($config, $cn, $prenom, $nom, $userpwd, $naissance, $sexe, $categorie, $employeeNumber)
{
    /*
     * $sexe : M ou F
     * $categorie : Eleves ou Profs ou Administratifs
     * $naissance : AAAAMMJJ
     *
     * Return $cn if succes.
     */
    $office = $naissance . "," . $sexe;

    if (! isset($userpwd)) {
        $userpwd = $naissance;
    }
    $userpwd = escapeshellarg($userpwd);
    $prenom = escapeshellarg(ucfirst($prenom));
    $nom = escapeshellarg(ucfirst($nom));

    if (empty($employeeNumber)) {
        // Pas de champ job-title pour employeeNumber dans ce cas
        $command = "user create '$cn' $userpwd --use-username-as-cn --given-name=$prenom --surname=$nom --mail-address='$cn@" . $config['domain'] . "' --physical-delivery-office='$office'";
        if ($categorie != '')
            $command = $command . " --userou='ou=$categorie," . $config['people_rdn'] . "'";
    } else {
        $command = "user create '$cn' $userpwd --use-username-as-cn --given-name=$prenom --surname=$nom --mail-address='$cn@" . $config['domain'] . "' --job-title='$employeeNumber' --physical-delivery-office='$office'";
        if ($categorie != '')
            $command = $command . " --userou='ou=$categorie," . $config['people_rdn'] . "'";
    }
    $message = array();
    $RES = sambatool($config, $command, $message);
    if ($RES == 0) {
        $newcn = explode("'", $message[0]);
        // Ajout a un groupe principal
        $RES = groupaddmember($config, $newcn[1], $categorie);
        return true;
    } else 
        return false;
}

function userdel($config, $cn)
{
    /*
     * Return true if userdel succes false if userdel fail
     */
    if (userexist($config, $cn)) {
        $message = array();
        $command = "user delete " . escapeshellarg($cn);
        $RES = sambatool($config, $command, $message);
        if ($RES == 0)
            return true;
        else
            return false;
    } else
        return false;
}

function usersetpassword($config, $cn, $password, $change = false)
{
    /*
     * Return true if password succes false if userdel fail
     */
    if (userexist($config, $cn)) {
        $message = array();
        $command = "user setpassword " . escapeshellarg($cn) . " --newpassword=" . escapeshellarg($password);
        if ($change)
            $command .= " --must-change-at-next-login";
        $RES = sambatool($config, $command, $message);
        if ($RES == 0)
            return true;
        else
            return false;
    } else
        return false;
}

/*
 * Gestion des OU
 */
function ouexist($config, $ou, $ouparent)
{

    /*
     * Return true if OU exist false in other cases
     */
    $contenu = array(
        "ou"
    );

    list ($ds, $r, $error) = bind_ad_gssapi($config);

    if ($r) {
        $ret = ldap_search($ds, "OU=$ouparent," . $config['ldap_base_dn'], "(ou=$ou)", $contenu);
        $info = ldap_get_entries($ds, $ret);
        if ($info["count"] > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        echo "Echec du bind sasl";
        return false;
    }
}

function ouadd($config, $ou, $ouparent)
{

    /*
     * Return true if OU is create or if there already exists false in other cases
     */

    // Ajoute le OU si il n'existe pas
    if (! ouexist($config, $ou, $ouparent)) {
        // Prépare les données
        $info["ou"] = "$ou";
        $info["name"] = "$ou";
        $info["objectclass"] = "top";
        $info["objectclass"] = "organizationalUnit";
        // Ajout
        list ($ds, $r, $error) = bind_ad_gssapi($config);
        // echo "DBG >> OU=$ouparent," . $config['ldap_base_dn'];
        $r = ldap_add($ds, "OU=$ou,OU=$ouparent," . $config['ldap_base_dn'], $info);
        ldap_close($ds);
        if (ouexist($config, $ou, $ouparent)) {
            return true;
        } else {
            return false;
        }
    } else {
        // le OU existe déja
        return true;
    }
}

function oudel($config, $ou, $ouparent)
{
    /*
     * Return true if OU is remove false in other cases
     */
    if (ouexist($config, $ou, $ouparent)) {
        list ($ds, $r, $error) = bind_ad_gssapi();
        // Verifier si le OU est vide !
        if (! ouexist($config, $ou, $ouparent)) {
            // On efface le OU
            $r = ldap_delete($ds, "OU=$ou,OU=$ouparent," . $config['ldap_base_dn']);
            ldap_close($ds);
        }
        if (! ouexist($ou, $ouparent)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/*
 * Samba-Tool
 * Available subcommands:
 * add - Creates a new AD group.
 * addmembers - Add members to an AD group.
 * delete - Deletes an AD group.
 * list - List all groups.
 * listmembers - List all members of an AD group.
 * removemembers - Remove members from an AD group.
 */
function grouplist($filter)
{

    /*
     * Return a array of cn répondant au critere filter
     */
}

function grouplistmembers($config, $cn)
{

    /*
     * Return array of member's cn
     */
    $message = array();
    $command = "group listmembers '$cn'";
    $res = sambatool($config, $command, $message);

    if ($res == 0)
        return $message;
    else
        return false;
}

function groupexist($config, $cn)
{

    /*
     * Return true if cn group exist
     */
    $message = array();
    $command = "group list ";
    $RES = sambatool($config, $command, $message);
    if ($RES == 0) {
        $key = array_search($cn, $message);
        if (! empty($key))
            return true;
        else
            return false;
    } else
        return false;
}

function groupadd($config, $cn, $inou, $description)
{

    /*
     * Principe :
     * samba-tool group add Classe_TARCU --groupou='ou=2TC,ou=groups' --description="Groupe Classe TARCU"
     * La commande retourne en cas de succes : Added group Classe_TARCU
     */

    /*
     * $cn : cn du groupe, exemple Classe_TARCU
     * $inou : ou de destination dans ou=Groups,ou=$inou,cn=$cn
     * $description : la description du groupe
     */

    /*
     * Return true if group is create false in other cases
     */
    if (! empty($cn) && ! empty($inou) && ! empty($description)) {

        // creation du ou si il n'existe pas
        // if ( !ouexist($inou,$dn["groups"]) ) {
        // ouadd ($inou, $dn["groups"]);
        // }
        $message = array();
        $command = "group add " . escapeshellarg($cn) . " --groupou=" . escapeshellarg($inou) . " --description=" . escapeshellarg($description);
        $RES = sambatool($config, $command, $message);
        if ($RES == 0)
            return true; 
        else 
            return false;
    } else {
        return false;
    }
}

function groupdel($config, $cn)
{

    /*
     * Principe : samba-tool group delete Classe_TARCU
     * La commande retourne en cas de succes : Deleted group Classe_TARCU
     */

    /*
     * $cn : cn du groupe a supprimer
     */

    /*
     * Return true if group is delete false in other cases
     */
    $message = array();
    $command = "group delete " . escapeshellarg($cn);
    $RES = sambatool($config, $command, $message);

    if ($RES == 0) {
        $group = explode(" ", $message[0]);
        if ($group[2] == $cn) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function groupaddmember($config, $cn, $ingroup)
{

    /*
     * Return true if cn is add in ingroup false in other cases
     */

    // le cn et le groupe exist ?
    if (userexist($config, $cn) && groupexist($config, $ingroup)) {
        // Ajout du cn in group
        $message = array();
        $command = "group addmembers " . escapeshellarg($ingroup) . " " . escapeshellarg($cn);
        $RES = sambatool($config, $command, $message);

        if ($RES == 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function groupaddlistmembers($cnlist, $ingroup)
{}

function groupdelmember($config, $cn, $ingroup)
{

    /*
     * Return true if cn is remove of ingroup false in other cases
     */

    // le cn et le groupe exist ?
    if (userexist($config, $cn) && groupexist($config, $ingroup)) {
        // Remove du cn in group
        $message = array();
        $command = "group removemembers " . escapeshellarg($ingroup) . " " . escapeshellarg($cn);
        $RES = sambatool($config, $command, $message);

        if ($RES == 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

?>
