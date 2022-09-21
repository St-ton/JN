<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\OrderModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class OrderController
 * @package JTL\REST\Controllers
 */
class OrderController extends AbstractController
{
    /**
     * OrderController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(OrderModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/order', [$this, 'index']);
        $routeGroup->get('/order/{id}', [$this, 'show']);
        $routeGroup->put('/order/{id}', [$this, 'update']);
        $routeGroup->post('/order', [$this, 'create']);
        $routeGroup->delete('/order/{id}', [$this, 'delete']);
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
        $data = parent::getCreateBaseData($request, $model, $data);
        if (!isset($data->id)) {
            // tkategorie has no auto increment ID
            $lastID   = $this->db->getSingleInt(
                'SELECT MAX(kBestellung) AS newID FROM tbestellung',
                'newID'
            );
            $data->id = ++$lastID;
        }

        return $data;
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
            'customerID'         => 'integer',
            'deliveryAddressID'  => 'integer',
            'billingAddressID'   => 'integer',
            'paymentMethodID'    => 'integer',
            'shippingMethodID'   => 'integer',
            'languageID'         => 'integer',
            'currencyID'         => 'integer',
            'shippingMethodName' => 'max:255',
            'paymentMethodName'  => 'max:255',
            'orderNO'            => 'max:255',
            'shippingInfo'       => 'max:255',
            'trackingID'         => 'max:255',
            'logistics'          => 'max:255',
            'trackingURL'        => 'max:255',
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
