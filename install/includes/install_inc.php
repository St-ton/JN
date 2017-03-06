<?php

/**
 * check for existens of a shop-DB (by looking for a specific table)
 *
 * @param NiceDB $niceDB
 * @return int returns =3(table `tsynclogin` not exist) or =4(table `tsynclogin` exist) or =false for failed
 */
function pruefeMySQLDaten($niceDB)
{
    if (!$niceDB->isConnected()) {
        return 1;
    }

    $obj = $niceDB->executeQuery("SHOW TABLES LIKE 'tsynclogin'", 1);

    return ($obj !== false) ? 4 : 3;
}

/**
 * @return bool
 */
function pruefeBereitsInstalliert()
{
    if (file_exists(PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php')) {
        //use buffer to avoid redeclaring constants errors
        ob_start();
        require_once PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php';
        ob_end_clean();

        return defined('BLOWFISH_KEY');
    }

    return false;
}


/**
 * @return array
 */
function gibIniDateien()
{
    $cDateien_arr = array(
        'php.ini',
        PFAD_ADMIN . 'php.ini',
        PFAD_ADMIN . 'includes/php.ini',
        PFAD_ADMIN . 'includes/widgets/php.ini',
        PFAD_ADMIN . 'templates/php/php.ini',
        'classes/core/php.ini',
        'includes/cron/php.ini',
        'dbeS/php.ini',
        'includes/libs/minify/php.ini',
        'includes/captcha/php.ini',
        'includes/modules/php.ini',
        'includes/ext/php.ini',
        'includes/libs/kcfinder/php.ini',
        'includes/plugins/jtl_search/version/105/frontend/php.ini',
        'includes/php.ini',
        'install/php.ini'
    );

    return $cDateien_arr;
}

/**
 * @return array
 */
function gibVorhandeneIniDateien()
{
    if (!defined('PFAD_ROOT')) {
        return array();
    }

    $cVorhandeneDateien_arr = array();
    $cDateien_arr           = gibIniDateien();
    if (is_array($cDateien_arr) && count($cDateien_arr) > 0) {
        foreach ($cDateien_arr as $cDatei) {
            $cVorhandeneDateien_arr['/' . $cDatei] = false;
            if (file_exists(PFAD_ROOT . $cDatei)) {
                $cVorhandeneDateien_arr['/' . $cDatei] = true;
            }
        }
    }

    return $cVorhandeneDateien_arr;
}

/**
 * @return bool
 */
function pruefeSchritt1Eingaben()
{
    return (isset($_POST['adminuser'], $_POST['adminpass'], $_POST['syncuser'], $_POST['syncpass']) &&
        strlen($_POST['adminuser']) > 0 &&
        strlen($_POST['adminpass']) > 0 &&
        strlen($_POST['syncuser']) > 0 &&
        strlen($_POST['syncpass']) > 0
    );
}

/**
 * @param int $length
 * @return string
 */
function generatePW($length = 8)
{
    $dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
    mt_srand((double)microtime() * 1000000);
    for ($i = 1; $i <= (count($dummy) * 2); $i++) {
        $swap         = mt_rand(0, count($dummy) - 1);
        $tmp          = $dummy[$swap];
        $dummy[$swap] = $dummy[0];
        $dummy[0]     = $tmp;
    }

    return substr(implode('', $dummy), 0, $length);
}

/**
 * @param string $url
 * @return string
 */
function parse_mysql_dump($url)
{
    $file_content = file($url);
    $errors       = '';
    $query        = '';
    foreach ($file_content as $i => $sql_line) {
        $tsl = trim($sql_line);
        if (($sql_line !== '') && (substr($tsl, 0, 2) !== '/*') && (substr($tsl, 0, 2) !== '--') && (substr($tsl, 0, 1) !== '#')) {
            $query .= $sql_line;
            if (preg_match('/;\s*$/', $sql_line)) {
                $result = $GLOBALS['DB']->executeQuery($query, 10);
                if (!$result) {
                    $errors .= '<br>' . $GLOBALS['DB']::getErrorMessage() .
                        ' Nr: ' . $GLOBALS['DB']::getErrorCode() . ' in Zeile ' . $i . '<br>' . $query . '<br>';
                }
                $query = '';
            }
        }
    }

    return $errors;
}

/**
 * @param string      $cDBHost
 * @param string      $cDBUser
 * @param string      $cDBPass
 * @param string      $cDBName
 * @param string|null $cDBSocket
 * @return bool
 */
function schreibeConfigDateiInstall($cDBHost, $cDBUser, $cDBPass, $cDBName, $cDBSocket = null)
{
    if (strlen($cDBHost) > 0 && strlen($cDBUser) > 0 && strlen($cDBPass) > 0 && strlen($cDBName) > 0) {
        define('BLOWFISH_KEY', gibUID(30));
        $socket = '';
        if ($cDBSocket !== null) {
            $socket = "\ndefine('DB_SOCKET', '" . $cDBSocket . "');";
        }

        $cPfadRoot = PFAD_ROOT;
        if (strpos(PFAD_ROOT, '\\') !== false) {
            $cPfadRoot = str_replace('\\', '\\\\', $cPfadRoot);
        }
        $cConfigFile = "<?php
define('PFAD_ROOT', '" . $cPfadRoot . "');
define('URL_SHOP', '" . substr(URL_SHOP, 0, strlen(URL_SHOP) - 1) . "');" .
$socket . "
define('DB_HOST','" . $cDBHost . "');
define('DB_NAME','" . $cDBName . "');
define('DB_USER','" . $cDBUser . "');
define('DB_PASS','" . $cDBPass . "');

define('BLOWFISH_KEY', '" . BLOWFISH_KEY . "');

//enables printing of warnings/infos/errors for the shop frontend
define('SHOP_LOG_LEVEL', 0);
//enables printing of warnings/infos/errors for the dbeS sync
define('SYNC_LOG_LEVEL', 0);
//enables printing of warnings/infos/errors for the admin backend
define('ADMIN_LOG_LEVEL', 0);
//enables printing of warnings/infos/errors for the smarty templates
define('SMARTY_LOG_LEVEL', 0);
//excplicitly show/hide errors
ini_set('display_errors', 0);" . "\n";
        //file speichern
        $file = fopen(PFAD_ROOT . PFAD_INCLUDES . 'config.JTL-Shop.ini.php', 'w');
        fwrite($file, $cConfigFile);
        fclose($file);

        return true;
    }

    return false;
}

/**
 * @param string $part
 * @return string
 */
function uname($part = 'a')
{
    $result = '';
    if (!function_is_disabled('php_uname')) {
        $result = @php_uname($part);
    } elseif (function_exists('posix_uname') && !function_is_disabled('posix_uname')) {
        $posix_equivs = array(
            'm' => 'machine',
            'n' => 'nodename',
            'r' => 'release',
            's' => 'sysname'
        );
        $puname = @posix_uname();
        $result = ($part === 'a' || !array_key_exists($part, $posix_equivs))
            ? implode(' ', $puname)
            : $puname[$posix_equivs[$part]]
        ;
    } else {
        if (!function_is_disabled('phpinfo')) {
            ob_start();
            phpinfo(INFO_GENERAL);
            $pinfo = ob_get_contents();
            ob_end_clean();
            if (preg_match('~System.*?(</B></td><TD ALIGN="left">| => |v">)([^<]*)~i', $pinfo, $match)) {
                $uname = $match[2];
                if ($part === 'r') {
                    $result = '';
                    if (!empty($uname) && preg_match('/\S+\s+\S+\s+([0-9.]+)/', $uname, $matchver)) {
                        $result = $matchver[1];
                    }
                } else {
                    $result = $uname;
                }
            }
        } else {
            $result = '';
        }
    }

    return $result;
}

/**
 * @param string $fn_name
 * @return bool
 */
function function_is_disabled($fn_name)
{
    return in_array($fn_name, explode(',', ini_get('disable_functions')), true);
}

