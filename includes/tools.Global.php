<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string             $seite
 * @param KategorieListe|int $KategorieListe
 * @param Artikel|int        $Artikel
 * @param string             $linkname
 * @param string             $linkURL
 * @param int                $kLink
 * @return string
 */
function createNavigation($seite, $KategorieListe = 0, $Artikel = 0, $linkname = '', $linkURL = '', $kLink = 0)
{
    $shopURL = Shop::getURL() . '/';
    if (strpos($linkURL, $shopURL) !== false) {
        $linkURL = str_replace($shopURL, '', $linkURL);
    }
    $brotnavi          = [];
    $SieSindHierString = Shop::Lang()->get('youarehere', 'breadcrumb') .
        ': <a href="' . $shopURL . '">' .
        Shop::Lang()->get('startpage', 'breadcrumb') . '</a>';
    $ele0              = new stdClass();
    $ele0->name        = Shop::Lang()->get('startpage', 'breadcrumb');
    $ele0->url         = '/';
    $ele0->urlFull     = $shopURL;
    $ele0->hasChild    = false;

    $brotnavi[]    = $ele0;
    $linkHelper    = LinkHelper::getInstance();
    $ele           = new stdClass();
    $ele->hasChild = false;
    switch ($seite) {
        case 'STARTSEITE':
            $SieSindHierString .= '<br />';
            break;

        case 'ARTIKEL':
            if (!isset($KategorieListe->elemente) || count($KategorieListe->elemente) === 0) {
                break;
            }
            $cntchr    = 0;
            $elemCount = count($KategorieListe->elemente) - 1;
            for ($i = $elemCount; $i >= 0; $i--) {
                $cntchr += strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
            }
            for ($i = $elemCount; $i >= 0; $i--) {
                if (isset($KategorieListe->elemente[$i]->cKurzbezeichnung, $KategorieListe->elemente[$i]->cURL)) {
                    if ($cntchr < 80) {
                        $SieSindHierString .= ' &gt; <a href="' . $KategorieListe->elemente[$i]->cURLFull . '">'
                            . $KategorieListe->elemente[$i]->cKurzbezeichnung . '</a>';
                    } else {
                        $cntchr            -= strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
                        $SieSindHierString .= ' &gt; ...';
                    }
                    $ele           = new stdClass();
                    $ele->hasChild = false;
                    $ele->name     = $KategorieListe->elemente[$i]->cKurzbezeichnung;
                    $ele->url      = $KategorieListe->elemente[$i]->cURL;
                    $ele->urlFull  = $KategorieListe->elemente[$i]->cURLFull;
                    $brotnavi[]    = $ele;
                }
            }
            $SieSindHierString .= ' &gt; <a href="' . $Artikel->cURLFull . '">' . $Artikel->cKurzbezeichnung . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $Artikel->cKurzbezeichnung;
            $ele->url           = $Artikel->cURL;
            $ele->urlFull       = $Artikel->cURLFull;
            if ($Artikel->isChild()) {
                $Vater                   = new Artikel();
                $oArtikelOptionen        = new stdClass();
                $oArtikelOptionen->nMain = 1;
                $Vater->fuelleArtikel($Artikel->kVaterArtikel, $oArtikelOptionen);
                $ele->name     = $Vater->cKurzbezeichnung;
                $ele->url      = $Vater->cURL;
                $ele->urlFull  = $Vater->cURLFull;
                $ele->hasChild = true;
            }
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'PRODUKTE':
            $cntchr    = 0;
            $elemCount = isset($KategorieListe->elemente) ? count($KategorieListe->elemente) : 0;
            for ($i = $elemCount - 1; $i >= 0; $i--) {
                $cntchr += strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
            }
            for ($i = $elemCount - 1; $i >= 0; $i--) {
                if ($cntchr < 80) {
                    $SieSindHierString .= ' &gt; <a href="' . $KategorieListe->elemente[$i]->cURLFull . '">'
                        . $KategorieListe->elemente[$i]->cKurzbezeichnung . '</a>';
                } else {
                    $cntchr            -= strlen($KategorieListe->elemente[$i]->cKurzbezeichnung);
                    $SieSindHierString .= ' &gt; ...';
                }
                $ele           = new stdClass();
                $ele->hasChild = false;
                $ele->name     = $KategorieListe->elemente[$i]->cKurzbezeichnung;
                $ele->url      = $KategorieListe->elemente[$i]->cURL;
                $ele->urlFull  = $KategorieListe->elemente[$i]->cURLFull;
                $brotnavi[]    = $ele;
            }

            $SieSindHierString .= '<br />';
            break;

        case 'WARENKORB':
            $url                = $linkHelper->getStaticRoute('warenkorb.php', false);
            $urlFull            = $linkHelper->getStaticRoute('warenkorb.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('basket', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('basket', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'PASSWORT VERGESSEN':
            $url                = $linkHelper->getStaticRoute('pass.php', false);
            $urlFull            = $linkHelper->getStaticRoute('pass.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('forgotpassword', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('forgotpassword', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'MEIN KONTO':
            $cText              = Session::Customer()->kKunde > 0
                ? Shop::Lang()->get('account', 'breadcrumb')
                : Shop::Lang()->get('login', 'breadcrumb');
            $url                = $linkHelper->getStaticRoute('jtl.php', false);
            $urlFull            = $linkHelper->getStaticRoute('jtl.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' . $cText . '</a>';
            $ele->name          = $cText;
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'BESTELLVORGANG':
            $url                = $linkHelper->getStaticRoute('jtl.php', false);
            $urlFull            = $linkHelper->getStaticRoute('jtl.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('checkout', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('checkout', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'REGISTRIEREN':
            $url                = $linkHelper->getStaticRoute('registrieren.php', false);
            $urlFull            = $linkHelper->getStaticRoute('registrieren.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('register', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('register', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'KONTAKT':
            $url                = $linkHelper->getStaticRoute('kontakt.php', false);
            $urlFull            = $linkHelper->getStaticRoute('kontakt.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('contact', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('contact', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'WARTUNG':
            $url                = $linkHelper->getStaticRoute('wartung.php', false);
            $urlFull            = $linkHelper->getStaticRoute('wartung.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('maintainance', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('maintainance', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSLETTER':
            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' .
                Shop::Lang()->get('newsletter', 'breadcrumb') . '</a>';
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWS':
        case 'UMFRAGE':
            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' . $linkname . '</a>';
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSDETAIL':
            $url                = $linkHelper->getStaticRoute('news.php', false);
            $urlFull            = $linkHelper->getStaticRoute('news.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('news', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('news', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;

            $SieSindHierString .= ' &gt; <a href="' . $linkURL . '">' . $linkname . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSKATEGORIE':
            $url                = $linkHelper->getStaticRoute('news.php', false);
            $urlFull            = $linkHelper->getStaticRoute('news.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('newskat', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('newskat', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;

            $SieSindHierString .= ' &gt; <a href="' . $linkURL . '">' . $linkname . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'NEWSMONAT':
            $url                = $linkHelper->getStaticRoute('news.php', false);
            $urlFull            = $linkHelper->getStaticRoute('news.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('newsmonat', 'breadcrumb') . '</a>';
            $ele->name          = Shop::Lang()->get('newsmonat', 'breadcrumb');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;

            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' . $linkname . '</a>';
            $ele                = new stdClass();
            $ele->hasChild      = false;
            $ele->name          = $linkname;
            $ele->url           = $linkURL;
            $ele->urlFull       = $shopURL . $linkURL;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'VERGLEICHSLISTE':
            $url                = $linkHelper->getStaticRoute('vergleichsliste.php', false);
            $urlFull            = $linkHelper->getStaticRoute('vergleichsliste.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('compare') . '</a>';
            $ele->name          = Shop::Lang()->get('compare');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        case 'WUNSCHLISTE':
            $url                = $linkHelper->getStaticRoute('wunschliste.php', false);
            $urlFull            = $linkHelper->getStaticRoute('wunschliste.php');
            $SieSindHierString .= ' &gt; <a href="' . $urlFull . '">' .
                Shop::Lang()->get('wishlist') . '</a>';
            $ele->name          = Shop::Lang()->get('wishlist');
            $ele->url           = $url;
            $ele->urlFull       = $urlFull;
            $brotnavi[]         = $ele;
            $SieSindHierString .= '<br />';
            break;

        default:
            $SieSindHierString .= ' &gt; <a href="' . $shopURL . $linkURL . '">' . $linkname . '</a>';
            $SieSindHierString .= '<br />';
            $oLink              = $kLink > 0 ? $linkHelper->getLinkObject($kLink) : null;
            $kVaterLink         = isset($oLink->kVaterLink) ? (int)$oLink->kVaterLink : null;
            $elems              = [];
            do {
                if ($kVaterLink === 0 || $kVaterLink === null) {
                    break;
                }
                $oItem = Shop::Container()->getDB()->select('tlink', 'kLink', $kVaterLink);
                if (!is_object($oItem)) {
                    break;
                }
                $oItem          = $linkHelper->getPageLink($oItem->kLink);
                $oItem->Sprache = $linkHelper->getPageLinkLanguage($oItem->kLink);
                $itm            = new stdClass();
                $itm->name      = $oItem->Sprache->cName;
                $itm->url       = baueURL($oItem, URLART_SEITE);
                $itm->urlFull   = baueURL($oItem, URLART_SEITE, 0, false, true);
                $itm->hasChild  = false;
                $elems[]        = $itm;
                $kVaterLink     = (int)$oItem->kVaterLink;
            } while (true);

            $elems        = array_reverse($elems);
            $brotnavi     = array_merge($brotnavi, $elems);
            $ele->name    = $linkname;
            $ele->url     = $linkURL;
            $ele->urlFull = $shopURL . $linkURL;
            $brotnavi[]   = $ele;
            break;
    }
    executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_CREATENAVIGATION, ['navigation' => &$brotnavi]);
    Shop::Smarty()->assign('Brotnavi', $brotnavi);

    return $SieSindHierString;
}

/**
 * @param float $price
 * @return string
 */
function gibPreisString($price)
{
    return str_replace(',', '.', sprintf('%.2f', $price));
}

/**
 * @param float      $price
 * @param object|int $currency
 * @param int        $html
 * @param int        $decimals
 * @return string
 */
function gibPreisStringLocalized($price, $currency = 0, $html = 1, $decimals = 2)
{
    if ($currency === 0 || is_numeric($currency)) {
        $currency = Session::Currency();
    } elseif (get_class($currency) === 'stdClass') {
        $currency = new Currency($currency->kWaehrung);
    }
    $localized    = number_format(
        $price * $currency->getConversionFactor(),
        $decimals,
        $currency->getDecimalSeparator(),
        $currency->getThousandsSeparator()
    );
    $currencyName = $html ? $currency->getHtmlEntity() : $currency->getName();

    return $currency->getForcePlacementBeforeNumber()
        ? ($currencyName . ' ' . $localized)
        : ($localized . ' ' . $currencyName);
}

/**
 * @param float $price
 * @param float $taxRate
 * @param int   $precision
 * @return float
 */
function berechneBrutto($price, $taxRate, $precision = 2)
{
    return round($price * (100 + $taxRate) / 100, (int)$precision);
}

/**
 * @param float $fPreisBrutto
 * @param float $taxRate
 * @param int   $precision
 * @return float
 */
function berechneNetto($fPreisBrutto, $taxRate, $precision = 2)
{
    return round($fPreisBrutto / (100 + (float)$taxRate) * 100, $precision);
}

/**
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cClass
 * @param bool   $bForceSteuer
 * @return string
 */
function getCurrencyConversion($fPreisNetto, $fPreisBrutto, $cClass = '', $bForceSteuer = true)
{
    $cString       = '';
    $oWaehrung_arr = Shop::Container()->getDB()->query(
        "SELECT * 
            FROM twaehrung 
            ORDER BY cStandard DESC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($oWaehrung_arr) > 0) {
        $oSteuerklasse = Shop::Container()->getDB()->select('tsteuerklasse', 'cStandard', 'Y');
        $kSteuerklasse = $oSteuerklasse !== null ? (int)$oSteuerklasse->kSteuerklasse : 1;
        if ((float)$fPreisNetto > 0) {
            $fPreisNetto  = (float)$fPreisNetto;
            $fPreisBrutto = berechneBrutto((float)$fPreisNetto, gibUst($kSteuerklasse));
        } elseif ((float)$fPreisBrutto > 0) {
            $fPreisNetto  = berechneNetto((float)$fPreisBrutto, gibUst($kSteuerklasse));
            $fPreisBrutto = (float)$fPreisBrutto;
        }
        $cString = '<span class="preisstring ' . $cClass . '">';
        foreach ($oWaehrung_arr as $i => $oWaehrung) {
            $cPreisLocalized       = number_format(
                $fPreisNetto * $oWaehrung->fFaktor,
                2,
                $oWaehrung->cTrennzeichenCent,
                $oWaehrung->cTrennzeichenTausend
            );
            $cPreisBruttoLocalized = number_format(
                $fPreisBrutto * $oWaehrung->fFaktor,
                2,
                $oWaehrung->cTrennzeichenCent,
                $oWaehrung->cTrennzeichenTausend
            );

            if ($oWaehrung->cVorBetrag === 'Y') {
                $cPreisLocalized       = $oWaehrung->cNameHTML . ' ' . $cPreisLocalized;
                $cPreisBruttoLocalized = $oWaehrung->cNameHTML . ' ' . $cPreisBruttoLocalized;
            } else {
                $cPreisLocalized       = $cPreisLocalized . ' ' . $oWaehrung->cNameHTML;
                $cPreisBruttoLocalized = $cPreisBruttoLocalized . ' ' . $oWaehrung->cNameHTML;
            }
            // Wurde geändert weil der Preis nun als Betrag gesehen wird
            // und die Steuer direkt in der Versandart als eSteuer Flag eingestellt wird
            if ($i > 0) {
                $cString .= $bForceSteuer
                    ? ('<br><strong>' . $cPreisBruttoLocalized . '</strong>' .
                        ' (<em>' . $cPreisLocalized . ' ' .
                        Shop::Lang()->get('net') . '</em>)')
                    : ('<br> ' . $cPreisBruttoLocalized);
            } else {
                $cString .= $bForceSteuer
                    ? ('<strong>' . $cPreisBruttoLocalized . '</strong>' .
                        ' (<em>' . $cPreisLocalized . ' ' .
                        Shop::Lang()->get('net') . '</em>)')
                    : '<strong>' . $cPreisBruttoLocalized . '</strong>';
            }
        }
        $cString .= '</span>';
    }

    return $cString;
}

/**
 * @param string $var
 * @return bool
 */
function hasGPCDataInteger($var)
{
    return isset($_POST[$var]) || isset($_GET[$var]) || isset($_COOKIE[$var]);
}

/**
 * @param string $var
 * @return array
 */
function verifyGPDataIntegerArray($var)
{
    if (isset($_REQUEST[$var])) {
        $val = $_REQUEST[$var];

        return is_numeric($val)
            ? [(int)$val]
            : array_map(function ($e) {
                return (int)$e;
            }, $val);
    }

    return [];
}


/**
 * @param string $var
 * @return int
 */
function verifyGPCDataInteger($var)
{
    if (isset($_GET[$var]) && is_numeric($_GET[$var])) {
        return (int)$_GET[$var];
    }
    if (isset($_POST[$var]) && is_numeric($_POST[$var])) {
        return (int)$_POST[$var];
    }
    if (isset($_COOKIE[$var]) && is_numeric($_COOKIE[$var])) {
        return (int)$_COOKIE[$var];
    }

    return 0;
}

/**
 * @param string $var
 * @return string
 */
function verifyGPDataString($var)
{
    if (isset($_POST[$var])) {
        return $_POST[$var];
    }
    if (isset($_GET[$var])) {
        return $_GET[$var];
    }

    return '';
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
 * @return mixed
 */
function getRealIp()
{
    $ip = null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip   = $list[0];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return ($ip = filter_var($ip, FILTER_VALIDATE_IP)) === false ? '0.0.0.0' : $ip;
}

/**
 * @param bool $bBestellung
 * @return mixed|string
 */
function gibIP($bBestellung = false)
{
    $ip   = getRealIp();
    $conf = Shop::getSettings([CONF_KAUFABWICKLUNG, CONF_GLOBAL]);
    if (($bBestellung && $conf['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'N')
        || (!$bBestellung && $conf['global']['global_ips_speichern'] === 'N')
    ) {
        $ip = substr($ip, 0, strpos($ip, '.', strpos($ip, '.') + 1) + 1) . '*.*';
    }

    return $ip;
}

/**
 * @param array $variBoxAnzahl_arr
 * @param int   $kArtikel
 * @param bool  $bIstVater
 * @param bool  $bExtern
 */
function fuegeVariBoxInWK($variBoxAnzahl_arr, $kArtikel, $bIstVater, $bExtern = false)
{
    if (!is_array($variBoxAnzahl_arr) || count($variBoxAnzahl_arr) === 0) {
        return;
    }
    $cKeys_arr     = array_keys($variBoxAnzahl_arr);
    $kVaterArtikel = $kArtikel;
    $attributes    = [];
    unset($_SESSION['variBoxAnzahl_arr']);
    // Es ist min. eine Anzahl vorhanden
    foreach ($cKeys_arr as $cKeys) {
        if ((float)$variBoxAnzahl_arr[$cKeys] <= 0) {
            continue;
        }
        // Switch zwischen 1 Vari und 2
        if ($cKeys[0] === '_') { // 1
            $cVariation0 = substr($cKeys, 1);
            list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
            // In die Session einbauen
            $oVariKombi                                 = new stdClass();
            $oVariKombi->fAnzahl                        = (float)$variBoxAnzahl_arr[$cKeys];
            $oVariKombi->cVariation0                    = StringHandler::filterXSS($cVariation0);
            $oVariKombi->kEigenschaft0                  = (int)$kEigenschaft0;
            $oVariKombi->kEigenschaftWert0              = (int)$kEigenschaftWert0;
            $_SESSION['variBoxAnzahl_arr'][$cKeys]      = $oVariKombi;
            $_POST['eigenschaftwert_' . $kEigenschaft0] = $kEigenschaftWert0;
        } else {
            if ($bExtern) {
                $cComb_arr                        = explode('_', $cKeys);
                $oVariKombi                       = new stdClass();
                $oVariKombi->fAnzahl              = (float)$variBoxAnzahl_arr[$cKeys];
                $oVariKombi->kEigenschaft_arr     = [];
                $oVariKombi->kEigenschaftWert_arr = [];
                foreach ($cComb_arr as $cComb) {
                    list($kEigenschaft, $kEigenschaftWert) = explode(':', $cComb);
                    $oVariKombi->kEigenschaft_arr[]            = (int)$kEigenschaft;
                    $oVariKombi->kEigenschaftWert_arr[]        = (int)$kEigenschaftWert;
                    $_POST['eigenschaftwert_' . $kEigenschaft] = (int)$kEigenschaftWert;
                }
                $_SESSION['variBoxAnzahl_arr'][$cKeys] = $oVariKombi;
            } else {
                list($cVariation0, $cVariation1) = explode('_', $cKeys);
                list($kEigenschaft0, $kEigenschaftWert0) = explode(':', $cVariation0);
                list($kEigenschaft1, $kEigenschaftWert1) = explode(':', $cVariation1);
                // In die Session einbauen
                $oVariKombi                                 = new stdClass();
                $oVariKombi->fAnzahl                        = (float)$variBoxAnzahl_arr[$cKeys];
                $oVariKombi->cVariation0                    = StringHandler::filterXSS($cVariation0);
                $oVariKombi->cVariation1                    = StringHandler::filterXSS($cVariation1);
                $oVariKombi->kEigenschaft0                  = (int)$kEigenschaft0;
                $oVariKombi->kEigenschaftWert0              = (int)$kEigenschaftWert0;
                $oVariKombi->kEigenschaft1                  = (int)$kEigenschaft1;
                $oVariKombi->kEigenschaftWert1              = (int)$kEigenschaftWert1;
                $_SESSION['variBoxAnzahl_arr'][$cKeys]      = $oVariKombi;
                $_POST['eigenschaftwert_' . $kEigenschaft0] = $kEigenschaftWert0;
                $_POST['eigenschaftwert_' . $kEigenschaft1] = $kEigenschaftWert1;
            }
        }
        $attributes[$cKeys]                   = new stdClass();
        $attributes[$cKeys]->oEigenschaft_arr = [];
        $attributes[$cKeys]->kArtikel         = 0;

        if ($bIstVater) {
            $kArtikel                             = ArtikelHelper::getArticleForParent($kVaterArtikel);
            $attributes[$cKeys]->oEigenschaft_arr = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel);
            $attributes[$cKeys]->kArtikel         = $kArtikel;
        } else {
            $attributes[$cKeys]->oEigenschaft_arr = ArtikelHelper::getSelectedPropertiesForArticle($kArtikel);
            $attributes[$cKeys]->kArtikel         = $kArtikel;
        }
    }
    $nRedirectErr_arr = [];
    if (!is_array($attributes) || count($attributes) === 0) {
        return;
    }
    $defaultOptions = Artikel::getDefaultOptions();
    foreach ($attributes as $i => $oAlleEigenschaftPre) {
        // Prüfe ob er Artikel in den Warenkorb gelegt werden darf
        $nRedirect_arr = WarenkorbHelper::addToCartCheck(
            (new Artikel())->fuelleArtikel($oAlleEigenschaftPre->kArtikel, $defaultOptions),
            (float)$variBoxAnzahl_arr[$i],
            $oAlleEigenschaftPre->oEigenschaft_arr
        );

        $_SESSION['variBoxAnzahl_arr'][$i]->bError = false;
        if (count($nRedirect_arr) > 0) {
            foreach ($nRedirect_arr as $nRedirect) {
                $nRedirect = (int)$nRedirect;
                if (!in_array($nRedirect, $nRedirectErr_arr, true)) {
                    $nRedirectErr_arr[] = $nRedirect;
                }
            }
            $_SESSION['variBoxAnzahl_arr'][$i]->bError = true;
        }
    }

    if (count($nRedirectErr_arr) > 0) {
        //redirekt zum artikel, um variation/en zu wählen / MBM beachten
        $articleID = $bIstVater
            ? $kVaterArtikel
            : $kArtikel;
        header('Location: ' . Shop::getURL() . '/?a=' . $articleID .
            '&r=' . implode(',', $nRedirectErr_arr), true, 302);
        exit();
    }
    foreach ($attributes as $i => $oAlleEigenschaftPost) {
        if (!$_SESSION['variBoxAnzahl_arr'][$i]->bError) {
            //#8224, #7482 -> do not call setzePositionsPreise() in loop @ Wanrekob::fuegeEin()
            fuegeEinInWarenkorb(
                $oAlleEigenschaftPost->kArtikel,
                (float)$variBoxAnzahl_arr[$i],
                $oAlleEigenschaftPost->oEigenschaft_arr,
                0,
                false,
                0,
                null,
                false
            );
        }
    }
    Session::Cart()->setzePositionsPreise();
    unset($_SESSION['variBoxAnzahl_arr']);
    Session::Cart()->redirectTo();
}

/**
 * @param int    $kArtikel
 * @param float  $fAnzahl
 * @param array  $oEigenschaftwerte_arr
 * @param bool   $cUnique
 * @param int    $kKonfigitem
 * @param int    $nPosTyp
 * @param string $cResponsibility
 */
function fuegeEinInWarenkorbPers(
    $kArtikel,
    $fAnzahl,
    $oEigenschaftwerte_arr,
    $cUnique = false,
    $kKonfigitem = 0,
    $nPosTyp = C_WARENKORBPOS_TYP_ARTIKEL,
    $cResponsibility = 'core'
) {
    if (!Session::Customer()->isLoggedIn()) {
        return;
    }
    $conf = Shop::getSettings([CONF_GLOBAL]);
    if ($conf['global']['warenkorbpers_nutzen'] !== 'Y') {
        return;
    }
    $nPosTyp  = (int)$nPosTyp;
    $kArtikel = (int)$kArtikel;
    // Persistenter Warenkorb
    if ($kArtikel > 0) {
        // Pruefe auf kArtikel
        $oArtikelVorhanden = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel', $kArtikel,
            null, null,
            null, null,
            false,
            'kArtikel, cName'
        );
        // Falls Artikel vorhanden
        if ($oArtikelVorhanden !== null) {
            // Sichtbarkeit pruefen
            $oSichtbarkeit = Shop::Container()->getDB()->select(
                'tartikelsichtbarkeit',
                'kArtikel', $kArtikel,
                'kKundengruppe', Session::CustomerGroup()->getID(),
                null, null,
                false,
                'kArtikel'
            );
            if (empty($oSichtbarkeit) || !isset($oSichtbarkeit->kArtikel) || !$oSichtbarkeit->kArtikel) {
                $oWarenkorbPers = new WarenkorbPers(Session::Customer()->getID());
                if ($nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                    $oWarenkorbPers->loescheGratisGeschenkAusWarenkorbPers();
                }
                $oWarenkorbPers->fuegeEin(
                    $kArtikel,
                    $oArtikelVorhanden->cName,
                    $oEigenschaftwerte_arr,
                    $fAnzahl,
                    $cUnique,
                    $kKonfigitem,
                    $nPosTyp,
                    $cResponsibility
                );
            }
        }
    } elseif ($kArtikel === 0 && !empty($kKonfigitem)) {
        // Konfigitems ohne Artikelbezug
        $konfItem       = new Konfigitemsprache($kKonfigitem, Shop::getLanguageID());
        $oWarenkorbPers = new WarenkorbPers(Session::Customer()->getID());
        $oWarenkorbPers->fuegeEin(
            $kArtikel,
            $konfItem->getName(),
            $oEigenschaftwerte_arr,
            $fAnzahl,
            $cUnique,
            $kKonfigitem,
            $nPosTyp,
            $cResponsibility
        );
    }
}

/**
 * Gibt den kArtikel von einem Varikombi Kind zurück und braucht dafür Eigenschaften und EigenschaftsWerte
 * Klappt nur bei max. 2 Dimensionen
 *
 * @param int $kArtikel
 * @param int $kEigenschaft0
 * @param int $kEigenschaftWert0
 * @param int $kEigenschaft1
 * @param int $kEigenschaftWert1
 * @return int
 */
function findeKindArtikelZuEigenschaft($kArtikel, $kEigenschaft0, $kEigenschaftWert0, $kEigenschaft1 = 0, $kEigenschaftWert1 = 0)
{
    if ($kEigenschaft0 > 0 && $kEigenschaftWert0 > 0) {
        $cSQLJoin   = " JOIN teigenschaftkombiwert
                          ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                          AND teigenschaftkombiwert.kEigenschaft = " . (int)$kEigenschaft0 . "
                          AND teigenschaftkombiwert.kEigenschaftWert = " . (int)$kEigenschaftWert0;
        $cSQLHaving = '';
        if ($kEigenschaft1 > 0 && $kEigenschaftWert1 > 0) {
            $cSQLJoin = " JOIN teigenschaftkombiwert
                              ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                              AND teigenschaftkombiwert.kEigenschaft IN(" . (int)$kEigenschaft0 . ", " . (int)$kEigenschaft1 . ")
                              AND teigenschaftkombiwert.kEigenschaftWert IN(" . (int)$kEigenschaftWert0 . ", " . (int)$kEigenschaftWert1 . ")";

            $cSQLHaving = " HAVING COUNT(*) = 2";
        }
        $oArtikel = Shop::Container()->getDB()->query(
            "SELECT kArtikel
                FROM tartikel
                " . $cSQLJoin . "
                WHERE tartikel.kVaterArtikel = " . (int)$kArtikel . "
                GROUP BY teigenschaftkombiwert.kEigenschaftKombi" . $cSQLHaving,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oArtikel->kArtikel) && count($oArtikel->kArtikel) > 0) {
            return (int)$oArtikel->kArtikel;
        }
    }

    return 0;
}

/**
 * @param int  $kArtikel
 * @param bool $bSichtbarkeitBeachten
 * @return array
 */
function gibVarKombiEigenschaftsWerte($kArtikel, $bSichtbarkeitBeachten = true)
{
    $oEigenschaftwerte_arr = [];
    $kArtikel              = (int)$kArtikel;
    if ($kArtikel > 0 && ArtikelHelper::isVariChild($kArtikel)) {
        $oArtikel                            = new Artikel();
        $oArtikelOptionen                    = new stdClass();
        $oArtikelOptionen->nMerkmale         = 0;
        $oArtikelOptionen->nAttribute        = 0;
        $oArtikelOptionen->nArtikelAttribute = 0;
        $oArtikelOptionen->nVariationKombi   = 1;

        if (!$bSichtbarkeitBeachten) {
            $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
        }

        $oArtikel->fuelleArtikel($kArtikel, $oArtikelOptionen);

        if ($oArtikel->oVariationenNurKind_arr !== null
            && is_array($oArtikel->oVariationenNurKind_arr)
            && count($oArtikel->oVariationenNurKind_arr) > 0
        ) {
            foreach ($oArtikel->oVariationenNurKind_arr as $oVariationenNurKind) {
                $oEigenschaftwerte                       = new stdClass();
                $oEigenschaftwerte->kEigenschaftWert     = $oVariationenNurKind->Werte[0]->kEigenschaftWert;
                $oEigenschaftwerte->kEigenschaft         = $oVariationenNurKind->kEigenschaft;
                $oEigenschaftwerte->cEigenschaftName     = $oVariationenNurKind->cName;
                $oEigenschaftwerte->cEigenschaftWertName = $oVariationenNurKind->Werte[0]->cName;

                $oEigenschaftwerte_arr[] = $oEigenschaftwerte;
            }
        }
    }

    return $oEigenschaftwerte_arr;
}

/**
 * @param int           $kArtikel
 * @param int           $anzahl
 * @param array         $oEigenschaftwerte_arr
 * @param int           $nWeiterleitung
 * @param bool          $cUnique
 * @param int           $kKonfigitem
 * @param stdClass|null $oArtikelOptionen
 * @param bool          $setzePositionsPreise
 * @param string        $cResponsibility
 * @return bool
 */
function fuegeEinInWarenkorb(
    $kArtikel,
    $anzahl,
    $oEigenschaftwerte_arr = [],
    $nWeiterleitung = 0,
    $cUnique = false,
    $kKonfigitem = 0,
    $oArtikelOptionen = null,
    $setzePositionsPreise = true,
    $cResponsibility = 'core'
) {
    $kArtikel = (int)$kArtikel;
    if (!($anzahl > 0 && ($kArtikel > 0 || $kArtikel === 0 && !empty($kKonfigitem) && !empty($cUnique)))) {
        return false;
    }
    $Artikel = new Artikel();
    if ($oArtikelOptionen === null) {
        $oArtikelOptionen = Artikel::getDefaultOptions();
    }
    $Artikel->fuelleArtikel($kArtikel, $oArtikelOptionen);
    if ((int)$anzahl != $anzahl && $Artikel->cTeilbar !== 'Y') {
        $anzahl = max((int)$anzahl, 1);
    }
    $redirectParam = WarenkorbHelper::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr);
    // verhindert, dass Konfigitems mit Preis=0 aus der Artikelkonfiguration fallen wenn 'Preis auf Anfrage' eingestellt ist
    if (!empty($kKonfigitem) && isset($redirectParam[0]) && $redirectParam[0] === R_AUFANFRAGE) {
        unset($redirectParam[0]);
    }

    if (count($redirectParam) > 0) {
        if (isset($_SESSION['variBoxAnzahl_arr'])) {
            return false;
        }
        if ($nWeiterleitung === 0) {
            $con = (strpos($Artikel->cURLFull, '?') === false) ? '?' : '&';
            if ($Artikel->kEigenschaftKombi > 0) {
                $url = empty($Artikel->cURLFull)
                    ? (Shop::getURL() . '/?a=' . $Artikel->kVaterArtikel .
                        '&a2=' . $Artikel->kArtikel . '&')
                    : ($Artikel->cURLFull . $con);
                header('Location: ' . $url . 'n=' . $anzahl . '&r=' . implode(',', $redirectParam), true, 302);
            } else {
                $url = empty($Artikel->cURLFull)
                    ? (Shop::getURL() . '/?a=' . $Artikel->kArtikel . '&')
                    : ($Artikel->cURLFull . $con);
                header('Location: ' . $url . 'n=' . $anzahl . '&r=' . implode(',', $redirectParam), true, 302);
            }
            exit;
        }

        return false;
    }
    Session::Cart()
           ->fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr, 1, $cUnique, $kKonfigitem, $setzePositionsPreise, $cResponsibility)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);

    resetNeuKundenKupon();
    unset(
        $_SESSION['VersandKupon'],
        $_SESSION['Versandart'],
        $_SESSION['Zahlungsart'],
        $_SESSION['TrustedShops']
    );
    // Wenn Kupon vorhanden und der cWertTyp prozentual ist, dann verwerfen und neuanlegen
    altenKuponNeuBerechnen();
    setzeLinks();
    // Persistenter Warenkorb
    if (!isset($_POST['login']) && !isset($_REQUEST['basket2Pers'])) {
        fuegeEinInWarenkorbPers($kArtikel, $anzahl, $oEigenschaftwerte_arr, $cUnique, $kKonfigitem);
    }
    // Hinweis
    Shop::Smarty()
        ->assign('hinweis', Shop::Lang()->get('basketAdded', 'messages'))
        ->assign('bWarenkorbHinzugefuegt', true)
        ->assign('bWarenkorbAnzahl', $anzahl);
    // Kampagne
    if (isset($_SESSION['Kampagnenbesucher'])) {
        setzeKampagnenVorgang(KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl);
    }
    // Warenkorb weiterleiten
    Session::Cart()->redirectTo((bool)$nWeiterleitung, $cUnique);

    return true;
}

/**
 *
 */
function altenKuponNeuBerechnen()
{
    // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb, dann verwerfen und neu anlegen
    if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'prozent') {
        $oKupon = $_SESSION['Kupon'];
        unset($_SESSION['Kupon']);
        Session::Cart()->setzePositionsPreise();
        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
        kuponAnnehmen($oKupon);
    }
}

/**
 * @param object $oWKPosition
 * @param object $Kupon
 * @return mixed
 */
function checkeKuponWKPos($oWKPosition, $Kupon)
{
    $oWKPosition->nPosTyp = (int)$oWKPosition->nPosTyp;
    if ($oWKPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
        return $oWKPosition;
    }
    $Artikel_qry    = " OR FIND_IN_SET('" .
        str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->cArtNr))
        . "', REPLACE(cArtikel, ';', ',')) > 0";
    $Hersteller_qry = " OR FIND_IN_SET('" .
        str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->kHersteller))
        . "', REPLACE(cHersteller, ';', ',')) > 0";
    $Kategorie_qry  = '';
    $Kunden_qry     = '';
    $kKategorie_arr = [];

    if ($oWKPosition->Artikel->kArtikel > 0 && $oWKPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
        $kArtikel = (int)$oWKPosition->Artikel->kArtikel;
        // Kind?
        if (ArtikelHelper::isVariChild($kArtikel)) {
            $kArtikel = ArtikelHelper::getParent($kArtikel);
        }
        $oKategorie_arr = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $kArtikel);
        foreach ($oKategorie_arr as $oKategorie) {
            $oKategorie->kKategorie = (int)$oKategorie->kKategorie;
            if (!in_array($oKategorie->kKategorie, $kKategorie_arr, true)) {
                $kKategorie_arr[] = $oKategorie->kKategorie;
            }
        }
    }
    foreach ($kKategorie_arr as $kKategorie) {
        $Kategorie_qry .= " OR FIND_IN_SET('" . $kKategorie . "', REPLACE(cKategorien, ';', ',')) > 0";
    }
    if (Session::Customer()->isLoggedIn()) {
        $Kunden_qry = " OR FIND_IN_SET('" . Session::Customer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
    }
    $kupons_mgl = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tkupon
            WHERE cAktiv = 'Y'
                AND dGueltigAb <= now()
                AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
                AND fMindestbestellwert <= " . Session::Cart()->gibGesamtsummeWaren(true, false) . "
                AND (kKundengruppe = -1
                    OR kKundengruppe = 0
                    OR kKundengruppe = " . Session::CustomerGroup()->getID() . ")
                AND (nVerwendungen = 0
                    OR nVerwendungen > nVerwendungenBisher)
                AND (cArtikel = '' {$Artikel_qry})
                AND (cHersteller = '-1' {$Hersteller_qry})
                AND (cKategorien = '' OR cKategorien = '-1' {$Kategorie_qry})
                AND (cKunden = '' OR cKunden = '-1' {$Kunden_qry})
                AND kKupon = " . (int)$Kupon->kKupon,
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (isset($kupons_mgl->kKupon)
        && $kupons_mgl->kKupon > 0
        && $kupons_mgl->cWertTyp === 'prozent'
        && !Session::Cart()->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)
    ) {
        $oWKPosition->fPreisEinzelNetto -= ($oWKPosition->fPreisEinzelNetto / 100) * $Kupon->fWert;
        $oWKPosition->fPreis            -= ($oWKPosition->fPreis / 100) * $Kupon->fWert;
        $oWKPosition->cHinweis           = $Kupon->cName .
            ' (' . str_replace('.', ',', $Kupon->fWert) .
            '% ' . Shop::Lang()->get('discount') . ')';

        if (is_array($oWKPosition->WarenkorbPosEigenschaftArr)) {
            foreach ($oWKPosition->WarenkorbPosEigenschaftArr as $attribute) {
                if (isset($attribute->fAufpreis) && (float)$attribute->fAufpreis > 0) {
                    $attribute->fAufpreis -= ((float)$attribute->fAufpreis / 100) * $Kupon->fWert;
                }
            }
        }
        foreach (Session::Currencies() as $currency) {
            $currencyName = $currency->getName();
            $oWKPosition->cGesamtpreisLocalized[0][$currencyName] = gibPreisStringLocalized(
                berechneBrutto($oWKPosition->fPreis * $oWKPosition->nAnzahl, gibUst($oWKPosition->kSteuerklasse)),
                $currency
            );
            $oWKPosition->cGesamtpreisLocalized[1][$currencyName] = gibPreisStringLocalized(
                $oWKPosition->fPreis * $oWKPosition->nAnzahl,
                $currency
            );
            $oWKPosition->cEinzelpreisLocalized[0][$currencyName] = gibPreisStringLocalized(
                berechneBrutto($oWKPosition->fPreis, gibUst($oWKPosition->kSteuerklasse)),
                $currency
            );
            $oWKPosition->cEinzelpreisLocalized[1][$currencyName] = gibPreisStringLocalized(
                $oWKPosition->fPreis,
                $currency
            );
        }
    }

    return $oWKPosition;
}

/**
 * @param object $oWKPosition
 * @param object $Kupon
 * @return mixed
 */
function checkSetPercentCouponWKPos($oWKPosition, $Kupon)
{
    $wkPos                = new stdClass();
    $wkPos->fPreis        = (float)0;
    $wkPos->cName         = '';
    $oWKPosition->nPosTyp = (int)$oWKPosition->nPosTyp;
    if ($oWKPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
        return $wkPos;
    }
    $Artikel_qry    = " OR FIND_IN_SET('" .
        str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->cArtNr))
        . "', REPLACE(cArtikel, ';', ',')) > 0";
    $Hersteller_qry = " OR FIND_IN_SET('" .
        str_replace('%', '\%', Shop::Container()->getDB()->escape($oWKPosition->Artikel->kHersteller))
        . "', REPLACE(cHersteller, ';', ',')) > 0";
    $Kategorie_qry  = '';
    $Kunden_qry     = '';
    $kKategorie_arr = [];

    if ($oWKPosition->Artikel->kArtikel > 0 && $oWKPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
        $kArtikel = (int)$oWKPosition->Artikel->kArtikel;
        // Kind?
        if (ArtikelHelper::isVariChild($kArtikel)) {
            $kArtikel = ArtikelHelper::getParent($kArtikel);
        }
        $categories = Shop::Container()->getDB()->selectAll('tkategorieartikel', 'kArtikel', $kArtikel, 'kKategorie');
        foreach ($categories as $category) {
            $category->kKategorie = (int)$category->kKategorie;
            if (!in_array($category->kKategorie, $kKategorie_arr, true)) {
                $kKategorie_arr[] = $category->kKategorie;
            }
        }
    }
    foreach ($kKategorie_arr as $kKategorie) {
        $Kategorie_qry .= " OR FIND_IN_SET('" . $kKategorie . "', REPLACE(cKategorien, ';', ',')) > 0";
    }
    if (Session::Customer()->isLoggedIn()) {
        $Kunden_qry = " OR FIND_IN_SET('" . Session::Customer()->getID() . "', REPLACE(cKunden, ';', ',')) > 0";
    }
    $kupons_mgl = Shop::Container()->getDB()->query(
        "SELECT *
            FROM tkupon
            WHERE cAktiv = 'Y'
                AND dGueltigAb <= now()
                AND (dGueltigBis > now() OR dGueltigBis = '0000-00-00 00:00:00')
                AND fMindestbestellwert <= " . Session::Cart()->gibGesamtsummeWaren(true, false) . "
                AND (kKundengruppe = -1
                    OR kKundengruppe = 0
                    OR kKundengruppe = " . Session::CustomerGroup()->getID() . ")
                AND (nVerwendungen = 0 OR nVerwendungen > nVerwendungenBisher)
                AND (cArtikel = '' {$Artikel_qry})
                AND (cHersteller = '-1' {$Hersteller_qry})
                AND (cKategorien = '' OR cKategorien = '-1' {$Kategorie_qry})
                AND (cKunden = '' OR cKunden = '-1' {$Kunden_qry})
                AND kKupon = " . (int)$Kupon->kKupon,
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (isset($kupons_mgl->kKupon) && $kupons_mgl->kKupon > 0 && $kupons_mgl->cWertTyp === 'prozent') {
        $wkPos->fPreis = $oWKPosition->fPreis *
            Session::Currency()->getConversionFactor() *
            $oWKPosition->nAnzahl *
            ((100 + gibUst($oWKPosition->kSteuerklasse)) / 100);
        $wkPos->cName  = $oWKPosition->cName;
    }

    return $wkPos;
}

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
 * @param array $oVariation_arr
 * @param int   $kEigenschaft
 * @param int   $kEigenschaftWert
 * @return bool|object
 */
