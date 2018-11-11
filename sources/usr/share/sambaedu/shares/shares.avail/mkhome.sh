#!/bin/bash
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
if [ ! -d "/home/$user" ]; then


[ -d "/home/$user" ] || mkdir /home/$user
cp -a /etc/skel/user.windows/* /home/$user > /dev/null # 2>&1
	
else
	useruid=`getent passwd $user | gawk -F ':' '{print $3}'`
	prop=`stat -c%u /home/$user`
	if [ "$prop" != "$useruid" ]; then
		chown -R $user:domain\\admins /home/$user > /dev/null 2>&1
	fi
fi

