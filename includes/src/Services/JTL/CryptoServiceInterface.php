<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Interface CryptoServiceInterface
 *
 * @package Services\JTL
 */
interface CryptoServiceInterface
{
    /**
     * @param int $bytesAmount
     * @return string
     * @throws \Exception
     */
    public function randomBytes($bytesAmount): string;

    /**
     * @param int $bytesAmount
     * @return string
     * @throws \Exception
     */
    public function randomString($bytesAmount): string;

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @throws \Exception
     */
    public function randomInt($min, $max): int;

    /**
     * @param string $string1
     * @param string $string2
     * @return bool
     */
    public function stableStringEquals(string $string1, string $string2): bool;

    /**
     * @param string $cText
     * @return string
     */
    public function encryptXTEA(string $cText): string;

    /**
     * @param string $cText
     * @return string
     */
    public function decryptXTEA(string $cText): string;
}
