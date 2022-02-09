<?php declare(strict_types=1);

namespace JTL\Link;

use Illuminate\Support\Collection;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class LinkGroupCollection
 *
 * this allows calls like LinkService::getLinkgroups()->Fuss to access a link group by its template name
 * for compatibility reasons only
 *
 * @package JTL\Link
 */
final class LinkGroupCollection extends Collection
{
    /**
     * @var array
     */
    public array $Link_Datenschutz = [];

    /**
     * @var array
     */
    public array $Link_Versandseite = [];

    /**
     * @var array
     */
    public array $Link_AGB = [];


    public function __wakeup()
    {
        $languageID         = Shop::getLanguageID();
        $fallbackLanguageID = LanguageHelper::getDefaultLanguage(true)->getId();
        /** @var LinkGroupInterface $linkGroup */
        foreach ($this->items as $linkGroup) {
            /** @var LinkInterface $link */
            foreach ($linkGroup->getLinks() as $link) {
                $link->setCurrentLanguageID($languageID);
                $link->setFallbackLanguageID($fallbackLanguageID);
            }
        }
    }

    /**
     * @param string $name
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByTemplate(string $name): ?LinkGroupInterface
    {
        return $this->filter(static function (LinkGroupInterface $e) use ($name) {
            return $e->getTemplate() === $name;
        })->first();
    }

    /**
     * @param int $id
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByID(int $id): ?LinkGroupInterface
    {
        return $this->filter(static function (LinkGroupInterface $e) use ($id) {
            return $e->getID() === $id;
        })->first();
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function __get($key)
    {
        return $this->getLinkgroupByTemplate($key) ?? parent::__get($key);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return \property_exists($this, $name) || $this->getLinkgroupByTemplate($name) !== null;
    }
}
