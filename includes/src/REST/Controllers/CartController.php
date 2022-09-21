<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CartModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CartController
 * @package JTL\REST\Controllers
 */
class CartController extends AbstractController
{
    /**
     * CartController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CartModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/cart', [$this, 'index']);
        $routeGroup->get('/cart/{id}', [$this, 'show']);
        $routeGroup->put('/cart/{id}', [$this, 'update']);
        $routeGroup->post('/cart', [$this, 'create']);
        $routeGroup->delete('/cart/{id}', [$this, 'delete']);
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
            'id'                => 'integer',
            'cartID'            => 'integer',
            'customerID'        => 'integer',
            'deliveryAddressID' => 'integer',
            'paymentInfoID'     => 'integer',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'                 => 'required|integer',
            'shippingMethodName' => 'max:255',
            'paymentMethodName'  => 'max:255',
            'orderNO'            => 'max:255',
            'shippingInfo'       => 'max:255',
            'trackingID'         => 'max:255',
            'logistics'          => 'max:255',
            'trackingURL'        => 'max:255',
        ];
    }
}
