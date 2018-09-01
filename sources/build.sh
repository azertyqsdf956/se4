#!/bin/bash
version="3.9.4"
if [ -z "$1" ]; then
	paquet="sambaedu"
else
	if [ "$1"=="all" ]; then
		paquet="sambaedu"
	else
		paquet="sambaedu-$1"
fi
fi
if [ "$paquet"=="sambaedu" ]; then
	debs="../sambaedu*${version}*.deb"
	deb=sambaedu
else
    debs="../${paquet}_${version}.*.deb"
	deb=$paquet
fi	
cd ~/Donnees/se3-git/se4/sources
rm -f $debs
dch -U -i ""
debuild -us -uc -b
scp -P 2222 $debs root@wawadeb.crdp.ac-caen.fr:/root/se4
ssh -p 2222 root@wawadeb.crdp.ac-caen.fr "se4/se4.sh $version"
ssh root@admin.sambaedu3.maison "apt-get update && apt-get -y upgrade $deb"
cd
