<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;

/**
 * Interface LinkListInterface
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
    public function setLinks(Collection $links): void;

    /**
     * @param LinkInterface $link
     */
    public function addLink(LinkInterface $link): void;
}
