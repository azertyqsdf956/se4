<?php

/**
 * Fonctions de gestion des partages

 * @Projet SambaEdu

 * @Auteurs Denis Bonnenfant @ Equipe Sambaedu

 * @Note: Ce fichier de fonction doit etre appele par un include

 * @Licence Distribue sous la licence GPL
 */
/**
 *
 * file: partages.inc.php
 *
 * @Repertoire: includes/
 */
function invert_login($config, $login)
{
    $nom = explode(".", $login);
    if (count($nom) == 2) {
        // on inverse
        $eleve = $nom[1] . "." . $nom[0];
        $res = search_user($config, $login);
        if ($res) {
            // c'est un login
            $out = 0;
            $res = array();
            @exec("sudo -s ls -d1 /var/sambaedu/Classes/Classe_*/" . $login . " 2>/dev/null", $res, $out);
            if ($out == 0) {
                // $REP = explode(" ", $res);
                if (count($res) > 0) {
                    foreach ($res as $tmpClasse) {
                        $tmpClasse = preg_replace("!^/var/sambaedu/Classes/Classe_(.+)/$login$!", "\${1}", $tmpClasse);
                        print "Inversion de $login -> $eleve<br>\n";
                        system("sudo /bin/mv '/var/sambaedu/Classes/Classe_$tmpClasse/$login' '/var/sambaedu/Classes/Classe_$tmpClasse/$eleve'");
                        print "classe : $tmpClasse\n";
                        print "inversion de /var/sambaedu/Classes/Classe_" . $tmpClasse . "/" . $eleve . " avec " . $eleve . " faite<br>\n";
                    }
                }
            }
        }
        return $eleve;
    } else {
        return $login;
    }
}

function cree_rep(array $config, string $login, $OldClasse = "")
{
    // fait les repertoires
    // Recherche de l'Eleve dans les Classes
    $Classe = "";
    $cnClasse = "";
    $eleve = invert_login($config, $login);
    $res1 = list_classes($config, $login);
    if (count($res1) == 1) {
        $Classe = $res1[0];
        $cnClasse = "Classe_" . $Classe;
        if ($OldClasse != "") {
            if ("Classe_$OldClasse" != $cnClasse) {
                print "  Changement de classe de '$eleve' : Classe_$OldClasse -> $cnClasse.<br>\n";
                if (! is_dir("/var/sambaedu/Classes/$cnClasse/$eleve")) {
                    system("sudo /bin/mkdir  '/var/sambaedu/Classes/$cnClasse/$eleve'");
                }
                if (! is_dir("/var/sambaedu/Classes/$cnClasse/$eleve/Archives")) {
                    system("sudo /bin/mkdir  '/var/sambaedu/Classes/$cnClasse/$eleve/Archives'");
                }
                if (! is_dir("/var/sambaedu/Classes/$cnClasse/$eleve/Archives/$eleve")) {
                    system("sudo /bin/mv -f '/var/sambaedu/Classes/Classe_$OldClasse/$eleve' '/var/sambaedu/Classes/$cnClasse/$eleve/Archives/'");
                } else {
                    system("sudo /bin/rm -fr '/var/sambaedu/Classes/Classe_$OldClasse/$eleve'");
                }
            }
        }
        if (! is_dir("/var/sambaedu/Classes/$cnClasse/$eleve")) {
            if (is_dir("/var/sambaedu/Classes/$cnClasse/.$eleve")) {
                print "Restauration du dossier '$cnClasse/.$eleve'.<br>\n";
                system("sudo /bin/mv '/var/sambaedu/Classes/$cnClasse/.$eleve' '/var/sambaedu/Classes/$cnClasse/$eleve'");
            } else {
                print "Création du dossier '$cnClasse/$eleve'.\n";
                system("sudo /bin/mkdir '/var/sambaedu/Classes/$cnClasse/$eleve'");
            }
        }
        if (is_dir("/var/sambaedu/Classes/$cnClasse/$eleve")) {
            print "Mise en place des droits sur $cnClasse/$eleve.<br>\n";

            system("sudo /usr/bin/setfacl -R -P --set user::rwx,group::---,user:$login:rwx,group:Equipe_$Classe:rwx,group:domain\ admins:rwx,mask::rwx,other::---,default:user::rwx,default:group::---,default:group:Equipe_$Classe:rwx,default:group:domain\ admins:rwx,default:mask::rwx,default:other::---,default:user:$login:rwx /var/sambaedu/Classes/$cnClasse/$eleve");
            // Modifie le groupe par defaut
            system("sudo chgrp domain\ admins /var/sambaedu/Classes/$cnClasse/$eleve");
            system("sudo chown www-admin /var/sambaedu/Classes/$cnClasse/$eleve");
        }
    } elseif (count($res1) > 1) {
        print("<div class='error_msg'>Erreur : '$eleve' est inscrit dans plusieurs Classes !</div><br>\n");
    } else {
        // L'eleve n'est inscrit dans aucune classe
        if ($OldClasse != "") {
            print "$eleve n'est inscrit dans aucune classe : Renommage de 'Classe_$OldClasse/$eleve' en 'Classe_$OldClasse/.$eleve'.<br>\n";
            system("sudo /bin/mv '/var/sambaedu/Classes/Classe_$OldClasse/$eleve' '/var/sambaedu/Classes/Classe_$OldClasse/.$eleve'");
        } else {
            print("<div class='error_msg'>Erreur : '$login' ne correspond pas  à un eleve !</div><br>\n");
        }
    }
}

