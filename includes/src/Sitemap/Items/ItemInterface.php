<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Interface ItemInterface
 * @package Sitemap\Items
 */
interface ItemInterface
{
    /**
     * @return string|null
     */
    public function getLastModificationTime(): ?string;

    /**
     * @param string $time
     */
    public function setLastModificationTime($time): void;

    /**
     * @return void
     */
    public function generateImage(): void;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string $image
     */
    public function setImage(string $image): void;

    /**
     * @return void
     */
    public function generateLocation(): void;

    /**
     * @return string
     */
    public function getLocation(): string;

    /**
     * @param string $location
     */
    public function setLocation(string $location): void;

    /**
     * @return string|null
     */
    public function getChangeFreq(): ?string;

    /**
     * @param string $changeFreq
     */
    public function setChangeFreq(string $changeFreq): void;

    /**
     * @return string|null
     */
    public function getPriority(): ?string;

    /**
     * @param string $priority
     */
    public function setPriority(string $priority): void;

    /**
     * @param mixed $data
     */
    public function setData($data): void;

    /**
     * @param mixed $data
     */
    public function generateData($data): void;
}
