#!/bin/bash
## $Id$ ##
#shares_Vista: users
#shares_CIFSFS: users
#action: start
#level: 09
#
#
##### Crée le répertoire personnel de user #####
#
#


if [ "$1" = "--help" -o "$1" = "-h" ]
then
	echo "Crée le répertoire personnel de user"
	echo "Usage : mkhome.sh user"
fi

user=$1
if [ -z "$1" ]
then
	echo "Usage : mkhome.sh user"
	exit 1
fi

# Creation du repertoire perso le cas echeant
# -------------------------------------------
if [ ! -d "/home/$user" -o ! -d "/home/$user/profil" ]; then


	. /usr/share/sambaedu/includes/config.inc.sh
	if [ -z "$config_path2UserSkel" ];then
		echo "Alerte la variable path2UserSkel de la table params est vide !!!"
		exit 1
	fi
    [ -d "/home/$user" ] || mkdir /home/$user
cp -a $config_path2UserSkel/* /home/$user > /dev/null # 2>&1
	
else
	useruid=`getent passwd $user | gawk -F ':' '{print $3}'`
	prop=`stat -c%u /home/$user`
	if [ "$prop" != "$useruid" ]; then
		chown -R $user:domain\\admins /home/$user > /dev/null 2>&1
		chown -R $user:domain\\admins /home/$user/profil/Bureau/* > /dev/null 2>&1
	fi
	if [ "localmenu" != "1" ]; then
		chown -R $user:domain\\admins /home/$user/profil/Demarrer/* > /dev/null 2>&1
	fi 
fi

