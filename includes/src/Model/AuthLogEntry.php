<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Model;

/**
 * Class AuthLogEntry
 * @package Model
 */
class AuthLogEntry
{
    /**
     * @var string
     */
    private $ip = '0.0.0.0';

    /**
     * @var string
     */
    private $user = 'Unknown user';

    /**
     * @var int
     */
    public $code = \AdminLoginStatus::ERROR_UNKNOWN;

    /**
     * @return array
     */
    public function asArray() : array
    {
        return [
            'ip'   => $this->getIP(),
            'code' => $this->getCode(),
            'user' => $this->getUser(),
        ];
    }

    /**
     * @return string
     */
    public function getIP() : string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return AuthLogEntry
     */
    public function setIP($ip) : self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string
     */
    public function getUser() : string
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return AuthLogEntry
     */
    public function setUser(string $user) : self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode() : int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return AuthLogEntry
     */
    public function setCode(int $code) : self
    {
        $this->code = $code;

        return $this;
    }
}