function findeVariation($oVariation_arr, $kEigenschaft, $kEigenschaftWert)
{
    $kEigenschaftWert = (int)$kEigenschaftWert;
    $kEigenschaft     = (int)$kEigenschaft;
    foreach ($oVariation_arr as $oVariation) {
        $oVariation->kEigenschaft = (int)$oVariation->kEigenschaft;
        if ($oVariation->kEigenschaft === $kEigenschaft
            && isset($oVariation->Werte)
            && is_array($oVariation->Werte)
            && count($oVariation->Werte) > 0
        ) {
            foreach ($oVariation->Werte as $oWert) {
                $oWert->kEigenschaftWert = (int)$oWert->kEigenschaftWert;
                if ($oWert->kEigenschaftWert === $kEigenschaftWert) {
                    return $oWert;
                }
            }
        }
    }

    return false;
}

/**
 * @param int|string $steuerland
 */
function setzeSteuersaetze($steuerland = 0)
{
    $_SESSION['Steuersatz'] = [];
    $billingCountryCode     = null;
    $merchantCountryCode    = 'DE';
    $Firma                  = Shop::Container()->getDB()->query(
        "SELECT cLand 
            FROM tfirma",
        \DB\ReturnType::SINGLE_OBJECT
    );
    if (!empty($Firma->cLand)) {
        $merchantCountryCode = landISO($Firma->cLand);
    }
    if (defined('STEUERSATZ_STANDARD_LAND')) {
        $merchantCountryCode = STEUERSATZ_STANDARD_LAND;
    }
    $deliveryCountryCode = $merchantCountryCode;
    if ($steuerland) {
        $deliveryCountryCode = $steuerland;
    }
    if (!empty(Session::Customer()->cLand)) {
        $deliveryCountryCode = Session::Customer()->cLand;
        $billingCountryCode  = Session::Customer()->cLand;
    }
    if (!empty($_SESSION['Lieferadresse']->cLand)) {
        $deliveryCountryCode = $_SESSION['Lieferadresse']->cLand;
    }
    if ($billingCountryCode === null) {
        $billingCountryCode = $deliveryCountryCode;
    }
    $_SESSION['Steuerland']     = $deliveryCountryCode;
    $_SESSION['cLieferlandISO'] = $deliveryCountryCode;

    // Pruefen, ob Voraussetzungen fuer innergemeinschaftliche Lieferung (IGL) erfuellt werden #3525
    // Bedingungen fuer Steuerfreiheit bei Lieferung in EU-Ausland:
    // Kunde hat eine zum Rechnungland passende, gueltige USt-ID gesetzt &&
    // Firmen-Land != Kunden-Rechnungsland && Firmen-Land != Kunden-Lieferland
    $UstBefreiungIGL = false;
    if (!empty(Session::Customer()->cUSTID)
        && $merchantCountryCode !== $deliveryCountryCode
        && $merchantCountryCode !== $billingCountryCode
        && (strcasecmp($billingCountryCode, substr(Session::Customer()->cUSTID, 0, 2)) === 0
            || (strcasecmp($billingCountryCode, 'GR') === 0 && strcasecmp(substr(Session::Customer()->cUSTID, 0, 2), 'EL') === 0))
    ) {
        $deliveryCountry = Shop::Container()->getDB()->select('tland', 'cISO', $deliveryCountryCode);
        $shopCountry     = Shop::Container()->getDB()->select('tland', 'cISO', $merchantCountryCode);
        if (!empty($deliveryCountry->nEU) && !empty($shopCountry->nEU)) {
            $UstBefreiungIGL = true;
        }
    }
    $steuerzonen   = Shop::Container()->getDB()->query(
        "SELECT tsteuerzone.kSteuerzone
            FROM tsteuerzone, tsteuerzoneland
            WHERE tsteuerzoneland.cISO = '" . $deliveryCountryCode . "'
                AND tsteuerzoneland.kSteuerzone = tsteuerzone.kSteuerzone",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    if (count($steuerzonen) === 0) {
        // Keine Steuerzone für $deliveryCountryCode hinterlegt - das ist fatal!
        $redirURL  = LinkHelper::getInstance()->getStaticRoute('bestellvorgang.php') . '?editRechnungsadresse=1';
        $urlHelper = new UrlHelper(Shop::getURL() . $_SERVER['REQUEST_URI']);
        $country   = ISO2land($deliveryCountryCode);

        Jtllog::writeLog('Keine Steuerzone f&uuml;r "' . $country . '" hinterlegt!', JTLLOG_LEVEL_ERROR);

        if (isAjaxRequest()) {
            $link                  = new stdClass();
            $link->nLinkart        = LINKTYP_STARTSEITE;
            $link->Sprache         = new stdClass();
            $link->Sprache->cTitle = Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages');

            Shop::Smarty()
                ->assign('cFehler', Shop::Lang()->get('missingTaxZoneForDeliveryCountry', 'errorMessages', $country))
                ->assign('Link', $link);
            Shop::Smarty()->display('layout/index.tpl');
            exit;
        }

        if ($redirURL === $urlHelper->normalize()) {
            Shop::Smarty()->assign(
                'cFehler',
                Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages')
                    . '<br/>'
                    . Shop::Lang()->get('missingTaxZoneForDeliveryCountry', 'errorMessages', $country)
            );

            return;
        }

        header('Location: ' . $redirURL);
        exit;
    }
    $steuerklassen = Shop::Container()->getDB()->query("SELECT * FROM tsteuerklasse", \DB\ReturnType::ARRAY_OF_OBJECTS);
    $qry           = '';
    foreach ($steuerzonen as $i => $steuerzone) {
        if ($i === 0) {
            $qry .= " kSteuerzone = " . (int)$steuerzone->kSteuerzone;
        } else {
            $qry .= " OR kSteuerzone = " . (int)$steuerzone->kSteuerzone;
        }
    }
    if (strlen($qry) > 5) {
        foreach ($steuerklassen as $steuerklasse) {
            $steuersatz = Shop::Container()->getDB()->query(
                "SELECT fSteuersatz
                    FROM tsteuersatz
                    WHERE kSteuerklasse = " . (int)$steuerklasse->kSteuerklasse . "
                    AND (" . $qry . ") ORDER BY nPrio DESC",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($steuersatz->fSteuersatz)) {
                $_SESSION['Steuersatz'][$steuerklasse->kSteuerklasse] = $steuersatz->fSteuersatz;
            } else {
                $_SESSION['Steuersatz'][$steuerklasse->kSteuerklasse] = 0;
            }
            if ($UstBefreiungIGL) {
                $_SESSION['Steuersatz'][$steuerklasse->kSteuerklasse] = 0;
            }
        }
    }
    if (isset($_SESSION['Warenkorb']) && get_class($_SESSION['Warenkorb']) === 'Warenkorb') {
        Session::Cart()->setzePositionsPreise();
    }
}

