<?php declare(strict_types=1);

namespace JTL\Router\Controller;

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

/**
 * Class NewsController
 * @package JTL\Router\Controller
 */
class NewsController extends AbstractController
{
    public function init(): bool
    {
        parent::init();
        return true;
    }

    public function handleState(JTLSmarty $smarty): void
    {
        echo $this->getResponse($smarty);
    }

    public function getResponse(JTLSmarty $smarty): string
    {
        $pagination       = new Pagination();
        $breadCrumbName   = null;
        $breadCrumbURL    = null;
        $cMetaTitle       = '';
        $cMetaDescription = '';
        $cMetaKeywords    = '';
        $link             = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_NEWS);
        $controller       = new Controller($this->db, $this->config, $smarty);

        switch ($controller->getPageType($this->state->getAsParams())) {
            case ViewType::NEWS_DETAIL:
                Shop::setPageType(\PAGE_NEWSDETAIL);
                $pagination = new Pagination('comments');
                $newsItemID = $this->state->newsItemID;
                $newsItem   = new Item($this->db);
                $newsItem->load($newsItemID);
                $newsItem->checkVisibility($this->customerGroupID);

                $cMetaTitle       = $newsItem->getMetaTitle();
                $cMetaDescription = $newsItem->getMetaDescription();
                $cMetaKeywords    = $newsItem->getMetaKeyword();
                if ((int)($_POST['kommentar_einfuegen'] ?? 0) > 0 && Form::validateToken()) {
                    $result = $controller->addComment($newsItemID, $_POST);
                }

                $controller->displayItem($newsItem, $pagination);

                $breadCrumbName = $newsItem->getTitle() ?? Shop::Lang()->get('news', 'breadcrumb');
                $breadCrumbURL  = URL::buildURL($newsItem, \URLART_NEWS);

                \executeHook(\HOOK_NEWS_PAGE_DETAILANSICHT, [
                    'newsItem'   => $newsItem,
                    'pagination' => $pagination
                ]);
                break;
            case ViewType::NEWS_CATEGORY:
                Shop::setPageType(\PAGE_NEWSKATEGORIE);
                $newsCategoryID     = $this->state->newsCategoryID;
                $overview           = $controller->displayOverview($pagination, $newsCategoryID, 0, $this->customerGroupID);
                $this->canonicalURL = $overview->getURL();
                $breadCrumbURL      = $this->canonicalURL;
                $breadCrumbName     = $overview->getName();
                $newsCategory       = new Category($this->db);
                $newsCategory->load($newsCategoryID);

                $cMetaTitle       = $newsCategory->getMetaTitle();
                $cMetaDescription = $newsCategory->getMetaDescription();
                $cMetaKeywords    = $newsCategory->getMetaKeyword();
                $smarty->assign('robotsContent', 'noindex, follow');
                break;
            case ViewType::NEWS_OVERVIEW:
                Shop::setPageType(\PAGE_NEWS);
                $newsCategoryID = 0;
                $overview       = $controller->displayOverview($pagination, $newsCategoryID, 0, $this->customerGroupID);
                break;
            case ViewType::NEWS_MONTH_OVERVIEW:
                Shop::setPageType(\PAGE_NEWSMONAT);
                $id                 = $this->state->newsOverviewID;
                $overview           = $controller->displayOverview($pagination, 0, $id, $this->customerGroupID);
                $this->canonicalURL = $overview->getURL();
                $breadCrumbURL      = $this->canonicalURL;
                $cMetaTitle         = $overview->getMetaTitle();
                $breadCrumbName     = !empty($overview->getName()) ? $overview->getName() : $cMetaTitle;
                $smarty->assign('robotsContent', 'noindex, follow');
                break;
            case ViewType::NEWS_DISABLED:
            default:
                // @todo
                Shop::$is404 = true;
                Shop::$kLink = 0;
                Shop::$kNews = 0;

                return;
        }

        $cMetaTitle = Metadata::prepareMeta(
            $cMetaTitle,
            null,
            (int)$this->config['metaangaben']['global_meta_maxlaenge_title']
        );

        if ($controller->getErrorMsg() !== '') {
            $this->alertService->addError($controller->getErrorMsg(), 'newsError');
        }
        if ($controller->getNoticeMsg() !== '') {
            $this->alertService->addNotice($controller->getNoticeMsg(), 'newsNote');
        }

        $smarty->assign('oPagination', $pagination)
            ->assign('Link', $link)
            ->assign('code_news', false);

        $this->preRender($smarty);

        return $smarty->getResponse('blog/index.tpl');
    }
}
