<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Validation;

/**
 * Interface ValidatorInterface
 * @package JTL\Plugin\Admin\Validation
 */
interface ValidatorInterface
{
    /**
     * @return string
     */
    public function getDir(): string;

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void;

    /**
     * @param string $path
     * @param bool   $forUpdate
     * @return int
     */
    public function validateByPath(string $path, bool $forUpdate = false): int;

    /**
     * @param int  $kPlugin
     * @param bool $forUpdate
     * @return int
     */
    public function validateByPluginID(int $kPlugin, bool $forUpdate = false): int;

    /**
     * @param array $xml
     * @param bool  $forUpdate
     * @return int
     * @former pluginPlausiIntern()
     */
    public function pluginPlausiIntern($xml, bool $forUpdate): int;
}
