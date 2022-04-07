<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Catalog\Navigation;
use JTL\Catalog\NavigationEntry;
use JTL\Filter\Metadata;
use JTL\Helpers\Form;
use JTL\Helpers\URL;
use JTL\News\Category;
use JTL\News\Controller;
use JTL\News\Item;
use JTL\News\ViewType;
use JTL\Pagination\Pagination;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty          = $smarty;
        $pagination            = new Pagination();
        $this->breadCrumbName  = null;
        $this->breadCrumbURL   = null;
        $this->metaTitle       = '';
        $this->metaDescription = '';
        $this->metaKeywords    = '';
        $linkService           = Shop::Container()->getLinkService();
        $link                  = $linkService->getSpecialPage(\LINKTYP_NEWS);
        $controller            = new Controller($this->db, $this->config, $this->smarty);

        switch ($controller->getPageType($this->state->getAsParams())) {
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
                    $controller->addComment($newsItemID, $_POST);
                }
                $controller->displayItem($newsItem, $pagination);

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
                $overview             = $controller->displayOverview(
                    $pagination,
                    $newsCategoryID,
                    0,
                    $this->customerGroupID
                );
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
                $controller->displayOverview($pagination, $newsCategoryID, 0, $this->customerGroupID);
                $this->canonicalURL  = $linkService->getStaticRoute('news.php');
                $this->breadCrumbURL = $this->canonicalURL;
                break;
            case ViewType::NEWS_MONTH_OVERVIEW:
                Shop::setPageType(\PAGE_NEWSMONAT);
                $id                   = $this->state->newsOverviewID;
                $overview             = $controller->displayOverview($pagination, 0, $id, $this->customerGroupID);
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

        if ($controller->getErrorMsg() !== '') {
            $this->alertService->addError($controller->getErrorMsg(), 'newsError');
        }
        if ($controller->getNoticeMsg() !== '') {
            $this->alertService->addNotice($controller->getNoticeMsg(), 'newsNote');
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
}
