<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;


/**
 * Class LinkList
 * @package Link
 */
interface LinkListInterface
{
    /**
     * @param int[] $linkIDs
     * @return Collection
     */
    public function createLinks(array $linkIDs): Collection;

    /**
     * @return Collection
     */
    public function getLinks(): Collection;

    /**
     * @param Collection $links
     */
    public function setLinks(Collection $links);

    /**
     * @param LinkInterface $link
     */
    public function addLink(LinkInterface $link);
}
