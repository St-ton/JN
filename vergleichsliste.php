<?php declare(strict_types=1);

use JTL\Cart\CartHelper;
use JTL\Catalog\ComparisonList;
use JTL\Helpers\Request;
use JTL\Shop;

require_once __DIR__ . '/includes/globalinclude.php';

Shop::setPageType(PAGE_VERGLEICHSLISTE);
$conf        = Shop::getSettings([CONF_VERGLEICHSLISTE, CONF_ARTIKELDETAILS]);
$compareList = new ComparisonList();
$attrVar     = $compareList->buildAttributeAndVariation();
$compareList->save();

if (Request::verifyGPCDataInt('addToCart') !== 0) {
    CartHelper::addProductIDToCart(
        Request::verifyGPCDataInt('addToCart'),
        Request::verifyGPDataString('anzahl')
    );
    Shop::Container()->getAlertService()->addNotice(Shop::Lang()->get('basketAdded', 'messages'), 'basketAdded');
}

$colWidth = ($conf['vergleichsliste']['vergleichsliste_spaltengroesse'] > 0)
    ? (int)$conf['vergleichsliste']['vergleichsliste_spaltengroesse']
    : 200;
Shop::Smarty()->assign('nBreiteTabelle', $colWidth * (count($compareList->oArtikel_arr) + 1))
    ->assign('cPrioSpalten_arr', $compareList->getPrioRows(true, false))
    ->assign('prioRows', $compareList->getPrioRows())
    ->assign('Link', Shop::Container()->getLinkService()->getPageLink(LINKTYP_VERGLEICHSLISTE))
    ->assign('oMerkmale_arr', $attrVar[0])
    ->assign('oVariationen_arr', $attrVar[1])
    ->assign('print', (int)(Request::getInt('print') === 1))
    ->assign('oVergleichsliste', $compareList)
    ->assignDeprecated('Einstellungen_Vergleichsliste', $conf, '5.2.0');

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_VERGLEICHSLISTE_PAGE);

Shop::Smarty()->display('comparelist/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
