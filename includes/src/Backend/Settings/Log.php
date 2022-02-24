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
    private int $id;

    /**
     * @var int
     */
    private int $adminID;

    /**
     * @var string
     */
    private string $adminName;

    /**
     * @var string
     */
    private string $changerIp;

    /**
     * @var string
     */
    private string $settingName;

    /**
     * @var string
     */
    private string $settingType;

    /**
     * @var string
     */
    private string $valueOld;

    /**
     * @var string
     */
    private string $valueNew;

    /**
     * @var string
     */
    private string $date;

    /**
     * Log constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param \stdClass $data
     * @return Log
     */
    public function init(\stdClass $data): self
    {
        $this->setID((int)$data->kEinstellungenLog);
        $this->setAdminID((int)$data->kAdminlogin);
        $this->setAdminName($data->adminName ?? \__('unknown') . '(' . $data->kAdminlogin . ')');
        $this->setChangerIP($data->cIP ?? '');
        $this->setSettingType($data->settingType);
        $this->setSettingName($data->cEinstellungenName);
        $this->setValueNew($data->cEinstellungenWertNeu);
        $this->setValueOld($data->cEinstellungenWertAlt);
        $this->setDate($data->dDatum);

        return $this;
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
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getAdminID(): int
    {
        return $this->adminID;
    }

    /**
     * @param int $adminId
     */
    public function setAdminID(int $adminId): void
    {
        $this->adminID = $adminId;
    }

    /**
     * @return string
     */
    public function getSettingName(): string
    {
        return $this->settingName;
    }

    /**
     * @param string $settingName
     */
    public function setSettingName(string $settingName): void
    {
        $this->settingName = $settingName;
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

    /**
     * @return string
     */
    public function getAdminName(): string
    {
        return $this->adminName;
    }

    /**
     * @param string $adminName
     */
    public function setAdminName(string $adminName): void
    {
        $this->adminName = $adminName;
    }

    /**
     * @return string
     */
    public function getChangerIP(): string
    {
        return $this->changerIp;
    }

    /**
     * @param string $ip
     */
    public function setChangerIP(string $ip): void
    {
        $this->changerIp = $ip;
    }

    /**
     * @return string
     */
    public function getSettingType(): string
    {
        return $this->settingType;
    }

    /**
     * @param string $settingType
     */
    public function setSettingType(string $settingType): void
    {
        $this->settingType = $settingType;
    }
}
