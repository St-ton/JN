<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_CUSTOMERFIELDS_VIEW', true, true);

/** @global JTLSmarty $smarty */
$Einstellungen  = Shop::getSettings([CONF_KUNDENFELD]);
$customerFields = CustomerFields::getInstance((int)$_SESSION['kSprache']);
$cHinweis       = '';
$cFehler        = '';
$step           = 'uebersicht';

setzeSprache();

// Tabs
$smarty->assign('cTab', $cStep ?? null);
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}

// Einstellungen
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
    $cHinweis .= saveAdminSectionSettings(CONF_KUNDENFELD, $_POST);
} elseif (isset($_POST['kundenfelder']) && (int)$_POST['kundenfelder'] === 1 && validateToken()) { // Kundenfelder
    $success = true;
    if (isset($_POST['loeschen'])) {
        $kKundenfeld_arr = $_POST['kKundenfeld'];

        if (is_array($kKundenfeld_arr) && count($kKundenfeld_arr) > 0) {
            foreach ($kKundenfeld_arr as $kKundenfeld) {
                $success = $success && $customerFields->delete((int)$kKundenfeld);
            }
            if ($success) {
                $cHinweis .= "Die ausgew&auml;hlten Kundenfelder wurden erfolgreich gel&ouml;scht.<br />";
            } else {
                $cFehler .= 'Die ausgew&auml;hlten Kundenfelder konnten nicht gel&ouml;scht werden.<br />';
            }
        } else {
            $cFehler .= "Fehler: Bitte w&auml;hlen Sie mindestens ein Kundenfeld aus.<br />";
        }
    } elseif (isset($_POST['aktualisieren']) && validateToken()) { // Aktualisieren
        // Kundenfelder auslesen und in Smarty assignen
        foreach ($customerFields->getCustomerFields() as $customerField) {
            $customerField->nSort = (int)$_POST['nSort_' . $customerField->kKundenfeld];
            $success              = $success && $customerFields->save($customerField);
        }
        if ($success) {
            $cHinweis .= 'Ihre Kundenfelder wurden erfolgreich aktualisiert.<br />';
        } else {
            $cFehler .= 'Ihre Kundenfelder konnten nicht aktualisiert werden.<br />';
        }
    } else { // Speichern
        $customerField = (object)[
            'kKundenfeld' => (int)$_POST['kKundenfeld'],
            'kSprache'    => (int)$_SESSION['kSprache'],
            'cName'       => StringHandler::htmlspecialchars(StringHandler::filterXSS($_POST['cName']), ENT_COMPAT | ENT_HTML401),
            'cWawi'       => StringHandler::filterXSS(str_replace(['"',"'"], '', $_POST['cWawi'])),
            'cTyp'        => StringHandler::filterXSS($_POST['cTyp']),
            'nSort'       => (int)$_POST['nSort'],
            'nPflicht'    => (int)$_POST['nPflicht'],
            'nEditierbar' => (int)$_POST['nEdit'],
        ];

        $cfValues = $_POST['cfValues'] ?? null;

        // Plausi
        $oPlausi = new PlausiKundenfeld();
        $oPlausi->setPostVar($_POST);
        $oPlausi->doPlausi($customerField->cTyp, $customerField->kKundenfeld > 0);

        if (count($oPlausi->getPlausiVar()) === 0) {
            // Update?
            if ($customerFields->save($customerField, $cfValues)) {
                $cHinweis .= 'Ihr Kundenfeld wurde erfolgreich gespeichert.<br />';
            } else {
                $cFehler .= 'Ihr Kundenfeld konnte nicht gespeichert werden.<br />';
            }
        } else {
            $vWrongFields = $oPlausi->getPlausiVar();
            if (isset($vWrongFields['cName']) && 2 === $vWrongFields['cName']) {
                $cFehler = 'Ein Feld mit diesen Namen existiert bereits!';
            } else {
                $cFehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
            }
            $smarty->assign('xPlausiVar_arr', $oPlausi->getPlausiVar())
                   ->assign('xPostVar_arr', $oPlausi->getPostVar())
                   ->assign('kKundenfeld', $customerField->kKundenfeld);
        }
    }
} elseif (RequestHelper::verifyGPDataString('a') === 'edit') { // Editieren
    $kKundenfeld = RequestHelper::verifyGPCDataInt('kKundenfeld');

    if ($kKundenfeld > 0) {
        $customerField = $customerFields->getCustomerField($kKundenfeld);

        if ($customerField !== null) {
            $customerField->oKundenfeldWert_arr = $customerFields->getCustomerFieldValues($customerField);
            $smarty->assign('oKundenfeld', $customerField);
        }
    }
}

$oConfig_arr = Shop::Container()->getDB()->selectAll('teinstellungenconf', 'kEinstellungenSektion', CONF_KUNDENFELD, '*', 'nSort');
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
        $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }

    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        CONF_KUNDENFELD,
        'cName',
        $oConfig_arr[$i]->cWertName
    );

    $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
}
// Kundenfelder auslesen und in Smarty assignen
$oKundenfeld_arr = $customerFields->getCustomerFields();
// tkundenfeldwert nachschauen ob dort Werte fuer tkundenfeld enthalten sind
foreach ($oKundenfeld_arr as $i => $oKundenfeld) {
    if ($oKundenfeld->cTyp === 'auswahl') {
        $oKundenfeld_arr[$i]->oKundenfeldWert_arr = $customerFields->getCustomerFieldValues($oKundenfeld);
    }
}

// calculate the highest sort-order number (based on the 'ORDER BY' above)
// to recommend the user the next sort-order-value, instead of a placeholder
$oLastElement      = end($oKundenfeld_arr);
$nHighestSortValue = (false !== $oLastElement) ? $oLastElement->nSort : 0;
$oPreLastElement   = prev($oKundenfeld_arr);
if (false === $oPreLastElement) {
    $nHighestSortDiff = ($oLastElement === false || $oLastElement->nSort === 0) ? 1 : $oLastElement->nSort;
} else {
    $nHighestSortDiff = $oLastElement->nSort - $oPreLastElement->nSort;
}
reset($oKundenfeld_arr); // we leave the array in a safe state

$smarty->assign('oKundenfeld_arr', $oKundenfeld_arr)
       ->assign('nHighestSortValue', $nHighestSortValue)
       ->assign('nHighestSortDiff', $nHighestSortDiff)
       ->assign('oConfig_arr', $oConfig_arr)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('kundenfeld.tpl');