/**
 * @param int $kSteuerklasse
 * @return mixed
 */
function gibUst($kSteuerklasse)
{
    if (!isset($_SESSION['Steuersatz']) || !is_array($_SESSION['Steuersatz']) || count($_SESSION['Steuersatz']) === 0) {
        setzeSteuersaetze();
    }
    if (isset($_SESSION['Steuersatz'])
        && is_array($_SESSION['Steuersatz'])
        && !isset($_SESSION['Steuersatz'][$kSteuerklasse])
    ) {
        $nKey_arr      = array_keys($_SESSION['Steuersatz']);
        $kSteuerklasse = $nKey_arr[0];
    }

    return $_SESSION['Steuersatz'][$kSteuerklasse];
}

/**
 * @param string $cISO
 * @return string
 */
function ISO2land($cISO)
{
    if (strlen($cISO) > 2) {
        return $cISO;
    }
    if (!isset($_SESSION['cISOSprache'])) {
        $oSprache                = gibStandardsprache(true);
        $_SESSION['cISOSprache'] = $oSprache->cISO;
    }
    $cSpalte = $_SESSION['cISOSprache'] === 'ger' ? 'cDeutsch' : 'cEnglisch';
    $land    = Shop::Container()->getDB()->select('tland', 'cISO', $cISO, null, null, null, null, false, $cSpalte);

    return $land->$cSpalte ?? $cISO;
}

/**
 * @param string $cLand
 * @return string
 */
function landISO($cLand)
{
    $iso = Shop::Container()->getDB()->select('tland', 'cDeutsch', $cLand, null, null, null, null, false, 'cISO');
    if (!empty($iso->cISO)) {
        return $iso->cISO;
    }
    $iso = Shop::Container()->getDB()->select('tland', 'cEnglisch', $cLand, null, null, null, null, false, 'cISO');
    if (!empty($iso->cISO)) {
        return $iso->cISO;
    }

    return 'noISO';
}

/**
 * @param object $obj
 * @param int    $art
 * @param int    $row
 * @param bool   $bForceNonSeo
 * @param bool   $bFull
 * @return string
 */
function baueURL($obj, $art, $row = 0, $bForceNonSeo = false, $bFull = false)
{
    $lang   = !standardspracheAktiv(true)
        ? ('&lang=' . Shop::getLanguageCode())
        : '';
    $prefix = $bFull === false ? '' : Shop::getURL() . '/';
    if ($bForceNonSeo) {
        $obj->cSeo = '';
    }
    if ($art && $obj) {
        executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_BAUEURL, ['obj' => &$obj, 'art' => &$art]);
        switch ($art) {
            case URLART_ARTIKEL:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?a=' . $obj->kArtikel . $lang;

            case URLART_KATEGORIE:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?k=' . $obj->kKategorie . $lang;
            case URLART_SEITE:
                if (!$row
                    && isset($_SESSION['cISOSprache'], $obj->cLocalizedSeo[$_SESSION['cISOSprache']])
                    && strlen($obj->cLocalizedSeo[$_SESSION['cISOSprache']])
                ) {
                    return $prefix . $obj->cLocalizedSeo[$_SESSION['cISOSprache']];
                }
                // Hole aktuelle Spezialseite und gib den URL Dateinamen zurück
                $oSpezialseite = Shop::Container()->getDB()->select('tspezialseite', 'nLinkart', (int)$obj->nLinkart);

                return !empty($oSpezialseite->cDateiname)
                    ? $prefix . $oSpezialseite->cDateiname
                    : $prefix . '?s=' . $obj->kLink . $lang;

            case URLART_HERSTELLER:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?h=' . $obj->kHersteller . $lang;

            case URLART_LIVESUCHE:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?l=' . $obj->kSuchanfrage . $lang;

            case URLART_TAG:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?t=' . $obj->kTag . $lang;

            case URLART_MERKMAL:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?m=' . $obj->kMerkmalWert . $lang;

            case URLART_NEWS:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?n=' . $obj->kNews . $lang;

            case URLART_NEWSMONAT:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?nm=' . $obj->kNewsMonatsUebersicht . $lang;

            case URLART_NEWSKATEGORIE:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?nk=' . $obj->kNewsKategorie . $lang;

            case URLART_UMFRAGE:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?u=' . $obj->kUmfrage . $lang;

            case URLART_SEARCHSPECIALS:
                return !empty($obj->cSeo) && !$row
                    ? $prefix . $obj->cSeo
                    : $prefix . '?q=' . $obj->kSuchspecial . $lang;
        }
    }

    return '';
}

/**
 * @param object $obj
 * @param int    $art
 * @return array
 */
