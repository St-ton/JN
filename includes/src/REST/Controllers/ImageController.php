<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use Exception;
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

/**
 * Class ImageController
 * @package JTL\REST\Controllers
 */
class ImageController extends AbstractController
{
    public const IMG_TYPE_PROPERTY_VALUE = 'propertyvalue';

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
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/image/{type}/{id}', $this->show(...));
        $routeGroup->get('/image/{type}', $this->index(...));
        $routeGroup->post('/image/{type}/{id}', $this->update(...));
        $routeGroup->post('/image/{type}', $this->create(...));
        $routeGroup->delete('/image/{type}/{id}[/{withfiles}]', $this->delete(...));
    }

    /**
     * @inheritdoc
     */
    public function index(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $this->modelClass = $this->getModelClass($params['type']);

        return parent::index($request, $params);
    }

    /**
     * @inheritdoc
     */
    protected function createItem(ServerRequestInterface $request): DataModelInterface
    {
        $item    = parent::createItem($request);
        $uploads = $request->getUploadedFiles();
        /** @var CategoryModel $item */
        if (!isset($uploads['image']) || (\is_array($uploads['image']) && \count($uploads['image']) === 0)) {
            return $item;
        }
        if (!\is_array($uploads['image'])) {
            $uploads['image'] = [$uploads['image']];
        }
        /** @var UploadedFile $file */
        $modelHasImages = $item->getAttribValue('images')->count() > 0;
        foreach ($uploads['image'] as $file) {
            $file->moveTo(\PFAD_ROOT . STORAGE_CATEGORIES . $file->getClientFilename());
            if (!$modelHasImages) {
                $model = new CategoryImageModel($this->db);
                $data  = (object)[
                    'id'         => $model->getNewID(),
                    'categoryID' => $item->id,
                    'type'       => '',
                    'file'       => $file->getClientFilename()
                ];
                $model::create($data, $this->db);
                $item->images = [(array)$data];
            }
        }
        $this->cacheID = \CACHING_GROUP_CATEGORY . '_' . $item->getId();

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $class = $this->getModelClass($params['type']);

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
            $result = $class::load(['id' => $params['id']], $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
        } catch (Exception) {
            return $this->sendNotFoundResponse('Item with id ' . $params['id'] . ' does not exist');
        }
        try {
//            $result->delete();
            $this->deletedItem($result);
            if ($params['withfiles'] ?? '' === 'withfiles') {
                $this->deleteFiles($params['type'], $result);
            }
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
        switch ($type) {
            case Image::TYPE_PRODUCT:
                $path = \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE . $item->path;
                break;
            case Image::TYPE_VARIATION:
                $path = \PFAD_ROOT . STORAGE_VARIATIONS . $item->path;
                break;
            case Image::TYPE_CHARACTERISTIC_VALUE:
                $path = \PFAD_ROOT . \STORAGE_CHARACTERISTIC_VALUES . $item->path;
                break;
            case Image::TYPE_CATEGORY:
                $path = \PFAD_ROOT . \STORAGE_CATEGORIES . $item->path;
                break;
            default:
                return;
        }
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
