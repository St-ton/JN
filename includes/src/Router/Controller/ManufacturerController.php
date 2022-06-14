<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\State;
use JTL\Session\Frontend;
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
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $manufacturerID   = (int)($args['id'] ?? 0);
        $manufacturerName = $args['name'] ?? null;
        if ($manufacturerID < 1 && $manufacturerName === null) {
            return $this->state;
        }
        $languageID = $this->parseLanguageFromArgs($args, $this->languageID ?? Shop::getLanguageID());

        $seo = $manufacturerID > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND kKey = :kid
                      AND kSprache = :lid',
                ['key' => 'kHersteller', 'kid' => $manufacturerID, 'lid' => $languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND cSeo = :seo
                      AND kSprache = :lid',
                ['key' => 'kHersteller', 'seo' => $manufacturerName, 'lid' => $languageID]
            );
        if ($seo === null) {
            return $this->handleSeoError($manufacturerID, $languageID);
        }
        $slug          = $seo->cSeo;
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;

        return $this->updateState($seo, $slug);
    }

    /**
     * @param int $manufacturerID
     * @param int $languageID
     * @return State
     */
    private function handleSeoError(int $manufacturerID, int $languageID): State
    {
        if ($manufacturerID > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kHersteller
                    FROM thersteller
                    WHERE kHersteller = :pid',
                ['pid' => $manufacturerID]
            );
            if ($exists !== null) {
                $seo = (object)[
                    'cSeo'     => '',
                    'cKey'     => 'kHersteller',
                    'kKey'     => $manufacturerID,
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
            Frontend::getCustomer()->getGroupID(),
            Shopsetting::getInstance()->getAll(),
            Shop::Container()->getAlertService()
        );
        if (!$controller->init()) {
            return $controller->notFoundResponse($request, $args, $smarty);
        }

        return $controller->getResponse($request, $args, $smarty);
    }
}
