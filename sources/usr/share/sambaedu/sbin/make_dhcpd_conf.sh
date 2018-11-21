#!/bin/bash

# Génération de la config dhcp de base à partir des fichiers de conf.
# N'écrit pas les réservations. Celles-ci sont inclues par le fichier
# reservations.conf généré  à partir de l'AD


. /usr/share/sambaedu/includes/config.inc.sh

sed -i "s/INTERFACESv4=.*$/INTERFACESv4=\"$config_dhcp_iface\"/" /etc/default/isc-dhcp-server

conf=/etc/dhcp/dhcpd.conf

echo "################################################################################">$conf
echo "# This File is automagically created by sambaedu, do not edit  ">>$conf
echo "##       GENERAL OPTIONS          ##############################################">>$conf
echo "allow booting;">>$conf
echo "allow bootp;">>$conf
echo "authoritative;">>$conf

echo "option domain-name \"$config_dhcp_domain_name\";">>$conf
echo "max-lease-time $config_dhcp_max_lease;">>$conf
echo "default-lease-time $config_dhcp_default_lease;">>$conf

echo "option wpad-url code 252 = string;">>$conf
echo "option client-arch code 93 = unsigned integer 16;">>$conf

if  [ -n "$config_wpad" ]; then
    echo "option wpad-url \"$config_wpad\";">>$conf
fi
    echo "option domain-name-servers $config_dhcp_dns_server_prim;">>$conf
if [ -n "$config_se4ad_ip" ]; then
    echo "option netbios-name-servers $config_se4ad_ip;">>$conf
fi
# boot ipxe
echo "###       BOOT OPTIONS          ##############################################">>$conf
echo "next-server  $config_dhcp_tftp_server;">>$conf
# booter en tftp undionly.kpxe, puis la conf ipxe :
# script ipxe statique avant install de sambaedu-ipxe, puis page php
echo "
option client-arch code 93 = unsigned integer 16;
if exists user-class and option user-class = \"sambaedu\" {
  filename \"${config_ipxe_url}${config_dhcp_ipxe_script}\"; 
} else {
  if exists client-arch {
     if option client-arch = 00:00 {
       filename \"undionly.kpxe\";
     } elsif option client-arch = 00:06 {
	   option vendor-class-identifier \"HTTPClient\";
       filename \"${config_ipxe_url}snponly_x32.efi\";
     } elsif option client-arch = 00:07 {
	   option vendor-class-identifier \"HTTPClient\";
       filename \"${config_ipxe_url}snponly_x64.efi\";
     }
  }
}">>$conf
# fichier option supplémentaire
if [ -n "$config_dhcp_extra_option" ]; then
	echo "include \"$config_dhcp_extra_option\";">>$conf
fi

# reseaux
echo "###       SUBNETS          ##############################################">>$conf

if [ -n "$config_dhcp_vlan" ]; then
    i="0"
    while [ $i -lt $config_dhcp_vlan ]; do
        i=$[$i+1]
        RESEAU=config_dhcp_reseau_$i
        MASQUE=config_dhcp_masque_$i
        BEGIN_RANGE=config_dhcp_begin_range_$i
        END_RANGE=config_dhcp_end_range_$i
        GATEWAY=config_dhcp_gateway_$i
        EXTRA_OPTION=config_dhcp_extra_option_$i
        if [ -n "${!RESEAU}" ]; then 
            echo "">>$conf
		    echo "#####  SUBNET DECLARATION $i #########">>$conf
		    echo "subnet ${!RESEAU} netmask ${!MASQUE} {">>$conf
		    echo "    range ${!BEGIN_RANGE} ${!END_RANGE};">>$conf
		    echo "    option routers ${!GATEWAY};">>$conf
		    if [ -n "${!EXTRA_OPTION}" ]; then
		        echo "    include \"${!EXTRA_OPTION}\";">>$conf
		    fi
		    echo "}">>$conf
        fi
    done
else
    RESEAU=$config_dhcp_reseau
    MASQUE=$config_dhcp_masque
    BEGIN_RANGE=$config_dhcp_begin_range
    END_RANGE=$config_dhcp_end_range
    GATEWAY=$config_dhcp_gateway
    EXTRA_OPTION=$config_dhcp_extra_option

    echo "">>$conf
    echo "#####  SUBNETS DECLARATION #########">>$conf
    echo "subnet $RESEAU netmask $MASQUE {">>$conf
    echo "    range $BEGIN_RANGE $END_RANGE;">>$conf
    echo "    option routers $GATEWAY;">>$conf
    if [ -n "$EXTRA_OPTION" ]; then
        echo "    include \"$EXTRA_OPTION\";">>$conf
    fi
    echo "}">>$conf
fi

# reservations
if [ -e /var/www/sambaedu/dhcp/make_reservations.php ]; then 
    if [ -e /etc/php/7.0/cli/conf.d/30-sambaedu.ini ]; then
        su www-admin -c "php /var/www/sambaedu/dhcp/make_reservations.php"
	fi
fi
if [ -e /etc/sambaedu/reservations.inc ] ; then
    echo "include \"/etc/sambaedu/reservations.inc\";" >> $conf
fi

systemctl restart isc-dhcp-server.service




