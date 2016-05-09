<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'jtl_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'wunschliste_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'kundenwerbenkeunden_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';

$AktuelleSeite = 'MEIN KONTO';
$cBrotNavi     = '';
$linkHelper    = LinkHelper::getInstance();
$Einstellungen = Shop::getSettings(
    array(
        CONF_GLOBAL,
        CONF_RSS,
        CONF_KUNDEN,
        CONF_KAUFABWICKLUNG,
        CONF_KUNDENFELD,
        CONF_KUNDENWERBENKUNDEN,
        CONF_TRUSTEDSHOPS
    )
);
$kLink            = $linkHelper->getSpecialPageLinkKey(LINKTYP_LOGIN);
$cHinweis         = '';
$hinweis          = '';
$cFehler          = '';
$showLoginCaptcha = false;

if (verifyGPCDataInteger('wlidmsg') > 0) {
    $cHinweis .= mappeWunschlisteMSG(verifyGPCDataInteger('wlidmsg'));
}
//Kunden in session aktualisieren
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $Kunde = new Kunde($_SESSION['Kunde']->kKunde);
    if ($Kunde->kKunde > 0) {
        $Kunde->angezeigtesLand = ISO2land($Kunde->cLand);
        $session->setCustomer($Kunde);
    }
}

// Redirect - Falls jemand eine Aktion durchführt die ein Kundenkonto beansprucht und der Gast nicht einloggt ist,
// wird dieser hier her umgeleitet und es werden die passenden Parameter erstellt.
// Nach dem erfolgreichen einloggen wird die zuvor angestrebte Aktion durchgeführt.
if (isset($_SESSION['JTL_REDIRECT']) || verifyGPCDataInteger('r') > 0) {
    $smarty->assign('oRedirect', (isset($_SESSION['JTL_REDIRECT']) ? $_SESSION['JTL_REDIRECT'] : gibRedirect(verifyGPCDataInteger('r'))));
    executeHook(HOOK_JTL_PAGE_REDIRECT_DATEN);
}
pruefeHttps();

unset($_SESSION['JTL_REDIRECT']);

if (isset($_GET['updated_pw']) && $_GET['updated_pw'] === 'true') {
    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
}
//loginbenutzer?
if (isset($_POST['login']) && intval($_POST['login']) === 1 && isset($_POST['email']) && isset($_POST['passwort'])) {
    $Kunde    = new Kunde();
    $csrfTest = validateToken();
    if ($csrfTest === false) {
        $cHinweis .= Shop::Lang()->get('csrfValidationFailed', 'global');
        Jtllog::writeLog('CSRF-Warnung fuer Login: ' . $_POST['login'], JTLLOG_LEVEL_ERROR);
    } else {
        $loginCaptchaOK = $Kunde->verifyLoginCaptcha($_POST);
        if ($loginCaptchaOK === true) {
            $nReturnValue   = $Kunde->holLoginKunde($_POST['email'], $_POST['passwort']);
            $nLoginversuche = $Kunde->nLoginversuche;
        } else {
            $nReturnValue   = 4;
            $nLoginversuche = $loginCaptchaOK;
        }
        if ($Kunde->kKunde > 0) {
            //create new session id to prevent session hijacking
            session_regenerate_id(false);
            //in tbesucher kKunde setzen
            if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
                Shop::DB()->query("UPDATE tbesucher SET kKunde = " . $Kunde->kKunde . " WHERE kBesucher = " . (int)$_SESSION["oBesucher"]->kBesucher, 4);
            }
            if ($Kunde->cAktiv === 'Y') {
                unset($_SESSION['Zahlungsart']);
                unset($_SESSION['Versandart']);
                unset($_SESSION['Lieferadresse']);
                unset($_SESSION['ks']);
                unset($_SESSION['VersandKupon']);
                unset($_SESSION['NeukundenKupon']);
                unset($_SESSION['Kupon']);
                // Lösche kompletten Kategorie Cache
                unset($_SESSION['kKategorieVonUnterkategorien_arr']);
                unset($_SESSION['oKategorie_arr']);
                unset($_SESSION['oKategorie_arr_new']);
                // Kampagne
                if (isset($_SESSION['Kampagnenbesucher'])) {
                    setzeKampagnenVorgang(KAMPAGNE_DEF_LOGIN, $Kunde->kKunde, 1.0); // Login
                }
                $session->setCustomer($Kunde);
                // Setzt aktuelle Wunschliste (falls vorhanden) vom Kunden in die Session
                setzeWunschlisteInSession();
                // Redirect URL
                $cURL = StringHandler::filterXSS(verifyGPDataString('cURL'));
                // Lade WarenkorbPers
                $bPersWarenkorbGeladen = false;
                if ($Einstellungen['global']['warenkorbpers_nutzen'] === 'Y' && count($_SESSION['Warenkorb']->PositionenArr) === 0) {
                    $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                    $oWarenkorbPers->ueberpruefePositionen(true);
                    if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                        foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
                            if (!isset($oWunschlistePos->Artikel->bHasKonfig) || !$oWunschlistePos->Artikel->bHasKonfig) {
                                fuegeEinInWarenkorb(
                                    $oWarenkorbPersPos->kArtikel,
                                    $oWarenkorbPersPos->fAnzahl,
                                    $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr,
                                    1,
                                    $oWarenkorbPersPos->cUnique,
                                    $oWarenkorbPersPos->kKonfigitem,
                                    null,
                                    false
                                );
                            }
                        }
                        $_SESSION['Warenkorb']->setzePositionsPreise();
                        $bPersWarenkorbGeladen = true;
                    }
                }
                // Pruefe, ob Artikel im Warenkorb vorhanden sind, welche für den aktuellen Kunden nicht mehr sichtbar sein duerfen
                pruefeWarenkorbArtikelSichtbarkeit($_SESSION['Kunde']->kKundengruppe);
                executeHook(HOOK_JTL_PAGE_REDIRECT);

                if (strlen($cURL) > 0) {
                    if (substr($cURL, 0, 4) !== 'http') {
                        header('Location: ' . $cURL, true, 301);
                        exit();
                    }
                } else {
                    // Existiert ein pers. Warenkorb? Wenn ja => frag Kunde ob er einen eventuell vorhandenen Warenkorb mergen möchte
                    if ($Einstellungen['global']['warenkorbpers_nutzen'] === 'Y' && $Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'Y' && !$bPersWarenkorbGeladen) {
                        setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
                    } elseif ($Einstellungen['global']['warenkorbpers_nutzen'] === 'Y' && $Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === 'P' && !$bPersWarenkorbGeladen) {
                        $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                        if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                            $smarty->assign('nWarenkorb2PersMerge', 1);
                        }
                    }
                }
            } else {
                $cHinweis .= Shop::Lang()->get('loginNotActivated', 'global');
            }
        } elseif ($nReturnValue === 2) { // Kunde ist gesperrt
            $cHinweis .= Shop::Lang()->get('accountLocked', 'global');
        } elseif ($nReturnValue === 3) { // Kunde ist nicht aktiv
            $cHinweis .= Shop::Lang()->get('accountInactive', 'global');
        } else {
            if (isset($Einstellungen['kunden']['kundenlogin_max_loginversuche']) && $Einstellungen['kunden']['kundenlogin_max_loginversuche'] !== '') {
                $maxAttempts = intval($Einstellungen['kunden']['kundenlogin_max_loginversuche']);
                if ($maxAttempts > 1 && $nLoginversuche >= $maxAttempts) {
                    $showLoginCaptcha = true;
                    $smarty->assign('code_login', generiereCaptchaCode(3));
                }
            }
            $cHinweis .= Shop::Lang()->get('incorrectLogin', 'global');
        }
    }
}

