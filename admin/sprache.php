<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global smarty
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('LANGUAGE_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$cHinweis = '';
$cFehler  = '';
$tab      = $_REQUEST['tab'] ?? 'variables';
$step     = 'overview';
setzeSprache();
$kSprache    = (int)$_SESSION['kSprache'];
$cISOSprache = $_SESSION['cISOSprache'];

if (isset($_FILES['csvfile']['tmp_name'])
    && FormHelper::validateToken()
    && RequestHelper::verifyGPDataString('importcsv') === 'langvars'
) {
    $csvFilename = $_FILES['csvfile']['tmp_name'];
    $importType  = RequestHelper::verifyGPCDataInt('importType');
    $res         = Shop::Lang()->import($csvFilename, $cISOSprache, $importType);

    if ($res === false) {
        $cFehler = 'Fehler beim Importieren der Datei';
    } else {
        $cHinweis = 'Es wurden ' . $res . ' Variablen aktualisiert';
    }
}

$oSprachISO            = Shop::Lang()->getLangIDFromIso($cISOSprache);
$kSprachISO            = $oSprachISO->kSprachISO ?? 0;
$oSpracheInstalled_arr = Shop::Lang()->getInstalled();
$oSpracheAvailable_arr = Shop::Lang()->getAvailable();
$oSektion_arr          = Shop::Lang()->getSections();
$bSpracheAktiv         = false;

if (count($oSpracheInstalled_arr) !== count($oSpracheAvailable_arr)) {
    $cHinweis = 'Es sind neue Sprache(n) verfügbar.';
}

foreach ($oSpracheInstalled_arr as $oSprache) {
    if ($oSprache->cISO === $cISOSprache) {
        $bSpracheAktiv = true;
        break;
    }
}

foreach ($oSpracheAvailable_arr as $oSprache) {
    $oSprache->bImported = in_array($oSprache, $oSpracheInstalled_arr);
}

if (isset($_REQUEST['action']) && FormHelper::validateToken()) {
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'newvar':
            // neue Variable erstellen
            $step                      = 'newvar';
            $oVariable                 = new stdClass();
            $oVariable->kSprachsektion = isset($_REQUEST['kSprachsektion']) ? (int)$_REQUEST['kSprachsektion'] : 1;
            $oVariable->cName          = $_REQUEST['cName'] ?? '';
            $oVariable->cWert_arr      = [];
            break;
        case 'delvar':
            // Variable loeschen
            Shop::Lang()->loesche($_GET['kSprachsektion'], $_GET['cName']);
            Shop::Cache()->flushTags([CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
            $cHinweis = 'Variable ' . $_GET['cName'] . ' wurde erfolgreich gelöscht.';
            break;
        case 'savevar':
            // neue Variable speichern
            $oVariable                 = new stdClass();
            $oVariable->kSprachsektion = (int)$_REQUEST['kSprachsektion'];
            $oVariable->cName          = $_REQUEST['cName'];
            $oVariable->cWert_arr      = $_REQUEST['cWert_arr'];
            $oVariable->cWertAlt_arr   = [];
            $oVariable->bOverwrite_arr = $_REQUEST['bOverwrite_arr'] ?? [];
            $cFehler_arr               = [];
            $oVariable->cSprachsektion = Shop::Container()->getDB()
                ->select('tsprachsektion', 'kSprachsektion', (int)$oVariable->kSprachsektion)
                ->cName;

            $oWertDB_arr = Shop::Container()->getDB()->queryPrepared(
                'SELECT s.cNameDeutsch AS cSpracheName, sw.cWert, si.cISO
                    FROM tsprachwerte AS sw
                        JOIN tsprachiso AS si
                            ON si.kSprachISO = sw.kSprachISO
                        JOIN tsprache AS s
                            ON s.cISO = si.cISO 
                    WHERE sw.cName = :cName
                        AND sw.kSprachsektion = :kSprachsektion',
                ['cName' => $oVariable->cName, 'kSprachsektion' => $oVariable->kSprachsektion],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($oWertDB_arr as $oWertDB) {
                $oVariable->cWertAlt_arr[$oWertDB->cISO] = $oWertDB->cWert;
            }

            if (!preg_match('/([\w\d]+)/', $oVariable->cName)) {
                $cFehler_arr[] = 'Die Variable darf nur aus Buchstaben und Zahlen bestehen und darf nicht leer sein.';
            }

            if (count($oVariable->bOverwrite_arr) !== count($oWertDB_arr)) {
                $cFehler_arr[] = 'Die Variable existiert bereits für folgende Sprachen: ' .
                    implode(' und ', array_map(function ($oWertDB) { return $oWertDB->cSpracheName; }, $oWertDB_arr)) .
                    '. Bitte wählen Sie aus, welche Versionen sie überschreiben möchten!';
            }

            if (count($cFehler_arr) > 0) {
                $cFehler = implode('<br>', $cFehler_arr);
                $step    = 'newvar';
            } else {
                foreach ($oVariable->cWert_arr as $cISO => $cWert) {
                    if (isset($oVariable->cWertAlt_arr[$cISO])) {
                        // alter Wert vorhanden
                        if ((int)$oVariable->bOverwrite_arr[$cISO] === 1) {
                            // soll ueberschrieben werden
                            Shop::Lang()
                                ->setzeSprache($cISO)
                                ->set($oVariable->kSprachsektion, $oVariable->cName, $cWert);
                        }
                    } else {
                        // kein alter Wert vorhanden
                        Shop::Lang()->fuegeEin($cISO, $oVariable->kSprachsektion, $oVariable->cName, $cWert);
                    }
                }

                Shop::Container()->getDB()->delete(
                    'tsprachlog',
                    ['cSektion', 'cName'],
                    [$oVariable->cSprachsektion, $oVariable->cName]
                );
                Shop::Cache()->flushTags([CACHING_GROUP_LANGUAGE]);
                Shop::Container()->getDB()->query(
                    'UPDATE tglobals SET dLetzteAenderung = NOW()',
                    \DB\ReturnType::DEFAULT
                );
            }

            break;
        case 'saveall':
            // geaenderte Variablen speichern
            $cChanged_arr = [];
            foreach ($_REQUEST['cWert_arr'] as $kSektion => $cSektionWert_arr) {
                foreach ($cSektionWert_arr as $cName => $cWert) {
                    if ((int)$_REQUEST['bChanged_arr'][$kSektion][$cName] === 1) {
                        // wurde geaendert => speichern
                        Shop::Lang()
                            ->setzeSprache($cISOSprache)
                            ->set((int)$kSektion, $cName, $cWert);
                        $cChanged_arr[] = $cName;
                    }
                }
            }

            Shop::Cache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);

            $cHinweis = count($cChanged_arr) > 0
                ? 'Variablen erfolgreich geändert: ' . implode(', ', $cChanged_arr)
                : 'Keine Variable wurde geändert';

            break;
        case 'clearlog':
            // Liste nicht gefundener Variablen leeren
            Shop::Lang()
                ->setzeSprache($cISOSprache)
                ->clearLog();
            Shop::Cache()->flushTags([CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
            $cHinweis .= 'Liste erfolgreich zurückgesetzt.';
            break;
        default:
            break;
    }
}

if ($step === 'newvar') {
    $smarty
        ->assign('oSektion_arr', $oSektion_arr)
        ->assign('oVariable', $oVariable)
        ->assign('oSprache_arr', $oSpracheAvailable_arr);
} elseif ($step === 'overview') {
    $oFilter                       = new Filter('langvars');
    $oSelectfield                  = $oFilter->addSelectfield('Sektion', 'sw.kSprachsektion', 0);
    $oSelectfield->bReloadOnChange = true;
    $oSelectfield->addSelectOption('(alle)', '', 0);

    foreach ($oSektion_arr as $oSektion) {
        $oSelectfield->addSelectOption($oSektion->cName, $oSektion->kSprachsektion, 4);
    }

    $oFilter->addTextfield(['Suche', 'Suchen im Variablennamen und im Inhalt'], ['sw.cName', 'sw.cWert'], 1);
    $oSelectfield = $oFilter->addSelectfield('System/eigene', 'bSystem', 0);
    $oSelectfield->addSelectOption('beide', '', 0);
    $oSelectfield->addSelectOption('nur System', '1', 4);
    $oSelectfield->addSelectOption('nur eigene', '0', 4);
    $oFilter->assemble();
    $cFilterSQL = $oFilter->getWhereSQL();

    $oWert_arr = Shop::Container()->getDB()->query(
        'SELECT sw.cName, sw.cWert, sw.cStandard, sw.bSystem, ss.kSprachsektion, ss.cName AS cSektionName
            FROM tsprachwerte AS sw
                JOIN tsprachsektion AS ss
                    ON ss.kSprachsektion = sw.kSprachsektion
            WHERE sw.kSprachISO = ' . (int)$kSprachISO . '
                ' . ($cFilterSQL !== '' ? 'AND ' . $cFilterSQL : ''),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    handleCsvExportAction(
        'langvars',
        $cISOSprache . '_' . date('YmdHis') . '.slf',
        $oWert_arr,
        ['cSektionName', 'cName', 'cWert', 'bSystem'],
        [],
        ';',
        false
    );

    $oPagination = (new Pagination('langvars'))
        ->setRange(4)
        ->setItemArray($oWert_arr)
        ->assemble();

    $oNotFound_arr = Shop::Container()->getDB()->query(
        'SELECT sl.*, ss.kSprachsektion
            FROM tsprachlog AS sl
                LEFT JOIN tsprachsektion AS ss
                    ON ss.cName = sl.cSektion
            WHERE kSprachISO = ' . (int)Shop::Lang()->kSprachISO,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty
        ->assign('oFilter', $oFilter)
        ->assign('oPagination', $oPagination)
        ->assign('oWert_arr', $oPagination->getPageItems())
        ->assign('bSpracheAktiv', $bSpracheAktiv)
        ->assign('oSprache_arr', $oSpracheAvailable_arr)
        ->assign('oNotFound_arr', $oNotFound_arr);
}

$smarty
    ->assign('tab', $tab)
    ->assign('step', $step)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('sprache.tpl');
