#
# Structure de la table `connexions`
#


CREATE TABLE IF NOT EXISTS connexions (
  id bigint(20) unsigned NOT NULL auto_increment,
  username varchar(20) NOT NULL default '',
  ip_address varchar(15) NOT NULL default '',
  netbios_name varchar(15) NOT NULL default '',
  logintime datetime NOT NULL default '0000-00-00 00:00:00',
  logouttime datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  UNIQUE KEY id_2 (id),
  KEY id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Contenu de la table `connexions`
#

# --------------------------------------------------------

#
# Structure de la table `sessions`
#

CREATE TABLE IF NOT EXISTS sessions (
  id smallint(5) unsigned NOT NULL auto_increment,
  sess varchar(20) NOT NULL default '',
  mdp varchar(20) NOT NULL default '',
  login varchar(20) NOT NULL default '',
  help tinyint(4) default NULL,
  intlevel tinyint(4) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY id_2 (id,sess),
  KEY id (id,sess)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



#
# Structure de la table `devoirs`
#

CREATE TABLE IF NOT EXISTS devoirs (
  id smallint(6) NOT NULL auto_increment,
  id_prof varchar(50) NOT NULL default '',
  id_devoir varchar(50) NOT NULL default '',
  nom_devoir varchar(50) NOT NULL default 'devoir',
  date_distrib date NOT NULL default '0000-00-00',
  date_recup date NOT NULL default '0000-00-00',
  description varchar(255) NOT NULL default '',
  liste_distrib text NOT NULL,
  liste_retard text NOT NULL,
  etat char(1) NOT NULL default 'D',
  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `alertes` (
  `ID` bigint(20) NOT NULL auto_increment,
  `NAME` varchar(100) NOT NULL default '',
  `MAIL` varchar(100) NOT NULL default '',
  `Q_ALERT` varchar(200) NOT NULL default '',
  `VALUE` varchar(100) NOT NULL default '',
  `CHOIX` varchar(40) NOT NULL default '',
  `TEXT` varchar(250) NOT NULL default '',
  `AFFICHAGE` tinyint(4) NOT NULL default '0',
  `VARIABLE` varchar(50) NOT NULL default '',
  `PREDEF` tinyint(4) NOT NULL default '0',
  `MENU` varchar(50) NOT NULL default '',
  `ACTIVE` tinyint(4) NOT NULL default '0',
  `SCRIPT` varchar(255) NOT NULL default '',
  `PARC` varchar(50) NOT NULL default '',
  `FREQUENCE` varchar(20) NOT NULL default '',
  `PERIODE_SCRIPT` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `MAIL_FREQUENCE` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- Contenu de la table `alertes`
-- 

INSERT INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES (51, 'Surveille apachese (909)', 'se3_is_admin', '', '', '', 'Test si l''interface d''administration marche', 1, '1', 1, '', 0, 'check_http -H localhost -p 909', '', '900', '2007-01-12 17:35:02');
INSERT INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES (52, 'Test swap', 'se3_is_admin', '', '', '', 'Test si le serveur swap à plus de 80%', 1, '', 1, '', 0, 'check_swap -w 80%', '', '3600', '2007-01-12 15:27:03');
INSERT INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES (53, 'Mises à jour', 'se3_is_admin', '', '', '', 'Test les mises à jour de sécurité disponibles', 1, '0', 1, '', 0, 'check_debian_packages --timeout=60', '', '302400', '2007-01-12 17:11:12');
INSERT INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES (50, 'Etat des disques', 'se3_is_admin', '', '', '', 'Espace libre sur les disques', 1, '0', 1, '', 0, 'check_disk -w 5% -c 3% -x /dev/shm -t 10 -e', '', '900', '2007-01-12 17:30:01');
INSERT INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES ('NULL', 'UPS', 'se3_is_admin', '', '', '', 'Recevoir les alertes de l\\''onduleur', 0, '', 1, '', 0, '', '', '900', 'NULL');
INSERT IGNORE INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES ('NULL', 'close maintenance', 'se3_is_admin', '', '', '', 'Fermeture d\\''une demande de maintenance', 0, 'close_maintenance', 1, '', 0, '', '', '900', '2007-04-26 18:57:12');
INSERT IGNORE INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES ('NULL', 'new maintenance', 'se3_is_admin', '', '', '', 'Ouverture d''une demande de maintenance', 0, 'new_maintenance', 1, '', 0, '', '', '900', '0000-00-00 00:00:00');
INSERT IGNORE INTO `alertes` (`ID`, `NAME`, `MAIL`, `Q_ALERT`, `VALUE`, `CHOIX`, `TEXT`, `AFFICHAGE`, `VARIABLE`, `PREDEF`, `MENU`, `ACTIVE`, `SCRIPT`, `PARC`, `FREQUENCE`, `PERIODE_SCRIPT`) VALUES ('NULL', 'change maintenance', 'se3_is_admin', '', '', '', 'Changement d''une demande de maintenance', 0, 'change_maintenance', 1, '', 0, '', '', '900', '0000-00-00 00:00:00');



CREATE TABLE IF NOT EXISTS delegation (
  ID int(11) NOT NULL auto_increment,
  login varchar(40) NOT NULL default '',
  parc varchar(40) NOT NULL default '',
  niveau varchar(20) NOT NULL default '',
  PRIMARY KEY  (login,parc),
  UNIQUE KEY ID (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS actionse3 (
  action varchar(30) NOT NULL default '',
  parc varchar(50) NOT NULL default '',
  jour varchar(30) NOT NULL default '',
  heure time NOT NULL default '00:00:00',
  UNIQUE KEY parc (parc,jour,heure)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Structure de la table `quotas`
-- 

CREATE TABLE IF NOT EXISTS `quotas` (
  `type` char(1) default NULL,
  `nom` varchar(255) default NULL,
  `quotasoft` mediumint(9) default NULL,
  `quotahard` mediumint(9) default NULL,
  `partition` varchar(10) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS action_clonage (
id INT(11),
mac VARCHAR(255),
name VARCHAR(255),
date INT(11),
type VARCHAR(255),
num_op INT(11),
infos VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS action_sauvegardes (
id INT( 11 ) NOT NULL ,
name VARCHAR( 255 ) NOT NULL ,
mac VARCHAR( 255 ) NOT NULL ,
`partition` VARCHAR( 255 ) NOT NULL ,
image VARCHAR( 255 ) NOT NULL ,
date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
descriptif TEXT NOT NULL,
df TEXT NOT NULL,
partitionnement TEXT NOT NULL,
identifiant int(11) NOT NULL auto_increment,
PRIMARY KEY  (identifiant)
);


CREATE TABLE IF NOT EXISTS action_rapports (
id INT( 11 ) NOT NULL ,
name VARCHAR( 255 ) NOT NULL ,
mac VARCHAR( 255 ) NOT NULL ,
date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
tache VARCHAR( 255 ) NOT NULL ,
statut VARCHAR( 255 ) NOT NULL ,
descriptif TEXT NOT NULL,
identifiant int(11) NOT NULL auto_increment,
PRIMARY KEY  (identifiant)
);


CREATE TABLE IF NOT EXISTS action_infos (
id INT( 11 ) NOT NULL ,
name VARCHAR( 255 ) NOT NULL ,
mac VARCHAR( 255 ) NOT NULL ,
nom VARCHAR( 255 ) NOT NULL ,
valeur VARCHAR( 255 ) NOT NULL ,
identifiant int(11) NOT NULL auto_increment,
PRIMARY KEY  (identifiant)
);
