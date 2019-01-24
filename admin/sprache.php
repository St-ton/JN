<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global smarty
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Filter;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('LANGUAGE_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$cHinweis = '';
$cFehler  = '';
$tab      = $_REQUEST['tab'] ?? 'variables';
$step     = 'overview';
$lang     = Shop::Lang();
setzeSprache();
$kSprache    = (int)$_SESSION['kSprache'];
$cISOSprache = $_SESSION['cISOSprache'];

if (isset($_FILES['csvfile']['tmp_name'])
    && Form::validateToken()
    && Request::verifyGPDataString('importcsv') === 'langvars'
) {
    $csvFilename = $_FILES['csvfile']['tmp_name'];
    $importType  = Request::verifyGPCDataInt('importType');
    $res         = $lang->import($csvFilename, $cISOSprache, $importType);

    if ($res === false) {
        $cFehler = __('errorImport');
    } else {
        $cHinweis = sprintf(__('successImport'), $res);
    }
}

$oSprachISO         = $lang->getLangIDFromIso($cISOSprache);
$kSprachISO         = $oSprachISO->kSprachISO ?? 0;
$installedLanguages = $lang->getInstalled();
$availableLanguages = $lang->getAvailable();
$oSektion_arr       = $lang->getSections();
$bSpracheAktiv      = false;

if (count($installedLanguages) !== count($availableLanguages)) {
    $cHinweis = __('newLangAvailable');
}

foreach ($installedLanguages as $oSprache) {
    if ($oSprache->cISO === $cISOSprache) {
        $bSpracheAktiv = true;
        break;
    }
}

foreach ($availableLanguages as $oSprache) {
    $oSprache->bImported = in_array($oSprache, $installedLanguages);
}

if (isset($_REQUEST['action']) && Form::validateToken()) {
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
            $lang->loesche($_GET['kSprachsektion'], $_GET['cName']);
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
            $cHinweis = sprintf(__('successVarRemove'), $_GET['cName']);
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
                                             ->select(
                                                 'tsprachsektion',
                                                 'kSprachsektion',
                                                 (int)$oVariable->kSprachsektion
                                             )
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
                $cFehler_arr[] = __('errorVarFormat');
            }

            if (count($oVariable->bOverwrite_arr) !== count($oWertDB_arr)) {
                $cFehler_arr[] = sprintf(
                    __('errorVarExistsForLang'),
                    implode(
                        ', ',
                        array_map(function ($oWertDB) {
                            return $oWertDB->cSpracheName;
                        }, $oWertDB_arr)
                    )
                );
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
                            $lang
                                ->setzeSprache($cISO)
                                ->set($oVariable->kSprachsektion, $oVariable->cName, $cWert);
                        }
                    } else {
                        // kein alter Wert vorhanden
                        $lang->fuegeEin($cISO, $oVariable->kSprachsektion, $oVariable->cName, $cWert);
                    }
                }

                Shop::Container()->getDB()->delete(
                    'tsprachlog',
                    ['cSektion', 'cName'],
                    [$oVariable->cSprachsektion, $oVariable->cName]
                );
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
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
                        $lang
                            ->setzeSprache($cISOSprache)
                            ->set((int)$kSektion, $cName, $cWert);
                        $cChanged_arr[] = $cName;
                    }
                }
            }

            Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);

            $cHinweis = count($cChanged_arr) > 0
                ? __('successVarChange') . implode(', ', $cChanged_arr)
                : __('errorVarChangeNone');

            break;
        case 'clearlog':
            // Liste nicht gefundener Variablen leeren
            $lang
                ->setzeSprache($cISOSprache)
                ->clearLog();
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
            $cHinweis .= __('successListReset');
            break;
        default:
            break;
    }
}

if ($step === 'newvar') {
    $smarty
        ->assign('oSektion_arr', $oSektion_arr)
        ->assign('oVariable', $oVariable)
        ->assign('oSprache_arr', $availableLanguages);
} elseif ($step === 'overview') {
    $filter                      = new Filter('langvars');
    $selectField                 = $filter->addSelectfield('Sektion', 'sw.kSprachsektion', 0);
    $selectField->reloadOnChange = true;
    $selectField->addSelectOption('(' . __('all') . ')', '', \Pagination\Operation::CUSTOM);

    foreach ($oSektion_arr as $oSektion) {
        $selectField->addSelectOption($oSektion->cName, $oSektion->kSprachsektion, \Pagination\Operation::EQUALS);
    }

    $filter->addTextfield(
        ['Suche', __('searchInContentAndVarName')],
        ['sw.cName', 'sw.cWert'],
        \Pagination\Operation::CONTAINS
    );
    $selectField = $filter->addSelectfield(__('systemOwn'), 'bSystem', 0);
    $selectField->addSelectOption(__('both'), '', \Pagination\Operation::CUSTOM);
    $selectField->addSelectOption(__('system'), '1', \Pagination\Operation::EQUALS);
    $selectField->addSelectOption(__('own'), '0', \Pagination\Operation::EQUALS);
    $filter->assemble();
    $filterSQL = $filter->getWhereSQL();

    $values = Shop::Container()->getDB()->query(
        'SELECT sw.cName, sw.cWert, sw.cStandard, sw.bSystem, ss.kSprachsektion, ss.cName AS cSektionName
            FROM tsprachwerte AS sw
                JOIN tsprachsektion AS ss
                    ON ss.kSprachsektion = sw.kSprachsektion
            WHERE sw.kSprachISO = ' . (int)$kSprachISO . '
                ' . ($filterSQL !== '' ? 'AND ' . $filterSQL : ''),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    handleCsvExportAction(
        'langvars',
        $cISOSprache . '_' . date('YmdHis') . '.slf',
        $values,
        ['cSektionName', 'cName', 'cWert', 'bSystem'],
        [],
        ';',
        false
    );

    $oPagination = (new Pagination('langvars'))
        ->setRange(4)
        ->setItemArray($values)
        ->assemble();

    $oNotFound_arr = Shop::Container()->getDB()->query(
        'SELECT sl.*, ss.kSprachsektion
            FROM tsprachlog AS sl
                LEFT JOIN tsprachsektion AS ss
                    ON ss.cName = sl.cSektion
            WHERE kSprachISO = ' . (int)$lang->kSprachISO,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty
        ->assign('oFilter', $filter)
        ->assign('oPagination', $oPagination)
        ->assign('oWert_arr', $oPagination->getPageItems())
        ->assign('bSpracheAktiv', $bSpracheAktiv)
        ->assign('oSprache_arr', $availableLanguages)
        ->assign('oNotFound_arr', $oNotFound_arr);
}

$smarty
    ->assign('tab', $tab)
    ->assign('step', $step)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('sprache.tpl');
