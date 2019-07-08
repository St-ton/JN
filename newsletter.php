<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\Alert\Alert;
use JTL\Customer\Kunde;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Session\Frontend;
use JTL\Optin\OptinNewsletter;
use JTL\Optin\OptinRefData;
use JTL\Optin\Optin;

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
if (Request::verifyGPCDataInt('abonnieren') > 0) {
    if (Text::filterEmailAddress($_POST['cEmail']) !== false) {
        $refData = (new OptinRefData())
            ->setSalutation(
                isset($_POST['cAnrede']) ? Text::filterXSS($db->escape(strip_tags($_POST['cAnrede']))) : ''
            )
            ->setFirstName(
                isset($_POST['cVorname']) ? Text::filterXSS($db->escape(strip_tags($_POST['cVorname']))) : ''
            )
            ->setLastName(
                isset($_POST['cNachname']) ? Text::filterXSS($db->escape(strip_tags($_POST['cNachname']))) : ''
            )
            ->setEmail(
                isset($_POST['cEmail']) ? Text::filterXSS($db->escape(strip_tags($_POST['cEmail']))) : ''
            )
            ->setLanguageID(Shop::getLanguage())
            ->setRealIP(Request::getRealIP());
        try {
            (new Optin(OptinNewsletter::class))
                ->getOptinInstance()
                ->createOptin($refData)
                ->sendActivationMail();
        } catch (Exception $e) {
            Shop::Container()->getLogService()->error($e->getMessage());
        }
    } else {
        $alertHelper->addAlert(
            Alert::TYPE_ERROR,
            Shop::Lang()->get('newsletterWrongemail', 'errorMessages'),
            'newsletterWrongemail'
        );
    }
    $smarty->assign('cPost_arr', Text::filterXSS($_POST));
} elseif (Request::verifyGPCDataInt('abmelden') === 1) {
    if (Text::filterEmailAddress($_POST['cEmail']) !== false) {
        try {
            (new Optin(OptinNewsletter::class))
                ->setEmail(Text::htmlentities(Text::filterXSS($db->escape($_POST['cEmail']))))
                ->setAction(Optin::DELETE_CODE)
                ->handleOptin();
        } catch (Exception $e) {
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
