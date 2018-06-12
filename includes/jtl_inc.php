<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
 * wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt. Nach dem erfolgreichen einloggen,
 * wird die zuvor angestrebte Aktion durchgeführt.
 *
 * @param int $cRedirect
 * @return stdClass
 */
function gibRedirect($cRedirect)
{
    $oRedirect = new stdClass();

    switch ($cRedirect) {
        case R_LOGIN_WUNSCHLISTE:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'a';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('a');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'n';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('n');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'Wunschliste';
            $oTMP->Wert                  = 1;
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_WUNSCHLISTE;
            $oRedirect->cURL             = Shop::Container()->getLinkService()->getStaticRoute('wunschliste.php', false);
            $oRedirect->cName            = Shop::Lang()->get('wishlist', 'redirect');
            break;
        case R_LOGIN_BEWERTUNG:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'a';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('a');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'bfa';
            $oTMP->Wert                  = 1;
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_BEWERTUNG;
            $oRedirect->cURL             = 'bewertung.php?a=' . RequestHelper::verifyGPCDataInt('a') . '&bfa=1';
            $oRedirect->cName            = Shop::Lang()->get('review', 'redirect');
            break;
        case R_LOGIN_TAG:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'a';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('a');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_TAG;
            $oRedirect->cURL             = '?a=' . RequestHelper::verifyGPCDataInt('a');
            $oRedirect->cName            = Shop::Lang()->get('tag', 'redirect');
            break;
        case R_LOGIN_NEWSCOMMENT:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 's';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('s');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'n';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('n');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_NEWSCOMMENT;
            $oRedirect->cURL             = '?s=' . RequestHelper::verifyGPCDataInt('s') . '&n=' . RequestHelper::verifyGPCDataInt('n');
            $oRedirect->cName            = Shop::Lang()->get('news', 'redirect');
            break;
        case R_LOGIN_UMFRAGE:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 'u';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('u');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_UMFRAGE;
            $oRedirect->cURL             = '?u=' . RequestHelper::verifyGPCDataInt('u');
            $oRedirect->cName            = Shop::Lang()->get('poll', 'redirect');
            break;
        case R_LOGIN_RMA:
            $oRedirect->oParameter_arr   = [];
            $oTMP                        = new stdClass();
            $oTMP->Name                  = 's';
            $oTMP->Wert                  = RequestHelper::verifyGPCDataInt('s');
            $oRedirect->oParameter_arr[] = $oTMP;
            $oRedirect->nRedirect        = R_LOGIN_RMA;
            $oRedirect->cURL             = '?s=' . RequestHelper::verifyGPCDataInt('s');
            $oRedirect->cName            = Shop::Lang()->get('rma', 'redirect');
            break;
        default:
            break;
    }
    executeHook(HOOK_JTL_INC_SWITCH_REDIRECT, ['cRedirect' => &$cRedirect, 'oRedirect' => &$oRedirect]);
    $_SESSION['JTL_REDIRECT'] = $oRedirect;

    return $oRedirect;
}

/**
 * Schaut nach dem Login, ob Kategorien nicht sichtbar sein dürfen und löscht eventuell diese aus der Session
 *
 * @param int $kKundengruppe
 * @return bool
 */
