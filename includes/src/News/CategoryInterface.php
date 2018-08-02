<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


/**
 * Interface CategoryInterface
 * @package News
 */
interface CategoryInterface
{
    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getNames(): array;

    /**
     * @param string   $name
     * @param int|null $idx
     */
    public function setName(string $name, int $idx = null);

    /**
     * @param string[] $names
     */
    public function setNames(array $names);

    /**
     * @return string[]
     */
    public function getMetaTitles(): array;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaTitle(int $idx = null): string;

    /**
     * @param string   $metaTitle
     * @param int|null $idx
     */
    public function setMetaTitle(string $metaTitle, int $idx = null);

    /**#
     * @param string[] $metaTitles
     */
    public function setMetaTitles(array $metaTitles);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaKeyword(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getMetaKeywords(): array;

    /**
     * @param string   $metaKeyword
     * @param int|null $idx
     */
    public function setMetaKeyword(string $metaKeyword, int $idx = null);

    /**
     * @param string[] $metaKeywords
     */
    public function setMetaKeywords(array $metaKeywords);

    /**
     * @param int|null $idx
     * @return string
     */
    public function getMetaDescription(int $idx = null): string;

    /**
     * @return string[]
     */
    public function getMetaDescriptions(): array;

    /**
     * @param string   $metaDescription
     * @param int|null $idx
     */
    public function setMetaDescription(string $metaDescription, int $idx = null);

    /**
     * @param string[] $metaDescriptions
     */
    public function setMetaDescriptions(array $metaDescriptions);
}
