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
    public function getLinkGroups(): LinkGroupCollection;

    /**
     * @param Collection $linkGroups
     */
    public function setLinkGroups(Collection $linkGroups);

    /**
     * @return LinkGroupCollection
     */
    public function getVisibleLinkGroups(): LinkGroupCollection;

    /**
     * @param LinkGroupCollection $linkGroups
     */
    public function setVisibleLinkGroups(LinkGroupCollection $linkGroups);

    /**
     * @param int $customerGroupID
     * @param int $customerID
     * @return $this
     */
    public function applyVisibilityFilter(int $customerGroupID, int $customerID): LinkGroupListInterface;

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
