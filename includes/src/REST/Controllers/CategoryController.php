<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Seo;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CategoryImageModel;
use JTL\REST\Models\CategoryModel;
use JTL\REST\Models\ProductCategoriesModel;
use JTL\REST\Models\SeoModel;
use JTL\Shop;
use Laminas\Diactoros\UploadedFile;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CategoryController
 * @package JTL\REST\Controllers
 */

/**
 * @OA\Get(
 *     path="/category/{categoryId}",
 *     tags={"category"},
 *     description="Get a single category",
 *     summary="Get a single category",
 *     @OA\Parameter(
 *         description="ID of category to delete",
 *         in="path",
 *         name="categoryId",
 *         required=true,
 *         @OA\Schema(
 *             format="int64",
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/CategoryModel"),
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid ID supplied"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Category not found"
 *     )
 * )
 */

/**
 * @OA\Get(
 *     path="/category",
 *     tags={"category"},
 *     description="A list of categories",
 *     summary="Get a list of categories",
 *     @OA\Response(
 *         response=200,
 *         description="A list of categories"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid ID supplied"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No categories found"
 *     )
 * )
 */

/**
 * @OA\Delete(
 *     path="/category/{categoryId}",
 *     description="deletes a single category based on the ID supplied",
 *     summary="Delete a single category",
 *     operationId="deleteCategory",
 *     tags={"category"},
 *     @OA\Parameter(
 *         description="ID of category to delete",
 *         in="path",
 *         name="categoryId",
 *         required=true,
 *         @OA\Schema(
 *             format="int64",
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Category deleted"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Categoty not found"
 *     )
 * )
 */
class CategoryController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CategoryModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/category', [$this, 'index']);
        $routeGroup->get('/category/{id}', [$this, 'show']);
        $routeGroup->put('/category/{id}', [$this, 'update']);
        $routeGroup->post('/category', [$this, 'create']);
        $routeGroup->delete('/category/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     * @OA\Post(
     *     path="/category",
     *     tags={"category"},
     *     summary="Create category",
     *     description="",
     *     summary="Create a category",
     *     operationId="createUser",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="An array of validation errors",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="invalid_fields",
     *                  type="object",
     *                  @OA\Property(property="name",type="string",example="The Name is required"),
     *                  @OA\Property(property="description",type="string",example="The Description maximum is 255")
     *              )
     *          ),
     *     ),
     *     @OA\RequestBody(
     *         description="Create category object",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryModel")
     *     )
     * )
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
                    'path'       => $file->getClientFilename()
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
    protected function getCreateBaseData(
        ServerRequestInterface $request,
        DataModelInterface $model,
        stdClass $data
    ): stdClass {
        $data = parent::getCreateBaseData($request, $model, $data);
        if (!isset($data->id)) {
            // tkategorie has no auto increment ID
            $lastCategoryID = $this->db->getSingleInt(
                'SELECT MAX(kKategorie) AS newID FROM tkategorie',
                'newID'
            );
            $data->id       = ++$lastCategoryID;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function createdItem(DataModelInterface $item): void
    {
        /** @var CategoryModel $item */
        $model = new SeoModel($this->db);
        foreach ($item->getLocalization() as $localization) {
            $seo           = new stdClass();
            $seo->cSeo     = Seo::checkSeo($localization->getSlug());
            $seo->cKey     = 'kKategorie';
            $seo->kKey     = $item->getId();
            $seo->kSprache = $localization->getLanguageID();
            $model::create($seo, $this->db);
        }
        $this->rebuildCategoryTree(0, 1);
        parent::createdItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function updatedItem(DataModelInterface $item): void
    {
        $this->rebuildCategoryTree(0, 1);
        parent::updatedItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function deletedItem(DataModelInterface $item): void
    {
        $this->deleteSubItems($item->getId());
        $this->db->queryPrepared(
            'DELETE FROM tseo WHERE cKey = :keyname AND kKey = :keyid',
            ['keyname' => 'kKategorie', 'keyid' => $item->getId()],
        );
        parent::deletedItem($item);
    }

    /**
     * @param int $parentId
     * @throws \Exception
     */
    protected function deleteSubItems(int $parentId): void
    {
        $subItems = CategoryModel::loadAll($this->db, 'kOberKategorie', $parentId);

        if (count($subItems) === 0) {
            return;
        }
        foreach ($subItems as $subItem) {
            $this->deleteSubItems($subItem->getId());
            $productCategories = ProductCategoriesModel::loadAll($this->db, 'kKategorie', $subItem->getId());
            if (count($productCategories) > 0) {
                foreach ($productCategories as $productCategory) {
                    $productCategory->delete();
                }
            }
            $subItem->delete();
        }
    }

    /**
     * update lft/rght values for categories in the nested set model
     *
     * @param int $parent_id
     * @param int $left
     * @param int $level
     * @return int
     */
    private function rebuildCategoryTree(int $parent_id, int $left, int $level = 0): int
    {
        $right  = $left + 1;
        $result = $this->db->selectAll(
            'tkategorie',
            'kOberKategorie',
            $parent_id,
            'kKategorie',
            'nSort, cName'
        );
        foreach ($result as $_res) {
            $right = $this->rebuildCategoryTree((int)$_res->kKategorie, $right, $level + 1);
        }
        $this->db->update('tkategorie', 'kKategorie', $parent_id, (object)[
            'lft'    => $left,
            'rght'   => $right,
            'nLevel' => $level,
        ]);

        return $right + 1;
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'           => 'numeric',
            'name'         => 'required|max:255',
            'parentID'     => 'numeric',
            'sort'         => 'numeric',
            'level'        => 'numeric',
            'description'  => 'max:255',
            'slug'         => 'max:255',
            'localization' => 'array',
            'images'       => 'array',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'           => 'numeric',
            'name'         => 'max:255',
            'description'  => 'max:255',
            'parentID'     => 'numeric',
            'localization' => 'array',
            'images'       => 'array',
        ];
    }
}
