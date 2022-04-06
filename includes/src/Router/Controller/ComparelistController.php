<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Cart\CartHelper;
use JTL\Catalog\ComparisonList;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ComparelistController
 * @package JTL\Router\Controller
 */
class ComparelistController extends AbstractController
{
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
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_VERGLEICHSLISTE);
        $compareList = new ComparisonList();
        $attrVar     = $compareList->buildAttributeAndVariation();
        $compareList->save();

        if (Request::verifyGPCDataInt('addToCart') !== 0) {
            CartHelper::addProductIDToCart(
                Request::verifyGPCDataInt('addToCart'),
                Request::verifyGPDataString('anzahl')
            );
            $this->alertService->addNotice(
                Shop::Lang()->get('basketAdded', 'messages'),
                'basketAdded'
            );
        }

        $colWidth = ($this->config['vergleichsliste']['vergleichsliste_spaltengroesse'] > 0)
            ? (int)$this->config['vergleichsliste']['vergleichsliste_spaltengroesse']
            : 200;
        $this->smarty->assign('nBreiteTabelle', $colWidth * (\count($compareList->oArtikel_arr) + 1))
            ->assign('cPrioSpalten_arr', $compareList->getPrioRows(true, false))
            ->assign('prioRows', $compareList->getPrioRows())
            ->assign('Link', Shop::Container()->getLinkService()->getPageLink(\LINKTYP_VERGLEICHSLISTE))
            ->assign('oMerkmale_arr', $attrVar[0])
            ->assign('oVariationen_arr', $attrVar[1])
            ->assign('print', (int)(Request::getInt('print') === 1))
            ->assign('oVergleichsliste', $compareList)
            ->assignDeprecated('Einstellungen_Vergleichsliste', $this->config, '5.2.0');

        $this->preRender();

        \executeHook(\HOOK_VERGLEICHSLISTE_PAGE);

        return $this->smarty->getResponse('comparelist/index.tpl');
    }
}
