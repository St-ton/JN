<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'einstellungen_inc.php';

$oAccount->permission('ORDER_PAYMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

loadConfigLocalizations(true, true);

/** @global Smarty\JTLSmarty $smarty */
$db               = Shop::Container()->getDB();
$standardwaehrung = $db->select('twaehrung', 'cStandard', 'Y');
$hinweis          = '';
$step             = 'uebersicht';
if (RequestHelper::verifyGPCDataInt('checkNutzbar') === 1) {
    ZahlungsartHelper::checkPaymentMethodAvailability();
    $hinweis = 'Ihre Zahlungsarten wurden auf Nutzbarkeit geprüft.';
}
// reset log
if (($action = RequestHelper::verifyGPDataString('a')) !== ''
    && $action === 'logreset'
    && ($kZahlungsart = RequestHelper::verifyGPCDataInt('kZahlungsart')) > 0
    && FormHelper::validateToken()
) {
    $oZahlungsart = $db->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    if (isset($oZahlungsart->cModulId) && strlen($oZahlungsart->cModulId) > 0) {
        (new ZahlungsLog($oZahlungsart->cModulId))->loeschen();
        $hinweis = 'Der Fehlerlog von ' . $oZahlungsart->cName . ' wurde erfolgreich zurückgesetzt.';
    }
}
if ($action !== 'logreset' && RequestHelper::verifyGPCDataInt('kZahlungsart') > 0 && FormHelper::validateToken()) {
    $step = 'einstellen';
    if ($action === 'payments') {
        // Zahlungseingaenge
        $step = 'payments';
    } elseif ($action === 'log') {
        // Log einsehen
        $step = 'log';
    }
}

if (isset($_POST['einstellungen_bearbeiten'], $_POST['kZahlungsart'])
    && (int)$_POST['einstellungen_bearbeiten'] === 1 && (int)$_POST['kZahlungsart'] > 0 && FormHelper::validateToken()
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
        $cKundengruppen = StringHandler::createSSK($_POST['kKundengruppe']);
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
    if (strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
        $kPlugin     = \Plugin\Plugin::getIDByModuleID($zahlungsart->cModulId);
        $cModulId    = \Plugin\Plugin::getModuleIDByPluginID($kPlugin, $zahlungsart->cName);
        $Conf        = $db->query(
            "SELECT *
                FROM tplugineinstellungenconf
                WHERE cWertName LIKE '" . $cModulId . "\_%'
                AND cConf = 'Y' ORDER BY nSort",
            \DB\ReturnType::ARRAY_OF_OBJECTS
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
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
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
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            $db->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_ZAHLUNGSARTEN, $Conf[$i]->cWertName]
            );
            $db->insert('teinstellungen', $aktWert);
            localizeConfig($Conf[$i]);
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
        $zahlungsartSprache->cGebuehrname      = $_POST['cGebuehrname_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisText      = $_POST['cHinweisText_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisTextShop  = $_POST['cHinweisTextShop_' . $sprache->cISO];

        $db->delete(
            'tzahlungsartsprache',
            ['kZahlungsart', 'cISOSprache'],
            [(int)$_POST['kZahlungsart'],$sprache->cISO]
        );
        $db->insert('tzahlungsartsprache', $zahlungsartSprache);
    }

    Shop::Container()->getCache()->flushAll();
    $hinweis = 'Zahlungsart gespeichert.';
    $step    = 'uebersicht';
}

if ($step === 'einstellen') {
    $zahlungsart = $db->select(
        'tzahlungsart',
        'kZahlungsart',
        RequestHelper::verifyGPCDataInt('kZahlungsart')
    );
    if ($zahlungsart === null) {
        $step    = 'uebersicht';
        $hinweis = 'Zahlungsart nicht gefunden.';
    } else {
        // Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
        if ((int)$zahlungsart->nSOAP === 1 || (int)$zahlungsart->nCURL === 1 || (int)$zahlungsart->nSOCKETS === 1) {
            ZahlungsartHelper::activatePaymentMethod($zahlungsart);
        }
        // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
        if (strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
            $kPlugin     = \Plugin\Plugin::getIDByModuleID($zahlungsart->cModulId);
            $cModulId    = \Plugin\Plugin::getModuleIDByPluginID($kPlugin, $zahlungsart->cName);
            $Conf        = $db->query(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $cModulId . "\_%'
                    ORDER BY nSort",
                \DB\ReturnType::ARRAY_OF_OBJECTS
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
                $setValue = $db->select(
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
                    localizeConfigValues($Conf[$i], $Conf[$i]->ConfWerte);
                }
                $setValue = $db->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    CONF_ZAHLUNGSARTEN,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = $setValue->cWert ?? null;
                localizeConfig($Conf[$i]);
            }
        }

        $kundengruppen = $db->query(
            'SELECT *
                FROM tkundengruppe
                ORDER BY cName',
            \DB\ReturnType::ARRAY_OF_OBJECTS
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
    $kZahlungsart = RequestHelper::verifyGPCDataInt('kZahlungsart');
    $oZahlungsart = $db->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    $filterStandard = new Filter('standard');
    $filterStandard->addDaterangefield('Zeitraum', 'dDatum');
    $filterStandard->assemble();

    if (isset($oZahlungsart->cModulId) && strlen($oZahlungsart->cModulId) > 0) {
        $paginationPaymentLog = (new Pagination('standard'))
            ->setItemCount(ZahlungsLog::count($oZahlungsart->cModulId, -1, $filterStandard->getWhereSQL()))
            ->assemble();
        $paymentLogs = (new ZahlungsLog($oZahlungsart->cModulId))->holeLog(
            $paginationPaymentLog->getLimitSQL(),
            -1,
            $filterStandard->getWhereSQL()
        );

        $smarty->assign('paymentLogs', $paymentLogs)
               ->assign('paymentData', $oZahlungsart)
               ->assign('filterStandard', $filterStandard)
               ->assign('paginationPaymentLog', $paginationPaymentLog);
    }
} elseif ($step === 'payments') {
    if (isset($_POST['action'], $_POST['kEingang_arr'])
        && $_POST['action'] === 'paymentwawireset'
        && FormHelper::validateToken()
    ) {
        $kEingang_arr = $_POST['kEingang_arr'];
        array_walk($kEingang_arr, function (&$i) {
            $i = (int)$i;
        });
        $db->query(
            "UPDATE tzahlungseingang
                SET cAbgeholt = 'N'
                WHERE kZahlungseingang IN (" . implode(',', $kEingang_arr) . ")",
            \DB\ReturnType::QUERYSINGLE
        );
    }

    $kZahlungsart = RequestHelper::verifyGPCDataInt('kZahlungsart');

    $oFilter = new Filter('payments-' . $kZahlungsart);
    $oFilter->addTextfield(
        ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'],
        ['cBestellNr', 'fBetrag', 'cVorname', 'cMail', 'cHinweis']
    );
    $oFilter->addDaterangefield('Zeitraum', 'dZeit');
    $oFilter->assemble();

    $oZahlungsart        = $db->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
    $oZahlunseingang_arr = $db->query(
        'SELECT ze.*, b.kZahlungsart, b.cBestellNr, k.kKunde, k.cVorname, k.cNachname, k.cMail
            FROM tzahlungseingang AS ze
                JOIN tbestellung AS b
                    ON ze.kBestellung = b.kBestellung
                JOIN tkunde AS k
                    ON b.kKunde = k.kKunde
            WHERE b.kZahlungsart = ' . $kZahlungsart . ' ' .
        ($oFilter->getWhereSQL() !== '' ? 'AND ' . $oFilter->getWhereSQL() : '') . '
            ORDER BY dZeit DESC',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $oPagination         = (new Pagination('payments' . $kZahlungsart))
        ->setItemArray($oZahlunseingang_arr)
        ->assemble();
    $cryptoService = Shop::Container()->getCryptoService();
    foreach ($oZahlunseingang_arr as &$oZahlunseingang) {
        $oZahlunseingang->cNachname = $cryptoService->decryptXTEA($oZahlunseingang->cNachname);
        $oZahlunseingang->dZeit     = date_create($oZahlunseingang->dZeit)->format('d.m.Y\<\b\r\>H:i');
    }
    unset($oZahlunseingang);
    $smarty->assign('oZahlungsart', $oZahlungsart)
           ->assign('oZahlunseingang_arr', $oPagination->getPageItems())
           ->assign('oPagination', $oPagination)
           ->assign('oFilter', $oFilter);
}

if ($step === 'uebersicht') {
    $oZahlungsart_arr = $db->selectAll(
        'tzahlungsart',
        ['nActive', 'nNutzbar'],
        [1, 1],
        '*',
        'cAnbieter, cName, nSort, kZahlungsart'
    );
    foreach ($oZahlungsart_arr as $oZahlungsart) {
        $oZahlungsart->nEingangAnzahl = (int)$db->executeQueryPrepared(
            'SELECT COUNT(*) AS `nAnzahl`
            FROM `tzahlungseingang` AS ze
                JOIN `tbestellung` AS b ON ze.`kBestellung` = b.`kBestellung`
            WHERE b.`kZahlungsart` = :kzahlungsart',
            ['kzahlungsart' => $oZahlungsart->kZahlungsart],
            \DB\ReturnType::SINGLE_OBJECT
        )->nAnzahl;
        $oZahlungsart->nLogCount = ZahlungsLog::count($oZahlungsart->cModulId);
        // jtl-shop/issues#288
        $oZahlungsart->nErrorLogCount = ZahlungsLog::count($oZahlungsart->cModulId, JTLLOG_LEVEL_ERROR);
    }
    $smarty->assign('zahlungsarten', $oZahlungsart_arr);
}
$smarty->assign('step', $step)
       ->assign('waehrung', $standardwaehrung->cName)
       ->assign('cHinweis', $hinweis)
       ->display('zahlungsarten.tpl');
