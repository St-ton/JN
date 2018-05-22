<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;


/**
 * Class LinkGroup
 * @package Link
 */
interface LinkGroupInterface
{
    /**
     * @param int $id
     * @return $this
     */
    public function load(int $id): LinkGroupInterface;

    /**
     * @param array $groupLanguages
     * @return $this
     */
    public function map(array $groupLanguages): LinkGroupInterface;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string;

    /**
     * @return array
     */
    public function getNames(): array;

    /**
     * @param array $name
     */
    public function setNames(array $name);

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id);

    /**
     * @return Collection
     */
    public function getLinks(): Collection;

    /**
     * @param Collection $links
     */
    public function setLinks(Collection $links);

    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param string $template
     */
    public function setTemplate(string $template);

    /**
     * @return array
     */
    public function getLanguageID(): array;

    /**
     * @param array $languageID
     */
    public function setLanguageID(array $languageID);

    /**
     * @return array
     */
    public function getLanguageCode(): array;

    /**
     * @param array $languageCode
     */
    public function setLanguageCode(array $languageCode);

    /**
     * @return bool
     */
    public function isSpecial(): bool;

    /**
     * @param bool $isSpecial
     */
    public function setIsSpecial(bool $isSpecial);

    /**
     * @param callable $func
     * @return Collection
     */
    public function filterLinks(callable $func): Collection;
}
