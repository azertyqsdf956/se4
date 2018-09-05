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

    $res = list_classes($config, $FILTRE);
    if (count($res) > 0) {
        // Au moins une classe a ete trouvee
        foreach ($res as $Classe) {
            $cnClasse = "Classe_" . $Classe;
            print "<b>Mise &#224; jour du partage de la classe : $Classe</b><br>\n";

            if (! is_dir("/var/sambaedu/Classes/$cnClasse")) {
                if (is_dir("/var/sambaedu/Classes/.$cnClasse")) {
                    print("<b> restauration du repertoire de la classe $Classe</b><br>\n");
                    system("sudo /bin/mv /var/sambaedu/Classes/.$cnClasse /var/sambaedu/Classes/$cnClasse");
                } else {
                    print("<b> Cr&#233;ation du repertoire de la  classe $Classe</b><br>\n");
                    system("sudo /bin/mkdir /var/sambaedu/Classes/$cnClasse");
                }
            }
            if (is_dir("/var/sambaedu/Classes/$cnClasse")) {

                // test dossier echange
                if (is_dir("/var/sambaedu/Classes/$cnClasse/_echange")) {
                    $etat = system("getfacl /var/sambaedu/Classes/" . $cnClasse . "/_echange 2>/dev/null | grep \'^group:" . $cnClasse . ":rwx\$\' >/dev/null");
                } else {
                    $etat = 1;
                }

                $ret = system("sudo setfacl -R -P --set user::rwx,group::---,group:Equipe_$Classe:rwx,group:domain\ admins:rwx,mask::rwx,other::---,default:user::rwx,default:group::---,default:group:Equipe_$Classe:rwx,default:group:domain\ admins:rwx,default:mask::rwx,default:other::--- /var/sambaedu/Classes/$cnClasse");

                if ($etat == 0) {
                    system("sudo /usr/share/se3/scripts/echange_classes.sh $cnClasse actif", $out);
                }

                // Modifie le groupe par defaut
                system("sudo chgrp domain\ admins /var/sambaedu/Classes/$cnClasse");
                system("sudo chown www-admin /var/sambaedu/Classes/$cnClasse");

                print "  $cnClasse/_travail<br>\n";
                if (! is_dir("/var/sambaedu/Classes/$cnClasse/_travail")) {
                    system("sudo /bin/mkdir /var/sambaedu/Classes/$cnClasse/_travail");
                }
                if (is_dir("/var/sambaedu/Classes/$cnClasse/_travail")) {

                    system("sudo /usr/bin/setfacl -R -P -m group:Classe_$Classe:rx,default:group:$cnClasse:rx /var/sambaedu/Classes/$cnClasse/_travail");

                    // Modifie le groupe par defaut
                    system("sudo chgrp domain\ admins /var/sambaedu/Classes/$cnClasse/_travail");
                    system("sudo chown www-admin /var/sambaedu/Classes/$cnClasse/_travail");
                }
                print "  $cnClasse/_profs<br>\n";
                if (! is_dir("/var/sambaedu/Classes/$cnClasse/_profs")) {
                    system("sudo /bin/mkdir /var/sambaedu/Classes/$cnClasse/_profs");
                }
                // Modifie le groupe par defaut
                system("sudo chgrp domain\ admins /var/sambaedu/Classes/$cnClasse/_profs");
                system("sudo chown www-admin /var/sambaedu/Classes/$cnClasse/_profs");
                // premiere passe : on analyse les repertoires
                $out = 0;
                $res = array();
                @exec("sudo ls -d1 /var/sambaedu/Classes/" . $cnClasse . "/* 2>/dev/null", $res, $out);
                if ($out == 0) {
                    foreach ($res as $oldeleve) {
                        if ( ! preg_match("!^/var/sambaedu/Classes/$cnClasse/_!", $oldeleve)) {
                            // On met à jour les anciens eleves de la classe
                            $oldeleve = preg_replace("!^/var/sambaedu/Classes/$cnClasse/!", "", $oldeleve);
                            $login = invert_login($config, $oldeleve);
                            update_eleve($config, $login);
                            // Modifie le groupe par defaut
                            if (is_dir("/var/sambaedu/Classes/$cnClasse/$login")) {
                                system("sudo chgrp domain\ admins /var/sambaedu/Classes/$cnClasse/$login");
                                system("sudo chown www-admin /var/sambaedu/Classes/$cnClasse/$login");
                            }
                        }
                    }
                }
                // deuxieme passe : on cherche dans l'annuaire
                $members = list_eleves($config, $Classe);
                foreach ($members as $member) {
                    // D.B. On met met a jour les eleves actuels de la classe pas encore faits
                    $eleve = invert_login($config, ldap_dn2cn($member));
                    if (! is_dir("/var/sambaedu/Classes/$cnClasse/$eleve")) {
                        update_eleve($config, ldap_dn2cn($member));
                        // Modifie le groupe par defaut
                        if (is_dir("/var/sambaedu/Classes/$cnClasse/$eleve")) {
                            system("sudo chgrp domain\ admins /var/sambaedu/Classes/$cnClasse/$eleve");
                            system("sudo chown www-admin /var/sambaedu/Classes/$cnClasse/$eleve");
                        }
                    }
                }
                // Retrait du droit w &#224; Equipe_$CLASSE et ajout de rx au groupe $cnClasse (Classe_ ) sur le dossier /var/se3/Classes/$cnClasse
                system("sudo /usr/bin/setfacl -m group:Equipe_$Classe:rx,group:$cnClasse:rx /var/sambaedu/Classes/$cnClasse");
            }
        }
    } elseif (is_dir("/var/sambaedu/Classes/Classe_$Classe")) {
        if (preg_match("/grp_/", $Classe)) {
            print "Ancien groupe '$rep' ignor&#233;e. utilisez le menu groupe<br>\n";
        } else {
            // le répertoire existe, mais la classe non : on renomme en .Classe_truc, au cas ou
            print("Le groupe n'existe plus : Renommage de la classe $Classe. en .Classe_$Classe<br>\n");
            system("sudo /bin/mv /var/sambaedu/Classes/Classe_$Classe /var/sambaedu/Classes/.Classe_$Classe");
        }
    }
} elseif ($option == '-e') {
    // on traite direct un eleve
    update_eleve($config, $Classe);
}
?>