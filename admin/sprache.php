<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global smarty
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;
use JTL\Pagination\Operation;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('LANGUAGE_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_exporter_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'csv_importer_inc.php';

$alertHelper = Shop::Container()->getAlertService();
$tab         = $_REQUEST['tab'] ?? 'variables';
$step        = 'overview';
$lang        = Shop::Lang();
setzeSprache();
$cISOSprache = $_SESSION['cISOSprache'];

if (isset($_FILES['csvfile']['tmp_name'])
    && Form::validateToken()
    && Request::verifyGPDataString('importcsv') === 'langvars'
) {
    $csvFilename = $_FILES['csvfile']['tmp_name'];
    $importType  = Request::verifyGPCDataInt('importType');
    $res         = $lang->import($csvFilename, $cISOSprache, $importType);

    if ($res === false) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorImport'), 'errorImport');
    } else {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, sprintf(__('successImport'), $res), 'successImport');
    }
}

$langIso            = $lang->getLangIDFromIso($cISOSprache);
$langIsoID          = $langIso->kSprachISO ?? 0;
$installedLanguages = $lang->getInstalled();
$availableLanguages = $lang->getAvailable();
$sections           = $lang->getSections();
$langIsActive       = false;

if (count($installedLanguages) !== count($availableLanguages)) {
    $alertHelper->addAlert(Alert::TYPE_NOTE, __('newLangAvailable'), 'newLangAvailable');
}

foreach ($installedLanguages as $language) {
    if ($language->cISO === $cISOSprache) {
        $langIsActive = true;
        break;
    }
}

foreach ($availableLanguages as $language) {
    $language->bImported = in_array($language, $installedLanguages);
}

if (isset($_REQUEST['action']) && Form::validateToken()) {
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'newvar':
            // neue Variable erstellen
            $step                     = 'newvar';
            $variable                 = new stdClass();
            $variable->kSprachsektion = isset($_REQUEST['kSprachsektion']) ? (int)$_REQUEST['kSprachsektion'] : 1;
            $variable->cName          = $_REQUEST['cName'] ?? '';
            $variable->cWert_arr      = [];
            break;
        case 'delvar':
            // Variable loeschen
            $lang->loesche($_GET['kSprachsektion'], $_GET['cName']);
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successVarRemove'), $_GET['cName']),
                'successVarRemove'
            );
            break;
        case 'savevar':
            // neue Variable speichern
            $variable                 = new stdClass();
            $variable->kSprachsektion = (int)$_REQUEST['kSprachsektion'];
            $variable->cName          = $_REQUEST['cName'];
            $variable->cWert_arr      = $_REQUEST['cWert_arr'];
            $variable->cWertAlt_arr   = [];
            $variable->bOverwrite_arr = $_REQUEST['bOverwrite_arr'] ?? [];
            $errors                   = [];
            $variable->cSprachsektion = Shop::Container()->getDB()
                                             ->select(
                                                 'tsprachsektion',
                                                 'kSprachsektion',
                                                 (int)$variable->kSprachsektion
                                             )
                ->cName;

            $data = Shop::Container()->getDB()->queryPrepared(
                'SELECT s.cNameDeutsch AS cSpracheName, sw.cWert, si.cISO
                    FROM tsprachwerte AS sw
                        JOIN tsprachiso AS si
                            ON si.kSprachISO = sw.kSprachISO
                        JOIN tsprache AS s
                            ON s.cISO = si.cISO 
                    WHERE sw.cName = :cName
                        AND sw.kSprachsektion = :kSprachsektion',
                ['cName' => $variable->cName, 'kSprachsektion' => $variable->kSprachsektion],
                ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($data as $item) {
                $variable->cWertAlt_arr[$item->cISO] = $item->cWert;
            }

            if (!preg_match('/([\w\d]+)/', $variable->cName)) {
                $errors[] = __('errorVarFormat');
            }

            if (count($variable->bOverwrite_arr) !== count($data)) {
                $errors[] = sprintf(
                    __('errorVarExistsForLang'),
                    implode(
                        ', ',
                        array_map(function ($oWertDB) {
                            return $oWertDB->cSpracheName;
                        }, $data)
                    )
                );
            }

            if (count($errors) > 0) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, implode('<br>', $errors), 'newVar');
                $step = 'newvar';
            } else {
                foreach ($variable->cWert_arr as $cISO => $cWert) {
                    if (isset($variable->cWertAlt_arr[$cISO])) {
                        // alter Wert vorhanden
                        if ((int)$variable->bOverwrite_arr[$cISO] === 1) {
                            // soll ueberschrieben werden
                            $lang
                                ->setzeSprache($cISO)
                                ->set($variable->kSprachsektion, $variable->cName, $cWert);
                        }
                    } else {
                        // kein alter Wert vorhanden
                        $lang->fuegeEin($cISO, $variable->kSprachsektion, $variable->cName, $cWert);
                    }
                }

                Shop::Container()->getDB()->delete(
                    'tsprachlog',
                    ['cSektion', 'cName'],
                    [$variable->cSprachsektion, $variable->cName]
                );
                Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
                Shop::Container()->getDB()->query(
                    'UPDATE tglobals SET dLetzteAenderung = NOW()',
                    ReturnType::DEFAULT
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
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);

            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                count($cChanged_arr) > 0
                    ? __('successVarChange') . implode(', ', $cChanged_arr)
                    : __('errorVarChangeNone'),
                'varChangeMessage'
            );

            break;
        case 'clearlog':
            // Liste nicht gefundener Variablen leeren
            $lang
                ->setzeSprache($cISOSprache)
                ->clearLog();
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_LANGUAGE]);
            Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successListReset'), 'successListReset');
            break;
        default:
            break;
    }
}

