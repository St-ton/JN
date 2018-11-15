<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\ExtensionData;

use Link\LinkList;
use Tightenco\Collect\Support\Collection;

/**
 * Class Links
 * @package Plugin\ExtensionData
 */
class Links
{
    /**
     * @var Collection
     */
    private $links;

    public function __construct()
    {
        $this->links = new Collection();
    }

    /**
     * @param $data
     * @return $this
     */
    public function load($data): self
    {
        $data        = \Functional\map($data, function ($e) {
            return (int)$e->kLink;
        });
        $links       = new LinkList(\Shop::Container()->getDB());
        $this->links = $links->createLinks($data);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @param Collection $links
     */
    public function setLinks(Collection $links): void
    {
        $this->links = $links;
    }
}
