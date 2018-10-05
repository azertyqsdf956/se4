<?php

// ------------actions---------------------------------------------

/**
 * Récupère l'action programmée pour la machine, au boot ipxe, ou lors d'actions à distance
 * on récupère l'action et un token unique commun à une action et qui permet d'authentifier la requête
 *
 * @param array $config
 * @param string $mac
 *            : adresse mac, ip, ou nom de la machine
 * @return array : tableau des attributes de la machine, avec les valeurs par défaut pour 'netbootinitialization' (action)
 *         et 'netbootmachinefilepath' (token)
 */
function get_action(array $config, string $mac)
{
    $machine = search_machine($config, $mac);
    if (count($machine) > 0) {
        if (! isset($machine['netbootinitialization']))
            $machine['netbootinitialization'] = "default";

        if ($machine['netbootinitialization'] != "default") {
            if (! isset($machine['netbootmachinefilepath']))
                $machine['netbootmachinefilepath'] = "";
        }
    }
    return $machine;
}

/**
 * enregistre l'action pour une machine
 *
 * @param array $config
 * @param string $cn
 *            : nom ou ip ou mac de la machine
 * @param string $action
 *            : action à effectuer
 * @param string $token
 *            jeton d'identification de l'action
 * @return boolean
 */
function set_action(array $config, string $cn, string $action = "default", string $token = "")
{
    $machine = get_action($config, $cn);
    if ($machine['netbootinitialization'] != $action)
        $attrs['netbootinitialization'] = $action;

    if ($action != "default" && $action != "windows" && $action != "linux") {
        if ($machine['netbootmachinefilepath'] != $token)
            $attrs['netbootmachinefilepath'] = $token;
    } else {
        $attrs['netbootmachinefilepath'] = "";
    }
    return modify_ad($config, $machine['cn'], "computer", $attrs);
}

/**
 * verifie que l'action est authentifée pour la machine
 *
 * @param array $config
 * @param string $cn
 *            :nom ou ip ou mac de la machine
 * @param string $token
 *            : jeton d'identification
 * @return boolean
 */
function auth_action(array $config, string $cn, string $token)
{
    $machine = get_action($config, $cn);
    if (isset($machine['netbootmachinefilepath']))
        return ($machine['netbootmachinefilepath'] == $token);
    else 
        return false;
}

/**
 * retourne un jeton d'identification pour le boot iPXE en cas d'autentification avec un compte computer_is_admin
 * @param array $config
 * @param string $login
 * @param string $password
 * @return string $token : jeton unique, "" si non autorisé
 */
function login_action(array $config, string $login, string $password){
    $token = "";
    if (user_valid_passwd($config, $login, $password) && have_right($config, "computer_is_admin", $login)){
        $token = bin2hex(random_bytes(32));
    }
    return $token;
}
?>