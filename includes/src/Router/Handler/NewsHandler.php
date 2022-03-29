<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\Router\AbstractHandler;
use JTL\Router\Controller\NewsController;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;

/**
 * Class NewsHandler
 * @package JTL\Router\Handler
 */
class NewsHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function getStateFromRequest(ServerRequest $request, array $args): State
    {
        $newsID = (int)($args['id'] ?? 0);
        if ($newsID < 1) {
            return $this->state;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kNews', 'kid' => $newsID]
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
        $controller = new NewsController(
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
