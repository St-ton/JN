<?php declare(strict_types=1);

use JTL\Alert\Alert;
use JTL\Backend\Settings\SectionFactory;
use JTL\Backend\Settings\Sections\PluginPaymentMethod;
use JTL\Checkout\Zahlungsart;
use JTL\Checkout\ZahlungsLog;
use JTL\DB\SqlObject;
use JTL\Helpers\Form;
use JTL\Helpers\PaymentMethod;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Recommendation\Manager;
use JTL\Backend\Settings\Manager as SettingsManager;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('ORDER_PAYMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';

Shop::Container()->getGetText()->loadConfigLocales(true, true);

$db              = Shop::Container()->getDB();
$defaultCurrency = $db->select('twaehrung', 'cStandard', 'Y');
$step            = 'uebersicht';
$alertService    = Shop::Container()->getAlertService();
$recommendations = new Manager($alertService, Manager::SCOPE_BACKEND_PAYMENT_PROVIDER);
$filteredPost    = Text::filterXSS($_POST);
$sectionFactory  = new SectionFactory();
$getText         = Shop::Container()->getGetText();
$settingManager  = new SettingsManager($db, $smarty, $oAccount, $getText, $alertService);
if (Request::verifyGPCDataInt('checkNutzbar') === 1) {
    PaymentMethod::checkPaymentMethodAvailability();
    $alertService->addAlert(Alert::TYPE_SUCCESS, __('successPaymentMethodCheck'), 'successPaymentMethodCheck');
}
// reset log
if (($action = Request::verifyGPDataString('a')) !== ''
    && $action === 'logreset'
    && ($paymentMethodID = Request::verifyGPCDataInt('kZahlungsart')) > 0
    && Form::validateToken()
) {
    $method = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

    if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
        (new ZahlungsLog($method->cModulId))->loeschen();
        $alertService->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successLogReset'), $method->cName),
            'successLogReset'
        );
    }
}
if ($action !== 'logreset' && Request::verifyGPCDataInt('kZahlungsart') > 0 && Form::validateToken()) {
    $step = 'einstellen';
    if ($action === 'payments') {
        $step = 'payments';
    } elseif ($action === 'log') {
        $step = 'log';
    } elseif ($action === 'del') {
        $step = 'delete';
    }
}
if (Request::postInt('einstellungen_bearbeiten') === 1
    && Request::postInt('kZahlungsart') > 0
    && Form::validateToken()
) {
    $step          = 'uebersicht';
    $paymentMethod = $db->select(
        'tzahlungsart',
        'kZahlungsart',
        Request::postInt('kZahlungsart')
    );
    if ($paymentMethod !== null) {
        $paymentMethod->kZahlungsart        = (int)$paymentMethod->kZahlungsart;
        $paymentMethod->nSort               = (int)$paymentMethod->nSort;
        $paymentMethod->nWaehrendBestellung = (int)$paymentMethod->nWaehrendBestellung;
    }
    $nMailSenden       = Request::postInt('nMailSenden');
    $nMailSendenStorno = Request::postInt('nMailSendenStorno');
    $nMailBits         = 0;
    if (is_array($filteredPost['kKundengruppe'])) {
        $filteredPost['kKundengruppe'] = array_map('\intval', $filteredPost['kKundengruppe']);
        $cKundengruppen                = Text::createSSK($filteredPost['kKundengruppe']);
        if (in_array(0, $filteredPost['kKundengruppe'], true)) {
            unset($cKundengruppen);
        }
    }
    if ($nMailSenden) {
        $nMailBits |= ZAHLUNGSART_MAIL_EINGANG;
    }
    if ($nMailSendenStorno) {
        $nMailBits |= ZAHLUNGSART_MAIL_STORNO;
    }
    if (!isset($cKundengruppen)) {
        $cKundengruppen = '';
    }

    $duringCheckout = Request::postInt('nWaehrendBestellung', $paymentMethod->nWaehrendBestellung);

    $upd                      = new stdClass();
    $upd->cKundengruppen      = $cKundengruppen;
    $upd->nSort               = Request::postInt('nSort');
    $upd->nMailSenden         = $nMailBits;
    $upd->cBild               = $filteredPost['cBild'];
    $upd->nWaehrendBestellung = $duringCheckout;
    $db->update('tzahlungsart', 'kZahlungsart', $paymentMethod->kZahlungsart, $upd);
    // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
    if (mb_strpos($paymentMethod->cModulId, 'kPlugin_') !== false) {
        $kPlugin = PluginHelper::getIDByModuleID($paymentMethod->cModulId);
        $sql     = new SqlObject();
        $sql->setWhere(" cWertName LIKE :mid 
                AND cConf = 'Y'");
        $sql->addParam('mid', $paymentMethod->cModulId . '\_%');
        $section         = new PluginPaymentMethod($settingManager, CONF_ZAHLUNGSARTEN);
        $post            = $_POST;
        $post['kPlugin'] = $kPlugin;
        $section->update($post);
    } else {
        $section = $sectionFactory->getSection(CONF_ZAHLUNGSARTEN, $settingManager);
        $sql     = new SqlObject();
        $sql->setWhere(' ec.cModulId = :mid');
        $sql->addParam('mid', $paymentMethod->cModulId);
        $section->load($sql);
        $post             = $_POST;
        $post['cModulId'] = $paymentMethod->cModulId;
        $section->update($post);
    }
    $localized               = new stdClass();
    $localized->kZahlungsart = Request::postInt('kZahlungsart');
    foreach (LanguageHelper::getAllLanguages(0, true) as $lang) {
        $langCode               = $lang->getCode();
        $localized->cISOSprache = $langCode;
        $localized->cName       = $paymentMethod->cName;
        if ($filteredPost['cName_' . $langCode]) {
            $localized->cName = $filteredPost['cName_' . $langCode];
        }
        $localized->cGebuehrname     = $filteredPost['cGebuehrname_' . $langCode];
        $localized->cHinweisText     = $filteredPost['cHinweisText_' . $langCode];
        $localized->cHinweisTextShop = $filteredPost['cHinweisTextShop_' . $langCode];

        $db->delete(
            'tzahlungsartsprache',
            ['kZahlungsart', 'cISOSprache'],
            [Request::postInt('kZahlungsart'), $langCode]
        );
        $db->insert('tzahlungsartsprache', $localized);
    }

    Shop::Container()->getCache()->flushAll();
    $alertService->addAlert(Alert::TYPE_SUCCESS, __('successPaymentMethodSave'), 'successSave');
    $step = 'uebersicht';
}

if ($step === 'einstellen') {
    $paymentMethod = new Zahlungsart(Request::verifyGPCDataInt('kZahlungsart'));
    if ($paymentMethod->getZahlungsart() === null) {
        $step = 'uebersicht';
        $alertService->addAlert(Alert::TYPE_ERROR, __('errorPaymentMethodNotFound'), 'errorNotFound');
    } else {
        $paymentMethod->cName = Text::filterXSS($paymentMethod->cName);
        PaymentMethod::activatePaymentMethod($paymentMethod);
        // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
        if (mb_strpos($paymentMethod->cModulId, 'kPlugin_') !== false) {
            $sql = new SqlObject();
            $sql->setWhere(" cWertName LIKE :mid 
                AND cConf = 'Y'");
            $sql->addParam('mid', $paymentMethod->cModulId . '\_%');
            $section = new PluginPaymentMethod($settingManager, CONF_ZAHLUNGSARTEN);
            $section->load($sql);
            $conf = $section->getItems();
        } else {
            $section = $sectionFactory->getSection(CONF_ZAHLUNGSARTEN, $settingManager);
            $sql     = new SqlObject();
            $sql->setWhere(' ec.cModulId = :mid');
            $sql->addParam('mid', $paymentMethod->cModulId);
            $section->load($sql);
            $conf = $section->getItems();
        }

        $customerGroups = $db->getObjects(
            'SELECT *
                FROM tkundengruppe
                ORDER BY cName'
        );
        $smarty->assign('configItems', $conf)
            ->assign('zahlungsart', $paymentMethod)
            ->assign('kundengruppen', $customerGroups)
            ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($paymentMethod))
            ->assign('Zahlungsartname', getNames($paymentMethod->kZahlungsart))
            ->assign('Gebuehrname', getshippingTimeNames($paymentMethod->kZahlungsart))
            ->assign('cHinweisTexte_arr', getHinweisTexte($paymentMethod->kZahlungsart))
            ->assign('cHinweisTexteShop_arr', getHinweisTexteShop($paymentMethod->kZahlungsart))
            ->assign('ZAHLUNGSART_MAIL_EINGANG', ZAHLUNGSART_MAIL_EINGANG)
            ->assign('ZAHLUNGSART_MAIL_STORNO', ZAHLUNGSART_MAIL_STORNO);
    }
} elseif ($step === 'log') {
    $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
    $method          = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);

    $filterStandard = new Filter('standard');
    $filterStandard->addDaterangefield('Zeitraum', 'dDatum');
    $filterStandard->assemble();

    if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
        $paginationPaymentLog = (new Pagination('standard'))
            ->setItemCount(ZahlungsLog::count($method->cModulId, -1, $filterStandard->getWhereSQL()))
            ->assemble();
        $paymentLogs          = (new ZahlungsLog($method->cModulId))->holeLog(
            $paginationPaymentLog->getLimitSQL(),
            -1,
            $filterStandard->getWhereSQL()
        );

        $smarty->assign('paymentLogs', $paymentLogs)
            ->assign('paymentData', $method)
            ->assign('filterStandard', $filterStandard)
            ->assign('paginationPaymentLog', $paginationPaymentLog);
    }
} elseif ($step === 'payments') {
    if (isset($filteredPost['action'], $filteredPost['kEingang_arr'])
        && $filteredPost['action'] === 'paymentwawireset'
        && Form::validateToken()
    ) {
        $db->query(
            "UPDATE tzahlungseingang
                SET cAbgeholt = 'N'
                WHERE kZahlungseingang IN (" . implode(',', array_map('\intval', $filteredPost['kEingang_arr'])) . ')'
        );
    }

    $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');

    $filter = new Filter('payments-' . $paymentMethodID);
    $filter->addTextfield(
        ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'],
        ['cBestellNr', 'fBetrag', 'cVorname', 'cMail', 'cHinweis']
    );
    $filter->addDaterangefield('Zeitraum', 'dZeit');
    $filter->assemble();

    $method        = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
    $incoming      = $db->getObjects(
        'SELECT ze.*, b.kZahlungsart, b.cBestellNr, k.kKunde, k.cVorname, k.cNachname, k.cMail
            FROM tzahlungseingang AS ze
                JOIN tbestellung AS b
                    ON ze.kBestellung = b.kBestellung
                JOIN tkunde AS k
                    ON b.kKunde = k.kKunde
            WHERE b.kZahlungsart = :pmid ' .
        ($filter->getWhereSQL() !== '' ? 'AND ' . $filter->getWhereSQL() : '') . '
            ORDER BY dZeit DESC',
        ['pmid' => $paymentMethodID]
    );
    $pagination    = (new Pagination('payments' . $paymentMethodID))
        ->setItemArray($incoming)
        ->assemble();
    $cryptoService = Shop::Container()->getCryptoService();
    foreach ($incoming as $item) {
        $item->cNachname = $cryptoService->decryptXTEA($item->cNachname);
        $item->dZeit     = date_create($item->dZeit)->format('d.m.Y\<\b\r\>H:i');
    }
    $smarty->assign('oZahlungsart', $method)
        ->assign('oZahlunseingang_arr', $pagination->getPageItems())
        ->assign('pagination', $pagination)
        ->assign('oFilter', $filter);
} elseif ($step === 'delete') {
    $paymentMethodID = Request::verifyGPCDataInt('kZahlungsart');
    $method          = $db->select('tzahlungsart', 'kZahlungsart', $paymentMethodID);
    $pluginID        = PluginHelper::getIDByModuleID($method->cModulId);
    if ($pluginID > 0) {
        try {
            Shop::Container()->getGetText()->loadPluginLocale(
                'base',
                PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
            );
            $alertService->addAlert(
                Alert::TYPE_WARNING,
                sprintf(__('Payment method can not been deleted'), __($method->cName)),
                'paymentcantdel',
                ['saveInSession' => true]
            );
        } catch (InvalidArgumentException $e) {
            // Only delete if plugin is not installed
            $db->delete('tversandartzahlungsart', 'kZahlungsart', $paymentMethodID);
            $db->delete('tzahlungsartsprache', 'kZahlungsart', $paymentMethodID);
            $db->delete('tzahlungsart', 'kZahlungsart', $paymentMethodID);
            $alertService->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('Payment method has been deleted'), $method->cName),
                'paymentdeleted',
                ['saveInSession' => true]
            );
        }
    }
    header('Location: ' . Shop::getAdminURL(true) . '/zahlungsarten.php');
    exit;
}

