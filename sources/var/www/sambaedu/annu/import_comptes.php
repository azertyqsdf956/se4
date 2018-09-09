#!/usr/bin/php

<?php
/* $Id: import_comptes.php 9509 2016-08-31 21:50:56Z keyser $ */
/*
 * Page d'import des comptes depuis les fichiers CSV/XML de Sconet
 * Auteur: Stéphane Boireau (ex-Animateur de Secteur pour les TICE sur Bernay/Pont-Audemer (27))
 * Portage LCS : jean-Luc Chrétien jean-luc.chretien@tice;accaen.fr
 * Dernière modification: 03/12/2011
 * modifs Christian Westphal 17/03/2013 christian.westphal@ac-strasbourg.fr
 */
//include "se3orlcs_import_comptes.php";
include "config.inc.php";
require_once "samba-tool.inc.php";
require_once "ldap.inc.php";
require_once "siecle.inc.php";

if ($argc < 17 || in_array($argv[1], array(
    '--help',
    '-help',
    '-h',
    '-?'
))) {
    // ===========================================================
    $chaine = "USAGE: Vous devez passer en paramétres (dans l'ordre):\n";
    $chaine .= "       . Le type du fichier 'csv' ou 'xml';\n";
    $chaine .= "       . le chemin du fichier élèves;\n";
    $chaine .= "       . le chemin du fichier XML de STS EDT;";
    $chaine .= "       . le préfixe (CLG_, LYC_, LP_, LEGT_) si vous en avez besoin;\n";
    $chaine .= "       . 'y' ou 'n' selon que l'import est annuel ou non;\n";
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez seulement une simulation ou non;\n";
    $chaine .= "       . le suffixe pour le fichier HTML result.SUFFIXE.html généré;\n";
    $chaine .= "       . une chaine aléatoire pour le sous-dossier de stockage des CSV;\n";
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez créer les CSV ou non.\n";
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez chronométrer les opérations ou non.\n";
    
    // ===========================================================
    // AJOUTS: 20070914 boireaus
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez créer des Equipes vides ou non.\n";
    $chaine .= "                    (avec 'n' elles sont créées et peuplées)\n";
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez créer Cours ou non.\n";
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez créer Matières ou non.\n";
    // ===========================================================
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez corriger ou non les attributs\n";
    $chaine .= "                    gecos, cn, sn et givenName si des différences sont trouvées.\n";
    $chaine .= "       . 'y' ou 'n' selon qu'il faut utiliser ou non un fichier F_UID.txt\n";
    $chaine .= "       . 'y' ou 'n' selon qu'il faut alimenter un groupe Professeurs Principaux\n";
    // ===========================================================
    
    echo $chaine;
    
    // Contrôler les champs affectés...
    if (isset($config["admin_mail"])) {
        $adressedestination = $config["admin_mail"];
        $sujet = "ERREUR: import_comptes.php ";
        $message = $chaine;
        $entete = "From: " . $config["admin_mail"];
        mail("$adressedestination", "$sujet", "$message", "$entete");
    }
    
    exit();
}

// Récupération des variables
array_shift($argv);
$res = import_comptes($config, $argv);
exit($res);
?>
