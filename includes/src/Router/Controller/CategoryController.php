<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryController
 * @package JTL\Router\Controller
 */
class CategoryController extends ProductListController
{
    /**
     * @inheritdoc
     */
    public function getStateFromSlug(array $args): State
    {
        $id   = (int)($args['id'] ?? 0);
        $name = $args['name'] ?? null;
        if ($id < 1 && $name === null) {
            return $this->state;
        }
        if ($name !== null) {
            $parser = new DefaultParser($this->db, $this->state);
            $name   = $parser->parse($name);
        }

        $seo = $id > 0
            ? $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                        AND kKey = :kid
                        AND kSprache = :lid',
                ['key' => 'kKategorie', 'kid' => $id, 'lid' => $this->state->languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key
                      AND cSeo = :seo',
                ['key' => 'kKategorie', 'seo' => $name]
            );
        if ($seo === null) {
            return $this->handleSeoError($id, $this->state->languageID);
        }
        $slug          = $seo->cSeo;
        $seo->kSprache = (int)$seo->kSprache;
        $seo->kKey     = (int)$seo->kKey;
        $this->updateState($seo, $slug);

        return $this->state;
    }

    /**
     * @param int $categoryID
     * @param int $languageID
     * @return State
     */
    private function handleSeoError(int $categoryID, int $languageID): State
    {
        if ($categoryID > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kKategorie
                    FROM tkategorie
                    WHERE kKategorie = :cid',
                ['cid' => $categoryID]
            );
            if ($exists !== null) {
                $seo = (object)[
                    'cSeo'     => '',
                    'cKey'     => 'kKategorie',
                    'kKey'     => $categoryID,
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
        if (!$this->init()) {
            return $this->notFoundResponse($request, $args, $smarty);
        }

        return parent::getResponse($request, $args, $smarty);
    }
}
