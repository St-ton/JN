<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Class PasswordService
 * @package Services\JTL
 */
class PasswordService implements PasswordServiceInterface
{
    const ASCII_MIN = 33;
    const ASCII_MAX = 127;

    protected $cryptoService;

    public function __construct(CryptoServiceInterface $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * @inheritdoc
     */
    public function generate($length)
    {
        /**
         * I have chosen to not use random_bytes, because using special characters in passwords is recommended. It is
         * therefore better to generate a password with random_int using a char whitelist.
         * Note: random_int is cryptographically secure
         */
        $result = '';
        for ($x = 0; $x < $length; $x++) {
            $no     = $this->cryptoService->randomInt(self::ASCII_MIN, self::ASCII_MAX);
            $result .= chr($no);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function verify($password, $hash)
    {
        $length = strlen($hash);
        if($length === 32) {
            // very old md5 hashes
            return md5($password) === $hash;
        } elseif ($length === 40) {
            return cryptPasswort($password, $hash) !== false;
        } else {
            return password_verify($password, $hash);
        }
    }

    /**
     * @inheritdoc
     */
    public function needsRehash($hash)
    {
        $length = strlen($hash);
        if($length === 32 || $length === 40) {
            return true;
        }

        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function getInfo($hash)
    {
        return password_get_info($hash);
    }
}
