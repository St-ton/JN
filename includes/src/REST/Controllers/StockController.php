<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\StockModel;
use JTL\REST\Transformers\CategoryTransformer;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class StockController
 * @package JTL\REST\Controllers
 */
class StockController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(StockModel::class, $fractal, $this->db, $this->cache);
        $this->primaryKeyName = '';
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/stock/product/{productID}/warehouse/{warehouseID}', [$this, 'show']);
        $routeGroup->get('/stock/product/{productID}', [$this, 'show']);
        $routeGroup->get('/stock/warehouse/{warehouseID}', [$this, 'show']);
        $routeGroup->get('/stock', [$this, 'index']);
        $routeGroup->put('/stock/{id}', [$this, 'update']);
        $routeGroup->post('/stock', [$this, 'create']);
        $routeGroup->delete('/stock/product/{productID}', [$this, 'delete']);
        $routeGroup->delete('/stock/warehouse/{warehouseID}', [$this, 'delete']);
        $routeGroup->delete('/stock/product/{productID}/warehouse/{warehouseID}', [$this, 'delete']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $params
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $productID   = (int)($params['productID'] ?? 0);
        $warehouseID = (int)($params['warehouseID'] ?? 0);
        $model       = new $this->modelClass($this->db);
        $keys        = [];
        $values      = [];
        if ($productID > 0) {
            $keys[]   = 'productID';
            $values[] = $productID;
        }
        if ($warehouseID > 0) {
            $keys[]   = 'warehouseID';
            $values[] = $warehouseID;
        }
        try {
            $items = $this->db->delete($model->getTableName(), $keys, $values);
        } catch (Exception $e) {
            return $this->sendCustomResponse(500, 'Error occurred deleting item: ' . $e->getMessage());
        }
        if ($items === 0) {
            return $this->sendNotFoundResponse('No items found');
        }

        return $this->sendEmptyResponse();
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $params
     * @return ResponseInterface
     */
    public function show(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $productID   = (int)($params['productID'] ?? 0);
        $warehouseID = (int)($params['warehouseID'] ?? 0);
        $result      = null;
        try {
            $attributes = [];
            if ($productID > 0) {
                $attributes['productID'] = $productID;
            }
            if ($warehouseID > 0) {
                $attributes['warehouseID'] = $warehouseID;
            }
            if ($productID > 0 && $warehouseID > 0) {
                $class = $this->modelClass;
                /** @var $class DataModelInterface */
                $result = $class::loadByAttributes($attributes, $this->db, DataModelInterface::ON_NOTEXISTS_FAIL);
            } elseif ($productID > 0) {
                $model = new $this->modelClass($this->db);
                $res   = $this->db->getCollection(
                    'SELECT * FROM ' . $model->getTableName() . ' WHERE kArtikel = :pid',
                    ['pid' => $productID]
                )->map(function (stdClass $e) {
                    /** @var DataModelInterface $instance */
                    $instance = new $this->modelClass($this->db);
                    $instance->fill($e);
                    $instance->setWasLoaded(true);

                    return $instance;
                });
                return $this->respondWithCollection($res, new CategoryTransformer());
            } elseif ($warehouseID > 0) {
                $model = new $this->modelClass($this->db);
                $res   = $this->db->queryPrepared(
                    'SELECT * FROM ' . $model->getTableName() . ' WHERE kWarenlager = :sid',
                    ['sid' => $warehouseID]
                )->map(function (stdClass $e) {
                    /** @var DataModelInterface $instance */
                    $instance = new $this->modelClass($this->db);
                    $instance->fill($e);
                    $instance->setWasLoaded(true);

                    return $instance;
                });
                return $this->respondWithCollection($res, new CategoryTransformer());
            }
        } catch (Exception) {
            return $this->sendNotFoundResponse();
        }
        return $this->respondWithModel($result);
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
            'productID'    => 'integer',
            'warehouseID'  => 'integer',
            'stock'        => 'numeric',
            'procured'     => 'numeric',
            'procuredDate' => 'max:255'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'productID'    => 'integer',
            'warehouseID'  => 'integer',
            'stock'        => 'numeric',
            'procured'     => 'numeric',
            'procuredDate' => 'max:255'
        ];
    }
}
