<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'news_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

$NaviFilter       = Shop::run();
$cParameter_arr   = Shop::getParameters();
$db               = Shop::Container()->getDB();
$service          = Shop::Container()->getNewsService();
$pagination       = new Pagination();
$breadCrumbName   = null;
$breadCrumbURL    = null;
$cMetaTitle       = '';
$cMetaDescription = '';
$cMetaKeywords    = '';
$Einstellungen    = Shopsetting::getInstance()->getAll();
$customerGroupID  = \Session\Session::CustomerGroup()->getID();
$linkService      = Shop::Container()->getLinkService();
$kLink            = $linkService->getSpecialPageLinkKey(LINKTYP_NEWS);
$link             = $linkService->getPageLink($kLink);
$controller       = new \News\Controller($db, $Einstellungen, $smarty);

switch ($controller->getPageType($cParameter_arr)) {
    case \News\ViewType::NEWS_DETAIL:
        Shop::setPageType(PAGE_NEWSDETAIL);
        Shop::$AktuelleSeite = 'NEWSDETAIL';
        $pagination          = new Pagination('comments');
        $newsItemID          = $cParameter_arr['kNews'];

        $newsItem = new \News\Item($db);
        $newsItem->load($newsItemID);

        $cMetaTitle       = $newsItem->getMetaTitle();
        $cMetaDescription = $newsItem->getMetaDescription();
        $cMetaKeywords    = $newsItem->getMetaKeyword();
        if ((int)($_POST['kommentar_einfuegen'] ?? 0) > 0) {
            $result = $controller->addComment($newsItemID, $_POST);
        }

        $controller->displayItem($newsItem, $pagination);

        $breadCrumbName = $newsItem->getTitle() ?? Shop::Lang()->get('news', 'breadcrumb');
        $breadCrumbURL  = UrlHelper::buildURL($newsItem, URLART_NEWS);

        executeHook(HOOK_NEWS_PAGE_DETAILANSICHT);
        break;
    case \News\ViewType::NEWS_CATEGORY:
        Shop::setPageType(PAGE_NEWSKATEGORIE);
        Shop::$AktuelleSeite = 'NEWSKATEGORIE';
        $kNewsKategorie      = (int)$cParameter_arr['kNewsKategorie'];
        $overview            = $controller->displayOverview($pagination, $kNewsKategorie, 0, $customerGroupID);
        $cCanonicalURL       = $overview->getURL();
        $breadCrumbURL       = $cCanonicalURL;
        $breadCrumbName      = $overview->getName();
        break;
    case \News\ViewType::NEWS_OVERVIEW:
        Shop::$AktuelleSeite = 'NEWS';
        Shop::setPageType(PAGE_NEWS);
        $kNewsKategorie = 0;
        $overview       = $controller->displayOverview($pagination, $kNewsKategorie, 0, $customerGroupID);
        break;
    case \News\ViewType::NEWS_MONTH_OVERVIEW:
        Shop::setPageType(PAGE_NEWSMONAT);
        Shop::$AktuelleSeite = 'NEWSMONAT';
        $id                  = (int)$cParameter_arr['kNewsMonatsUebersicht'];
        $overview            = $controller->displayOverview($pagination, 0, $id, $customerGroupID);
        $cCanonicalURL       = $overview->getURL();
        $breadCrumbURL       = $cCanonicalURL;
        $breadCrumbName      = $overview->getName();

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
    (int)$Einstellungen['metaangaben']['global_meta_maxlaenge_title']
);

Shop::Smarty()->assign('hinweis', $controller->getNoticeMsg())
    ->assign('fehler', $controller->getErrorMsg())
    ->assign('oPagination', $pagination)
    ->assign('code_news', false);

require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('blog/index.tpl');
require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