if ($step === 'newvar') {
    $smarty
        ->assign('oSektion_arr', $sections)
        ->assign('oVariable', $variable)
        ->assign('oSprache_arr', $availableLanguages);
} elseif ($step === 'overview') {
    $filter                      = new Filter('langvars');
    $selectField                 = $filter->addSelectfield('Sektion', 'sw.kSprachsektion', 0);
    $selectField->reloadOnChange = true;
    $selectField->addSelectOption('(' . __('all') . ')', '', Operation::CUSTOM);

    foreach ($sections as $oSektion) {
        $selectField->addSelectOption($oSektion->cName, $oSektion->kSprachsektion, Operation::EQUALS);
    }

    $filter->addTextfield(
        ['Suche', __('searchInContentAndVarName')],
        ['sw.cName', 'sw.cWert'],
        Operation::CONTAINS
    );
    $selectField = $filter->addSelectfield(__('systemOwn'), 'bSystem', 0);
    $selectField->addSelectOption(__('both'), '', Operation::CUSTOM);
    $selectField->addSelectOption(__('system'), '1', Operation::EQUALS);
    $selectField->addSelectOption(__('own'), '0', Operation::EQUALS);
    $filter->assemble();
    $filterSQL = $filter->getWhereSQL();

    $values = Shop::Container()->getDB()->query(
        'SELECT sw.cName, sw.cWert, sw.cStandard, sw.bSystem, ss.kSprachsektion, ss.cName AS cSektionName
            FROM tsprachwerte AS sw
                JOIN tsprachsektion AS ss
                    ON ss.kSprachsektion = sw.kSprachsektion
            WHERE sw.kSprachISO = ' . (int)$langIsoID . '
                ' . ($filterSQL !== '' ? 'AND ' . $filterSQL : ''),
        ReturnType::ARRAY_OF_OBJECTS
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

    $pagination = (new Pagination('langvars'))
        ->setRange(4)
        ->setItemArray($values)
        ->assemble();

    $notFound = Shop::Container()->getDB()->query(
        'SELECT sl.*, ss.kSprachsektion
            FROM tsprachlog AS sl
                LEFT JOIN tsprachsektion AS ss
                    ON ss.cName = sl.cSektion
            WHERE kSprachISO = ' . (int)$lang->currentLanguageID,
        ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty
        ->assign('oFilter', $filter)
        ->assign('oPagination', $pagination)
        ->assign('oWert_arr', $pagination->getPageItems())
        ->assign('bSpracheAktiv', $langIsActive)
        ->assign('oSprache_arr', $availableLanguages)
        ->assign('oNotFound_arr', $notFound);
}

$smarty
    ->assign('tab', $tab)
    ->assign('step', $step)
    ->display('sprache.tpl');
