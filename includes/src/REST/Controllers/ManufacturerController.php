<?php declare(strict_types=1);

namespace JTL\REST\Controllers;

use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\Helpers\Seo;
use JTL\Model\DataModelInterface;
use JTL\REST\Models\ManufacturerModel;
use JTL\REST\Models\SeoModel;
use Laminas\Diactoros\UploadedFile;
use League\Fractal\Manager;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ManufacturerController
 * @package JTL\REST\Controllers
 */
class ManufacturerController extends AbstractController
{
    /**
     * ManufacturerController constructor.
     * @inheritdoc
     */
    public function __construct(Manager $fractal, protected DbInterface $db, protected JTLCacheInterface $cache)
    {
        parent::__construct(ManufacturerModel::class, $fractal, $this->db, $this->cache);
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteGroup $routeGroup): void
    {
        $routeGroup->get('/manufacturer', [$this, 'index']);
        $routeGroup->get('/manufacturer/{id}', [$this, 'show']);
        $routeGroup->put('/manufacturer/{id}', [$this, 'update']);
        $routeGroup->post('/manufacturer', [$this, 'create']);
        $routeGroup->delete('/manufacturer/{id}', [$this, 'delete']);
    }

    /**
     * @inheritdoc
     */
    protected function createItem(ServerRequestInterface $request): DataModelInterface
    {
        $item = parent::createItem($request);
        /** @var ManufacturerModel $item */
        $uploads = $request->getUploadedFiles();
        if (!isset($uploads['image']) || (\is_array($uploads['image']) && \count($uploads['image']) === 0)) {
            return $item;
        }
        if (!\is_array($uploads['image'])) {
            $uploads['image'] = [$uploads['image']];
        }
        $modelHasImages = !empty($item->getAttribValue('image'));
        /** @var UploadedFile $file */
        foreach ($uploads['image'] as $file) {
            $file->moveTo(\PFAD_ROOT . STORAGE_MANUFACTURERS . $file->getClientFilename());
            if (!$modelHasImages) {
                $item->setWasLoaded(true);
                $item->image = $file->getClientFilename();
                $item->save(['image'], false);
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
            $id       = $this->db->getSingleInt(
                'SELECT MAX(kHersteller) AS newID FROM thersteller',
                'newID'
            );
            $data->id = ++$id;
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
            $seo->cKey     = 'kHersteller';
            $seo->kKey     = $item->getId();
            $seo->kSprache = $localization->getLanguageID();
            $model::create($seo, $this->db);
        }
        $this->cacheID = \CACHING_GROUP_MANUFACTURER . '_' . $item->getId();
        parent::createdItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function deletedItem(DataModelInterface $item): void
    {
        $this->db->queryPrepared(
            'DELETE FROM tseo WHERE cKey = :keyname AND kKey = :keyid',
            ['keyname' => 'kHersteller', 'keyid' => $item->getId()]
        );
        parent::deletedItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function createRequestValidationRules(ServerRequestInterface $request): array
    {
        return [
            'id'           => 'integer',
            'name'         => 'required|max:255',
            'localization' => 'required|array'
        ];
    }
}
