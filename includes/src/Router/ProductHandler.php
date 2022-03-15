<?php declare(strict_types=1);

namespace JTL\Router;

use stdClass;

/**
 * Class ProductHandler
 * @package JTL\Router
 */
class ProductHandler extends AbstractHandler
{
    /**
     * @param array $args
     * @return stdClass|null
     */
    public function handle(array $args): ?stdClass
    {
        $productID = (int)($args['id'] ?? 0);
        if ($productID < 1) {
            return null;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kArtikel', 'kid' => $productID]
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
