<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;


/**
 * Class LinkGroupList
 * @package Filter
 */
interface LinkGroupListInterface
{
    /**
     * @return $this
     */
    public function loadAll(): LinkGroupListInterface;

    /**
     * @return LinkGroupCollection
     */
    public function getLinkgroups(): LinkGroupCollection;

    /**
     * @param Collection $linkgroups
     */
    public function setLinkgroups(Collection $linkgroups);

    /**
     * @return Collection
     */
    public function getVisibleLinkgroups(): Collection;

    /**
     * @param Collection $linkgroups
     */
    public function setVisiblyLinkgroups(Collection $linkgroups);

    /**
     * @return $this
     */
    public function applyVisibilityFilter(): LinkGroupListInterface;

    /**
     * @former aktiviereLinks()
     * @param int $pageType
     * @return LinkGroupCollection
     */
    public function activate(int $pageType): LinkGroupCollection;

    /**
     * @param string $name
     * @param bool   $filtered
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByTemplate(string $name, $filtered = true);

    /**
     * @param int  $id
     * @param bool $filtered
     * @return LinkGroupInterface|null
     */
    public function getLinkgroupByID(int $id, $filtered = true);
}
