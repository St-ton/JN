<?php declare(strict_types=1);

use JTL\DB\SqlObject;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
/** @global \JTL\Smarty\JTLSmarty $smarty */

$oAccount->permission('MODULE_LIVESEARCH_VIEW', true, true);

setzeSprache();
$languageID  = (int)$_SESSION['editLanguageID'];
$settingsIDs = [
    'livesuche_max_ip_count',
    'sonstiges_livesuche_all_top_count',
    'sonstiges_livesuche_all_last_count',
    'boxen_livesuche_count',
    'boxen_livesuche_anzeigen'
];
$db          = Shop::Container()->getDB();
$alertHelper = Shop::Container()->getAlertService();

$liveSearchSQL = new SqlObject();
$liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
if (mb_strlen(Request::verifyGPDataString('cSuche')) > 0) {
    $cSuche = $db->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));

    if (mb_strlen($cSuche) > 0) {
        $liveSearchSQL->setWhere(' AND tsuchanfrage.cSuche LIKE :srch');
        $liveSearchSQL->addParam('srch', '%' . $cSuche . '%');
        $smarty->assign('cSuche', $cSuche);
    } else {
        $alertHelper->addError(__('errorSearchTermMissing'), 'errorSearchTermMissing');
    }
}
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    $alertHelper->addSuccess(
        saveAdminSettings($settingsIDs, $_POST, [CACHING_GROUP_OPTION], true),
        'saveSettings'
    );
    $smarty->assign('tab', 'einstellungen');
}

if (Request::verifyGPCDataInt('nSort') > 0) {
    $smarty->assign('nSort', Request::verifyGPCDataInt('nSort'));

    switch (Request::verifyGPCDataInt('nSort')) {
        case 1:
            $liveSearchSQL->setOrder(' tsuchanfrage.cSuche ASC ');
            break;
        case 11:
            $liveSearchSQL->setOrder(' tsuchanfrage.cSuche DESC ');
            break;
        case 2:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
            break;
        case 22:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche ASC ');
            break;
        case 3:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAktiv DESC ');
            break;
        case 33:
            $liveSearchSQL->setOrder(' tsuchanfrage.nAktiv ASC ');
            break;
    }
} else {
    $smarty->assign('nSort', -1);
}

