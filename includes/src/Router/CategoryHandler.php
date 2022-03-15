<?php declare(strict_types=1);

namespace JTL\Router;

use stdClass;

/**
 * Class CategoryHandler
 * @package JTL\Router
 */
class CategoryHandler extends AbstractHandler
{
    /**
     * @param array $args
     * @return stdClass|null
     */
    public function handle(array $args): ?stdClass
    {
        $categoryID = (int)($args['id'] ?? 0);
        if ($categoryID < 1) {
            return null;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kKategorie', 'kid' => $categoryID]
        );
        if ($seo !== null) {
            $slug          = $seo->cSeo;
            $seo->kSprache = (int)$seo->kSprache;
            $seo->kKey     = (int)$seo->kKey;
            $this->updateShopParams($seo, $slug);
        }

        return $seo;
    }
}
