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

$cHinweis = '';
$cFehler  = '';
$tab      = 'variables';
$step     = 'overview';
setzeSprache();
$oSprache     = Sprache::getInstance();
$oSprache_arr = $oSprache->getInstalled();

if (validateToken()) {
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'newvar') {
            // neue Variable erstellen
            $step                      = 'newvar';
            $oVariable                 = new stdClass();
            $oVariable->kSprachsektion = 1;
            $oVariable->cName          = '';
            $oVariable->cWert_arr      = [];
        }
    } elseif (isset($_POST['action'])) {
        if ($_POST['action'] === 'savevar') {
            // neue Variable speichern
            $oVariable                 = new stdClass();
            $oVariable->kSprachsektion = (int)$_POST['kSprachsektion'];
            $oVariable->cName          = $_POST['cName'];
            $oVariable->cWert_arr      = $_POST['cWert_arr'];
            $oVariable->cWertAlt_arr   = [];
            $oVariable->bOverwrite_arr = isset($_POST['bOverwrite_arr']) ? $_POST['bOverwrite_arr'] : [];
            $cFehler_arr               = [];

            $oWertDB_arr = Shop::DB()->query(
                "SELECT s.cNameDeutsch AS cSpracheName, sw.cWert, si.cISO
                    FROM tsprachwerte AS sw
                        JOIN tsprachiso AS si
                            ON si.kSprachISO = sw.kSprachISO
                        JOIN tsprache AS s
                            ON s.cISO = si.cISO 
                    WHERE sw.cName = '" . $oVariable->cName . "'
                        AND sw.kSprachsektion = " . $oVariable->kSprachsektion,
                2
            );

            foreach ($oWertDB_arr as $oWertDB) {
                $oVariable->cWertAlt_arr[$oWertDB->cISO] = $oWertDB->cWert;
            }

            if (!preg_match('/([\w\d]+)/', $oVariable->cName)) {
                $cFehler_arr[] = 'Die Variable darf nur aus Buchstaben und Zahlen bestehen und darf nicht leer sein.';
            }

            if (count($oVariable->bOverwrite_arr) !== count($oWertDB_arr)) {
                $cFehler_arr[] = 'Die Variable existiert bereits f&uuml;r die Sprachen ' .
                    implode(' und ', array_map(function ($oWertDB) { return $oWertDB->cSpracheName; }, $oWertDB_arr)) .
                    '. Bitte w&auml;hlen Sie aus, welche Versionen sie &Uuml;berschreiben m&ouml;chten!';
            }

            if (count($cFehler_arr) > 0) {
                $cFehler = implode('<br>', $cFehler_arr);
                $step    = 'newvar';
            } else {
                foreach ($oVariable->cWert_arr as $cISO => $cWert) {
                    if (isset($oVariable->cWertAlt_arr[$cISO])) {
                        // alter Wert verhanden
                        if ((int)$oVariable->bOverwrite_arr[$cISO] === 1) {
                            // soll ueberschrieben werden
                            $oSprache
                                ->setzeSprache($cISO)
                                ->set($oVariable->kSprachsektion, $oVariable->cName, $cWert);
                        }
                    } else {
                        // kein alter Wert vorhanden
                        $oSprache->fuegeEin($cISO, $oVariable->kSprachsektion, $oVariable->cName, $cWert);
                    }
                }
            }
        }
    }
}

