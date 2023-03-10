<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\TaxRateModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TaxRateController
 * @package JTL\REST\Controllers
 */
class TaxRateController extends AbstractController
{
    /**
     * TaxRateController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(TaxRateModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     * @OA\Get(
     *   path="/taxrate",
     *   tags={"taxrate"},
     *   summary="List tax rates",
     *   @OA\Response(
     *     response=200,
     *     description="A list with tax rates"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Tax rates not found"
     *   )
     * )
     * @OA\Get(
     *     path="/taxrate/{id}",
     *     tags={"taxrate"},
     *     description="Get a tax rate by ID",
     *     summary="Get a tax rate by ID",
     *     operationId="getTaxrateById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of tax rate that needs to be fetched",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateModel"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tax rate not found"
     *     )
     * )
     *  @OA\Delete(
     *     path="/taxrate/{id}",
     *     description="Deletes a single tax rate based on the ID supplied",
     *     summary="Delete a single tax rate",
     *     operationId="deleteTaxRate",
     *     tags={"taxrate"},
     *     @OA\Parameter(
     *         description="ID of tax rate to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             format="int64",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Tax rate deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tax rate not found"
     *     )
     * )
     * @OA\Put(
     *     path="/taxrate/{id}",
     *     tags={"taxrate"},
     *     operationId="updateTaxRate",
     *     summary="Update an existing tax rate",
     *     description="",
     *     @OA\RequestBody(
     *         required=true,
     *         description="TaxRate object that needs to be modified",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tax Rate not found",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception",
     *     )
     * )
     * @OA\Post(
     *     path="/taxrate",
     *     tags={"taxrate"},
     *     operationId="createTaxRate",
     *     summary="Create a new tax rate",
     *     description="",
     *     @OA\RequestBody(
     *         required=true,
     *         description="TaxRate object that needs to be created",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateModel")
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Validation exception",
     *     )
     * )
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/taxrate', $this->index(...));
        $routeGroup->get('/taxrate/{id}', $this->show(...));
        $routeGroup->put('/taxrate/{id}', $this->update(...));
        $routeGroup->post('/taxrate', $this->create(...));
        $routeGroup->delete('/taxrate/{id}', $this->delete(...));
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'         => 'required|integer',
            'RateID'     => 'required|integer',
            'taxClassID' => 'required|integer',
            'rate'       => 'required|numeric',
            'priority'   => 'integer'
        ];
    }
}
