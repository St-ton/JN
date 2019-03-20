<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\PaymentMethod;
use JTL\Shop;
use JTL\Sprache;
use JTL\Helpers\Text;
use JTL\Checkout\ZahlungsLog;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;
use JTL\Plugin\Helper;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_PAYMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

Shop::Container()->getGetText()->loadConfigLocales(true, true);

/** @global \JTL\Smarty\JTLSmarty $smarty */
$db               = Shop::Container()->getDB();
$standardwaehrung = $db->select('twaehrung', 'cStandard', 'Y');
$step             = 'uebersicht';
$alertHelper      = Shop::Container()->getAlertService();
if (Request::verifyGPCDataInt('checkNutzbar') === 1) {
    PaymentMethod::checkPaymentMethodAvailability();
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPaymentMethodCheck'), 'successPaymentMethodCheck');
}
// reset log
if (($action = Request::verifyGPDataString('a')) !== ''
    && $action === 'logreset'
    && ($kZahlungsart = Request::verifyGPCDataInt('kZahlungsart')) > 0
    && Form::validateToken()
) {
    $method = $db->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    if (isset($method->cModulId) && mb_strlen($method->cModulId) > 0) {
        (new ZahlungsLog($method->cModulId))->loeschen();
        $alertHelper->addAlert(
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
    }
}

if (isset($_POST['einstellungen_bearbeiten'], $_POST['kZahlungsart'])
    && (int)$_POST['einstellungen_bearbeiten'] === 1 && (int)$_POST['kZahlungsart'] > 0 && Form::validateToken()
) {
    $step              = 'uebersicht';
    $zahlungsart       = $db->select(
        'tzahlungsart',
        'kZahlungsart',
        (int)$_POST['kZahlungsart']
    );
    $nMailSenden       = (int)$_POST['nMailSenden'];
    $nMailSendenStorno = (int)$_POST['nMailSendenStorno'];
    $nMailBits         = 0;
    if (is_array($_POST['kKundengruppe'])) {
        $cKundengruppen = Text::createSSK($_POST['kKundengruppe']);
        if (in_array(0, $_POST['kKundengruppe'])) {
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

    $nWaehrendBestellung = isset($_POST['nWaehrendBestellung'])
        ? (int)$_POST['nWaehrendBestellung']
        : $zahlungsart->nWaehrendBestellung;

    $upd                      = new stdClass();
    $upd->cKundengruppen      = $cKundengruppen;
    $upd->nSort               = (int)$_POST['nSort'];
    $upd->nMailSenden         = $nMailBits;
    $upd->cBild               = $_POST['cBild'];
    $upd->nWaehrendBestellung = $nWaehrendBestellung;
    $db->update('tzahlungsart', 'kZahlungsart', (int)$zahlungsart->kZahlungsart, $upd);
    // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
    if (mb_strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
        $kPlugin     = Helper::getIDByModuleID($zahlungsart->cModulId);
        $cModulId    = Helper::getModuleIDByPluginID($kPlugin, $zahlungsart->cName);
        $Conf        = $db->query(
            "SELECT *
                FROM tplugineinstellungenconf
                WHERE cWertName LIKE '" . $cModulId . "\_%'
                AND cConf = 'Y' ORDER BY nSort",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert          = new stdClass();
            $aktWert->kPlugin = $kPlugin;
            $aktWert->cName   = $Conf[$i]->cWertName;
            $aktWert->cWert   = $_POST[$Conf[$i]->cWertName];

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->delete(
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$kPlugin, $Conf[$i]->cWertName]
            );
            $db->insert('tplugineinstellungen', $aktWert);
        }
    } else {
        $Conf        = $db->selectAll(
            'teinstellungenconf',
            ['cModulId', 'cConf'],
            [$zahlungsart->cModulId, 'Y'],
            '*',
            'nSort'
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; ++$i) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = CONF_ZAHLUNGSARTEN;
            $aktWert->cModulId              = $zahlungsart->cModulId;

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = mb_substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_ZAHLUNGSARTEN, $Conf[$i]->cWertName]
            );
            $db->insert('teinstellungen', $aktWert);
            Shop::Container()->getGetText()->localizeConfig($Conf[$i]);
        }
    }

    $sprachen = Sprache::getAllLanguages();
    if (!isset($zahlungsartSprache)) {
        $zahlungsartSprache = new stdClass();
    }
    $zahlungsartSprache->kZahlungsart = (int)$_POST['kZahlungsart'];
    foreach ($sprachen as $sprache) {
        $zahlungsartSprache->cISOSprache = $sprache->cISO;
        $zahlungsartSprache->cName       = $zahlungsart->cName;
        if ($_POST['cName_' . $sprache->cISO]) {
            $zahlungsartSprache->cName = $_POST['cName_' . $sprache->cISO];
        }
        $zahlungsartSprache->cGebuehrname     = $_POST['cGebuehrname_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisText     = $_POST['cHinweisText_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisTextShop = $_POST['cHinweisTextShop_' . $sprache->cISO];

        $db->delete(
            'tzahlungsartsprache',
            ['kZahlungsart', 'cISOSprache'],
            [(int)$_POST['kZahlungsart'], $sprache->cISO]
        );
        $db->insert('tzahlungsartsprache', $zahlungsartSprache);
    }

    Shop::Container()->getCache()->flushAll();
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successPaymentMethodSave'), 'successSave');
    $step = 'uebersicht';
}

