<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\Alert\Alert;
use JTL\Customer\Kunde;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Session\Frontend;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

Shop::setPageType(PAGE_NEWSLETTER);
$db           = Shop::Container()->getDB();
$smarty       = Shop::Smarty();
$alertHelper  = Shop::Container()->getAlertService();
$links        = $db->selectAll('tlink', 'nLinkart', LINKTYP_NEWSLETTER);
$oLink        = new stdClass();
$oLink->kLink = 0;
foreach ($links as $l) {
    $customerGroupIDs = Text::parseSSK($l->cKundengruppen);
    $ok               = array_reduce($customerGroupIDs, function ($c, $p) {
        return $c === true || $p === 'NULL' || (int)$p === Frontend::getCustomerGroup()->getID();
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
    $oLink               = $db->select('tlink', 'nLinkart', LINKTYP_404);
    $bFileNotFound       = true;
    Shop::$kLink         = (int)$oLink->kLink;
    Shop::$bFileNotFound = true;
    Shop::$is404         = true;

    return;
}

$cCanonicalURL = '';
$option        = 'eintragen';
if (isset($_GET['fc']) && mb_strlen($_GET['fc']) > 0) {
    $option     = 'freischalten';
    $optCode    = Text::htmlentities(Text::filterXSS(strip_tags($_GET['fc'])));
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
            ReturnType::DEFAULT
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
        $alertHelper->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('newsletterActive', 'messages'),
            'newsletterActive'
        );
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterNoactive', 'errorMessages'),
            'newsletterNoactive'
        );
    }
} elseif (isset($_GET['lc']) && mb_strlen($_GET['lc']) > 0) { // Loeschcode wurde uebergeben
    $option     = 'loeschen';
    $deleteCode = Text::htmlentities(strip_tags($_GET['lc']));
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

        $alertHelper->addAlert(
            Alert::TYPE_NOTE,
            Shop::Lang()->get('newsletterDelete', 'messages'),
            'newsletterDelete'
        );
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterNocode', 'errorMessages'),
            'newsletterNocode'
        );
    }
}
if (Request::verifyGPCDataInt('abonnieren') > 0) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'newsletter_inc.php';
    $customer            = new stdClass();
    $customer->cAnrede   = isset($_POST['cAnrede'])
        ? Text::filterXSS($db->escape(strip_tags($_POST['cAnrede'])))
        : Frontend::getCustomer()->cAnrede;
    $customer->cVorname  = isset($_POST['cVorname'])
        ? Text::filterXSS($db->escape(strip_tags($_POST['cVorname'])))
        : Frontend::getCustomer()->cVorname;
    $customer->cNachname = isset($_POST['cNachname'])
        ? Text::filterXSS($db->escape(strip_tags($_POST['cNachname'])))
        : Frontend::getCustomer()->cNachname;
    $customer->cEmail    = isset($_POST['cEmail'])
        ? Text::filterXSS($db->escape(strip_tags($_POST['cEmail'])))
        : null;
    $customer->cRegIp    = Request::getRealIP();
    if (!SimpleMail::checkBlacklist($customer->cEmail)) {
        $smarty->assign('oPlausi', fuegeNewsletterEmpfaengerEin($customer, true));
        $db->delete('tnewsletterempfaengerblacklist', 'cMail', $customer->cEmail);
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Text::filterEmailAddress($_POST['cEmail']) !== false
                ? (Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />')
                : (Shop::Lang()->get('invalidEmail') . '<br />'),
            'newsletterBlockedInvalid'
        );
    }
    $smarty->assign('cPost_arr', Text::filterXSS($_POST));
} elseif (isset($_POST['abmelden']) && (int)$_POST['abmelden'] === 1) {
    if (Text::filterEmailAddress($_POST['cEmail']) !== false) {
        $recicpient = $db->select(
            'tnewsletterempfaenger',
            'cEmail',
            Text::htmlentities(Text::filterXSS($db->escape($_POST['cEmail'])))
        );
        if (!empty($recicpient->kNewsletterEmpfaenger)) {
            executeHook(
                HOOK_NEWSLETTER_PAGE_EMPFAENGERLOESCHEN,
                ['oNewsletterEmpfaenger' => $recicpient]
            );
            $db->delete(
                'tnewsletterempfaenger',
                'cEmail',
                Text::htmlentities(Text::filterXSS($_POST['cEmail']))
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

            $alertHelper->addAlert(
                Alert::TYPE_NOTE,
                Shop::Lang()->get('newsletterDelete', 'messages'),
                'newsletterDelete'
            );
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                Shop::Lang()->get('newsletterNoexists', 'errorMessages'),
                'newsletterNoexists'
            );
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
            'newsletterWrongemail'
        );
        $smarty->assign('oFehlendeAngaben', (object)['cUnsubscribeEmail' => 1]);
    }
} elseif (isset($_GET['show']) && (int)$_GET['show'] > 0) {
    $customerGroupID = Frontend::getCustomer()->getID();
    $option          = 'anzeigen';
    $history         = $db->query(
        "SELECT kNewsletterHistory, nAnzahl, cBetreff, cHTMLStatic, cKundengruppeKey,
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kNewsletterHistory = " . (int)$_GET['show'],
        ReturnType::SINGLE_OBJECT
    );
    if ($history->kNewsletterHistory > 0 && pruefeNLHistoryKundengruppe($customerGroupID, $history->cKundengruppeKey)) {
        $smarty->assign('oNewsletterHistory', $history);
    }
}
if (Frontend::getCustomer()->getID() > 0) {
    $customer = new Kunde(Frontend::getCustomer()->getID());
    $smarty->assign('bBereitsAbonnent', pruefeObBereitsAbonnent($customer->kKunde))
           ->assign('oKunde', $customer);
}
$cCanonicalURL = $linkHelper->getStaticRoute('newsletter.php');

$smarty->assign('cOption', $option)
       ->assign('Link', $link)
       ->assign('nAnzeigeOrt', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
       ->assign('code_newsletter', false);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_NEWSLETTER_PAGE);

$smarty->display('newsletter/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
