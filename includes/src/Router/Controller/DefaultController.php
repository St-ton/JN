<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Language\LanguageHelper;
use JTL\Router\ControllerFactory;
use JTL\Router\DefaultParser;
use JTL\Router\Router;
use JTL\Router\State;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
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
        $slug   = $parser->parse($slug);
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
        if (\REDIR_OLD_ROUTES === true && \get_class($controller) !== SearchController::class) {
            $langID = $this->state->languageID ?: Shop::getLanguageID();
            $locale = null;
            foreach (LanguageHelper::getAllLanguages() as $language) {
                if ($language->getId() === $langID) {
                    $locale = $language->getIso639();
                }
            }
            $className = $controller instanceof PageController
                ? PageController::class
                : \get_class($controller);
            $type      = match ($className) {
                CategoryController::class            => Router::TYPE_CATEGORY,
                CharacteristicValueController::class => Router::TYPE_CHARACTERISTIC_VALUE,
                ManufacturerController::class        => Router::TYPE_MANUFACTURERS,
                NewsController::class                => Router::TYPE_NEWS,
                ProductController::class             => Router::TYPE_PRODUCT,
                SearchSpecialController::class       => Router::TYPE_SEARCH_SPECIAL,
                SearchQueryController::class         => Router::TYPE_SEARCH_QUERY,
                default                              => Router::TYPE_PAGE
            };
            $test = Shop::getRouter()->getPathByType($type, [
                'name' => $args['slug'],
                'lang' => $locale
            ]);

            return new RedirectResponse(Shop::getURL() . $test, 301);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
