<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/tools.Global.deprecations.php';

/**
 * @param array  $data
 * @param string $key
 * @param bool   $bStringToLower
 */
function objectSort(&$data, $key, $bStringToLower = false)
{
    $dataCount = count($data);
    for ($i = $dataCount - 1; $i >= 0; $i--) {
        $swapped = false;
        for ($j = 0; $j < $i; $j++) {
            $dataJ  = $data[$j]->$key;
            $dataJ1 = $data[$j + 1]->$key;
            if ($bStringToLower) {
                $dataJ  = strtolower($dataJ);
                $dataJ1 = strtolower($dataJ1);
            }
            if ($dataJ > $dataJ1) {
                $tmp          = $data[$j];
                $data[$j]     = $data[$j + 1];
                $data[$j + 1] = $tmp;
                $swapped      = true;
            }
        }
        if (!$swapped) {
            return;
        }
    }
}

/**
 * @param object $originalObj
 * @return stdClass
 */
function kopiereMembers($originalObj)
{
    if (!is_object($originalObj)) {
        return $originalObj;
    }
    $obj = new stdClass();
    foreach (array_keys(get_object_vars($originalObj)) as $member) {
        $obj->$member = $originalObj->$member;
    }

    return $obj;
}

/**
 * @param stdClass|object $src
 * @param stdClass|object $dest
 */
function memberCopy($src, &$dest)
{
    if ($dest === null) {
        $dest = new stdClass();
    }
    foreach (array_keys(get_object_vars($src)) as $key) {
        if (!is_object($src->$key) && !is_array($src->$key)) {
            $dest->$key = $src->$key;
        }
    }
}

/**
 * @param Artikel $Artikel
 * @param string $einstellung
 * @return int
 */
function gibVerfuegbarkeitsformularAnzeigen(Artikel $Artikel, string $einstellung): int
{
    if ($einstellung !== 'N'
        && ((int)$Artikel->inWarenkorbLegbar === INWKNICHTLEGBAR_LAGER
            || (int)$Artikel->inWarenkorbLegbar === INWKNICHTLEGBAR_LAGERVAR
            || ($Artikel->fLagerbestand <= 0 && $Artikel->cLagerKleinerNull === 'Y'))
    ) {
        switch ($einstellung) {
            case 'Y':
                return 1;
            case 'P':
                return 2;
            case 'L':
            default:
                return 3;
        }
    }

    return 0;
}

/**
 * @param string $cURL
 * @return bool
 */
function pruefeSOAP($cURL = '')
{
    return !(strlen($cURL) > 0 && !phpLinkCheck($cURL)) && class_exists('SoapClient');
}

/**
 * @param string $cURL
 * @return bool
 */
function pruefeCURL($cURL = '')
{
    return !(strlen($cURL) > 0 && !phpLinkCheck($cURL)) && function_exists('curl_init');
}

/**
 * @return bool
 */
function pruefeALLOWFOPEN()
{
    return (int)ini_get('allow_url_fopen') === 1;
}

/**
 * @param string $cSOCKETS
 * @return bool
 */
function pruefeSOCKETS($cSOCKETS = '')
{
    return !(strlen($cSOCKETS) > 0 && !phpLinkCheck($cSOCKETS)) && function_exists('fsockopen');
}

/**
 * @param string $url
 * @return bool
 */
function phpLinkCheck($url)
{
    $url    = parse_url(trim($url));
    $scheme = strtolower($url['scheme']);
    if ($scheme !== 'http' && $scheme !== 'https') {
        return false;
    }
    if (!isset($url['port'])) {
        $url['port'] = 80;
    }
    if (!isset($url['path'])) {
        $url['path'] = '/';
    }

    return !fsockopen($url['host'], $url['port'], $errno, $errstr, 30)
        ? false
        : true;
}

/**
 * YYYY-MM-DD HH:MM:SS, YYYY-MM-DD, now oder now()
 *
 * @param string $cDatum
 * @return array
 */
function gibDatumTeile(string $cDatum)
{
    $date_arr = [];
    if (strlen($cDatum) > 0) {
        if ($cDatum === 'now()') {
            $cDatum = 'now';
        }
        try {
            $date                 = new DateTime($cDatum);
            $date_arr['cDatum']   = $date->format('Y-m-d');
            $date_arr['cZeit']    = $date->format('H:m:s');
            $date_arr['cJahr']    = $date->format('Y');
            $date_arr['cMonat']   = $date->format('m');
            $date_arr['cTag']     = $date->format('d');
            $date_arr['cStunde']  = $date->format('H');
            $date_arr['cMinute']  = $date->format('i');
            $date_arr['cSekunde'] = $date->format('s');
        } catch (Exception $e) {
        }
    }

    return $date_arr;
}

