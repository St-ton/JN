<?php declare(strict_types=1);

namespace JTL\Router;

use JTL\DB\DbInterface;
use JTL\Router\Handler\HandlerInterface;
use JTL\Shop;
use stdClass;

/**
 * Class AbstractHandler
 * @package JTL\Router
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, State $state)
    {
        $this->db    = $db;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function updateState(stdClass $seo, string $slug): State
    {
        $this->state->slug = $seo->cSeo ?? $slug;
        if (isset($seo->kSprache, $seo->kKey)) {
            $this->state->languageID = (int)$seo->kSprache;
            $this->state->itemID     = (int)$seo->kKey;
            $this->state->type       = $seo->cKey;
            $mapping                 = $this->state->getMapping();
            if (isset($mapping[$seo->cKey])) {
                $this->state->{$mapping[$seo->cKey]} = $this->state->itemID;
            }
        }
        $this->updateShopParams($slug);
        Shop::getProductFilter()->initStates($this->state->getAsParams());
        \executeHook(\HOOK_INDEX_NAVI_HEAD_POSTGET);

        return $this->state;
    }

    /**
     * @param string $slug
     * @return void
     */
    protected function updateShopParams(string $slug): void
    {
        if (\strcasecmp($this->state->slug, $slug) !== 0) {
            return;
        }
        if ($slug !== $this->state->slug) {
            \http_response_code(301);
            \header('Location: ' . Shop::getURL() . '/' . $this->state->slug);
            exit;
        }
        Shop::updateLanguage($this->state->languageID);
        Shop::$cCanonicalURL             = Shop::getURL() . '/' . $this->state->slug;
        Shop::$is404                     = $this->state->is404;
        Shop::$kSprache                  = $this->state->languageID;
        Shop::$kSeite                    = $this->state->pageID;
        Shop::$kKategorieFilter          = $this->state->categoryFilterID;
        Shop::$customFilters             = $this->state->customFilters;
        Shop::$manufacturerFilterIDs     = $this->state->manufacturerFilterIDs;
        Shop::$kHerstellerFilter         = $this->state->manufacturerFilterID;
        Shop::$bHerstellerFilterNotFound = $this->state->manufacturerFilterNotFound;
        Shop::$bKatFilterNotFound        = $this->state->categoryFilterNotFound;
        Shop::$bSEOMerkmalNotFound       = $this->state->characteristicNotFound;
        Shop::$MerkmalFilter             = $this->state->characteristicFilterIDs;
        Shop::$SuchFilter                = $this->state->searchFilterIDs;
        Shop::$categoryFilterIDs         = $this->state->categoryFilterIDs;
        if ($this->state->type !== '') {
            Shop::${$this->state->type} = $this->state->itemID;
        }
        \executeHook(\HOOK_SEOCHECK_ENDE);
    }
}
