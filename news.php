<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'news_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

$NaviFilter     = Shop::run();
$cParameter_arr = Shop::getParameters();
Shop::setPageType(PAGE_NEWS);


$db = Shop::Container()->getDB();

$service = Shop::Container()->getNewsService();

$pagination = new Pagination();
//$test = new \News\CommentList($db);
//Shop::dbg($test->getComments(2), true);

//$item = new \News\Item(Shop::Container()->getDB());
//$item->load(1);
//Shop::dbg($item, true, 'ITEM:');
//Shop::dbg($service);
//Shop::dbg(Shop::Lang()->_getIsoFromLangID(1)->cISO);

$breadCrumbName         = null;
$breadCrumbURL          = null;
$cHinweis               = '';
$cFehler                = '';
$step                   = 'news_uebersicht';
$cMetaTitle             = '';
$cMetaDescription       = '';
$cMetaKeywords          = '';
$AktuelleSeite          = 'NEWS';
$Einstellungen          = Shopsetting::getInstance()->getAll();
$nAktuelleSeite         = (Shop::$kSeite !== null && Shop::$kSeite > 0) ? Shop::$kSeite : 1;
$oNewsUebersicht_arr    = [];
$linkHelper             = Shop::Container()->getLinkService();
$kLink                  = $linkHelper->getSpecialPageLinkKey(LINKTYP_NEWS);
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$cUploadVerzeichnis     = PFAD_ROOT . PFAD_NEWSBILDER;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);


$controller = new \News\Controller($db, $Einstellungen, $smarty);
$pageType  = $controller->getPageType($cParameter_arr);

switch ($pageType) {
    case \News\ViewType::NEWS_DISABLED:
        Shop::$is404    = true;
        Shop::$kLink    = 0;
        Shop::$kNews = 0;
        return;
    case \News\ViewType::NEWS_DETAIL:
        Shop::setPageType(PAGE_NEWSDETAIL);
        Shop::$AktuelleSeite = 'NEWSDETAIL';
        $AktuelleSeite       = 'NEWSDETAIL';
        $step                = 'news_detailansicht';

        $kNews = $cParameter_arr['kNews'];

        $newsItem = new \News\Item($db);
        $newsItem->load($kNews);

        $pagination = new Pagination('comments');

        $cMetaTitle         = $newsItem->getMetaTitle();
        $cMetaDescription   = $newsItem->getMetaDescription();
        $cMetaKeywords      = $newsItem->getMetaKeyword();


        if (isset($_POST['kommentar_einfuegen']) && (int)$_POST['kommentar_einfuegen'] > 0) {
            $controller->addComment($kNews, $_POST);
        }

        $controller->displayItem($newsItem, $pagination);

        $breadCrumbName = $newsItem->getTitle() ?? Shop::Lang()->get('news', 'breadcrumb');
        $breadCrumbURL  = UrlHelper::buildURL($newsItem, URLART_NEWS);

        executeHook(HOOK_NEWS_PAGE_DETAILANSICHT);
        break;
    case \News\ViewType::NEWS_CATEGORY:
    case \News\ViewType::NEWS_OVERVIEW:
        if ($pageType === \News\ViewType::NEWS_OVERVIEW) {
            Shop::$AktuelleSeite = 'NEWS';
            $AktuelleSeite       = 'NEWS';
            Shop::setPageType(PAGE_NEWS);
            $kNewsKategorie = 0;
        } else {
            Shop::setPageType(PAGE_NEWSKATEGORIE);
            Shop::$AktuelleSeite = 'NEWSKATEGORIE';
            $AktuelleSeite       = 'NEWSKATEGORIE';
            $kNewsKategorie      = (int)$cParameter_arr['kNewsKategorie'];
        }

//        $oNewsKategorie = new \News\Category($db);
//        $oNewsKategorie->load($kNewsKategorie);
//        Shop::dbg($oNewsKategorie, true, 'NEW:');

//        if (!isset($oNewsKategorie) || !is_object($oNewsKategorie)) {
//            Shop::setPageType(PAGE_NEWS);
//            Shop::$AktuelleSeite                  = 'NEWS';
//            $cFehler                              .= Shop::Lang()->get('newsRestricted', 'news');
//            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
//            baueNewsKruemel(Shop::Smarty(), Shop::$AktuelleSeite, $cCanonicalURL);
//        }
        // Canonical
        if (isset($oNewsKategorie->cSeo)) {
            $cCanonicalURL  = Shop::getURL() . '/' . $oNewsKategorie->cSeo;
            $breadCrumbURL  = $cCanonicalURL;
            $breadCrumbName = $oNewsKategorie->cName;
        }

        $controller->displayOverview($pagination, $kNewsKategorie);

        break;
    case \News\ViewType::NEWS_MONTH_OVERVIEW:
        Shop::setPageType(PAGE_NEWSMONAT);
        Shop::$AktuelleSeite   = 'NEWSMONAT';
        $AktuelleSeite         = 'NEWSMONAT';
        $kNewsMonatsUebersicht = (int)$cParameter_arr['kNewsMonatsUebersicht'];
        $oNewsMonatsUebersicht = getMonthOverview($kNewsMonatsUebersicht);
        Shop::dbg($oNewsMonatsUebersicht, false, '$oNewsMonatsUebersicht:');

        if (isset($oNewsMonatsUebersicht->cSeo)) {
            $cCanonicalURL  = Shop::getURL() . '/' . $oNewsMonatsUebersicht->cSeo;
            $breadCrumbURL  = $cCanonicalURL;
            $breadCrumbName = $oNewsMonatsUebersicht->cName;
        }
        if (!isset($_SESSION['NewsNaviFilter'])) {
            $_SESSION['NewsNaviFilter'] = new stdClass();
        }
        $_SESSION['NewsNaviFilter']->cDatum   = (int)$oNewsMonatsUebersicht->nMonat . '-' .
            (int)$oNewsMonatsUebersicht->nJahr;
        $_SESSION['NewsNaviFilter']->nNewsKat = -1;
        $controller->displayOverview($pagination, 0, $kNewsMonatsUebersicht);

        break;
    default:
        die('default???');
}



$cMetaTitle = \Filter\Metadata::prepareMeta($cMetaTitle, null, (int)$Einstellungen['metaangaben']['global_meta_maxlaenge_title']);

Shop::Smarty()->assign('hinweis', $cHinweis)
    ->assign('fehler', $cFehler)
    ->assign('oPagination', $pagination)
    ->assign('step', $step)
    ->assign('code_news', false);

require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
Shop::Smarty()->display('blog/index.tpl');
require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