if (Request::postInt('livesuche') === 1) { //Formular wurde abgeschickt
    // Suchanfragen aktualisieren
    if (isset($_POST['suchanfragenUpdate'])) {
        if (GeneralObject::hasCount('kSuchanfrageAll', $_POST)) {
            foreach ($_POST['kSuchanfrageAll'] as $searchQueryID) {
                if (mb_strlen($_POST['nAnzahlGesuche_' . $searchQueryID]) > 0
                    && (int)$_POST['nAnzahlGesuche_' . $searchQueryID] > 0
                ) {
                    $_upd                 = new stdClass();
                    $_upd->nAnzahlGesuche = (int)$_POST['nAnzahlGesuche_' . $searchQueryID];
                    $db->update('tsuchanfrage', 'kSuchanfrage', (int)$searchQueryID, $_upd);
                }
            }
        }
        // Eintragen in die Mapping Tabelle
        $searchQueries = $db->selectAll(
            'tsuchanfrage',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        // Wurde ein Mapping durchgefuehrt
        $mappingExists = 0;
        if (is_array($_POST['kSuchanfrageAll']) && count($_POST['kSuchanfrageAll']) > 0) {
            $whereIn   = ' IN (';
            $deleteIDs = [];
            // nAktiv Reihe updaten
            foreach ($_POST['kSuchanfrageAll'] as $i => $searchQueryID) {
                $searchQueryID = (int)$searchQueryID;
                $db->update('tsuchanfrage', 'kSuchanfrage', $searchQueryID, (object)['nAktiv' => 0]);
                $deleteIDs[] = $searchQueryID;
            }
            $whereIn .= implode(',', $deleteIDs);
            $whereIn .= ')';
            // Deaktivierte Suchanfragen aus tseo loeschen
            $db->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey" . $whereIn
            );
            // Deaktivierte Suchanfragen in tsuchanfrage updaten
            $db->query(
                "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $whereIn
            );
            foreach (Request::verifyGPDataIntegerArray('nAktiv') as $active) {
                $query = $db->select('tsuchanfrage', 'kSuchanfrage', $active);
                $db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kSuchanfrage', $active, $languageID]
                );
                // Aktivierte Suchanfragen in tseo eintragen
                $ins           = new stdClass();
                $ins->cSeo     = Seo::checkSeo(Seo::getSeo($query->cSuche));
                $ins->cKey     = 'kSuchanfrage';
                $ins->kKey     = $active;
                $ins->kSprache = $languageID;
                $db->insert('tseo', $ins);
                // Aktivierte Suchanfragen in tsuchanfrage updaten
                $upd         = new stdClass();
                $upd->nAktiv = 1;
                $upd->cSeo   = $ins->cSeo;
                $db->update('tsuchanfrage', 'kSuchanfrage', $active, $upd);
            }
        }
        $succesMapMessage = '';
        $errorMapMessage  = '';
        foreach ($searchQueries as $sucheanfrage) {
            $index = 'mapping_' . $sucheanfrage->kSuchanfrage;
            if (!isset($_POST[$index])
                || mb_convert_case($sucheanfrage->cSuche, MB_CASE_LOWER) !==
                mb_convert_case($_POST[$index], MB_CASE_LOWER)
            ) {
                if (!empty($_POST[$index])) {
                    $mappingExists           = 1;
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $sucheanfrage->cSuche;
                    $mapping->cSucheNeu      = $_POST[$index];
                    $mapping->nAnzahlGesuche = $sucheanfrage->nAnzahlGesuche;
                    $mappedSearch            = $db->getSingleObject(
                        'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                            FROM tsuchanfrage
                            WHERE cSuche = :cSuche',
                        [
                            'cSuche' => $mapping->cSucheNeu,
                            'mapped' => $mapping->cSuche,
                        ]
                    );
                    if ((int)($mappedSearch->kSuchanfrage ?? 0) > 0 && (int)($mappedSearch->isEqual ?? 0) === 0) {
                        $db->insert('tsuchanfragemapping', $mapping);
                        $db->queryPrepared(
                            'UPDATE tsuchanfrage
                                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                WHERE kSprache = :lid
                                    AND cSuche = :src',
                            [
                                'cnt' => $sucheanfrage->nAnzahlGesuche,
                                'lid' => $languageID,
                                'src' => $_POST[$index]
                            ]
                        );
                        $db->delete(
                            'tsuchanfrage',
                            'kSuchanfrage',
                            (int)$sucheanfrage->kSuchanfrage
                        );
                        $upd       = new stdClass();
                        $upd->kKey = (int)$mappedSearch->kSuchanfrage;
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
                    } else {
                        $errorMapMessage .= ((int)($mappedSearch->isEqual ?? 0) === 1
                            ? sprintf(__('errorSearchMapLoop'), $mapping->cSuche, $mapping->cSucheNeu)
                            : __('errorSearchMapToNotExist')
                        ) . '<br />';
                    }
                }
            } else {
                $errorMapMessage .= sprintf(__('errorSearchMapSelf'), Text::filterXSS($_POST[$index]));
            }
        }
        $alertHelper->addSuccess($succesMapMessage ?? '', 'successSearchMap');
        $alertHelper->addError($errorMapMessage ?? '', 'errorSearchMap');
        $alertHelper->addSuccess(__('successSearchRefresh'), 'successSearchRefresh');
    } elseif (isset($_POST['submitMapping'])) { // Auswahl mappen
        $mapping = Request::verifyGPDataString('cMapping');

        if (mb_strlen($mapping) > 0) {
            $mappingQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
            if (count($mappingQueryIDs) > 0) {
                foreach ($mappingQueryIDs as $searchQueryID) {
                    $query = $db->select('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
                    if ($query->kSuchanfrage > 0) {
                        if (mb_convert_case($query->cSuche, MB_CASE_LOWER) !==
                            mb_convert_case($mapping, MB_CASE_LOWER)
                        ) {
                            $mappedSearch = $db->getSingleObject(
                                'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                                    FROM tsuchanfrage
                                    WHERE cSuche = :cSuche',
                                [
                                    'cSuche' => $mapping,
                                    'mapped' => $query->cSuche,
                                ]
                            );
                            if ((int)($mappedSearch->kSuchanfrage ?? 0) > 0
                                && (int)($mappedSearch->isEqual ?? 0) === 0
                            ) {
                                $queryMapping                 = new stdClass();
                                $queryMapping->kSprache       = $languageID;
                                $queryMapping->cSuche         = $query->cSuche;
                                $queryMapping->cSucheNeu      = $mapping;
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
                                            'lid' => $languageID,
                                            'sid' => $mappedSearch->kSuchanfrage
                                        ]
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
                                            'kid' => (int)$mappedSearch->kSuchanfrage,
                                            'sid' => (int)$query->kSuchanfrage
                                        ]
                                    );

                                    $alertHelper->addSuccess(
                                        sprintf(__('successSearchMapMultiple'), $queryMapping->cSucheNeu),
                                        'successSearchMapMultiple'
                                    );
                                }
                            } else {
                                if ((int)($mappedSearch->isEqual ?? 0) === 1) {
                                    $alertHelper->addError(
                                        sprintf(__('errorSearchMapLoop'), $query->cSuche, $mapping),
                                        'errorSearchMapToNotExist'
                                    );
                                } else {
                                    $alertHelper->addError(__('errorSearchMapToNotExist'), 'errorSearchMapToNotExist');
                                }
                                break;
                            }
                        } else {
                            $alertHelper->addError(__('errorSearchMapSelf'), 'errorSearchMapSelf');
                            break;
                        }
                    } else {
                        $alertHelper->addError(__('errorSearchMapNotExist'), 'errorSearchMapNotExist');
                        break;
                    }
                }
            } else {
                $alertHelper->addError(__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            }
        } else {
            $alertHelper->addError(__('errorMapNameMissing'), 'errorMapNameMissing');
        }
    } elseif (isset($_POST['delete'])) { // Auswahl loeschen
        $deleteQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
        if (count($deleteQueryIDs) > 0) {
            foreach ($deleteQueryIDs as $searchQueryID) {
                $data          = $db->select(
                    'tsuchanfrage',
                    'kSuchanfrage',
                    $searchQueryID
                );
                $obj           = new stdClass();
                $obj->kSprache = (int)$data->kSprache;
                $obj->cSuche   = $data->cSuche;

                $db->delete('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
                $db->insert('tsuchanfrageblacklist', $obj);
                // Aus tseo loeschen
                $db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', $searchQueryID]);
                $alertHelper->addSuccess(sprintf(__('successSearchDelete'), $data->cSuche), 'sucSearchDelete');
                $alertHelper->addSuccess(sprintf(__('successSearchBlacklist'), $data->cSuche), 'sucSearchBlacklist');
            }
        } else {
            $alertHelper->addError(__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
        }
    }
} elseif (Request::postInt('livesuche') === 2) { // Erfolglos mapping
    if (isset($_POST['erfolglosEdit'])) { // Editieren
        $smarty->assign('nErfolglosEditieren', 1);
    } elseif (isset($_POST['erfolglosUpdate'])) { // Update
        $failedQueries = $db->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        foreach ($failedQueries as $failedQuery) {
            $idx = 'mapping_' . $failedQuery->kSuchanfrageErfolglos;
            if (mb_strlen(Request::postVar($idx, '')) > 0) {
                if (mb_convert_case($failedQuery->cSuche, MB_CASE_LOWER) !==
                    mb_convert_case($_POST[$idx], MB_CASE_LOWER)
                ) {
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $failedQuery->cSuche;
                    $mapping->cSucheNeu      = $_POST[$idx];
                    $mapping->nAnzahlGesuche = $failedQuery->nAnzahlGesuche;

                    $oldQuery = $db->getSingleObject(
                        'SELECT tsuchanfrageerfolglos.kSuchanfrageErfolglos, IF(:mapped = :cSuche, 1, 0) isEqual
                            FROM tsuchanfrageerfolglos
                            WHERE cSuche = :cSuche',
                        [
                            'cSuche' => $mapping->cSuche,
                            'mapped' => $mapping->cSucheNeu,
                        ]
                    );
                    //check if loops would be created with mapping
                    $bIsLoop           = (int)($oldQuery->isEqual ?? 0) > 0;
                    $sSearchMappingTMP = $mapping->cSucheNeu;
                    while (!empty($sSearchMappingTMP) && !$bIsLoop) {
                        $oSearchMappingNextTMP = $db->getSingleObject(
                            'SELECT tsuchanfragemapping.cSucheNeu,
                                IF(:mapped = tsuchanfragemapping.cSucheNeu, 1, 0) isEqual
                                FROM tsuchanfragemapping
                                WHERE tsuchanfragemapping.cSuche = :cSuche
                                    AND tsuchanfragemapping.kSprache = :languageID',
                            [
                                'languageID' => $languageID,
                                'cSuche'     => $sSearchMappingTMP,
                                'mapped'     => $mapping->cSuche,
                            ]
                        );
                        if ((int)($oSearchMappingNextTMP->isEqual ?? 0) === 1) {
                            $bIsLoop = true;
                            break;
                        }
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

                            $alertHelper->addSuccess(
                                sprintf(
                                    __('successSearchMap'),
                                    $mapping->cSuche,
                                    $mapping->cSucheNeu
                                ),
                                'successSearchMap'
                            );
                        }
                    } else {
                        $alertHelper->addError(
                            sprintf(
                                __('errorSearchMapLoop'),
                                $mapping->cSuche,
                                $mapping->cSucheNeu
                            ),
                            'errorSearchMapLoop'
                        );
                    }
                } else {
                    $alertHelper->addError(sprintf(__('errorSearchMapSelf'), $failedQuery->cSuche), 'errSearchMapSelf');
                }
            } elseif (Request::postInt('nErfolglosEditieren') === 1) {
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
            $alertHelper->addSuccess(__('successSearchDeleteMultiple'), 'successSearchDeleteMultiple');
        } else {
            $alertHelper->addError(__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
        }
    }
    $smarty->assign('tab', 'erfolglos');
} elseif (Request::postInt('livesuche') === 3) { // Blacklist
    $blacklist = $_POST['suchanfrageblacklist'];
    $blacklist = explode(';', $blacklist);
    $count     = count($blacklist);

    $db->delete('tsuchanfrageblacklist', 'kSprache', $languageID);
    for ($i = 0; $i < $count; $i++) {
        if (!empty($blacklist[$i])) {
            $ins           = new stdClass();
            $ins->cSuche   = $blacklist[$i];
            $ins->kSprache = $languageID;
            $db->insert('tsuchanfrageblacklist', $ins);
        }
    }
    $smarty->assign('tab', 'blacklist');
    $alertHelper->addSuccess(__('successBlacklistRefresh'), 'successBlacklistRefresh');
} elseif (Request::postInt('livesuche') === 4) { // Mappinglist
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
                    $alertHelper->addSuccess(
                        sprintf(__('successSearchMapDelete'), $queryMapping->cSuche),
                        'successSearchMapDelete'
                    );
                } else {
                    $alertHelper->addError(sprintf(__('errorSearchMapNotFound'), $mappingID), 'errSearchMapNotFound');
                }
            }
        } else {
            $alertHelper->addError(__('errorAtLeastOneSearchMap'), 'errorAtLeastOneSearchMap');
        }
    }
    $smarty->assign('tab', 'mapping');
}

