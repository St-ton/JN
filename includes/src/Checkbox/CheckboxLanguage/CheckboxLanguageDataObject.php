<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\DataObjects\AbstractDataObject;

/**
 * Class CheckboxLanguageDataObject
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageDataObject extends AbstractDataObject
{
    /**
     * @var string
     */
    private string $primaryKey = 'kCheckBoxSprache';

    /**
     * @var int
     */
    protected int $checkboxLanguageID = 0;

    /**
     * @var int
     */
    protected int $checkboxID = 0;

    /**
     * @var int
     */
    protected int $languageID = 0;

    /**
     * @var string
     */
    protected string $text = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var array|string[]
     */
    private array $mapping = [
        'checkboxLanguageID' => 'checkboxLanguageID',
        'checkboxID'         => 'checkboxID',
        'languageID'         => 'languageID',
        'text'               => 'text',
        'description'        => 'description',
    ];

    /**
     * @var array|string[]
     */
    private array $columnMapping = [
        'kCheckBoxSprache' => 'checkboxLanguageID',
        'kCheckBox'        => 'checkboxID',
        'kSprache'         => 'languageID',
        'cText'            => 'text',
        'cBeschreibung'    => 'description',
    ];

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return string[]
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
    public function getCheckboxLanguageID(): int
    {
        return $this->checkboxLanguageID;
    }

    /**
     * @param int|string $checkboxLanguageID
     * @return CheckboxLanguageDataObject
     */
    public function setCheckboxLanguageID(int|string $checkboxLanguageID): CheckboxLanguageDataObject
    {
        $this->checkboxLanguageID = (int)$checkboxLanguageID;

        return $this;
    }

    /**
     * @return int
     */
    public function getCheckboxID(): int
    {
        return $this->checkboxID;
    }

    /**
     * @param int|string $checkboxID
     * @return CheckboxLanguageDataObject
     */
    public function setCheckboxID(int|string $checkboxID): CheckboxLanguageDataObject
    {
        $this->checkboxID = (int)$checkboxID;

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int|string $languageID
     * @return CheckboxLanguageDataObject
     */
    public function setLanguageID(int|string $languageID): CheckboxLanguageDataObject
    {
        $this->languageID = (int)$languageID;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return CheckboxLanguageDataObject
     */
    public function setText(string $text): CheckboxLanguageDataObject
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return CheckboxLanguageDataObject
     */
    public function setDescription(string $description): CheckboxLanguageDataObject
    {
        $this->description = $description;

        return $this;
    }
}