function pruefeKategorieSichtbarkeit($kKundengruppe)
{
    $kKundengruppe = (int)$kKundengruppe;
    if (!$kKundengruppe) {
        return false;
    }
    $cacheID      = 'catlist_p_' . Shop::Cache()->getBaseID(false, false, $kKundengruppe, true, false);
    $save         = false;
    $categoryList = Shop::Cache()->get($cacheID);
    $useCache     = true;
    if ($categoryList === false) {
        $useCache     = false;
        $categoryList = $_SESSION;
    }

    $oKatSichtbarkeit_arr = Shop::Container()->getDB()->selectAll(
        'tkategoriesichtbarkeit',
        'kKundengruppe',
        $kKundengruppe,
        'kKategorie'
    );

    $cKatKey_arr = array_keys($categoryList);
    foreach ($oKatSichtbarkeit_arr as $oKatSichtbarkeit) {
        $visCount = count($_SESSION['kKategorieVonUnterkategorien_arr'][0]);
        for ($i = 0; $i < $visCount; $i++) {
            if ($categoryList['kKategorieVonUnterkategorien_arr'][0][$i] == $oKatSichtbarkeit->kKategorie) {
                unset($categoryList['kKategorieVonUnterkategorien_arr'][0][$i]);
                $save = true;
            }
            $categoryList['kKategorieVonUnterkategorien_arr'][0] = array_merge($categoryList['kKategorieVonUnterkategorien_arr'][0]);
        }

        if (isset($categoryList['kKategorieVonUnterkategorien_arr'][$oKatSichtbarkeit->kKategorie])) {
            unset($categoryList['kKategorieVonUnterkategorien_arr'][$oKatSichtbarkeit->kKategorie]);
            $save = true;
        }
        $ckkCount = count($cKatKey_arr);
        for ($i = 0; $i < $ckkCount; $i++) {
            if (isset($categoryList['oKategorie_arr'][$oKatSichtbarkeit->kKategorie])) {
                unset($categoryList['oKategorie_arr'][$oKatSichtbarkeit->kKategorie]);
                $save = true;
            }
        }
    }
    if ($save === true) {
        if ($useCache === true) {
            //category list has changed - write back changes to cache
            Shop::Cache()->set($cacheID, $categoryList, [CACHING_GROUP_CATEGORY]);
        } else {
            $_SESSION['oKategorie_arr'] = $categoryList;
        }
    }

    return true;
}

/**
 * @param int $kKunde
 * @return bool
 */
function setzeWarenkorbPersInWarenkorb($kKunde)
{
    $kKunde = (int)$kKunde;
    if (!$kKunde) {
        return false;
    }
    $cart = Session::Cart();
    foreach ($cart->PositionenArr as $oWarenkorbPos) {
        if ($oWarenkorbPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $kArtikelGeschenk = (int)$oWarenkorbPos->kArtikel;
            // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
            $oArtikelGeschenk = Shop::Container()->getDB()->query(
                "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand,
                       tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                    JOIN tartikel 
                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
                        AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0) {
                WarenkorbPers::addToCheck(
                    $kArtikelGeschenk,
                    1,
                    [],
                    null,
                    null,
                    C_WARENKORBPOS_TYP_GRATISGESCHENK
                );
            }
        } else {
            WarenkorbPers::addToCheck(
                $oWarenkorbPos->kArtikel,
                $oWarenkorbPos->nAnzahl,
                $oWarenkorbPos->WarenkorbPosEigenschaftArr,
                $oWarenkorbPos->cUnique,
                $oWarenkorbPos->kKonfigitem,
                $oWarenkorbPos->nPosTyp,
                $oWarenkorbPos->cResponsibility
            );
        }
    }
    $cart->PositionenArr = [];

    $oWarenkorbPers = new WarenkorbPers($kKunde);
    foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
        if ($oWarenkorbPersPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
            $kArtikelGeschenk = (int)$oWarenkorbPersPos->kArtikel;
            // Pruefen ob der Artikel wirklich ein Gratis Geschenk ist
            $oArtikelGeschenk = Shop::Container()->getDB()->query(
                "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand,
                       tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                    FROM tartikelattribut
                    JOIN tartikel 
                        ON tartikel.kArtikel = tartikelattribut.kArtikel
                    WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
                        AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                        AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                            $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
                \DB\ReturnType::SINGLE_OBJECT
            );
            if (isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0) {
                if ($oArtikelGeschenk->fLagerbestand <= 0
                    && $oArtikelGeschenk->cLagerKleinerNull === 'N'
                    && $oArtikelGeschenk->cLagerBeachten === 'Y'
                ) {
                    break;
                }
                executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                     ->fuegeEin($kArtikelGeschenk, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
            }
        } else {
            fuegeEinInWarenkorb(
                $oWarenkorbPersPos->kArtikel,
                $oWarenkorbPersPos->fAnzahl,
                $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr,
                1,
                $oWarenkorbPersPos->cUnique,
                $oWarenkorbPersPos->kKonfigitem,
                null,
                true,
                $oWarenkorbPersPos->cResponsibility
            );
        }
    }

    return true;
}

