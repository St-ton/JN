<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use Exception;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Media\Image;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CategoryImageModel;
use JTL\REST\Models\CategoryModel;
use JTL\REST\Models\CharacteristicValueImageModel;
use JTL\REST\Models\ProductCategoriesModel;
use JTL\REST\Models\ProductImageModel;
use JTL\REST\Models\ProductPropertyValueImage;
use JTL\Shop;
use Laminas\Diactoros\UploadedFile;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnhandledMatchError;

/**
 * Class ImageController
 * @package JTL\REST\Controllers
 */
class ImageController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct('null', $fractal, $this->db, $this->cache);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getModelClass(string $type): string
    {
        return match ($type) {
            Image::TYPE_PRODUCT              => ProductImageModel::class,
            Image::TYPE_CATEGORY             => CategoryImageModel::class,
            Image::TYPE_VARIATION            => ProductPropertyValueImage::class,
            Image::TYPE_CHARACTERISTIC_VALUE => CharacteristicValueImageModel::class
        };
    }

    /**
     * @inheritdoc
     * @OA\Get(
     *   path="/image/product/{id}",
     *   tags={"product"},
     *   description="List image with primary key <id>",
     *   summary="List image with primary key <id>",
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       description="ID of image that needs to be fetched",
     *       required=true,
     *       @OA\Schema(
     *           type="integer"
     *       )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="List image with primary key <id>",
     *     @OA\JsonContent(ref="#/components/schemas/ProductImageModel"),
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Image not found"
     *   )
     * )
     * @OA\Get(
     *     path="/image/category/{id}",
     *     tags={"category"},
     *     description="List image with primary key <id>",
     *     summary="List image with primary key <id>",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of image that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryImageModel"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category images not found"
     *     )
     * )
     * @OA\Get(
     *     path="/image/variation/{id}",
     *     tags={"variation"},
     *     description="List image with primary key <id>",
     *     summary="List image with primary key <id>",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of iamge that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProductPropertyValueImage"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Variation images not found"
     *     )
     * )
     * @OA\Get(
     *     path="/image/characteristicvalue/{id}",
     *     tags={"characteristicvalue"},
     *     description="List image with primary key <id>",
     *     summary="List image with primary key <id>",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of image that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CharacteristicValueImageModel"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Characteristic value images not found"
     *     )
     * )
     * @OA\Post(
     *     path="/images/product/{productId}",
     *     tags={"product"},
     *     operationId="createProductImage",
     *     summary="Create a new product image",
     *     description="Create a new product image",
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of product that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Image to upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image",
     *                     description="Image",
     *                     type="string",
     *                     format="binary"
     *                )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="A list with product images",
     *       @OA\JsonContent(ref="#/components/schemas/ProductImageModel"),
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception",
     *     )
     * )
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/image/{type}/{id}', $this->show(...));
        $routeGroup->get('/image/{type}', $this->index(...));
        $routeGroup->put('/image/{type}/{id}', $this->update(...));
        $routeGroup->post('/image/{type}', $this->create(...));
        $routeGroup->post('/image/{type}/{refid:\d+}', $this->create(...));
        $routeGroup->delete('/image/{type}/{id}[/{withfiles}]', $this->delete(...));
    }

    /**
     * @inheritdoc
     */
    public function index(ServerRequestInterface $request, array $params): ResponseInterface
    {
        try {
            $this->modelClass = $this->getModelClass($params['type']);
        } catch (UnhandledMatchError) {
            return $this->sendCustomResponse(500, 'Error occurred listing items - unknown type');
        }

        return parent::index($request, $params);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $params
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $validatorResponse = $this->validateRequest($request, $this->createRequestValidationRules($request));
        if ($validatorResponse !== true) {
            return $this->sendInvalidFieldResponse($validatorResponse);
        }
        try {
            $this->modelClass = $this->getModelClass($params['type']);
            $result           = $this->createItem($request->withAttribute('ref', $params['refid'] ?? 0));
            $this->createdItem($result);
        } catch (UnhandledMatchError) {
            return $this->sendCustomResponse(500, 'Error occurred creating item - unknown type');
        } catch (Exception $e) {
            return $this->sendCustomResponse(500, 'Error occurred creating item - duplicate ID? ' . $e->getMessage());
        }

        return $this->setStatusCode(201)->respondWithModel($result);
    }

    /**
     * @inheritdoc
     */
    protected function createItem(ServerRequestInterface $request): DataModelInterface
    {
        $uploads = $request->getUploadedFiles();
        if (!isset($uploads['image']) || (\is_array($uploads['image']) && \count($uploads['image']) === 0)) {
            throw new InvalidArgumentException('Error occurred creating image - no data given');
        }
        if (\count($uploads['image']) > 1) {
            throw new InvalidArgumentException('Only one image at a time please');
        }
        /** @var UploadedFile $file */
//        $modelHasImages = $item->getAttribValue('images')->count() > 0;
        return match ($this->modelClass) {
            ProductImageModel::class             => $this->createProductImage($request),
            ProductPropertyValueImage::class     => $this->createProductPropertyValueImage($request),
            CharacteristicValueImageModel::class => $this->createCharacteristicImage($request),
            CategoryImageModel::class            => $this->createCategoryImage($request)
        };
    }

    private function createProductPropertyValueImage(ServerRequestInterface $request): DataModelInterface
    {
        $reference = (int)($request->getAttribute('ref') ?? 0);
        /** @var ProductPropertyValueImage $model */
        $model    = new $this->modelClass($this->db);
        $basePath = \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE;
        foreach ($request->getUploadedFiles()['image'] as $file) {
            $fileName = \str_replace(' ', '_', $file->getClientFilename());
            $file->moveTo($basePath . $fileName);
            $model->setPath($fileName);
            $model->setId($model->getNewID());
        }
        if ($reference > 0) {
            $model->setPropertyValueID($reference);
            $model->save();
        }

        return $model;
    }

    private function createCharacteristicImage(ServerRequestInterface $request): DataModelInterface
    {
        $reference = (int)($request->getAttribute('ref') ?? 0);
        /** @var CharacteristicValueImageModel $model */
        $model    = new $this->modelClass($this->db);
        $basePath = \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE;
        foreach ($request->getUploadedFiles()['image'] as $file) {
            $fileName = \str_replace(' ', '_', $file->getClientFilename());
            $file->moveTo($basePath . $fileName);
            $model->setPath($fileName);
        }
        if ($reference > 0) {
            $model->setId($reference);
            $model->save();
        }

        return $model;
    }

    private function createProductImage(ServerRequestInterface $request): DataModelInterface
    {
        $reference = (int)($request->getAttribute('ref') ?? 0);
        /** @var ProductImageModel $model */
        $model    = new $this->modelClass($this->db);
        $basePath = \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE;
        foreach ($request->getUploadedFiles()['image'] as $file) {
            $fileName = \str_replace(' ', '_', $file->getClientFilename());
            $file->moveTo($basePath . $fileName);
            $model->setPath($fileName);
            $model->setId($model->getNewID());
        }
        if ($reference > 0) {
            $model->setProductID($reference);
            $model->save();
        }

        return $model;
    }

    private function createCategoryImage(ServerRequestInterface $request): DataModelInterface
    {
        $reference = (int)($request->getAttribute('ref') ?? 0);
        /** @var CategoryImageModel $model */
        $model       = new $this->modelClass($this->db);
        $model->type = '';
        $basePath    = \PFAD_ROOT . \STORAGE_CATEGORIES;
        foreach ($request->getUploadedFiles()['image'] as $file) {
            $fileName = \str_replace(' ', '_', $file->getClientFilename());
            $file->moveTo($basePath . $fileName);
//            if (!$modelHasImages) {
//                $model = new $this->modelClass($this->db);
//                $data  = (object)[
//                    'id'         => $model->getNewID(),
//                    'categoryID' => $item->id,
//                    'type'       => '',
//                    'file'       => $file->getClientFilename()
//                ];
//                $model::create($data, $this->db);
//                $item->images = [(array)$data];
//            }
            $model->setPath($fileName);
        }
        if ($reference > 0) {
            $model->setCategoryID($reference);
            $model->save();
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        try {
            $class = $this->getModelClass($params['type']);
        } catch (UnhandledMatchError) {
            return $this->sendCustomResponse(500, 'Error occurred showing item - unknown type');
        }

        return $this->getImage($class, $params['id']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $params
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request, array $params): ResponseInterface
    {
        try {
            $class = $this->getModelClass($params['type']);
            /** @var DataModelInterface $result */
            /** @var DataModelInterface $class */
            $result = $class::load(['id' => $params['id']], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (UnhandledMatchError | Exception) {
            return $this->sendNotFoundResponse('Item with id ' . $params['id'] . ' does not exist');
        }
        try {
            if ($params['withfiles'] ?? '' === 'withfiles') {
                $this->deleteFiles($params['type'], $result);
            }
            $result->delete();
            $this->deletedItem($result);
        } catch (Exception $e) {
            return $this->sendCustomResponse(500, 'Error occurred deleting item: ' . $e->getMessage());
        }

        return $this->sendEmptyResponse();
    }

    /**
     * @param string             $type
     * @param DataModelInterface $item
     * @return void
     */
    protected function deleteFiles(string $type, DataModelInterface $item): void
    {
        $path = match ($type) {
            Image::TYPE_PRODUCT              => \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE,
            Image::TYPE_CATEGORY             => \PFAD_ROOT . \STORAGE_CATEGORIES,
            Image::TYPE_VARIATION            => \PFAD_ROOT . \STORAGE_VARIATIONS,
            Image::TYPE_CHARACTERISTIC_VALUE => \PFAD_ROOT . \STORAGE_CHARACTERISTIC_VALUES
        } . $item->getPath();

        $real = \realpath($path);
        if ($path !== false && \str_starts_with($real, \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE) && \file_exists($real)) {
            \unlink($real);
        }
    }

    /**
     * @param DataModelInterface $item
     * @return void
     */
    protected function deletedItem(DataModelInterface $item): void
    {
        if ($this->cacheID !== null) {
            $this->cache->flush($this->cacheID);
        }
        if (\count($this->cacheTags) > 0) {
            $this->cache->flushTags($this->cacheTags);
        }
    }

    /**
     * @param string $class
     * @param string $id
     * @return ResponseInterface
     */
    public function getImage(string $class, string $id): ResponseInterface
    {
        try {
            /** @var $class DataModelInterface */
            $result = $class::load(['id' => $id], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception) {
            return $this->sendNotFoundResponse();
        }
        return $this->respondWithModel($result);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [];
    }
}
