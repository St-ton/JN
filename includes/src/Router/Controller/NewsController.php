<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use Exception;
use Illuminate\Support\Collection;
use JTL\Catalog\Navigation;
use JTL\Catalog\NavigationEntry;
use JTL\Filter\Metadata;
use JTL\Helpers\CMS;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\News\Category;
use JTL\News\CategoryList;
use JTL\News\Item;
use JTL\News\ViewType;
use JTL\Pagination\Pagination;
use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use JTL\Smarty\JTLSmarty;
use League\Route\Http\Exception\NotFoundException;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use function Functional\every;

/**
 * Class NewsController
 * @package JTL\Router\Controller
 */
class NewsController extends AbstractController
{
    /**
     * @var string|null
     */
    private ?string $breadCrumbName;

    /**
     * @var string|null
     */
    private ?string $breadCrumbURL;

    /**
     * @var string
     */
    private string $errorMsg = '';

    /**
     * @var string
     */
    private string $noticeMsg = '';

    /**
     * @var string
     */
    protected string $tseoSelector = 'kNews';

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();

        return true;
    }

    /**
     * @param array $args
     * @return State
     */
    public function getStateFromSlug(array $args): State
    {
        $id   = (int)($args['id'] ?? 0);
        $name = $args['name'] ?? null;
        if ($id < 1 && $name === null) {
            return $this->state;
        }
        if ($name !== null) {
            $parser = new DefaultParser($this->db, $this->state);
            $name   = $parser->parse($name);
        }
        $seo = $id > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND kKey = :kid
                      AND kSprache = :lid',
                ['key' => $this->tseoSelector, 'kid' => $id, 'lid' => $this->state->languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey IN (\'kNews\', \'kNewsKategorie\', \'kNewsMonatsUebersicht\')
                        AND cSeo = :seo',
                ['seo' => $name]
            );
        if ($seo === null) {
            return $this->handleSeoError($id, $this->state->languageID);
        }
        $slug          = $seo->cSeo;
        $seo->kKey     = (int)$seo->kKey;
        $seo->kSprache = (int)$seo->kSprache;

        return $this->updateState($seo, $slug);
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $name = \SLUG_ALLOW_SLASHES ? 'name:.+' : 'name';
        $route->get('/' . \ROUTE_PREFIX_NEWS . '/{id:\d+}', [$this, 'getResponse'])
            ->setName('ROUTE_NEWS_BY_ID' . $dynName);
        $route->get('/' . \ROUTE_PREFIX_NEWS . '[/{' . $name . '}]', [$this, 'getResponse'])
            ->setName('ROUTE_NEWS_BY_NAME' . $dynName);
        $route->post('/' . \ROUTE_PREFIX_NEWS . '/{id:\d+}', [$this, 'getResponse'])
            ->setName('ROUTE_NEWS_BY_ID' . $dynName . 'POST');
        $route->post('/' . \ROUTE_PREFIX_NEWS . '/{' . $name . '}', [$this, 'getResponse'])
            ->setName('ROUTE_NEWS_BY_NAME' . $dynName . 'POST');
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        if (!isset($args['name'])) {
            $args['name'] = '';
        }
        $this->getStateFromSlug($args);
        if (!$this->init()) {
            return $this->notFoundResponse($request, $args, $smarty);
        }
        $this->smarty          = $smarty;
        $pagination            = new Pagination();
        $this->breadCrumbName  = null;
        $this->breadCrumbURL   = null;
        $this->metaTitle       = '';
        $this->metaDescription = '';
        $this->metaKeywords    = '';
        $linkService           = Shop::Container()->getLinkService();
        $link                  = $linkService->getSpecialPage(\LINKTYP_NEWS);
        switch ($this->getPageType($this->state->getAsParams())) {
            case ViewType::NEWS_DETAIL:
                Shop::setPageType(\PAGE_NEWSDETAIL);
                $pagination = new Pagination('comments');
                $newsItemID = $this->state->newsItemID;
                $newsItem   = new Item($this->db);
                $newsItem->load($newsItemID);
                $newsItem->checkVisibility($this->customerGroupID);
                $this->canonicalURL    = $newsItem->getURL();
                $this->metaTitle       = $newsItem->getMetaTitle();
                $this->metaDescription = $newsItem->getMetaDescription();
                $this->metaKeywords    = $newsItem->getMetaKeyword();
                if ((int)($_POST['kommentar_einfuegen'] ?? 0) > 0 && Form::validateToken()) {
                    $this->addComment($newsItemID, $_POST);
                }
                $this->displayItem($newsItem, $pagination);

                $this->breadCrumbName = $newsItem->getTitle() ?? Shop::Lang()->get('news', 'breadcrumb');
                $this->breadCrumbURL  = URL::buildURL($newsItem, \URLART_NEWS);

                \executeHook(\HOOK_NEWS_PAGE_DETAILANSICHT, [
                    'newsItem'   => $newsItem,
                    'pagination' => $pagination
                ]);
                break;
            case ViewType::NEWS_CATEGORY:
                Shop::setPageType(\PAGE_NEWSKATEGORIE);
                $newsCategoryID       = $this->state->newsCategoryID;
                $overview             = $this->displayOverview($pagination, $newsCategoryID);
                $this->breadCrumbName = $overview->getName();
                $newsCategory         = new Category($this->db);
                $newsCategory->load($newsCategoryID);
                $this->canonicalURL    = $newsCategory->getURL();
                $this->breadCrumbURL   = $this->canonicalURL;
                $this->metaTitle       = $newsCategory->getMetaTitle();
                $this->metaDescription = $newsCategory->getMetaDescription();
                $this->metaKeywords    = $newsCategory->getMetaKeyword();
                $this->smarty->assign('robotsContent', 'noindex, follow');
                break;
            case ViewType::NEWS_OVERVIEW:
                Shop::setPageType(\PAGE_NEWS);
                $newsCategoryID = 0;
                try {
                    $page                  = $linkService->getSpecialPage(\LINKTYP_NEWS);
                    $this->canonicalURL    = $page->getURL($this->languageID);
                    $this->metaTitle       = $page->getMetaTitle();
                    $this->metaDescription = $page->getMetaDescription();
                    $this->metaKeywords    = $page->getMetaKeyword();
                } catch (Exception) {
                    $this->canonicalURL = $linkService->getStaticRoute('news.php');
                }
                $this->displayOverview($pagination, $newsCategoryID);

                $this->breadCrumbURL = $this->canonicalURL;
                break;
            case ViewType::NEWS_MONTH_OVERVIEW:
                Shop::setPageType(\PAGE_NEWSMONAT);
                $id                   = $this->state->newsOverviewID;
                $overview             = $this->displayOverview($pagination, 0, $id);
                $this->canonicalURL   = $overview->getURL();
                $this->breadCrumbURL  = $this->canonicalURL;
                $this->metaTitle      = $overview->getMetaTitle();
                $this->breadCrumbName = !empty($overview->getName()) ? $overview->getName() : $this->metaTitle;
                $this->smarty->assign('robotsContent', 'noindex, follow');
                break;
            case ViewType::NEWS_DISABLED:
            default:
                throw new NotFoundException();
        }

        $this->metaTitle = Metadata::prepareMeta(
            $this->metaTitle,
            null,
            (int)$this->config['metaangaben']['global_meta_maxlaenge_title']
        );

        if ($this->getErrorMsg() !== '') {
            $this->alertService->addError($this->getErrorMsg(), 'newsError');
        }
        if ($this->getNoticeMsg() !== '') {
            $this->alertService->addNotice($this->getNoticeMsg(), 'newsNote');
        }

        $this->smarty->assign('oPagination', $pagination)
            ->assign('Link', $link)
            ->assign('code_news', false);

        $this->preRender();

        return $this->smarty->getResponse('blog/index.tpl');
    }

    /**
     * @inheritdoc
     */
    protected function getNavigation(): Navigation
    {
        $nav = parent::getNavigation();
        if ($this->breadCrumbName !== null && $this->breadCrumbURL !== null) {
            $breadCrumbEntry = new NavigationEntry();
            $breadCrumbEntry->setURL($this->breadCrumbURL);
            $breadCrumbEntry->setName($this->breadCrumbName);
            $breadCrumbEntry->setURLFull($this->breadCrumbURL);
            $nav->setCustomNavigationEntry($breadCrumbEntry);
        }

        return $nav;
    }

    /**
     * @param array $params
     * @return int
     */
    protected function getPageType(array $params): int
    {
        if (!isset($_SESSION['NewsNaviFilter'])) {
            $_SESSION['NewsNaviFilter'] = new stdClass();
        }
        if (Request::verifyGPCDataInt('nSort') > 0) {
            $_SESSION['NewsNaviFilter']->nSort = Request::verifyGPCDataInt('nSort');
        } elseif (Request::verifyGPCDataInt('nSort') === -1) {
            $_SESSION['NewsNaviFilter']->nSort = -1;
        } elseif (!isset($_SESSION['NewsNaviFilter']->nSort)) {
            $_SESSION['NewsNaviFilter']->nSort = 1;
        }
        if ((int)$params['cDatum'] === -1) {
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        } elseif (\mb_strlen($params['cDatum']) > 0) {
            $_SESSION['NewsNaviFilter']->cDatum = \mb_substr_count($params['cDatum'], '-') > 0
                ? Text::filterXSS($params['cDatum'])
                : -1;
        } elseif (!isset($_SESSION['NewsNaviFilter']->cDatum)) {
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        }
        if ($params['nNewsKat'] > 0) {
            $_SESSION['NewsNaviFilter']->nNewsKat = $params['nNewsKat'];
        } elseif (!isset($_SESSION['NewsNaviFilter']->nNewsKat) || $params['nNewsKat'] === -1) {
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
        }
        if ($this->config['news']['news_benutzen'] !== 'Y') {
            return ViewType::NEWS_DISABLED;
        }
        $currentNewsType = ViewType::NEWS_OVERVIEW;
        if ($params['kNews'] > 0) {
            $currentNewsType = ViewType::NEWS_DETAIL;
        } elseif ($params['kNewsKategorie'] > 0) {
            $currentNewsType = ViewType::NEWS_CATEGORY;
        } elseif ($params['kNewsMonatsUebersicht'] > 0) {
            $currentNewsType = ViewType::NEWS_MONTH_OVERVIEW;
            if (($data = $this->getMonthOverview($params['kNewsMonatsUebersicht'])) !== null) {
                $_SESSION['NewsNaviFilter']->cDatum   = $data->nMonat . '-' . $data->nJahr;
                $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            }
        }
        $this->smarty->assign('oDatum_arr', $this->getNewsDates(self::getFilterSQL(true)))
            ->assign('nPlausiValue_arr', [
                'cKommentar' => 0,
                'nAnzahl'    => 0,
                'cEmail'     => 0,
                'cName'      => 0,
                'captcha'    => 0
            ]);

        return $currentNewsType;
    }

    /**
     * @param int $id
     * @return stdClass|null
     */
    private function getMonthOverview(int $id): ?stdClass
    {
        $item = $this->db->getSingleObject(
            "SELECT tnewsmonatsuebersicht.*, tseo.cSeo
                FROM tnewsmonatsuebersicht
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kNewsMonatsUebersicht'
                    AND tseo.kKey = :nmi
                    AND tseo.kSprache = :lid
                WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = :nmi",
            [
                'nmi' => $id,
                'lid' => $this->languageID
            ]
        );
        if ($item !== null) {
            $item->kNewsMonatsUebersicht = (int)$item->kNewsMonatsUebersicht;
            $item->kSprache              = (int)$item->kSprache;
            $item->nMonat                = (int)$item->nMonat;
            $item->nJahr                 = (int)$item->nJahr;
        }

        return $item;
    }

    /**
     * @param Item       $newsItem
     * @param Pagination $pagination
     */
    protected function displayItem(Item $newsItem, Pagination $pagination): void
    {
        $newsCategories = $this->getNewsCategories($newsItem->getID());
        $comments       = $newsItem->getComments()->getThreadedItems()->filter(static function ($item): bool {
            return $item->isActive();
        });
        $itemsPerPage   = ($perPage = (int)$this->config['news']['news_kommentare_anzahlproseite']) > 0
            ? [$perPage, $perPage * 2, $perPage * 5]
            : [10, 20, 50];
        $pagination->setItemsPerPageOptions($itemsPerPage)
            ->setItemCount($comments->count())
            ->assemble();
        if ($pagination->getItemsPerPage() > 0) {
            $comments = $comments->forPage(
                $pagination->getPage() + 1,
                $pagination->getItemsPerPage()
            );
        }
        if ($newsItem->isVisible()) {
            $this->smarty->assign('oNewsKommentar_arr', $comments)
                ->assign('comments', $comments)
                ->assign('cNewsErr', false)
                ->assign('oPagiComments', $pagination)
                ->assign('oNewsKategorie_arr', $newsCategories)
                ->assign('oNewsArchiv', $newsItem)
                ->assign('newsItem', $newsItem)
                ->assign('userCanComment', Frontend::getCustomer()->getID() > 0)
                ->assign('oNews_arr', $this->config['news']['news_benutzen'] === 'Y'
                    ? CMS::getHomeNews($this->config)
                    : []);
        } else {
            $this->smarty->assign('cNewsErr', true)
                ->assign('newsItem', $newsItem);
        }
    }

    /**
     * @param Pagination $pagination
     * @param int        $categoryID
     * @param int        $monthOverviewID
     * @return Category
     */
    public function displayOverview(Pagination $pagination, int $categoryID = 0, int $monthOverviewID = 0): Category
    {
        $category = new Category($this->db);
        if ($categoryID > 0) {
            $category->load($categoryID);
        } elseif ($monthOverviewID > 0) {
            $category->getMonthOverview($monthOverviewID);
        } else {
            $category->getOverview(self::getFilterSQL());
        }
        $items         = $category->filterAndSortItems($this->customerGroupID, $this->languageID);
        $newsCountShow = ($conf = (int)$this->config['news']['news_anzahl_uebersicht']) > 0
            ? $conf
            : 10;
        $pagination->setItemsPerPageOptions([$newsCountShow, $newsCountShow * 2, $newsCountShow * 5])
            ->setItemCount($category->getItems()->count())
            ->assemble();
        if ($pagination->getItemsPerPage() > -1) {
            $items = $items->forPage(
                $pagination->getPage() + 1,
                $pagination->getItemsPerPage()
            );
        }
        $this->smarty->assign('oNewsUebersicht_arr', $items)
            ->assign('newsItems', $items)
            ->assign('noarchiv', 0)
            ->assign('oNewsKategorie_arr', $this->getAllNewsCategories(true))
            ->assign('nSort', $_SESSION['NewsNaviFilter']->nSort)
            ->assign('cDatum', $_SESSION['NewsNaviFilter']->cDatum)
            ->assign('oNewsCat', $category)
            ->assign('oPagination', $pagination)
            ->assign('kNewsKategorie', $_SESSION['NewsNaviFilter']->nNewsKat);
        if ($items->count() === 0) {
            $this->smarty->assign('noarchiv', 1);
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            $_SESSION['NewsNaviFilter']->cDatum   = -1;
        }

        \executeHook(\HOOK_NEWS_PAGE_NEWSUEBERSICHT, [
            'category' => $category,
            'items'    => $items
        ]);

        return $category;
    }

    /**
     * @param bool $activeOnly
     * @return Collection
     */
    public function getAllNewsCategories(bool $activeOnly = false): Collection
    {
        $itemList = new CategoryList($this->db);
        $ids      = $this->db->getInts(
            'SELECT node.kNewsKategorie AS id
                FROM tnewskategorie AS node INNER JOIN tnewskategorie AS parent
                WHERE node.lvl > 0 
                    AND parent.lvl > 0 ' . ($activeOnly ? ' AND node.nAktiv = 1 ' : '') .
            ' GROUP BY node.kNewsKategorie
                ORDER BY node.lft, node.nSort ASC',
            'id'
        );
        $itemList->createItems($ids);

        return $itemList->generateTree();
    }

    /**
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function addComment(int $id, array $data): bool
    {
        if ($this->config['news']['news_kommentare_nutzen'] !== 'Y') {
            return false;
        }
        $checks    = self::checkComment($data, $id, $this->config);
        $checkedOK = every($checks, static function ($e): bool {
            return $e === 0;
        });

        \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_PLAUSI);

        if (Frontend::getCustomer()->getID() <= 0) {
            return true;
        }
        if ($checkedOK) {
            $comment             = new stdClass();
            $comment->kNews      = (int)$data['kNews'];
            $comment->kKunde     = (int)$_SESSION['Kunde']->kKunde;
            $comment->nAktiv     = $this->config['news']['news_kommentare_freischalten'] === 'Y' ? 0 : 1;
            $comment->cName      = $_SESSION['Kunde']->cVorname . ' ' . $_SESSION['Kunde']->cNachname[0] . '.';
            $comment->cEmail     = $_SESSION['Kunde']->cMail;
            $comment->cKommentar = Text::htmlentities(Text::filterXSS($data['cKommentar']));
            $comment->dErstellt  = 'now()';

            \executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$comment]);

            $this->db->insert('tnewskommentar', $comment);
            if ($this->config['news']['news_kommentare_freischalten'] === 'Y') {
                $this->noticeMsg .= Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br>';
            } else {
                $this->noticeMsg .= Shop::Lang()->get('newscommentAdd', 'messages') . '<br>';
            }
        } else {
            $this->errorMsg .= self::getCommentErrors($checks);
            $this->smarty->assign('nPlausiValue_arr', $checks)
                ->assign('cPostVar_arr', Text::filterXSS($data));
        }

        return true;
    }

    /**
     * @param array $post
     * @param int   $newsID
     * @param array $config
     * @return array
     */
    public static function checkComment(array $post, int $newsID, array $config): array
    {
        $checks = [
            'cKommentar' => 0,
            'nAnzahl'    => 0,
            'cEmail'     => 0,
            'cName'      => 0,
            'captcha'    => 0
        ];
        if (empty($post['cKommentar'])) {
            $checks['cKommentar'] = 1;
        } elseif (\mb_strlen($post['cKommentar']) > 1000) {
            $checks['cKommentar'] = 2;
        }
        if (Frontend::getCustomer()->getID() > 0 && $newsID > 0) {
            $commentCount = Shop::Container()->getDB()->getSingleInt(
                'SELECT COUNT(*) AS cnt
                    FROM tnewskommentar
                    WHERE kNews = :nid
                        AND kKunde = :cid',
                'cnt',
                ['nid' => $newsID, 'cid' => Frontend::getCustomer()->getID()]
            );

            if ($commentCount > (int)$config['news']['news_kommentare_anzahlprobesucher']
                && (int)$config['news']['news_kommentare_anzahlprobesucher'] !== 0
            ) {
                $checks['nAnzahl'] = 1;
            }
            $post['cEmail'] = Frontend::getCustomer()->cMail;
        } else {
            // Kunde ist nicht eingeloggt - Name prüfen
            if (empty($post['cName'])) {
                $checks['cName'] = 1;
            }
            if (empty($post['cEmail']) || Text::filterEmailAddress($post['cEmail']) === false) {
                $checks['cEmail'] = 1;
            }
        }
        if ((!isset($checks['cName']) || !$checks['cName']) && SimpleMail::checkBlacklist($post['cEmail'])) {
            $checks['cEmail'] = 2;
        }

        return $checks;
    }

    /**
     * @param array $checks
     * @return string
     */
    public static function getCommentErrors(array $checks): string
    {
        $msg = '';
        if ($checks['cKommentar'] > 0) {
            // Kommentarfeld ist leer
            if ($checks['cKommentar'] === 1) {
                $msg .= Shop::Lang()->get('newscommentMissingtext', 'errorMessages') . '<br />';
            } elseif ($checks['cKommentar'] === 2) {
                // Kommentar ist länger als 1000 Zeichen
                $msg .= Shop::Lang()->get('newscommentLongtext', 'errorMessages') . '<br />';
            }
        }
        // Kunde hat bereits einen Newskommentar zu der aktuellen News geschrieben
        if ($checks['nAnzahl'] === 1) {
            $msg .= Shop::Lang()->get('newscommentAlreadywritten', 'errorMessages') . '<br />';
        }
        // Kunde ist nicht eingeloggt und das Feld Name oder Email ist leer
        if ($checks['cName'] === 1 || $checks['cEmail'] === 1) {
            $msg .= Shop::Lang()->get('newscommentMissingnameemail', 'errorMessages') . '<br />';
        }
        // Emailadresse ist auf der Blacklist
        if ($checks['cEmail'] === 2) {
            $msg .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
        }

        return $msg;
    }

    /**
     * @param bool $activeOnly
     * @return stdClass
     */
    public static function getFilterSQL(bool $activeOnly = false): stdClass
    {
        $sql              = new stdClass();
        $sql->cDatumSQL   = '';
        $sql->cNewsKatSQL = '';
        $sql->cSortSQL    = match ((int)$_SESSION['NewsNaviFilter']->nSort) {
            2       => ' ORDER BY tnews.dGueltigVon',
            3       => ' ORDER BY tnewssprache.title',
            4       => ' ORDER BY tnewssprache.title DESC',
            5       => ' ORDER BY nNewsKommentarAnzahl DESC',
            6       => ' ORDER BY nNewsKommentarAnzahl',
            default => ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC',
        };
        if ($_SESSION['NewsNaviFilter']->cDatum !== -1 && \mb_strlen($_SESSION['NewsNaviFilter']->cDatum) > 0) {
            $date = \explode('-', $_SESSION['NewsNaviFilter']->cDatum);
            if (\count($date) > 1) {
                [$nMonat, $nJahr] = $date;
                $sql->cDatumSQL   = ' AND MONTH(tnews.dGueltigVon) = ' . (int)$nMonat . ' 
                                      AND YEAR(tnews.dGueltigVon) = ' . (int)$nJahr;
            } else { //invalid date given/xss -> reset to -1
                $_SESSION['NewsNaviFilter']->cDatum = -1;
            }
        }
        $catID = (int)($_SESSION['NewsNaviFilter']->nNewsKat ?? 0);
        if ($catID > 0) {
            $sql->cNewsKatSQL = ' AND tnewskategorienews.kNewsKategorie = ' . $catID;
        }
        if ($activeOnly) {
            $sql->cNewsKatSQL .= ' JOIN tnewskategorie 
                                   ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                                   AND tnewskategorie.nAktiv = 1';
        }

        return $sql;
    }

    /**
     * @param stdClass $sql
     * @return stdClass[]
     */
    private function getNewsDates(stdClass $sql): array
    {
        $dateData = $this->db->getObjects(
            'SELECT MONTH(tnews.dGueltigVon) AS nMonat, YEAR(tnews.dGueltigVon) AS nJahr
                FROM tnews
                JOIN tnewskategorienews
                    ON tnewskategorienews.kNews = tnews.kNews' . $sql->cNewsKatSQL . "
                JOIN tnewssprache
                    ON tnewssprache.kNews = tnews.kNews
                WHERE tnews.nAktiv = 1
                    AND tnews.dGueltigVon <= NOW()
                    AND (tnews.cKundengruppe LIKE '%;-1;%'
                        OR FIND_IN_SET(:cgid, REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                    AND tnewssprache.languageID = :lid
                GROUP BY nJahr, nMonat
                ORDER BY dGueltigVon DESC",
            ['lid' => $this->languageID, 'cgid' => $this->customerGroupID]
        );
        $dates    = [];
        $code     = Shop::getLanguageCode();
        foreach ($dateData as $date) {
            $item        = new stdClass();
            $item->cWert = $date->nMonat . '-' . $date->nJahr;
            $item->cName = self::mapDateName((int)$date->nMonat, (int)$date->nJahr, $code);
            $dates[]     = $item;
        }

        return $dates;
    }

    /**
     * @param string|int $month
     * @param string|int $year
     * @param string     $langCode
     * @return string
     */
    public static function mapDateName($month, $year, string $langCode): string
    {
        $month = (int)$month;
        $year  = (int)$year;
        $name  = '';
        // @todo: i18n!
        if ($langCode === 'ger') {
            switch ($month) {
                case 1:
                    return Shop::Lang()->get('january', 'news') . ',' . $year;
                case 2:
                    return Shop::Lang()->get('february', 'news') . ' ' . $year;
                case 3:
                    return Shop::Lang()->get('march', 'news') . ' ' . $year;
                case 4:
                    return Shop::Lang()->get('april', 'news') . ' ' . $year;
                case 5:
                    return Shop::Lang()->get('may', 'news') . ' ' . $year;
                case 6:
                    return Shop::Lang()->get('june', 'news') . ' ' . $year;
                case 7:
                    return Shop::Lang()->get('july', 'news') . ' ' . $year;
                case 8:
                    return Shop::Lang()->get('august', 'news') . ' ' . $year;
                case 9:
                    return Shop::Lang()->get('september', 'news') . ' ' . $year;
                case 10:
                    return Shop::Lang()->get('october', 'news') . ' ' . $year;
                case 11:
                    return Shop::Lang()->get('november', 'news') . ' ' . $year;
                case 12:
                    return Shop::Lang()->get('december', 'news') . ' ' . $year;
            }
        } else {
            $name .= \date('F', \mktime(0, 0, 0, $month, 1, $year)) . ', ' . $year;
        }

        return $name;
    }

    /**
     * @param int $newsItemID
     * @return Category[]
     */
    public function getNewsCategories(int $newsItemID): array
    {
        $categoryIDs = $this->db->getInts(
            'SELECT kNewsKategorie
                FROM tnewskategorienews
                WHERE kNews = :nid',
            'kNewsKategorie',
            ['nid' => $newsItemID]
        );
        $items       = [];
        foreach ($categoryIDs as $categoryID) {
            $items[] = (new Category($this->db))->load($categoryID);
        }

        return $items;
    }

    /**
     * @return string
     */
    public function getErrorMsg(): string
    {
        return $this->errorMsg;
    }

    /**
     * @param string $errorMsg
     */
    public function setErrorMsg(string $errorMsg): void
    {
        $this->errorMsg = $errorMsg;
    }

    /**
     * @return string
     */
    public function getNoticeMsg(): string
    {
        return $this->noticeMsg;
    }

    /**
     * @param string $noticeMsg
     */
    public function setNoticeMsg(string $noticeMsg): void
    {
        $this->noticeMsg = $noticeMsg;
    }
}
