<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryController
 * @package JTL\Router\Controller
 */
class CategoryController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $categoryID   = (int)($args['id'] ?? 0);
        $categoryName = $args['name'] ?? null;
        if ($categoryID < 1 && $categoryName === null) {
            return $this->state;
        }
        $parser       = new DefaultParser($this->db, $this->state);
        $categoryName = $parser->parse($categoryName);
        $languageID   = $this->parseLanguageFromArgs($args, $this->languageID ?? Shop::getLanguageID());

        $seo = $categoryID > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                        AND kKey = :kid
                        AND kSprache = :lid',
                ['key' => 'kKategorie', 'kid' => $categoryID, 'lid' => $languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND cSeo = :seo',
                ['key' => 'kKategorie', 'seo' => $categoryName]
            );
        if ($seo === null) {
            $this->state->is404 = true;

            return $this->updateProductFilter();
        }
        $slug          = $seo->cSeo;
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;
        $this->updateState($seo, $slug);

        return $this->state;
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
            Frontend::getCustomer()->getGroupID(),
            Shopsetting::getInstance()->getAll(),
            $this->alertService
        );
        if (!$controller->init()) {
            return $controller->notFoundResponse($request, $args, $smarty);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
