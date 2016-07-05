<?php

/**
 * Class Redirect
 *
 * @access public
 */
class Redirect
{
    /**
     * @var int
     */
    public $kRedirect;

    /**
     * @var string
     */
    public $cFromUrl;

    /**
     * @var string
     */
    public $cToUrl;

    /**
     * @param int $kRedirect
     */
    public function __construct($kRedirect = 0)
    {
        $kRedirect = intval($kRedirect);
        if ($kRedirect > 0) {
            $this->loadFromDB($kRedirect);
        }
    }

    /**
     * @param int $kRedirect
     * @return $this
     */
    public function loadFromDB($kRedirect)
    {
        $obj = Shop::DB()->select('tredirect', 'kRedirect', intval($kRedirect));
        if (is_object($obj) && $obj->kRedirect > 0) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * @param int $kRedirect
     * @return $this
     */
    public function delete($kRedirect)
    {
        $kRedirect = (int)$kRedirect;
        Shop::DB()->delete('tredirect', 'kRedirect', $kRedirect);
        Shop::DB()->delete('tredirectreferer', 'kRedirect', $kRedirect);

        return $this;
    }

    /**
     * @return int
     */
    public function deleteAll()
    {
        return Shop::DB()->query("
            DELETE tredirect, tredirectreferer
                FROM tredirect
                LEFT JOIN tredirectreferer ON tredirect.kRedirect = tredirectreferer.kRedirect
                WHERE tredirect.cToUrl = ''", 3
        );
    }

    /**
     * @param string $cUrl
     * @return mixed
     */
    public function find($cUrl)
    {
        return Shop::DB()->select('tredirect', 'cFromUrl', $this->normalize($cUrl));
    }

    /**
     * @param string $cSource
     * @param string $cDestiny
     * @return bool
     */
    public function isDeadlock($cSource, $cDestiny)
    {
        $nPos      = strrpos($cSource, '/');
        $nPos      = $nPos !== false ? ($nPos + 1) : 0;
        $cSource   = substr($cSource, $nPos);
        $xPath_arr = parse_url(Shop::getURL());
        $cDestiny  = (isset($xPath_arr['path'])) ? $xPath_arr['path'] . '/' . $cDestiny : $cDestiny;
        $oObj      = Shop::DB()->select('tredirect', 'cFromUrl', $cDestiny, 'cToUrl', $cSource);

        return (isset($oObj->kRedirect) && intval($oObj->kRedirect) > 0);
    }

    /**
     * @param string $cSource
     * @param string $cDestiny
     * @param bool   $bForce
     * @return bool
     */
    public function saveExt($cSource, $cDestiny, $bForce = false)
    {
        if (strlen($cSource) > 1 && substr($cSource, 0, 1) !== '/') {
            $cSource = '/' . $cSource;
        }
        if (strlen($cDestiny) > 1 && substr($cDestiny, 0, 1) !== '/') {
            $cDestiny = '/' . $cDestiny;
        }
        if (strlen($cSource) > 1 && strlen($cDestiny) > 1 && $cSource != $cDestiny || $bForce) {
            if (!$this->isDeadlock($cSource, $cDestiny)) {
                $oRedirect = $this->find($cSource);

                if (!$oRedirect) {
                    $oObj           = new stdClass();
                    $oObj->cFromUrl = StringHandler::convertISO($cSource);
                    $oObj->cToUrl   = StringHandler::convertISO($cDestiny);

                    $kRedirect = Shop::DB()->insert('tredirect', $oObj);
                    if (intval($kRedirect) > 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $cFile
     * @return array
     */
    public function doImport($cFile)
    {
        $cError_arr = array();
        if (file_exists($cFile)) {
            $handle = fopen($cFile, 'r');
            if ($handle) {
                $oSprache     = gibStandardsprache(true);
                $cMapping_arr = array();
                $i            = 0;
                while (($csv = fgetcsv($handle, 30000, ';')) !== false) {
                    if ($i > 0) {
                        if ($cMapping_arr !== null) {
                            $this->import($csv, $i, $cError_arr, $cMapping_arr, $oSprache);
                        } else {
                            $cError_arr[] = 'Die Kopfzeile entspricht nicht der Konvention!';
                            break;
                        }
                    } else {
                        $cMapping_arr = $this->readHeadRow($csv);
                    }
                    $i++;
                }

                fclose($handle);
            } else {
                $cError_arr[] = 'Datei konnte nicht gelesen werden';
            }
        } else {
            $cError_arr[] = 'Datei konnte nicht gefunden werden';
        }

        return $cError_arr;
    }

    /**
     * @param string  $csv
     * @param int     $nRow
     * @param array   $cError_arr
     * @param array   $cMapping_arr
     * @param object  $oSprache
     * @return $this
     */
    protected function import($csv, $nRow, &$cError_arr, $cMapping_arr, $oSprache)
    {
        $xParse_arr = parse_url($csv[$cMapping_arr['sourceurl']]);
        $cFromUrl   = $xParse_arr['path'];
        if (isset($xParse_arr['query'])) {
            $cFromUrl .= '?' . $xParse_arr['query'];
        }
        $options = array(
            'cFromUrl' => $cFromUrl
        );
        $options['cArtNr'] = null;
        if (isset($csv[$cMapping_arr['articlenumber']])) {
            $options['cArtNr'] = $csv[$cMapping_arr['articlenumber']];
        }
        $options['cToUrl'] = null;
        if (isset($csv[$cMapping_arr['destinyurl']])) {
            $options['cToUrl'] = $csv[$cMapping_arr['destinyurl']];
        }
        $options['cIso'] = $oSprache->cISO;
        if (isset($csv[$cMapping_arr['languageiso']])) {
            $options['cIso'] = $csv[$cMapping_arr['languageiso']];
        }
        if ($options['cArtNr'] === null && $options['cToUrl'] === null) {
            $cError_arr[] = "Row {$nRow}: articlenumber und destinyurl sind nicht vorhanden oder fehlerhaft";
        } elseif ($options['cArtNr'] !== null && $options['cToUrl'] !== null) {
            $cError_arr[] = "Row {$nRow}: Nur articlenumber und destinyurl darf vorhanden sein";
        } elseif ($options['cToUrl'] !== null) {
            if (!$this->saveExt($options['cFromUrl'], $options['cToUrl'])) {
                $cError_arr[] = "Row {$nRow}: Konnte nicht gespeichert werden (Vielleicht bereits vorhanden?)";
            }
        } else {
            $cUrl = $this->getArtNrUrl($options['cArtNr'], $options['cIso']);
            if ($cUrl !== null) {
                if (!$this->saveExt($options['cFromUrl'], $cUrl)) {
                    $cError_arr[] = "Row {$nRow}: Konnte nicht gespeichert werden (Vielleicht bereits vorhanden?)";
                }
            } else {
                $cError_arr[] = "Row {$nRow}: Artikelnummer ({$options['cArtNr']}) konnte nicht im Shop gefunden werden";
            }
        }

        return $this;
    }

    /**
     * @param string $cArtNr
     * @param string $cIso
     * @return null|string
     */
    public function getArtNrUrl($cArtNr, $cIso)
    {
        if (strlen($cArtNr) > 0) {
            $oObj = Shop::DB()->query(
                "SELECT tartikel.kArtikel, tseo.cSeo
                    FROM tartikel
                    LEFT JOIN tsprache ON tsprache.cISO = '" . Shop::DB()->escape(strtolower($cIso)) . "'
                    LEFT JOIN tseo ON tseo.kKey = tartikel.kArtikel
                        AND tseo.cKey = 'kArtikel'
                        AND tseo.kSprache = tsprache.kSprache
                    WHERE tartikel.cArtNr = '" . Shop::DB()->escape($cArtNr) . "'
                    LIMIT 1", 1
            );

            return baueURL($oObj, URLART_ARTIKEL);
        }

        return;
    }

    /**
     * Parse head row from import file
     *
     * @param array $cRow_arr
     * @return array|null
     */
    public function readHeadRow($cRow_arr)
    {
        $cMapping_arr = array(
            'sourceurl' => null
        );
        // Must not be present in the file
        $cOption_arr = array('articlenumber', 'destinyurl', 'languageiso');

        if (is_array($cRow_arr) && count($cRow_arr) > 0) {
            $cMember_arr = array_keys($cMapping_arr);
            foreach ($cRow_arr as $i => $cRow) {
                $bExist = false;
                if (in_array($cRow, $cOption_arr)) {
                    $cMapping_arr[$cRow] = $i;
                    $bExist              = true;
                } else {
                    foreach ($cMember_arr as $cMember) {
                        if ($cMember == $cRow) {
                            $cMapping_arr[$cMember] = $i;
                            $bExist                 = true;
                            break;
                        }
                    }
                }

                if (!$bExist) {
                    return;
                }
            }

            return $cMapping_arr;
        }

        return;
    }

    /**
     * @param string $cUrl
     * @return bool|string
     */
    public function checkFallbackRedirect($cUrl)
    {
        $exploded = explode('/', trim($cUrl, '/'));
        if (count($exploded) > 0) {
            $lastPath = $exploded[count($exploded) - 1];
            $filename = strtok($lastPath, '?');
            $seoPath  = Shop::DB()->select('tseo', 'cSeo', $lastPath);
            if ((isset($seoPath->cSeo) && strlen($seoPath->cSeo) > 0) || $filename === 'jtl.php' ||
                $filename === 'warenkorb.php' || $filename === 'kontakt.php' || $filename === 'news.php') {
                return $lastPath;
            }
        }

        return false;
    }

    /**
     * @param string $cUrl
     * @return bool|string
     */
    public function test($cUrl)
    {
        //Fallback e.g. if last URL-Path exists in tseo --> do not track 404 hit, instant redirect!
        if ($fallbackPath = $this->checkFallbackRedirect($cUrl)) {
            return $fallbackPath;
        }
        $cRedirectUrl = false;
        $cUrl         = $this->normalize($cUrl);
        if ($this->isValid($cUrl)) {
            if (is_string($cUrl) && strlen($cUrl) > 0) {
                $parsedUrl       = parse_url($cUrl);
                $cUrlQueryString = null;
                if (isset($parsedUrl['query']) && isset($parsedUrl['path'])) {
                    $cUrl            = $parsedUrl['path'];
                    $cUrlQueryString = $parsedUrl['query'];
                }
                $oItem = $this->find($cUrl);
                if (!is_object($oItem)) {
                    $conf = Shop::getConfig(array(CONF_GLOBAL));
                    if (!isset($_GET['notrack']) && (!isset($conf['global']['redirect_save_404']) || $conf['global']['redirect_save_404'] === 'Y')) {
                        $oItem           = new self();
                        $oItem->cFromUrl = $cUrl;
                        $oItem->cToUrl   = '';
                        unset($oItem->kRedirect);
                        $oItem->kRedirect = Shop::DB()->insert('tredirect', $oItem);
                    }
                } elseif (strlen($oItem->cToUrl) > 0) {
                    $cRedirectUrl = $oItem->cToUrl . (($cUrlQueryString !== null) ? ('?' . $cUrlQueryString) : '');
                }
                $cReferer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
                if (strlen($cReferer) > 0) {
                    $cReferer = $this->normalize($cReferer);
                }
                $cIP = getRealIp();
                // Eintrag für diese IP bereits vorhanden?
                $oEntry = Shop::DB()->query(
                    "SELECT *
                        FROM tredirectreferer tr
                        LEFT JOIN tredirect t ON t.kRedirect = tr.kRedirect
                        WHERE tr.cIP = '{$cIP}'
                        AND t.cFromUrl = '{$cUrl}' LIMIT 1", 1
                );
                if ($oEntry === false || $oEntry === null || (is_object($oEntry) && $oItem->nCount == 0)) {
                    $oReferer               = new stdClass();
                    $oReferer->kRedirect    = (isset($oItem->kRedirect)) ? $oItem->kRedirect : 0;
                    $oReferer->kBesucherBot = (isset($_SESSION['oBesucher']->kBesucherBot)) ? intval($_SESSION['oBesucher']->kBesucherBot) : 0;
                    $oReferer->cRefererUrl  = (is_string($cReferer)) ? $cReferer : '';
                    $oReferer->cIP          = $cIP;
                    $oReferer->dDate        = time();
                    Shop::DB()->insert('tredirectreferer', $oReferer);
                    if (is_object($oItem)) {
                        if (!isset($oItem->nCount)) {
                            $oItem->nCount = 0;
                        }
                        $oItem->nCount++;
                        Shop::DB()->update('tredirect', 'kRedirect', $oItem->kRedirect, $oItem);
                    }
                }
            }
        }

        return $cRedirectUrl;
    }

    /**
     * @param string $cUrl
     * @return bool
     */
    public function isValid($cUrl)
    {
        $cPath_arr       = pathinfo($cUrl);
        $cInvalidExt_arr = array(
            'jpg',
            'gif',
            'bmp',
            'xml',
            'ico',
            'txt',
            'png'
        );

        if (isset($cPath_arr['extension']) && strlen($cPath_arr['extension']) > 0) {
            $cExt = strtolower($cPath_arr['extension']);
            if (in_array($cExt, $cInvalidExt_arr)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $cUrl
     * @return bool
     */
    public function isAvailable($cUrl)
    {
        $sep = (parse_url($cUrl, PHP_URL_QUERY) === null) ? '?' : '&';
        $cUrl .= $sep . 'notrack';
        $cHeader_arr = @get_headers($cUrl);
        if (empty($cHeader_arr)) {
            return false;
        }
        foreach ($cHeader_arr as $head) { //Nur der letzte Status Code ist relevant (Redirects werden übersprungen)
            if (preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $head)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $cUrl
     * @return string
     */
    public function normalize($cUrl)
    {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.helper.Url.php';
        $oUrl = new UrlHelper();
        $oUrl->setUrl($cUrl);

        $cUrl = $oUrl->normalize();
        $cUrl = trim($cUrl, "\\/");
        $cUrl = "/{$cUrl}";

        return $cUrl;
    }

    /**
     * @param string $bUmgeleiteteUrls
     * @param string $cSuchbegriff
     * @return int
     */
    public function getCount($bUmgeleiteteUrls, $cSuchbegriff)
    {
        $where = '';
        if ($bUmgeleiteteUrls == '1' || !empty($cSuchbegriff)) {
            $where .= 'WHERE ';
        }
        if ($bUmgeleiteteUrls == '1') {
            $where .= ' cToUrl != ""';
        }
        if (!empty($cSuchbegriff) && $bUmgeleiteteUrls == '1') {
            $where .= ' AND ';
        }
        if (!empty($cSuchbegriff)) {
            $where .= "cFromUrl like '%{$cSuchbegriff}%'";
        }
        $oCount = Shop::DB()->query("SELECT COUNT(*) AS nCount FROM tredirect {$where}", 1);
        if (is_object($oCount)) {
            return intval($oCount->nCount);
        }

        return 0;
    }

    /**
     * @param int    $nStart
     * @param int    $nLimit
     * @param string $bUmgeleiteteUrls
     * @param string $cSortierFeld
     * @param string $cSortierung
     * @param string $cSuchbegriff
     * @param bool   $cMitVerweis
     * @return mixed
     */
    public function getList($nStart, $nLimit, $bUmgeleiteteUrls, $cSortierFeld, $cSortierung, $cSuchbegriff, $cMitVerweis = true)
    {
        $cSub_arr = array(
            'dFirst',
            'dLast'
        );
        if (in_array($cSortierFeld, $cSub_arr)) {
            $cSortierFeld = "tredirectreferer.{$cSortierFeld}";
        }

        $where = '';
        if ($bUmgeleiteteUrls == '1' || $bUmgeleiteteUrls == '2' || !empty($cSuchbegriff)) {
            $where .= 'WHERE ';
        }
        if ($bUmgeleiteteUrls == '1') {
            $where .= ' cToUrl != ""';
        } elseif ($bUmgeleiteteUrls === '2') {
            $where .= ' cToUrl = ""';
        }
        if (!empty($cSuchbegriff) && $bUmgeleiteteUrls == '1') {
            $where .= ' AND ';
        }
        if (!empty($cSuchbegriff)) {
            $where .= "cFromUrl LIKE '%{$cSuchbegriff}%'";
        }
        $oRedirect_arr = Shop::DB()->query(
            "SELECT tredirect.kRedirect, tredirect.cFromUrl, tredirect.cToUrl, tredirect.nCount
                FROM tredirect {$where}
                ORDER BY {$cSortierFeld} {$cSortierung} LIMIT {$nStart},{$nLimit}", 2
        );

        if ($cMitVerweis) {
            if (is_array($oRedirect_arr) && count($oRedirect_arr)) {
                foreach ($oRedirect_arr as &$oRedirect) {
                    $oRedirect->oRedirectReferer_arr = $this->getVerweise($oRedirect->kRedirect);
                }
            }
        }

        return $oRedirect_arr;
    }

    /**
     * @param int $kRedirect
     * @return mixed
     */
    public function getVerweise($kRedirect)
    {
        return Shop::DB()->query(
            "SELECT tredirectreferer.*, tbesucherbot.cName AS cBesucherBotName, tbesucherbot.cUserAgent AS cBesucherBotAgent
                FROM tredirectreferer
                LEFT JOIN tbesucherbot
                    ON tredirectreferer.kBesucherBot = tbesucherbot.kBesucherBot
                    WHERE kRedirect = " . intval($kRedirect) . "
                ORDER BY dDate ASC LIMIT 100", 2
        );
    }
}