if ($step === 'einstellen') {
    $zahlungsart = $db->select(
        'tzahlungsart',
        'kZahlungsart',
        Request::verifyGPCDataInt('kZahlungsart')
    );
    if ($zahlungsart === null) {
        $step = 'uebersicht';
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorPaymentMethodNotFound'), 'errorNotFound');
    } else {
        // Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
        if ((int)$zahlungsart->nSOAP === 1 || (int)$zahlungsart->nCURL === 1 || (int)$zahlungsart->nSOCKETS === 1) {
            PaymentMethod::activatePaymentMethod($zahlungsart);
        }
        // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
        if (mb_strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
            $kPlugin     = Helper::getIDByModuleID($zahlungsart->cModulId);
            $cModulId    = Helper::getModuleIDByPluginID($kPlugin, $zahlungsart->cName);
            $Conf        = $db->query(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $cModulId . "\_%'
                    ORDER BY nSort",
                ReturnType::ARRAY_OF_OBJECTS
            );
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; ++$i) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = $db->selectAll(
                        'tplugineinstellungenconfwerte',
                        'kPluginEinstellungenConf',
                        (int)$Conf[$i]->kPluginEinstellungenConf,
                        '*',
                        'nSort'
                    );
                }
                $setValue                = $db->select(
                    'tplugineinstellungen',
                    'kPlugin',
                    (int)$Conf[$i]->kPlugin,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = $setValue->cWert;
            }
        } else {
            $Conf        = $db->selectAll(
                'teinstellungenconf',
                'cModulId',
                $zahlungsart->cModulId,
                '*',
                'nSort'
            );
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; ++$i) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = $db->selectAll(
                        'teinstellungenconfwerte',
                        'kEinstellungenConf',
                        (int)$Conf[$i]->kEinstellungenConf,
                        '*',
                        'nSort'
                    );
                    Shop::Container()->getGetText()->localizeConfigValues($Conf[$i], $Conf[$i]->ConfWerte);
                }
                $setValue                = $db->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    CONF_ZAHLUNGSARTEN,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = $setValue->cWert ?? null;
                Shop::Container()->getGetText()->localizeConfig($Conf[$i]);
            }
        }

        $kundengruppen = $db->query(
            'SELECT *
                FROM tkundengruppe
                ORDER BY cName',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $smarty->assign('Conf', $Conf)
               ->assign('zahlungsart', $zahlungsart)
               ->assign('kundengruppen', $kundengruppen)
               ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($zahlungsart))
               ->assign('sprachen', Sprache::getAllLanguages())
               ->assign('Zahlungsartname', getNames($zahlungsart->kZahlungsart))
               ->assign('Gebuehrname', getshippingTimeNames($zahlungsart->kZahlungsart))
               ->assign('cHinweisTexte_arr', getHinweisTexte($zahlungsart->kZahlungsart))
               ->assign('cHinweisTexteShop_arr', getHinweisTexteShop($zahlungsart->kZahlungsart))
               ->assign('ZAHLUNGSART_MAIL_EINGANG', ZAHLUNGSART_MAIL_EINGANG)
               ->assign('ZAHLUNGSART_MAIL_STORNO', ZAHLUNGSART_MAIL_STORNO);
    }
} elseif ($step === 'log') {
    $kZahlungsart = Request::verifyGPCDataInt('kZahlungsart');
    $method       = $db->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

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
    if (isset($_POST['action'], $_POST['kEingang_arr'])
        && $_POST['action'] === 'paymentwawireset'
        && Form::validateToken()
    ) {
        $kEingang_arr = $_POST['kEingang_arr'];
        array_walk($kEingang_arr, function (&$i) {
            $i = (int)$i;
        });
        $db->query(
            "UPDATE tzahlungseingang
                SET cAbgeholt = 'N'
                WHERE kZahlungseingang IN (" . implode(',', $kEingang_arr) . ')',
            ReturnType::QUERYSINGLE
        );
    }

    $kZahlungsart = Request::verifyGPCDataInt('kZahlungsart');

    $oFilter = new Filter('payments-' . $kZahlungsart);
    $oFilter->addTextfield(
        ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'],
        ['cBestellNr', 'fBetrag', 'cVorname', 'cMail', 'cHinweis']
    );
    $oFilter->addDaterangefield('Zeitraum', 'dZeit');
    $oFilter->assemble();

    $method        = $db->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
    $incoming      = $db->query(
        'SELECT ze.*, b.kZahlungsart, b.cBestellNr, k.kKunde, k.cVorname, k.cNachname, k.cMail
            FROM tzahlungseingang AS ze
                JOIN tbestellung AS b
                    ON ze.kBestellung = b.kBestellung
                JOIN tkunde AS k
                    ON b.kKunde = k.kKunde
            WHERE b.kZahlungsart = ' . $kZahlungsart . ' ' .
        ($oFilter->getWhereSQL() !== '' ? 'AND ' . $oFilter->getWhereSQL() : '') . '
            ORDER BY dZeit DESC',
        ReturnType::ARRAY_OF_OBJECTS
    );
    $oPagination   = (new Pagination('payments' . $kZahlungsart))
        ->setItemArray($incoming)
        ->assemble();
    $cryptoService = Shop::Container()->getCryptoService();
    foreach ($incoming as $item) {
        $item->cNachname = $cryptoService->decryptXTEA($item->cNachname);
        $item->dZeit     = date_create($item->dZeit)->format('d.m.Y\<\b\r\>H:i');
    }
    $smarty->assign('oZahlungsart', $method)
           ->assign('oZahlunseingang_arr', $oPagination->getPageItems())
           ->assign('oPagination', $oPagination)
           ->assign('oFilter', $oFilter);
}

if ($step === 'uebersicht') {
    $methods = $db->selectAll(
        'tzahlungsart',
        ['nActive', 'nNutzbar'],
        [1, 1],
        '*',
        'cAnbieter, cName, nSort, kZahlungsart'
    );
    foreach ($methods as $method) {
        $method->nEingangAnzahl = (int)$db->executeQueryPrepared(
            'SELECT COUNT(*) AS `nAnzahl`
            FROM `tzahlungseingang` AS ze
                JOIN `tbestellung` AS b ON ze.`kBestellung` = b.`kBestellung`
            WHERE b.`kZahlungsart` = :kzahlungsart',
            ['kzahlungsart' => $method->kZahlungsart],
            ReturnType::SINGLE_OBJECT
        )->nAnzahl;
        $method->nLogCount      = ZahlungsLog::count($method->cModulId);
        $method->nErrorLogCount = ZahlungsLog::count($method->cModulId, JTLLOG_LEVEL_ERROR);
    }
    $smarty->assign('zahlungsarten', $methods);
}
$smarty->assign('step', $step)
       ->assign('waehrung', $standardwaehrung->cName)
       ->display('zahlungsarten.tpl');
