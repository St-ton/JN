<?php declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Router\DefaultParser;
use JTL\Router\State;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CharacteristicValueController
 * @package JTL\Router\Controller
 */
class CharacteristicValueController extends ProductListController
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
                ['key' => 'kMerkmalWert', 'kid' => $id, 'lid' => $this->state->languageID]
            )
            : $this->db->getSingleObject(
                'SELECT *
                    FROM tseo
                    WHERE cKey = :key AND cSeo = :seo',
                ['key' => 'kMerkmalWert', 'seo' => $name]
            );
        if ($seo === null) {
            return $this->handleSeoError($id, $this->state->languageID);
        }
        $slug          = $seo->cSeo;
        $seo->kKey     = (int)$seo->kKey;
        $seo->kSprache = (int)$seo->kSprache;

        return $this->updateState($seo, $slug);
    }

    /**
     * @param int $id
     * @param int $languageID
     * @return State
     */
    private function handleSeoError(int $id, int $languageID): State
    {
        if ($id > 0) {
            $exists = $this->db->getSingleObject(
                'SELECT kMerkmalWert
                    FROM tmerkmalwert
                    WHERE kMerkmalWert = :pid',
                ['pid' => $id]
            );
            if ($exists !== null) {
                $seo = (object)[
                    'cSeo'     => '',
                    'cKey'     => 'kMerkmalWert',
                    'kKey'     => $id,
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
