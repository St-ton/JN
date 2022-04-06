<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\DB\SqlObject;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class StatusController
 * @package JTL\Router\Controller\Backend
 */
class LivesearchController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions('MODULE_LIVESEARCH_VIEW');
        $this->getText->loadAdminLocale('pages/livesuche');

        $this->setzeSprache();
        $languageID  = (int)$_SESSION['editLanguageID'];
        $settingsIDs = [
            'livesuche_max_ip_count',
            'sonstiges_livesuche_all_top_count',
            'sonstiges_livesuche_all_last_count',
            'boxen_livesuche_count',
            'boxen_livesuche_anzeigen'
        ];

        $liveSearchSQL = new SqlObject();
        $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
        if (\mb_strlen(Request::verifyGPDataString('cSuche')) > 0) {
            $cSuche = $this->db->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));

            if (\mb_strlen($cSuche) > 0) {
                $liveSearchSQL->setWhere(' AND tsuchanfrage.cSuche LIKE :srch');
                $liveSearchSQL->addParam('srch', '%' . $cSuche . '%');
                $smarty->assign('cSuche', $cSuche);
            } else {
                $this->alertService->addError(\__('errorSearchTermMissing'), 'errorSearchTermMissing');
            }
        }
        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->alertService->addSuccess(
                $this->saveAdminSettings($settingsIDs, $_POST, [\CACHING_GROUP_OPTION], true),
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
                        if (\mb_strlen($_POST['nAnzahlGesuche_' . $searchQueryID]) > 0
                            && (int)$_POST['nAnzahlGesuche_' . $searchQueryID] > 0
                        ) {
                            $_upd                 = new stdClass();
                            $_upd->nAnzahlGesuche = (int)$_POST['nAnzahlGesuche_' . $searchQueryID];
                            $this->db->update('tsuchanfrage', 'kSuchanfrage', (int)$searchQueryID, $_upd);
                        }
                    }
                }
                // Eintragen in die Mapping Tabelle
                $searchQueries = $this->db->selectAll(
                    'tsuchanfrage',
                    'kSprache',
                    $languageID,
                    '*',
                    'nAnzahlGesuche DESC'
                );
                // Wurde ein Mapping durchgefuehrt
                if (\is_array($_POST['kSuchanfrageAll']) && \count($_POST['kSuchanfrageAll']) > 0) {
                    $whereIn   = ' IN (';
                    $deleteIDs = [];
                    // nAktiv Reihe updaten
                    foreach ($_POST['kSuchanfrageAll'] as $searchQueryID) {
                        $searchQueryID = (int)$searchQueryID;
                        $this->db->update('tsuchanfrage', 'kSuchanfrage', $searchQueryID, (object)['nAktiv' => 0]);
                        $deleteIDs[] = $searchQueryID;
                    }
                    $whereIn .= \implode(',', $deleteIDs);
                    $whereIn .= ')';
                    // Deaktivierte Suchanfragen aus tseo loeschen
                    $this->db->query(
                        "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                        AND kKey" . $whereIn
                    );
                    // Deaktivierte Suchanfragen in tsuchanfrage updaten
                    $this->db->query(
                        "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $whereIn
                    );
                    foreach (Request::verifyGPDataIntegerArray('nAktiv') as $active) {
                        $query = $this->db->select('tsuchanfrage', 'kSuchanfrage', $active);
                        $this->db->delete(
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
                        $this->db->insert('tseo', $ins);
                        // Aktivierte Suchanfragen in tsuchanfrage updaten
                        $upd         = new stdClass();
                        $upd->nAktiv = 1;
                        $upd->cSeo   = $ins->cSeo;
                        $this->db->update('tsuchanfrage', 'kSuchanfrage', $active, $upd);
                    }
                }
                $succesMapMessage = '';
                $errorMapMessage  = '';
                foreach ($searchQueries as $sucheanfrage) {
                    $index = 'mapping_' . $sucheanfrage->kSuchanfrage;
                    if (!isset($_POST[$index])
                        || \mb_convert_case($sucheanfrage->cSuche, \MB_CASE_LOWER) !==
                        \mb_convert_case($_POST[$index], \MB_CASE_LOWER)
                    ) {
                        if (!empty($_POST[$index])) {
                            $mapping                 = new stdClass();
                            $mapping->kSprache       = $languageID;
                            $mapping->cSuche         = $sucheanfrage->cSuche;
                            $mapping->cSucheNeu      = $_POST[$index];
                            $mapping->nAnzahlGesuche = $sucheanfrage->nAnzahlGesuche;
                            $mappedSearch            = $this->db->getSingleObject(
                                'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                            FROM tsuchanfrage
                            WHERE cSuche = :cSuche',
                                [
                                    'cSuche' => $mapping->cSucheNeu,
                                    'mapped' => $mapping->cSuche,
                                ]
                            );
                            if ((int)($mappedSearch->kSuchanfrage ?? 0) > 0 && (int)($mappedSearch->isEqual ?? 0) === 0) {
                                $this->db->insert('tsuchanfragemapping', $mapping);
                                $this->db->queryPrepared(
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
                                $this->db->delete(
                                    'tsuchanfrage',
                                    'kSuchanfrage',
                                    (int)$sucheanfrage->kSuchanfrage
                                );
                                $upd       = new stdClass();
                                $upd->kKey = (int)$mappedSearch->kSuchanfrage;
                                $this->db->update(
                                    'tseo',
                                    ['cKey', 'kKey'],
                                    ['kSuchanfrage', (int)$sucheanfrage->kSuchanfrage],
                                    $upd
                                );

                                $succesMapMessage .= \sprintf(
                                    \__('successSearchMap'),
                                    $mapping->cSuche,
                                    $mapping->cSucheNeu
                                ) . '<br />';
                            } else {
                                $errorMapMessage .= ((int)($mappedSearch->isEqual ?? 0) === 1
                                        ? \sprintf(\__('errorSearchMapLoop'), $mapping->cSuche, $mapping->cSucheNeu)
                                        : \__('errorSearchMapToNotExist')
                                    ) . '<br />';
                            }
                        }
                    } else {
                        $errorMapMessage .= \sprintf(\__('errorSearchMapSelf'), Text::filterXSS($_POST[$index]));
                    }
                }
                $this->alertService->addSuccess($succesMapMessage ?? '', 'successSearchMap');
                $this->alertService->addError($errorMapMessage ?? '', 'errorSearchMap');
                $this->alertService->addSuccess(\__('successSearchRefresh'), 'successSearchRefresh');
            } elseif (isset($_POST['submitMapping'])) { // Auswahl mappen
                $mapping = Request::verifyGPDataString('cMapping');

                if (\mb_strlen($mapping) > 0) {
                    $mappingQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
                    if (\count($mappingQueryIDs) > 0) {
                        foreach ($mappingQueryIDs as $searchQueryID) {
                            $query = $this->db->select('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
                            if ($query->kSuchanfrage > 0) {
                                if (\mb_convert_case($query->cSuche, \MB_CASE_LOWER) !==
                                    \mb_convert_case($mapping, \MB_CASE_LOWER)
                                ) {
                                    $mappedSearch = $this->db->getSingleObject(
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

                                        $mappingID = $this->db->insert(
                                            'tsuchanfragemapping',
                                            $queryMapping
                                        );
                                        if ($mappingID > 0) {
                                            $this->db->queryPrepared(
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
                                            $this->db->delete(
                                                'tsuchanfrage',
                                                'kSuchanfrage',
                                                (int)$query->kSuchanfrage
                                            );
                                            $this->db->queryPrepared(
                                                "UPDATE tseo
                                            SET kKey = :kid
                                            WHERE cKey = 'kSuchanfrage'
                                                AND kKey = :sid",
                                                [
                                                    'kid' => (int)$mappedSearch->kSuchanfrage,
                                                    'sid' => (int)$query->kSuchanfrage
                                                ]
                                            );

                                            $this->alertService->addSuccess(
                                                \sprintf(\__('successSearchMapMultiple'), $queryMapping->cSucheNeu),
                                                'successSearchMapMultiple'
                                            );
                                        }
                                    } else {
                                        if ((int)($mappedSearch->isEqual ?? 0) === 1) {
                                            $this->alertService->addError(
                                                \sprintf(\__('errorSearchMapLoop'), $query->cSuche, $mapping),
                                                'errorSearchMapToNotExist'
                                            );
                                        } else {
                                            $this->alertService->addError(\__('errorSearchMapToNotExist'), 'errorSearchMapToNotExist');
                                        }
                                        break;
                                    }
                                } else {
                                    $this->alertService->addError(\__('errorSearchMapSelf'), 'errorSearchMapSelf');
                                    break;
                                }
                            } else {
                                $this->alertService->addError(\__('errorSearchMapNotExist'), 'errorSearchMapNotExist');
                                break;
                            }
                        }
                    } else {
                        $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
                    }
                } else {
                    $this->alertService->addError(\__('errorMapNameMissing'), 'errorMapNameMissing');
                }
            } elseif (isset($_POST['delete'])) { // Auswahl loeschen
                $deleteQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
                if (\count($deleteQueryIDs) > 0) {
                    foreach ($deleteQueryIDs as $searchQueryID) {
                        $data          = $this->db->select(
                            'tsuchanfrage',
                            'kSuchanfrage',
                            $searchQueryID
                        );
                        $obj           = new stdClass();
                        $obj->kSprache = (int)$data->kSprache;
                        $obj->cSuche   = $data->cSuche;

                        $this->db->delete('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
                        $this->db->insert('tsuchanfrageblacklist', $obj);
                        // Aus tseo loeschen
                        $this->db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', $searchQueryID]);
                        $this->alertService->addSuccess(\sprintf(\__('successSearchDelete'), $data->cSuche), 'sucSearchDelete');
                        $this->alertService->addSuccess(\sprintf(\__('successSearchBlacklist'), $data->cSuche), 'sucSearchBlacklist');
                    }
                } else {
                    $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
                }
            }
        } elseif (Request::postInt('livesuche') === 2) { // Erfolglos mapping
            if (isset($_POST['erfolglosEdit'])) { // Editieren
                $smarty->assign('nErfolglosEditieren', 1);
            } elseif (isset($_POST['erfolglosUpdate'])) { // Update
                $failedQueries = $this->db->selectAll(
                    'tsuchanfrageerfolglos',
                    'kSprache',
                    $languageID,
                    '*',
                    'nAnzahlGesuche DESC'
                );
                foreach ($failedQueries as $failedQuery) {
                    $idx = 'mapping_' . $failedQuery->kSuchanfrageErfolglos;
                    if (\mb_strlen(Request::postVar($idx, '')) > 0) {
                        if (\mb_convert_case($failedQuery->cSuche, \MB_CASE_LOWER) !==
                            \mb_convert_case($_POST[$idx], \MB_CASE_LOWER)
                        ) {
                            $mapping                 = new stdClass();
                            $mapping->kSprache       = $languageID;
                            $mapping->cSuche         = $failedQuery->cSuche;
                            $mapping->cSucheNeu      = $_POST[$idx];
                            $mapping->nAnzahlGesuche = $failedQuery->nAnzahlGesuche;

                            $oldQuery = $this->db->getSingleObject(
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
                                $oSearchMappingNextTMP = $this->db->getSingleObject(
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
                                    $this->db->insert('tsuchanfragemapping', $mapping);
                                    $this->db->delete(
                                        'tsuchanfrageerfolglos',
                                        'kSuchanfrageErfolglos',
                                        (int)$oldQuery->kSuchanfrageErfolglos
                                    );

                                    $this->alertService->addSuccess(
                                        \sprintf(
                                            \__('successSearchMap'),
                                            $mapping->cSuche,
                                            $mapping->cSucheNeu
                                        ),
                                        'successSearchMap'
                                    );
                                }
                            } else {
                                $this->alertService->addError(
                                    \sprintf(
                                        \__('errorSearchMapLoop'),
                                        $mapping->cSuche,
                                        $mapping->cSucheNeu
                                    ),
                                    'errorSearchMapLoop'
                                );
                            }
                        } else {
                            $this->alertService->addError(\sprintf(\__('errorSearchMapSelf'), $failedQuery->cSuche), 'errSearchMapSelf');
                        }
                    } elseif (Request::postInt('nErfolglosEditieren') === 1) {
                        $idx = 'cSuche_' . $failedQuery->kSuchanfrageErfolglos;

                        $failedQuery->cSuche = Text::filterXSS($_POST[$idx]);
                        $upd                 = new stdClass();
                        $upd->cSuche         = $failedQuery->cSuche;
                        $this->db->update(
                            'tsuchanfrageerfolglos',
                            'kSuchanfrageErfolglos',
                            (int)$failedQuery->kSuchanfrageErfolglos,
                            $upd
                        );
                    }
                }
            } elseif (isset($_POST['erfolglosDelete'])) { // Loeschen
                $queryIDs = $_POST['kSuchanfrageErfolglos'];
                if (\is_array($queryIDs) && \count($queryIDs) > 0) {
                    foreach ($queryIDs as $queryID) {
                        $this->db->delete(
                            'tsuchanfrageerfolglos',
                            'kSuchanfrageErfolglos',
                            (int)$queryID
                        );
                    }
                    $this->alertService->addSuccess(\__('successSearchDeleteMultiple'), 'successSearchDeleteMultiple');
                } else {
                    $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
                }
            }
            $smarty->assign('tab', 'erfolglos');
        } elseif (Request::postInt('livesuche') === 3) { // Blacklist
            $blacklist = $_POST['suchanfrageblacklist'];
            $blacklist = \explode(';', $blacklist);
            $count     = \count($blacklist);

            $this->db->delete('tsuchanfrageblacklist', 'kSprache', $languageID);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($blacklist[$i])) {
                    $ins           = new stdClass();
                    $ins->cSuche   = $blacklist[$i];
                    $ins->kSprache = $languageID;
                    $this->db->insert('tsuchanfrageblacklist', $ins);
                }
            }
            $smarty->assign('tab', 'blacklist');
            $this->alertService->addSuccess(\__('successBlacklistRefresh'), 'successBlacklistRefresh');
        } elseif (Request::postInt('livesuche') === 4) { // Mappinglist
            if (isset($_POST['delete'])) {
                if (\is_array($_POST['kSuchanfrageMapping'])) {
                    foreach ($_POST['kSuchanfrageMapping'] as $mappingID) {
                        $queryMapping = $this->db->select(
                            'tsuchanfragemapping',
                            'kSuchanfrageMapping',
                            (int)$mappingID
                        );
                        if (isset($queryMapping->cSuche) && \mb_strlen($queryMapping->cSuche) > 0) {
                            $this->db->delete(
                                'tsuchanfragemapping',
                                'kSuchanfrageMapping',
                                (int)$mappingID
                            );
                            $this->alertService->addSuccess(
                                \sprintf(\__('successSearchMapDelete'), $queryMapping->cSuche),
                                'successSearchMapDelete'
                            );
                        } else {
                            $this->alertService->addError(\sprintf(\__('errorSearchMapNotFound'), $mappingID), 'errSearchMapNotFound');
                        }
                    }
                } else {
                    $this->alertService->addError(\__('errorAtLeastOneSearchMap'), 'errorAtLeastOneSearchMap');
                }
            }
            $smarty->assign('tab', 'mapping');
        }

        $queryCount        = (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
        FROM tsuchanfrage
        WHERE kSprache = :lid' . $liveSearchSQL->getWhere(),
            \array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
        )->cnt;
        $failedQueryCount  = (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt
        FROM tsuchanfrageerfolglos
        WHERE kSprache = :lid',
            ['lid' => $languageID]
        )->cnt;
        $mappingCount      = (int)$this->db->getSingleObject(
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

        $searchQueries = $this->db->getObjects(
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
            \array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
        );
        foreach ($searchQueries as $item) {
            if (isset($item->tcSeo) && \mb_strlen($item->tcSeo) > 0) {
                $item->cSeo = $item->tcSeo;
            }
            unset($item->tcSeo);
        }

        $failedQueries  = $this->db->getObjects(
            'SELECT *
        FROM tsuchanfrageerfolglos
        WHERE kSprache = :lid
        ORDER BY nAnzahlGesuche DESC
        LIMIT ' . $paginationFailed->getLimitSQL(),
            ['lid' => $languageID]
        );
        $queryBlacklist = $this->db->getCollection(
            'SELECT *
        FROM tsuchanfrageblacklist
        WHERE kSprache = :lid
        ORDER BY kSuchanfrageBlacklist',
            ['lid' => $languageID]
        )->each(static function (stdClass $item) {
            $item->cSuche = \htmlentities($item->cSuche);

            return $item;
        })->toArray();
        $queryMapping   = $this->db->getObjects(
            'SELECT *
        FROM tsuchanfragemapping
        WHERE kSprache = :lid
        LIMIT ' . $paginationMapping->getLimitSQL(),
            ['lid' => $languageID]
        );
        $this->getAdminSectionSettings($settingsIDs, true);

        return $smarty->assign('Suchanfragen', $searchQueries)
            ->assign('Suchanfragenerfolglos', $failedQueries)
            ->assign('Suchanfragenblacklist', $queryBlacklist)
            ->assign('Suchanfragenmapping', $queryMapping)
            ->assign('oPagiSuchanfragen', $paginationQueries)
            ->assign('oPagiErfolglos', $paginationFailed)
            ->assign('oPagiMapping', $paginationMapping)
            ->assign('route', $this->route)
            ->getResponse('livesuche.tpl');
    }
}
