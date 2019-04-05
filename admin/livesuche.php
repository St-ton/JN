<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;
use JTL\Sprache;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_LIVESEARCH_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

setzeSprache();

$settingsIDs = [423, 425, 422, 437, 438];
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
$cLivesucheSQL         = new stdClass();
$cLivesucheSQL->cWhere = '';
$cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
if (mb_strlen(Request::verifyGPDataString('cSuche')) > 0) {
    $cSuche = $db->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));

    if (mb_strlen($cSuche) > 0) {
        $cLivesucheSQL->cWhere = " AND tsuchanfrage.cSuche LIKE '%" . $cSuche . "%'";
        $smarty->assign('cSuche', $cSuche);
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchTermMissing'), 'errorSearchTermMissing');
    }
}
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    $alertHelper->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSettings($settingsIDs, $_POST),
        'saveSettings'
    );
    $smarty->assign('tab', 'einstellungen');
}

if (Request::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

    switch (Request::verifyGPCDataInt('nSort')) {
        case 1:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche ASC ';
            break;
        case 11:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.cSuche DESC ';
            break;
        case 2:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche DESC ';
            break;
        case 22:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAnzahlGesuche ASC ';
            break;
        case 3:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv DESC ';
            break;
        case 33:
            $cLivesucheSQL->cOrder = ' tsuchanfrage.nAktiv ASC ';
            break;
    }
} else {
    $smarty->assign('nSort', -1);
}

