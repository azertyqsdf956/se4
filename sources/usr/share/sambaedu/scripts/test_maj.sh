#!/bin/bash

## $Id$ ##
#
##### Retourne si une maj se3 est n&cessaire #####

if [ "$1" = "--help" -o "$1" = "-h" ]
then
	echo "Retourne si une maj de se3 est à faire"
	echo "Usage : aucune option"
	exit
fi
tmpfile="$(mktemp)"
maj="no"
dpkg -l|grep "sambaedu" |cut -d ' ' -f3|while read package
do
# # LC_ALL=C apt-get -s install $package|grep newest >/dev/null|| echo $package
mod_install=$(apt-cache policy $package | grep "Install" | cut -d" " -f4)
mod_candidat=$(apt-cache policy $package | grep "Candidat" | cut -d" " -f4)
    if [ "$mod_install" != "$mod_candidat" ]; then
        echo "$package pas glop"
        echo "yes" > "$tmpfile"
        echo $maj
        break
    fi
done

if [ -n $(grep "yes" "$tmpfile") ]; then
    echo "0"
    exit 0
else
    echo "1"
    exit 1
fi

