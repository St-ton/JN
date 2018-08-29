<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

Shop::setPageType(PAGE_NEWSLETTER);
$AktuelleSeite = 'NEWSLETTER';
$links         = Shop::Container()->getDB()->selectAll('tlink', 'nLinkart', LINKTYP_NEWSLETTER);
$oLink         = new stdClass();
$oLink->kLink  = 0;
foreach ($links as $l) {
    $customerGroupIDs = StringHandler::parseSSK($l->cKundengruppen);
    $ok               = array_reduce($customerGroupIDs, function ($c, $p) {
        return $c === true || $p === 'NULL' || (int)$p === Session::CustomerGroup()->getID();
    }, false);
    if ($ok === true) {
        $oLink = $l;
        break;
    }
}
$linkHelper = Shop::Container()->getLinkService();
if (isset($oLink->kLink) && $oLink->kLink > 0) {
    $link = $linkHelper->getLinkByID($oLink->kLink);
} else {
    $oLink                   = Shop::Container()->getDB()->select('tlink', 'nLinkart', LINKTYP_404);
    $bFileNotFound           = true;
    Shop::$kLink             = (int)$oLink->kLink;
    Shop::$bFileNotFound     = true;
    Shop::$is404             = true;
    $cParameter_arr['is404'] = true;
    return;
}

$cHinweis      = '';
$cFehler       = '';
$cCanonicalURL = '';
$Einstellungen = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_NEWSLETTER]);

