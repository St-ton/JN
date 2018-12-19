<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;

use Tightenco\Collect\Support\Collection;

/**
 * Interface LinkGroupInterface
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
    public function setNames(array $name): void;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return Collection
     */
    public function getLinks(): Collection;

    /**
     * @param Collection $links
     */
    public function setLinks(Collection $links): void;

    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void;

    /**
     * @return array
     */
    public function getLanguageID(): array;

    /**
     * @param array $languageID
     */
    public function setLanguageID(array $languageID): void;

    /**
     * @return array
     */
    public function getLanguageCode(): array;

    /**
     * @param array $languageCode
     */
    public function setLanguageCode(array $languageCode): void;

    /**
     * @return bool
     */
    public function isSpecial(): bool;

    /**
     * @param bool $isSpecial
     */
    public function setIsSpecial(bool $isSpecial): void;

    /**
     * @param callable $func
     * @return Collection
     */
    public function filterLinks(callable $func): Collection;

    /**
     * @param int $langID
     * @return bool
     */
    public function isAvailableInLanguage(int $langID): bool;
}
