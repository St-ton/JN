<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Request;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

Shop::setPageType(PAGE_NEWSLETTER);
$db           = Shop::Container()->getDB();
$smarty       = Shop::Smarty();
$links        = $db->selectAll('tlink', 'nLinkart', LINKTYP_NEWSLETTER);
$oLink        = new stdClass();
$oLink->kLink = 0;
foreach ($links as $l) {
    $customerGroupIDs = StringHandler::parseSSK($l->cKundengruppen);
    $ok               = array_reduce($customerGroupIDs, function ($c, $p) {
        return $c === true || $p === 'NULL' || (int)$p === \Session\Session::getCustomerGroup()->getID();
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
    $oLink                   = $db->select('tlink', 'nLinkart', LINKTYP_404);
    $bFileNotFound           = true;
    Shop::$kLink             = (int)$oLink->kLink;
    Shop::$bFileNotFound     = true;
    Shop::$is404             = true;
    $cParameter_arr['is404'] = true;

    return;
}

$cHinweis               = '';
$cFehler                = '';
$cCanonicalURL          = '';
$Einstellungen          = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_NEWSLETTER]);
$AktuelleKategorie      = new Kategorie(Request::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$option                 = 'eintragen';
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
if (isset($_GET['fc']) && strlen($_GET['fc']) > 0) {
    $option     = 'freischalten';
    $optCode    = StringHandler::htmlentities(StringHandler::filterXSS(strip_tags($_GET['fc'])));
    $recicpient = $db->select('tnewsletterempfaenger', 'cOptCode', $optCode);
    if (isset($recicpient->kNewsletterEmpfaenger) && $recicpient->kNewsletterEmpfaenger > 0) {
        executeHook(HOOK_NEWSLETTER_PAGE_EMPFAENGERFREISCHALTEN, ['oNewsletterEmpfaenger' => $recicpient]);
        $db->update(
            'tnewsletterempfaenger',
            'kNewsletterEmpfaenger',
            (int)$recicpient->kNewsletterEmpfaenger,
            (object)['nAktiv' => 1]
        );
        $db->query(
            'UPDATE tnewsletterempfaenger, tkunde
                SET tnewsletterempfaenger.kKunde = tkunde.kKunde
                WHERE tkunde.cMail = tnewsletterempfaenger.cEmail
                    AND tnewsletterempfaenger.kKunde = 0',
            \DB\ReturnType::DEFAULT
        );
        $upd           = new stdClass();
        $upd->dOptCode = 'NOW()';
        $upd->cOptIp   = Request::getRealIP();
        $db->update(
            'tnewsletterempfaengerhistory',
            ['cOptCode', 'cAktion'],
            [$optCode, 'Eingetragen'],
            $upd
        );
        $cHinweis = Shop::Lang()->get('newsletterActive', 'messages');
    } else {
        $cFehler = Shop::Lang()->get('newsletterNoactive', 'errorMessages');
    }
} elseif (isset($_GET['lc']) && strlen($_GET['lc']) > 0) { // Loeschcode wurde uebergeben
    $option     = 'loeschen';
    $deleteCode = StringHandler::htmlentities(strip_tags($_GET['lc']));
    $recicpient = $db->select('tnewsletterempfaenger', 'cLoeschCode', $deleteCode);
    if (!empty($recicpient->cLoeschCode)) {
        executeHook(
            HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
            ['oNewsletterEmpfaenger' => $recicpient]
        );

        $db->delete('tnewsletterempfaenger', 'cLoeschCode', $deleteCode);
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
        $hist->dAusgetragen = 'NOW()';
        $hist->dOptCode     = '_DBNULL_';
        $hist->cRegIp       = Request::getRealIP();
        $db->insert('tnewsletterempfaengerhistory', $hist);

        executeHook(HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN, [
            'oNewsletterEmpfaengerHistory' => $hist
        ]);
        $blacklist            = new stdClass();
        $blacklist->cMail     = $recicpient->cEmail;
        $blacklist->dErstellt = 'NOW()';
        $db->insert('tnewsletterempfaengerblacklist', $blacklist);

        $cHinweis = Shop::Lang()->get('newsletterDelete', 'messages');
    } else {
        $cFehler = Shop::Lang()->get('newsletterNocode', 'errorMessages');
    }
}
if (isset($_POST['abonnieren']) && (int)$_POST['abonnieren'] === 1) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
    $customer            = new stdClass();
    $customer->cAnrede   = isset($_POST['cAnrede'])
        ? StringHandler::filterXSS($db->escape(strip_tags($_POST['cAnrede'])))
        : null;
    $customer->cVorname  = isset($_POST['cVorname'])
        ? StringHandler::filterXSS($db->escape(strip_tags($_POST['cVorname'])))
        : null;
    $customer->cNachname = isset($_POST['cNachname'])
        ? StringHandler::filterXSS($db->escape(strip_tags($_POST['cNachname'])))
        : null;
    $customer->cEmail    = isset($_POST['cEmail'])
        ? StringHandler::filterXSS($db->escape(strip_tags($_POST['cEmail'])))
        : null;
    $customer->cRegIp    = Request::getRealIP();
    if (!SimpleMail::checkBlacklist($customer->cEmail)) {
        $smarty->assign('oPlausi', fuegeNewsletterEmpfaengerEin($customer, true));
        $db->delete('tnewsletterempfaengerblacklist', 'cMail', $customer->cEmail);
    } else {
        $cFehler .= StringHandler::filterEmailAddress($_POST['cEmail']) !== false
            ? (Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />')
            : (Shop::Lang()->get('invalidEmail') . '<br />');
    }
    $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST));
} elseif (isset($_POST['abonnieren']) && (int)$_POST['abonnieren'] === 2) {
    $oPlausi                      = new stdClass();
    $oPlausi->cPost_arr['cEmail'] = isset($_POST['cEmail'])
        ? StringHandler::filterXSS($db->escape(strip_tags($_POST['cEmail'])))
        : null;
    $smarty->assign('oPlausi', $oPlausi);
} elseif (isset($_POST['abmelden']) && (int)$_POST['abmelden'] === 1) {
    if (StringHandler::filterEmailAddress($_POST['cEmail']) !== false) {
        $recicpient = $db->select(
            'tnewsletterempfaenger',
            'cEmail',
            StringHandler::htmlentities(StringHandler::filterXSS($db->escape($_POST['cEmail'])))
        );
        if (!empty($recicpient->kNewsletterEmpfaenger)) {
            executeHook(
                HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
                ['oNewsletterEmpfaenger' => $recicpient]
            );
            $db->delete(
                'tnewsletterempfaenger',
                'cEmail',
                StringHandler::htmlentities(StringHandler::filterXSS($_POST['cEmail']))
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
            $hist->dAusgetragen = 'NOW()';
            $hist->dOptCode     = '_DBNULL_';
            $hist->cRegIp       = Request::getRealIP();
            $db->insert('tnewsletterempfaengerhistory', $hist);

            executeHook(
                HOOK_NEWSLETTER_PAGE_HISTORYEMPFAENGEREINTRAGEN,
                ['oNewsletterEmpfaengerHistory' => $hist]
            );
            $blacklist            = new stdClass();
            $blacklist->cMail     = $recicpient->cEmail;
            $blacklist->dErstellt = 'NOW()';
            $db->insert('tnewsletterempfaengerblacklist', $blacklist);

            $cHinweis = Shop::Lang()->get('newsletterDelete', 'messages');
        } else {
            $cFehler = Shop::Lang()->get('newsletterNoexists', 'errorMessages');
        }
    } else {
        $cFehler = Shop::Lang()->get('newsletterWrongemail', 'errorMessages');
        $smarty->assign('oFehlendeAngaben', (object)['cUnsubscribeEmail' => 1]);
    }
} elseif (isset($_GET['show']) && (int)$_GET['show'] > 0) {
    $kKundengruppe = \Session\Session::getCustomer()->getID();
    $option        = 'anzeigen';
    $history       = $db->query(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, cHTMLStatic, cKundengruppeKey,
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kNewsletterHistory = " . (int)$_GET['show'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    if ($history->kNewsletterHistory > 0 && pruefeNLHistoryKundengruppe($kKundengruppe, $history->cKundengruppeKey)) {
        $smarty->assign('oNewsletterHistory', $history);
    }
}
if (\Session\Session::getCustomer()->getID() > 0) {
    $customer = new Kunde(\Session\Session::getCustomer()->getID());
    $smarty->assign('bBereitsAbonnent', pruefeObBereitsAbonnent($customer->kKunde))
           ->assign('oKunde', $customer);
}
$cCanonicalURL    = Shop::getURL() . '/newsletter.php';
$oMeta            = $linkHelper->buildSpecialPageMeta(LINKTYP_NEWSLETTER);
$cMetaTitle       = $oMeta->cTitle;
$cMetaDescription = $oMeta->cDesc;
$cMetaKeywords    = $oMeta->cKeywords;

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('cOption', $option)
       ->assign('Link', $link)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
       ->assign('code_newsletter', false);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_NEWSLETTER_PAGE);

$smarty->display('newsletter/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
