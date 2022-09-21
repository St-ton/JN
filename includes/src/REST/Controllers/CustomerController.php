<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CustomerModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CustomerController
 * @package JTL\REST\Controllers
 */
class CustomerController extends AbstractController
{
    /**
     * CustomerController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CustomerModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/customer', [$this, 'index']);
        $routeGroup->get('/customer/{id}', [$this, 'show']);
        $routeGroup->put('/customer/{id}', [$this, 'update']);
        $routeGroup->post('/customer', [$this, 'create']);
        $routeGroup->delete('/customer/{id}', [$this, 'delete']);
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
        // tkunde has auto increment
        if (!isset($data->id)) {
            // tkategorie has no auto increment ID
            $lastID   = $this->db->getSingleInt(
                'SELECT MAX(kKunde) AS newID FROM tkunde',
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
            'id'                => 'integer',
            'customerGroupID'   => 'integer',
            'languageID'        => 'integer',
            'customerNO'        => 'max:255',
            'firstname'         => 'max:255',
            'surname'           => 'max:255',
            'company'           => 'max:255',
            'additional'        => 'max:255',
            'street'            => 'max:255',
            'streetNO'          => 'max:255',
            'additionalAddress' => 'max:255',
            'zip'               => 'max:255',
            'city'              => 'max:255',
            'state'             => 'max:255',
            'country'           => 'max:255',
            'tel'               => 'max:255',
            'mobile'            => 'max:255',
            'fax'               => 'max:255',
            'mail'              => 'max:255',
            'ustidnr'           => 'max:255',
            'www'               => 'max:255',
            'loginAttempts'     => 'integer',
            'locked'            => 'in:Y,N',
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