if (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 1) { //Formular wurde abgeschickt
    // Suchanfragen aktualisieren
    if (isset($_POST['suchanfragenUpdate'])) {
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            foreach ($_POST['kSuchanfrageAll'] as $kSuchanfrage) {
                if (mb_strlen($_POST['nAnzahlGesuche_' . $kSuchanfrage]) > 0
                    && (int)$_POST['nAnzahlGesuche_' . $kSuchanfrage] > 0
                ) {
                    $_upd                 = new stdClass();
                    $_upd->nAnzahlGesuche = (int)$_POST['nAnzahlGesuche_' . $kSuchanfrage];
                    $db->update('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage, $_upd);
                }
            }
        }
        // Eintragen in die Mapping Tabelle
        $searchQueries = $db->selectAll(
            'tsuchanfrage',
            'kSprache',
            (int)$_SESSION['kSprache'],
            '*',
            'nAnzahlGesuche DESC'
        );
        // Wurde ein Mapping durchgefuehrt
        $nMappingVorhanden = 0;
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            $cSQLDel = ' IN (';
            // nAktiv Reihe updaten
            foreach ($_POST['kSuchanfrageAll'] as $i => $kSuchanfrage) {
                $upd         = new stdClass();
                $upd->nAktiv = 0;
                $db->update('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage, $upd);
                // Loeschequery vorbereiten
                if ($i > 0) {
                    $cSQLDel .= ', ' . (int)$kSuchanfrage;
                } else {
                    $cSQLDel .= (int)$kSuchanfrage;
                }
            }

            $cSQLDel .= ')';
            // Deaktivierte Suchanfragen aus tseo loeschen
            $db->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey" . $cSQLDel,
                ReturnType::AFFECTED_ROWS
            );
            // Deaktivierte Suchanfragen in tsuchanfrage updaten
            $db->query(
                "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $cSQLDel,
                ReturnType::AFFECTED_ROWS
            );
            if (isset($_POST['nAktiv']) && is_array($_POST['nAktiv'])) {
                foreach ($_POST['nAktiv'] as $i => $nAktiv) {
                    $query = $db->select('tsuchanfrage', 'kSuchanfrage', (int)$nAktiv);
                    $db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kSuchanfrage', (int)$nAktiv, (int)$_SESSION['kSprache']]
                    );
                    // Aktivierte Suchanfragen in tseo eintragen
                    $ins           = new stdClass();
                    $ins->cSeo     = Seo::checkSeo(Seo::getSeo($query->cSuche));
                    $ins->cKey     = 'kSuchanfrage';
                    $ins->kKey     = $nAktiv;
                    $ins->kSprache = $_SESSION['kSprache'];
                    $db->insert('tseo', $ins);
                    // Aktivierte Suchanfragen in tsuchanfrage updaten
                    $upd         = new stdClass();
                    $upd->nAktiv = 1;
                    $upd->cSeo   = $ins->cSeo;
                    $db->update('tsuchanfrage', 'kSuchanfrage', (int)$nAktiv, $upd);
                }
            }
        }
        foreach ($searchQueries as $sucheanfrage) {
            if (!isset($_POST['mapping_' . $sucheanfrage->kSuchanfrage])
                || mb_convert_case($sucheanfrage->cSuche, MB_CASE_LOWER) !==
                mb_convert_case($_POST['mapping_' . $sucheanfrage->kSuchanfrage], MB_CASE_LOWER)
            ) {
                if (!empty($_POST['mapping_' . $sucheanfrage->kSuchanfrage])) {
                    $nMappingVorhanden       = 1;
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $_SESSION['kSprache'];
                    $mapping->cSuche         = $sucheanfrage->cSuche;
                    $mapping->cSucheNeu      = $_POST['mapping_' . $sucheanfrage->kSuchanfrage];
                    $mapping->nAnzahlGesuche = $sucheanfrage->nAnzahlGesuche;
                    $Neuesuche               = $db->select(
                        'tsuchanfrage',
                        'cSuche',
                        $mapping->cSucheNeu
                    );
                    if (isset($Neuesuche->kSuchanfrage) && $Neuesuche->kSuchanfrage > 0) {
                        $db->insert('tsuchanfragemapping', $mapping);
                        $db->queryPrepared(
                            'UPDATE tsuchanfrage
                                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                WHERE kSprache = :lid
                                    AND cSuche = :src',
                            [
                                'cnt' => $sucheanfrage->nAnzahlGesuche,
                                'lid' => (int)$_SESSION['kSprache'],
                                'src' => $_POST['mapping_' . $sucheanfrage->kSuchanfrage]
                            ],
                            ReturnType::DEFAULT
                        );
                        $db->delete(
                            'tsuchanfrage',
                            'kSuchanfrage',
                            (int)$sucheanfrage->kSuchanfrage
                        );
                        $upd       = new stdClass();
                        $upd->kKey = (int)$Neuesuche->kSuchanfrage;
                        $db->update(
                            'tseo',
                            ['cKey', 'kKey'],
                            ['kSuchanfrage', (int)$sucheanfrage->kSuchanfrage],
                            $upd
                        );

                        $succesMapMessage .= sprintf(
                            __('successSearchMap'),
                            $mapping->cSuche,
                            $mapping->cSucheNeu
                        ) . '<br />';
                    }
                }
            } else {
                $errorMapMessage .= sprintf(__('errorSearchMapSelf'), $mapping->cSucheNeu);
            }
        }
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, $succesMapMessage ?? '', 'successSearchMap');
        $alertHelper->addAlert(Alert::TYPE_ERROR, $errorMapMessage ?? '', 'errorSearchMap');
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successSearchRefresh'), 'successSearchRefresh');
    } elseif (isset($_POST['submitMapping'])) { // Auswahl mappen
        $cMapping = Request::verifyGPDataString('cMapping');

        if (mb_strlen($cMapping) > 0) {
            if (is_array($_POST['kSuchanfrage']) && count($_POST['kSuchanfrage']) > 0) {
                foreach ($_POST['kSuchanfrage'] as $kSuchanfrage) {
                    $query = $db->select('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);

                    if ($query->kSuchanfrage > 0) {
                        if (mb_convert_case($query->cSuche, MB_CASE_LOWER) !==
                            mb_convert_case($cMapping, MB_CASE_LOWER)
                        ) {
                            $oSuchanfrageNeu = $db->select('tsuchanfrage', 'cSuche', $cMapping);
                            if (isset($oSuchanfrageNeu->kSuchanfrage) && $oSuchanfrageNeu->kSuchanfrage > 0) {
                                $queryMapping                 = new stdClass();
                                $queryMapping->kSprache       = $_SESSION['kSprache'];
                                $queryMapping->cSuche         = $query->cSuche;
                                $queryMapping->cSucheNeu      = $cMapping;
                                $queryMapping->nAnzahlGesuche = $query->nAnzahlGesuche;

                                $mappingID = $db->insert(
                                    'tsuchanfragemapping',
                                    $queryMapping
                                );
                                if ($mappingID > 0) {
                                    $db->queryPrepared(
                                        'UPDATE tsuchanfrage
                                            SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                            WHERE kSprache = :lid
                                                AND kSuchanfrage = :sid',
                                        [
                                            'cnt' => $query->nAnzahlGesuche,
                                            'lid' => (int)$_SESSION['kSprache'],
                                            'sid' => $oSuchanfrageNeu->kSuchanfrage
                                        ],
                                        ReturnType::DEFAULT
                                    );
                                    $db->delete(
                                        'tsuchanfrage',
                                        'kSuchanfrage',
                                        (int)$query->kSuchanfrage
                                    );
                                    $db->queryPrepared(
                                        "UPDATE tseo
                                            SET kKey = :kid
                                            WHERE cKey = 'kSuchanfrage'
                                                AND kKey = :sid",
                                        [
                                            'kid' => (int)$oSuchanfrageNeu->kSuchanfrage,
                                            'sid' => (int)$query->kSuchanfrage
                                        ],
                                        ReturnType::DEFAULT
                                    );

                                    $alertHelper->addAlert(
                                        Alert::TYPE_SUCCESS,
                                        __('successSearchMapMultiple'),
                                        'successSearchMapMultiple'
                                    );
                                }
                            } else {
                                $alertHelper->addAlert(
                                    Alert::TYPE_ERROR,
                                    __('errorSearchMapToNotExist'),
                                    'errorSearchMapToNotExist'
                                );
                                break;
                            }
                        } else {
                            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchMapSelf'), 'errorSearchMapSelf');
                            break;
                        }
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            __('errorSearchMapNotExist'),
                            'errorSearchMapNotExist'
                        );
                        break;
                    }
                }
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorMapNameMissing'), 'errorMapNameMissing');
        }
    } elseif (isset($_POST['delete'])) { // Auswahl loeschen
        if (is_array($_POST['kSuchanfrage'])) {
            foreach ($_POST['kSuchanfrage'] as $kSuchanfrage) {
                $kSuchanfrage_obj = $db->select(
                    'tsuchanfrage',
                    'kSuchanfrage',
                    (int)$kSuchanfrage
                );
                $obj              = new stdClass();
                $obj->kSprache    = (int)$kSuchanfrage_obj->kSprache;
                $obj->cSuche      = $kSuchanfrage_obj->cSuche;

                $db->delete('tsuchanfrage', 'kSuchanfrage', (int)$kSuchanfrage);
                $db->insert('tsuchanfrageblacklist', $obj);
                // Aus tseo loeschen
                $db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', (int)$kSuchanfrage]);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successSearchDelete'), $kSuchanfrage_obj->cSuche),
                    'successSearchDelete'
                );
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successSearchBlacklist'), $kSuchanfrage_obj->cSuche),
                    'successSearchBlacklist'
                );
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
        }
    }
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 2) { // Erfolglos mapping
    if (isset($_POST['erfolglosEdit'])) { // Editieren
        $smarty->assign('nErfolglosEditieren', 1);
    } elseif (isset($_POST['erfolglosUpdate'])) { // Update
        $failedQueries = $db->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            (int)$_SESSION['kSprache'],
            '*',
            'nAnzahlGesuche DESC'
        );
        foreach ($failedQueries as $failedQuery) {
            $idx = 'mapping_' . $failedQuery->kSuchanfrageErfolglos;
            if (isset($_POST[$idx]) && mb_strlen($_POST[$idx]) > 0) {
                if (mb_convert_case($failedQuery->cSuche, MB_CASE_LOWER) !==
                    mb_convert_case($_POST[$idx], MB_CASE_LOWER)
                ) {
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $_SESSION['kSprache'];
                    $mapping->cSuche         = $failedQuery->cSuche;
                    $mapping->cSucheNeu      = $_POST[$idx];
                    $mapping->nAnzahlGesuche = $failedQuery->nAnzahlGesuche;

                    $oldQuery = $db->select(
                        'tsuchanfrageerfolglos',
                        'cSuche',
                        $mapping->cSuche
                    );
                    //check if loops would be created with mapping
                    $bIsLoop           = false;
                    $sSearchMappingTMP = $mapping->cSucheNeu;
                    while (!empty($sSearchMappingTMP)) {
                        if ($sSearchMappingTMP === $mapping->cSuche) {
                            $bIsLoop = true;
                            break;
                        }
                        $oSearchMappingNextTMP = $db->select(
                            'tsuchanfragemapping',
                            'kSprache',
                            $_SESSION['kSprache'],
                            'cSuche',
                            $sSearchMappingTMP
                        );
                        if (!empty($oSearchMappingNextTMP->cSucheNeu)) {
                            $sSearchMappingTMP = $oSearchMappingNextTMP->cSucheNeu;
                        } else {
                            $sSearchMappingTMP = null;
                        }
                    }

                    if (!$bIsLoop) {
                        if (isset($oldQuery->kSuchanfrageErfolglos) && $oldQuery->kSuchanfrageErfolglos > 0) {
                            $oCheckMapping = $db->select(
                                'tsuchanfrageerfolglos',
                                'cSuche',
                                $mapping->cSuche
                            );
                            $db->insert('tsuchanfragemapping', $mapping);
                            $db->delete(
                                'tsuchanfrageerfolglos',
                                'kSuchanfrageErfolglos',
                                (int)$oldQuery->kSuchanfrageErfolglos
                            );

                            $alertHelper->addAlert(
                                Alert::TYPE_SUCCESS,
                                sprintf(
                                    __('successSearchMap'),
                                    $mapping->cSuche,
                                    $mapping->cSucheNeu
                                ),
                                'successSearchMap'
                            );
                        }
                    } else {
                        $alertHelper->addAlert(
                            Alert::TYPE_ERROR,
                            sprintf(
                                __('errorSearchMapLoop'),
                                $mapping->cSuche,
                                $mapping->cSucheNeu
                            ),
                            'errorSearchMapLoop'
                        );
                    }
                } else {
                    $alertHelper->addAlert(
                        Alert::TYPE_ERROR,
                        sprintf(__('errorSearchMapSelf'), $failedQuery->cSuche),
                        'errorSearchMapSelf'
                    );
                }
            } elseif ((int)$_POST['nErfolglosEditieren'] === 1) {
                $idx = 'cSuche_' . $failedQuery->kSuchanfrageErfolglos;

                $failedQuery->cSuche = Text::filterXSS($_POST[$idx]);
                $upd                 = new stdClass();
                $upd->cSuche         = $failedQuery->cSuche;
                $db->update(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$failedQuery->kSuchanfrageErfolglos,
                    $upd
                );
            }
        }
    } elseif (isset($_POST['erfolglosDelete'])) { // Loeschen
        $queryIDs = $_POST['kSuchanfrageErfolglos'];
        if (is_array($queryIDs) && count($queryIDs) > 0) {
            foreach ($queryIDs as $queryID) {
                $db->delete(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$queryID
                );
            }
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                __('successSearchDeleteMultiple'),
                'successSearchDeleteMultiple'
            );
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_ERROR,
                __('errorAtLeastOneSearch'),
                'errorAtLeastOneSearch'
            );
        }
    }
    $smarty->assign('tab', 'erfolglos');
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 3) { // Blacklist
    $blacklist = $_POST['suchanfrageblacklist'];
    $blacklist = explode(';', $blacklist);
    $count     = count($blacklist);

    $db->delete('tsuchanfrageblacklist', 'kSprache', (int)$_SESSION['kSprache']);
    for ($i = 0; $i < $count; $i++) {
        if (!empty($blacklist[$i])) {
            $ins           = new stdClass();
            $ins->cSuche   = $blacklist[$i];
            $ins->kSprache = (int)$_SESSION['kSprache'];
            $db->insert('tsuchanfrageblacklist', $ins);
        }
    }
    $smarty->assign('tab', 'blacklist');
    $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successBlacklistRefresh'), 'successBlacklistRefresh');
} elseif (isset($_POST['livesuche']) && (int)$_POST['livesuche'] === 4) { // Mappinglist
    if (isset($_POST['delete'])) {
        if (is_array($_POST['kSuchanfrageMapping'])) {
            foreach ($_POST['kSuchanfrageMapping'] as $mappingID) {
                $queryMapping = $db->select(
                    'tsuchanfragemapping',
                    'kSuchanfrageMapping',
                    (int)$mappingID
                );
                if (isset($queryMapping->cSuche) && mb_strlen($queryMapping->cSuche) > 0) {
                    $db->delete(
                        'tsuchanfragemapping',
                        'kSuchanfrageMapping',
                        (int)$mappingID
                    );
                    $alertHelper->addAlert(
                        Alert::TYPE_SUCCESS,
                        sprintf(__('successSearchMapDelete'), $queryMapping->cSuche),
                        'successSearchMapDelete'
                    );
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorSearchMapNotFound'), 'errorSearchMapNotFound');
                }
            }
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorAtLeastOneSearchMap'), 'errorAtLeastOneSearchMap');
        }
    }
    $smarty->assign('tab', 'mapping');
}

