# fichier bash à sourcer
# lecture et ecriture des parametres de config sambaedu4
# fichier à inclure au début de tout script devant accéder à la conf 
#
# assignation des variables de conf
function get_config() {
for conf in $(find /etc/sambaedu -name *.conf -type f); do
# version de transition avec param=value (se3) et config_param= value (se4)
#    for ligne in $(sed -E "/^#.*$/d;s|^(\S+)\s*=\s*(\".*\")$|config_\1=\2 \1=\2|g" $conf); do
# version finale
    for ligne in $(sed -E "/^#.*$/d;s|^(\S+)\s*=\s*\"(.*)\"$|config_\1='\2'|g" $conf); do
        eval $ligne
    done
done
}
# fonction permettant l'écriture des parametres
# set_config module param [value]
function set_config() {
    if [ "$1" == "sambaedu" ]; then
	    conf="/etc/sambaedu/sambaedu.conf"
    else
	    conf="/etc/sambaedu/sambaedu.conf.d/$1.conf"
    fi
    if [ -n "$2" ]; then
        if [ -f "$conf" ]; then
            if $(grep -q "^$2[= ]" $conf); then
               if [ -z "$3" ]; then
                   sed -i "/^${2}\s*=.*$/d" $conf
	       else
                   sed -i "s|^${2}\s*=\s*.*$|${2} = \"${3}\"|" $conf
               fi
            elif [ -n "$3" ]; then
               echo "$2 = \"$3\"">>$conf
            fi
        else
	    if [ -n "$3" ]; then
                echo "$2 = \"$3\"">>$conf
            fi
        fi
        eval config_$2=\'$3\'
    fi
}

# fonction permettant le renommage d'un parametre
# mv_config module old_param new_param [value]
# si pas de valeur on garde l'existante.
function mv_config() {
    if [ -n "$4" ]; then
        set_config $1 $3 "$4"
    else
        param=config_$2         
        set_config $1 $3 "${!param}"
    fi
    set_config $1 $2
}

# fonction pour écrire un parametre dans un fichier de conf
# usage : write_param fichier config_param
# remplace ###_PARAM_### par la valeur de $config_param dans le fichier
function write_param() {
    param=${2/config_/}
    param=${param^^}
    valeur=$2
    sed -i "s|###_${param}_###|${!valeur}|g" $1
}

# fonction pour mettre à jour un fichier de conf :
# usage : update_conf fichier field param
# field[ ]= valeur : remplace valeur par $config_param

function update_conf() {
    conf=$1
    field=$2
    param=$3
    if [ -n "$field" ]; then
    	re=".*$field[= ]*"
        if grep -q "$re" $conf; then
            sed -i "s|^\(\s*${field}[\s=]*\).*$|\1 $param|" $conf
        else
            echo "$field = $param">>$conf
        fi
fi
}    
# lecture de la conf
get_config
