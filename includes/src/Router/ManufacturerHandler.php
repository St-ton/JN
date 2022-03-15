<?php declare(strict_types=1);

namespace JTL\Router;

use stdClass;

/**
 * Class ManufacturerHandler
 * @package JTL\Router
 */
class ManufacturerHandler extends AbstractHandler
{
    /**
     * @param array $args
     * @return stdClass|null
     */
    public function handle(array $args): ?stdClass
    {
        $manufacturerID = (int)($args['id'] ?? 0);
        if ($manufacturerID < 1) {
            return null;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kHersteller', 'kid' => $manufacturerID]
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
