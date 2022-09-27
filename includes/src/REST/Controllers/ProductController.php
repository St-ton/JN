<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Seo;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\ProductImageModel;
use JTL\REST\Models\ProductModel;
use JTL\REST\Models\SeoModel;
use JTL\Shop;
use Laminas\Diactoros\UploadedFile;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ProductController
 * @package JTL\REST\Controllers
 */
class ProductController extends AbstractController
{
    /**
     * ProductController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(ProductModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     * @OA\Get(
     *   path="/product",
     *   summary="list products",
     *   @OA\Response(
     *     response=200,
     *     description="A list with products"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Product not found"
     *   )
     * )
     * @OA\Get(
     *     path="/product/{productId}",
     *     tags={"product"},
     *     description="Get a product by ID",
     *     operationId="getProductById",
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of product that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProductModel"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/product', [$this, 'index']);
        $routeGroup->get('/product/{id}', [$this, 'show']);
        $routeGroup->put('/product/{id}', [$this, 'update']);
        $routeGroup->post('/product', [$this, 'create']);
        $routeGroup->delete('/product/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createItem(ServerRequestInterface $request): DataModelInterface
    {
        $uploads = $request->getUploadedFiles();
        $item    = parent::createItem($request);
        /** @var ProductModel $item */
        if (!isset($uploads['image']) || (\is_array($uploads['image']) && \count($uploads['image']) === 0)) {
            return $item;
        }
        /** @var UploadedFile $file */
        $modelHasImages = $item->getAttribValue('images')->count() > 0;
        foreach ($uploads['image'] as $i => $file) {
            $file->moveTo(\PFAD_ROOT . PFAD_MEDIA_IMAGE_STORAGE . $file->getClientFilename());
            if (!$modelHasImages) {
                $model = new ProductImageModel($this->db);
                $data  = (object)[
                    'id'          => $model->getNewID(),
                    'mainImageID' => null,
                    'productID'   => $item->id,
                    'imageID'     => $model->getNewImageID(),
                    'path'        => $file->getClientFilename(),
                    'imageNo'     => ++$i
                ];
                $model::create($data, $this->db);
            }
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function getCreateBaseData(
        ServerRequestInterface $request,
        DataModelInterface $model,
        stdClass $data
    ): stdClass {
        $data = parent::getCreateBaseData($request, $model, $data);
        if (!isset($data->id)) {
            // tkategorie has no auto increment ID
            $lastProductID = $this->db->getSingleInt(
                'SELECT MAX(kArtikel) AS newID FROM tartikel',
                'newID'
            );
            $data->id      = ++$lastProductID;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function createdItem(DataModelInterface $item): void
    {
        $baseSeo = Seo::getSeo($item->getSlug());
        $model   = new SeoModel($this->db);
        foreach ($item->getLocalization() as $localization) {
            $seo           = new stdClass();
            $seo->cSeo     = Seo::checkSeo($baseSeo);
            $seo->cKey     = 'kArtikel';
            $seo->kKey     = $item->getId();
            $seo->kSprache = $localization->getLanguageID();
            $model::create($seo, $this->db);
        }
        $this->cacheID = \CACHING_GROUP_PRODUCT . '_' . $item->getId();
        parent::createdItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function deletedItem(DataModelInterface $item): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tseo WHERE cKey = :keyname AND kKey = :keyid',
            ['keyname' => 'kArtikel', 'keyid' => $item->getId()],
        );
        parent::deletedItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'                       => 'integer',
            'taxRate'                  => 'numeric',
            'fAbnahmeintervall'        => 'numeric',
            'fLieferzeit'              => 'numeric',
            'kEigenschaftKombi'        => 'integer',
            'commodityGroup'           => 'integer',
            'fLieferantenlagerbestand' => 'numeric',
            'name'                     => 'required|max:255',
            'description'              => 'max:255',
            'parentID'                 => 'integer',
            'images'                   => 'array',
            'localization'             => 'array',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'name'         => 'max:255',
            'description'  => 'max:255',
            'parentID'     => 'min:0',
            'images'       => 'array',
            'localization' => 'array',
        ];
    }
}
