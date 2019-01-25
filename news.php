<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\URL;
use Pagination\Pagination;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'news_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

$NaviFilter       = Shop::run();
$params           = Shop::getParameters();
$db               = Shop::Container()->getDB();
$service          = Shop::Container()->getNewsService();
$pagination       = new Pagination();
$breadCrumbName   = null;
$breadCrumbURL    = null;
$cMetaTitle       = '';
$cMetaDescription = '';
$cMetaKeywords    = '';
$conf             = Shopsetting::getInstance()->getAll();
$customerGroupID  = \Session\Frontend::getCustomerGroup()->getID();
$linkService      = Shop::Container()->getLinkService();
$link             = $linkService->getPageLink($linkService->getSpecialPageLinkKey(LINKTYP_NEWS));
$controller       = new \News\Controller($db, $conf, $smarty);

switch ($controller->getPageType($params)) {
    case \News\ViewType::NEWS_DETAIL:
        Shop::setPageType(PAGE_NEWSDETAIL);
        $pagination = new Pagination('comments');
        $newsItemID = $params['kNews'];
        $newsItem   = new \News\Item($db);
        $newsItem->load($newsItemID);

        $cMetaTitle       = $newsItem->getMetaTitle();
        $cMetaDescription = $newsItem->getMetaDescription();
        $cMetaKeywords    = $newsItem->getMetaKeyword();
        if ((int)($_POST['kommentar_einfuegen'] ?? 0) > 0) {
            $result = $controller->addComment($newsItemID, $_POST);
        }

        $controller->displayItem($newsItem, $pagination);

        $breadCrumbName = $newsItem->getTitle() ?? Shop::Lang()->get('news', 'breadcrumb');
        $breadCrumbURL  = URL::buildURL($newsItem, URLART_NEWS);

        executeHook(HOOK_NEWS_PAGE_DETAILANSICHT, [
            'newsItem'   => $newsItem,
            'pagination' => $pagination
        ]);
        break;
    case \News\ViewType::NEWS_CATEGORY:
        Shop::setPageType(PAGE_NEWSKATEGORIE);
        $kNewsKategorie = (int)$params['kNewsKategorie'];
        $overview       = $controller->displayOverview($pagination, $kNewsKategorie, 0, $customerGroupID);
        $cCanonicalURL  = $overview->getURL();
        $breadCrumbURL  = $cCanonicalURL;
        $breadCrumbName = $overview->getName();
        break;
    case \News\ViewType::NEWS_OVERVIEW:
        Shop::setPageType(PAGE_NEWS);
        $kNewsKategorie = 0;
        $overview       = $controller->displayOverview($pagination, $kNewsKategorie, 0, $customerGroupID);
        break;
    case \News\ViewType::NEWS_MONTH_OVERVIEW:
        Shop::setPageType(PAGE_NEWSMONAT);
        $id             = (int)$params['kNewsMonatsUebersicht'];
        $overview       = $controller->displayOverview($pagination, 0, $id, $customerGroupID);
        $cCanonicalURL  = $overview->getURL();
        $breadCrumbURL  = $cCanonicalURL;
        $breadCrumbName = $overview->getName();

        break;
    case \News\ViewType::NEWS_DISABLED:
    default:
        Shop::$is404 = true;
        Shop::$kLink = 0;
        Shop::$kNews = 0;

        return;
}

$cMetaTitle = \Filter\Metadata::prepareMeta(
    $cMetaTitle,
    null,
    (int)$conf['metaangaben']['global_meta_maxlaenge_title']
);

Shop::Smarty()->assign('hinweis', $controller->getNoticeMsg())
    ->assign('fehler', $controller->getErrorMsg())
    ->assign('oPagination', $pagination)
    ->assign('code_news', false);

require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('blog/index.tpl');
require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
