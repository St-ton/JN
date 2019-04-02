<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation;

/**
 * Interface ValidationItemInterface
 * @package JTL\Plugin\Admin\Validation
 */
interface ValidationItemInterface
{
    /**
     * @return int
     */
    public function validate(): int;

    /**
     * @return array
     */
    public function getBaseNode(): array;

    /**
     * @param array $node
     */
    public function setBaseNode(array $node): void;

    /**
     * @return array
     */
    public function getInstallNode(): array;

    /**
     * @param array $node
     */
    public function setInstallNode(array $node): void;

    /**
     * @return string
     */
    public function getPluginID(): string;

    /**
     * @param string $id
     */
    public function setPluginID(string $id): void;

    /**
     * @return string
     */
    public function getDir(): string;

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void;

    /**
     * @return string
     */
    public function getBaseDir(): string;

    /**
     * @param string $dir
     */
    public function setBaseDir(string $dir): void;

    /**
     * @return string
     */
    public function getVersion(): string;

    /**
     * @param string $version
     */
    public function setVersion(string $version): void;
}
