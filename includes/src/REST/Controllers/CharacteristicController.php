<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\CharacteristicModel;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class CharacteristicController
 * @package JTL\REST\Controllers
 */
class CharacteristicController extends AbstractController
{
    /**
     * CharacteristicController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(CharacteristicModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/characteristic', [$this, 'index']);
        $routeGroup->get('/characteristic/{id}', [$this, 'show']);
        $routeGroup->put('/characteristic/{id}', [$this, 'update']);
        $routeGroup->post('/characteristic', [$this, 'create']);
        $routeGroup->delete('/characteristic/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function getCreateBaseData(ServerRequestInterface $request, DataModelInterface $model, stdClass $data): stdClass
    {
        $data = parent::getCreateBaseData($request, $model, $data);
        if (!isset($data->id)) {
            // tmerkmal has no auto increment ID
            $lastCharacteristicId = $this->db->getSingleInt(
                'SELECT MAX(kMerkmal) AS newID FROM tmerkmal',
                'newID'
            );
            $data->id             = ++$lastCharacteristicId;
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
            'sort'         => 'integer',
            'name'         => 'required|max:255',
            'type'         => 'in:TEXTSWATCHES,IMGSWATCHES,RADIO,BILD,BILD-TEXT,SELECTBOX,TEXT',
            'isMulti'      => 'integer',
            'localization' => 'array',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'name'         => 'max:255',
            'localization' => 'array',
        ];
    }
}
