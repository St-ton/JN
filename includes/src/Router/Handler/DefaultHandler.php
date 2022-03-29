<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\Router\AbstractHandler;
use JTL\Router\ControllerFactory;
use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;

/**
 * Class DefaultHandler
 * @package JTL\Router\Handler
 */
class DefaultHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function getStateFromRequest(ServerRequest $request, array $args): State
    {
        $slug = $args['slug'] ?? $args['any'] ?? null;
        if ($slug === null) {
            return $this->state;
        }
        $parser = new DefaultParser($this->db, $this->state);
        $slug   = $parser->parse($slug);
        $seo    = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cSeo = :slg',
            ['slg' => $slug]
        );
        if ($seo === null) {
            $this->state->is404 = true;

            return $this->state;
        }
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;

        return $this->updateState($seo, $slug);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty): string
    {
//        Shop::dbg($args, true, 'Default!');
        $this->getStateFromRequest($request, $args);
        Shop::seoCheckFinish();
        $cf         = new ControllerFactory($this->state, $this->db);
        $controller = $cf->getEntryPoint();
        $check      = $controller->init();
//        Shop::dbg($this->state, true);
//        Shop::dbg($controller, true);
        if ($check === false) {
            return $controller->notFoundResponse($smarty);
        }

        return $controller->getResponse(Shop::Smarty());
    }
}
