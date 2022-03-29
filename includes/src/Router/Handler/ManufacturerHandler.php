<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\Router\AbstractHandler;
use JTL\Router\Controller\ProductListController;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;

/**
 * Class ManufacturerHandler
 * @package JTL\Router\Handler
 */
class ManufacturerHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function getStateFromRequest(ServerRequest $request, array $args): State
    {
        $manufacturerID = (int)($args['id'] ?? 0);
        if ($manufacturerID < 1) {
            return $this->state;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kHersteller', 'kid' => $manufacturerID]
        );
        if ($seo === null) {
            $this->state->is404 = true;

            return $this->state;
        }
        $slug          = $seo->cSeo;
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;

        return $this->updateState($seo, $slug);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty): string
    {
        $this->getStateFromRequest($request, $args);
        $controller = new ProductListController(
            $this->db,
            $this->state,
            Frontend::getCustomer()->getGroupID(),
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService()
        );
        if (!$controller->init()) {
            return $controller->notFoundResponse($smarty);
        }

        return $controller->getResponse($smarty);
    }
}
