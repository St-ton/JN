<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/tools.Global.deprecations.php';

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
    $linkHelper    = Shop::Container()->getLinkService();
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
            $oLink             = $kLink > 0 ? $linkHelper->getLinkByID($kLink) : null;
            $elems             = $oLink !== null
                ? $linkHelper->getParentLinks($oLink->getID())->map(function (\Link\LinkInterface $link) {
                    $res           = new stdClass();
                    $res->name     = $link->getName();
                    $res->url      = $link->getURL();
                    $res->urlFull  = $link->getURL();
                    $res->hasChild = false;

                    return $res;
                })->reverse()->all()
                : [];

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
 * @param string $cISO
 * @return string
 */
function ISO2land($cISO)
{
    if (strlen($cISO) > 2) {
        return $cISO;
    }
    if (!isset($_SESSION['cISOSprache'])) {
        $oSprache                = Sprache::getDefaultLanguage(true);
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
    if ($obj instanceof \Link\LinkInterface) {
        return $obj->getURL();
    }
    $lang   = !Sprache::isDefaultLanguageActive(true)
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
                    $seoobj = Shop::Container()->getDB()->queryPrepared(
                        "SELECT tseo.cSeo
                            FROM tartikelsprache
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikelsprache.kArtikel
                                AND tseo.kSprache = :lid
                            WHERE tartikelsprache.kArtikel = :aid
                            AND tartikelsprache.kSprache = :lid",
                        [
                            'lid' => (int)$Sprache->kSprache,
                            'aid' => (int)$obj->kArtikel
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                } else {
                    $seoobj = Shop::Container()->getDB()->queryPrepared(
                        "SELECT tseo.cSeo
                            FROM tartikel
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikel.kArtikel
                                AND tseo.kSprache = :lid
                            WHERE tartikel.kArtikel = :aid",
                        [
                            'lid' => (int)$Sprache->kSprache,
                            'aid' => (int)$obj->kArtikel
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                }
                $url = (isset($seoobj->cSeo) && $seoobj->cSeo)
                    ? $seoobj->cSeo
                    : '?a=' . $obj->kArtikel . '&amp;lang=' . $Sprache->cISO;
                break;

            case URLART_KATEGORIE:
                if ($Sprache->cStandard !== 'Y') {
                    $seoobj = Shop::Container()->getDB()->queryPrepared(
                        "SELECT tseo.cSeo
                            FROM tkategoriesprache
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kKategorie'
                                AND tseo.kKey = tkategoriesprache.kKategorie
                                AND tseo.kSprache = :lid
                                WHERE tkategoriesprache.kKategorie = :cid
                            AND tkategoriesprache.kSprache = :lid",
                        [
                            'lid' => (int)$Sprache->kSprache,
                            'cid' => (int)$obj->kKategorie
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                } else {
                    $seoobj = Shop::Container()->getDB()->queryPrepared(
                        "SELECT tseo.cSeo
                            FROM tkategorie
                            LEFT JOIN tseo
                                ON tseo.cKey = 'kKategorie'
                                AND tseo.kKey = tkategorie.kKategorie
                                AND tseo.kSprache = :lid
                            WHERE tkategorie.kKategorie = :cid",
                        [
                            'lid' => (int)$Sprache->kSprache,
                            'cid' => (int)$obj->kKategorie
                        ],
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                }
                $url = $seoobj->cSeo ?? '?k=' . $obj->kKategorie . '&amp;lang=' . $Sprache->cISO;
                break;

            case URLART_SEITE:
                //@deprecated since 4.05 - this is now done within the link helper
                $seoobj = Shop::Container()->getDB()->queryPrepared(
                    "SELECT tseo.cSeo
                        FROM tlinksprache
                        LEFT JOIN tseo
                            ON tseo.cKey = 'kLink'
                            AND tseo.kKey = tlinksprache.kLink
                            AND tseo.kSprache = :lid
                        WHERE tlinksprache.kLink = :lnkid
                            AND tlinksprache.cISOSprache = :ciso",
                    [
                        'lid'   => (int)$Sprache->kSprache,
                        'lnkid' => (int)$obj->kLink,
                        'ciso'  => $Sprache->cISO
                    ],
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
        $Sprachen   = Sprache::getAllLanguages();
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
        if (!$bSpracheDa) { //lang mitgegeben, aber nicht mehr in db vorhanden -> alter Sprachlink
            $kArtikel              = RequestHelper::verifyGPCDataInt('a');
            $kKategorie            = RequestHelper::verifyGPCDataInt('k');
            $kSeite                = RequestHelper::verifyGPCDataInt('s');
            $kVariKindArtikel      = RequestHelper::verifyGPCDataInt('a2');
            $kHersteller           = RequestHelper::verifyGPCDataInt('h');
            $kSuchanfrage          = RequestHelper::verifyGPCDataInt('l');
            $kMerkmalWert          = RequestHelper::verifyGPCDataInt('m');
            $kTag                  = RequestHelper::verifyGPCDataInt('t');
            $kSuchspecial          = RequestHelper::verifyGPCDataInt('q');
            $kNews                 = RequestHelper::verifyGPCDataInt('n');
            $kNewsMonatsUebersicht = RequestHelper::verifyGPCDataInt('nm');
            $kNewsKategorie        = RequestHelper::verifyGPCDataInt('nk');
            $kUmfrage              = RequestHelper::verifyGPCDataInt('u');
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

    $waehrung = RequestHelper::verifyGPDataString('curr');
    if ($waehrung) {
        $Waehrungen = Shop::Container()->getDB()->query(
            "SELECT cISO, kWaehrung
                FROM twaehrung",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $cart       = Session::Cart();
        foreach ($Waehrungen as $Waehrung) {
            if ($Waehrung->cISO === $waehrung) {
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
 * @return \Link\LinkGroupCollection
 */
function setzeLinks()
{
    $linkGroups = Shop::Container()->getLinkService()->getLinkGroups();
    $_SESSION['Link_Datenschutz']  = $linkGroups->Link_Datenschutz;
    $_SESSION['Link_AGB']          = $linkGroups->Link_AGB;
    $_SESSION['Link_Versandseite'] = $linkGroups->Link_Versandseite;

    return $linkGroups;
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
                if (RequestHelper::verifyGPCDataInt('sf' . $i) > 0) {
                    $filter[] = RequestHelper::verifyGPCDataInt('sf' . $i);
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
                if (RequestHelper::verifyGPCDataInt('tf' . $i) > 0) {
                    $filter[] = RequestHelper::verifyGPCDataInt('tf' . $i);
                }
                ++$i;
            }
        }
    }

    return $filter;
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
 * @param int $kSprache
 * @param int $kKundengruppe
 * @return object|bool
 */
function gibAGBWRB($kSprache, $kKundengruppe)
{
    if ($kSprache <= 0 || $kKundengruppe <= 0) {
        return false;
    }
    $oLinkAGB   = null;
    $oLinkWRB   = null;
    // kLink für AGB und WRB suchen
    foreach (Shop::Container()->getLinkService()->getSpecialPages() as $sp) {
        /** @var \Link\LinkInterface $sp */
        if ($sp->getLinkType() === LINKTYP_AGB) {
            $oLinkAGB = $sp;
        } elseif ($sp->getLinkType() === LINKTYP_WRB) {
            $oLinkWRB = $sp;
        }
    }
    $oAGBWRB = Shop::Container()->getDB()->select(
        'ttext',
        'kKundengruppe', (int)$kKundengruppe,
        'kSprache', (int)$kSprache
    );
    if (!empty($oAGBWRB->kText)) {
        $oAGBWRB->cURLAGB  = $oLinkAGB->getURL() ?? '';
        $oAGBWRB->cURLWRB  = $oLinkWRB->getURL() ?? '';
        $oAGBWRB->kLinkAGB = $oLinkAGB !== null
            ? $oLinkAGB->getID()
            : 0;
        $oAGBWRB->kLinkWRB = $oLinkWRB !== null
            ? $oLinkWRB->getID()
            : 0;

        return $oAGBWRB;
    }
    $oAGBWRB = Shop::Container()->getDB()->select('ttext', 'nStandard', 1);
    if (!empty($oAGBWRB->kText)) {
        $oAGBWRB->cURLAGB  = $oLinkAGB !== null ? $oLinkAGB->getURL() : '';
        $oAGBWRB->cURLWRB  = $oAGBWRB !== null ? $oLinkAGB->getURL() : '';
        $oAGBWRB->kLinkAGB = $oLinkAGB !== null ? $oLinkAGB->getID() : 0;
        $oAGBWRB->kLinkWRB = $oAGBWRB !== null ? $oAGBWRB->getID() : 0;

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
        $oSprache = Sprache::getDefaultLanguage(true);
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
function baueSuchSpecialURL(int $kKey)
{
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
 *
 */
function setzeSpracheUndWaehrungLink()
{
    global $oZusatzFilter, $sprachURL, $AktuellerArtikel, $kSeite, $kLink, $AktuelleSeite;
    $shopURL    = Shop::getURL() . '/';
    $helper     = Shop::Container()->getLinkService();
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
function pruefeEmailblacklist(string $cEmail)
{
    $cEmail = strtolower(StringHandler::filterXSS($cEmail));
    if (StringHandler::filterEmailAddress($cEmail) === false) {
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
        $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32)();
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
    foreach ($fehlendeAngaben as $angabe) {
        if ($angabe > 0) {
            return 0;
        }
    }

    return 1;
}