/**
 * Besucher nach 3 Std in Besucherarchiv verschieben
 */
function archiviereBesucher()
{
    $iInterval = 3;
    Shop::Container()->getDB()->queryPrepared(
        "INSERT INTO tbesucherarchiv
            (kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser,
              cAusstiegsseite, nBesuchsdauer, kBesucherBot, dZeit)
            SELECT kBesucher, cIP, kKunde, kBestellung, cReferer, cEinstiegsseite, cBrowser, cAusstiegsseite,
            (UNIX_TIMESTAMP(dLetzteAktivitaet) - UNIX_TIMESTAMP(dZeit)) AS nBesuchsdauer, kBesucherBot, dZeit
              FROM tbesucher
              WHERE dLetzteAktivitaet <= date_sub(now(), INTERVAL :interval HOUR)",
        [ 'interval' => $iInterval ],
        \DB\ReturnType::AFFECTED_ROWS
    );
    Shop::Container()->getDB()->queryPrepared(
        "DELETE FROM tbesucher
            WHERE dLetzteAktivitaet <= date_sub(now(), INTERVAL :interval HOUR)",
        [ 'interval' => $iInterval ],
        \DB\ReturnType::AFFECTED_ROWS
    );
}

/**
 * @param string $dir
 * @return bool
 */
function delDirRecursively(string $dir)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    $res      = true;
    foreach ($iterator as $fileInfo) {
        $fileName = $fileInfo->getFilename();
        if ($fileName !== '.gitignore' && $fileName !== '.gitkeep') {
            $func = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $res  = $res && $func($fileInfo->getRealPath());
        }
    }

    return $res;
}

/**
 * @param object $oObj
 * @return mixed
 */
function deepCopy($oObj)
{
    return unserialize(serialize($oObj));
}

/**
 * @param array $requestData
 * @return bool
 */
function validateCaptcha(array $requestData)
{
    $valid = Shop::Container()->getCaptchaService()->validate($requestData);

    if ($valid) {
        Session::set('bAnti_spam_already_checked', true);
    } else {
        Shop::Smarty()->assign('bAnti_spam_failed', true);
    }

    return $valid;
}

/**
 * create a hidden input field for xsrf validation
 *
 * @return string
 * @throws Exception
 */
function getTokenInput()
{
    if (!isset($_SESSION['jtl_token'])) {
        $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32);
    }

    return '<input type="hidden" class="jtl_token" name="jtl_token" value="' . $_SESSION['jtl_token'] . '" />';
}

/**
 * validate token from POST/GET
 *
 * @return bool
 */
function validateToken()
{
    if (!isset($_SESSION['jtl_token'])) {
        return false;
    }

    $token = $_POST['jtl_token'] ?? $_GET['token'] ?? null;

    if ($token === null) {
        return false;
    }

    return Shop::Container()->getCryptoService()->stableStringEquals($_SESSION['jtl_token'], $token);
}

/**
 * @param DateTime|string|int $date
 * @param int $weekdays
 * @return DateTime
 */
function dateAddWeekday($date, $weekdays)
{
    try {
        if (is_string($date)) {
            $resDate = new DateTime($date);
        } elseif (is_numeric($date)) {
            $resDate = new DateTime();
            $resDate->setTimestamp($date);
        } elseif (is_object($date) && is_a($date, 'DateTime')) {
            /** @var DateTime $date */
            $resDate = new DateTime($date->format(DateTime::ATOM));
        } else {
            $resDate = new DateTime();
        }
    } catch (Exception $e) {
        Jtllog::writeLog($e->getMessage());
        $resDate = new DateTime();
    }

    if ((int)$resDate->format('w') === 0) {
        // Add one weekday if startdate is on sunday
        $resDate->add(DateInterval::createFromDateString('1 weekday'));
    }

    // Add $weekdays as normal days
    $resDate->add(DateInterval::createFromDateString($weekdays . ' day'));

    if ((int)$resDate->format('w') === 0) {
        // Add one weekday if enddate is on sunday
        $resDate->add(DateInterval::createFromDateString('1 weekday'));
    }

    return $resDate;
}

/**
 * @param array $fehlendeAngaben
 * @return int
 */
function eingabenKorrekt($fehlendeAngaben)
{
    return (int)\Functional\none($fehlendeAngaben, function ($e) { return $e > 0; });
}