if ($step === 'uebersicht') {
    $methods = $db->selectAll(
        'tzahlungsart',
        ['nActive', 'nNutzbar'],
        [1, 1],
        '*',
        'cAnbieter, cName, nSort, kZahlungsart, cModulId'
    );
    foreach ($methods as $method) {
        $method->markedForDelete = false;

        $pluginID = PluginHelper::getIDByModuleID($method->cModulId);
        if ($pluginID > 0) {
            try {
                Shop::Container()->getGetText()->loadPluginLocale(
                    'base',
                    PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID)
                );
            } catch (InvalidArgumentException $e) {
                $method->markedForDelete = true;
                $alertService->addAlert(
                    Alert::TYPE_WARNING,
                    sprintf(__('Plugin for payment method not found'), $method->cName, $method->cAnbieter),
                    'notfound_' . $pluginID
                );
            }
        }
        $method->nEingangAnzahl = (int)$db->getSingleObject(
            'SELECT COUNT(*) AS `cnt`
                FROM `tzahlungseingang` AS ze
                    JOIN `tbestellung` AS b ON ze.`kBestellung` = b.`kBestellung`
                WHERE b.`kZahlungsart` = :kzahlungsart',
            ['kzahlungsart' => $method->kZahlungsart]
        )->cnt;
        $method->nLogCount      = ZahlungsLog::count($method->cModulId);
        $method->nErrorLogCount = ZahlungsLog::count($method->cModulId, JTLLOG_LEVEL_ERROR);
        $method->cName          = __($method->cName);
        $method->cAnbieter      = __($method->cAnbieter);
    }
    $smarty->assign('zahlungsarten', $methods);
}
$smarty->assign('step', $step)
    ->assign('waehrung', $defaultCurrency->cName)
    ->assign('recommendations', $recommendations)
    ->display('zahlungsarten.tpl');