function update_eleve($config, $login)
{
    $eleve = invert_login($config, $login);
    // Recherche du dossier Eleve
    $out = 0;
    $res = array();
    @exec("sudo ls -d1 /var/sambaedu/Classes/Classe_*/" . $eleve . " 2>/dev/null", $res, $out);
    if ($out == 0) {
        // $reps = explode(" ", $res);
        if (count($res) > 0) {
            foreach ($res as $rep) {
                if (preg_match("/Classe_grp_/", $rep)) {
                    print "Ancien groupe '$rep' ignoré.<br>\n";
                } else {
                    if ($rep != "") {
                        if (preg_match("!^/var/sambaedu/Classes/Classe_.+/$eleve$!", $rep)) {
                            $rep = preg_replace("!^/var/sambaedu/Classes/Classe_(.+)/$eleve$!", "\${1}", $rep);
                        } else {
                            print "Bizarre : Le répertoire '$rep' de l'ancienne classe de '$eleve' n'est pas de la forme '/var/sambaedu/Classes/Classe_*/$eleve' ! <br>\n";
                            $rep = ""; // On laisse tomber la gestion de l'ancien r&#233;pertoire
                        }
                    }
                    cree_rep($config, $login);
                }
            }
        }
    } else {
        cree_rep($config, $login);
    }
    return true;
}

function update_classes(array $config, string $classes = "*")
{
    $res = list_classes($config, $classes);
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

                system("sudo setfacl -R -P --set user::rwx,group::---,group:Equipe_$Classe:rwx,group:domain\ admins:rwx,mask::rwx,other::---,default:user::rwx,default:group::---,default:group:Equipe_$Classe:rwx,default:group:domain\ admins:rwx,default:mask::rwx,default:other::--- /var/sambaedu/Classes/$cnClasse");

                if ($etat == 0) {
                    //@TODO
                    system("sudo /usr/share/se3/scripts/echange_classes.sh $cnClasse actif");
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
                        if (! preg_match("!^/var/sambaedu/Classes/$cnClasse/_!", $oldeleve)) {
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
            print "Ancien groupe '$Classe' ignor&#233;e. utilisez le menu groupe<br>\n";
        } else {
            // le répertoire existe, mais la classe non : on renomme en .Classe_truc, au cas ou
            print("Le groupe n'existe plus : Renommage de la classe $Classe. en .Classe_$Classe<br>\n");
            system("sudo /bin/mv /var/sambaedu/Classes/Classe_$Classe /var/sambaedu/Classes/.Classe_$Classe");
        }
    }
}
?>