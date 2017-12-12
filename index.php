<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require __DIR__ . '/includes/globalinclude.php';

$NaviFilter = Shop::run();
$linkHelper = LinkHelper::getInstance();
if (Shop::$kLink > 0) {
    $link = $linkHelper->getPageLink(Shop::$kLink);
}
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
WarenkorbHelper::checkAdditions();
Shop::getEntryPoint();
$cParameter_arr = Shop::getParameters();
Shop::Smarty()->assign('NaviFilter', $NaviFilter);
if (Shop::$fileName !== null) {
    require PFAD_ROOT . Shop::$fileName;
}
if (Shop::$is404 === true) {
    if (!isset($seo)) {
        $seo = null;
    }
    executeHook(HOOK_INDEX_SEO_404, ['seo' => $seo]);
    if (!Shop::$kLink) {
        $hookInfos     = urlNotFoundRedirect([
            'key'   => 'kLink',
            'value' => $cParameter_arr['kLink']
        ]);
        $kLink         = $hookInfos['value'];
        $bFileNotFound = $hookInfos['isFileNotFound'];
        if (!$kLink) {
            $kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_404);
            Shop::$kLink = $kLink;
        }
    }
    require_once PFAD_ROOT . 'seite.php';
} elseif (Shop::$fileName === null && Shop::getPageType() !== null) {
    require_once PFAD_ROOT . 'seite.php';
}
