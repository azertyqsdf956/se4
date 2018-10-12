#!/bin/bash
#
##### Permet la génération du preseed de se4-AD#####
# denis Bonnenfant d'après la version de franck molle
# 
# @TODO mettre a jour pour install directe depuis un se4 ? 

function usage() 
{
echo "Script intéractif permettant la génération du preseed  se4-AD"
}

if [ "$1" = "--help" -o "$1" = "-h" ]
then
	usage
	echo "Usage : pas d'option"
	exit
fi

# Fonction écriture fichier de conf /etc/sambaedu/se4ad.config
function write_sambaedu_conf
{
if [ -e "$se4ad_config" ] ; then
	echo "$se4ad_config existe on en écrase le contenu"
fi
echo -e "$COLINFO"
#echo "Pas de fichier de conf $se4ad_config  -> On en crée un avec les params du se4ad"
echo -e "$COLTXT"
echo "## Adresse IP du futur SE4-AD ##" > $se4ad_config
echo "se4ad_ip=\"$config_se4ad_ip\"" >> $se4ad_config
echo "## Nom de domaine samba du SE4-AD ##" >> $se4ad_config
echo "smb4_domain=\"$config_samba_domain\"" >>  $se4ad_config
echo "## Suffixe du domaine##" >> $se4ad_config
echo "suffix_domain=\"$suffix_domain\"" >>  $se4ad_config
echo "## Nom de domaine complet - realm du SE4-AD ##" >> $se4ad_config
echo "ad_domain=\"$config_domain\"" >> $se4ad_config
echo "## Adresse IP de SE3 ##" >> $se4ad_config
echo "se3ip=\"$config_se4fs_ip\"" >> $se4ad_config
echo "## Nom du domaine samba actuel" >> $se4ad_config
echo "se3_domain=\"$config_samba_domain\""  >> $se4ad_config
echo "##Adresse du serveur DNS##" >> $se4ad_config
echo "nameserver=\"$my_dnsserver\"" >> $se4ad_config
echo "##Pass admin LDAP##" >> $se4ad_config
echo "adminPw=\"$config_ldap_passwd\"" >> $se4ad_config
echo "##base dn LDAP##" >> $se4ad_config
echo "ldap_base_dn=\"$config_ldap_base_dn\"" >> $se4ad_config
echo "##Rdn admin LDAP##" >> $se4ad_config
echo "adminRdn=\"$config_admin_rdn\"" >> $se4ad_config

chmod +x $se4ad_config
}


# copie des clés ssh présente sur le serveur principal sur le container
function write_ssh_keys
{
ssh_keys_host="/root/.ssh/authorized_keys"

if [ -e "$ssh_keys_host" ];then
    echo -e "$COLINFO"
    echo "Copie du fichier des clés SSH $ssh_keys_host"
    cp "$ssh_keys_host" "$dir_preseed/"
    echo -e "$COLCMD"
else
    touch $dir_preseed/authorized_keys
    chmod 600 $dir_preseed/authorized_keys
fi
}

# Génération du preseed avec les données saisies
function write_preseed
{
dir_config_preseed="/usr/share/doc/sambaedu/"
template_preseed="preseed_se4ad.example.gz"
target_preseed="$dir_preseed/se4ad.preseed"

if [ -e "$dir_config_preseed/$template_preseed" ];then
    echo -e "$COLINFO"
    echo "Copie du modele $template_preseed dans $target_preseed"
    gunzip -c "$dir_config_preseed/$template_preseed" > "$target_preseed"
    echo -e "$COLCMD"
fi


echo -e "$COLINFO"
echo "Modification du preseed avec les données saisies"
echo -e "$COLCMD"

sed -e "s/###_SE4AD_IP_###/$config_se4ad_ip/g; s/###_SE4_MASK_###/$my_mask/g; s/###_SE4_GW_###/$my_gateway/g; s/###_NAMESERVER_###/$my_dnsserver/g; s/###_SE4AD_NAME_###/$config_se4ad_name/g" -i  $target_preseed
sed -e "s/###_DOMAIN_###/$config_domain/g; s/###_SE4FS_IP_###/$config_se4fs_ip/g; s/###_NTP_SERV_###/$my_gateway/g; s|###_BOOT_DISK_###|/dev/sda|g; s|###_IPXE_URL_###|$config_ipxe_url|g; /^[#;].*$/d;/^$/d" -i  $target_preseed 
}

# Fonction copie des fichiers de conf @LXC/etc/sambaedu
function cp_config_to_preseed()
{
mkdir -p $dir_preseed/secret/
cd $dir_config
echo "Création de l'archive d'export des données $se4ad_config_tgz et copie sur $dir_preseed"
echo -e "$COLCMD"
tar -czf $se4ad_config_tgz export_se4ad
cp -av  $se4ad_config_tgz $dir_preseed/secret/
cd -
echo -e "$COLTXT"


sleep 2
}

#
# Affichage message de fin
function display_end_message() {
display_end_title="Génération du preseed terminée !!"	
	
display_end_txt="Le preseed de $se4name a été généré

Pour lancer l'installation sur serveur $se4name, deux solutions :
- Via un boot PXE sur le se3, partie maintenance, rubrique installation puis  **Netboot Debian stretch SE4-AD**

- Par installation via clé ou CD netboot. vous devrez entrer l'url suivante au debian installeur :
http://$se3ip/diconf/se4ad.preseed

Le mot de passe root temporaire sera fixé à \"se4ad\""

$dialog_box --backtitle "$BACKTITLE" --title "$display_end_title" --msgbox "$display_end_txt" 20 70


echo -e "$COLTITRE"
echo "Génération du preseed de $se4name terminée !!
url pour l'installation :  
http://$config_se3fs_ip/diconf/se4ad.preseed"
echo -e "$COLTXT"
}


######## Debut du Script ########


# Variables :
. /usr/share/sambaedu/includes/config.inc.sh
. /usr/share/sambaedu/includes/utils.inc.sh

get_config
my_network

dialog_box="$(which whiptail)"
tempfile=`tempfile 2>/dev/null` || tempfile=/tmp/inst$$


dir_config="/etc/sambaedu"
dir_export="/etc/sambaedu/export_se4ad"
mkdir -p "$dir_export"
dir_preseed="/var/www/sambaedu/ipxe/diconf"
se4ad_config="$dir_export/se4ad.config"
script_phase2="install_se4ad_phase2.sh"
se4ad_config_tgz="se4ad.config.tgz"


write_sambaedu_conf
cp_config_to_preseed
write_ssh_keys
write_preseed



# echo "Appuyez sur ENTREE "
exit 0