/**
 * Prüfe ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein dürfen
 *
 * @param int $kKundengruppe
 */
function pruefeWarenkorbArtikelSichtbarkeit($kKundengruppe)
{
    $kKundengruppe = (int)$kKundengruppe;
    $cart          = Session::Cart();
    if ($kKundengruppe > 0
        && isset($cart->PositionenArr)
        && count($cart->PositionenArr) > 0
    ) {
        foreach ($cart->PositionenArr as $i => $oPosition) {
            // Wenn die Position ein Artikel ist
            $bKonfig = !empty($oPosition->cUnique);
            if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && !$bKonfig) {
                // Artikelsichtbarkeit prüfen
                $oArtikelSichtbarkeit = Shop::Container()->getDB()->query(
                    "SELECT kArtikel
                        FROM tartikelsichtbarkeit
                        WHERE kArtikel = " . (int)$oPosition->kArtikel . "
                            AND kKundengruppe = " . $kKundengruppe,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oArtikelSichtbarkeit->kArtikel)
                    && $oArtikelSichtbarkeit->kArtikel > 0
                    && (int)$cart->PositionenArr[$i]->kKonfigitem === 0
                ) {
                    unset($cart->PositionenArr[$i]);
                }
                // Auf vorhandenen Preis prüfen
                $oArtikelPreis = Shop::Container()->getDB()->query(
                    "SELECT fVKNetto
                       FROM tpreise
                       WHERE kArtikel = " . (int)$oPosition->kArtikel . "
                           AND kKundengruppe = " . $kKundengruppe,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (!isset($oArtikelPreis->fVKNetto)) {
                    unset($cart->PositionenArr[$i]);
                }
            }
        }
    }
}

/**
 * @param string $userLogin
 * @param string $passLogin
 */
