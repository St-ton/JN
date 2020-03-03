<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Consent;

/**
 * Interface ItemInterface
 * @package JTL\Consent
 */
interface ItemInterface
{
    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string;

    /**
     * @param string   $name
     * @param int|null $idx
     */
    public function setName(string $name, int $idx = null): void;

    /**
     * @return int|string
     */
    public function getID();

    /**
     * @param int|string $id
     */
    public function setID($id): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getDescription(int $idx = null): string;

    /**
     * @param string   $description
     * @param int|null $idx
     */
    public function setDescription(string $description, int $idx = null): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getPurpose(int $idx = null): string;

    /**
     * @param string   $purpose
     * @param int|null $idx
     */
    public function setPurpose(string $purpose, int $idx = null): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getCompany(int $idx = null): string;

    /**
     * @param string   $company
     * @param int|null $idx
     */
    public function setCompany(string $company, int $idx = null): void;

    /**
     * @param int|null $idx
     * @return string
     */
    public function getTOS(int $idx = null): string;

    /**
     * @param string   $tos
     * @param int|null $idx
     */
    public function setTOS(string $tos, int $idx = null): void;

    /**
     * @return int
     */
    public function getCurrentLanguageID(): int;

    /**
     * @param int $currentLanguageID
     */
    public function setCurrentLanguageID(int $currentLanguageID): void;
}
