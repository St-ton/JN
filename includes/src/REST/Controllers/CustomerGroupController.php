<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CustomerGroupModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CustomerGroupController
 * @package JTL\REST\Controllers
 */
class CustomerGroupController extends AbstractController
{
    /**
     * CustomerGroupController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CustomerGroupModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/customerGroup', [$this, 'index']);
        $routeGroup->get('/customerGroup/{id}', [$this, 'show']);
        $routeGroup->put('/customerGroup/{id}', [$this, 'update']);
        $routeGroup->post('/customerGroup', [$this, 'create']);
        $routeGroup->delete('/customerGroup/{id}', [$this, 'delete']);
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
            // tmerkmalwert has no auto increment ID
            $id       = $this->db->getSingleInt(
                'SELECT MAX(kKundengruppe) AS newID FROM tkundengruppe',
                'newID'
            );
            $data->id = ++$id;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'           => 'integer',
            'name'         => 'required|max:255',
            'discount'     => 'numeric',
            'default'      => 'in:Y,N',
            'shopLogin'    => 'in:Y,N',
            'net'          => 'integer',
            'localization' => 'array',
            'attributes'   => 'array',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
        ];
    }
}
