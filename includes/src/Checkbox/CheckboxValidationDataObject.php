<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\DataObjects\AbstractDataObject;

/**
 * Class CheckboxValidationDataObject
 * @package JTL\Checkbox
 */
class CheckboxValidationDataObject extends AbstractDataObject
{
    /**
     * @var int
     */
    protected int $customerGroupId = 0;

    /**
     * @var int
     */
    protected int $location = 0;

    /**
     * @var bool
     */
    protected bool $active = false;

    /**
     * @var bool
     */
    protected bool $logging = false;

    /**
     * @var bool
     */
    protected bool $language = false;

    /**
     * @var bool
     */
    protected bool $special = false;

    /**
     * @var bool
     */
    protected bool $hasDownloads = false;

    /**
     * @var string[]
     */
    private array $mapping = [
        'customerGroupId' => 'customerGroupId',
        'kKundengruppe'   => 'customerGroupId',
        'location'        => 'location',
        'language'        => 'language',
        'active'          => 'active',
        'logging'         => 'logging',
        'special'         => 'special',
    ];

    /**
     * @var string[]
     */
    private array $columnMapping = [
    ];

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return \array_merge($this->mapping, $this->columnMapping);
    }

    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return \array_flip($this->mapping);
    }

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return \array_flip($this->columnMapping);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }

    /**
     * @param int $customerGroupId
     * @return CheckboxValidationDataObject
     */
    public function setCustomerGroupId(int $customerGroupId): CheckboxValidationDataObject
    {
        $this->customerGroupId = $customerGroupId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocation(): int
    {
        return $this->location;
    }

    /**
     * @param int $location
     * @return CheckboxValidationDataObject
     */
    public function setLocation(int $location): CheckboxValidationDataObject
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return CheckboxValidationDataObject
     */
    public function setActive(bool|int|string $active): CheckboxValidationDataObject
    {
        $this->active = $this->checkAndReturnBoolValue($active);

        return $this;
    }

    /**
     * @return bool
     */
    public function getLogging(): bool
    {
        return $this->logging;
    }

    /**
     * @param bool $logging
     * @return CheckboxValidationDataObject
     */
    public function setLogging(bool|int|string $logging): CheckboxValidationDataObject
    {
        $this->logging = $this->checkAndReturnBoolValue($logging);

        return $this;
    }

    /**
     * @return bool
     */
    public function getSpecial(): bool
    {
        return $this->special;
    }

    /**
     * @param bool $special
     * @return CheckboxValidationDataObject
     */
    public function setSpecial(bool|int|string $special): CheckboxValidationDataObject
    {
        $this->special = $this->checkAndReturnBoolValue($special);

        return $this;
    }

    /**
     * @return bool
     */
    public function getHasDownloads(): bool
    {
        return $this->hasDownloads;
    }

    /**
     * @param bool $hasDownloads
     * @return CheckboxValidationDataObject
     */
    public function setHasDownloads(bool|int|string $hasDownloads): CheckboxValidationDataObject
    {
        $this->hasDownloads = $this->checkAndReturnBoolValue($hasDownloads);

        return $this;
    }

    /**
     * @return bool
     */
    public function getLanguage(): bool
    {
        return $this->language;
    }

    /**
     * @param bool $language
     * @return CheckboxValidationDataObject
     */
    public function setLanguage(bool|int|string $language): CheckboxValidationDataObject
    {
        $this->language = $this->checkAndReturnBoolValue($language);

        return $this;
    }
}
