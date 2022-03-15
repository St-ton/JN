<?php declare(strict_types=1);

namespace JTL\Router;

use stdClass;

/**
 * Class DefaultHandler
 * @package JTL\Router
 */
class DefaultHandler extends AbstractHandler
{
    /**
     * @param array $args
     * @return stdClass|null
     */
    public function handle(array $args): ?stdClass
    {
        $slug = $args['slug'] ?? null;
        if ($slug === null) {
            return null;
        }
        $parser = new DefaultParser($this->db);
        $slug   = $parser->parse($slug);
        $seo    = $this->db->getSingleObject(
            'SELECT *
                FROM tseo
                WHERE cSeo = :slg',
            ['slg' => $slug]
        );
        if ($seo !== null) {
            $seo->kSprache = (int)$seo->kSprache;
            $seo->kKey     = (int)$seo->kKey;
            $this->updateShopParams($seo, $slug, $parser->getParams());
        }

        return $seo;
    }
}
