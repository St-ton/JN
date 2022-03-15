<?php declare(strict_types=1);

namespace JTL\Router;

use stdClass;

/**
 * Class PageHandler
 * @package JTL\Router
 */
class PageHandler extends AbstractHandler
{
    /**
     * @param array $args
     * @return stdClass|null
     */
    public function handle(array $args): ?stdClass
    {
        $linkID = (int)($args['id'] ?? 0);
        if ($linkID < 1) {
            return null;
        }
        $seo = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cKey = :key AND kKey = :kid',
            ['key' => 'kLink', 'kid' => $linkID]
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
