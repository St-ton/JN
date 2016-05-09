<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

Shop::setPageType(PAGE_NEWSLETTER);
$AktuelleSeite = 'NEWSLETTER';
$oLink         = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_NEWSLETTER, 1);
if (isset($oLink->kLink)) {
    $kLink = $oLink->kLink;
} else {
    $kLink        = 0;
    $oLink        = new stdClass();
    $oLink->kLink = 0;
}

$Link       = new stdClass();
$linkHelper = LinkHelper::getInstance();
if (isset($oLink->kLink) && $oLink->kLink > 0) {
    //hole Link
    $Link = $linkHelper->getPageLink($oLink->kLink);
    //url
    $requestURL    = baueURL($Link, URLART_SEITE);
    $sprachURL     = baueSprachURLS($Link, URLART_SEITE);
    $Link->Sprache = $linkHelper->getPageLinkLanguage($oLink->kLink);
    $smarty->assign('Navigation', createNavigation($AktuelleSeite, 0, 0, $Link->Sprache->cName, $requestURL));
} else {
    $smarty->assign('Navigation', createNavigation($AktuelleSeite, 0, 0, Shop::Lang()->get('newsletter', 'breadcrumb'), 'newsletter.php'));
}

$cHinweis      = '';
$cFehler       = '';
$cCanonicalURL = '';
$Einstellungen = Shop::getSettings(array(CONF_GLOBAL, CONF_RSS, CONF_NEWSLETTER));