if ($step === 'newvar') {
    $oSektion_arr = Shop::DB()->query(
        "SELECT *
            FROM tsprachsektion",
        2
    );

    $smarty
        ->assign('oSektion_arr', $oSektion_arr)
        ->assign('oVariable', $oVariable)
        ->assign('oSprache_arr', $oSprache_arr);
} elseif ($step === 'overview') {
    $oSektion_arr                  = Shop::DB()->query("SELECT * FROM tsprachsektion", 2);
    $oFilter                       = new Filter('langvars');
    $oSelectfield                  = $oFilter->addSelectfield('Sektion', 'sw.kSprachsektion', 1);
    $oSelectfield->bReloadOnChange = true;
    $oSelectfield->addSelectOption('(alle)', '', 0);

    foreach ($oSektion_arr as $oSektion) {
        $oSelectfield->addSelectOption($oSektion->cName, $oSektion->kSprachsektion, 4);
    }

    $oFilter->addTextfield(['Suche', 'Suchen im Variablennamen und im Inhalt'], ['sw.cName', 'sw.cWert'], 1);
    $oFilter->assemble();
    $cFilterSQL = $oFilter->getWhereSQL();

    $oWert_arr = Shop::DB()->query(
        "SELECT sw.cName, sw.cWert, sw.bSystem, ss.kSprachsektion, ss.cName AS cSektionName
            FROM tsprachwerte AS sw
                JOIN tsprachsektion AS ss
                    ON ss.kSprachsektion = sw.kSprachsektion
            WHERE kSprachISO = " . $oSprache->kSprachISO . "
                " . ($cFilterSQL !== '' ? "AND " . $cFilterSQL : ""),
        2
    );
    $smarty
        ->assign('oFilter', $oFilter)
        ->assign('oWert_arr', $oWert_arr)
        ->assign('oSprache_arr', $oSprache_arr);
}

$smarty
    ->assign('tab', $tab)
    ->assign('step', $step)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->display('sprache.tpl');

