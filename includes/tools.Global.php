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
 * @param string $langISO
 */
function checkeSpracheWaehrung($langISO = '')
{
    /** @var array('Vergleichsliste' => Vergleichsliste,'Warenkorb' => Warenkorb) $_SESSION */
    if (strlen($langISO) > 0) {
        //Kategorien zurücksetzen, da sie lokalisiert abgelegt wurden
        if ($langISO !== Shop::getLanguageCode()) {
            $_SESSION['oKategorie_arr']     = [];
            $_SESSION['oKategorie_arr_new'] = [];
        }
        $lang = \Functional\first(Sprache::getAllLanguages(), function ($l) use ($langISO) {
            return $l->cISO === $langISO;
        });
        if ($lang !== null) {
            $_SESSION['cISOSprache'] = $lang->cISO;
            $_SESSION['kSprache']    = (int)$lang->kSprache;
            Shop::setLanguage($lang->kSprache, $lang->cISO);
            unset($_SESSION['Suche']);
            setzeLinks();
            if (isset($_SESSION['Wunschliste'])) {
                Session::WishList()->umgebungsWechsel();
            }
            if (isset($_SESSION['Vergleichsliste'])) {
                Session::CompareList()->umgebungsWechsel();
            }
            $_SESSION['currentLanguage'] = clone $lang;
            unset($_SESSION['currentLanguage']->cURL);
        } else {
            // lang mitgegeben, aber nicht mehr in db vorhanden -> alter Sprachlink
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

    $currencyCode = RequestHelper::verifyGPDataString('curr');
    if ($currencyCode) {
        $cart     = \Session\Session::Cart();
        $currency = \Functional\first(\Session\Session::Currencies(), function (Currency $c) use ($currencyCode) {
            return $c->getCode() === $currencyCode;
        });
        if ($currency !== null) {
            $_SESSION['Waehrung']      = $currency;
            $_SESSION['cWaehrungName'] = $currency->getName();
            if (isset($_SESSION['Wunschliste'])) {
                \Session\Session::WishList()->umgebungsWechsel();
            }
            if (isset($_SESSION['Vergleichsliste'])) {
                \Session\Session::CompareList()->umgebungsWechsel();
            }
            unset($_SESSION['TrustedShops']);
            if ($cart !== null) {
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);
                if (count($cart->PositionenArr) > 0) {
                    $cart->setzePositionsPreise();
                }
            }
        }
    }
    Shop::Lang()->autoload();
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
    foreach ($fehlendeAngaben as $angabe) {
        if ($angabe > 0) {
            return 0;
        }
    }

    return 1;
}