pruefeHttps();
//hole alle OberKategorien
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
$startKat             = new Kategorie();
$startKat->kKategorie = 0;
$cOption              = 'eintragen';
// Freischaltcode wurde übergeben
if (isset($_GET['fc']) && strlen($_GET['fc']) > 0) {
    $cOption               = 'freischalten';
    $cFreischaltCode       = StringHandler::htmlentities(StringHandler::filterXSS(Shop::DB()->escape(strip_tags($_GET['fc']))));
    $oNewsletterEmpfaenger = Shop::DB()->query(
        "SELECT *
            FROM tnewsletterempfaenger
            WHERE cOptCode='" . $cFreischaltCode . "'", 1
    );

    if ($oNewsletterEmpfaenger->kNewsletterEmpfaenger > 0) {
        executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN, array('oNewsletterEmpfaenger' => $oNewsletterEmpfaenger));
        // Newsletterempfaenger freischalten
        Shop::DB()->query(
            "UPDATE tnewsletterempfaenger
                SET nAktiv = 1
                WHERE kNewsletterEmpfaenger = " . (int)$oNewsletterEmpfaenger->kNewsletterEmpfaenger, 3
        );
        // Pruefen, ob mittlerweile ein Kundenkonto existiert und wenn ja, dann kKunde in tnewsletterempfänger aktualisieren
        Shop::DB()->query(
            "UPDATE tnewsletterempfaenger, tkunde
                SET tnewsletterempfaenger.kKunde = tkunde.kKunde
                WHERE tkunde.cMail = tnewsletterempfaenger.cEmail
                    AND tnewsletterempfaenger.kKunde = 0", 3
        );
        // Protokollieren (freigeschaltet)
        Shop::DB()->query(
            "UPDATE tnewsletterempfaengerhistory
                SET dOptCode = now(), cOptIp = '" . gibIP() . "'
                WHERE cOptCode = '" . $cFreischaltCode . "'
                    AND cAktion = 'Eingetragen'", 4
        );

        $cHinweis = Shop::Lang()->get('newsletterActive', 'messages');
    } else {
        $cFehler = Shop::Lang()->get('newsletterNoactive', 'errorMessages');
    }
} elseif (isset($_GET['lc']) && strlen($_GET['lc']) > 0) { // Loeschcode wurde uebergeben
    $cOption               = 'loeschen';
    $cLoeschCode           = StringHandler::htmlentities(StringHandler::filterXSS(Shop::DB()->escape(strip_tags($_GET['lc']))));
    $oNewsletterEmpfaenger = Shop::DB()->query(
        "SELECT *
            FROM tnewsletterempfaenger
            WHERE cLoeschCode='" . $cLoeschCode . "'", 1
    );

    if (isset($oNewsletterEmpfaenger->cLoeschCode) && strlen($oNewsletterEmpfaenger->cLoeschCode) > 0) {
        executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN, array('oNewsletterEmpfaenger' => $oNewsletterEmpfaenger));

        Shop::DB()->delete('tnewsletterempfaenger', 'cLoeschCode', $cLoeschCode);
        $oNewsletterEmpfaengerHistory               = new stdClass();
        $oNewsletterEmpfaengerHistory->kSprache     = $oNewsletterEmpfaenger->kSprache;
        $oNewsletterEmpfaengerHistory->kKunde       = $oNewsletterEmpfaenger->kKunde;
        $oNewsletterEmpfaengerHistory->cAnrede      = $oNewsletterEmpfaenger->cAnrede;
        $oNewsletterEmpfaengerHistory->cVorname     = $oNewsletterEmpfaenger->cVorname;
        $oNewsletterEmpfaengerHistory->cNachname    = $oNewsletterEmpfaenger->cNachname;
        $oNewsletterEmpfaengerHistory->cEmail       = $oNewsletterEmpfaenger->cEmail;
        $oNewsletterEmpfaengerHistory->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
        $oNewsletterEmpfaengerHistory->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
        $oNewsletterEmpfaengerHistory->cAktion      = 'Geloescht';
        $oNewsletterEmpfaengerHistory->dEingetragen = $oNewsletterEmpfaenger->dEingetragen;
        $oNewsletterEmpfaengerHistory->dAusgetragen = 'now()';
        $oNewsletterEmpfaengerHistory->dOptCode     = '0000-00-00';

        Shop::DB()->insert('tnewsletterempfaengerhistory', $oNewsletterEmpfaengerHistory);

        executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, array('oNewsletterEmpfaengerHistory' => $oNewsletterEmpfaengerHistory));
        // Blacklist
        $oBlacklist            = new stdClass();
        $oBlacklist->cMail     = $oNewsletterEmpfaenger->cEmail;
        $oBlacklist->dErstellt = 'now()';
        Shop::DB()->insert('tnewsletterempfaengerblacklist', $oBlacklist);

        $cHinweis = Shop::Lang()->get('newsletterDelete', 'messages');
    } else {
        $cFehler = Shop::Lang()->get('newsletterNocode', 'errorMessages');
    }
}
// Abonnieren
if (isset($_POST['abonnieren']) && intval($_POST['abonnieren']) === 1) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';

    $oKunde            = new stdClass();
    $oKunde->cAnrede   = (isset($_POST['cAnrede'])) ? StringHandler::filterXSS(Shop::DB()->escape(strip_tags($_POST['cAnrede']))) : null;
    $oKunde->cVorname  = (isset($_POST['cVorname'])) ? StringHandler::filterXSS(Shop::DB()->escape(strip_tags($_POST['cVorname']))) : null;
    $oKunde->cNachname = (isset($_POST['cNachname'])) ? StringHandler::filterXSS(Shop::DB()->escape(strip_tags($_POST['cNachname']))) : null;
    $oKunde->cEmail    = (isset($_POST['cEmail'])) ? StringHandler::filterXSS(Shop::DB()->escape(strip_tags($_POST['cEmail']))) : null;

    if (!pruefeEmailblacklist($oKunde->cEmail)) {
        $smarty->assign('oPlausi', fuegeNewsletterEmpfaengerEin($oKunde, true));
        Shop::DB()->delete('tnewsletterempfaengerblacklist', 'cMail', $oKunde->cEmail);
    } else {
        $cFehler .= (empty($_POST['cEmail'])) ?
            (Shop::Lang()->get('invalidEmail', 'global') . '<br />') :
            (Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />');
    }

    $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
} elseif (isset($_POST['abmelden']) && intval($_POST['abmelden']) === 1) { // Abmelden
    if (valid_email($_POST['cEmail'])) {
        // Pruefen, ob Email bereits vorhanden
        $oNewsletterEmpfaenger = Shop::DB()->select('tnewsletterempfaenger', 'cEmail', StringHandler::htmlentities(StringHandler::filterXSS(Shop::DB()->escape($_POST['cEmail']))));

        if (isset($oNewsletterEmpfaenger->kNewsletterEmpfaenger) && $oNewsletterEmpfaenger->kNewsletterEmpfaenger > 0) {
            executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN, array('oNewsletterEmpfaenger' => $oNewsletterEmpfaenger));
            // Newsletterempfaenger loeschen
            Shop::DB()->delete('tnewsletterempfaenger', 'cEmail', StringHandler::htmlentities(StringHandler::filterXSS(Shop::DB()->escape($_POST['cEmail']))));
            $oNewsletterEmpfaengerHistory               = new stdClass();
            $oNewsletterEmpfaengerHistory->kSprache     = $oNewsletterEmpfaenger->kSprache;
            $oNewsletterEmpfaengerHistory->kKunde       = $oNewsletterEmpfaenger->kKunde;
            $oNewsletterEmpfaengerHistory->cAnrede      = $oNewsletterEmpfaenger->cAnrede;
            $oNewsletterEmpfaengerHistory->cVorname     = $oNewsletterEmpfaenger->cVorname;
            $oNewsletterEmpfaengerHistory->cNachname    = $oNewsletterEmpfaenger->cNachname;
            $oNewsletterEmpfaengerHistory->cEmail       = $oNewsletterEmpfaenger->cEmail;
            $oNewsletterEmpfaengerHistory->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
            $oNewsletterEmpfaengerHistory->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
            $oNewsletterEmpfaengerHistory->cAktion      = 'Geloescht';
            $oNewsletterEmpfaengerHistory->dEingetragen = $oNewsletterEmpfaenger->dEingetragen;
            $oNewsletterEmpfaengerHistory->dAusgetragen = 'now()';
            $oNewsletterEmpfaengerHistory->dOptCode     = '0000-00-00';

            Shop::DB()->insert('tnewsletterempfaengerhistory', $oNewsletterEmpfaengerHistory);

            executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, array('oNewsletterEmpfaengerHistory' => $oNewsletterEmpfaengerHistory));
            // Blacklist
            $oBlacklist            = new stdClass();
            $oBlacklist->cMail     = $oNewsletterEmpfaenger->cEmail;
            $oBlacklist->dErstellt = 'now()';
            Shop::DB()->insert('tnewsletterempfaengerblacklist', $oBlacklist);

            $cHinweis = Shop::Lang()->get('newsletterDelete', 'messages');
        } else {
            $cFehler = Shop::Lang()->get('newsletterNoexists', 'errorMessages');
        }
    } else {
        $cFehler = Shop::Lang()->get('newsletterWrongemail', 'errorMessages');
    }
} elseif (isset($_GET['show']) && intval($_GET['show']) > 0) { // History anzeigen
    $cOption            = 'anzeigen';
    $kNewsletterHistory = (int)$_GET['show'];
    $oNewsletterHistory = Shop::DB()->query(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum, cHTMLStatic, cKundengruppeKey
            FROM tnewsletterhistory
            WHERE kNewsletterHistory = " . $kNewsletterHistory . "
            ", 1
    );
    $kKundengruppe = 0;
    if (isset($_SESSION['Kunde']->kKundengruppe) && intval($_SESSION['Kunde']->kKundengruppe) > 0) {
        $kKundengruppe = (int)$_SESSION['Kunde']->kKundengruppe;
    }
    if ($oNewsletterHistory->kNewsletterHistory > 0) {
        // Prüfe Kundengruppe
        if (pruefeNLHistoryKundengruppe($kKundengruppe, $oNewsletterHistory->cKundengruppeKey)) {
            $smarty->assign('oNewsletterHistory', $oNewsletterHistory);
        }
    }
}
// Ist Kunde eingeloggt?
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $oKunde = new Kunde($_SESSION['Kunde']->kKunde);
    $smarty->assign('bBereitsAbonnent', pruefeObBereitsAbonnent($oKunde->kKunde))
           ->assign('oKunde', $oKunde);
}
// Canonical
$cCanonicalURL = Shop::getURL() . '/newsletter.php';
// Metaangaben
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_NEWSLETTER);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('cOption', $cOption)
       ->assign('Einstellungen', $Einstellungen)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
       ->assign('code_newsletter', generiereCaptchaCode($Einstellungen['newsletter']['newsletter_sicherheitscode']));

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_NEWSLETTER_PAGE);

$smarty->display('newsletter/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
