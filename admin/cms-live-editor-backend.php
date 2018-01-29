<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
/** @global JTLSmarty $smarty */
$hinweis            = '';
$fehler             = '';
$step               = 'uebersicht';
$links              = null;
$clearCache         = false;
$continue           = true;


if ($step === 'uebersicht') {
    $lCount = Shop::DB()->query("SELECT COUNT(kPage) AS anz FROM tcmspage;", 1);

    // Paginationen
    $oPagiCmsLinks = (new Pagination('cmsLinks'))
        ->setItemCount((int)$lCount->anz)
        ->assemble();

    $links  = Shop::DB()->query("SELECT * FROM tcmspage LIMIT " . $oPagiCmsLinks->getLimitSQL(), 2);
    foreach ($links as &$link) {
        $cmsPage = new CMSPage($link->kPage);
        $cmsPage->renderPreview();
        $link->preview = $cmsPage->cPreviewHtml_arr;
    }
}


$smarty->assign('step', $step)
    ->assign('hinweis', $hinweis)
    ->assign('fehler', $fehler)
    ->assign('links', $links)
    ->assign('oPagiCmsLinks', $oPagiCmsLinks)
    ->display('cmslinks.tpl');
