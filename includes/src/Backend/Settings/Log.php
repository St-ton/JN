<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

/**
 * Class Log
 * @package JTL\Backend\Settings
 */
class Log
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $adminId;

    /**
     * @var string
     */
    private $setting;

    /**
     * @var string
     */
    private $valueOld;

    /**
     * @var string
     */
    private $valueNew;

    /**
     * @var string
     */
    private $date;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getAdminId(): int
    {
        return $this->adminId;
    }

    /**
     * @param int $adminId
     */
    public function setAdminId(int $adminId): void
    {
        $this->adminId = $adminId;
    }

    /**
     * @return string
     */
    public function getSetting(): string
    {
        return $this->setting;
    }

    /**
     * @param string $setting
     */
    public function setSetting(string $setting): void
    {
        $this->setting = $setting;
    }

    /**
     * @return string
     */
    public function getValueOld(): string
    {
        return $this->valueOld;
    }

    /**
     * @param string $valueOld
     */
    public function setValueOld(string $valueOld): void
    {
        $this->valueOld = $valueOld;
    }

    /**
     * @return string
     */
    public function getValueNew(): string
    {
        return $this->valueNew;
    }

    /**
     * @param string $valueNew
     */
    public function setValueNew(string $valueNew): void
    {
        $this->valueNew = $valueNew;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}