$queryCount        = (int)$db->getSingleObject(
    'SELECT COUNT(*) AS cnt
        FROM tsuchanfrage
        WHERE kSprache = :lid' . $liveSearchSQL->getWhere(),
    array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
)->cnt;
$failedQueryCount  = (int)$db->getSingleObject(
    'SELECT COUNT(*) AS cnt
        FROM tsuchanfrageerfolglos
        WHERE kSprache = :lid',
    ['lid' => $languageID]
)->cnt;
$mappingCount      = (int)$db->getSingleObject(
    'SELECT COUNT(*) AS cnt
        FROM tsuchanfragemapping
        WHERE kSprache = :lid',
    ['lid' => $languageID]
)->cnt;
$paginationQueries = (new Pagination('suchanfragen'))
    ->setItemCount($queryCount)
    ->assemble();
$paginationFailed  = (new Pagination('erfolglos'))
    ->setItemCount($failedQueryCount)
    ->assemble();
$paginationMapping = (new Pagination('mapping'))
    ->setItemCount($mappingCount)
    ->assemble();

$searchQueries = $db->getObjects(
    "SELECT tsuchanfrage.*, tseo.cSeo AS tcSeo
        FROM tsuchanfrage
        LEFT JOIN tseo 
            ON tseo.cKey = 'kSuchanfrage'
            AND tseo.kKey = tsuchanfrage.kSuchanfrage
            AND tseo.kSprache = :lid
        WHERE tsuchanfrage.kSprache = :lid
            " . $liveSearchSQL->getWhere() . '
        GROUP BY tsuchanfrage.kSuchanfrage
        ORDER BY ' . $liveSearchSQL->getOrder() . '
        LIMIT ' . $paginationQueries->getLimitSQL(),
    array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
);
foreach ($searchQueries as $item) {
    if (isset($item->tcSeo) && mb_strlen($item->tcSeo) > 0) {
        $item->cSeo = $item->tcSeo;
    }
    unset($item->tcSeo);
}

