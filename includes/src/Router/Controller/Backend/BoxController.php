<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\Revision;
use JTL\Boxes\Type;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Link\LinkGroupInterface;
use JTL\Mapper\PageTypeToPageNiceName;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\filter;
use function Functional\map;
use function Functional\reindex;

/**
 * Class BoxController
 * @package JTL\Router\Controller\Backend
 */
class BoxController extends AbstractBackendController
{
    /**
     * @var array|null
     */
    private ?array $visibility = null;

    private ?int $currentBoxID = null;

    /**
     * @var array
     */
    private static array $validPageTypes = [
        \PAGE_UNBEKANNT,
        \PAGE_ARTIKEL,
        \PAGE_ARTIKELLISTE,
        \PAGE_WARENKORB,
        \PAGE_MEINKONTO,
        \PAGE_KONTAKT,
        \PAGE_NEWS,
        \PAGE_NEWSLETTER,
        \PAGE_LOGIN,
        \PAGE_REGISTRIERUNG,
        \PAGE_BESTELLVORGANG,
        \PAGE_BEWERTUNG,
        \PAGE_PASSWORTVERGESSEN,
        \PAGE_WARTUNG,
        \PAGE_WUNSCHLISTE,
        \PAGE_VERGLEICHSLISTE,
        \PAGE_STARTSEITE,
        \PAGE_VERSAND,
        \PAGE_AGB,
        \PAGE_DATENSCHUTZ,
        \PAGE_LIVESUCHE,
        \PAGE_HERSTELLER,
        \PAGE_SITEMAP,
        \PAGE_GRATISGESCHENK,
        \PAGE_WRB,
        \PAGE_PLUGIN,
        \PAGE_NEWSLETTERARCHIV,
        \PAGE_EIGENE,
        \PAGE_AUSWAHLASSISTENT,
        \PAGE_BESTELLABSCHLUSS,
        \PAGE_404,
        \PAGE_BESTELLSTATUS,
        \PAGE_NEWSMONAT,
        \PAGE_NEWSDETAIL,
        \PAGE_NEWSKATEGORIE
    ];

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::BOXES_VIEW);
        $this->getText->loadAdminLocale('pages/boxen');

        $pageID = Request::verifyGPCDataInt('page');
        $linkID = Request::verifyGPCDataInt('linkID');
        $boxID  = Request::verifyGPCDataInt('item');

        if (Request::postInt('einstellungen') > 0) {
            $this->saveAdminSectionSettings(\CONF_BOXEN, $_POST);
        } elseif (isset($_REQUEST['action']) && !isset($_REQUEST['revision-action']) && Form::validateToken()) {
            switch ($_REQUEST['action']) {
                case 'delete-invisible':
                    $items = !empty($_POST['kInvisibleBox']) && \count($_POST['kInvisibleBox']) > 0
                        ? $_POST['kInvisibleBox']
                        : [];
                    $this->actionDeleteInvisible($items);
                    break;

                case 'new':
                    $this->actionNew(
                        $boxID,
                        $pageID,
                        (int)($_REQUEST['container'] ?? 0),
                        Text::filterXSS($_REQUEST['position'])
                    );
                    break;

                case 'del':
                    $this->actionDelete($boxID);
                    break;

                case 'edit_mode':
                    $this->actionEditMode($boxID);
                    break;

                case 'edit':
                    $this->actionEdit($boxID, $linkID);
                    break;

                case 'resort':
                    $this->actionResort($pageID);
                    break;

                case 'activate':
                    $this->actionActivate($boxID);
                    break;

                case 'container':
                    $this->actionContainer();
                    break;

                default:
                    break;
            }
            $this->cache->flushTags([\CACHING_GROUP_OBJECT, \CACHING_GROUP_BOX, 'boxes']);
            $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        }
        $boxList      = Shop::Container()->getBoxService()->buildList($pageID, false);
        $boxContainer = Shop::Container()->getTemplateService()->getActiveTemplate()->getBoxLayout();

        $this->assignFilterMapping($pageID);
        $this->alertService->addWarning(\__('warningNovaSidebar'), 'warningNovaSidebar', ['dismissable' => false]);
        $this->getAdminSectionSettings(\CONF_BOXEN);
        $this->assignScrollPosition();

        return $smarty->assign('validPageTypes', self::getMappedValidPageTypes())
            ->assign('bBoxenAnzeigen', $this->getVisibility($pageID))
            ->assign('oBoxenLeft_arr', $boxList['left'] ?? [])
            ->assign('oBoxenTop_arr', $boxList['top'] ?? [])
            ->assign('oBoxenBottom_arr', $boxList['bottom'] ?? [])
            ->assign('oBoxenRight_arr', $boxList['right'] ?? [])
            ->assign('oContainerTop_arr', $this->getContainer('top'))
            ->assign('oContainerBottom_arr', $this->getContainer('bottom'))
            ->assign('oVorlagen_arr', $this->getTemplates($pageID))
            ->assign('oBoxenContainer', $boxContainer)
            ->assign('nPage', $pageID)
            ->assign('invisibleBoxes', $this->getInvisibleBoxes())
            ->assign('route', $this->route)
            ->getResponse('boxen.tpl');
    }

    /**
     * @param int $pageID
     * @return void
     */
    private function assignFilterMapping(int $pageID): void
    {
        $filterMapping = [];
        if ($pageID === \PAGE_ARTIKELLISTE) { // map category name
            $filterMapping = $this->db->getObjects('SELECT kKategorie AS id, cName AS name FROM tkategorie');
        } elseif ($pageID === \PAGE_ARTIKEL) { // map article name
            $filterMapping = $this->db->getObjects('SELECT kArtikel AS id, cName AS name FROM tartikel');
        } elseif ($pageID === \PAGE_HERSTELLER) { // map manufacturer name
            $filterMapping = $this->db->getObjects('SELECT kHersteller AS id, cName AS name FROM thersteller');
        } elseif ($pageID === \PAGE_EIGENE) { // 9map page name
            $filterMapping = $this->db->getObjects('SELECT kLink AS id, cName AS name FROM tlink');
        }
        $filterMapping = reindex($filterMapping, static function ($e) {
            return $e->id;
        });
        $filterMapping = map($filterMapping, static function ($e) {
            return $e->name;
        });

        $this->smarty->assign('filterMapping', $filterMapping);
    }

    /**
     * @param array $ids
     * @return void
     */
    private function actionDeleteInvisible(array $ids): void
    {
        $cnt = 0;
        foreach (\array_map('\intval', $ids) as $boxID) {
            if ($this->delete($boxID)) {
                ++$cnt;
            }
        }
        $this->alertService->addSuccess($cnt . \__('successBoxDelete'), 'successBoxDelete');
    }

    /**
     * @param int    $boxID
     * @param int    $pageID
     * @param int    $containerID
     * @param string $position
     * @return void
     */
    private function actionNew(int $boxID, int $pageID, int $containerID, string $position): void
    {
        if ($boxID === 0) {
            // Neuer Container
            if ($this->create(0, $pageID, $position)) {
                $this->alertService->addSuccess(\__('successContainerCreate'), 'successContainerCreate');
            } else {
                $this->alertService->addError(\__('errorContainerCreate'), 'errorContainerCreate');
            }
            return;
        }
        if ($this->create($boxID, $pageID, $position, $containerID)) {
            $this->alertService->addSuccess(\__('successBoxCreate'), 'successBoxCreate');
        } else {
            $this->alertService->addError(\__('errorBoxCreate'), 'errorBoxCreate');
        }

        if ($this->currentBoxID !== null
            && $this->currentBoxID > 0
            && Request::postVar('saveAndContinue')
        ) {
            $this->actionEditMode($this->currentBoxID);
        }
    }

    /**
     * @param int $boxID
     * @return void
     */
    private function actionDelete(int $boxID): void
    {
        $ok = $this->delete($boxID);
        if ($ok) {
            $this->alertService->addSuccess(\__('successBoxDelete'), 'successBoxDelete');
        } else {
            $this->alertService->addError(\__('errorBoxDelete'), 'errorBoxDelete');
        }
    }

    /**
     * @param int $boxID
     * @return void
     */
    private function actionEditMode(int $boxID): void
    {
        $box = $this->getByID($boxID);
        // revisions need this as a different formatted array
        $revisionData = [];
        foreach ($box->oSprache_arr as $lang) {
            $revisionData[$lang->cISO] = $lang;
        }
        $links = Shop::Container()->getLinkService()->getAllLinkGroups()->filter(
            static function (LinkGroupInterface $e): bool {
                return $e->isSpecial() === false;
            }
        );

        $this->smarty->assign('oEditBox', $box)
            ->assign('revisionData', $revisionData)
            ->assign('oLink_arr', $links);
    }

    /**
     * @param int $boxID
     * @param int $linkID
     * @return void
     */
    private function actionEdit(int $boxID, int $linkID): void
    {
        $ok    = false;
        $title = Text::xssClean($_REQUEST['boxtitle']);
        $type  = $_REQUEST['typ'];
        if ($type === Type::TEXT) {
            $oldBox = $this->getByID($boxID);
            if ($oldBox->supportsRevisions === true) {
                $revision = new Revision($this->db);
                $revision->addRevision('box', $boxID, true);
            }
            $ok = $this->update($boxID, $title);
            if ($ok) {
                foreach ($_REQUEST['title'] as $iso => $title) {
                    $content = $_REQUEST['text'][$iso];
                    $ok      = $this->updateLanguage($boxID, $iso, $title, $content);
                    if (!$ok) {
                        break;
                    }
                }
            }
        } elseif (($type === Type::LINK && $linkID > 0) || $type === Type::CATBOX) {
            $ok = $this->update($boxID, $title, $linkID);
            if ($ok) {
                foreach ($_REQUEST['title'] as $iso => $title) {
                    $ok = $this->updateLanguage($boxID, $iso, $title, '');
                    if (!$ok) {
                        break;
                    }
                }
            }
        }

        if ($ok) {
            $this->alertService->addSuccess(\__('successBoxEdit'), 'successBoxEdit');
        } else {
            $this->alertService->addError(\__('errorBoxEdit'), 'errorBoxEdit');
        }

        if (Request::postVar('saveAndContinue')) {
            $this->actionEditMode($boxID);
        }
    }

    /**
     * @param int $pageID
     * @return void
     */
    private function actionResort(int $pageID): void
    {
        $position = Text::filterXSS($_REQUEST['position']);
        $boxes    = \array_map('\intval', $_REQUEST['box'] ?? []);
        $sort     = \array_map('\intval', $_REQUEST['sort'] ?? []);
        $active   = \array_map('\intval', $_REQUEST['aktiv'] ?? []);
        $ignore   = \array_map('\intval', $_REQUEST['ignore'] ?? []);
        $ok       = $this->setVisibility($pageID, $position, (bool)($_REQUEST['box_show'] ?? false));
        foreach ($boxes as $i => $boxIDtoSort) {
            $idx = 'box-filter-' . $boxIDtoSort;
            $this->sort(
                $boxIDtoSort,
                $pageID,
                $sort[$i],
                \in_array($boxIDtoSort, $active, true),
                \in_array($boxIDtoSort, $ignore, true)
            );
            $this->filterBoxVisibility($boxIDtoSort, $pageID, $_POST[$idx] ?? '');
        }
        // see jtlshop/jtl-shop/issues#544 && jtlshop/shop4#41
        if ($position !== 'left' || $pageID > 0) {
            $this->setVisibility($pageID, $position, isset($_REQUEST['box_show']));
        }
        if ($ok) {
            $this->alertService->addSuccess(\__('successBoxRefresh'), 'successBoxRefresh');
        } else {
            $this->alertService->addError(\__('errorBoxesVisibilityEdit'), 'errorBoxesVisibilityEdit');
        }
    }

    /**
     * @param int $boxID
     * @return void
     */
    private function actionActivate(int $boxID): void
    {
        if ($this->activate($boxID, 0, (bool)$_REQUEST['value'])) {
            $this->alertService->addSuccess(\__('successBoxEdit'), 'successBoxEdit');
        } else {
            $this->alertService->addError(\__('errorBoxEdit'), 'errorBoxEdit');
        }
    }

    private function actionContainer(): void
    {
        $position = Text::filterXSS($_REQUEST['position']);
        if ($this->setVisibility(0, $position, (bool)$_GET['value'])) {
            $this->alertService->addSuccess(\__('successBoxEdit'), 'successBoxEdit');
        } else {
            $this->alertService->addError(\__('errorBoxEdit'), 'errorBoxEdit');
        }
    }

    /**
     * @return array
     */
    public static function getValidPageTypes(): array
    {
        return self::$validPageTypes;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $affectedBoxes = $this->db->getInts(
            'SELECT kBox 
                FROM tboxen 
                WHERE kBox = :bid OR kContainer = :bid',
            'kBox',
            ['bid' => $id]
        );

        return \count($affectedBoxes) > 0
            && $this->db->getAffectedRows(
                'DELETE tboxen, tboxensichtbar, tboxsprache
                    FROM tboxen
                    LEFT JOIN tboxensichtbar USING (kBox)
                    LEFT JOIN tboxsprache USING (kBox)
                    WHERE tboxen.kBox IN (' . \implode(',', $affectedBoxes) . ')'
            ) > 0;
    }

    /**
     * @param int $baseType
     * @return stdClass|null
     * @former holeVorlage()
     */
    private function getTemplate(int $baseType): ?stdClass
    {
        return $this->db->select('tboxvorlage', 'kBoxvorlage', $baseType);
    }

    /**
     * @param int    $pageID
     * @param string $position
     * @param int    $containerID
     * @return int
     * @former letzteSortierID()
     */
    private function getLastSortID(int $pageID, string $position = 'left', int $containerID = 0): int
    {
        $box = $this->db->getSingleObject(
            'SELECT tboxensichtbar.nSort, tboxen.ePosition
                FROM tboxensichtbar
                LEFT JOIN tboxen
                    ON tboxensichtbar.kBox = tboxen.kBox
                WHERE tboxensichtbar.kSeite = :pageid
                    AND tboxen.ePosition = :position
                    AND tboxen.kContainer = :containerid
                ORDER BY tboxensichtbar.nSort DESC
                LIMIT 1',
            [
                'pageid'      => $pageID,
                'position'    => $position,
                'containerid' => $containerID
            ]
        );

        return $box ? ++$box->nSort : 0;
    }

    /**
     * @param int    $boxID
     * @param string $isoCode
     * @return stdClass|stdClass[]|null
     */
    public function getContent(int $boxID, string $isoCode = '')
    {
        return $isoCode !== ''
            ? $this->db->select('tboxsprache', 'kBox', $boxID, 'cISO', $isoCode)
            : $this->db->selectAll('tboxsprache', 'kBox', $boxID);
    }

    /**
     * @param int $boxID
     * @return stdClass
     * @throws \InvalidArgumentException
     */
    public function getByID(int $boxID): stdClass
    {
        $box = $this->db->getSingleObject(
            'SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = :bxid',
            ['bxid' => $boxID]
        );
        if ($box === null) {
            throw new \InvalidArgumentException('Cannot load box ' . $boxID);
        }
        $box->oSprache_arr      = ($box && ($box->eTyp === Type::TEXT || $box->eTyp === Type::CATBOX
                || $box->eTyp === Type::LINK))
            ? $this->getContent($boxID)
            : [];
        $box->kBox              = (int)$box->kBox;
        $box->kBoxvorlage       = (int)$box->kBoxvorlage;
        $box->supportsRevisions = $box->kBoxvorlage === \BOX_EIGENE_BOX_OHNE_RAHMEN
            || $box->kBoxvorlage === \BOX_EIGENE_BOX_MIT_RAHMEN;

        return $box;
    }

    /**
     * @param int    $baseID
     * @param int    $pageID
     * @param string $position
     * @param int    $containerID
     * @return bool
     */
    public function create(int $baseID, int $pageID, string $position = 'left', int $containerID = 0): bool
    {
        $validPageTypes   = self::getValidPageTypes();
        $box              = new stdClass();
        $template         = $this->getTemplate($baseID);
        $box->cTitel      = $template === null
            ? ''
            : $template->cName;
        $box->kBoxvorlage = $baseID;
        $box->ePosition   = $position;
        $box->kContainer  = $containerID;
        $box->kCustomID   = (isset($template->kCustomID) && \is_numeric($template->kCustomID))
            ? (int)$template->kCustomID
            : 0;

        $boxID              = $this->db->insert('tboxen', $box);
        $this->currentBoxID = $boxID;
        if ($boxID) {
            $visibility       = new stdClass();
            $visibility->kBox = $boxID;
            foreach ($validPageTypes as $validPageType) {
                $visibility->nSort  = $this->getLastSortID($pageID, $position, $containerID);
                $visibility->kSeite = $validPageType;
                $visibility->bAktiv = ($pageID === $validPageType || $pageID === 0) ? 1 : 0;
                $this->db->insert('tboxensichtbar', $visibility);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int    $boxID
     * @param string $title
     * @param int    $customID
     * @return bool
     * @former bearbeiteBox()
     */
    public function update(int $boxID, string $title, int $customID = 0): bool
    {
        $box            = new stdClass();
        $box->cTitel    = Text::filterXSS($title);
        $box->kCustomID = $customID;

        return $this->db->update('tboxen', 'kBox', $boxID, $box) >= 0;
    }

    /**
     * @param int    $boxID
     * @param string $isoCode
     * @param string $title
     * @param string $content
     * @return bool
     * @former bearbeiteBoxSprache()
     */
    public function updateLanguage(int $boxID, string $isoCode, string $title, string $content): bool
    {
        $box = $this->db->select('tboxsprache', 'kBox', $boxID, 'cISO', $isoCode);
        if (isset($box->kBox)) {
            $upd          = new stdClass();
            $upd->cTitel  = $title;
            $upd->cInhalt = $content;

            return $this->db->update('tboxsprache', ['kBox', 'cISO'], [$boxID, $isoCode], $upd) >= 0;
        }
        $ins          = new stdClass();
        $ins->kBox    = $boxID;
        $ins->cISO    = $isoCode;
        $ins->cTitel  = $title;
        $ins->cInhalt = $content;

        return $this->db->insert('tboxsprache', $ins) > 0;
    }

    /**
     * @param int    $pageID
     * @param string $position
     * @param bool   $show
     * @return bool
     * @former setzeBoxAnzeige()
     */
    public function setVisibility(int $pageID, string $position, bool $show): bool
    {
        $validPageTypes = self::getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            foreach ($validPageTypes as $validPageType) {
                if (!$ok) {
                    break;
                }
                $ok = $this->db->queryPrepared(
                    'INSERT INTO tboxenanzeige 
                        SET bAnzeigen = :show, nSeite = :page, ePosition = :position
                        ON DUPLICATE KEY UPDATE
                          bAnzeigen = :show',
                    [
                        'show'     => (int)$show,
                        'page'     => $validPageType,
                        'position' => $position
                    ]
                );
            }

            return $ok !== 0;
        }

        return $this->db->queryPrepared(
            'INSERT INTO tboxenanzeige 
                SET bAnzeigen = :show, nSeite = :page, ePosition = :position
                ON DUPLICATE KEY UPDATE bAnzeigen = :show',
            [
                    'show'     => (int)$show,
                    'page'     => $pageID,
                    'position' => $position
                ]
        ) !== 0;
    }

    /**
     * @param int  $boxID
     * @param int  $pageID
     * @param int  $sort
     * @param bool $active
     * @param bool $ignore
     * @return bool
     * @former sortBox()
     */
    public function sort(int $boxID, int $pageID, int $sort, bool $active = true, bool $ignore = false): bool
    {
        $validPageTypes = self::getValidPageTypes();
        if ($pageID === 0) {
            $ok = true;
            foreach ($validPageTypes as $validPageType) {
                if (!$ok) {
                    break;
                }
                if ($ignore) {
                    $ok = $this->db->queryPrepared(
                        'INSERT INTO tboxensichtbar (kBox, kSeite, nSort, bAktiv)
                        VALUES (:boxID, :validPageType, :sort, :active)
                        ON DUPLICATE KEY UPDATE
                          nSort = :sort',
                        [
                            'boxID'         => $boxID,
                            'validPageType' => $validPageType,
                            'sort'          => $sort,
                            'active'        => (int)$active
                        ]
                    );
                } else {
                    $ok = $this->db->queryPrepared(
                        'INSERT INTO tboxensichtbar (kBox, kSeite, nSort, bAktiv)
                        VALUES (:boxID, :validPageType, :sort, :active)
                        ON DUPLICATE KEY UPDATE
                          nSort = :sort, bAktiv = :active',
                        [
                            'boxID'         => $boxID,
                            'validPageType' => $validPageType,
                            'sort'          => $sort,
                            'active'        => (int)$active
                        ]
                    );
                }
            }

            return $ok !== 0;
        }

        return $this->db->queryPrepared(
            'INSERT INTO tboxensichtbar (kBox, kSeite, nSort, bAktiv)
                VALUES (:boxID, :validPageType, :sort, :active)
                ON DUPLICATE KEY UPDATE
                  nSort = :sort, bAktiv = :active',
            [
                    'boxID'         => $boxID,
                    'validPageType' => $pageID,
                    'sort'          => $sort,
                    'active'        => (int)$active
                ]
        ) !== 0;
    }

    /**
     * @param int          $boxID
     * @param int          $pageID
     * @param array|string $filter
     * @return int
     */
    public function filterBoxVisibility(int $boxID, int $pageID, array|string $filter = ''): int
    {
        if (\is_array($filter)) {
            $filter = \array_unique($filter);
            $filter = \implode(',', $filter);
        }

        return $this->db->update(
            'tboxensichtbar',
            ['kBox', 'kSeite'],
            [$boxID, $pageID],
            (object)['cFilter' => $filter]
        );
    }

    /**
     * @param int  $boxID
     * @param int  $pageID
     * @param bool $active
     * @return bool
     * @former aktiviereBox()
     */
    public function activate(int $boxID, int $pageID, bool $active = true): bool
    {
        $upd = (object)['bAktiv' => (int)$active];
        if ($pageID === 0) {
            $ok = true;
            foreach (self::getValidPageTypes() as $validPageType) {
                if (!$ok) {
                    break;
                }
                $ok = $this->db->update(
                    'tboxensichtbar',
                    ['kBox', 'kSeite'],
                    [$boxID, $validPageType],
                    $upd
                );
            }

            return $ok;
        }

        return $this->db->update('tboxensichtbar', ['kBox', 'kSeite'], [$boxID, 0], $upd) >= 0;
    }

    /**
     * @param int $pageID
     * @return array
     * @former holeVorlagen()
     */
    public function getTemplates(int $pageID = -1): array
    {
        $templates = [];
        $sql       = $pageID >= 0
            ? 'WHERE (cVerfuegbar = "' . $pageID . '" OR cVerfuegbar = "0")'
            : '';
        $data      = $this->db->getObjects(
            'SELECT * 
                FROM tboxvorlage ' . $sql . ' 
                ORDER BY cVerfuegbar ASC'
        );
        foreach ($data as $template) {
            $id   = 0;
            $name = \__('templateTypeTemplate');
            if ($template->eTyp === Type::TEXT) {
                $id   = 1;
                $name = \__('templateTypeContent');
            } elseif ($template->eTyp === Type::LINK) {
                $id   = 2;
                $name = \__('templateTypeLinkList');
            } elseif ($template->eTyp === Type::PLUGIN) {
                $id   = 3;
                $name = \__('templateTypePlugin');
            } elseif ($template->eTyp === Type::CATBOX) {
                $id   = 4;
                $name = \__('templateTypeCategory');
            } elseif ($template->eTyp === Type::EXTENSION) {
                $id   = 5;
                $name = \__('templateTypeExtension');
            }

            if (!isset($templates[$id])) {
                $templates[$id]               = new stdClass();
                $templates[$id]->oVorlage_arr = [];
            }
            $template->cName                = \__($template->cName);
            $templates[$id]->cName          = $name;
            $templates[$id]->oVorlage_arr[] = $template;
        }

        return $templates;
    }

    /**
     * @param int  $pageID
     * @param bool $global
     * @return array|bool
     * @former holeBoxAnzeige()
     */
    public function getVisibility(int $pageID, bool $global = true)
    {
        if ($this->visibility !== null) {
            return $this->visibility;
        }
        $visibility = [];
        $data       = $this->db->selectAll('tboxenanzeige', 'nSeite', $pageID);
        if (\count($data) > 0) {
            foreach ($data as $box) {
                $visibility[$box->ePosition] = (bool)$box->bAnzeigen;
            }
            $this->visibility = $visibility;

            return $visibility;
        }

        return $pageID !== 0 && $global
            ? $this->getVisibility(0)
            : false;
    }

    /**
     * @param string $position
     * @return array
     * @former holeContainer()
     */
    public function getContainer(string $position): array
    {
        return $this->db->selectAll(
            'tboxen',
            ['kBoxvorlage', 'ePosition'],
            [\BOX_CONTAINER, $position],
            'kBox',
            'kBox ASC'
        );
    }

    /**
     * @return array
     */
    public function getInvisibleBoxes(): array
    {
        $model         = Shop::Container()->getTemplateService()->getActiveTemplate();
        $unavailabe    = filter($model->getBoxLayout(), static function ($e): bool {
            return $e === false;
        });
        $wherePosition = '';
        if (\count($unavailabe) > 0) {
            $mapped        = map($unavailabe, static function ($e, $key) {
                return "'" . $key . "'";
            });
            $wherePosition = ' ePosition IN (' . \implode(',', $mapped) . ') OR ';
        }

        return $this->db->getObjects(
            'SELECT tboxen.*, tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cTemplate 
                FROM tboxen 
                    LEFT JOIN tboxvorlage
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE ' . $wherePosition . ' (kContainer > 0  AND kContainer NOT IN (SELECT kBox FROM tboxen))'
        );
    }

    /**
     * @return array
     */
    public static function getMappedValidPageTypes(): array
    {
        return map(self::$validPageTypes, static function ($pageID) {
            return [
                'pageID'   => $pageID,
                'pageName' => (new PageTypeToPageNiceName())->mapPageTypeToPageNiceName($pageID)
            ];
        });
    }
}
