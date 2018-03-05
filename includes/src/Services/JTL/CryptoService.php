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
    public function randomBytes($bytesAmount)
    {
        return random_bytes($bytesAmount);
    }

    /**
     * @inheritdoc
     */
    public function randomString($bytesAmount)
    {
        return bin2hex($this->randomBytes($bytesAmount));
    }

    /**
     * @inheritdoc
     */
    public function randomInt($min, $max)
    {
        return random_int($min, $max);
    }
}
