<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\CategoryAttributeModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryAttributeController
 * @package JTL\REST\Controllers
 */
class CategoryAttributeController extends AbstractController
{
    /**
     * CategoryAttributeController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CategoryAttributeModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     * @OA\Get(
     *   path="/categoryattribute",
     *   tags={"categoryattribute"},
     *   summary="List category attributes",
     *   @OA\Response(
     *     response=200,
     *     description="A list with category attributes"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Category attributes not found"
     *   )
     * )
     * @OA\Get(
     *     path="/categoryattribute/{categoryattributeId}",
     *     tags={"categoryattribute"},
     *     description="Get a category attribute by ID",
     *     summary="Get a category attribute by ID",
     *     operationId="getCategoryattributeById",
     *     @OA\Parameter(
     *         name="categoryattributeId",
     *         in="path",
     *         description="ID of category attribute that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             format="int64",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryAttributeModel"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category attribute not found"
     *     )
     * )
     * @OA\Delete(
     *     path="/categoryattribute/{categoryattributeId}",
     *     description="Deletes a single category attribute based on the ID supplied",
     *     summary="Delete a single category attribute",
     *     operationId="deleteCategoryAttribute",
     *     tags={"categoryattribute"},
     *     @OA\Parameter(
     *         description="ID of category attribute to delete",
     *         in="path",
     *         name="categoryattributeId",
     *         required=true,
     *         @OA\Schema(
     *             format="int64",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category attribute deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category attribute not found"
     *     )
     * )
     * @OA\Put(
     *     path="/categoryattribute/{categoryattributeId}",
     *     tags={"categoryattribute"},
     *     operationId="updateCategoryAttribute",
     *     summary="Update an existing category attribute",
     *     description="",
     *     @OA\Parameter(
     *         name="categoryattributeId",
     *         in="path",
     *         description="ID of category attribute that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             format="int64",
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="CategoryAttribute object that needs to be modified",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryAttributeModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category attribute not found",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception",
     *     )
     * )
     * @OA\Post(
     *     path="/categoryattribute",
     *     tags={"categoryattribute"},
     *     operationId="createCategoryAttribute",
     *     summary="Create a new category attribute",
     *     description="",
     *     @OA\RequestBody(
     *         required=true,
     *         description="CategoryAttribute object that needs to be created",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryAttributeModel")
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception",
     *     )
     * )
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('categoryattribute', $this->index(...));
        $routeGroup->get('categoryattribute/{id}', $this->show(...));
        $routeGroup->put('categoryattribute/{id}', $this->update(...));
        $routeGroup->post('categoryattribute', $this->create(...));
        $routeGroup->delete('categoryattribute/{id}', $this->delete(...));
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'         => 'required|integer',
            'categoryID' => 'required|integer',
            'name'       => 'required|max:255',
            'value'      => 'required|max:255'
        ];
    }
}
