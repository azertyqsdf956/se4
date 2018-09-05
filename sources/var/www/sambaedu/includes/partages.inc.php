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
?>