//hole alle OberKategorien
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$cOption                = 'eintragen';
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
// Freischaltcode wurde übergeben
if (isset($_GET['fc']) && strlen($_GET['fc']) > 0) {
    $cOption         = 'freischalten';
    $cFreischaltCode = StringHandler::htmlentities(StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_GET['fc']))));
    $recicpient      = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'cOptCode', $cFreischaltCode);

    if (isset($recicpient->kNewsletterEmpfaenger) && $recicpient->kNewsletterEmpfaenger > 0) {
        executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN, ['oNewsletterEmpfaenger' => $recicpient]);
        // Newsletterempfaenger freischalten
        Shop::Container()->getDB()->update(
            'tnewsletterempfaenger',
            'kNewsletterEmpfaenger',
            (int)$recicpient->kNewsletterEmpfaenger,
            (object)['nAktiv' => 1]
        );
        // Pruefen, ob mittlerweile ein Kundenkonto existiert
        // und wenn ja, dann kKunde in tnewsletterempfänger aktualisieren
        Shop::Container()->getDB()->query(
            "UPDATE tnewsletterempfaenger, tkunde
                SET tnewsletterempfaenger.kKunde = tkunde.kKunde
                WHERE tkunde.cMail = tnewsletterempfaenger.cEmail
                    AND tnewsletterempfaenger.kKunde = 0", 3
        );
        // Protokollieren (freigeschaltet)
        $upd           = new stdClass();
        $upd->dOptCode = 'now()';
        $upd->cOptIp   = RequestHelper::getIP();
        Shop::Container()->getDB()->update(
            'tnewsletterempfaengerhistory',
            ['cOptCode', 'cAktion'],
            [$cFreischaltCode, 'Eingetragen'],
            $upd
        );
        $cHinweis = Shop::Lang()->get('newsletterActive', 'messages');
    } else {
        $cFehler = Shop::Lang()->get('newsletterNoactive', 'errorMessages');
    }
} elseif (isset($_GET['lc']) && strlen($_GET['lc']) > 0) { // Loeschcode wurde uebergeben
    $cOption     = 'loeschen';
    $cLoeschCode = StringHandler::htmlentities(
        StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_GET['lc'])))
    );
    $recicpient  = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'cLoeschCode', $cLoeschCode);

    if (!empty($recicpient->cLoeschCode)) {
        executeHook(
            HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
            ['oNewsletterEmpfaenger' => $recicpient]
        );

        Shop::Container()->getDB()->delete('tnewsletterempfaenger', 'cLoeschCode', $cLoeschCode);
        $hist               = new stdClass();
        $hist->kSprache     = $recicpient->kSprache;
        $hist->kKunde       = $recicpient->kKunde;
        $hist->cAnrede      = $recicpient->cAnrede;
        $hist->cVorname     = $recicpient->cVorname;
        $hist->cNachname    = $recicpient->cNachname;
        $hist->cEmail       = $recicpient->cEmail;
        $hist->cOptCode     = $recicpient->cOptCode;
        $hist->cLoeschCode  = $recicpient->cLoeschCode;
        $hist->cAktion      = 'Geloescht';
        $hist->dEingetragen = $recicpient->dEingetragen;
        $hist->dAusgetragen = 'now()';
        $hist->dOptCode     = '0000-00-00';
        $hist->cRegIp       = RequestHelper::getIP(); // IP of the current event-issuer

        Shop::Container()->getDB()->insert('tnewsletterempfaengerhistory', $hist);

        executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN,
            ['oNewsletterEmpfaengerHistory' => $hist]
        );
        $oBlacklist            = new stdClass();
        $oBlacklist->cMail     = $recicpient->cEmail;
        $oBlacklist->dErstellt = 'now()';
        Shop::Container()->getDB()->insert('tnewsletterempfaengerblacklist', $oBlacklist);

        $cHinweis = Shop::Lang()->get('newsletterDelete', 'messages');
    } else {
        $cFehler = Shop::Lang()->get('newsletterNocode', 'errorMessages');
    }
}
// Abonnieren
if (isset($_POST['abonnieren']) && (int)$_POST['abonnieren'] === 1) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';

    $oKunde            = new stdClass();
    $oKunde->cAnrede   = isset($_POST['cAnrede'])
        ? StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_POST['cAnrede'])))
        : null;
    $oKunde->cVorname  = isset($_POST['cVorname'])
        ? StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_POST['cVorname'])))
        : null;
    $oKunde->cNachname = isset($_POST['cNachname'])
        ? StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_POST['cNachname'])))
        : null;
    $oKunde->cEmail    = isset($_POST['cEmail'])
        ? StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_POST['cEmail'])))
        : null;
    $oKunde->cRegIp    = RequestHelper::getIP(); // IP of the current event-issuer

    if (!SimpleMail::checkBlacklist($oKunde->cEmail)) {
        Shop::Smarty()->assign('oPlausi', fuegeNewsletterEmpfaengerEin($oKunde, true));
        Shop::Container()->getDB()->delete('tnewsletterempfaengerblacklist', 'cMail', $oKunde->cEmail);
    } else {
        $cFehler .= StringHandler::filterEmailAddress($_POST['cEmail']) !== false
            ? (Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />')
            : (Shop::Lang()->get('invalidEmail') . '<br />');
    }

    Shop::Smarty()->assign('cPost_arr', StringHandler::filterXSS($_POST));
} elseif (isset($_POST['abonnieren']) && (int)$_POST['abonnieren'] === 2) {
    // weiterleitung vom Footer zu newsletter.php
    $oPlausi = new stdClass();
    $oPlausi->cPost_arr['cEmail'] = isset($_POST['cEmail'])
        ? StringHandler::filterXSS(Shop::Container()->getDB()->escape(strip_tags($_POST['cEmail'])))
        : null;
    Shop::Smarty()->assign('oPlausi', $oPlausi);
} elseif (isset($_POST['abmelden']) && (int)$_POST['abmelden'] === 1) { // Abmelden
    if (StringHandler::filterEmailAddress($_POST['cEmail']) !== false) {
        // Pruefen, ob Email bereits vorhanden
        $recicpient = Shop::Container()->getDB()->select(
            'tnewsletterempfaenger',
            'cEmail',
            StringHandler::htmlentities(StringHandler::filterXSS(Shop::Container()->getDB()->escape($_POST['cEmail'])))
        );

        if (!empty($recicpient->kNewsletterEmpfaenger)) {
            executeHook(
                HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
                ['oNewsletterEmpfaenger' => $recicpient]
            );
            // Newsletterempfaenger loeschen
            Shop::Container()->getDB()->delete(
                'tnewsletterempfaenger',
                'cEmail',
                StringHandler::htmlentities(StringHandler::filterXSS(Shop::Container()->getDB()->escape($_POST['cEmail'])))
            );
            $hist               = new stdClass();
            $hist->kSprache     = $recicpient->kSprache;
            $hist->kKunde       = $recicpient->kKunde;
            $hist->cAnrede      = $recicpient->cAnrede;
            $hist->cVorname     = $recicpient->cVorname;
            $hist->cNachname    = $recicpient->cNachname;
            $hist->cEmail       = $recicpient->cEmail;
            $hist->cOptCode     = $recicpient->cOptCode;
            $hist->cLoeschCode  = $recicpient->cLoeschCode;
            $hist->cAktion      = 'Geloescht';
            $hist->dEingetragen = $recicpient->dEingetragen;
            $hist->dAusgetragen = 'now()';
            $hist->dOptCode     = '0000-00-00';
            $hist->cRegIp       = RequestHelper::getIP(); // IP of the current event-issuer

            Shop::Container()->getDB()->insert('tnewsletterempfaengerhistory', $hist);

            executeHook(
                HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN,
                ['oNewsletterEmpfaengerHistory' => $hist]
            );
            // Blacklist
            $oBlacklist            = new stdClass();
            $oBlacklist->cMail     = $recicpient->cEmail;
            $oBlacklist->dErstellt = 'now()';
            Shop::Container()->getDB()->insert('tnewsletterempfaengerblacklist', $oBlacklist);

            $cHinweis = Shop::Lang()->get('newsletterDelete', 'messages');
        } else {
            $cFehler = Shop::Lang()->get('newsletterNoexists', 'errorMessages');
        }
    } else {
        $cFehler                             = Shop::Lang()->get('newsletterWrongemail', 'errorMessages');
        $oFehlendeAngaben                    = new stdClass();
        $oFehlendeAngaben->cUnsubscribeEmail = 1;
        Shop::Smarty()->assign('oFehlendeAngaben', $oFehlendeAngaben);
    }
} elseif (isset($_GET['show']) && (int)$_GET['show'] > 0) { // History anzeigen
    $cOption            = 'anzeigen';
    $kNewsletterHistory = (int)$_GET['show'];
    $oNewsletterHistory = Shop::Container()->getDB()->query(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, cHTMLStatic, cKundengruppeKey, 
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kNewsletterHistory = " . $kNewsletterHistory,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $kKundengruppe      = 0;
    if (isset($_SESSION['Kunde']->kKundengruppe) && (int)$_SESSION['Kunde']->kKundengruppe > 0) {
        $kKundengruppe = (int)$_SESSION['Kunde']->kKundengruppe;
    }
    if ($oNewsletterHistory->kNewsletterHistory > 0
        && pruefeNLHistoryKundengruppe($kKundengruppe, $oNewsletterHistory->cKundengruppeKey)
    ) {
        Shop::Smarty()->assign('oNewsletterHistory', $oNewsletterHistory);
    }
}
// Ist Kunde eingeloggt?
if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
    $oKunde = new Kunde($_SESSION['Kunde']->kKunde);
    Shop::Smarty()->assign('bBereitsAbonnent', pruefeObBereitsAbonnent($oKunde->kKunde))
        ->assign('oKunde', $oKunde);
}
$cCanonicalURL    = Shop::getURL() . '/newsletter.php';
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_NEWSLETTER);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;

Shop::Smarty()->assign('hinweis', $cHinweis)
    ->assign('fehler', $cFehler)
    ->assign('cOption', $cOption)
    ->assign('Link', $link)
    ->assign('nAnzeigeOrt', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
    ->assign('code_newsletter', false);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_NEWSLETTER_PAGE);

Shop::Smarty()->display('newsletter/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
