<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Class CryptoService
 * @package Services\JTL
 */
class CryptoService implements CryptoServiceInterface
{
    /**
     * @inheritdoc
     */
    public function randomBytes($bytesAmount): string
    {
        return random_bytes($bytesAmount);
    }

    /**
     * @inheritdoc
     */
    public function randomString($bytesAmount): string
    {
        return \bin2hex($this->randomBytes($bytesAmount));
    }

    /**
     * @inheritdoc
     */
    public function randomInt($min, $max): int
    {
        return random_int($min, $max);
    }

    /**
     * @inheritdoc
     */
    public function stableStringEquals(string $string1, string $string2): bool
    {
        return \hash_equals($string1, $string2);
    }

    /**
     * @param string $cText
     * @return string
     */
    public function encryptXTEA(string $cText): string
    {
        return \strlen($cText) > 0
            ? (new \XTEA(BLOWFISH_KEY))->encrypt($cText)
            : $cText;
    }

    /**
     * @param string $cText
     * @return string
     */
    public function decryptXTEA(string $cText): string
    {
        return \strlen($cText) > 0
            ? (new \XTEA(BLOWFISH_KEY))->decrypt($cText)
            : $cText;
    }
}
