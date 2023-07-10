<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\DB\SqlObject;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class LivesearchController
 * @package JTL\Router\Controller\Backend
 */
class LivesearchController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->checkPermissions(Permissions::MODULE_LIVESEARCH_VIEW);
        $this->getText->loadAdminLocale('pages/livesuche');
        $this->setLanguage();

        $settingsIDs = [
            'livesuche_max_ip_count',
            'sonstiges_livesuche_all_top_count',
            'sonstiges_livesuche_all_last_count',
            'boxen_livesuche_count',
            'boxen_livesuche_anzeigen'
        ];
        if ($this->request->requestInt('einstellungen') === 1) {
            $this->alertService->addSuccess(
                $this->saveAdminSettings($settingsIDs, $this->request->getBody(), [\CACHING_GROUP_OPTION], true),
                'saveSettings'
            );
            $this->smarty->assign('tab', 'einstellungen');
        }
        if ($this->request->postInt('livesuche') === 1) { //Formular wurde abgeschickt
            // Suchanfragen aktualisieren
            if ($this->request->post('suchanfragenUpdate') !== null) {
                $this->actionUpdate($this->currentLanguageID);
            } elseif ($this->request->post('submitMapping') !== null) { // Auswahl mappen
                $this->actionMap($this->currentLanguageID);
            } elseif ($this->request->post('delete') !== null) { // Auswahl loeschen
                $deleteQueryIDs = $this->request->requestIntArray('kSuchanfrage');
                $this->actionDelete($deleteQueryIDs);
            }
        } elseif ($this->request->postInt('livesuche') === 2) { // Erfolglos mapping
            $this->actionMapWithoutSuccess($this->currentLanguageID);
            $this->smarty->assign('tab', 'erfolglos');
        } elseif ($this->request->postInt('livesuche') === 3) { // Blacklist
            $this->actionBlacklist($this->currentLanguageID);
        } elseif ($this->request->postInt('livesuche') === 4) { // Mappinglist
            if ($this->request->post('delete') !== null) {
                $this->actionDeleteMapping($this->request->post('kSuchanfrageMapping'));
            }
            $this->smarty->assign('tab', 'mapping');
        }
        $this->assignData($this->currentLanguageID);
        $this->getAdminSectionSettings($settingsIDs, true);

        return $this->smarty->getResponse('livesuche.tpl');
    }

    /**
     * @param array $queryIDs
     * @return void
     */
    private function actionDelete(array $queryIDs): void
    {
        if (\count($queryIDs) === 0) {
            $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            return;
        }
        foreach ($queryIDs as $queryID) {
            $data = $this->db->select(
                'tsuchanfrage',
                'kSuchanfrage',
                $queryID
            );
            if ($data === null) {
                continue;
            }
            $obj           = new stdClass();
            $obj->kSprache = (int)$data->kSprache;
            $obj->cSuche   = $data->cSuche;

            $this->db->delete('tsuchanfrage', 'kSuchanfrage', $queryID);
            $this->db->insert('tsuchanfrageblacklist', $obj);
            // Aus tseo loeschen
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', $queryID]);
            $this->alertService->addSuccess(
                \sprintf(\__('successSearchDelete'), $data->cSuche),
                'sucSearchDelete'
            );
            $this->alertService->addSuccess(
                \sprintf(\__('successSearchBlacklist'), $data->cSuche),
                'sucSearchBlacklist'
            );
        }
    }

    /**
     * @param int $languageID
     * @return void
     */
    private function actionMap(int $languageID): void
    {
        $mapping = $this->request->request('cMapping');
        if (\mb_strlen($mapping) === 0) {
            $this->alertService->addError(\__('errorMapNameMissing'), 'errorMapNameMissing');
            return;
        }
        $mappingQueryIDs = $this->request->requestIntArray('kSuchanfrage');
        if (\count($mappingQueryIDs) === 0) {
            $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            return;
        }
        foreach ($mappingQueryIDs as $searchQueryID) {
            $query = $this->db->select('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
            if ($query === null || $query->kSuchanfrage <= 0) {
                $this->alertService->addError(\__('errorSearchMapNotExist'), 'errorSearchMapNotExist');
                return;
            }
            if (\mb_convert_case($query->cSuche, \MB_CASE_LOWER) === \mb_convert_case($mapping, \MB_CASE_LOWER)) {
                $this->alertService->addError(\__('errorSearchMapSelf'), 'errorSearchMapSelf');
                return;
            }
            $mappedSearch = $this->db->getSingleObject(
                'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                    FROM tsuchanfrage
                    WHERE cSuche = :cSuche',
                [
                    'cSuche' => $mapping,
                    'mapped' => $query->cSuche,
                ]
            );
            if ($mappedSearch === null || (int)($mappedSearch->isEqual ?? 0) !== 0) {
                if ((int)($mappedSearch->isEqual ?? 0) === 1) {
                    $this->alertService->addError(
                        \sprintf(\__('errorSearchMapLoop'), $query->cSuche, $mapping),
                        'errorSearchMapToNotExist'
                    );
                } else {
                    $this->alertService->addError(
                        \__('errorSearchMapToNotExist'),
                        'errorSearchMapToNotExist'
                    );
                }
                return;
            }
            $queryMapping                 = new stdClass();
            $queryMapping->kSprache       = $languageID;
            $queryMapping->cSuche         = $query->cSuche;
            $queryMapping->cSucheNeu      = $mapping;
            $queryMapping->nAnzahlGesuche = $query->nAnzahlGesuche;

            $mappingID = $this->db->insert('tsuchanfragemapping', $queryMapping);
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
                $this->db->delete('tsuchanfrage', 'kSuchanfrage', (int)$query->kSuchanfrage);
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
        }
    }

    /**
     * @param int $languageID
     * @return void
     */
    private function actionMapWithoutSuccess(int $languageID): void
    {
        if ($this->request->post('erfolglosEdit') !== null) { // Editieren
            $this->smarty->assign('nErfolglosEditieren', 1);
            return;
        }
        if ($this->request->post('erfolglosUpdate') !== null) { // Update
            $this->actionUpdateWithoutSuccess($languageID);
            return;
        }
        if ($this->request->post('erfolglosDelete') !== null) { // Loeschen
            $queryIDs = $this->request->post('kSuchanfrageErfolglos', []);
            if (!\is_array($queryIDs) || \count($queryIDs) === 0) {
                $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
                return;
            }
            foreach ($queryIDs as $queryID) {
                $this->db->delete('tsuchanfrageerfolglos', 'kSuchanfrageErfolglos', (int)$queryID);
            }
            $this->alertService->addSuccess(\__('successSearchDeleteMultiple'), 'successSearchDeleteMultiple');
        }
    }

    /**
     * @param int $languageID
     * @return void
     */
    private function actionUpdateWithoutSuccess(int $languageID): void
    {
        $failedQueries = $this->db->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        foreach ($failedQueries as $failedQuery) {
            $idx = 'mapping_' . $failedQuery->kSuchanfrageErfolglos;
            if (\mb_strlen($this->request->post($idx, '')) > 0) {
                if (\mb_convert_case($failedQuery->cSuche, \MB_CASE_LOWER) !==
                    \mb_convert_case($this->request->post($idx), \MB_CASE_LOWER)
                ) {
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $failedQuery->cSuche;
                    $mapping->cSucheNeu      = $this->request->post($idx);
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
                        if ($oSearchMappingNextTMP !== null && !empty($oSearchMappingNextTMP->cSucheNeu)) {
                            $sSearchMappingTMP = $oSearchMappingNextTMP->cSucheNeu;
                        } else {
                            $sSearchMappingTMP = null;
                        }
                    }

                    if (!$bIsLoop) {
                        if ($oldQuery !== null && $oldQuery->kSuchanfrageErfolglos > 0) {
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
                    $this->alertService->addError(
                        \sprintf(\__('errorSearchMapSelf'), $failedQuery->cSuche),
                        'errSearchMapSelf'
                    );
                }
            } elseif ($this->request->postInt('nErfolglosEditieren') === 1) {
                $idx                 = 'cSuche_' . $failedQuery->kSuchanfrageErfolglos;
                $failedQuery->cSuche = Text::filterXSS($this->request->post($idx));
                $upd                 = (object)['cSuche' => $failedQuery->cSuche];
                $this->db->update(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$failedQuery->kSuchanfrageErfolglos,
                    $upd
                );
            }
        }
    }

    /**
     * @param int $languageID
     * @return void
     */
    private function actionBlacklist(int $languageID): void
    {
        $this->db->delete('tsuchanfrageblacklist', 'kSprache', $languageID);
        foreach (\explode(';', $this->request->post('suchanfrageblacklist', [])) as $item) {
            if (!empty($item)) {
                $ins           = new stdClass();
                $ins->cSuche   = $item;
                $ins->kSprache = $languageID;
                $this->db->insert('tsuchanfrageblacklist', $ins);
            }
        }
        $this->smarty->assign('tab', 'blacklist');
        $this->alertService->addSuccess(\__('successBlacklistRefresh'), 'successBlacklistRefresh');
    }

    /**
     * @param array|null $data
     * @return void
     */
    private function actionDeleteMapping(?array $data): void
    {
        if (!\is_array($data)) {
            $this->alertService->addError(\__('errorAtLeastOneSearchMap'), 'errorAtLeastOneSearchMap');
            return;
        }
        foreach (\array_map('\intval', $data) as $mappingID) {
            $queryMapping = $this->db->select('tsuchanfragemapping', 'kSuchanfrageMapping', $mappingID);
            if ($queryMapping !== null && \mb_strlen($queryMapping->cSuche) > 0) {
                $this->db->delete(
                    'tsuchanfragemapping',
                    'kSuchanfrageMapping',
                    $mappingID
                );
                $this->alertService->addSuccess(
                    \sprintf(\__('successSearchMapDelete'), $queryMapping->cSuche),
                    'successSearchMapDelete'
                );
            } else {
                $this->alertService->addError(
                    \sprintf(\__('errorSearchMapNotFound'), $mappingID),
                    'errSearchMapNotFound'
                );
            }
        }
    }

    /**
     * @param int $languageID
     * @return void
     */
    private function actionUpdate(int $languageID): void
    {
        foreach ($this->request->post('kSuchanfrageAll', []) as $searchQueryID) {
            $value = $this->request->post('nAnzahlGesuche_' . $searchQueryID, '');
            if ((int)$value > 0) {
                $upd = (object)['nAnzahlGesuche' => (int)$value];
                $this->db->update('tsuchanfrage', 'kSuchanfrage', (int)$searchQueryID, $upd);
            }
        }
        $searchQueries = $this->db->selectAll(
            'tsuchanfrage',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        // Wurde ein Mapping durchgefuehrt
        if (\is_array($this->request->post('kSuchanfrageAll')) && \count($this->request->post('kSuchanfrageAll')) > 0) {
            $whereIn   = ' IN (';
            $deleteIDs = [];
            // nAktiv Reihe updaten
            foreach ($this->request->post('kSuchanfrageAll') as $searchQueryID) {
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
            foreach ($this->request->requestIntArray('nAktiv') as $active) {
                $query = $this->db->select('tsuchanfrage', 'kSuchanfrage', $active);
                $this->db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kSuchanfrage', $active, $languageID]
                );
                if ($query === null) {
                    continue;
                }
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
        foreach ($searchQueries as $searchQuery) {
            $index = 'mapping_' . $searchQuery->kSuchanfrage;
            if ($this->request->post($index) === null
                || \mb_convert_case($searchQuery->cSuche, \MB_CASE_LOWER) !==
                \mb_convert_case($this->request->post($index), \MB_CASE_LOWER)
            ) {
                if (!empty($this->request->post($index))) {
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $searchQuery->cSuche;
                    $mapping->cSucheNeu      = $this->request->post($index);
                    $mapping->nAnzahlGesuche = $searchQuery->nAnzahlGesuche;
                    $mappedSearch            = $this->db->getSingleObject(
                        'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                            FROM tsuchanfrage
                            WHERE cSuche = :cSuche',
                        [
                            'cSuche' => $mapping->cSucheNeu,
                            'mapped' => $mapping->cSuche,
                        ]
                    );
                    if ($mappedSearch !== null && (int)($mappedSearch->isEqual ?? 0) === 0) {
                        $this->db->insert('tsuchanfragemapping', $mapping);
                        $this->db->queryPrepared(
                            'UPDATE tsuchanfrage
                                SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                                WHERE kSprache = :lid
                                    AND cSuche = :src',
                            [
                                'cnt' => $searchQuery->nAnzahlGesuche,
                                'lid' => $languageID,
                                'src' => $this->request->post($index)
                            ]
                        );
                        $this->db->delete('tsuchanfrage', 'kSuchanfrage', (int)$searchQuery->kSuchanfrage);
                        $this->db->update(
                            'tseo',
                            ['cKey', 'kKey'],
                            ['kSuchanfrage', (int)$searchQuery->kSuchanfrage],
                            (object)['kKey' => (int)$mappedSearch->kSuchanfrage]
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
                $errorMapMessage .= \sprintf(\__('errorSearchMapSelf'), Text::filterXSS($this->request->post($index)));
            }
        }
        $this->alertService->addSuccess($succesMapMessage ?? '', 'successSearchMap');
        $this->alertService->addError($errorMapMessage ?? '', 'errorSearchMap');
        $this->alertService->addSuccess(\__('successSearchRefresh'), 'successSearchRefresh');
    }

    /**
     * @param int $languageID
     * @return void
     */
    private function assignData(int $languageID): void
    {
        $liveSearchSQL = new SqlObject();
        $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
        if (\mb_strlen($this->request->request('cSuche')) > 0) {
            $query = $this->db->escape(Text::filterXSS($this->request->request('cSuche')));
            if (\mb_strlen($query) > 0) {
                $liveSearchSQL->setWhere(' AND tsuchanfrage.cSuche LIKE :srch');
                $liveSearchSQL->addParam('srch', '%' . $query . '%');
                $this->smarty->assign('cSuche', $query);
            } else {
                $this->alertService->addError(\__('errorSearchTermMissing'), 'errorSearchTermMissing');
            }
        }
        if ($this->request->requestInt('nSort') > 0) {
            $this->smarty->assign('nSort', $this->request->requestInt('nSort'));

            switch ($this->request->requestInt('nSort')) {
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
            $this->smarty->assign('nSort', -1);
        }

        $queryCount        = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfrage
                WHERE kSprache = :lid' . $liveSearchSQL->getWhere(),
            'cnt',
            \array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
        );
        $failedQueryCount  = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfrageerfolglos
                WHERE kSprache = :lid',
            'cnt',
            ['lid' => $languageID]
        );
        $mappingCount      = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfragemapping
                WHERE kSprache = :lid',
            'cnt',
            ['lid' => $languageID]
        );
        $paginationQueries = (new Pagination('suchanfragen'))
            ->setItemCount($queryCount)
            ->assemble();
        $paginationFailed  = (new Pagination('erfolglos'))
            ->setItemCount($failedQueryCount)
            ->assemble();
        $paginationMapping = (new Pagination('mapping'))
            ->setItemCount($mappingCount)
            ->assemble();

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
        )->each(static function (stdClass $item): stdClass {
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
        $searchQueries  = $this->db->getObjects(
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

        $this->smarty->assign('Suchanfragen', $searchQueries)
            ->assign('Suchanfragenerfolglos', $failedQueries)
            ->assign('Suchanfragenblacklist', $queryBlacklist)
            ->assign('Suchanfragenmapping', $queryMapping)
            ->assign('oPagiSuchanfragen', $paginationQueries)
            ->assign('oPagiErfolglos', $paginationFailed)
            ->assign('oPagiMapping', $paginationMapping);
    }
}