function baueSprachURLS($obj, $art)
{
    $urls   = [];
    $seoobj = null;
    if (!($art && $obj && count(Session::Languages()) > 0)) {
        return [];
    }
    foreach (Session::Languages() as $Sprache) {
        if ((int)$Sprache->kSprache === Shop::getLanguageID()) {
            continue;
        }
        switch ($art) {
            case URLART_ARTIKEL:
                //@deprecated since 4.05 - this is now done within the article class itself
                if ($Sprache->cStandard !== 'Y') {
                    $seoobj = Shop::Container()->getDB()->query(
                        "SELECT tseo.cSeo
                            FROM tartikelsprache
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikelsprache.kArtikel
                                AND tseo.kSprache = " . (int)$Sprache->kSprache . "
                            WHERE tartikelsprache.kArtikel = " . (int)$obj->kArtikel . "
                            AND tartikelsprache.kSprache = " . (int)$Sprache->kSprache,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                } else {
                    $seoobj = Shop::Container()->getDB()->query(
                        "SELECT tseo.cSeo
                            FROM tartikel
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikel.kArtikel
                                AND tseo.kSprache = " . (int)$Sprache->kSprache . "
                            WHERE tartikel.kArtikel = " . (int)$obj->kArtikel,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                }
                $url = (isset($seoobj->cSeo) && $seoobj->cSeo)
                    ? $seoobj->cSeo
                    : '?a=' . $obj->kArtikel . '&amp;lang=' . $Sprache->cISO;
                break;

            case URLART_KATEGORIE:
                if ($Sprache->cStandard !== 'Y') {
                    $seoobj = Shop::Container()->getDB()->query(
                        "SELECT tseo.cSeo
                            FROM tkategoriesprache
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kKategorie'
                                AND tseo.kKey = tkategoriesprache.kKategorie
                                AND tseo.kSprache = " . (int)$Sprache->kSprache . "
                                WHERE tkategoriesprache.kKategorie = " . (int)$obj->kKategorie . "
                            AND tkategoriesprache.kSprache = " . (int)$Sprache->kSprache,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                } else {
                    $seoobj = Shop::Container()->getDB()->query(
                        "SELECT tseo.cSeo
                            FROM tkategorie
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kKategorie'
                                AND tseo.kKey = tkategorie.kKategorie
                                AND tseo.kSprache = " . (int)$Sprache->kSprache . "
                            WHERE tkategorie.kKategorie = " . (int)$obj->kKategorie,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                }
                $url = $seoobj->cSeo ?? '?k=' . $obj->kKategorie . '&amp;lang=' . $Sprache->cISO;
                break;

            case URLART_SEITE:
                //@deprecated since 4.05 - this is now done within the link helper
                $seoobj = Shop::Container()->getDB()->query(
                    "SELECT tseo.cSeo
                        FROM tlinksprache
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kLink'
                            AND tseo.kKey = tlinksprache.kLink
                            AND tseo.kSprache = " . (int)$Sprache->kSprache . "
                        WHERE tlinksprache.kLink = " . (int)$obj->kLink . "
                            AND tlinksprache.cISOSprache = '" . $Sprache->cISO . "'",
                        \DB\ReturnType::SINGLE_OBJECT
                );
                $url    = (isset($seoobj->cSeo) && $seoobj->cSeo)
                    ? $seoobj->cSeo
                    : '?s=' . $obj->kLink . '&amp;lang=' . $Sprache->cISO;
                break;

            default:
                $url = $obj . '&amp;lang=' . $Sprache->cISO;
                break;
        }
        $urls[$Sprache->cISO] = $url;
    }

    return $urls;
}

/**
 * @param string $lang
 */
function checkeSpracheWaehrung($lang = '')
{
    /** @var array('Vergleichsliste' => Vergleichsliste,'Warenkorb' => Warenkorb) $_SESSION */
    if (strlen($lang) > 0) {
        //Kategorien zurücksetzen, da sie lokalisiert abgelegt wurden
        if ($lang !== Shop::getLanguageCode()) {
            $_SESSION['oKategorie_arr']     = [];
            $_SESSION['oKategorie_arr_new'] = [];
        }
        $bSpracheDa = false;
        $Sprachen   = gibAlleSprachen();
        foreach ($Sprachen as $Sprache) {
            if ($Sprache->cISO === $lang) {
                $_SESSION['cISOSprache'] = $Sprache->cISO;
                $_SESSION['kSprache']    = (int)$Sprache->kSprache;
                Shop::setLanguage($Sprache->kSprache, $Sprache->cISO);
                unset($_SESSION['Suche']);
                $bSpracheDa = true;
                setzeLinks();
                if (isset($_SESSION['Wunschliste'])) {
                    Session::WishList()->umgebungsWechsel();
                }
                if (isset($_SESSION['Vergleichsliste'])) {
                    Session::CompareList()->umgebungsWechsel();
                }
                $_SESSION['currentLanguage'] = clone $Sprache;
                unset($_SESSION['currentLanguage']->cURL);
            }
        }
        // Suchspecialoverlays
        holeAlleSuchspecialOverlays(Shop::getLanguageID());
        if (!$bSpracheDa) { //lang mitgegeben, aber nicht mehr in db vorhanden -> alter Sprachlink
            $kArtikel              = verifyGPCDataInteger('a');
            $kKategorie            = verifyGPCDataInteger('k');
            $kSeite                = verifyGPCDataInteger('s');
            $kVariKindArtikel      = verifyGPCDataInteger('a2');
            $kHersteller           = verifyGPCDataInteger('h');
            $kSuchanfrage          = verifyGPCDataInteger('l');
            $kMerkmalWert          = verifyGPCDataInteger('m');
            $kTag                  = verifyGPCDataInteger('t');
            $kSuchspecial          = verifyGPCDataInteger('q');
            $kNews                 = verifyGPCDataInteger('n');
            $kNewsMonatsUebersicht = verifyGPCDataInteger('nm');
            $kNewsKategorie        = verifyGPCDataInteger('nk');
            $kUmfrage              = verifyGPCDataInteger('u');
            $cSeo                  = '';
            //redirect per 301
            http_response_code(301);
            if ($kArtikel > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kArtikel',
                    'kKey', $kArtikel,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kKategorie > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kKategorie',
                    'kKey', $kKategorie,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kSeite > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kLink',
                    'kKey', $kSeite,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kVariKindArtikel > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kArtikel',
                    'kKey', $kVariKindArtikel,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kHersteller > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kHersteller',
                    'kKey', $kHersteller,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kSuchanfrage > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kSuchanfrage',
                    'kKey', $kSuchanfrage,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kMerkmalWert > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kMerkmalWert',
                    'kKey', $kMerkmalWert,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kTag > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kTag',
                    'kKey', $kTag,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kSuchspecial > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kSuchspecial',
                    'kKey', $kSuchspecial,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kNews > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kNews',
                    'kKey', $kNews,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kNewsMonatsUebersicht > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kNewsMonatsUebersicht',
                    'kKey', $kNewsMonatsUebersicht,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kNewsKategorie > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kNewsKategorie',
                    'kKey', $kNewsKategorie,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            } elseif ($kUmfrage > 0) {
                $dbRes = Shop::Container()->getDB()->select(
                    'tseo',
                    'cKey', 'kUmfrage',
                    'kKey', $kUmfrage,
                    'kSprache', Shop::getLanguageID()
                );
                $cSeo  = $dbRes->cSeo;
            }
            header('Location: ' . Shop::getURL() . '/' . $cSeo, true, 301);
            exit;
        }
    }

    $waehrung = verifyGPDataString('curr');
    if ($waehrung) {
        $Waehrungen = Shop::Container()->getDB()->query(
            "SELECT cISO, kWaehrung 
                FROM twaehrung",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $cart       = Session::Cart();
        foreach ($Waehrungen as $Waehrung) {
            if ($Waehrung->cISO === $waehrung) {
                setFsession($Waehrung->kWaehrung, 0, 0);
                $currency = new Currency($Waehrung->kWaehrung);

                $_SESSION['Waehrung']      = $currency;
                $_SESSION['cWaehrungName'] = $currency->getName();

                if (isset($_SESSION['Wunschliste'])) {
                    Session::WishList()->umgebungsWechsel();
                }
                if (isset($_SESSION['Vergleichsliste'])) {
                    Session::CompareList()->umgebungsWechsel();
                }
                // Trusted Shops Kaeuferschutz raus falls vorhanden
                unset($_SESSION['TrustedShops']);
                if ($cart !== null) {
                    $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
                    if (count($cart->PositionenArr) > 0) {
                        $cart->setzePositionsPreise();
                    }
                }
            }
        }
    }
    Shop::Lang()->autoload();
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
    $arr = get_object_vars($src);
    if (is_array($arr)) {
        foreach (array_keys($arr) as $key) {
            if (!is_object($src->$key) && !is_array($src->$key)) {
                $dest->$key = $src->$key;
            }
        }
    }
}

/**
 * @param int $kWaehrung
 * @param int $ArtSort
 * @param int $ArtZahl
 * @return bool
 */
function setFsession($kWaehrung, $ArtSort, $ArtZahl)
{
    if (!empty($_SERVER['HTTP_COOKIE'])) {
        return false;
    }
    $fsess     = Shop::Container()->getDB()->select('tfsession', 'cIP', gibIP(), 'cAgent', $_SERVER['HTTP_USER_AGENT']);
    $kWaehrung = (int)$kWaehrung;
    if ($fsess !== null && !empty($fsess->cIP)) {
        if ($kWaehrung) {
            $_upd            = new stdClass();
            $_upd->kWaehrung = $kWaehrung;
            Shop::Container()->getDB()->update('tfsession', ['cIP', 'cAgent'], [gibIP(), $_SERVER['HTTP_USER_AGENT']], $_upd);
        } elseif ($ArtSort) {
            $_upd                  = new stdClass();
            $_upd->nUserSortierung = $ArtSort;
            Shop::Container()->getDB()->update('tfsession', ['cIP', 'cAgent'], [gibIP(), $_SERVER['HTTP_USER_AGENT']], $_upd);
        } elseif ($ArtZahl) {
            $_upd                   = new stdClass();
            $_upd->nUserArtikelzahl = $ArtZahl;
            Shop::Container()->getDB()->update('tfsession', ['cIP', 'cAgent'], [gibIP(), $_SERVER['HTTP_USER_AGENT']], $_upd);
        }
    } else {
        $fs                   = new stdClass();
        $fs->cIP              = gibIP();
        $fs->cAgent           = $_SERVER['HTTP_USER_AGENT'];
        $fs->kWaehrung        = $kWaehrung;
        $fs->nUserSortierung  = $ArtSort;
        $fs->nUserArtikelzahl = $ArtZahl;
        $fs->dErstellt        = 'now()';
        Shop::Container()->getDB()->insert('tfsession', $fs);
    }

    return true;
}

/**
 * @return bool
 */
function getFsession()
{
    if (isset($_SERVER['HTTP_COOKIE']) || !isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    $fsess = Shop::Container()->getDB()->select('tfsession', 'cIP', gibIP(), 'cAgent', $_SERVER['HTTP_USER_AGENT']);
    if ($fsess !== null && isset($fsess->cIP)) {
        if ($fsess->nUserArtikelzahl) {
            $_SESSION['ArtikelProSeite'] = (int)$fsess->nUserArtikelzahl;
        }
        if ($fsess->nUserSortierung) {
            $_SESSION['Usersortierung'] = (int)$fsess->nUserSortierung;
        }
        if ($fsess->kWaehrung) {
            $currency = new Currency($fsess->kWaehrung);

            $_SESSION['Waehrung']      = $currency;
            $_SESSION['cWaehrungName'] = $currency->getName();
        }
    }
    if (time() % 10 === 0) {
        Shop::Container()->getDB()->query(
            "DELETE
                FROM tfsession
                WHERE date_sub(now(), INTERVAL 60 MINUTE) > dErstellt", 4
        );
    }

    return true;
}

/**
 * @return null|stdClass
 */
function setzeLinks()
{
    $linkHelper                    = LinkHelper::getInstance();
    $linkGroups                    = $linkHelper->getLinkGroups();
    $_SESSION['Link_Datenschutz']  = $linkGroups->Link_Datenschutz;
    $_SESSION['Link_AGB']          = $linkGroups->Link_AGB;
    $_SESSION['Link_Versandseite'] = $linkGroups->Link_Versandseite;
    executeHook(HOOK_TOOLSGLOBAL_INC_SETZELINKS);

    return $linkGroups;
}

/**
 * @param bool      $bShop
 * @param int| null $kSprache - optional lang id to check against instead of session value
 * @return bool
 */
function standardspracheAktiv($bShop = false, $kSprache = null)
{
    if ($kSprache === null && !isset($_SESSION['kSprache'])) {
        return true;
    }
    $langToCheckAgainst = $kSprache !== null ? (int)$kSprache : Shop::getLanguageID();
    if ($langToCheckAgainst > 0) {
        foreach (Session::Languages() as $Sprache) {
            if ($Sprache->cStandard === 'Y' && (int)$Sprache->kSprache === $langToCheckAgainst && !$bShop) {
                return true;
            }
            if ($Sprache->cShopStandard === 'Y' && (int)$Sprache->kSprache === $langToCheckAgainst && $bShop) {
                return true;
            }
        }

        return false;
    }

    return true;
}

/**
 * @param bool $bShop
 * @return mixed
 */
function gibStandardsprache($bShop = true)
{
    foreach (Session::Languages() as $Sprache) {
        if ($Sprache->cStandard === 'Y' && !$bShop) {
            return $Sprache;
        }
        if ($Sprache->cShopStandard === 'Y' && $bShop) {
            return $Sprache;
        }
    }

    $cacheID = 'shop_lang_' . (($bShop === true) ? 'b' : '');
    if (($lang = Shop::Cache()->get($cacheID)) !== false && $lang !== null) {
        return $lang;
    }
    $row  = $bShop ? 'cShopStandard' : 'cStandard';
    $lang = Shop::Container()->getDB()->select('tsprache', $row, 'Y');
    $lang->kSprache = (int)$lang->kSprache;
    Shop::Cache()->set($cacheID, $lang, [CACHING_GROUP_LANGUAGE]);

    return $lang;
}

/**
 * @param bool $bISO
 * @return mixed
 */
function gibStandardWaehrung($bISO = false)
{
    return $bISO === true ? Session::Currency()->getCode() : Session::Currency()->getID();
}

/**
 * @param array  $Positionen
 * @param int    $Nettopreise
 * @param int    $htmlWaehrung
 * @param mixed int|object $oWaehrung
 * @return array
 */
function gibAlteSteuerpositionen($Positionen, $Nettopreise = -1, $htmlWaehrung = 1, $oWaehrung = 0)
{
    if ($Nettopreise === -1) {
        $Nettopreise = $_SESSION['NettoPreise'];
    }
    $taxRates = [];
    $taxPos   = [];
    $conf     = Shop::getSettings([CONF_GLOBAL]);
    if ($conf['global']['global_steuerpos_anzeigen'] === 'N') {
        return $taxPos;
    }
    foreach ($Positionen as $position) {
        if ($position->fMwSt > 0 && !in_array($position->fMwSt, $taxRates, true)) {
            $taxRates[] = $position->fMwSt;
        }
    }
    sort($taxRates);
    foreach ($Positionen as $position) {
        if ($position->fMwSt <= 0) {
            continue;
        }
        $i = array_search($position->fMwSt, $taxRates);

        if (!isset($taxPos[$i]->fBetrag) || !$taxPos[$i]->fBetrag) {
            $taxPos[$i]                  = new stdClass();
            $taxPos[$i]->cName           = lang_steuerposition($position->fMwSt, $Nettopreise);
            $taxPos[$i]->fUst            = $position->fMwSt;
            $taxPos[$i]->fBetrag         = ($position->fPreis * $position->nAnzahl * $position->fMwSt) / 100.0;
            $taxPos[$i]->cPreisLocalized = gibPreisStringLocalized($taxPos[$i]->fBetrag, $oWaehrung, $htmlWaehrung);
        } else {
            $taxPos[$i]->fBetrag         += ($position->fPreis * $position->nAnzahl * $position->fMwSt) / 100.0;
            $taxPos[$i]->cPreisLocalized = gibPreisStringLocalized($taxPos[$i]->fBetrag, $oWaehrung, $htmlWaehrung);
        }
    }

    return $taxPos;
}

/**
 * @param string $email
 * @return bool
 */
function valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * @param int $kKundengruppe
 * @return array
 */
function gibMoeglicheVerpackungen($kKundengruppe)
{
    $fSummeWarenkorb = Session::Cart()->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
    $oVerpackung_arr = Shop::Container()->getDB()->query(
        "SELECT * FROM tverpackung
            JOIN tverpackungsprache
                ON tverpackung.kVerpackung = tverpackungsprache.kVerpackung
            WHERE tverpackungsprache.cISOSprache = '" . Shop::getLanguageCode() . "'
            AND (tverpackung.cKundengruppe = '-1'
                OR FIND_IN_SET('" . (int)$kKundengruppe . "', REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
            AND " . $fSummeWarenkorb . " >= tverpackung.fMindestbestellwert
            AND tverpackung.nAktiv = 1
            ORDER BY tverpackung.kVerpackung",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $currencyCode = Session::Currency()->getID();
    foreach ($oVerpackung_arr as $i => $oVerpackung) {
        $oVerpackung_arr[$i]->nKostenfrei = 0;
        if ($fSummeWarenkorb >= $oVerpackung->fKostenfrei
            && $oVerpackung->fBrutto > 0
            && $oVerpackung->fKostenfrei != 0
        ) {
            $oVerpackung_arr[$i]->nKostenfrei = 1;
        }
        $oVerpackung_arr[$i]->fBruttoLocalized = gibPreisStringLocalized(
            $oVerpackung_arr[$i]->fBrutto,
            $currencyCode
        );
    }

    return $oVerpackung_arr;
}

/**
 * @param Versandart|object $versandart
 * @param string            $cISO
 * @param string            $plz
 * @return object|null
 */
function gibVersandZuschlag($versandart, $cISO, $plz)
{
    $versandzuschlaege = Shop::Container()->getDB()->selectAll(
        'tversandzuschlag',
        ['kVersandart', 'cISO'],
        [(int)$versandart->kVersandart, $cISO]
    );

    foreach ($versandzuschlaege as $versandzuschlag) {
        //ist plz enthalten?
        $plz_x = Shop::Container()->getDB()->query(
            "SELECT * FROM tversandzuschlagplz
                WHERE ((cPLZAb <= '" . $plz . "'
                    AND cPLZBis >= '" . $plz . "')
                    OR cPLZ = '" . $plz . "')
                    AND kVersandzuschlag = " . (int)$versandzuschlag->kVersandzuschlag,
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($plz_x->kVersandzuschlagPlz) && $plz_x->kVersandzuschlagPlz > 0) {
            //posname lokalisiert ablegen
            $versandzuschlag->angezeigterName = [];
            foreach (Session::Languages() as $Sprache) {
                $name_spr = Shop::Container()->getDB()->select(
                    'tversandzuschlagsprache',
                    'kVersandzuschlag', (int)$versandzuschlag->kVersandzuschlag,
                    'cISOSprache', $Sprache->cISO
                );

                $versandzuschlag->angezeigterName[$Sprache->cISO] = $name_spr->cName;
            }
            $versandzuschlag->cPreisLocalized = gibPreisStringLocalized($versandzuschlag->fZuschlag);

            return $versandzuschlag;
        }
    }

    return null;
}

/**
 * @todo Hier gilt noch zu beachten, dass fWarenwertNetto vom Zusatzartikel
 *       darf kein Netto sein, sondern der Preis muss in Brutto angegeben werden.
 * @param Versandart|object $versandart
 * @param String            $cISO
 * @param Artikel|stdClass  $oZusatzArtikel
 * @param Artikel|int       $Artikel
 * @return int|string
 */
function berechneVersandpreis($versandart, $cISO, $oZusatzArtikel, $Artikel = 0)
{
    if (!isset($oZusatzArtikel->fAnzahl)) {
        if (!isset($oZusatzArtikel)) {
            $oZusatzArtikel = new stdClass();
        }
        $oZusatzArtikel->fAnzahl         = 0;
        $oZusatzArtikel->fWarenwertNetto = 0;
        $oZusatzArtikel->fGewicht        = 0;
    }
    $versandberechnung = Shop::Container()->getDB()->select(
        'tversandberechnung',
        'kVersandberechnung',
        $versandart->kVersandberechnung
    );
    $preis             = 0;
    switch ($versandberechnung->cModulId) {
        case 'vm_versandkosten_pauschale_jtl':
            $preis = $versandart->fPreis;
            break;

        case 'vm_versandberechnung_gewicht_jtl':
            $warenkorbgewicht  = $Artikel
                ? $Artikel->fGewicht
                : Session::Cart()->getWeight();
            $warenkorbgewicht += $oZusatzArtikel->fGewicht;
            $versand           = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tversandartstaffel
                    WHERE kVersandart = " . (int)$versandart->kVersandart . "
                        AND fBis >= " . $warenkorbgewicht . "
                    ORDER BY fBis ASC",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($versand->kVersandartStaffel)) {
                $preis = $versand->fPreis;
            } else {
                return -1;
            }
            break;

        case 'vm_versandberechnung_warenwert_jtl':
            $warenkorbwert  = $Artikel
                ? $Artikel->Preise->fVKNetto
                : Session::Cart()->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
            $warenkorbwert += $oZusatzArtikel->fWarenwertNetto;
            $versand        = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tversandartstaffel
                    WHERE kVersandart = " . (int)$versandart->kVersandart . "
                        AND fBis >= " . $warenkorbwert . "
                    ORDER BY fBis ASC",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($versand->kVersandartStaffel)) {
                $preis = $versand->fPreis;
            } else {
                return -1;
            }
            break;

        case 'vm_versandberechnung_artikelanzahl_jtl':
            $artikelanzahl = 1;
            if (!$Artikel) {
                $artikelanzahl = isset($_SESSION['Warenkorb'])
                    ? Session::Cart()->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
                    : 0;
            }
            $artikelanzahl += $oZusatzArtikel->fAnzahl;
            $versand        = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tversandartstaffel
                    WHERE kVersandart = " . (int)$versandart->kVersandart . "
                        AND fBis >= " . $artikelanzahl . "
                    ORDER BY fBis ASC",
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($versand->kVersandartStaffel)) {
                $preis = $versand->fPreis;
            } else {
                return -1;
            }
            break;

        default:
            //bearbeite fremdmodule
            break;
    }
    //artikelabhaengiger Versand?
    if ($versandart->cNurAbhaengigeVersandart === 'Y'
        && (!empty($Artikel->FunktionsAttribute['versandkosten'])
            || !empty($Artikel->FunktionsAttribute['versandkosten gestaffelt']))
    ) {
        $fArticleSpecific = VersandartHelper::gibArtikelabhaengigeVersandkosten($cISO, $Artikel, 1);
        $preis           += $fArticleSpecific->fKosten ?? 0;
    }
    //Deckelung?
    if ($preis >= $versandart->fDeckelung && $versandart->fDeckelung > 0) {
        $preis = $versandart->fDeckelung;
    }
    //Zuschlag
    if (isset($versandart->Zuschlag->fZuschlag) && $versandart->Zuschlag->fZuschlag != 0) {
        $preis += $versandart->Zuschlag->fZuschlag;
    }
    //versandkostenfrei?
    $fArtikelPreis     = 0;
    $fGesamtsummeWaren = 0;
    if ($versandart->eSteuer === 'netto') {
        if ($Artikel) {
            $fArtikelPreis = $Artikel->Preise->fVKNetto;
        }
        if (isset($_SESSION['Warenkorb'])) {
            $fGesamtsummeWaren = berechneNetto(
                Session::Cart()->gibGesamtsummeWarenExt(
                    [C_WARENKORBPOS_TYP_ARTIKEL],
                    1
                ),
                gibUst(Session::Cart()->gibVersandkostenSteuerklasse())
            );
        }
    } elseif ($versandart->eSteuer === 'brutto') {
        if ($Artikel) {
            $fArtikelPreis = berechneBrutto($Artikel->Preise->fVKNetto, gibUst($Artikel->kSteuerklasse));
        }
        if (isset($_SESSION['Warenkorb'])) {
            $fGesamtsummeWaren = Session::Cart()->gibGesamtsummeWarenExt(
                [C_WARENKORBPOS_TYP_ARTIKEL],
                1
            );
        }
    }

    if ($versandart->fVersandkostenfreiAbX > 0
        && (($Artikel && $fArtikelPreis >= $versandart->fVersandkostenfreiAbX)
            || ($fGesamtsummeWaren >= $versandart->fVersandkostenfreiAbX))
    ) {
        $preis = 0;
    }
    executeHook(HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS, [
        'fPreis'         => &$preis,
        'versandart'     => $versandart,
        'cISO'           => $cISO,
        'oZusatzArtikel' => $oZusatzArtikel,
        'Artikel'        => $Artikel,
    ]);

    return $preis;
}

/**
 * calculate shipping costs for exports
 *
 * @param string  $cISO
 * @param Artikel $Artikel
 * @param int     $barzahlungZulassen
 * @param int     $kKundengruppe
 * @return int
 */
function gibGuenstigsteVersandkosten($cISO, $Artikel, $barzahlungZulassen, $kKundengruppe)
{
    $versandpreis = 99999;
    $query        = "SELECT *
            FROM tversandart
            WHERE cIgnoreShippingProposal != 'Y'
                AND cLaender LIKE '%" . $cISO . "%'
                AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE '^([0-9 -]* )?" . $Artikel->kVersandklasse . " ')
                AND (cKundengruppen = '-1'
                    OR FIND_IN_SET('{$kKundengruppe}', REPLACE(cKundengruppen, ';', ',')) > 0)";
    // artikelabhaengige Versandarten nur laden und prüfen wenn der Artikel das entsprechende Funktionasattribut hat
    if (empty($Artikel->FunktionsAttribute['versandkosten'])
        && empty($Artikel->FunktionsAttribute['versandkosten gestaffelt'])
    ) {
        $query .= " AND cNurAbhaengigeVersandart = 'N'";
    }
    $methods = Shop::Container()->getDB()->query($query, \DB\ReturnType::ARRAY_OF_OBJECTS);
    foreach ($methods as $method) {
        if (!$barzahlungZulassen) {
            $za_bar = Shop::Container()->getDB()->select(
                'tversandartzahlungsart',
                'kZahlungsart', 6,
                'kVersandart', (int)$method->kVersandart
            );
            if ($za_bar !== null && isset($za_bar->kVersandartZahlungsart) && $za_bar->kVersandartZahlungsart > 0) {
                continue;
            }
        }
        $vp = berechneVersandpreis($method, $cISO, null, $Artikel);
        if ($vp !== -1 && $vp < $versandpreis) {
            $versandpreis = $vp;
        }
        if ($vp === 0) {
            break;
        }
    }

    return $versandpreis === 99999 ? -1 : $versandpreis;
}

/**
 * @param int  $kKundengruppe
 * @param bool $bIgnoreSetting
 * @param bool $bForceAll
 * @return array
 */
function gibBelieferbareLaender($kKundengruppe = 0, $bIgnoreSetting = false, $bForceAll = false)
{
    if (empty($kKundengruppe)) {
        $kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $sprache = Shop::Container()->getDB()->select('tsprache', 'kSprache', Shop::getLanguageID());
    $sel_var = 'cDeutsch';
    $conf    = Shop::getSettings([CONF_KUNDEN]);
    if (strtolower($sprache->cNameEnglisch) !== 'german') {
        $sel_var = 'cEnglisch';
    }
    if (!$bForceAll && ($conf['kunden']['kundenregistrierung_nur_lieferlaender'] === 'Y' || $bIgnoreSetting)) {
        $laender_arr = [];
        $ll_obj_arr  = Shop::Container()->getDB()->query(
            "SELECT cLaender
                FROM tversandart
                WHERE (cKundengruppen = '-1'
                  OR FIND_IN_SET('{$kKundengruppe}', REPLACE(cKundengruppen, ';', ',')) > 0)",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($ll_obj_arr as $cLaender) {
            $pcs = explode(' ', $cLaender->cLaender);
            foreach ($pcs as $land) {
                if ($land && !in_array($land, $laender_arr, true)) {
                    $laender_arr[] = $land;
                }
            }
        }
        $laender_arr = array_map(function ($e) {
            return '"' . $e . '"';
        }, $laender_arr);
        $where       = ' cISO IN (' . implode(',', $laender_arr) . ')';
        $laender     = count($laender_arr) > 0
            ? Shop::Container()->getDB()->query(
                "SELECT cISO, $sel_var AS cName 
                    FROM tland 
                    WHERE $where 
                    ORDER BY $sel_var",
                \DB\ReturnType::ARRAY_OF_OBJECTS)
            : [];
    } else {
        $laender = Shop::Container()->getDB()->query(
            "SELECT cISO, $sel_var AS cName 
                FROM tland 
                ORDER BY $sel_var",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
    }
    if (is_array($laender)) {
        usort(
            $laender, function ($a, $b) {
                $a = mb_convert_case($a->cName, MB_CASE_LOWER, 'utf-8');
                $b = mb_convert_case($b->cName, MB_CASE_LOWER, 'utf-8');
                $a = str_replace(
                    ['ä', 'ü', 'ö', 'ss'],
                    ['a', 'u', 'o', 'ß'],
                    $a
                );
                $b = str_replace(
                    ['ä', 'ü', 'ö', 'ss'],
                    ['a', 'u', 'o', 'ß'],
                    $b
                );
                if ($a === $b) {
                    return 0;
                }

                return $a < $b ? -1 : 1;
            }
        );
    }
    executeHook(HOOK_TOOLSGLOBAL_INC_GIBBELIEFERBARELAENDER, [
        'oLaender_arr' => &$laender
    ]);

    return $laender;
}

/**
 * @param int $sec
 * @return string
 */
function gibCaptchaCode($sec)
{
    $cryptoService = Shop::Container()->getCryptoService();
    $code          = '';
    switch ((int)$sec) {
        case 1:
            $chars = '1234567890';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars{$cryptoService->randomInt(0, strlen($chars) - 1)};
            }
            break;
        case 2:
        case 3:
        default:
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars{$cryptoService->randomInt(0, strlen($chars) - 1)};
            }
            break;
    }

    return strtoupper($code);
}

/**
 * @param string $klartext
 * @return string
 */
function encodeCode($klartext)
{
    $cryptoService = Shop::Container()->getCryptoService();
    if (strlen($klartext) !== 4) {
        return '0';
    }
    $key  = BLOWFISH_KEY;
    $mod1 = (ord($key[0]) + ord($key[1]) + ord($key[2])) % 9 + 1;
    $mod2 = strlen($_SERVER['DOCUMENT_ROOT']) % 9 + 1;

    $s1 = ord($klartext{0}) - $mod2 + $mod1 + 123;
    $s2 = ord($klartext{1}) - $mod1 + $mod2 + 234;
    $s3 = ord($klartext{2}) + $mod1 + 345;
    $s4 = ord($klartext{3}) + $mod2 + 456;

    $r1 = $cryptoService->randomInt(100, 999);
    $r2 = $cryptoService->randomInt(0, 9);
    $r3 = $cryptoService->randomInt(10, 99);
    $r4 = $cryptoService->randomInt(1000, 9999);

    return $r1 . $s3 . $r2 . $s4 . $r3 . $s1 . $s2 . $r4;
}

/**
 * @param int|string $sec
 * @return stdClass|false
 */
function generiereCaptchaCode($sec)
{
    if ($sec === 'N' || !$sec || ((int)$sec === 7 || $sec === 'Y')) {
        return false;
    }

    $cryptoService = Shop::Container()->getCryptoService();

    $code = new stdClass();
    if ((int)$sec === 4) {
        $rnd       = time() % 4 + 1;
        $code->art = $rnd;
        switch ($rnd) {
            case 1:
                $x1          = $cryptoService->randomInt(1, 10);
                $x2          = $cryptoService->randomInt(1, 10);
                $code->code  = $x1 + $x2;
                $code->frage = Shop::Lang()->get('captchaMathQuestion') . ' ' . $x1 . ' ' .
                    Shop::Lang()->get('captchaAddition') . ' ' . $x2 . '?';
                break;

            case 2:
                $x1          = $cryptoService->randomInt(3, 10);
                $x2          = $cryptoService->randomInt(1, $x1 - 1);
                $code->code  = $x1 - $x2;
                $code->frage = Shop::Lang()->get('captchaMathQuestion') . ' ' . $x1 . ' ' .
                    Shop::Lang()->get('captchaSubtraction') . ' ' . $x2 . '?';
                break;

            case 3:
                $x1          = $cryptoService->randomInt(2, 5);
                $x2          = $cryptoService->randomInt(2, 5);
                $code->code  = $x1 * $x2;
                $code->frage = Shop::Lang()->get('captchaMathQuestion') . ' ' . $x1 . ' ' .
                    Shop::Lang()->get('captchaMultiplication') . ' ' . $x2 . '?';
                break;

            case 4:
                $x1          = $cryptoService->randomInt(2, 5);
                $x2          = $cryptoService->randomInt(2, 5);
                $code->code  = $x1;
                $x1         *= $x2;
                $code->frage = Shop::Lang()->get('captchaMathQuestion') . ' ' . $x1 . ' ' .
                    Shop::Lang()->get('captchaDivision') . ' ' . $x2 . '?';
                break;
        }
    } elseif ((int)$sec === 5) { //unsichtbarer Token
        $code->code              = '';
        $_SESSION['xcrsf_token'] = null;
    } else {
        $code->code    = gibCaptchaCode($sec);
        $code->codeURL = Shop::getURL() . '/' . PFAD_INCLUDES . 'captcha/captcha.php?c=' .
            encodeCode($code->code) . '&amp;s=' . $sec . '&amp;l=' . $cryptoService->randomInt(0, 9);
    }
    $code->codemd5 = md5(PFAD_ROOT . $code->code);

    return $code;
}

/**
 * @param string $data
 * @return int
 */
function checkeTel($data)
{
    if (!$data) {
        return 1;
    }
    if (!preg_match('/^[0-9\-\(\)\/\+\s]{1,}$/', $data)) {
        return 2;
    }

    return 0;
}

/**
 * @param string $data
 * @return int
 */
function checkeDatum($data)
{
    if (!$data) {
        return 1;
    }
    if (!preg_match('/^\d{1,2}\.\d{1,2}\.(\d{4})$/', $data)) {
        return 2;
    }
    list($tag, $monat, $jahr) = explode('.', $data);
    if (!checkdate($monat, $tag, $jahr)) {
        return 3;
    }

    return 0;
}

/**
 * @param Artikel $Artikel
 * @param string $einstellung
 * @return int
 */
function gibVerfuegbarkeitsformularAnzeigen($Artikel, $einstellung)
{
    if (isset($einstellung)
        && $einstellung !== 'N'
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
 * Gibt einen String für einen Header mit dem angegebenen Status-Code aus
 *
 * @param int $nStatusCode
 * @return string
 */
function makeHTTPHeader($nStatusCode)
{
    $proto = !empty($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
    $codes = [
        100 => $proto . ' 100 Continue',
        101 => $proto . ' 101 Switching Protocols',
        200 => $proto . ' 200 OK',
        201 => $proto . ' 201 Created',
        202 => $proto . ' 202 Accepted',
        203 => $proto . ' 203 Non-Authoritative Information',
        204 => $proto . ' 204 No Content',
        205 => $proto . ' 205 Reset Content',
        206 => $proto . ' 206 Partial Content',
        300 => $proto . ' 300 Multiple Choices',
        301 => $proto . ' 301 Moved Permanently',
        302 => $proto . ' 302 Found',
        303 => $proto . ' 303 See Other',
        304 => $proto . ' 304 Not Modified',
        305 => $proto . ' 305 Use Proxy',
        307 => $proto . ' 307 Temporary Redirect',
        400 => $proto . ' 400 Bad Request',
        401 => $proto . ' 401 Unauthorized',
        402 => $proto . ' 402 Payment Required',
        403 => $proto . ' 403 Forbidden',
        404 => $proto . ' 404 Not Found',
        405 => $proto . ' 405 Method Not Allowed',
        406 => $proto . ' 406 Not Acceptable',
        407 => $proto . ' 407 Proxy Authentication Required',
        408 => $proto . ' 408 Request Time-out',
        409 => $proto . ' 409 Conflict',
        410 => $proto . ' 410 Gone',
        411 => $proto . ' 411 Length Required',
        412 => $proto . ' 412 Precondition Failed',
        413 => $proto . ' 413 Request Entity Too Large',
        414 => $proto . ' 414 Request-URI Too Large',
        415 => $proto . ' 415 Unsupported Media Type',
        416 => $proto . ' 416 Requested range not satisfiable',
        417 => $proto . ' 417 Expectation Failed',
        500 => $proto . ' 500 Internal Server Error',
        501 => $proto . ' 501 Not Implemented',
        502 => $proto . ' 502 Bad Gateway',
        503 => $proto . ' 503 Service Unavailable',
        504 => $proto . ' 504 Gateway Time-out'
    ];

    return $codes[$nStatusCode] ?? '';
}

/**
 * @param array $nFilter_arr
 * @return array
 */
function setzeMerkmalFilter($nFilter_arr = [])
{
    $filter = [];
    if (is_array($nFilter_arr) && count($nFilter_arr) > 1) {
        foreach ($nFilter_arr as $nFilter) {
            if ((int)$nFilter > 0) {
                $filter[] = (int)$nFilter;
            }
        }
    } else {
        if (isset($_GET['mf'])) {
            if (is_string($_GET['mf'])) {
                $filter[] = $_GET['mf'];
            } else {
                foreach ($_GET['mf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['mf'])) {
            if (is_string($_POST['mf'])) {
                $filter[] = $_POST['mf'];
            } else {
                foreach ($_POST['mf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (count($_GET) > 0) {
            foreach ($_GET as $key => $value) {
                if (preg_match('/mf\d+/i', $key)) {
                    $filter[] = (int)$value;
                }
            }
        } elseif (count($_POST) > 0) {
            foreach ($_POST as $key => $value) {
                if (preg_match('/mf\d+/i', $key)) {
                    $filter[] = (int)$value;
                }
            }
        }
    }

    return $filter;
}

/**
 * @param array $nFilter_arr
 * @return array
 */
function setzeSuchFilter($nFilter_arr = [])
{
    $filter = [];
    if (is_array($nFilter_arr) && count($nFilter_arr) > 1) {
        foreach ($nFilter_arr as $nFilter) {
            if ((int)$nFilter > 0) {
                $filter[] = (int)$nFilter;
            }
        }
    } else {
        if (isset($_GET['sf'])) {
            if (is_string($_GET['sf'])) {
                $filter[] = $_GET['sf'];
            } else {
                foreach ($_GET['sf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['sf'])) {
            if (is_string($_POST['sf'])) {
                $filter[] = $_POST['sf'];
            } else {
                foreach ($_POST['sf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } else {
            $i = 1;
            while ($i < 20) {
                if (verifyGPCDataInteger('sf' . $i) > 0) {
                    $filter[] = verifyGPCDataInteger('sf' . $i);
                }
                ++$i;
            }
        }
    }

    return $filter;
}

/**
 * @param array $nFilter_arr
 * @return array
 */
function setzeTagFilter($nFilter_arr = [])
{
    $filter = [];
    if (is_array($nFilter_arr) && count($nFilter_arr) > 1) {
        foreach ($nFilter_arr as $nFilter) {
            if ((int)$nFilter > 0) {
                $filter[] = (int)$nFilter;
            }
        }
    } else {
        if (isset($_GET['tf'])) {
            if (is_string($_GET['tf'])) {
                $filter[] = $_GET['tf'];
            } else {
                foreach ($_GET['tf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } elseif (isset($_POST['tf'])) {
            if (is_string($_POST['tf'])) {
                $filter[] = $_POST['tf'];
            } else {
                foreach ($_POST['tf'] as $mf => $value) {
                    $filter[] = $value;
                }
            }
        } else {
            $i = 1;
            while ($i < 20) {
                if (verifyGPCDataInteger('tf' . $i) > 0) {
                    $filter[] = verifyGPCDataInteger('tf' . $i);
                }
                ++$i;
            }
        }
    }

    return $filter;
}

/**
 * Überprüft Parameter und gibt falls erfolgreich kWunschliste zurück, ansonten 0
 *
 * @return int
 */
function checkeWunschlisteParameter()
{
    $cURLID = StringHandler::filterXSS(Shop::Container()->getDB()->escape(verifyGPDataString('wlid')));

    if (strlen($cURLID) > 0) {
        $campaing = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
        $id       = ($campaing->kKampagne > 0)
            ? ($cURLID . '&' . $campaing->cParameter . '=' . $campaing->cWert)
            : $cURLID;
        $keys     = ['nOeffentlich', 'cURLID'];
        $values   = [1, $id];
        $wishList = Shop::Container()->getDB()->select('twunschliste', $keys, $values);

        if ($wishList !== null && $wishList->kWunschliste > 0) {
            return (int)$wishList->kWunschliste;
        }
    }

    return 0;
}

/**
 * @param array $articles
 * @param int   $weightAcc
 * @param int   $shippingWeightAcc
 */
function baueGewicht(array $articles, $weightAcc = 2, $shippingWeightAcc = 2)
{
    $weightAcc         = (int)$weightAcc;
    $shippingWeightAcc = (int)$shippingWeightAcc;
    foreach ($articles as $article) {
        if ($article->fGewicht > 0) {
            $article->Versandgewicht    = str_replace('.', ',', round($article->fGewicht, $shippingWeightAcc));
            $article->Versandgewicht_en = round($article->fGewicht, $shippingWeightAcc);
        }
        if ($article->fArtikelgewicht > 0) {
            $article->Artikelgewicht    = str_replace('.', ',', round($article->fArtikelgewicht, $weightAcc));
            $article->Artikelgewicht_en = round($article->fArtikelgewicht, $weightAcc);
        }
    }
}

/**
 * @param int    $kKundengruppe
 * @param string $cLand
 * @return int|mixed
 */
function gibVersandkostenfreiAb($kKundengruppe, $cLand = '')
{
    // Ticket #1018
    $versandklassen            = VersandartHelper::getShippingClasses(Session::Cart());
    $isStandardProductShipping = VersandartHelper::normalerArtikelversand($cLand);
    $cacheID                   = 'vkfrei_' . $kKundengruppe . '_' .
        $cLand . '_' . $versandklassen . '_' . Shop::getLanguageCode();
    if (($oVersandart = Shop::Cache()->get($cacheID)) === false) {
        if (strlen($cLand) > 0) {
            $cKundeSQLWhere = " AND cLaender LIKE '%" . StringHandler::filterXSS($cLand) . "%'";
        } else {
            $landIso        = Shop::Container()->getDB()->query(
                "SELECT cISO
                    FROM tfirma
                    JOIN tland
                        ON tfirma.cLand = tland.cDeutsch
                    LIMIT 0,1",
                \DB\ReturnType::SINGLE_OBJECT
            );
            $cKundeSQLWhere = '';
            if (isset($landIso->cISO)) {
                $cKundeSQLWhere = " AND cLaender LIKE '%{$landIso->cISO}%'";
            }
        }
        $cProductSpecificSQLWhere = !empty($isStandardProductShipping) ? " AND cNurAbhaengigeVersandart = 'N' " : "";
        $oVersandart = Shop::Container()->getDB()->queryPrepared(
            "SELECT tversandart.*, tversandartsprache.cName AS cNameLocalized
                FROM tversandart
                LEFT JOIN tversandartsprache
                    ON tversandart.kVersandart = tversandartsprache.kVersandart
                    AND tversandartsprache.cISOSprache = :cLangID
                WHERE fVersandkostenfreiAbX > 0
                    AND (cVersandklassen = '-1'
                        OR cVersandklassen RLIKE :cShippingClass)
                    AND (cKundengruppen = '-1'
                        OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)
                    " . $cKundeSQLWhere . $cProductSpecificSQLWhere . "
                ORDER BY fVersandkostenfreiAbX
                LIMIT 1",
            [
                'cLangID'        => Shop::getLanguageCode(),
                'cShippingClass' => $versandklassen,
                'cGroupID'       => '^([0-9 -]* )?' . $kKundengruppe . ' '
            ],
            \DB\ReturnType::SINGLE_OBJECT
        );
        Shop::Cache()->set($cacheID, $oVersandart, [CACHING_GROUP_OPTION]);
    }

    return !empty($oVersandart) && $oVersandart->fVersandkostenfreiAbX > 0
        ? $oVersandart
        : 0;
}

/**
 * @param Versandart|object $oVersandart
 * @param float             $fWarenkorbSumme
 * @return string
 */
function baueVersandkostenfreiString($oVersandart, $fWarenkorbSumme)
{
    if (is_object($oVersandart)
        && (float)$oVersandart->fVersandkostenfreiAbX > 0
        && isset($_SESSION['Warenkorb'], $_SESSION['Steuerland'])
    ) {
        $fSummeDiff = (float)$oVersandart->fVersandkostenfreiAbX - (float)$fWarenkorbSumme;
        //check if vkfreiabx is calculated net or gross
        if ($oVersandart->eSteuer === 'netto') {
            //calculate net with default tax class
            $defaultTaxClass = Shop::Container()->getDB()->select('tsteuerklasse', 'cStandard', 'Y');
            if ($defaultTaxClass !== null && isset($defaultTaxClass->kSteuerklasse)) {
                $taxClasss  = (int)$defaultTaxClass->kSteuerklasse;
                $defaultTax = Shop::Container()->getDB()->select('tsteuersatz', 'kSteuerklasse', $taxClasss);
                if ($defaultTax !== null) {
                    $defaultTaxValue = $defaultTax->fSteuersatz;
                    $fSummeDiff      = (float)$oVersandart->fVersandkostenfreiAbX -
                        berechneNetto((float)$fWarenkorbSumme, $defaultTaxValue);
                }
            }
        }
        // localization - see /jtl-shop/issues#347
        if (isset($oVersandart->cNameLocalized)) {
            $cName = $oVersandart->cNameLocalized;
        } else {
            $VersandartSprache = Shop::Container()->getDB()->select(
                'tversandartsprache',
                'kVersandart', $oVersandart->kVersandart,
                'cISOSprache', Shop::getLanguageCode()
            );
            $cName             = !empty($VersandartSprache->cName)
                ? $VersandartSprache->cName
                : $oVersandart->cName;
        }
        if ($fSummeDiff <= 0) {
            return sprintf(
                Shop::Lang()->get('noShippingCostsReached', 'basket'),
                $cName,
                baueVersandkostenfreiLaenderString($oVersandart), (string)$oVersandart->cLaender
            );
        }

        return sprintf(
            Shop::Lang()->get('noShippingCostsAt', 'basket'),
            gibPreisStringLocalized($fSummeDiff),
            $cName,
            baueVersandkostenfreiLaenderString($oVersandart)
        );
    }

    return '';
}

/**
 * @param Versandart $oVersandart
 * @return string
 */
function baueVersandkostenfreiLaenderString($oVersandart)
{
    if (is_object($oVersandart) && (float)$oVersandart->fVersandkostenfreiAbX > 0) {
        $cacheID = 'bvkfls_' .
            $oVersandart->fVersandkostenfreiAbX .
            strlen($oVersandart->cLaender) . '_' .
            Shop::getLanguageID();
        if (($vkfls = Shop::Cache()->get($cacheID)) === false) {
            // remove empty strings
            $cLaender_arr = array_filter(explode(' ', $oVersandart->cLaender));
            $resultString = '';
            // only select the needed row
            $select = $_SESSION['cISOSprache'] === 'ger'
                ? 'cDeutsch'
                : 'cEnglisch';
            // generate IN sql statement with stringified country isos
            $sql = " cISO IN (" . implode(', ', array_map(function ($iso) {
                return "'" . $iso . "'";
            }, $cLaender_arr)) . ')';
            $countries = Shop::Container()->getDB()->query(
                "SELECT " . $select . " AS name 
                    FROM tland 
                    WHERE " . $sql,
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            // re-concatinate isos with "," for the final output
            $resultString = implode(', ', array_map(function ($e) {
                return $e->name;
            }, $countries));

            $vkfls = sprintf(Shop::Lang()->get('noShippingCostsAtExtended', 'basket'), $resultString);
            Shop::Cache()->set($cacheID, $vkfls, [CACHING_GROUP_OPTION]);
        }

        return $vkfls;
    }

    return '';
}

/**
 * @param float        $preis
 * @param int|Currency $waehrung
 * @param int          $html
 * @return string
 */
function gibPreisLocalizedOhneFaktor($preis, $waehrung = 0, $html = 1)
{
    $currency     = !$waehrung ? Session::Currency() : $waehrung;
    if (get_class($currency) === 'stdClass') {
        $currency = new Currency($currency->kWaehrung);
    }
    $localized    = number_format($preis, 2, $currency->getDecimalSeparator(), $currency->getThousandsSeparator());
    $waherungname = $html ? $currency->getHtmlEntity() : $currency->getName();

    return $currency->getForcePlacementBeforeNumber()
        ? $waherungname . ' ' . $localized
        : $localized . ' ' . $waherungname;
}

/**
 * Bekommt einen String von Keys getrennt durch einen seperator (z.b. ;1;5;6;)
 * und gibt ein Array mit den Keys zurück
 *
 * @param string $cKeys
 * @param string $cSeperator
 * @return array
 */
function gibKeyArrayFuerKeyString($cKeys, $cSeperator)
{
    $cTMP_arr = explode($cSeperator, $cKeys);
    $kKey_arr = [];
    if (is_array($cTMP_arr) && count($cTMP_arr) > 0) {
        foreach ($cTMP_arr as $cTMP) {
            if (strlen($cTMP) > 0) {
                $kKey_arr[] = (int)$cTMP;
            }
        }
    }

    return $kKey_arr;
}

/**
 * Erhält ein Array von Keys und fügt Sie zu einem String zusammen
 * wobei jeder Key durch den Seperator getrennt wird (z.b. ;1;5;6;).
 *
 * @param array  $cKey_arr
 * @param string $cSeperator
 * @return string
 */
function gibKeyStringFuerKeyArray($cKey_arr, $cSeperator)
{
    $cKeys = '';
    if (is_array($cKey_arr) && count($cKey_arr) > 0 && strlen($cSeperator) > 0) {
        $cKeys .= ';';
        foreach ($cKey_arr as $i => $cKey) {
            if ($i > 0) {
                $cKeys .= ';' . $cKey;
            } else {
                $cKeys .= $cKey;
            }
        }
        $cKeys .= ';';
    }

    return $cKeys;
}

/**
 * Diese Funktion erhält einen Text als String und parsed ihn. Variablen die geparsed werden lauten wie folgt:
 * $#a:ID:NAME#$ => ID = kArtikel NAME => Wunschname ... wird in eine URL (evt. SEO) zum Artikel umgewandelt.
 * $#k:ID:NAME#$ => ID = kKategorie NAME => Wunschname ... wird in eine URL (evt. SEO) zur Kategorie umgewandelt.
 * $#h:ID:NAME#$ => ID = kHersteller NAME => Wunschname ... wird in eine URL (evt. SEO) zum Hersteller umgewandelt.
 * $#m:ID:NAME#$ => ID = kMerkmalWert NAME => Wunschname ... wird in eine URL (evt. SEO) zum MerkmalWert umgewandelt.
 * $#n:ID:NAME#$ => ID = kNews NAME => Wunschname ... wird in eine URL (evt. SEO) zur News umgewandelt.
 * $#t:ID:NAME#$ => ID = kTag NAME => Wunschname ... wird in eine URL (evt. SEO) zum Tag umgewandelt.
 * $#l:ID:NAME#$ => ID = kSuchanfrage NAME => Wunschname ... wird in eine URL (evt. SEO) zur Livesuche umgewandelt.
 *
 * @param string $cText
 * @return mixed
 */
function parseNewsText($cText)
{
    preg_match_all(
        '/\${1}\#{1}[akhmntl]{1}:[0-9]+\:{0,1}[a-zA-Z0-9äÄöÖüÜß\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\]\ ]{0,}\#{1}\${1}/',
        $cText,
        $cTreffer_arr
    );
    if (!is_array($cTreffer_arr[0]) || count($cTreffer_arr[0]) === 0) {
        return $cText;
    }
    if (!isset($_SESSION['kSprache'])) {
        $_lang    = gibStandardsprache();
        $kSprache = (int)$_lang->kSprache;
    } else {
        $kSprache = Shop::getLanguageID();
    }
    // Parameter
    $cParameter_arr = [
        'a' => URLART_ARTIKEL,
        'k' => URLART_KATEGORIE,
        'h' => URLART_HERSTELLER,
        'm' => URLART_MERKMAL,
        'n' => URLART_NEWS,
        't' => URLART_TAG,
        'l' => URLART_LIVESUCHE
    ];
    foreach ($cTreffer_arr[0] as $cTreffer) {
        $cParameter = substr($cTreffer, strpos($cTreffer, '#') + 1, 1);
        $nBis       = strpos($cTreffer, ':', 4);
        // Es wurde kein Name angegeben
        if ($nBis === false) {
            $nBis  = strpos($cTreffer, ':', 3);
            $nVon  = strpos($cTreffer, '#', $nBis);
            $cKey  = substr($cTreffer, $nBis + 1, ($nVon - 1) - $nBis);
            $cName = '';
        } else {
            $cKey  = substr($cTreffer, 4, $nBis - 4);
            $cName = substr($cTreffer, $nBis + 1, strpos($cTreffer, '#', $nBis) - ($nBis + 1));
        }

        $oObjekt    = new stdClass();
        $bVorhanden = false;
        //switch($cURLArt_arr[$i])
        switch ($cParameter_arr[$cParameter]) {
            case URLART_ARTIKEL:
                $oObjekt->kArtikel = (int)$cKey;
                $oObjekt->cKey     = 'kArtikel';
                $cTabellenname     = 'tartikel';
                $cSpracheSQL       = '';
                if (Shop::getLanguageID() > 0 && !standardspracheAktiv()) {
                    $cTabellenname = 'tartikelsprache';
                    $cSpracheSQL   = " AND tartikelsprache.kSprache = " . Shop::getLanguageID();
                }
                $oArtikel = Shop::Container()->getDB()->query(
                    "SELECT {$cTabellenname}.kArtikel, {$cTabellenname}.cName, tseo.cSeo
                        FROM {$cTabellenname}
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kArtikel'
                            AND tseo.kKey = {$cTabellenname}.kArtikel
                            AND tseo.kSprache = {$kSprache}
                        WHERE {$cTabellenname}.kArtikel = " . (int)$cKey . $cSpracheSQL,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oArtikel->kArtikel) && $oArtikel->kArtikel > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oArtikel->cSeo;
                    $oObjekt->cName = !empty($oArtikel->cName) ? $oArtikel->cName : 'Link';
                }
                break;

            case URLART_KATEGORIE:
                $oObjekt->kKategorie = (int)$cKey;
                $oObjekt->cKey       = 'kKategorie';
                $cTabellenname       = 'tkategorie';
                $cSpracheSQL         = '';
                if ($kSprache > 0 && !standardspracheAktiv()) {
                    $cTabellenname = "tkategoriesprache";
                    $cSpracheSQL   = " AND tkategoriesprache.kSprache = " . $kSprache;
                }
                $oKategorie = Shop::Container()->getDB()->query(
                    "SELECT {$cTabellenname}.kKategorie, {$cTabellenname}.cName, tseo.cSeo
                        FROM {$cTabellenname}
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kKategorie'
                            AND tseo.kKey = {$cTabellenname}.kKategorie
                            AND tseo.kSprache = {$kSprache}
                        WHERE {$cTabellenname}.kKategorie = " . (int)$cKey . $cSpracheSQL,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oKategorie->kKategorie) && $oKategorie->kKategorie > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oKategorie->cSeo;
                    $oObjekt->cName = !empty($oKategorie->cName) ? $oKategorie->cName : 'Link';
                }
                break;

            case URLART_HERSTELLER:
                $oObjekt->kHersteller = (int)$cKey;
                $oObjekt->cKey        = 'kHersteller';
                $cTabellenname        = 'thersteller';
                $oHersteller          = Shop::Container()->getDB()->query(
                    "SELECT thersteller.kHersteller, thersteller.cName, tseo.cSeo
                        FROM thersteller
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kHersteller'
                            AND tseo.kKey = {$cTabellenname}.kHersteller
                            AND tseo.kSprache = {$kSprache}
                        WHERE {$cTabellenname}.kHersteller = " . (int)$cKey,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oHersteller->kHersteller) && $oHersteller->kHersteller > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oHersteller->cSeo;
                    $oObjekt->cName = !empty($oHersteller->cName) ? $oHersteller->cName : 'Link';
                }
                break;

            case URLART_MERKMAL:
                $oObjekt->kMerkmalWert = (int)$cKey;
                $oObjekt->cKey         = 'kMerkmalWert';
                $oMerkmalWert          = Shop::Container()->getDB()->query(
                    "SELECT tmerkmalwertsprache.kMerkmalWert, tmerkmalwertsprache.cWert, tseo.cSeo
                        FROM tmerkmalwertsprache
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kMerkmalWert'
                            AND tseo.kKey = tmerkmalwertsprache.kMerkmalWert
                            AND tseo.kSprache = {$kSprache}
                        WHERE tmerkmalwertsprache.kMerkmalWert = " . (int)$cKey . "
                            AND tmerkmalwertsprache.kSprache = " . $kSprache,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oMerkmalWert->kMerkmalWert) && $oMerkmalWert->kMerkmalWert > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oMerkmalWert->cSeo;
                    $oObjekt->cName = !empty($oMerkmalWert->cWert) ? $oMerkmalWert->cWert : 'Link';
                }
                break;

            case URLART_NEWS:
                $oObjekt->kNews = (int)$cKey;
                $oObjekt->cKey  = 'kNews';
                $oNews          = Shop::Container()->getDB()->query(
                    "SELECT tnews.kNews, tnews.cBetreff, tseo.cSeo
                        FROM tnews
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kNews'
                            AND tseo.kKey = tnews.kNews
                            AND tseo.kSprache = {$kSprache}
                        WHERE tnews.kNews = " . (int)$cKey,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oNews->kNews) && $oNews->kNews > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oNews->cSeo;
                    $oObjekt->cName = !empty($oNews->cBetreff) ? $oNews->cBetreff : 'Link';
                }
                break;

            case URLART_UMFRAGE:
                $oObjekt->kNews = (int)$cKey;
                $oObjekt->cKey  = 'kUmfrage';
                $oUmfrage       = Shop::Container()->getDB()->query(
                    "SELECT tumfrage.kUmfrage, tumfrage.cName, tseo.cSeo
                        FROM tumfrage
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kUmfrage'
                            AND tseo.kKey = tumfrage.kUmfrage
                            AND tseo.kSprache = {$kSprache}
                        WHERE tumfrage.kUmfrage = " . (int)$cKey,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oUmfrage->kUmfrage) && $oUmfrage->kUmfrage > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oUmfrage->cSeo;
                    $oObjekt->cName = !empty($oUmfrage->cName) ? $oUmfrage->cName : 'Link';
                }
                break;

            case URLART_TAG:
                $oObjekt->kNews = (int)$cKey;
                $oObjekt->cKey  = 'kTag';
                $oTag           = Shop::Container()->getDB()->query(
                    "SELECT ttag.kTag, ttag.cName, tseo.cSeo
                        FROM ttag
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kTag'
                            AND tseo.kKey = ttag.kTag
                            AND tseo.kSprache = {$kSprache}
                        WHERE ttag.kTag = " . (int)$cKey,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oTag->kTag) && $oTag->kTag > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oTag->cSeo;
                    $oObjekt->cName = !empty($oTag->cName) ? $oTag->cName : 'Link';
                }
                break;

            case URLART_LIVESUCHE:
                $oObjekt->kNews = (int)$cKey;
                $oObjekt->cKey  = 'kSuchanfrage';
                $oSuchanfrage   = Shop::Container()->getDB()->query(
                    "SELECT tsuchanfrage.kSuchanfrage, tsuchanfrage.cSuche, tseo.cSeo
                        FROM tsuchanfrage
                        LEFT JOIN tseo 
                            ON tseo.cKey = 'kSuchanfrage'
                            AND tseo.kKey = tsuchanfrage.kSuchanfrage
                            AND tseo.kSprache = {$kSprache}
                        WHERE tsuchanfrage.kSuchanfrage = " . (int)$cKey,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oSuchanfrage->kSuchanfrage) && $oSuchanfrage->kSuchanfrage > 0) {
                    $bVorhanden     = true;
                    $oObjekt->cSeo  = $oSuchanfrage->cSeo;
                    $oObjekt->cName = !empty($oSuchanfrage->cSuche) ? $oSuchanfrage->cSuche : 'Link';
                }
                break;
        }
        executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_PARSENEWSTEXT);

        if (strlen($cName) > 0) {
            $oObjekt->cName = $cName;
            $cName          = ':' . $cName;
        }
        if ($bVorhanden) {
            $cURL  = baueURL($oObjekt, $cParameter_arr[$cParameter]);
            $cText = str_replace(
                '$#' . $cParameter . ':' . $cKey . $cName . '#$',
                '<a href="' . Shop::getURL() . '/' . $cURL . '">' . $oObjekt->cName . '</a>',
                $cText
            );
        } else {
            $cText = str_replace(
                '$#' . $cParameter . ':' . $cKey . $cName . '#$',
                '<a href="' . Shop::getURL() . '/" >' . Shop::Lang()->get('parseTextNoLinkID') . '</a>',
                $cText
            );
        }
    }

    return $cText;
}

/**
 * @param int $kSprache
 * @param int $kKundengruppe
 * @return object|bool
 */
function gibAGBWRB($kSprache, $kKundengruppe)
{
    if ($kSprache <= 0 || $kKundengruppe <= 0) {
        return false;
    }
    $linkHelper = LinkHelper::getInstance();
    $oLinkAGB   = null;
    $oLinkWRB   = null;
    // kLink für AGB und WRB suchen
    foreach ($linkHelper->getSpecialPages() as $sp) {
        if ($sp->nLinkart === LINKTYP_AGB) {
            $oLinkAGB = $sp;
        } elseif ($sp->nLinkart === LINKTYP_WRB) {
            $oLinkWRB = $sp;
        }
    }
    $oAGBWRB = Shop::Container()->getDB()->select('ttext', 'kKundengruppe', (int)$kKundengruppe, 'kSprache', (int)$kSprache);
    if (!empty($oAGBWRB->kText)) {
        $oAGBWRB->cURLAGB  = $oLinkAGB->cURL ?? '';
        $oAGBWRB->cURLWRB  = $oLinkWRB->cURL ?? '';
        $oAGBWRB->kLinkAGB = (isset($oLinkAGB->kLink) && $oLinkAGB->kLink > 0)
            ? (int)$oLinkAGB->kLink
            : 0;
        $oAGBWRB->kLinkWRB = (isset($oLinkWRB->kLink) && $oLinkWRB->kLink > 0)
            ? (int)$oLinkWRB->kLink
            : 0;

        return $oAGBWRB;
    }
    $oAGBWRB = Shop::Container()->getDB()->select('ttext', 'nStandard', 1);
    if (!empty($oAGBWRB->kText)) {
        $oAGBWRB->cURLAGB  = $oLinkAGB->cURL ?? '';
        $oAGBWRB->cURLWRB  = $oLinkWRB->cURL ?? '';
        $oAGBWRB->kLinkAGB = (isset($oLinkAGB->kLink) && $oLinkAGB->kLink > 0)
            ? (int)$oLinkAGB->kLink
            : 0;
        $oAGBWRB->kLinkWRB = (isset($oLinkWRB->kLink) && $oLinkWRB->kLink > 0)
            ? (int)$oLinkWRB->kLink
            : 0;

        return $oAGBWRB;
    }

    return false;
}

/**
 * @param int $kSprache
 * @return array|mixed
 */
function holeAlleSuchspecialOverlays($kSprache = 0)
{
    if (!$kSprache) {
        $oSprache = gibStandardsprache(true);
        $kSprache = $oSprache->kSprache;
        if (!$kSprache) {
            return [];
        }
    }
    $kSprache = (int)$kSprache;
    $cacheID  = 'haso_' . $kSprache;
    if (($overlays = Shop::Cache()->get($cacheID)) === false) {
        $ssoList = Shop::Container()->getDB()->query(
            "SELECT tsuchspecialoverlay.*, tsuchspecialoverlaysprache.kSprache,
                tsuchspecialoverlaysprache.cBildPfad, tsuchspecialoverlaysprache.nAktiv,
                tsuchspecialoverlaysprache.nPrio, tsuchspecialoverlaysprache.nMargin,
                tsuchspecialoverlaysprache.nTransparenz,
                tsuchspecialoverlaysprache.nGroesse, tsuchspecialoverlaysprache.nPosition
                FROM tsuchspecialoverlay
                JOIN tsuchspecialoverlaysprache
                    ON tsuchspecialoverlaysprache.kSuchspecialOverlay = tsuchspecialoverlay.kSuchspecialOverlay
                    AND tsuchspecialoverlaysprache.kSprache = " . $kSprache . "
                WHERE tsuchspecialoverlaysprache.nAktiv = 1
                    AND tsuchspecialoverlaysprache.nPrio > 0
                ORDER BY tsuchspecialoverlaysprache.nPrio DESC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        $overlays = [];
        foreach ($ssoList as $sso) {
            $sso->kSuchspecialOverlay = (int)$sso->kSuchspecialOverlay;
            $sso->nAktiv              = (int)$sso->nAktiv;
            $sso->nPrio               = (int)$sso->nPrio;
            $sso->nMargin             = (int)$sso->nMargin;
            $sso->nTransparenz        = (int)$sso->nTransparenz;
            $sso->nGroesse            = (int)$sso->nGroesse;
            $sso->nPosition           = (int)$sso->nPosition;

            $idx = strtolower(str_replace([' ', '-', '_'], '', $sso->cSuchspecial));
            $idx = preg_replace(
                ['/Ä/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/', '/ß/'],
                ['ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss'],
                $idx
            );
            $overlays[$idx]              = $sso;
            $overlays[$idx]->cPfadKlein  = PFAD_SUCHSPECIALOVERLAY_KLEIN . $overlays[$idx]->cBildPfad;
            $overlays[$idx]->cPfadNormal = PFAD_SUCHSPECIALOVERLAY_NORMAL . $overlays[$idx]->cBildPfad;
            $overlays[$idx]->cPfadGross  = PFAD_SUCHSPECIALOVERLAY_GROSS . $overlays[$idx]->cBildPfad;
        }
        Shop::Cache()->set($cacheID, $overlays, [CACHING_GROUP_OPTION]);
    }

    return $overlays;
}

/**
 * @return array
 */
function baueAlleSuchspecialURLs()
{
    $overlays = [];

    // URLs bauen
    $overlays[SEARCHSPECIALS_BESTSELLER]        = new stdClass();
    $overlays[SEARCHSPECIALS_BESTSELLER]->cName = Shop::Lang()->get('bestseller');
    $overlays[SEARCHSPECIALS_BESTSELLER]->cURL  = baueSuchSpecialURL(SEARCHSPECIALS_BESTSELLER);

    $overlays[SEARCHSPECIALS_SPECIALOFFERS]        = new stdClass();
    $overlays[SEARCHSPECIALS_SPECIALOFFERS]->cName = Shop::Lang()->get('specialOffers');
    $overlays[SEARCHSPECIALS_SPECIALOFFERS]->cURL  = baueSuchSpecialURL(SEARCHSPECIALS_SPECIALOFFERS);

    $overlays[SEARCHSPECIALS_NEWPRODUCTS]        = new stdClass();
    $overlays[SEARCHSPECIALS_NEWPRODUCTS]->cName = Shop::Lang()->get('newProducts');
    $overlays[SEARCHSPECIALS_NEWPRODUCTS]->cURL  = baueSuchSpecialURL(SEARCHSPECIALS_NEWPRODUCTS);

    $overlays[SEARCHSPECIALS_TOPOFFERS]        = new stdClass();
    $overlays[SEARCHSPECIALS_TOPOFFERS]->cName = Shop::Lang()->get('topOffers');
    $overlays[SEARCHSPECIALS_TOPOFFERS]->cURL  = baueSuchSpecialURL(SEARCHSPECIALS_TOPOFFERS);

    $overlays[SEARCHSPECIALS_UPCOMINGPRODUCTS]        = new stdClass();
    $overlays[SEARCHSPECIALS_UPCOMINGPRODUCTS]->cName = Shop::Lang()->get('upcomingProducts');
    $overlays[SEARCHSPECIALS_UPCOMINGPRODUCTS]->cURL  = baueSuchSpecialURL(SEARCHSPECIALS_UPCOMINGPRODUCTS);

    $overlays[SEARCHSPECIALS_TOPREVIEWS]        = new stdClass();
    $overlays[SEARCHSPECIALS_TOPREVIEWS]->cName = Shop::Lang()->get('topReviews');
    $overlays[SEARCHSPECIALS_TOPREVIEWS]->cURL  = baueSuchSpecialURL(SEARCHSPECIALS_TOPREVIEWS);

    return $overlays;
}

/**
 * @param int $kKey
 * @return mixed|string
 */
function baueSuchSpecialURL($kKey)
{
    $kKey    = (int)$kKey;
    $cacheID = 'bsurl_' . $kKey . '_' . Shop::getLanguageID();
    if (($url = Shop::Cache()->get($cacheID)) !== false) {
        executeHook(HOOK_BOXEN_INC_SUCHSPECIALURL);

        return $url;
    }
    $oSeo = Shop::Container()->getDB()->select(
        'tseo',
        'kSprache', Shop::getLanguageID(),
        'cKey', 'suchspecial',
        'kKey', $kKey,
        false,
        'cSeo'
    );
    if (!isset($oSeo->cSeo)) {
        $oSeo = new stdClass();
    }

    $oSeo->kSuchspecial = $kKey;
    executeHook(HOOK_BOXEN_INC_SUCHSPECIALURL);
    $url = baueURL($oSeo, URLART_SEARCHSPECIALS);
    Shop::Cache()->set($cacheID, $url, [CACHING_GROUP_CATEGORY]);

    return $url;
}

/**
 * @param string      $cPasswort
 * @param null{string $cHashPasswort
 * @return bool|string
 * @deprecated since 5.0
 */
function cryptPasswort($cPasswort, $cHashPasswort = null)
{
    $cSalt   = sha1(uniqid(mt_rand(), true));
    $nLaenge = strlen($cSalt);
    $nLaenge = max($nLaenge >> 3, ($nLaenge >> 2) - strlen($cPasswort));
    $cSalt   = $cHashPasswort
        ? substr($cHashPasswort, min(strlen($cPasswort), strlen($cHashPasswort) - $nLaenge), $nLaenge)
        : strrev(substr($cSalt, 0, $nLaenge));
    $cHash   = sha1($cPasswort);
    $cHash   = sha1(substr($cHash, 0, strlen($cPasswort)) . $cSalt . substr($cHash, strlen($cPasswort)));
    $cHash   = substr($cHash, $nLaenge);
    $cHash   = substr($cHash, 0, strlen($cPasswort)) . $cSalt . substr($cHash, strlen($cPasswort));

    return $cHashPasswort && $cHashPasswort !== $cHash ? false : $cHash;
}

/**
 *
 */
function setzeSpracheUndWaehrungLink()
{
    global $oZusatzFilter, $sprachURL, $AktuellerArtikel, $kSeite, $kLink, $AktuelleSeite;
    $shopURL    = Shop::getURL() . '/';
    $helper     = LinkHelper::getInstance();
    $NaviFilter = Shop::getProductFilter();
    if ($kSeite !== null && $kSeite > 0) {
        $kLink = $kSeite;
    }
    // Sprachauswahl
    if (count(Session::Languages()) > 1) {
        /** @var Artikel $AktuellerArtikel */
        if ($AktuellerArtikel !== null
            && $AktuellerArtikel->kArtikel > 0
            && empty($AktuellerArtikel->cSprachURL_arr)
        ) {
            $AktuellerArtikel->baueArtikelSprachURL();
        }
        foreach (Session::Languages() as $i => $oSprache) {
            if (isset($AktuellerArtikel->kArtikel, $AktuellerArtikel->cSprachURL_arr[$oSprache->cISO])
                && $AktuellerArtikel->kArtikel > 0
            ) {
                $oSprache->cURL     = $AktuellerArtikel->cSprachURL_arr[$oSprache->cISO];
                $oSprache->cURLFull = $shopURL . $AktuellerArtikel->cSprachURL_arr[$oSprache->cISO];
            } elseif (($kLink > 0 || $kSeite > 0) && isset($sprachURL[$oSprache->cISO])) {
                $oSprache->cURL     = $sprachURL[$oSprache->cISO];
                $oSprache->cURLFull = $shopURL . $sprachURL[$oSprache->cISO];
            } elseif ($AktuelleSeite === 'WARENKORB'
                || $AktuelleSeite === 'KONTAKT'
                || $AktuelleSeite === 'REGISTRIEREN'
                || $AktuelleSeite === 'MEIN KONTO'
                || $AktuelleSeite === 'NEWSLETTER'
                || $AktuelleSeite === 'UMFRAGE'
                || $AktuelleSeite === 'BESTELLVORGANG'
                || $AktuelleSeite === 'STARTSEITE'
                || $AktuelleSeite === 'PASSWORT VERGESSEN'
                || $AktuelleSeite === 'NEWS'
                || $AktuelleSeite === 'WUNSCHLISTE'
                || $AktuelleSeite === 'VERGLEICHSLISTE'
            ) {
                switch ($AktuelleSeite) {
                    case 'STARTSEITE':
                        $id               = null;
                        $originalLanguage = $NaviFilter->getLanguageID();
                        $NaviFilter->setLanguageID($oSprache->kSprache);
                        $oSprache->cURL = $NaviFilter->getFilterURL()->getURL($oZusatzFilter);
                        $NaviFilter->setLanguageID($originalLanguage);
                        if ($oSprache->cURL === $shopURL) {
                            $oSprache->cURL .= '?lang=' . $oSprache->cISO;
                        }
                        $oSprache->cURLFull = $oSprache->cURL;
                        break;

                    case 'WARENKORB':
                        $id = 'warenkorb.php';
                        break;

                    case 'KONTAKT':
                        $id = 'kontakt.php';
                        break;

                    case 'REGISTRIEREN':
                        $id = 'registrieren.php';
                        break;

                    case 'MEIN KONTO':
                        $id = 'jtl.php';
                        break;

                    case 'NEWSLETTER':
                        $id = 'newsletter.php';
                        break;

                    case 'UMFRAGE':
                        $id = 'umfrage.php';
                        break;

                    case 'BESTELLVORGANG':
                        $id = 'bestellvorgang.php';
                        break;

                    case 'PASSWORT VERGESSEN':
                        $id = 'pass.php';
                        break;

                    case 'NEWS':
                        $id = 'news.php';
                        break;

                    case 'VERGLEICHSLISTE':
                        $id = 'vergleichsliste.php';
                        break;

                    case 'WUNSCHLISTE':
                        $id = 'wunschliste.php';
                        break;

                    default:
                        $id = null;
                        break;
                }
                if ($id !== null) {
                    $url = $helper->getStaticRoute($id, false, false, $oSprache->cISO);
                    //check if there is a SEO link for the given file
                    if ($url === $id) { //no SEO link - fall back to php file with GET param
                        $url = $shopURL . $id . '?lang=' . $oSprache->cISO;
                    } else { //there is a SEO link - make it a full URL
                        $url = $helper->getStaticRoute($id, true, false, $oSprache->cISO);
                    }
                    $oSprache->cURL     = $url;
                    $oSprache->cURLFull = $url;
                }

                executeHook(HOOK_TOOLSGLOBAL_INC_SWITCH_SETZESPRACHEUNDWAEHRUNG_SPRACHE);
            } else {
                $originalLanguage = $NaviFilter->getLanguageID();
                $NaviFilter->setLanguageID($oSprache->kSprache);
                $cUrl = $NaviFilter->getFilterURL()->getURL($oZusatzFilter);
                $NaviFilter->setLanguageID($originalLanguage);
                if ($NaviFilter->getPage() > 1) {
                    if (strpos($sprachURL, 'navi.php') !== false) {
                        $cUrl .= '&amp;seite=' . $NaviFilter->getPage();
                    } else {
                        $cUrl .= SEP_SEITE . $NaviFilter->getPage();
                    }
                }
                $oSprache->cURL     = $cUrl;
                $oSprache->cURLFull = $cUrl;
            }
        }
    }
    // Währungsauswahl
    if (count(Session::Currencies()) > 1) {
        if (isset($AktuellerArtikel->kArtikel)
            && $AktuellerArtikel->kArtikel > 0
            && empty($AktuellerArtikel->cSprachURL_arr)
        ) {
            $AktuellerArtikel->baueArtikelSprachURL(false);
        }
        foreach (Session::Currencies() as $i => $currency) {
            $url = '';
            if (isset($AktuellerArtikel->kArtikel, $_SESSION['kSprache'], $AktuellerArtikel->cSprachURL_arr[$_SESSION['cISOSprache']])
                && $AktuellerArtikel->kArtikel > 0
            ) {
                $url = $AktuellerArtikel->cSprachURL_arr[$_SESSION['cISOSprache']] .
                    '?curr=' . $currency->getCode();
            } elseif ($AktuelleSeite === 'WARENKORB'
                || $AktuelleSeite === 'KONTAKT'
                || $AktuelleSeite === 'REGISTRIEREN'
                || $AktuelleSeite === 'MEIN KONTO'
                || $AktuelleSeite === 'NEWSLETTER'
                || $AktuelleSeite === 'UMFRAGE'
                || $AktuelleSeite === 'BESTELLVORGANG'
                || $AktuelleSeite === 'NEWS'
                || $AktuelleSeite === 'PASSWORT VERGESSEN'
                || $AktuelleSeite === 'WUNSCHLISTE'
            ) { // Special Seiten
                switch ($AktuelleSeite) {
                    case 'WARENKORB':
                        $id = 'warenkorb.php';
                        break;

                    case 'KONTAKT':
                        $id = 'kontakt.php';
                        break;

                    case 'REGISTRIEREN':
                        $id = 'registrieren.php';
                        break;

                    case 'MEIN KONTO':
                        $id = 'jtl.php';
                        break;

                    case 'NEWSLETTER':
                        $id = 'newsletter.php';
                        break;

                    case 'UMFRAGE':
                        $id = 'umfrage.php';
                        break;

                    case 'BESTELLVORGANG':
                        $id = 'bestellvorgang.php';
                        break;

                    case 'NEWS':
                        $id = 'news.php';
                        break;

                    case 'PASSWORT VERGESSEN':
                        $id = 'pass.php';
                        break;

                    case 'WUNSCHLISTE':
                        $id = 'wunschliste.php';
                        break;

                    default:
                        $id = null;
                        break;
                }
                if ($id !== null) {
                    $url = $helper->getStaticRoute($id, false);
                    //check if there is a SEO link for the given file
                    if ($url === $id) { //no SEO link - fall back to php file with GET param
                        $url = $shopURL . $id . '?lang=' . $_SESSION['cISOSprache'] . '&curr=' . $currency->getCode();
                    } else { //there is a SEO link - make it a full URL
                        $url = $helper->getStaticRoute($id) . '?curr=' . $currency->getCode();
                    }
                }
            } elseif ($kLink > 0) {
                $url = '?s=' . $kLink . '&lang=' . $_SESSION['cISOSprache'] . '&curr=' . $currency->getCode();
            } else {
                $url = $NaviFilter->getFilterURL()->getURL($oZusatzFilter);
                $url .= strpos($url, '?') === false
                    ? ('?curr=' . $currency->getCode())
                    : ('&curr=' . $currency->getCode());
            }
            $currency->setURL($url);
            $url = strpos($url, Shop::getURL()) === false
                ? ($shopURL . $url)
                : $url;
            $currency->setURLFull($url);
        }
    }

    executeHook(HOOK_TOOLSGLOBAL_INC_SETZESPRACHEUNDWAEHRUNG_WAEHRUNG, [
        'oNaviFilter'       => &$NaviFilter,
        'oZusatzFilter'     => &$oZusatzFilter,
        'cSprachURL'        => &$sprachURL,
        'oAktuellerArtikel' => &$AktuellerArtikel,
        'kSeite'            => &$kSeite,
        'kLink'             => &$kLink,
        'AktuelleSeite'     => &$AktuelleSeite
    ]);
}

/**
 * Prueft ob SSL aktiviert ist und auch durch Einstellung genutzt werden soll
 * -1 = SSL nicht aktiv und nicht erlaubt
 * 1 = SSL aktiv durch Einstellung nicht erwünscht
 * 2 = SSL aktiv und erlaubt
 * 4 = SSL nicht aktiv aber erzwungen
 *
 * @return int
 */
function pruefeSSL()
{
    $conf       = Shop::getSettings([CONF_GLOBAL]);
    $cSSLNutzen = $conf['global']['kaufabwicklung_ssl_nutzen'];
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
    }
    // Ist im Server SSL aktiv?
    if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] === '1')) {
        if ($cSSLNutzen === 'P') { // SSL durch Einstellung erlaubt?
            return 2;
        }

        return 1;
    }
    if ($cSSLNutzen === 'P') {
        return 4;
    }

    return -1;
}

/**
 * @param int    $nAnzahlStellen
 * @param string $cString
 * @return bool|string
 * @deprecated since 5.0
 */
function gibUID($nAnzahlStellen = 40, $cString = '')
{
    $cUID            = '';
    $cSalt           = '';
    $cSaltBuchstaben = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789';
    // Gen SALT
    for ($j = 0; $j < 30; $j++) {
        $cSalt .= substr($cSaltBuchstaben, mt_rand(0, strlen($cSaltBuchstaben) - 1), 1);
    }
    $cSalt = md5($cSalt);
    mt_srand();
    // Wurde ein String übergeben?
    if (strlen($cString) > 0) {
        // Hat der String Elemente?
        list($cString_arr) = explode(';', $cString);
        if (is_array($cString_arr) && count($cString_arr) > 0) {
            foreach ($cString_arr as $string) {
                $cUID .= md5($string . md5(PFAD_ROOT . (time() - mt_rand())));
            }

            $cUID = md5($cUID . $cSalt);
        } else {
            $sl = strlen($cString);
            for ($i = 0; $i < $sl; $i++) {
                $nPos = mt_rand(0, strlen($cString) - 1);
                if (((int)date('w') % 2) <= strlen($cString)) {
                    $nPos = (int)date('w') % 2;
                }
                $cUID .= md5(substr($cString, $nPos, 1) . $cSalt . md5(PFAD_ROOT . (microtime(true) - mt_rand())));
            }
        }
        $cUID = cryptPasswort($cUID . $cSalt);
    } else {
        $cUID = cryptPasswort(md5(M_PI . $cSalt . md5(time() - mt_rand())));
    }
    // Anzahl Stellen beachten
    return $nAnzahlStellen > 0 ? substr($cUID, 0, $nAnzahlStellen) : $cUID;
}

/**
 * @param string $cText
 * @return string
 */
function verschluesselXTEA($cText)
{
    return strlen($cText) > 0
        ? (new XTEA(BLOWFISH_KEY))->encrypt($cText)
        : $cText;
}

/**
 * @param string $cText
 * @return string
 */
function entschluesselXTEA($cText)
{
    return strlen($cText) > 0
        ? (new XTEA(BLOWFISH_KEY))->decrypt($cText)
        : $cText;
}

/**
 * Prüft ob eine die angegebende Email in temailblacklist vorhanden ist
 * Gibt true zurück, falls Email geblockt, ansonsten false
 *
 * @param string $cEmail
 * @return bool
 */
function pruefeEmailblacklist($cEmail)
{
    $cEmail = strtolower(StringHandler::filterXSS($cEmail));
    if (!valid_email($cEmail)) {
        return true;
    }
    $Einstellungen = Shop::getSettings([CONF_EMAILBLACKLIST]);
    if ($Einstellungen['emailblacklist']['blacklist_benutzen'] !== 'Y') {
        return false;
    }
    $oEmailBlackList_arr = Shop::Container()->getDB()->query(
        "SELECT cEmail 
            FROM temailblacklist",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    foreach ($oEmailBlackList_arr as $oEmailBlackList) {
        if (strpos($oEmailBlackList->cEmail, '*') !== false) {
            $cEmailBlackListRegEx = str_replace("*", "[a-z0-9\-\_\.\@\+]*", $oEmailBlackList->cEmail);
            preg_match('/' . $cEmailBlackListRegEx . '/', $cEmail, $cTreffer_arr);
            // Blocked
            if (isset($cTreffer_arr[0]) && strlen($cEmail) === strlen($cTreffer_arr[0])) {
                // Email schonmal geblockt worden?
                $oEmailblacklistBlock = Shop::Container()->getDB()->select('temailblacklistblock', 'cEmail', $cEmail);
                if (!empty($oEmailblacklistBlock->cEmail)) {
                    $_upd                = new stdClass();
                    $_upd->dLetzterBlock = 'now()';
                    Shop::Container()->getDB()->update('temailblacklistblock', 'cEmail', $cEmail, $_upd);
                } else {
                    // temailblacklistblock Eintrag
                    $oEmailblacklistBlock                = new stdClass();
                    $oEmailblacklistBlock->cEmail        = $cEmail;
                    $oEmailblacklistBlock->dLetzterBlock = 'now()';
                    Shop::Container()->getDB()->insert('temailblacklistblock', $oEmailblacklistBlock);
                }

                return true;
            }
        } elseif (strtolower($oEmailBlackList->cEmail) === strtolower($cEmail)) {
            // Email schonmal geblockt worden?
            $oEmailblacklistBlock = Shop::Container()->getDB()->select('temailblacklistblock', 'cEmail', $cEmail);

            if (!empty($oEmailblacklistBlock->cEmail)) {
                $_upd                = new stdClass();
                $_upd->dLetzterBlock = 'now()';
                Shop::Container()->getDB()->update('temailblacklistblock', 'cEmail', $cEmail, $_upd);
            } else {
                // temailblacklistblock Eintrag
                $oEmailblacklistBlock                = new stdClass();
                $oEmailblacklistBlock->cEmail        = $cEmail;
                $oEmailblacklistBlock->dLetzterBlock = 'now()';
                Shop::Container()->getDB()->insert('temailblacklistblock', $oEmailblacklistBlock);
            }

            return true;
        }
    }

    return false;
}

/**
 * @param string $cMail
 * @param string $cBestellNr
 * @return null|TrustedShops
 */
function gibTrustedShopsBewertenButton($cMail, $cBestellNr)
{
    $button = null;
    if (strlen($cMail) > 0 && strlen($cBestellNr) > 0) {
        $languageCode = StringHandler::convertISO2ISO639(Shop::getLanguageCode());
        $langCodes    = ['de', 'en', 'fr', 'pl', 'es'];
        if (in_array($languageCode, $langCodes, true)) {
            $ts       = new TrustedShops(-1, $languageCode);
            $tsRating = $ts->holeKundenbewertungsstatus($languageCode);

            if (!empty($tsRating->cTSID)
                && $tsRating->kTrustedshopsKundenbewertung > 0
                && (int)$tsRating->nStatus === 1
            ) {
                $button       = new stdClass();
                $imageBaseURL = Shop::getImageBaseURL() .
                    PFAD_TEMPLATES .
                    Template::getInstance()->getDir() .
                    '/themes/base/images/trustedshops/rate_now_';
                $images       = [
                    'de' => $imageBaseURL . 'de.png',
                    'en' => $imageBaseURL . 'en.png',
                    'fr' => $imageBaseURL . 'fr.png',
                    'es' => $imageBaseURL . 'es.png',
                    'nl' => $imageBaseURL . 'nl.png',
                    'pl' => $imageBaseURL . 'pl.png'
                ];

                $button->cURL    = 'https://www.trustedshops.com/buyerrating/rate_' .
                    $tsRating->cTSID .
                    'html&buyerEmail=' . urlencode(base64_encode($cMail)) .
                    '&shopOrderID=' . urlencode(base64_encode($cBestellNr));
                $button->cPicURL = $images[$languageCode];
            }
        }
    }

    return $button;
}

/**
 * gibt alle Sprachen zurück
 *
 * @param int $nOption
 * 0 = Normales Array
 * 1 = Gib ein Assoc mit Key = kSprache
 * 2 = Gib ein Assoc mit Key = cISO
 * @return array
 */
function gibAlleSprachen($nOption = 0)
{
    $languages = Session::Languages();
    if (count($languages) > 0) {
        switch ($nOption) {
            case 2:
                return baueAssocArray($languages, 'cISO');

            case 1:
                return baueAssocArray($languages, 'kSprache');

            case 0:
            default:
                return $languages;
        }
    }
    $oSprach_arr = array_map(
        function ($s) {
            $s->kSprache = (int)$s->kSprache;

            return $s;
        },
        Shop::Container()->getDB()->query(
            "SELECT * 
                FROM tsprache 
                ORDER BY cShopStandard DESC, cNameDeutsch",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        )
    );
    switch ($nOption) {
        case 2:
            return baueAssocArray($oSprach_arr, 'cISO');

        case 1:
            return baueAssocArray($oSprach_arr, 'kSprache');

        case 0:
        default:
            return $oSprach_arr;
    }
}

/**
 * @param string $cURL
 * @return bool
 */
function pruefeSOAP($cURL = '')
{    return !(strlen($cURL) > 0 && !phpLinkCheck($cURL)) && class_exists('SoapClient');
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
 * Bekommmt ein Array von Objekten und baut ein assoziatives Array
 *
 * @param array $oObjekt_arr
 * @param string $cKey
 * @return array
 */
function baueAssocArray(array $oObjekt_arr, $cKey)
{
    $oObjektAssoc_arr = [];
    foreach ($oObjekt_arr as $oObjekt) {
        if (is_object($oObjekt)) {
            $oMember_arr = array_keys(get_object_vars($oObjekt));
            if (is_array($oMember_arr) && count($oMember_arr) > 0) {
                $oObjektAssoc_arr[$oObjekt->$cKey] = new stdClass();
                foreach ($oMember_arr as $oMember) {
                    $oObjektAssoc_arr[$oObjekt->$cKey]->$oMember = $oObjekt->$oMember;
                }
            }
        }
    }

    return $oObjektAssoc_arr;
}

/**
 * @param string $cAnrede
 * @param int    $kSprache
 * @param int    $kKunde
 * @return mixed
 */
function mappeKundenanrede($cAnrede, $kSprache, $kKunde = 0)
{
    $kSprache = (int)$kSprache;
    $kKunde   = (int)$kKunde;
    if (($kSprache > 0 || $kKunde > 0) && strlen($cAnrede) > 0) {
        if ($kSprache === 0 && $kKunde > 0) {
            $oKunde = Shop::Container()->getDB()->query(
                "SELECT kSprache
                    FROM tkunde
                    WHERE kKunde = " . $kKunde,
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oKunde->kSprache) && $oKunde->kSprache > 0) {
                $kSprache = (int)$oKunde->kSprache;
            }
        }
        $cISOSprache = '';
        if ($kSprache > 0) { // Kundensprache, falls gesetzt
            $oSprache = Shop::Container()->getDB()->select('tsprache', 'kSprache', $kSprache);
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $cISOSprache = $oSprache->cISO;
            }
        } else { // Ansonsten Standardsprache
            $oSprache = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
            if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
                $cISOSprache = $oSprache->cISO;
            }
        }
        $cName       = $cAnrede === 'm' ? 'salutationM' : 'salutationW';
        $oSprachWert = Shop::Container()->getDB()->query(
            "SELECT tsprachwerte.cWert
                FROM tsprachwerte
                JOIN tsprachiso
                    ON tsprachiso.cISO = '" . $cISOSprache . "'
                WHERE tsprachwerte.kSprachISO = tsprachiso.kSprachISO
                    AND tsprachwerte.cName = '" . $cName . "'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (isset($oSprachWert->cWert) && strlen($oSprachWert->cWert) > 0) {
            $cAnrede = $oSprachWert->cWert;
        }
    }

    return $cAnrede;
}

/**
 *
 */
function pruefeKampagnenParameter()
{
    $campaigns = Kampagne::getAvailable();
    if (!empty($_SESSION['oBesucher']->kBesucher) && count($campaigns) > 0) {
        $bKampagnenHit = false;
        foreach ($campaigns as $oKampagne) {
            // Wurde für die aktuelle Kampagne der Parameter via GET oder POST uebergeben?
            if (strlen(verifyGPDataString($oKampagne->cParameter)) > 0
                && isset($oKampagne->nDynamisch)
                && ((int)$oKampagne->nDynamisch === 1
                    || ((int)$oKampagne->nDynamisch === 0
                        && isset($oKampagne->cWert)
                        && strtolower($oKampagne->cWert) === strtolower(verifyGPDataString($oKampagne->cParameter)))
                )
            ) {
                $referrer = gibReferer();
                //wurde der HIT für diesen Besucher schon gezaehlt?
                $oVorgang = Shop::Container()->getDB()->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey', 'cCustomData'],
                    [
                        KAMPAGNE_DEF_HIT,
                        (int)$oKampagne->kKampagne,
                        (int)$_SESSION['oBesucher']->kBesucher,
                        StringHandler::filterXSS(Shop::Container()->getDB()->escape($_SERVER['REQUEST_URI'])) . ';' . $referrer
                    ]
                );

                if (!isset($oVorgang->kKampagneVorgang)) {
                    $oKampagnenVorgang               = new stdClass();
                    $oKampagnenVorgang->kKampagne    = $oKampagne->kKampagne;
                    $oKampagnenVorgang->kKampagneDef = KAMPAGNE_DEF_HIT;
                    $oKampagnenVorgang->kKey         = $_SESSION['oBesucher']->kBesucher;
                    $oKampagnenVorgang->fWert        = 1.0;
                    $oKampagnenVorgang->cParamWert   = verifyGPDataString($oKampagne->cParameter);
                    $oKampagnenVorgang->cCustomData  = StringHandler::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer;
                    if ((int)$oKampagne->nDynamisch === 0) {
                        $oKampagnenVorgang->cParamWert = $oKampagne->cWert;
                    }
                    $oKampagnenVorgang->dErstellt = 'now()';

                    Shop::Container()->getDB()->insert('tkampagnevorgang', $oKampagnenVorgang);
                    // Kampagnenbesucher in die Session
                    $_SESSION['Kampagnenbesucher']        = $oKampagne;
                    $_SESSION['Kampagnenbesucher']->cWert = $oKampagnenVorgang->cParamWert;

                    break;
                }
            }

            if (!$bKampagnenHit
                && isset($_SERVER['HTTP_REFERER'])
                && strpos($_SERVER['HTTP_REFERER'], '.google.') !== false
            ) {
                // Besucher kommt von Google und hat vorher keine Kampagne getroffen
                $oVorgang = Shop::Container()->getDB()->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey'],
                    [KAMPAGNE_DEF_HIT, KAMPAGNE_INTERN_GOOGLE, (int)$_SESSION['oBesucher']->kBesucher]
                );

                if (!isset($oVorgang->kKampagneVorgang)) {
                    $oKampagne                       = new Kampagne(KAMPAGNE_INTERN_GOOGLE);
                    $oKampagnenVorgang               = new stdClass();
                    $oKampagnenVorgang->kKampagne    = KAMPAGNE_INTERN_GOOGLE;
                    $oKampagnenVorgang->kKampagneDef = KAMPAGNE_DEF_HIT;
                    $oKampagnenVorgang->kKey         = $_SESSION['oBesucher']->kBesucher;
                    $oKampagnenVorgang->fWert        = 1.0;
                    $oKampagnenVorgang->cParamWert   = $oKampagne->cWert;
                    $oKampagnenVorgang->dErstellt    = 'now()';

                    if ((int)$oKampagne->nDynamisch === 1) {
                        $oKampagnenVorgang->cParamWert = verifyGPDataString($oKampagne->cParameter);
                    }

                    Shop::Container()->getDB()->insert('tkampagnevorgang', $oKampagnenVorgang);
                    // Kampagnenbesucher in die Session
                    $_SESSION['Kampagnenbesucher']        = $oKampagne;
                    $_SESSION['Kampagnenbesucher']->cWert = $oKampagnenVorgang->cParamWert;
                }
            }
        }
    }
}

/**
 * @param int $kKampagneDef
 * @param int $kKey
 * @param float $fWert
 * @param string $cCustomData
 * @return int
 */
function setzeKampagnenVorgang($kKampagneDef, $kKey, $fWert, $cCustomData = null)
{
    if ($kKampagneDef > 0 && $kKey > 0 && $fWert > 0 && isset($_SESSION['Kampagnenbesucher'])) {
        $oKampagnenVorgang               = new stdClass();
        $oKampagnenVorgang->kKampagne    = $_SESSION['Kampagnenbesucher']->kKampagne;
        $oKampagnenVorgang->kKampagneDef = $kKampagneDef;
        $oKampagnenVorgang->kKey         = $kKey;
        $oKampagnenVorgang->fWert        = $fWert;
        $oKampagnenVorgang->cParamWert   = $_SESSION['Kampagnenbesucher']->cWert;
        $oKampagnenVorgang->dErstellt    = 'now()';

        if ($cCustomData !== null) {
            $oKampagnenVorgang->cCustomData = strlen($cCustomData) > 255 ? substr($cCustomData, 0, 255) : $cCustomData;
        }

        return Shop::Container()->getDB()->insert('tkampagnevorgang', $oKampagnenVorgang);
    }

    return 0;
}

/**
 * YYYY-MM-DD HH:MM:SS, YYYY-MM-DD, now oder now()
 *
 * @param string $cDatum
 * @return array
 */
function gibDatumTeile($cDatum)
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
 *
 */
function pruefeZahlungsartNutzbarkeit()
{
    foreach (Shop::Container()->getDB()->selectAll('tzahlungsart', 'nActive', 1) as $oZahlungsart) {
        // Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
        if ((int)$oZahlungsart->nSOAP === 1 || (int)$oZahlungsart->nCURL === 1 || (int)$oZahlungsart->nSOCKETS === 1) {
            aktiviereZahlungsart($oZahlungsart);
        }
    }
}

/**
 * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
 *
 * @param Zahlungsart|object $oZahlungsart
 * @return bool
 */
function aktiviereZahlungsart($oZahlungsart)
{
    if ($oZahlungsart->kZahlungsart > 0) {
        $kZahlungsart = (int)$oZahlungsart->kZahlungsart;
        $nNutzbar     = 0;
        // SOAP
        if (!empty($oZahlungsart->nSOAP)) {
            $nNutzbar = pruefeSOAP() ? 1 : 0;
        }
        // CURL
        if (!empty($oZahlungsart->nCURL)) {
            $nNutzbar = pruefeCURL() ? 1 : 0;
        }
        // SOCKETS
        if (!empty($oZahlungsart->nSOCKETS)) {
            $nNutzbar = pruefeSOCKETS() ? 1 : 0;
        }
        Shop::Container()->getDB()->update('tzahlungsart', 'kZahlungsart', $kZahlungsart, (object)['nNutzbar' => $nNutzbar]);
    }

    return false;
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
    Shop::Container()->getDB()::RET_AFFECTED_ROWS);
    Shop::Container()->getDB()->queryPrepared(
        "DELETE FROM tbesucher 
            WHERE dLetzteAktivitaet <= date_sub(now(), INTERVAL :interval HOUR)",
    [ 'interval' => $iInterval ],
    Shop::Container()->getDB()::RET_AFFECTED_ROWS);
}

/**
 * @param string $cISO
 * @param int    $kSprache
 * @return int|string|bool
 */
function gibSprachKeyISO($cISO = '', $kSprache = 0)
{
    if (strlen($cISO) > 0) {
        $oSprache = Shop::Container()->getDB()->select('tsprache', 'cISO', $cISO);

        if (isset($oSprache->kSprache) && $oSprache->kSprache > 0) {
            return (int)$oSprache->kSprache;
        }
    } elseif ((int)$kSprache > 0) {
        $oSprache = Shop::Container()->getDB()->select('tsprache', 'kSprache', (int)$kSprache);

        if (isset($oSprache->cISO) && strlen($oSprache->cISO) > 0) {
            return $oSprache->cISO;
        }
    }

    return false;
}

/**
 * @param float $gesamtsumme
 * @return float
 */
function optionaleRundung($gesamtsumme)
{
    $conf = Shop::getSettings([CONF_KAUFABWICKLUNG]);
    if (isset($conf['kaufabwicklung']['bestellabschluss_runden5'])
        && (int)$conf['kaufabwicklung']['bestellabschluss_runden5'] === 1
    ) {
        $int          = (int)($gesamtsumme * 100); // FIRST multiply, THEN cast to int!
        $letzteStelle = $int % 10;
        if ($letzteStelle < 3) {
            $int -= $letzteStelle;
        } elseif ($letzteStelle > 2 && $letzteStelle < 8) {
            $int = $int - $letzteStelle + 5;
        } elseif ($letzteStelle > 7) {
            $int = $int - $letzteStelle + 10;
        }

        return $int / 100;
    }

    return $gesamtsumme;
}

/**
 * @param string $dir
 * @return bool
 */
function delDirRecursively($dir)
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
 * @param Resource $ch
 * @param int $maxredirect
 * @return bool|mixed
 */
function curl_exec_follow($ch, $maxredirect = 5)
{
    $mr = $maxredirect === null ? 5 : (int)$maxredirect;
    if (ini_get('open_basedir') === '') {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    } else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        if ($mr > 0) {
            $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            $rch = curl_copy_handle($ch);
            curl_setopt($rch, CURLOPT_HEADER, true);
            curl_setopt($rch, CURLOPT_NOBODY, true);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
            do {
                curl_setopt($rch, CURLOPT_URL, $newurl);
                $header = curl_exec($rch);
                if (curl_errno($rch)) {
                    $code = 0;
                } else {
                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                    if ($code === 301 || $code === 302) {
                        preg_match('/Location:(.*?)\n/', $header, $matches);
                        $newurl = trim(array_pop($matches));
                    } else {
                        $code = 0;
                    }
                }
            } while ($code && --$mr);
            curl_close($rch);
            if (!$mr) {
                if ($maxredirect === null) {
                    trigger_error('Too many redirects. When following redirects, ' .
                        'libcurl hit the maximum amount.', E_USER_WARNING);
                } else {
                    $maxredirect = 0;
                }

                return false;
            }
            curl_setopt($ch, CURLOPT_URL, $newurl);
        }
    }

    return curl_exec($ch);
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @param null   $cPost
 * @return mixed|string
 */
function http_get_contents($cURL, $nTimeout = 15, $cPost = null)
{
    return make_http_request($cURL, $nTimeout, $cPost, false);
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @param null   $cPost
 * @return int
 */
function http_get_status($cURL, $nTimeout = 15, $cPost = null)
{
    return make_http_request($cURL, $nTimeout, $cPost, true);
}

/**
 * @param string $cURL
 * @param int    $nTimeout
 * @param null   $cPost
 * @param bool   $bReturnStatus - false = return content on success / true = return status code instead of content
 * @return mixed|string
 */
function make_http_request($cURL, $nTimeout = 15, $cPost = null, $bReturnStatus = false)
{
    $nCode = 0;
    $cData = '';

    if (function_exists('curl_init')) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $cURL);
        curl_setopt($curl, CURLOPT_TIMEOUT, $nTimeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, DEFAULT_CURL_OPT_VERIFYPEER);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, DEFAULT_CURL_OPT_VERIFYHOST);
        curl_setopt($curl, CURLOPT_REFERER, Shop::getURL());

        if ($cPost !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $cPost);
        }

        $cData     = curl_exec_follow($curl);
        $cInfo_arr = curl_getinfo($curl);
        $nCode     = (int)$cInfo_arr['http_code'];

        curl_close($curl);
    } elseif (ini_get('allow_url_fopen')) {
        @ini_set('default_socket_timeout', $nTimeout);
        $fileHandle = @fopen($cURL, 'r');
        if ($fileHandle) {
            @stream_set_timeout($fileHandle, $nTimeout);

            $cData = '';
            while (($buffer = fgets($fileHandle)) !== false) {
                $cData .= $buffer;
            }
            if (preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $http_response_header[0], $match)) {
                $nCode = (int)$match[1];
            }
            fclose($fileHandle);
        }
    }
    if (!($nCode >= 200 && $nCode < 300)) {
        $cData = '';
    }

    return $bReturnStatus ? $nCode : $cData;
}

/**
 * @param string|array|object $data the string, array or object to convert recursively
 * @param bool                $encode true if data should be utf-8-encoded or false if data should be utf-8-decoded
 * @param bool                $copy false if objects should be changed, true if they should be cloned first
 * @return string|array|object converted data
 */
function utf8_convert_recursive($data, $encode = true, $copy = false)
{
    if (is_string($data)) {
        $isUtf8 = mb_detect_encoding($data, 'UTF-8', true) !== false;

        if ((!$isUtf8 && $encode) || ($isUtf8 && !$encode)) {
            $data = $encode ? StringHandler::convertUTF8($data) : StringHandler::convertISO($data);
        }
    } elseif (is_array($data)) {
        foreach ($data as $key => $val) {
            $newKey = (string)utf8_convert_recursive($key, $encode);
            $newVal = utf8_convert_recursive($val, $encode);
            unset($data[$key]);
            $data[$newKey] = $newVal;
        }
    } elseif (is_object($data)) {
        if ($copy) {
            $data = clone $data;
        }

        foreach (get_object_vars($data) as $key => $val) {
            $newKey = (string)utf8_convert_recursive($key, $encode);
            $newVal = utf8_convert_recursive($val, $encode);
            unset($data->$key);
            $data->$newKey = $newVal;
        }
    }

    return $data;
}

/**
 * JSON-Encode $data only if it is not already encoded, meaning it avoids double encoding
 *
 * @param mixed $data
 * @return string or false when $data is not encodable
 * @throws Exception
 */
function json_safe_encode($data)
{
    $data = utf8_convert_recursive($data);
    // encode data if not already encoded
    if (is_string($data)) {
        // data is a string
        json_decode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // it is not a JSON string yet
            $data = json_encode($data);
        }
    } else {
        $data = json_encode($data);
    }

    return $data;
}

/**
 * @param object $NaviFilter
 * @param int    $nAnzahl
 * @param bool   $bSeo
 */
function doMainwordRedirect($NaviFilter, $nAnzahl, $bSeo = false)
{
    $cMainword_arr = [
        'getCategory'       => [
            'cKey'   => 'kKategorie',
            'cParam' => 'k'
        ],
        'getManufacturer'   => [
            'cKey'   => 'kHersteller',
            'cParam' => 'h'
        ],
        'getSearchQuery'    => [
            'cKey'   => 'kSuchanfrage',
            'cParam' => 'l'
        ],
        'getAttributeValue' => [
            'cKey'   => 'kMerkmalWert',
            'cParam' => 'm'
        ],
        'getTag'            => [
            'cKey'   => 'kTag',
            'cParam' => 't'
        ],
        'getSearchSpecial'  => [
            'cKey'   => 'kKey',
            'cParam' => 'q'
        ]
    ];

    $kSprache = Shop::getLanguageID();
    if ((int)$nAnzahl === 0 && Shop::getProductFilter()->getFilterCount() > 0) {
        foreach ($cMainword_arr as $function => $cInfo_arr) {
            $cKey   = $cInfo_arr['cKey'];
            $cParam = $cInfo_arr['cParam'];
            $data   = method_exists($NaviFilter, $function)
                ? $NaviFilter->$function()
                : null;
            if (isset($data->$cKey) && (int)$data->$cKey > 0) {
                $cUrl = "?{$cParam}={$data->$cKey}";
                if ($bSeo && isset($data->cSeo) && is_array($data->cSeo)) {
                    $cUrl = "{$data->cSeo[$kSprache]}";
                }
                if (strlen($cUrl) > 0) {
                    header("Location: {$cUrl}", true, 301);
                    exit();
                }
            }
        }
    }
}

/**
 * @param int  $kStueckliste
 * @param bool $bAssoc
 * @return array
 */
function gibStuecklistenKomponente($kStueckliste, $bAssoc = false)
{
    $kStueckliste = (int)$kStueckliste;
    if ($kStueckliste > 0) {
        $oObj_arr = Shop::Container()->getDB()->selectAll('tstueckliste', 'kStueckliste', $kStueckliste);
        if (count($oObj_arr) > 0) {
            if ($bAssoc) {
                $oArtikelAssoc_arr = [];
                foreach ($oObj_arr as $oObj) {
                    $oArtikelAssoc_arr[$oObj->kArtikel] = $oObj;
                }

                return $oArtikelAssoc_arr;
            }

            return $oObj_arr;
        }
    }

    return [];
}

/**
 * @param Artikel $oArtikel
 * @param float   $fAnzahl
 * @return int|null
 */
function pruefeWarenkorbStueckliste($oArtikel, $fAnzahl)
{
    $oStueckliste = ArtikelHelper::isStuecklisteKomponente($oArtikel->kArtikel, true);
    if (!(is_object($oArtikel) && $oArtikel->cLagerBeachten === 'Y'
        && $oArtikel->cLagerKleinerNull !== 'Y'
        && ($oArtikel->kStueckliste > 0 || $oStueckliste))
    ) {
        return null;
    }
    $isComponent = false;
    $components  = null;
    if (isset($oStueckliste->kStueckliste)) {
        $isComponent = true;
    } else {
        $components = gibStuecklistenKomponente($oArtikel->kStueckliste, true);
    }
    foreach (Session::Cart()->PositionenArr as $oPosition) {
        if ($oPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
            continue;
        }
        // Komponente soll hinzugefügt werden aber die Stückliste ist bereits im Warenkorb
        // => Prüfen ob der Lagebestand nicht unterschritten wird
        if ($isComponent
            && isset($oPosition->Artikel->kStueckliste)
            && $oPosition->Artikel->kStueckliste > 0
            && ($oPosition->nAnzahl * $oStueckliste->fAnzahl + $fAnzahl) > $oArtikel->fLagerbestand
        ) {
            return R_LAGER;
        }
        if (!$isComponent && count($components) > 0) {
            //Test auf Stücklistenkomponenten in der aktuellen Position
            if (!empty($oPosition->Artikel->kStueckliste)) {
                $oPositionKomponenten_arr = gibStuecklistenKomponente($oPosition->Artikel->kStueckliste, true);
                foreach ($oPositionKomponenten_arr as $oKomponente) {
                    $desiredComponentQuantity = $fAnzahl * $components[$oKomponente->kArtikel]->fAnzahl;
                    $currentComponentStock    = $oPosition->Artikel->fLagerbestand * $oKomponente->fAnzahl;
                    if ($desiredComponentQuantity > $currentComponentStock) {
                        return R_LAGER;
                    }
                }
            } elseif (isset($components[$oPosition->kArtikel])
                && (($oPosition->nAnzahl * $components[$oPosition->kArtikel]->fAnzahl) +
                    ($components[$oPosition->kArtikel]->fAnzahl * $fAnzahl)) > $oPosition->Artikel->fLagerbestand
            ) {
                return R_LAGER;
            }
        }
    }

    return null;
}

/**
 * @param string $metaProposal the proposed meta text value.
 * @param string $metaSuffix append suffix to meta value that wont be shortened
 * @param int $maxLength $metaProposal will be truncated to $maxlength - strlen($metaSuffix) characters
 * @return string truncated meta value with optional suffix (always appended if set)
 */
function prepareMeta($metaProposal, $metaSuffix = null, $maxLength = null)
{
    $metaProposal = str_replace('"', '', StringHandler::unhtmlentities($metaProposal));
    $metaSuffix   = !empty($metaSuffix) ? $metaSuffix : '';
    if (!empty($maxLength) && $maxLength > 0) {
        $metaProposal = substr($metaProposal, 0, (int)$maxLength);
    }

    return StringHandler::htmlentities(trim(preg_replace('/\s\s+/', ' ', $metaProposal))) . $metaSuffix;
}

/**
 * @return mixed
 */
function gibLetztenTokenDaten()
{
    return isset($_SESSION['xcrsf_token'])
        ? json_decode($_SESSION['xcrsf_token'], true)
        : '';
}

/**
 * @param bool $bAlten
 * @return string
 */
function gibToken($bAlten = false)
{
    if ($bAlten) {
        $cToken_arr = gibLetztenTokenDaten();
        if (!empty($cToken_arr) && array_key_exists('token', $cToken_arr)) {
            return $cToken_arr['token'];
        }
    }

    return sha1(md5(microtime(true)) . (rand(0, 5000000000) * 1000));
}

/**
 * @param bool $bAlten
 * @return string
 */
function gibTokenName($bAlten = false)
{
    if ($bAlten) {
        $cToken_arr = gibLetztenTokenDaten();
        if (!empty($cToken_arr) && array_key_exists('name', $cToken_arr)) {
            return $cToken_arr['name'];
        }
    }

    return substr(sha1(md5(microtime(true)) . (rand(0, 1000000000) * 1000)), 0, 4);
}

/**
 * @return bool
 */
function validToken()
{
    $cName = gibTokenName(true);

    return isset($_POST[$cName]) && gibToken(true) === $_POST[$cName];
}

/**
 * Converts price into given currency
 *
 * @param float  $price
 * @param string $iso - EUR / USD
 * @param int    $id - kWaehrung
 * @param bool   $useRounding
 * @param int    $precision
 * @return float|bool
 */
function convertCurrency($price, $iso = null, $id = null, $useRounding = true, $precision = 2)
{
    if (count(Session::Currencies()) === 0) {
        $_SESSION['Waehrungen'] = [];
        $allCurrencies          = Shop::Container()->getDB()->selectAll('twaehrung', [], [], 'kWaehrung');
        foreach ($allCurrencies as $currency) {
            $_SESSION['Waehrungen'][] = new Currency($currency->kWaehrung);
        }
    }
    foreach (Session::Currencies() as $currency) {
        if (($iso !== null && $currency->getCode() === $iso) || ($id !== null && $currency->getID() === (int)$id)) {
            $newprice = $price * $currency->getConversionFactor();

            return $useRounding ? round($newprice, $precision) : $newprice;
        }
    }

    return false;
}

/**
 *
 */
function resetNeuKundenKupon()
{
    if (Session::Customer()->isLoggedIn()) {
        $hash = Kuponneukunde::Hash(
            null,
            trim($_SESSION['Kunde']->cNachname),
            trim($_SESSION['Kunde']->cStrasse),
            null,
            trim($_SESSION['Kunde']->cPLZ),
            trim($_SESSION['Kunde']->cOrt),
            trim($_SESSION['Kunde']->cLand)
        );
        Shop::Container()->getDB()->delete('tkuponneukunde', ['cDatenHash','cVerwendet'], [$hash,'N']);
    }

    unset($_SESSION['NeukundenKupon'], $_SESSION['NeukundenKuponAngenommen']);
    Session::Cart()
           ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
           ->setzePositionsPreise();
}

/**
 * @param int $kKonfig
 * @param JTLSmarty $smarty
 */
function holeKonfigBearbeitenModus($kKonfig, &$smarty)
{
    $cart = Session::Cart();
    if (isset($cart->PositionenArr[$kKonfig]) && class_exists('Konfigitem')) {
        /** @var WarenkorbPos $oBasePosition */
        $oBasePosition = $cart->PositionenArr[$kKonfig];
        /** @var WarenkorbPos $oBasePosition */
        if ($oBasePosition->istKonfigVater()) {
            $nKonfigitem_arr         = [];
            $nKonfigitemAnzahl_arr   = [];
            $nKonfiggruppeAnzahl_arr = [];

            /** @var WarenkorbPos $oPosition */
            foreach ($cart->PositionenArr as &$oPosition) {
                if ($oPosition->cUnique === $oBasePosition->cUnique && $oPosition->istKonfigKind()) {
                    $oKonfigitem                                              = new Konfigitem($oPosition->kKonfigitem);
                    $nKonfigitem_arr[]                                        = $oKonfigitem->getKonfigitem();
                    $nKonfigitemAnzahl_arr[$oKonfigitem->getKonfigitem()]     = $oPosition->nAnzahl / $oBasePosition->nAnzahl;
                    if ($oKonfigitem->ignoreMultiplier()) {
                        $nKonfiggruppeAnzahl_arr[$oKonfigitem->getKonfiggruppe()] = $oPosition->nAnzahl;
                    } else {
                        $nKonfiggruppeAnzahl_arr[$oKonfigitem->getKonfiggruppe()] = $oPosition->nAnzahl / $oBasePosition->nAnzahl;
                    }

                }
            }
            unset($oPosition);

            $smarty->assign('fAnzahl', $oBasePosition->nAnzahl)
                   ->assign('kEditKonfig', $kKonfig)
                   ->assign('nKonfigitem_arr', $nKonfigitem_arr)
                   ->assign('nKonfigitemAnzahl_arr', $nKonfigitemAnzahl_arr)
                   ->assign('nKonfiggruppeAnzahl_arr', $nKonfiggruppeAnzahl_arr);
        }
        if (isset($oBasePosition->WarenkorbPosEigenschaftArr)) {
            $oEigenschaftWertEdit_arr = [];
            foreach ($oBasePosition->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                $oEigenschaftWertEdit_arr[$oWarenkorbPosEigenschaft->kEigenschaft] = (object)[
                    'kEigenschaft'                  => $oWarenkorbPosEigenschaft->kEigenschaft,
                    'kEigenschaftWert'              => $oWarenkorbPosEigenschaft->kEigenschaftWert,
                    'cEigenschaftWertNameLocalized' => $oWarenkorbPosEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']],
                ];
            }

            if (count($oEigenschaftWertEdit_arr) > 0) {
                $smarty->assign('oEigenschaftWertEdit_arr', $oEigenschaftWertEdit_arr);
            }
        }
    }
}

/**
 * @param array $hookInfos
 * @param bool  $forceExit
 * @return array
 */
function urlNotFoundRedirect(array $hookInfos = null, $forceExit = false)
{
    $url         = $_SERVER['REQUEST_URI'];
    $redirect    = new Redirect();
    $redirectUrl = $redirect->test($url);
    if ($redirectUrl !== false && $redirectUrl !== $url && '/' . $redirectUrl !== $url) {
        $cUrl_arr = parse_url($redirectUrl);
        if (!array_key_exists('scheme', $cUrl_arr)) {
            $redirectUrl = strpos($redirectUrl, '/') === 0
                ? Shop::getURL() . $redirectUrl
                : Shop::getURL() . '/' . $redirectUrl;
        }
        http_response_code(301);
        header('Location: ' . $redirectUrl);
        exit;
    }
    http_response_code(404);

    if ($forceExit || !$redirect->isValid($url)) {
        exit;
    }
    $isFileNotFound = true;
    executeHook(HOOK_PAGE_NOT_FOUND_PRE_INCLUDE, [
        'isFileNotFound'  => &$isFileNotFound,
        $hookInfos['key'] => &$hookInfos['value']
    ]);
    $hookInfos['isFileNotFound'] = $isFileNotFound;

    return $hookInfos;
}

/**
 * @param int $minDeliveryDays
 * @param int $maxDeliveryDays
 * @return mixed
 */
function getDeliverytimeEstimationText($minDeliveryDays, $maxDeliveryDays)
{
    $deliveryText = ($minDeliveryDays === $maxDeliveryDays) ? str_replace(
        '#DELIVERYDAYS#', $minDeliveryDays, Shop::Lang()->get('deliverytimeEstimationSimple')
    ) : str_replace(
        ['#MINDELIVERYDAYS#', '#MAXDELIVERYDAYS#'],
        [$minDeliveryDays, $maxDeliveryDays],
        Shop::Lang()->get('deliverytimeEstimation')
    );

    executeHook(HOOK_GET_DELIVERY_TIME_ESTIMATION_TEXT, [
        'min'  => $minDeliveryDays,
        'max'  => $maxDeliveryDays,
        'text' => &$deliveryText
    ]);

    return $deliveryText;
}

/**
 * Prüft ob reCaptcha mit private und public key konfiguriert ist
 *
 * @return bool
 */
function reCaptchaConfigured()
{
    $settings = Shop::getSettings([CONF_GLOBAL]);

    return !empty($settings['global']['global_google_recaptcha_private'])
        && !empty($settings['global']['global_google_recaptcha_public']);
}

/**
 * @param string $response
 * @return bool
 */
function validateReCaptcha($response)
{
    $settings = Shop::getSettings([CONF_GLOBAL]);
    $secret   = $settings['global']['global_google_recaptcha_private'];
    $url      = 'https://www.google.com/recaptcha/api/siteverify';
    if (empty($secret)) {
        return true;
    }

    $json = http_get_contents($url, 30, [
        'secret'   => $secret,
        'response' => $response,
        'remoteip' => getRealIp()
    ]);

    if (is_string($json)) {
        $result = json_decode($json);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $_SESSION['bAnti_spam_already_checked'] = (isset($result->success) && $result->success);
        }
    }

    return false;
}

/**
 * @param array $requestData
 * @return bool
 */
function validateCaptcha(array $requestData)
{
    $confGlobal = Shop::getSettings([CONF_GLOBAL]);
    $reCaptcha  = reCaptchaConfigured();
    $valid      = false;

    // Captcha Prüfung ist bei eingeloggtem Kunden, bei bereits erfolgter Prüfung
    // oder ausgeschaltetem Captcha nicht notwendig
    if ((isset($_SESSION['bAnti_spam_already_checked']) && $_SESSION['bAnti_spam_already_checked'] === true)
        || $confGlobal['global']['anti_spam_method'] === 'N'
        || Session::Customer()->isLoggedIn()
    ) {
        return true;
    }

    // Captcha Prüfung für reCaptcha ist nicht möglich, wenn keine Konfiguration hinterlegt ist
    if (!$reCaptcha && (int)$confGlobal['global']['anti_spam_method'] === 7) {
        return true;
    }

    // Wenn reCaptcha konfiguriert ist, wird davon ausgegangen, dass reCaptcha verwendet wird, egal was in
    // $confGlobal['global']['anti_spam_method'] angegeben ist.
    if ($reCaptcha) {
        $valid = validateReCaptcha($requestData['g-recaptcha-response']);
    } elseif ((int)$confGlobal['global']['anti_spam_method'] === 5) {
        $valid = validToken();
    } elseif (isset($requestData['captcha'], $requestData['md5'])) {
        $valid = $requestData['md5'] === md5(PFAD_ROOT . $requestData['captcha']);
    }

    if ($valid) {
        $_SESSION['bAnti_spam_already_checked'] = true;
    }

    return $valid;
}

/**
 * @return int
 */
function getDefaultLanguageID()
{
    $languageID = Shop::getLanguageID();
    if ($languageID === 0) {
        $oSpracheTMP = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        if ($oSpracheTMP !== null && $oSpracheTMP->kSprache > 0) {
            $languageID = $oSpracheTMP->kSprache;
        }
    }

    return (int)$languageID;
}

/**
 * creates an csrf token
 *
 * @return string
 * @throws Exception
 */
function generateCSRFToken()
{
    return Shop::Container()->getCryptoService()->randomString(32);
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
        $_SESSION['jtl_token'] = generateCSRFToken();
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
 * @return bool
 */
function isAjaxRequest()
{
    return isset($_REQUEST['isAjax'])
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

/**
 * @param string $filename
 * @return string delimiter guess
 */
function guessCsvDelimiter($filename)
{
    $file      = fopen($filename, 'r');
    $firstLine = fgets($file);

    foreach ([';', ',', '|', '\t'] as $delim) {
        if (strpos($firstLine, $delim) !== false) {
            fclose($file);

            return $delim;
        }
    }
    fclose($file);

    return ';';
}

/**
 * return trimmed description without (double) line breaks
 *
 * @param string $cDesc
 * @return string
 */
function truncateMetaDescription($cDesc)
{
    $conf      = Shop::getSettings([CONF_METAANGABEN]);
    $maxLength = !empty($conf['metaangaben']['global_meta_maxlaenge_description'])
        ? (int)$conf['metaangaben']['global_meta_maxlaenge_description']
        : 0;

    return prepareMeta($cDesc, null, $maxLength);
}

/**
 * @deprecated since 4.0
 * @return int
 */
function gibSeitenTyp()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getPageType();
}

/**
 * @deprecated since 4.0
 * @param string $cString
 * @param int    $nSuche
 * @return mixed|string
 */
function filterXSS($cString, $nSuche = 0)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return StringHandler::filterXSS($cString, $nSuche);
}

/**
 * @deprecated since 4.0
 * @param bool $bForceSSL
 * @return string
 */
function gibShopURL($bForceSSL = false)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::getURL($bForceSSL);
}

/**
 * @deprecated since 4.0 - use Jtllog::writeLog() insted
 * @param string $logfile
 * @param string $entry
 * @param int    $level
 * @return bool
 */
function writeLog($logfile, $entry, $level)
{
    if (ES_LOGGING > 0 && ES_LOGGING >= $level) {
        $logfile = fopen($logfile, 'a');
        if (!$logfile) {
            return false;
        }
        fwrite($logfile, "\n[" . date('m.d.y H:i:s') . "] [" . gibIP() . "]\n" . $entry);
        fclose($logfile);
    }

    return true;
}

/**
 * https? wenn erwünscht reload mit https
 *
 * @return bool
 * @deprecated since 4.06
 */
function pruefeHttps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @deprecated since 4.06
 */
function loeseHttps()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @return array
 * @deprecated since 5.0
 */
function holePreisanzeigeEinstellungen()
{
    trigger_error(__FUNCTION__ . ' is deprecated and does not return correct values anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @deprecated since 5.0
 */
function checkeWarenkorbEingang()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    WarenkorbHelper::checkAdditions();
}

/**
 * @param Artikel|object $Artikel
 * @param int            $anzahl
 * @param array          $oEigenschaftwerte_arr
 * @param int            $precision
 * @return array
 * @deprecated since 5.0
 */
function pruefeFuegeEinInWarenkorb($Artikel, $anzahl, $oEigenschaftwerte_arr, $precision = 2)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return WarenkorbHelper::addToCartCheck($Artikel, $anzahl, $oEigenschaftwerte_arr, $precision);
}

/**
 * @param string         $lieferland
 * @param string         $versandklassen
 * @param int            $kKundengruppe
 * @param Artikel|object $oArtikel
 * @param bool           $checkProductDepedency
 * @return mixed
 * @deprecated since 5.0
 */
function gibGuenstigsteVersandart($lieferland, $versandklassen, $kKundengruppe, $oArtikel, $checkProductDepedency = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return VersandartHelper::getFavourableShippingMethod($lieferland, $versandklassen, $kKundengruppe, $oArtikel, $checkProductDepedency);
}

/**
 * Gibt von einem Artikel mit normalen Variationen, ein Array aller ausverkauften Variationen zurück
 *
 * @param int          $kArtikel
 * @param null|Artikel $oArtikel
 * @return array
 * @deprecated since 5.0 - not used in core
 */
function pruefeVariationAusverkauft($kArtikel = 0, $oArtikel = null)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if ((int)$kArtikel > 0) {
        $oArtikel = (new Artikel())->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
    }

    $oVariationsAusverkauft_arr = [];
    if ($oArtikel !== null
        && $oArtikel->kEigenschaftKombi === 0
        && $oArtikel->nIstVater === 0
        && $oArtikel->Variationen !== null
        && count($oArtikel->Variationen) > 0
    ) {
        foreach ($oArtikel->Variationen as $oVariation) {
            if (!isset($oVariation->Werte) || count($oVariation->Werte) === 0) {
                continue;
            }
            foreach ($oVariation->Werte as $oVariationWert) {
                // Ist Variation ausverkauft?
                if ($oVariationWert->fLagerbestand <= 0) {
                    $oVariationWert->cNameEigenschaft                      = $oVariation->cName;
                    $oVariationsAusverkauft_arr[$oVariation->kEigenschaft] = $oVariationWert;
                }
            }
        }
    }

    return $oVariationsAusverkauft_arr;
}

/**
 * Sortiert ein Array von Objekten anhand von einem bestimmten Member vom Objekt
 * z.B. sortiereFilter($NaviFilter->MerkmalFilter, "kMerkmalWert");
 *
 * @param array $oFilter_arr
 * @param string $cKey
 * @return array
 * @deprecated since 5.0 - not used in core
 */
function sortiereFilter($oFilter_arr, $cKey)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $kKey_arr        = [];
    $oFilterSort_arr = [];

    if (is_array($oFilter_arr) && count($oFilter_arr) > 0) {
        foreach ($oFilter_arr as $oFilter) {
            // Baue das Array mit Keys auf, die sortiert werden sollen
            $kKey_arr[] = (int)$oFilter->$cKey;
        }
        // Sortiere das Array
        sort($kKey_arr, SORT_NUMERIC);
        foreach ($kKey_arr as $kKey) {
            foreach ($oFilter_arr as $oFilter) {
                if ((int)$oFilter->$cKey === $kKey) {
                    // Baue das Array auf, welches sortiert zurueckgegeben wird
                    $oFilterSort_arr[] = $oFilter;
                    break;
                }
            }
        }
    }

    return $oFilterSort_arr;
}

/**
 * Holt die Globalen Metaangaben und Return diese als Assoc Array wobei die Keys => kSprache sind
 *
 * @return array|mixed
 * @deprecated since 5.0
 */
function holeGlobaleMetaAngaben()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Filter\Metadata::getGlobalMetaData();
}

/**
 * @return array
 * @deprecated since 5.0
 */
function holeExcludedKeywords()
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return \Filter\Metadata::getExcludes();
}

/**
 * Erhält einen String aus dem alle nicht erlaubten Wörter rausgefiltert werden
 *
 * @param string $cString
 * @param array  $oExcludesKeywords_arr
 * @return string
 * @deprecated since 5.0
 */
function gibExcludesKeywordsReplace($cString, $oExcludesKeywords_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    if (is_array($oExcludesKeywords_arr) && count($oExcludesKeywords_arr) > 0) {
        foreach ($oExcludesKeywords_arr as $i => $oExcludesKeywords) {
            $oExcludesKeywords_arr[$i] = ' ' . $oExcludesKeywords . ' ';
        }

        return str_replace($oExcludesKeywords_arr, ' ', $cString);
    }

    return $cString;
}


/**
 * @param float $fSumme
 * @return string
 * @deprecated since 5.0 - not used in core
 */
function formatCurrency($fSumme)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $fSumme    = (float)$fSumme;
    $fSummeABS = null;
    $fCents    = null;
    if ($fSumme > 0) {
        $fSummeABS = abs($fSumme);
        $fSumme    = floor($fSumme * 100);
        $fCents    = $fSumme % 100;
        $fSumme    = (string)floor($fSumme / 100);
        if ($fCents < 10) {
            $fCents = '0' . $fCents;
        }
        for ($i = 0; $i < floor((strlen($fSumme) - (1 + $i)) / 3); $i++) {
            $fSumme = substr($fSumme, 0, strlen($fSumme) - (4 * $i + 3)) . '.' .
                substr($fSumme, 0, strlen($fSumme) - (4 * $i + 3));
        }
    }

    return (($fSummeABS ? '' : '-') . $fSumme . ',' . $fCents);
}

/**
 * Mapped die Suchspecial Einstellungen und liefert die Einstellungswerte als Assoc Array zurück.
 * Das Array kann via kKey Assoc angesprochen werden.
 *
 * @param array $oSuchspecialEinstellung_arr
 * @return array
 * @deprecated since 5.0
 */
function gibSuchspecialEinstellungMapping(array $oSuchspecialEinstellung_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $oEinstellungen_arr = [];
    foreach ($oSuchspecialEinstellung_arr as $key => $oSuchspecialEinstellung) {
        switch ($key) {
            case 'suchspecials_sortierung_bestseller':
                $oEinstellungen_arr[SEARCHSPECIALS_BESTSELLER] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_sonderangebote':
                $oEinstellungen_arr[SEARCHSPECIALS_SPECIALOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_neuimsortiment':
                $oEinstellungen_arr[SEARCHSPECIALS_NEWPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topangebote':
                $oEinstellungen_arr[SEARCHSPECIALS_TOPOFFERS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_inkuerzeverfuegbar':
                $oEinstellungen_arr[SEARCHSPECIALS_UPCOMINGPRODUCTS] = $oSuchspecialEinstellung;
                break;
            case 'suchspecials_sortierung_topbewertet':
                $oEinstellungen_arr[SEARCHSPECIALS_TOPREVIEWS] = $oSuchspecialEinstellung;
                break;
        }
    }

    return $oEinstellungen_arr;
}

/**
 * @param int $nSeitentyp
 * @return string
 * @deprecated since 5.0 - not used in core
 */
function mappeSeitentyp($nSeitentyp)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    switch ((int)$nSeitentyp) {
        case PAGE_ARTIKEL:
            return 'Artikeldetails';

        case PAGE_ARTIKELLISTE:
            return 'Artikelliste';

        case PAGE_WARENKORB:
            return 'Warenkorb';

        case PAGE_MEINKONTO:
            return 'Mein Konto';

        case PAGE_KONTAKT:
            return 'Kontakt';

        case PAGE_UMFRAGE:
            return 'Umfrage';

        case PAGE_NEWS:
            return 'News';

        case PAGE_NEWSLETTER:
            return 'Newsletter';

        case PAGE_LOGIN:
            return 'Login';

        case PAGE_REGISTRIERUNG:
            return 'Registrierung';

        case PAGE_BESTELLVORGANG:
            return 'Bestellvorgang';

        case PAGE_BEWERTUNG:
            return 'Bewertung';

        case PAGE_DRUCKANSICHT:
            return 'Druckansicht';

        case PAGE_PASSWORTVERGESSEN:
            return 'Passwort vergessen';

        case PAGE_WARTUNG:
            return 'Wartung';

        case PAGE_WUNSCHLISTE:
            return 'Wunschliste';

        case PAGE_VERGLEICHSLISTE:
            return 'Vergleichsliste';

        case PAGE_STARTSEITE:
            return 'Startseite';

        case PAGE_VERSAND:
            return 'Versand';

        case PAGE_AGB:
            return 'AGB';

        case PAGE_DATENSCHUTZ:
            return 'Datenschutz';

        case PAGE_TAGGING:
            return 'Tagging';

        case PAGE_LIVESUCHE:
            return 'Livesuche';

        case PAGE_HERSTELLER:
            return 'Hersteller';

        case PAGE_SITEMAP:
            return 'Sitemap';

        case PAGE_GRATISGESCHENK:
            return 'Gratis Geschenk ';

        case PAGE_WRB:
            return 'WRB';

        case PAGE_PLUGIN:
            return 'Plugin';

        case PAGE_NEWSLETTERARCHIV:
            return 'Newsletterarchiv';

        case PAGE_EIGENE:
            return 'Eigene Seite';

        case PAGE_UNBEKANNT:
        default:
            return 'Unbekannt';
    }
}

/**
 * @param bool $cache
 * @return int
 * @deprecated since 5.0
 */
function getSytemlogFlag($cache = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    return Jtllog::getSytemlogFlag($cache);
}

/**
 * @param object $startKat
 * @param object $AufgeklappteKategorien
 * @param object $AktuelleKategorie
 * @deprecated since 5.0
 */
function baueKategorieListenHTML($startKat, $AufgeklappteKategorien, $AktuelleKategorie)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    KategorieHelper::buildCategoryListHTML($startKat, $AktuelleKategorie, $AktuelleKategorie);
}

/**
 * @param Kategorie $AktuelleKategorie
 * @deprecated since 5.0
 */
function baueUnterkategorieListeHTML($AktuelleKategorie)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    KategorieHelper::getSubcategoryList($AktuelleKategorie);
}

/**
 * @param Kategorie $Kategorie
 * @param int       $kKundengruppe
 * @param int       $kSprache
 * @param bool      $bString
 * @return array|string
 * @deprecated since 5.0
 */
function gibKategoriepfad($Kategorie, $kKundengruppe, $kSprache, $bString = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $helper = KategorieHelper::getInstance($kSprache, $kKundengruppe);

    return $helper->getPath($Kategorie, $bString);
}

/**
 * @return string
 * @deprecated since 5.0
 */
function gibLagerfilter()
{
    return Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL();
}

/**
 * @param array $variBoxAnzahl_arr
 * @return bool
 * @deprecated since 5.0
 */
function pruefeVariBoxAnzahl($variBoxAnzahl_arr)
{
    return WarenkorbHelper::checkVariboxAmount($variBoxAnzahl_arr);
}

/**
 * @param string $cPfad
 * @return string
 * @deprecated since 5.0 - not used in core anymore
 */
function gibArtikelBildPfad($cPfad)
{
    return strlen(trim($cPfad)) > 0
        ? $cPfad
        : BILD_KEIN_ARTIKELBILD_VORHANDEN;
}

/**
 * @param int $nKategorieBox
 * @return array
 * @deprecated since 5.0 - not used in core anymore
 */
function gibAlleKategorienNoHTML($nKategorieBox = 0)
{
    $oKategorienNoHTML_arr = [];
    $nTiefe                = 0;

    if (K_KATEGORIE_TIEFE <= 0) {
        return [];
    }
    $oKategorien = new KategorieListe();
    $oKategorien->getAllCategoriesOnLevel(0);
    foreach ($oKategorien->elemente as $oKategorie) {
        //Kategoriebox Filter
        if ($nKategorieBox > 0
            && $nTiefe === 0
            && $oKategorie->CategoryFunctionAttributes[KAT_ATTRIBUT_KATEGORIEBOX] != $nKategorieBox
        ) {
            continue;
        }
        unset($oKategorienNoHTML);
        $oKategorienNoHTML = $oKategorie;
        unset($oKategorienNoHTML->Unterkategorien);
        $oKategorienNoHTML->oUnterKat_arr               = [];
        $oKategorienNoHTML_arr[$oKategorie->kKategorie] = $oKategorienNoHTML;
        //nur wenn unterkategorien enthalten sind!
        if (K_KATEGORIE_TIEFE < 2) {
            continue;
        }
        $oAktKategorie = new Kategorie($oKategorie->kKategorie);
        if ($oAktKategorie->bUnterKategorien) {
            $nTiefe           = 1;
            $oUnterKategorien = new KategorieListe();
            $oUnterKategorien->getAllCategoriesOnLevel($oAktKategorie->kKategorie);
            foreach ($oUnterKategorien->elemente as $oUKategorie) {
                unset($oKategorienNoHTML);
                $oKategorienNoHTML = $oUKategorie;
                unset($oKategorienNoHTML->Unterkategorien);
                $oKategorienNoHTML->oUnterKat_arr                                                        = [];
                $oKategorienNoHTML_arr[$oKategorie->kKategorie]->oUnterKat_arr[$oUKategorie->kKategorie] = $oKategorienNoHTML;

                if (K_KATEGORIE_TIEFE < 3) {
                    continue;
                }
                $nTiefe                = 2;
                $oUnterUnterKategorien = new KategorieListe();
                $oUnterUnterKategorien->getAllCategoriesOnLevel($oUKategorie->kKategorie);
                foreach ($oUnterUnterKategorien->elemente as $oUUKategorie) {
                    unset($oKategorienNoHTML);
                    $oKategorienNoHTML = $oUUKategorie;
                    unset($oKategorienNoHTML->Unterkategorien);
                    $oKategorienNoHTML_arr[$oKategorie->kKategorie]->oUnterKat_arr[$oUKategorie->kKategorie]->oUnterKat_arr[$oUUKategorie->kKategorie] = $oKategorienNoHTML;
                }
            }
        }
    }

    return $oKategorienNoHTML_arr;
}

/**
 * @param int $size
 * @param string $format
 * @return string
 */
function formatSize($size, $format = '%.2f')
{
    $units = ['b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
    $res   = '';
    foreach ($units as $n => $unit) {
        $div = 1024 ** $n;
        if ($size > $div) {
            $res = sprintf("$format %s", $size / $div, $unit);
        }
    }

    return $res;
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

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed
     * @return void
     */
    function dd()
    {
        array_map(function ($var) {
            dump($var);
        }, func_get_args());
        die(1);
    }
}

if (!function_exists('array_flatten')) {
    /**
     * @param array $array
     * @return array|bool
     */
    function array_flatten($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

/**
 * @param array $fehlendeAngaben
 * @return int
 */
function eingabenKorrekt($fehlendeAngaben)
{
    foreach ($fehlendeAngaben as $angabe) {
        if ($angabe > 0) {
            return 0;
        }
    }

    return 1;
}
