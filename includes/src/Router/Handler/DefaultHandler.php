<?php declare(strict_types=1);

namespace JTL\Router\Handler;

use JTL\Router\AbstractHandler;
use JTL\Router\ControllerFactory;
use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DefaultHandler
 * @package JTL\Router\Handler
 */
class DefaultHandler extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
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
            if (\str_ends_with($slug, '.php')) {
                $data = $this->db->getSingleObject(
                    'SELECT * 
                        FROM tspezialseite
                        WHERE cDateiname = :slg',
                    ['slg' => $slug]
                );
                if ($data !== null) {
                    $this->state->fileName = $slug;

                    return $this->state;
                }
            }
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
    public function handle(ServerRequest $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->getStateFromSlug($args);
        Shop::seoCheckFinish();
        $cf         = new ControllerFactory($this->state, $this->db, $smarty);
        $controller = $cf->getEntryPoint();
        $check      = $controller->init();
        if ($check === false) {
            return $controller->notFoundResponse();
        }

        return $controller->getResponse();
    }
}