function fuehreLoginAus($userLogin, $passLogin)
{
    global $cHinweis;
    $Kunde    = new Kunde();
    $csrfTest = validateToken();
    if ($csrfTest === false) {
        $cHinweis .= Shop::Lang()->get('csrfValidationFailed');
        Jtllog::writeLog('CSRF-Warnung fuer Login: ' . $_POST['login']);
    } else {
        $cart           = Session::Cart();
        $Einstellungen  = Shop::getSettings([CONF_GLOBAL, CONF_KAUFABWICKLUNG, CONF_KUNDEN]);
        $loginCaptchaOK = $Kunde->verifyLoginCaptcha($_POST);
        if ($loginCaptchaOK === true) {
            $nReturnValue   = $Kunde->holLoginKunde($userLogin, $passLogin);
            $nLoginversuche = $Kunde->nLoginversuche;
        } else {
            $nReturnValue   = 4;
            $nLoginversuche = $loginCaptchaOK;
        }
        if ($Kunde->kKunde > 0) {
            unset($_SESSION['showLoginCaptcha']);
            $oKupons[] = !empty($_SESSION['VersandKupon']) ? $_SESSION['VersandKupon'] : null;
            $oKupons[] = !empty($_SESSION['oVersandfreiKupon']) ? $_SESSION['oVersandfreiKupon'] : null;
            $oKupons[] = !empty($_SESSION['NeukundenKupon']) ? $_SESSION['NeukundenKupon'] : null;
            $oKupons[] = !empty($_SESSION['Kupon']) ? $_SESSION['Kupon'] : null;
            //create new session id to prevent session hijacking
            session_regenerate_id(false);
            //in tbesucher kKunde setzen
            if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
                Shop::Container()->getDB()->update(
                    'tbesucher',
                    'kBesucher',
                    (int)$_SESSION['oBesucher']->kBesucher,
                    (object)['kKunde' => $Kunde->kKunde]
                );
            }
            if ($Kunde->cAktiv === 'Y') {
                unset(
                    $_SESSION['Zahlungsart'],
                    $_SESSION['Versandart'],
                    $_SESSION['Lieferadresse'],
                    $_SESSION['ks'],
                    $_SESSION['VersandKupon'],
                    $_SESSION['NeukundenKupon'],
                    $_SESSION['Kupon'],
                    $_SESSION['kKategorieVonUnterkategorien_arr'],
                    $_SESSION['oKategorie_arr'],
                    $_SESSION['oKategorie_arr_new']
                );
                // Kampagne
                if (isset($_SESSION['Kampagnenbesucher'])) {
                    Kampagne::setCampaignAction(KAMPAGNE_DEF_LOGIN, $Kunde->kKunde, 1.0); // Login
                }
                $session = \Session\Session::getInstance();
                $session->setCustomer($Kunde);
                // Setzt aktuelle Wunschliste (falls vorhanden) vom Kunden in die Session
                setzeWunschlisteInSession();
                // Redirect URL
                $cURL = StringHandler::filterXSS(RequestHelper::verifyGPDataString('cURL'));
                // Lade WarenkorbPers
                $bPersWarenkorbGeladen = false;
                if ($Einstellungen['global']['warenkorbpers_nutzen'] === 'Y'
                    && count($cart->PositionenArr) === 0
                ) {
                    $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                    $oWarenkorbPers->ueberpruefePositionen(true);
                    if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                        foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
                            if (empty($oWarenkorbPers->Artikel->bHasKonfig)) {
                                // Gratisgeschenk in Warenkorb legen
                                if ((int)$oWarenkorbPersPos->nPosTyp === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                                    $kArtikelGeschenk = (int)$oWarenkorbPersPos->kArtikel;
                                    $oArtikelGeschenk = Shop::Container()->getDB()->query(
                                        "SELECT tartikelattribut.kArtikel, tartikel.fLagerbestand, 
                                            tartikel.cLagerKleinerNull, tartikel.cLagerBeachten
                                            FROM tartikelattribut
                                            JOIN tartikel 
                                                ON tartikel.kArtikel = tartikelattribut.kArtikel
                                            WHERE tartikelattribut.kArtikel = " . $kArtikelGeschenk . "
                                                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_GRATISGESCHENK . "'
                                                AND CAST(tartikelattribut.cWert AS DECIMAL) <= " .
                                                    $cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true),
                                        \DB\ReturnType::SINGLE_OBJECT
                                    );
                                    if ((isset($oArtikelGeschenk->kArtikel) && $oArtikelGeschenk->kArtikel > 0)
                                        && ($oArtikelGeschenk->fLagerbestand > 0
                                            || $oArtikelGeschenk->cLagerKleinerNull === 'Y'
                                            || $oArtikelGeschenk->cLagerBeachten === 'N')
                                    ) {
                                        executeHook(HOOK_WARENKORB_PAGE_GRATISGESCHENKEINFUEGEN);
                                        $cart->loescheSpezialPos(C_WARENKORBPOS_TYP_GRATISGESCHENK)
                                             ->fuegeEin($kArtikelGeschenk, 1, [], C_WARENKORBPOS_TYP_GRATISGESCHENK);
                                    }
                                    // Konfigitems ohne Artikelbezug
                                } elseif ($oWarenkorbPersPos->kArtikel === 0 && !empty($oWarenkorbPersPos->kKonfigitem)) {
                                    $oKonfigitem = new Konfigitem($oWarenkorbPersPos->kKonfigitem);
                                    $cart->erstelleSpezialPos(
                                        $oKonfigitem->getName(),
                                        $oWarenkorbPersPos->fAnzahl,
                                        $oKonfigitem->getPreis(),
                                        $oKonfigitem->getSteuerklasse(),
                                        C_WARENKORBPOS_TYP_ARTIKEL,
                                        false,
                                        !Session::CustomerGroup()->isMerchant(),
                                        '',
                                        $oWarenkorbPersPos->cUnique,
                                        $oWarenkorbPersPos->kKonfigitem,
                                        $oWarenkorbPersPos->kArtikel
                                    );
                                //Artikel in den Warenkorb einfügen
                                } else {
                                    fuegeEinInWarenkorb(
                                        $oWarenkorbPersPos->kArtikel,
                                        $oWarenkorbPersPos->fAnzahl,
                                        $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr,
                                        1,
                                        $oWarenkorbPersPos->cUnique,
                                        $oWarenkorbPersPos->kKonfigitem,
                                        null,
                                        false,
                                        $oWarenkorbPersPos->cResponsibility
                                    );
                                }
                            }
                        }
                        $cart->setzePositionsPreise();
                        $bPersWarenkorbGeladen = true;
                    }
                }
                // Pruefe, ob Artikel im Warenkorb vorhanden sind,
                // welche für den aktuellen Kunden nicht mehr sichtbar sein duerfen
                pruefeWarenkorbArtikelSichtbarkeit($_SESSION['Kunde']->kKundengruppe);
                executeHook(HOOK_JTL_PAGE_REDIRECT);
                WarenkorbHelper::checkAdditions();
                if (strlen($cURL) > 0) {
                    if (strpos($cURL, 'http') !== 0) {
                        $cURL = Shop::getURL() . '/' . ltrim($cURL, '/');
                    }
                    header('Location: ' . $cURL, true, 301);
                    exit();
                }
                if (!$bPersWarenkorbGeladen && $Einstellungen['global']['warenkorbpers_nutzen'] === 'Y') {
                    // Existiert ein pers. Warenkorb?
                    // Wenn ja => frag Kunde ob er einen eventuell vorhandenen Warenkorb mergen möchte
                    if ($Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y') {
                        setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
                    } elseif ($Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P') {
                        $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                        if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                            Shop::Smarty()->assign('nWarenkorb2PersMerge', 1);
                        }
                    }
                }
                // Kupons übernehmen, wenn erst der Warenkorb befüllt und sich dann angemeldet wurde
                if (count($oKupons) > 0) {
                    foreach ($oKupons as $Kupon) {
                        if (!empty($Kupon)) {
                            $Kuponfehler  = checkeKupon($Kupon);
                            $nReturnValue = angabenKorrekt($Kuponfehler);
                            executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN_PLAUSI, [
                                'error'        => &$Kuponfehler,
                                'nReturnValue' => &$nReturnValue
                            ]);
                            if ($nReturnValue) {
                                if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === 'standard') {
                                    kuponAnnehmen($Kupon);
                                    executeHook(HOOK_WARENKORB_PAGE_KUPONANNEHMEN);
                                } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === 'versandkupon') {
                                    // Versandfrei Kupon
                                    $_SESSION['oVersandfreiKupon'] = $Kupon;
                                    Shop::Smarty()->assign(
                                        'cVersandfreiKuponLieferlaender_arr',
                                        explode(';', $Kupon->cLieferlaender)
                                    );
                                }
                            } else {
                                Shop::Smarty()->assign('cKuponfehler', $Kuponfehler['ungueltig']);
                            }
                        }
                    }
                }
                // setzte Sprache auf Sprache des Kunden
                $oISOSprache = Shop::Lang()->getIsoFromLangID($Kunde->kSprache);
                if ((int)$_SESSION['kSprache'] !== (int)$Kunde->kSprache && !empty($oISOSprache->cISO)) {
                    $_SESSION['kSprache']        = (int)$Kunde->kSprache;
                    $_SESSION['cISOSprache']     = $oISOSprache->cISO;
                    $_SESSION['currentLanguage'] = Sprache::getAllLanguages(1)[$Kunde->kSprache];
                    Shop::setLanguage($Kunde->kSprache, $oISOSprache->cISO);
                    Shop::Lang()->setzeSprache($oISOSprache->cISO);
                }
            } else {
                $cHinweis .= Shop::Lang()->get('loginNotActivated');
            }
        } elseif ($nReturnValue === 2) { // Kunde ist gesperrt
            $cHinweis .= Shop::Lang()->get('accountLocked');
        } elseif ($nReturnValue === 3) { // Kunde ist nicht aktiv
            $cHinweis .= Shop::Lang()->get('accountInactive');
        } else {
            if (isset($Einstellungen['kunden']['kundenlogin_max_loginversuche'])
                && $Einstellungen['kunden']['kundenlogin_max_loginversuche'] !== ''
            ) {
                $maxAttempts = (int)$Einstellungen['kunden']['kundenlogin_max_loginversuche'];
                if ($maxAttempts > 1 && $nLoginversuche >= $maxAttempts) {
                    $_SESSION['showLoginCaptcha'] = true;
                }
            }
            $cHinweis .= Shop::Lang()->get('incorrectLogin');
        }
    }
}
