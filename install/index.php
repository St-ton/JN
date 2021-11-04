<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

if (isset($_GET['phpinfo'])) {
    phpinfo();
    exit;
}

// allow the loading of composer-libs (system-check)
require_once '../includes/vendor/autoload.php';

ini_set('display_errors', 1); // --TO-CHECK--

// Pfad
$cREQUEST_URI = $_SERVER['REQUEST_URI'];

// Work Around bei Direktaufruf
if (strpos($cREQUEST_URI, '.php')) {
    $nPos         = strrpos($cREQUEST_URI, '/') + 1;
    $cREQUEST_URI = substr($cREQUEST_URI, 0, strlen($cREQUEST_URI) - (strlen($cREQUEST_URI) - $nPos));
}
$protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || (int)$_SERVER['HTTPS'] === 1))
    ? 'https://'
    : 'http://';
$cShopPort = '';
if ((int)$_SERVER['SERVER_PORT'] !== 80) {
    $cShopPort = ((int)$_SERVER['SERVER_PORT'] === 443 && $protocol === 'https://')
        ? ''
        : (':' . (int)$_SERVER['SERVER_PORT']);
}
$host     = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
$cShopURL = $protocol . $host . $cShopPort . substr($cREQUEST_URI, 0, strlen($cREQUEST_URI) - 8);

define('PFAD_ROOT', realpath(__DIR__ . '/..') . '/');
define('URL_SHOP', $cShopURL);
define('SHOP_LOG_LEVEL', E_ALL);
define('SMARTY_LOG_LEVEL', E_ALL);

require_once PFAD_ROOT . 'includes/defines.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
if (!is_writable(PFAD_ROOT . PFAD_COMPILEDIR)) {
    die('Der Installer kann nicht gestartet werden. Bitte geben Sie dem Verzeichnis ' . PFAD_ROOT . PFAD_COMPILEDIR . ' Schreibrechte f&uuml;r alle.
    Schreibrechte k&ouml;nnen Sie mit Ihrem FTP-Programm setzen. Rufen Sie danach diese Seite erneut auf.');
}

require_once PFAD_ROOT . PFAD_SMARTY . 'SmartyBC.class.php';
$szPhpVersion = PHP_VERSION;
if(version_compare($szPhpVersion, '7.3.0', '<'))
{
    $smarty = new Smarty();
    $smarty->setCaching(0);
    $smarty->setDebugging(false);
    $smarty->setForceCompile(true);
    $smarty->setTemplateDir(PFAD_ROOT . PFAD_INSTALL . 'template/')
           ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR)
           ->setConfigDir(PFAD_ROOT . PFAD_INSTALL . 'template/lang/')
           ->assign('PFAD_ROOT', PFAD_ROOT);

    $cHinweis = 'Der Installer kann nicht gestartet werden. Sie verwenden aktuell die PHP-Version <b>' . $szPhpVersion . '</b>.<br />' .
        'JTL-Shop4 setzt mindestens die PHP-Version <b>7.3</b> voraus.';
    $smarty->assign('URL_SHOP', URL_SHOP)
           ->assign('PFAD_INSTALL', PFAD_INSTALL)
           ->assign('PFAD_ADMIN_TEMPLATE', PFAD_ADMIN . PFAD_TEMPLATES . 'bootstrap/')
           ->assign('versionAbort', true)
           ->assign('cHinweis', $cHinweis)
           ->display('install.tpl');

   exit(-1);
}

require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Shop.php';
$shop = Shop::getInstance();
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES_LIBS . 'password_compat/password.php';
require_once PFAD_ROOT . PFAD_INSTALL . PFAD_INCLUDES . 'install_inc.php';


$oSC    = new Systemcheck_Environment();
$vTests = $oSC->executeTestGroup('Shop4');
$oFS    = new Systemcheck_Platform_Filesystem(PFAD_ROOT);

$smarty = new Smarty();
$smarty->setCaching(0);
$smarty->setDebugging(false);
$smarty->setForceCompile(true);
$smarty->setTemplateDir(PFAD_ROOT . PFAD_INSTALL . 'template/')
       ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR)
       ->setConfigDir(PFAD_ROOT . PFAD_INSTALL . 'template/lang/')
       ->assign('PFAD_ROOT', PFAD_ROOT)
       ->assign('versionAbort', false);

$cHinweis  = '';
$nCon      = 0;
$step      = 'schritt2';
$DB        = null;
$configErr = false;
$dbError   = '';
// Pruefe Datenbankverbindung
if (isset($_POST['DBhost'], $_POST['DBuser']) && strlen($_POST['DBhost']) > 0 && strlen($_POST['DBuser']) > 0) {
    if (!empty($_POST['DBsocket'])) {
        define('DB_SOCKET', $_POST['DBsocket']);
    }
    try {
        $DB   = new NiceDB($_POST['DBhost'], $_POST['DBuser'], $_POST['DBpass'], $_POST['DBname']);
        $nCon = pruefeMySQLDaten($DB);
    } catch (Exception $exc) {
        $dbError = 'Datenbankfehler: ' . $exc->getMessage();
        $nCon    = 5;
    }
}

$bAnforderungen     = $oSC->getIsPassed();
$bVerzeichnisRechte = $oFS->getIsPassed();

if ($nCon !== 3 || !$bAnforderungen || !$bVerzeichnisRechte) {
    // step back, if something wrong
    $step = 'schritt0';
    $DB   = null;
} else {
    $step = pruefeSchritt1Eingaben()
        ? 'schritt2'
        : 'schritt1';
}

