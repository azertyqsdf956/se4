<?php

/*
 * #!/usr/bin/perl -w
 *
 * # $Id$
 *
 *
 * # Encodage UTF-8
 * # Met a jour l'arborescence des partages Classes
 * # en definissant les acl basees sur les posixGroup Equipe_* et Classe_*
 * #
 * # syntaxe : updateClasses.pl -e|-c ALL|Classe|login
 * # - ALL : pour passer en revue toutes les classes (les dossiers manquant sont crees, aucun dossier n'est supprime)
 * # - NomClasse : par exemple 1-S1 pour la Classe Classe_1-S1 (si la classe n'existe pas dans l'annuaire, le rep Classe est supprime (renomme .Classe_1-S1) )
 * # - eleve : login d'un eleve, la Classe est lue dans l'annuaire
 * # Si le dossier de l'eleve est absent il est cree ( si .eleve existe, il est restaure)
 * # Si le dossier de l'eleve existait dans une autre classe, il est deplace et renomme archive les droits sont mis &#224; jour
 * # Si l'eleve n'est inscrit dans aucune classe, son dossier eleve est renomme .eleve s'il existait dans l'aborescence classes...
 * # D.B. Si l'eleve a un dossier dans 2 classes, il est deplace de dans la nouvelle et renomme Archive
 * # D.B.
 * # Jean Le Bail ( jean.lebail@etab.ac-caen.fr ) 10 juillet 2007
 * # Denis Bonnenfant (denis.bonnenfant@diderot.org) 7 octobre 2007 : inversion des noms et petites modifs
 * # Denis Bonnenfant (denis.bonnenfant@diderot.org) 3 septembre 2008 : Création du réeprtoire élève avant de dmigrer les dossiers de l'année d'avant
 * #
 * # renomme si necessaire les repertoires prenom.nom en nom.prenom afin de permettre une visualisation dans l'ordre des listes de classes
 *
 *
 *
 * # fonction qui teste le type de login et qui renvoie nom.prenom dans le cas d'un login prenom.nom, ou sinon le login
 * # si la fonction est appelle avec un login, elle cherche si il y a un répertoire à inverser
 * # sinon renvoie le login
 */
include "config.inc.php";
require_once "ldap.inc.php";
require_once "partages.inc.php";
include "ihm.inc.php";

require_once "lang.inc.php";

// HTMLPurifier
require_once ("traitement_data.inc.php");

if ($argc < 1 || in_array($argv[1], array(
    '--help',
    '-help',
    '-h',
    '-?'
))) {
    // ===========================================================
    $chaine = "USAGE : updateClasses.php -e|-c|-h ALL|Classe|login:\n";
    $chaine .= "       .  -c ALL : pour passer en revue toutes les classes (les dossiers manquant sont crees, aucun dossier n'est supprime)\n";
    $chaine .= "       . -c NomClasse : par exemple 1-S1 pour la Classe Classe_1-S1 (si la classe n'existe pas dans l'annuaire, le rep Classe est supprime (renomme .Classe_1-S1) )\n";
    $chaine .= "       . -e eleve : login d'un eleve, la Classe est lue dans l'annuaire\n";
    $chaine .= "       . 'y' ou 'n' selon que l'import est annuel ou non;\n";
    $chaine .= "       . 'y' ou 'n' selon que vous souhaitez seulement une simulation ou non;\n";
    $chaine .= "       . Si le dossier de l'eleve est absent il est cree ( si .eleve existe, il est restaure)\n";
    $chaine .= "       . Si le dossier de l'eleve existait dans une autre classe, il est deplace et renomme archive les droits sont mis à jour\n";
    $chaine .= "       . Si l'eleve n'est inscrit dans aucune classe, son dossier eleve est renomme .eleve s'il existait dans l'aborescence classes...\n";
    print $chaine;
    exit();
} else {
    $option = $argv[1];
    $Classe = $argv[2];
    echo $option . " " . $Classe;
}

if ($option == '-c') {
    if ($Classe == 'ALL') {
        $FILTRE = "*";
    } else {
        $FILTRE = "$Classe";
    }
    update_classes($config, $FILTRE);
} elseif ($option == '-e') {
    // on traite direct un eleve
    update_eleve($config, $Classe);
}
?>