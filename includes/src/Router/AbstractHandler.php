<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\DB\DbInterface;
use JTL\Shop;
use stdClass;

/**
 * Class AbstractHandler
 * @package JTL\Router
 */
abstract class AbstractHandler
{
    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param stdClass $seo
     * @param string   $slug
     * @param array    $parsedParams
     * @return void
     */
    protected function updateShopParams(stdClass $seo, string $slug, array $parsedParams = []): void
    {
        if (\strcasecmp($seo->cSeo, $slug) !== 0) {
            return;
        }
        if ($slug !== $seo->cSeo) {
            \http_response_code(301);
            \header('Location: ' . Shop::getURL() . '/' . $seo->cSeo);
            exit;
        }
        Shop::updateLanguage($seo->kSprache);
        Shop::$cCanonicalURL = Shop::getURL() . '/' . $seo->cSeo;
        Shop::${$seo->cKey}  = $seo->kKey;
        foreach ($parsedParams as $key => $value) {
            Shop::${$key} = $value;
        }
    }
}