$failedQueries  = $db->getObjects(
    'SELECT *
        FROM tsuchanfrageerfolglos
        WHERE kSprache = :lid
        ORDER BY nAnzahlGesuche DESC
        LIMIT ' . $paginationFailed->getLimitSQL(),
    ['lid' => $languageID]
);
$queryBlacklist = $db->getCollection(
    'SELECT *
        FROM tsuchanfrageblacklist
        WHERE kSprache = :lid
        ORDER BY kSuchanfrageBlacklist',
    ['lid' => $languageID]
)->each(static function (stdClass $item) {
    $item->cSuche = htmlentities($item->cSuche);

    return $item;
})->toArray();
$queryMapping   = $db->getObjects(
    'SELECT *
        FROM tsuchanfragemapping
        WHERE kSprache = :lid
        LIMIT ' . $paginationMapping->getLimitSQL(),
    ['lid' => $languageID]
);
getAdminSectionSettings($settingsIDs, true);
$smarty->assign('Suchanfragen', $searchQueries)
    ->assign('Suchanfragenerfolglos', $failedQueries)
    ->assign('Suchanfragenblacklist', $queryBlacklist)
    ->assign('Suchanfragenmapping', $queryMapping)
    ->assign('oPagiSuchanfragen', $paginationQueries)
    ->assign('oPagiErfolglos', $paginationFailed)
    ->assign('oPagiMapping', $paginationMapping)
    ->display('livesuche.tpl');
