<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation;

/**
 * Interface ValidatorInterface
 * @package Plugin\Admin\Validation
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
     * @param      $xml
     * @param bool $forUpdate
     * @return int
     * @former pluginPlausiIntern()
     */
    public function pluginPlausiIntern($xml, bool $forUpdate): int;
}
