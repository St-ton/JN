<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CartItemModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CartItemController
 * @package JTL\REST\Controllers
 */
class CartItemController extends AbstractController
{
    /**
     * CartItemController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CartItemModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/cartitem', [$this, 'index']);
        $routeGroup->get('/cartitem/{id}', [$this, 'show']);
        $routeGroup->put('/cartitem/{id}', [$this, 'update']);
        $routeGroup->post('/cartitem', [$this, 'create']);
        $routeGroup->delete('/cartitem/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     * @todo
     */
    protected function createItem(ServerRequestInterface $request): DataModelInterface
    {
        return parent::createItem($request);
    }

    /**
     * @inheritdoc
     */
    protected function getCreateBaseData(ServerRequestInterface $request, DataModelInterface $model, stdClass $data): stdClass
    {
        return parent::getCreateBaseData($request, $model, $data);
    }

    /**
     * @inheritdoc
     */
    protected function createdItem(DataModelInterface $item): void
    {
//        $baseSeo = Seo::getSeo($item->getSlug());
//        $model   = new SeoModel($this->db);
//        foreach ($item->getLocalization() as $localization) {
//            $seo           = new stdClass();
//            $seo->cSeo     = Seo::checkSeo($baseSeo);
//            $seo->cKey     = 'kArtikel';
//            $seo->kKey     = $item->getId();
//            $seo->kSprache = $localization->getLanguageID();
//            $model::create($seo, $this->db);
//        }
//        $this->cacheID = \CACHING_GROUP_PRODUCT . '_' . $item->getId();
        parent::createdItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function deletedItem(DataModelInterface $item): void
    {
        parent::deletedItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'                 => 'integer',
            'cartID'             => 'integer',
            'productID'          => 'integer',
            'shippingClassID'    => 'integer',
            'name'               => 'max:255',
            'deliveryState'      => 'max:255',
            'artNO'              => 'max:255',
            'unit'               => 'max:255',
            'netSinglePrice'     => 'numeric',
            'price'              => 'numeric',
            'taxPercent'         => 'numeric',
            'qty'                => 'numeric',
            'posType'            => 'integer',
            'notice'             => 'max:255',
            'unique'             => 'max:255',
            'responsibility'     => 'max:255',
            'configItemID'       => 'integer',
            'orderItemID'        => 'integer',
            'stockBefore'        => 'numeric',
            'longestMinDelivery' => 'integer',
            'longestMaxDelivery' => 'integer',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id' => 'required|integer'
        ];
    }
}
