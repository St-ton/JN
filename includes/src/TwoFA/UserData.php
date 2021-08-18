<?php declare(strict_types=1);

namespace JTL\TwoFA;

/**
 * Class UserData
 * @package JTL\Backend
 */
class UserData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var bool
     */
    private $use2FA;

    /**
     * @param int    $id
     * @param string $name
     * @param string $secret
     * @param bool   $use2FA
     */
    public function __construct(int $id = 0, string $name = '', string $secret = '', bool $use2FA = false)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->secret = $secret;
        $this->use2FA = $use2FA;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserData
     */
    public function setID(int $id): UserData
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return UserData
     */
    public function setName(string $name): UserData
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret ?? '';
    }

    /**
     * @param string|null $secret
     * @return UserData
     */
    public function setSecret(?string $secret): UserData
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUse2FA(): bool
    {
        return $this->use2FA;
    }

    /**
     * @param bool $use2FA
     * @return UserData
     */
    public function setUse2FA(bool $use2FA): UserData
    {
        $this->use2FA = $use2FA;

        return $this;
    }
}
