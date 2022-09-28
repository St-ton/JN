<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\REST\Models\CustomerModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CustomerController
 * @package JTL\REST\Controllers
 * @OA\Delete(
 *     path="/customer/{customerId}",
 *     description="deletes a single customer based on the ID supplied",
 *     summary="Delete a single customer",
 *     operationId="deleteCustomer",
 *     tags={"customer"},
 *     @OA\Parameter(
 *         description="ID of customer to delete",
 *         in="path",
 *         name="customerId",
 *         required=true,
 *         @OA\Schema(
 *             format="int64",
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Customer deleted"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Customer not found"
 *     )
 * )
 * @OA\Get(
 *   path="/customer",
 *   tags={"customer"},
 *   summary="list customers",
 *   @OA\Response(
 *     response=200,
 *     description="A list with customers"
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Customer not found"
 *   )
 * )
 * @OA\Get(
 *     path="/customer/{customerId}",
 *     tags={"customer"},
 *     description="Get a customer by ID",
 *     operationId="getCustomerById",
 *     @OA\Parameter(
 *         name="customerId",
 *         in="path",
 *         description="ID of customer that needs to be fetched",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/CustomerModel"),
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Customer not found"
 *     )
 * )
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
