# fichier bash à sourcer
# incompatible sh /dash !!!
# fonctions utiles pour générer les configurations des services sambaedu
# 
# Denis Bonnenfant
#
# licence GPL

cdr2mask()
{
   # Number of args to shift, 255..255, first non-255 byte, zeroes
   set -- $(( 5 - ($1 / 8) )) 255 255 255 255 $(( (255 << (8 - ($1 % 8))) & 255 )) 0 0 0
   [ $1 -gt 1 ] && shift $1 || shift
   echo ${1-0}.${2-0}.${3-0}.${4-0}
}

# Fonction permettant de récuperer la configuration réseau complète
# 
# 

my_network() {


read my_gateway my_interface<<<$(ip -o -f inet route show default 0.0.0.0/0 | cut -d ' ' -f3,5)
read my_address my_cdr my_broadcast<<<$(ip -o -f inet addr show dev "$my_interface" | awk '{sub("/", " ", $4); print $4, $6}')
my_mask=$(cdr2mask $my_cdr)
my_network=$(ip -o -f inet route show dev $my_interface  src $my_address | cut -d/ -f1)
my_hostname=$(hostname -s 2>/dev/null)
my_domain=$(hostname -d 2>/dev/null) || true
my_fqdn=$(hostname -f 2>/dev/null) || true
# attention ne donne pas le dns externe si ad est configuré
my_dnsserver=$(grep -m 1 "^nameserver" /etc/resolv.conf | cut -d" " -f2) || true
my_proxy="$http_proxy"
if [ -e "etc/sambaedu/sambaedu.conf.d/dhcp.conf" ]; then
    my_vlan=$(grep $my_network /etc/sambaedu/sambaedu.conf.d/dhcp.conf | cut -d= -f1 | sed "s/^.*_//")
fi
}
