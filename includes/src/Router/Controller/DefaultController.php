<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Language\LanguageHelper;
use JTL\Router\ControllerFactory;
use JTL\Router\DefaultParser;
use JTL\Router\Middleware\PhpFileCheckMiddleware;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DefaultController
 * @package JTL\Router\Controller
 */
class DefaultController extends AbstractController
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
        $slug   = $parser->parse($slug, $args);
        $seo    = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cSeo = :slg',
            ['slg' => $slug]
        );
        if ($seo === null) {
            $seo = (object)[];
            if (\str_ends_with($slug, '.php') && !\str_ends_with($slug, 'index.php')) {
                $data = $this->db->getSingleObject(
                    'SELECT * 
                        FROM tspezialseite
                        WHERE cDateiname = :slg',
                    ['slg' => $slug]
                );
                if ($data !== null) {
                    $this->state->fileName = $slug;

                    return $this->updateState($seo, $slug);
                }
                $this->state->is404 = true;
            }

            return $this->updateState($seo, $slug);
        }
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;

        return $this->updateState($seo, $slug);
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $phpFileCheckMiddleware = new PhpFileCheckMiddleware();
        $route->get('/{slug:.+}', [$this, 'getResponse'])
            ->setName('catchall' . $dynName)
            ->middleware($phpFileCheckMiddleware);
        $route->post('/{slug:.+}', [$this, 'getResponse'])
            ->setName('catchallPOST' . $dynName)
            ->middleware($phpFileCheckMiddleware);
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        if (\count($args) === 0) {
            $args['slug'] = \ltrim($request->getUri()->getPath(), '/');
        }
        $this->getStateFromSlug($args);
        $cf         = new ControllerFactory($this->state, $this->db, $this->cache, $smarty);
        $controller = $cf->getEntryPoint($request);
        $check      = $controller->init();
        if ($check === false) {
            return $controller->notFoundResponse($request, $args, $smarty);
        }
        if (\REDIR_OLD_ROUTES === true
            && $controller::class !== SearchController::class
            && !isset($_GET['opcEditMode'])
        ) {
            $langID    = $this->state->languageID ?: Shop::getLanguageID();
            $locale    = null;
            $isDefault = false;
            foreach (LanguageHelper::getAllLanguages() as $language) {
                if ($language->getId() === $langID) {
                    $locale    = $language->getIso639();
                    $isDefault = $language->isShopDefault();
                }
            }
            $scheme = $this->config['global']['routing_default_language'] ?? 'F';
            if (ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false) {
                $scheme = 'F';
            }
            if ($isDefault && ($scheme === 'F' || ($scheme === 'L' && !empty($args['lang'])))) {
                return $controller->getResponse($request, $args, $smarty);
            }
            $scheme = $this->config['global']['routing_scheme'] ?? 'F';
            if (ENABLE_EXPERIMENTAL_ROUTING_SCHEMES === false) {
                $scheme = 'F';
            }
            if (!$isDefault && ($scheme === 'F' || ($scheme === 'L' && !empty($args['lang'])))) {
                return $controller->getResponse($request, $args, $smarty);
            }
            $className = $controller instanceof PageController
                ? PageController::class
                : \get_class($controller);
            $type      = $this->getRouteTypeByClassName($className);
            $test      = Shop::getRouter()->getURLByType($type, [
                'name' => $args['slug'],
                'lang' => $locale
            ]);
            $query     = $request->getUri()->getQuery();
            if (\mb_strlen($query) > 0) {
                $test .= '?' . $query;
            }

            return new RedirectResponse($test, 301);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
