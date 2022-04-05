<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\Router\AbstractHandler;
use JTL\Router\Controller\ProductController;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;
use League\Route\Route;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ProductHandler
 * @package JTL\Router\Handler
 */
class ProductHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $productID = (int)($args['id'] ?? 0);
        if ($productID < 1) {
            return $this->state;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kArtikel', 'kid' => $productID]
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
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty, Route $route): ResponseInterface
    {
        $this->getStateFromSlug($args);
        $controller = new ProductController(
            $this->db,
            $this->state,
            Frontend::getCustomer()->getGroupID(),
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService()
        );
        if (!$controller->init()) {
            return $controller->notFoundResponse($request, $args, $smarty, $route);
        }

        return $controller->getResponse($request, $args, $smarty, $route);
    }
}
