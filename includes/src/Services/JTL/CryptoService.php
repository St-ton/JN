<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;


class CryptoService implements CryptoServiceInterface
{
    public function randomBytes($bytesAmount)
    {
        return random_bytes($bytesAmount);
    }

    public function randomString($bytesAmount)
    {
        return bin2hex($this->randomBytes($bytesAmount));
    }

    public function randomInt($min, $max)
    {
        return random_int($min, $max);
    }
}