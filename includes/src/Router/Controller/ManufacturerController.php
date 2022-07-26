<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\State;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ManufacturerController
 * @package JTL\Router\Controller
 */
class ManufacturerController extends AbstractController
{
    /**
     * @var string
     */
    protected string $tseoSelector = 'kHersteller';

    /**
     * @inheritdoc
     */
    protected function handleSeoError(int $id, int $languageID): State
    {
        if ($id > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kHersteller
                    FROM thersteller
                    WHERE kHersteller = :pid',
                ['pid' => $id]
            );
            if ($exists !== null) {
                $seo = (object)[
                    'cSeo'     => '',
                    'cKey'     => $this->tseoSelector,
                    'kKey'     => $id,
                    'kSprache' => $languageID
                ];

                return $this->updateState($seo, $seo->cSeo);
            }
        }
        $this->state->is404 = true;

        return $this->updateProductFilter();
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);
        $controller = new ProductListController(
            $this->db,
            $this->cache,
            $this->state,
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService()
        );
        if (!$controller->init()) {
            return $controller->notFoundResponse($request, $args, $smarty);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
