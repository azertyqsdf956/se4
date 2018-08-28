<?php
/*
 * fonction pour lire des fichiers smb.conf
 */
function read_smb_conf()
{
    $config = array();
         if ($handle = opendir('/etc/samba/')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $config = array_merge($config, parse_ini_file($entry));
                }
            }
            closedir($handle);
        }
    return ($config);
}

/*
 * fonction pour ecrire un fichier .ini (compatible smb.conf)
 */
function write_ini_file($file, $array = []) {
    // check first argument is string
    if (!is_string($file)) {
        throw new \InvalidArgumentException('Function argument 1 must be a string.');
    }
    
    // check second argument is array
    if (!is_array($array)) {
        throw new \InvalidArgumentException('Function argument 2 must be an array.');
    }
    
    // process array
    $data = array();
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $data[] = "[$key]";
            foreach ($val as $skey => $sval) {
                if (is_array($sval)) {
                    foreach ($sval as $_skey => $_sval) {
                        if (is_numeric($_skey)) {
                            $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                        } else {
                            $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                        }
                    }
                } else {
                    $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                }
            }
        } else {
            $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
        }
        // empty line
        $data[] = null;
    }
    
    // open file pointer, init flock options
    $fp = fopen($file, 'w');
    $retries = 0;
    $max_retries = 100;
    
    if (!$fp) {
        return false;
    }
    
    // loop until get lock, or reach max retries
    do {
        if ($retries > 0) {
            usleep(rand(1, 5000));
        }
        $retries += 1;
    } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);
    
    // couldn't get the lock
    if ($retries == $max_retries) {
        return false;
    }
    
    // got lock, write data
    fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);
    
    // release lock
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return true;
}
/*
 * fonction pour lister les partages :
 * 
 */
function list_shares ($samba)
{
    $shares = array();
    foreach ($samba as $section => $val) {
        if (isset ($val['path'])) {
            $shares['$section'] = $val;
        }
    }
    return $shares;
}

?>