// (Schritt 0) Zeige Install
switch ($step) {
    case 'schritt0':
        $cHinweis = '';
        if (!(isset($_POST['DBhost'], $_POST['DBuser'], $_POST['installiere']) &&
            strlen($_POST['DBhost']) > 0 &&
            strlen($_POST['DBuser']) > 0)
        ) {
            $cHinweis = 'Bitte f&uuml;llen Sie die Datenbankinformationen aus';
        }
        if ($nCon === 1) {
            $cHinweis = 'Die Angaben Datenbankhost (' . $_POST['DBhost'] .
                '), Benutzername (' . $_POST['DBuser'] . ') oder Passwort stimmen nicht. <br />
            Es konnte keine Verbindung zum MySQL-Server aufgebaut werden. Bitte &uuml;berpr&uuml;fen Sie die Eingaben.';
        } elseif ($nCon === 2) {
            $cHinweis = 'Der angegebene Benutzername hat auf dem angegebenem Server keine Rechte f&uuml;r die Datenbank ' . $_POST['DBname'];
        } elseif ($nCon === 4) {
            $cHinweis = 'JTL Shop ist bereits installiert! Eventuell muss die Datenbank neu angelegt werden.';
        } elseif ($nCon === 5) {
            $cHinweis = $dbError;
        }
        if (!$bAnforderungen) {
            $cHinweis = 'Installation kann nicht fortgesetzt werden, da einige Anforderungen nicht erf&uuml;llt werden.';
        } elseif (!$bVerzeichnisRechte) {
            $cHinweis = 'Installation kann nicht fortgesetzt werden, da einige Schreibrechte fehlen.<br />
                Bitte schauen Sie in der Installationsanleitung nach, wie Sie die Schreibrechte setzen k&ouml;nnen.';
        }
        // Bereits installiert?
        $bInstalliert = pruefeBereitsInstalliert();
        if ($bInstalliert) {
            $cHinweis = 'Installation kann nicht fortgesetzt werden, da der Shop bereits installiert wurde.';
        }

        $smarty->assign('bOk', $bAnforderungen && $bVerzeichnisRechte && !$bInstalliert)
               ->assign('cVerzeichnis_arr', $oFS->getFoldersChecked())
               ->assign('cVorhandeneIniDateien_arr', gibVorhandeneIniDateien());
        break;

    case 'schritt1':
        srand();
        $smarty->assign('cAdminPass', generatePW(8))
               ->assign('cSynUser', generatePW(8))
               ->assign('cSyncPass', generatePW(8))
               ->assign('cPostVar_arr', $_POST);
        break;

    case 'schritt2':
        $cHinweis                      = parse_mysql_dump('initial_schema.sql');
        $adminLogin                    = new stdClass();
        $adminLogin->cLogin            = $_POST['adminuser'];
        $adminLogin->cPass             = md5($_POST['adminpass']);
        $adminLogin->cName             = 'Admin';
        $adminLogin->cMail             = '';
        $adminLogin->kAdminlogingruppe = 1;
        $adminLogin->nLoginVersuch     = 0;
        $adminLogin->bAktiv            = 1;

        if ($cHinweis === '' && !$DB->insertRow('tadminlogin', $adminLogin)) {
            $cHinweis .= '<br />' . $DB->getError() . ' Nr: ' . $DB->getErrorCode();
        }

        $syncLogin        = new stdClass();
        $syncLogin->cMail = '';
        $syncLogin->cName = $_POST['syncuser'];
        $syncLogin->cPass = $_POST['syncpass'];

        if (preg_match('/[^A-Za-z0-9\!"\#\$%&\'\(\)\*\+,-\.\/:;\=\>\?@\[\\\\\]\^_`\|\}~]/', $_POST['syncpass'])) {
            $cHinweis .= 'Benutzername und Passwort dürfen nur Groß- und Kleinbuchstaben, Zahlen sowie folgende ' .
                'Sonderzeichen enthalten: !\"#$%&\'()*+,-./:;=>?@[\\]^_`|}~';
        }
        if ($cHinweis === '' && !$DB->insertRow('tsynclogin', $syncLogin)) {
            $cHinweis .= '<br />' . $DB->getError() . ' Nr: ' . $DB->getErrorCode();
        }
        // Zahlungarten auf Nutzbarkeit pruefen
        pruefeZahlungsartNutzbarkeit();

        if ($cHinweis === '') {
            if (!schreibeConfigDateiInstall(
                $_POST['DBhost'],
                $_POST['DBuser'],
                $_POST['DBpass'],
                $_POST['DBname'],
                (!empty($_POST['DBsocket']))
                    ? $_POST['DBsocket']
                    : null)
            ) {
                $cHinweis  = 'Beim Schreiben der Konfigurationsdatei ist ein unbekannter Fehler aufgetreten.';
                $configErr = true;
            } else {
                $smarty->assign('cPostVar_arr', $_POST)
                       ->assign('BLOWFISH_KEY', BLOWFISH_KEY);
            }
        }
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('configErr', $configErr)
       ->assign('step', $step)
       ->assign('URL_SHOP', URL_SHOP)
       ->assign('PFAD_INSTALL', PFAD_INSTALL)
       ->assign('PFAD_ADMIN_TEMPLATE', PFAD_ADMIN . PFAD_TEMPLATES . 'bootstrap/')
       ->assign('oTests', $vTests)
       ->display('install.tpl');