/*
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'template_inc.php';

$cHinweis       = '';
$cFehler        = '';
$cTab           = 'sprachvariablen';
$cISO           = (isset($_REQUEST['cISO']) ? $_REQUEST['cISO'] : null);
$kSprachsektion = (isset($_REQUEST['kSprachsektion']) ? intval($_REQUEST['kSprachsektion']) : null);

$oSprache = Sprache::getInstance(false);
$oSprache->setzeSprache($cISO);
if (isset($_POST['clearLog'])) {
    $clear = $oSprache->clearLog();
    if ($clear > 0) {
        $cHinweis .= 'Liste erfolgreich zur&uuml;ckgesetzt.';
    } else {
        $cFehler .= 'Konnte Liste nicht zur&uuml;cksetzen.';
    }
}
if ($oSprache->gueltig() || (isset($_REQUEST['action']) && $_REQUEST['action'] === 'import' && validateToken())) {
    if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
            case 'updateSection':
                $cName_arr = $_POST['cName'];
                $cWert_arr = $_POST['cWert'];
                foreach ($cName_arr as $i => $cName) {
                    $oSprache->setzeWert($kSprachsektion, $cName, $cWert_arr[$i]);
                }
                $cHinweis = 'Variablen wurden erfolgreich aktualisiert.';
                Shop::Cache()->flushTags(array(CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE));
                break;

            case 'search':
                if (isset($_POST['update'])) {
                    $cName_arr          = $_POST['cName'];
                    $cWert_arr          = $_POST['cWert'];
                    $kSprachsektion_arr = $_POST['kSprachsektion'];
                    foreach ($cName_arr as $i => $cName) {
                        $oSprache->setzeWert($kSprachsektion_arr[$i], $cName, $cWert_arr[$i]);
                    }
                    $cHinweis = 'Variablen wurden erfolgreich aktualisiert.';
                    Shop::Cache()->flushTags(array(CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE));
                }
                $cTab      = 'suche';
                $cSuchwort = $_POST['cSuchwort'];
                if (strlen($cSuchwort) >= 3) {
                    $oSuchWerte_arr = $oSprache->suche($cSuchwort);
                    if (count($oSuchWerte_arr) > 0) {
                        $smarty->assign('oSuchWerte_arr', $oSuchWerte_arr)
                               ->assign('cSuchwort', $cSuchwort);
                    } else {
                        $cFehler = 'Die Suche lieferte keine Ergebnisse.';
                    }
                } else {
                    $cFehler = 'Das Suchwort muss mindestens 3 Zeichen lang sein.';
                }
                break;

            case 'export':
                $cTab = 'export';
                $nTyp = (int)$_POST['nTyp'];

                $cFileName = $oSprache->export($nTyp);
                if (file_exists($cFileName)) {
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Content-type: text/plain');
                    header('Content-Disposition: attachment; filename="' . $cISO . '_' . time() . '.slf"');
                    readfile($cFileName);
                    exit;
                } else {
                    $cFehler = 'Export fehlgeschlagen.';
                }
                break;

            case 'import':
                $cTab       = 'import';
                $nTyp       = (int)$_POST['nTyp'];
                $cSprachISO = $_POST['cSprachISO'];

                if (isset($_FILES['langfile']) && $_FILES['langfile']['error'] == 0) {
                    $cTmpFile     = $_FILES['langfile']['tmp_name'];
                    $nUpdateCount = $oSprache->import($cTmpFile, $cSprachISO, $nTyp);
                    if ($nUpdateCount !== false) {
                        $cHinweis = 'Es wurden ' . $nUpdateCount . ' Variablen aktualisiert';
                        Shop::Cache()->flushTags(array(CACHING_GROUP_LANGUAGE));
                    } else {
                        $cFehler = 'Fehler beim Importieren der Datei.';
                    }
                } else {
                    $cFehler = 'Sie haben keine Import-Datei ausgew&auml;hlt.';
                }
                break;

            case 'delete':
                $cTab           = 'sprachvariablen';
                $kSprachsektion = (int)$_GET['kSprachsektion'];
                $cName          = $_GET['cName'];

                if ($oSprache->loesche($kSprachsektion, $cName)) {
                    $cHinweis = 'Variable wurde erfolgreich gel&ouml;scht.';
                    Shop::Cache()->flushTags(array(CACHING_GROUP_LANGUAGE));
                } else {
                    $cFehler = 'Variable konnte nicht gel&ouml;scht werden.';
                }

                break;

            case 'add':
                $cTab           = 'hinzufuegen';
                $kSprachsektion = (int)$_POST['kSprachsektion'];
                $cName          = $_POST['cName'];
                $cSprachISO_arr = $_POST['cSprachISO'];
                $cWert_arr      = $_POST['cWert'];
                $bForceInsert   = isset($_POST['forceInsert']) && (int)$_POST['forceInsert'] === 1 ? true : false;

                if (!preg_match('/([\w\d]+)/', $cName)) {
                    $cFehler = 'Die Variable darf nur aus Buchstaben und Zahlen bestehen.';
                } else {
                    $bError     = false;
                    $cLastName  = '';
                    $cExistArr  = [];
                    $cInsertArr = [];
                    foreach ($cWert_arr as $i => $cWert) {
                        $cLastName        = $cName;
                        $cISO             = $cSprachISO_arr[$i];
                        $cWert_arr[$cISO] = &$cWert_arr[$i];
                        if (!$oSprache->fuegeEin($cISO, $kSprachsektion, $cName, $cWert)) {
                            if ($bForceInsert) {
                                $oSprache->setzeSprache($cISO)->setzeWert($kSprachsektion, $cName, $cWert);
                            } else {
                                $bError = true;
                                $cExistArr[$cISO] = $oSprache->setzeSprache($cISO)->get($cName, $oSprache->getSectionName($kSprachsektion));
                            }
                        } else {
                            $cInsertArr[] = $cISO;
                        }
                    }
                    if ($bError) {
                        $cFehler = 'Die Variable ' . $cLastName . ' existiert bereits in ' . strtoupper(implode(', ', array_keys($cExistArr))) . '.';
                        if (count($cInsertArr) > 0) {
                            $cHinweis = 'Die Variable ' . $cLastName . ' wurde f&uuml;r ' . strtoupper(implode(', ', $cInsertArr)) . ' hinzugef&uuml;gt.';
                        }
                        $smarty
                            ->assign('cPostArr', [
                                    'kSprachsektion' => $kSprachsektion,
                                    'cName'          => $cName,
                                    'cWert'          => $cWert_arr,
                                    'cExist'         => $cExistArr,
                                ])
                            ->assign('forceInsert', true);
                    } else {
                        $cHinweis = 'Variable wurde erfolgreich gespeichert.';
                        Shop::Cache()->flushTags(array(CACHING_GROUP_LANGUAGE));
                    }
                }
                break;

            default:
                break;
        }

        Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
    }

    $smarty->assign('oWerte_arr', $oSprache->gibAlleWerte())
           ->assign('oLogWerte_arr', $oSprache->gibLogWerte());
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('cTab', $cTab)
       ->assign('cISO', $cISO)
       ->assign('kSprachsektion', $kSprachsektion)
       ->assign('oInstallierteSprachen', $oSprache->gibInstallierteSprachen())
       ->assign('oVerfuegbareSprachen', $oSprache->gibVerfuegbareSprachen())
       ->display('sprache.tpl');
*/