$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;

$editRechnungsadresse = (isset($_GET['editRechnungsadresse']) && !empty($Kunde->kKunde)) ? (int)$_GET['editRechnungsadresse'] : 0;
if (isset($_POST['editRechnungsadresse']) && (int)$_POST['editRechnungsadresse'] === 1 && !empty($Kunde->kKunde)) {
    $editRechnungsadresse = (int)$_POST['editRechnungsadresse'];
}

Shop::setPageType(PAGE_LOGIN);
$step = 'login';
if (isset($_GET['loggedout'])) {
    $cHinweis .= Shop::Lang()->get('loggedOut', 'global');
}
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    Shop::setPageType(PAGE_MEINKONTO);
    $step = 'mein Konto';

    // abmelden + meldung
    if (isset($_GET['logout']) && (int)$_GET['logout'] === 1) {
        if (!empty($_SESSION['Kunde']->kKunde)) {
            // Sprache und Waehrung beibehalten
            $kSprache    = Shop::$kSprache;
            $cISOSprache = Shop::$cISO;
            $Waehrung    = $_SESSION['Waehrung'];
            // Kategoriecache loeschen
            unset($_SESSION['kKategorieVonUnterkategorien_arr']);
            unset($_SESSION['oKategorie_arr']);
            unset($_SESSION['oKategorie_arr_new']);
            unset($_SESSION['Warenkorb']);

            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 7000000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            session_destroy();
            $session = new Session();
            session_regenerate_id(true);

            $_SESSION['kSprache']    = $kSprache;
            $_SESSION['cISOSprache'] = $cISOSprache;
            $_SESSION['Waehrung']    = $Waehrung;
            Shop::setLanguage($kSprache, $cISOSprache);

            header('Location: jtl.php?loggedout=1', true, 303);
            exit();
        }
    }

    if (isset($_GET['del']) && intval($_GET['del']) === 1) {
        $step = 'account loeschen';
    }
    // Vorhandenen Warenkorb mit persistenten Warenkorb mergen?
    if (verifyGPCDataInteger('basket2Pers') === 1) {
        setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
        header('Location: jtl.php', true, 303);
        exit();
    }
    // Wunschliste loeschen
    if (verifyGPCDataInteger('wllo') > 0 && validateToken()) {
        $step = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        $cHinweis .= wunschlisteLoeschen(verifyGPCDataInteger('wllo'));
    }
    // Wunschliste Standard setzen
    if (isset($_POST['wls']) && intval($_POST['wls']) > 0 && validateToken()) {
        $step = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        $cHinweis .= wunschlisteStandard(verifyGPCDataInteger('wls'));
    }
    // Kunden werben Kunden
    if (verifyGPCDataInteger('KwK') === 1 && isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
        if ($Einstellungen['kundenwerbenkunden']['kwk_nutzen'] === 'Y') {
            $step = 'kunden_werben_kunden';
            if (verifyGPCDataInteger('kunde_werben') === 1) {
                if (!pruefeEmailblacklist($_POST['cEmail'])) {
                    if (pruefeEingabe($_POST)) {
                        if (setzeKwKinDB($_POST, $Einstellungen)) {
                            $cHinweis .= sprintf(Shop::Lang()->get('kwkAdd', 'messages') . '<br />',
                                StringHandler::filterXSS($_POST['cEmail']));
                        } else {
                            $cFehler .= sprintf(Shop::Lang()->get('kwkAlreadyreg', 'errorMessages') . '<br />',
                                StringHandler::filterXSS($_POST['cEmail']));
                        }
                    } else {
                        $cFehler .= Shop::Lang()->get('kwkWrongdata', 'errorMessages') . '<br />';
                    }
                } else {
                    $cFehler .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
                }
            }
        }
    }
    // WunschlistePos in den Warenkorb adden
    if (isset($_GET['wlph']) && intval($_GET['wlph']) > 0 && intval($_GET['wl']) > 0) {
        $cURLID          = StringHandler::filterXSS(verifyGPDataString('wlid'));
        $kWunschlistePos = verifyGPCDataInteger('wlph');
        $kWunschliste    = verifyGPCDataInteger('wl');
        $step            = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        $oWunschlistePos = giboWunschlistePos($kWunschlistePos);
        if (isset($oWunschlistePos->kArtikel) || $oWunschlistePos->kArtikel > 0) {
            $oEigenschaftwerte_arr = (ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)) ?
                gibVarKombiEigenschaftsWerte($oWunschlistePos->kArtikel) :
                gibEigenschaftenZuWunschliste($kWunschliste, $oWunschlistePos->kWunschlistePos);
            if (!$oWunschlistePos->bKonfig) {
                fuegeEinInWarenkorb($oWunschlistePos->kArtikel, $oWunschlistePos->fAnzahl, $oEigenschaftwerte_arr);
            }
            $cParamWLID = (strlen($cURLID) > 0) ? ('&wlid=' . $cURLID) : '';
            header('Location: jtl.php?wl=' . $kWunschliste . '&wlidmsg=1' . $cParamWLID, true, 303);
            exit();
        }
    }
    // WunschlistePos alle in den Warenkorb adden
    if (isset($_GET['wlpah']) && intval($_GET['wlpah']) === 1 && intval($_GET['wl']) > 0) {
        $cURLID       = StringHandler::filterXSS(verifyGPDataString('wlid'));
        $kWunschliste = verifyGPCDataInteger('wl');
        $step         = 'mein Konto';
        $oWunschliste = giboWunschliste($kWunschliste);
        $oWunschliste = new Wunschliste($oWunschliste->kWunschliste);

        if (isset($oWunschliste->CWunschlistePos_arr) && is_array($oWunschliste->CWunschlistePos_arr) && count($oWunschliste->CWunschlistePos_arr) > 0) {
            foreach ($oWunschliste->CWunschlistePos_arr as $oWunschlistePos) {
                $oEigenschaftwerte_arr = (ArtikelHelper::isVariChild($oWunschlistePos->kArtikel)) ?
                    gibVarKombiEigenschaftsWerte($oWunschlistePos->kArtikel) :
                    gibEigenschaftenZuWunschliste($kWunschliste, $oWunschlistePos->kWunschlistePos);
                if (!$oWunschlistePos->Artikel->bHasKonfig && !$oWunschlistePos->bKonfig && isset($oWunschlistePos->Artikel->inWarenkorbLegbar) && $oWunschlistePos->Artikel->inWarenkorbLegbar > 0) {
                    fuegeEinInWarenkorb($oWunschlistePos->kArtikel, $oWunschlistePos->fAnzahl, $oEigenschaftwerte_arr);
                }
            }
            header('Location: jtl.php?wl=' . $kWunschliste . '&wlid=' . $cURLID . '&wlidmsg=2', true, 303);
            exit();
        }
    }
    // Wunschliste aktualisieren bzw alle Positionen
    if (verifyGPCDataInteger('wla') > 0 && verifyGPCDataInteger('wl') > 0) {
        $step         = 'mein Konto';
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste) {
            // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
            $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', (int)$kWunschliste);
            if (!empty($oWunschliste->kKunde) && $oWunschliste->kKunde == $_SESSION['Kunde']->kKunde) {
                $step = 'wunschliste anzeigen';
                $cHinweis .= wunschlisteAktualisieren($kWunschliste);

                $CWunschliste            = (isset($_SESSION['Wunschliste']->kWunschliste)) ?
                    new Wunschliste($_SESSION['Wunschliste']->kWunschliste) :
                    new Wunschliste($kWunschliste);
                $_SESSION['Wunschliste'] = $CWunschliste;
                $cBrotNavi               = createNavigation('', 0, 0, $CWunschliste->cName, 'jtl.php?wl=' . $CWunschliste->kWunschliste);
            }
        }
    }
    // neue Wunschliste speichern
    if (isset($_POST['wlh']) && intval($_POST['wlh']) > 0) {
        $step             = 'mein Konto';
        $cWunschlisteName = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cWunschlisteName']));
        $cHinweis .= wunschlisteSpeichern($cWunschlisteName);
    }
    // Wunschliste via Email
    if (verifyGPCDataInteger('wlvm') > 0 && verifyGPCDataInteger('wl') > 0) {
        $kWunschliste = verifyGPCDataInteger('wl');
        $step         = (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) ? 'mein Konto' : 'login';
        // Pruefen, ob der MD5 vorhanden ist
        if (intval($kWunschliste) > 0) {
            $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', (int)$kWunschliste, 'kKunde', (int)$_SESSION['Kunde']->kKunde, null, null, false, 'kWunschliste, cURLID');
            if (isset($oWunschliste->kWunschliste) && $oWunschliste->kWunschliste > 0 && strlen($oWunschliste->cURLID) > 0) {
                $step = 'wunschliste anzeigen';
                // Soll die Wunschliste nun an die Emailempfaenger geschickt werden?
                if (isset($_POST['send']) && intval($_POST['send']) === 1) {
                    if ($Einstellungen['global']['global_wunschliste_anzeigen'] === 'Y') {
                        $cEmail_arr = explode(' ', StringHandler::htmlentities(StringHandler::filterXSS($_POST['email'])));
                        $cHinweis .= wunschlisteSenden($cEmail_arr, $kWunschliste);
                        // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                        $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
                        $smarty->assign('CWunschliste', $CWunschliste);
                        $cBrotNavi = createNavigation('', 0, 0, $CWunschliste->cName, 'jtl.php?wl=' . $CWunschliste->kWunschliste);
                    }
                } else {
                    // Maske aufbauen
                    $step = 'wunschliste versenden';
                    // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                    $CWunschliste = bauecPreis(new Wunschliste($kWunschliste));
                    $smarty->assign('CWunschliste', $CWunschliste);
                    $cBrotNavi = createNavigation('', 0, 0, $CWunschliste->cName, 'jtl.php?wl=' . $CWunschliste->kWunschliste);
                }
            }
        }
    }
    // Wunschliste alle Positionen loeschen
    if (verifyGPCDataInteger('wldl') === 1) {
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste) {
            $oWunschliste = new Wunschliste($kWunschliste);

            if ($oWunschliste->kKunde == $_SESSION['Kunde']->kKunde && $oWunschliste->kKunde) {
                $step = 'wunschliste anzeigen';
                $oWunschliste->entferneAllePos();
                if ($_SESSION['Wunschliste']->kWunschliste == $oWunschliste->kWunschliste) {
                    $_SESSION['Wunschliste']->CWunschlistePos_arr = array();
                    $cBrotNavi                                    = createNavigation('', 0, 0, $_SESSION['Wunschliste']->cName, 'jtl.php?wl=' . $_SESSION['Wunschliste']->kWunschliste);
                }
                $cHinweis .= Shop::Lang()->get('wishlistDelAll', 'messages');
            }
        }
    }
    // Wunschliste Artikelsuche
    if (verifyGPCDataInteger('wlsearch') === 1) {
        $cSuche       = strip_tags(StringHandler::filterXSS(verifyGPDataString('cSuche')));
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste) {
            $oWunschliste = new Wunschliste($kWunschliste);
            if ($oWunschliste->kKunde == $_SESSION['Kunde']->kKunde && $oWunschliste->kKunde) {
                $step = 'wunschliste anzeigen';
                $smarty->assign('wlsearch', $cSuche);
                $oWunschlistePosSuche_arr          = $oWunschliste->sucheInWunschliste($cSuche);
                $oWunschliste->CWunschlistePos_arr = $oWunschlistePosSuche_arr;
                $smarty->assign('CWunschliste', $oWunschliste);
                $cBrotNavi = createNavigation('', 0, 0, $oWunschliste->cName, 'jtl.php?wl=' . $oWunschliste->kWunschliste);
            }
        }
    } elseif (verifyGPCDataInteger('wl') > 0 && verifyGPCDataInteger('wlvm') === 0) { // Wunschliste anzeigen
        $step         = (!empty($_SESSION['Kunde']->kKunde)) ? 'mein Konto' : 'login';
        $kWunschliste = verifyGPCDataInteger('wl');
        if ($kWunschliste > 0) {
            // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
            $oWunschliste = Shop::DB()->select('twunschliste', 'kWunschliste', intval($kWunschliste));
            if (isset($_SESSION['Kunde']->kKunde) && isset($oWunschliste->kKunde) && $oWunschliste->kKunde == $_SESSION['Kunde']->kKunde) {
                // Wurde nOeffentlich verändert
                if (isset($_REQUEST['nstd']) && validateToken()) {
                    $nOeffentlich = verifyGPCDataInteger('nstd');
                    // Wurde nstd auf 1 oder 0 gesetzt?
                    if ($nOeffentlich === 0) {
                        // nOeffentlich der Wunschliste updaten zu Privat
                        Shop::DB()->query("UPDATE twunschliste SET nOeffentlich = 0, cURLID = '' WHERE kWunschliste = " . intval($kWunschliste), 3);
                        $cHinweis .= Shop::Lang()->get('wishlistSetPrivate', 'messages');
                    } elseif ($nOeffentlich === 1) {
                        $cURLID = gibUID(32, substr(md5($kWunschliste), 0, 16) . time());
                        // Kampagne
                        $oKampagne = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
                        if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                            $cURLID .= '&' . $oKampagne->cParameter . '=' . $oKampagne->cWert;
                        }
                        // nOeffentlich der Wunschliste updaten zu öffentlich
                        Shop::DB()->query(
                            "UPDATE twunschliste
                                SET nOeffentlich = 1, cURLID = '" . $cURLID . "'
                                WHERE kWunschliste = " . intval($kWunschliste), 3
                        );
                        $cHinweis .= Shop::Lang()->get('wishlistSetPublic', 'messages');
                    }
                }
                // Wunschliste aufbauen und cPreis setzen (Artikelanzahl mit eingerechnet)
                $CWunschliste = bauecPreis(new Wunschliste($oWunschliste->kWunschliste));

                $smarty->assign('CWunschliste', $CWunschliste);
                $step      = 'wunschliste anzeigen';
                $cBrotNavi = createNavigation('', 0, 0, $CWunschliste->cName, 'jtl.php?wl=' . $CWunschliste->kWunschliste);
            }
        }
    }
    if ($editRechnungsadresse == 1) {
        $step = 'rechnungsdaten';
    }
    if (isset($_GET['pass']) && intval($_GET['pass']) === 1) {
        $step = 'passwort aendern';
    }
    // Kundendaten speichern
    if (isset($_POST['edit']) && intval($_POST['edit']) === 1) {
        $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
        $fehlendeAngaben = checkKundenFormular(1, 0);
        $kKundengruppe   = Kundengruppe::getCurrent();
        // CheckBox Plausi
        $oCheckBox           = new CheckBox();
        $fehlendeAngaben     = array_merge($fehlendeAngaben, $oCheckBox->validateCheckBox(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $_POST, true));
        $knd                 = getKundendaten($_POST, 0, 0);
        $cKundenattribut_arr = getKundenattribute($_POST);
        $nReturnValue        = angabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_JTL_PAGE_KUNDENDATEN_PLAUSI);

        if ($nReturnValue) {
            $knd->cAbgeholt = 'N';
            $knd->updateInDB();
            // CheckBox Spezialfunktion ausführen
            $oCheckBox->triggerSpecialFunction(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, true, $_POST, array('oKunde' => $knd))
                      ->checkLogging(CHECKBOX_ORT_KUNDENDATENEDITIEREN, $kKundengruppe, $_POST, true);
            // Kundendatenhistory
            Kundendatenhistory::saveHistory($_SESSION['Kunde'], $knd, Kundendatenhistory::QUELLE_MEINKONTO);
            $_SESSION['Kunde'] = $knd;
            // Update Kundenattribute
            if (is_array($cKundenattribut_arr) && count($cKundenattribut_arr) > 0) {
                $oKundenfeldNichtEditierbar_arr = getKundenattributeNichtEditierbar();
                $cSQL                           = '';
                if (is_array($oKundenfeldNichtEditierbar_arr) && count($oKundenfeldNichtEditierbar_arr) > 0) {
                    $cSQL .= ' AND (';
                    foreach ($oKundenfeldNichtEditierbar_arr as $i => $oKundenfeldNichtEditierbar) {
                        if ($i == 0) {
                            $cSQL .= 'kKundenfeld != ' . (int)$oKundenfeldNichtEditierbar->kKundenfeld;
                        } else {
                            $cSQL .= ' AND kKundenfeld != ' . (int)$oKundenfeldNichtEditierbar->kKundenfeld;
                        }
                    }
                    $cSQL .= ')';
                }
                Shop::DB()->query("DELETE FROM tkundenattribut WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . $cSQL, 3);

                $nKundenattributKey_arr = array_keys($cKundenattribut_arr);
                foreach ($nKundenattributKey_arr as $kKundenfeld) {
                    $oKundenattribut              = new stdClass();
                    $oKundenattribut->kKunde      = (int)$_SESSION['Kunde']->kKunde;
                    $oKundenattribut->kKundenfeld = (int)$cKundenattribut_arr[$kKundenfeld]->kKundenfeld;
                    $oKundenattribut->cName       = $cKundenattribut_arr[$kKundenfeld]->cWawi;
                    $oKundenattribut->cWert       = $cKundenattribut_arr[$kKundenfeld]->cWert;

                    Shop::DB()->insert('tkundenattribut', $oKundenattribut);
                }
            }
            $step = 'mein Konto';
            $cHinweis .= Shop::Lang()->get('dataEditSuccessful', 'login');
            setzeSteuersaetze();
            if (isset($_SESSION['Warenkorb']->kWarenkorb) && $_SESSION['Warenkorb']->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
                $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized();
            }
        } else {
            $smarty->assign('fehlendeAngaben', $fehlendeAngaben);
        }
    }
    if (isset($_POST['pass_aendern']) && intval($_POST['pass_aendern'] && validateToken()) === 1) {
        $step = 'passwort aendern';
        if (!isset($_POST['altesPasswort']) || !isset($_POST['neuesPasswort1']) || !$_POST['altesPasswort'] || !$_POST['neuesPasswort1']) {
            $cHinweis .= Shop::Lang()->get('changepasswordFilloutForm', 'login');
        }
        if (
            (isset($_POST['neuesPasswort1']) && !isset($_POST['neuesPasswort2'])) ||
            (isset($_POST['neuesPasswort2']) && !isset($_POST['neuesPasswort1'])) ||
            $_POST['neuesPasswort1'] !== $_POST['neuesPasswort2']
        ) {
            $cHinweis .= Shop::Lang()->get('changepasswordPassesNotEqual', 'login');
        }
        if (isset($_POST['neuesPasswort1']) && strlen($_POST['neuesPasswort1']) < $Einstellungen['kunden']['kundenregistrierung_passwortlaenge']) {
            $cHinweis .= Shop::Lang()->get('changepasswordPassTooShort', 'login') . ' ' . lang_passwortlaenge($Einstellungen['kunden']['kundenregistrierung_passwortlaenge']);
        }
        if (isset($_POST['neuesPasswort1']) && isset($_POST['neuesPasswort2']) &&
            $_POST['neuesPasswort1'] && $_POST['neuesPasswort1'] === $_POST['neuesPasswort2'] &&
            strlen($_POST['neuesPasswort1']) >= $Einstellungen['kunden']['kundenregistrierung_passwortlaenge']
        ) {
            $oKunde = new Kunde($_SESSION['Kunde']->kKunde);
            $oUser  = Shop::DB()->select('tkunde', 'kKunde', (int)$_SESSION['Kunde']->kKunde, null, null, null, null, false, 'cPasswort, cMail');
            if (isset($oUser->cPasswort) && isset($oUser->cMail)) {
                $ok = $oKunde->checkCredentials($oUser->cMail, $_POST['altesPasswort']);
                if ($ok !== false) {
                    $oKunde->updatePassword($_POST['neuesPasswort1']);
                    $step = 'mein Konto';
                    $cHinweis .= Shop::Lang()->get('changepasswordSuccess', 'login');
                } else {
                    $cHinweis .= Shop::Lang()->get('changepasswordWrongPass', 'login');
                }
            }
        }
    }
    if (verifyGPCDataInteger('bestellung') > 0) {
        //bestellung von diesem Kunden?
        $bestellung = new Bestellung(verifyGPCDataInteger('bestellung'));
        $bestellung->fuelleBestellung();

        if (isset($bestellung->kKunde) && isset($_SESSION['Kunde']->kKunde) && $bestellung->kKunde !== null &&
            intval($bestellung->kKunde) > 0 && $bestellung->kKunde == $_SESSION['Kunde']->kKunde) {
            // Download wurde angefordert?
            if (verifyGPCDataInteger('dl') > 0) {
                if (class_exists('Download')) {
                    $nReturn = Download::getFile(verifyGPCDataInteger('dl'), $_SESSION['Kunde']->kKunde, $bestellung->kBestellung);
                    if ($nReturn != 1) {
                        $cFehler = Download::mapGetFileErrorCode($nReturn);
                    }
                }
            }
            $step                               = 'bestellung';
            $_SESSION['Kunde']->angezeigtesLand = ISO2land($_SESSION['Kunde']->cLand);
            $smarty->assign('Bestellung', $bestellung)
                   ->assign('Kunde', $bestellung->oRechnungsadresse)// Work Around Daten von trechnungsadresse
                   ->assign('Lieferadresse', ((isset($bestellung->Lieferadresse)) ? $bestellung->Lieferadresse : null));
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $smarty->assign('oTrustedShopsBewertenButton', gibTrustedShopsBewertenButton($bestellung->oRechnungsadresse->cMail, $bestellung->cBestellNr));
            }
        } else {
            $step = 'login';
        }
    }
    if (isset($_POST['del_acc']) && intval($_POST['del_acc']) === 1) {
        $csrfTest = validateToken();
        if ($csrfTest === false) {
            $cHinweis .= Shop::Lang()->get('csrfValidationFailed', 'global');
            Jtllog::writeLog('CSRF-Warnung fuer Account-Loeschung und kKunde ' . (int)$_SESSION['Kunde']->kKunde, JTLLOG_LEVEL_ERROR);
        } else {
            $oBestellung = Shop::DB()->query(
                "SELECT kBestellung
                    FROM tbestellung
                    WHERE (cStatus = " . BESTELLUNG_STATUS_OFFEN . " OR cStatus = " . BESTELLUNG_STATUS_IN_BEARBEITUNG . ")
                    AND kKunde = " . (int)$_SESSION['Kunde']->kKunde, 1
            );
            if (empty($oBestellung->kBestellung) || !$oBestellung->kBestellung) {
                $oBestellung_arr     = Shop::DB()->query("SELECT * FROM tbestellung WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde, 2);
                $nAnzahlBestellungen = Shop::DB()->delete('bestellung', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                $cText               = utf8_decode('Der Kunde ' . $_SESSION['Kunde']->cVorname . ' ' . $_SESSION['Kunde']->cNachname .
                    ' (' . $_SESSION['Kunde']->kKunde . ') hat am ' . date('d.m.Y') . ' um ' . date('H:m:i') . ' Uhr sein Kundenkonto und ' . $nAnzahlBestellungen . ' Bestellungen gelöscht.');
                if (count($oBestellung_arr) > 0) {
                    $cText .= "\n" . print_r($oBestellung_arr, true);
                }
                writeLog(PFAD_ROOT . '/jtllogs/geloeschteKundenkontos.log', $cText, 1);

                // Newsletter
                Shop::DB()->delete('tnewsletterempfaenger', 'cEmail', $_SESSION['Kunde']->cMail);
                $oNewsletterHistory               = new stdClass();
                $oNewsletterHistory->kSprache     = (int)$_SESSION['Kunde']->kSprache;
                $oNewsletterHistory->kKunde       = (int)$_SESSION['Kunde']->kSprache;
                $oNewsletterHistory->cAnrede      = $_SESSION['Kunde']->cAnrede;
                $oNewsletterHistory->cVorname     = $_SESSION['Kunde']->cVorname;
                $oNewsletterHistory->cNachname    = $_SESSION['Kunde']->cNachname;
                $oNewsletterHistory->cEmail       = $_SESSION['Kunde']->cMail;
                $oNewsletterHistory->cOptCode     = '';
                $oNewsletterHistory->cLoeschCode  = '';
                $oNewsletterHistory->cAktion      = 'Geloescht';
                $oNewsletterHistory->dAusgetragen = 'now()';
                $oNewsletterHistory->dEingetragen = '';
                $oNewsletterHistory->dOptCode     = '';

                Shop::DB()->insert('tnewsletterempfaengerhistory', $oNewsletterHistory);

                Shop::DB()->delete('tzahlungsinfo', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->delete('tkunde', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->delete('tlieferadresse', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->query(
                    "DELETE twarenkorb, twarenkorbpos, twarenkorbposeigenschaft, twarenkorbpers, twarenkorbperspos, twarenkorbpersposeigenschaft
                    FROM twarenkorb
                    LEFT JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = twarenkorb.kWarenkorb
                    LEFT JOIN twarenkorbposeigenschaft ON twarenkorbposeigenschaft.kWarenkorbPos = twarenkorbpos.kWarenkorbPos
                    LEFT JOIN twarenkorbpers ON twarenkorbpers.kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                    LEFT JOIN twarenkorbperspos ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
                    LEFT JOIN twarenkorbpersposeigenschaft ON twarenkorbpersposeigenschaft.kWarenkorbPersPos = twarenkorbperspos.kWarenkorbPersPos
                    WHERE twarenkorb.kKunde = " . (int)$_SESSION['Kunde']->kKunde, 4
                );
                Shop::DB()->delete('tkundenattribut', 'kKunde', (int)$_SESSION['Kunde']->kKunde);
                Shop::DB()->query(
                    "DELETE twunschliste, twunschlistepos, twunschlisteposeigenschaft, twunschlisteversand
                    FROM twunschliste
                    LEFT JOIN twunschlistepos ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                    LEFT JOIN twunschlisteposeigenschaft ON twunschlisteposeigenschaft.kWunschlistePos = twunschlistepos.kWunschlistePos
                    LEFT JOIN twunschlisteversand ON twunschlisteversand.kWunschliste = twunschliste.kWunschliste
                    WHERE twunschliste.kKunde = " . (int)$_SESSION['Kunde']->kKunde, 4
                );
                $obj->tkunde = $_SESSION['Kunde'];
                sendeMail(MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT, $obj);

                executeHook(HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN);
                session_destroy();
                header('Location: ' . Shop::getURL(), true, 303);
                exit;
            } else {
                $step = 'mein Konto';
                $cHinweis .= Shop::Lang()->get('accountDeleteFailure', 'global');
            }
        }
    }
    if ($step === 'mein Konto') {
        $Kunde->cGuthabenLocalized = gibPreisStringLocalized($Kunde->fGuthaben);
        $smarty->assign('Kunde', $_SESSION['Kunde']);
        // Download wurde angefordert?
        if (verifyGPCDataInteger('dl') > 0) {
            if (class_exists('Download')) {
                $nReturn = Download::getFile(verifyGPCDataInteger('dl'), $_SESSION['Kunde']->kKunde,
                    verifyGPCDataInteger('kBestellung'));
                if ($nReturn != 1) {
                    $cFehler = Download::mapGetFileErrorCode($nReturn);
                }
            }
        }
        $oDownload_arr = array();
        if (class_exists('Download')) {
            $oDownload_arr = Download::getDownloads(array('kKunde' => $_SESSION['Kunde']->kKunde), Shop::$kSprache);
            $smarty->assign('oDownload_arr', $oDownload_arr);
        }
        $Lieferadressen      = array();
        $oLieferdatenTMP_arr = Shop::DB()->query("SELECT kLieferadresse FROM tlieferadresse WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde, 2);

        if (is_array($oLieferdatenTMP_arr) && count($oLieferdatenTMP_arr) > 0) {
            foreach ($oLieferdatenTMP_arr as $oLieferdatenTMP) {
                if ($oLieferdatenTMP->kLieferadresse > 0) {
                    $Lieferadressen[] = new Lieferadresse($oLieferdatenTMP->kLieferadresse);
                }
            }
        }

        $smarty->assign('Lieferadressen', $Lieferadressen);
        $Bestellungen     = array();
        $oWunschliste_arr = array();
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $Bestellungen = Shop::DB()->query(
                "SELECT *, date_format(dErstellt,'%d.%m.%Y') AS dBestelldatum
                    FROM tbestellung
                    WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                    ORDER BY kBestellung DESC LIMIT " . CUSTOMER_ACCOUNT_MAX_ORDERS, 2
            );
            if (is_array($Bestellungen) && count($Bestellungen) > 0) {
                foreach ($Bestellungen as $i => $oBestellung) {
                    $Bestellungen[$i]->bDownload = false;
                    if (is_array($oDownload_arr) && count($oDownload_arr) > 0) {
                        foreach ($oDownload_arr as $oDownload) {
                            if ($oBestellung->kBestellung == $oDownload->kBestellung) {
                                $Bestellungen[$i]->bDownload = true;
                            }
                        }
                    }
                }
            }
        }
        $orderCount = count($Bestellungen);
        for ($i = 0; $i < $orderCount; $i++) {
            if ($Bestellungen[$i]->kWaehrung > 0) {
                $Bestellungen[$i]->Waehrung = Shop::DB()->select('twaehrung', 'kWaehrung', (int)$Bestellungen[$i]->kWaehrung);
                if (isset($Bestellungen[$i]->fWaehrungsFaktor) && $Bestellungen[$i]->fWaehrungsFaktor !== 1 && isset($Bestellungen[$i]->Waehrung->fFaktor)) {
                    $Bestellungen[$i]->Waehrung->fFaktor = $Bestellungen[$i]->fWaehrungsFaktor;
                }
            }
            $Bestellungen[$i]->cBestellwertLocalized = gibPreisStringLocalized($Bestellungen[$i]->fGesamtsumme, $Bestellungen[$i]->Waehrung);
            $Bestellungen[$i]->Status                = lang_bestellstatus($Bestellungen[$i]->cStatus);
        }
        // Hole Wunschliste für eingeloggten Kunden
        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
            $oWunschliste_arr = Shop::DB()->query("
                SELECT *
                    FROM twunschliste
                    WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                    ORDER BY dErstellt DESC", 2
            );
        }
        // Pruefen, ob der Kunde Wunschlisten hat
        if (count($oWunschliste_arr) > 0) {
            $smarty->assign('oWunschliste_arr', $oWunschliste_arr);
        }
        $smarty->assign('Bestellungen', $Bestellungen);

        executeHook(HOOK_JTL_PAGE_MEINKKONTO);
    }
    if ($step === 'rechnungsdaten') {
        $knd = $_SESSION['Kunde'];
        if (isset($_POST['edit']) && intval($_POST['edit']) === 1) {
            $knd                 = getKundendaten($_POST, 0, 0);
            $cKundenattribut_arr = getKundenattribute($_POST);
        } else {
            $cKundenattribut_arr = $knd->cKundenattribut_arr;
        }
        if (preg_match('/^\d{4}\-\d{2}\-(\d{2})$/', $knd->dGeburtstag)) {
            list($jahr, $monat, $tag) = explode('-', $knd->dGeburtstag);
            $knd->dGeburtstag         = $tag . '.' . $monat . '.' . $jahr;
        }
        $smarty->assign('Kunde', $knd)
               ->assign('cKundenattribut_arr', $cKundenattribut_arr)
               ->assign('Einstellungen', $Einstellungen)
               ->assign('laender', gibBelieferbareLaender($_SESSION['Kunde']->kKundengruppe));
        // selbstdef. Kundenfelder
        $oKundenfeld_arr = Shop::DB()->query(
            "SELECT *
                FROM tkundenfeld
                WHERE kSprache = " . (int)Shop::$kSprache . "
                ORDER BY nSort DESC", 2
        );
        if (is_array($oKundenfeld_arr) && count($oKundenfeld_arr) > 0) {
            // tkundenfeldwert nachschauen ob dort Werte für tkundenfeld enthalten sind
            foreach ($oKundenfeld_arr as $i => $oKundenfeld) {
                if ($oKundenfeld->cTyp === 'auswahl') {
                    $oKundenfeldWert_arr = Shop::DB()->query(
                        "SELECT *
                            FROM tkundenfeldwert
                            WHERE kKundenfeld = " . (int)$oKundenfeld->kKundenfeld, 2
                    );

                    $oKundenfeld_arr[$i]->oKundenfeldWert_arr = $oKundenfeldWert_arr;
                }
            }
        }

        $smarty->assign('oKundenfeld_arr', $oKundenfeld_arr);
    }
}
if (strlen($cBrotNavi) === 0) {
    $cBrotNavi = createNavigation($AktuelleSeite);
}
// Canonical
$cCanonicalURL = Shop::getURL() . '/jtl.php';
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_LOGIN);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;
$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('hinweis', $cHinweis)
       ->assign('showLoginCaptcha', $showLoginCaptcha)
       ->assign('step', $step)
       ->assign('Navigation', $cBrotNavi)
       ->assign('requestURL', (isset($requestURL)) ? $requestURL : null)
       ->assign('Einstellungen', $Einstellungen)
       ->assign('BESTELLUNG_STATUS_BEZAHLT', BESTELLUNG_STATUS_BEZAHLT)
       ->assign('BESTELLUNG_STATUS_VERSANDT', BESTELLUNG_STATUS_VERSANDT)
       ->assign('BESTELLUNG_STATUS_OFFEN', BESTELLUNG_STATUS_OFFEN)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_KUNDENDATENEDITIEREN);
require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
executeHook(HOOK_JTL_PAGE);

$smarty->display('account/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