$languages         = Sprache::getAllLanguages();
$queryCount        = $db->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tsuchanfrage
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . $cLivesucheSQL->cWhere,
    ReturnType::SINGLE_OBJECT
);
$failedQueryCount  = $db->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tsuchanfrageerfolglos
        WHERE kSprache = ' . (int)$_SESSION['kSprache'],
    ReturnType::SINGLE_OBJECT
);
$mappingCount      = $db->query(
    'SELECT COUNT(*) AS nAnzahl
        FROM tsuchanfragemapping
        WHERE kSprache = ' . (int)$_SESSION['kSprache'],
    ReturnType::SINGLE_OBJECT
);
$oPagiSuchanfragen = (new Pagination('suchanfragen'))
    ->setItemCount($queryCount->nAnzahl)
    ->assemble();
$oPagiErfolglos    = (new Pagination('erfolglos'))
    ->setItemCount($failedQueryCount->nAnzahl)
    ->assemble();
$oPagiMapping      = (new Pagination('mapping'))
    ->setItemCount($mappingCount->nAnzahl)
    ->assemble();

$searchQueries = $db->query(
    "SELECT tsuchanfrage.*, tseo.cSeo AS tcSeo
        FROM tsuchanfrage
        LEFT JOIN tseo ON tseo.cKey = 'kSuchanfrage'
            AND tseo.kKey = tsuchanfrage.kSuchanfrage
            AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . '
        WHERE tsuchanfrage.kSprache = ' . (int)$_SESSION['kSprache'] . '
            ' . $cLivesucheSQL->cWhere . '
        GROUP BY tsuchanfrage.kSuchanfrage
        ORDER BY ' . $cLivesucheSQL->cOrder . '
        LIMIT ' . $oPagiSuchanfragen->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);

if (isset($searchQueries->tcSeo) && mb_strlen($searchQueries->tcSeo) > 0) {
    $searchQueries->cSeo = $searchQueries->tcSeo;
}
unset($searchQueries->tcSeo);

$failedQueries  = $db->query(
    'SELECT *
        FROM tsuchanfrageerfolglos
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
        ORDER BY nAnzahlGesuche DESC
        LIMIT ' . $oPagiErfolglos->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
$queryBlacklist = $db->query(
    'SELECT *
        FROM tsuchanfrageblacklist
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
        ORDER BY kSuchanfrageBlacklist',
    ReturnType::ARRAY_OF_OBJECTS
);
$queryMapping   = $db->query(
    'SELECT *
        FROM tsuchanfragemapping
        WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
        LIMIT ' . $oPagiMapping->getLimitSQL(),
    ReturnType::ARRAY_OF_OBJECTS
);
$smarty->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
       ->assign('Sprachen', $languages)
       ->assign('Suchanfragen', $searchQueries)
       ->assign('Suchanfragenerfolglos', $failedQueries)
       ->assign('Suchanfragenblacklist', $queryBlacklist)
       ->assign('Suchanfragenmapping', $queryMapping)
       ->assign('oPagiSuchanfragen', $oPagiSuchanfragen)
       ->assign('oPagiErfolglos', $oPagiErfolglos)
       ->assign('oPagiMapping', $oPagiMapping)
       ->display('livesuche.tpl');
