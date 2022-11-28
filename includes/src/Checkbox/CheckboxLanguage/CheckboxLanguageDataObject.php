<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\DataObjects\AbstractGenericDataObject;

class CheckboxLanguageDataObject extends AbstractGenericDataObject
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
     * @var string[]
     */
    private array $mapping = [
        'checkboxLanguageID' => 'checkboxLanguageID',
        'checkboxID'         => 'checkboxID',
        'languageID'         => 'languageID',
        'text'               => 'text',
        'description'        => 'description',
        ];

    private array $columnMapping = [
        'kCheckBoxSprache'   => 'checkboxLanguageID',
        'kCheckBox'          => 'checkboxID',
        'kSprache'           => 'languageID',
        'cText'              => 'text',
        'cBeschreibung'      => 'description',
    ];

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $map = $this->getMapping();
        if ($name === $this->primaryKey) {
            $this->checkboxLanguageID = (int)$value;
        }

        if (isset($map[$name])) {
            $method = 'set' . \str_replace(' ', '', \ucwords(\str_replace('_', ' ', $map[$name])));
            $this->$method($value);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $map = $this->getMapping();

        if ($name === 'kCheckBoxSprache') {
            return $this->checkboxLanguageID;
        }

        if (isset($map[$name])) {
            $prop = $map[$name];

            return $this->$prop;
        }

        return null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->$name);
    }


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
        return array_merge($this->mapping, $this->columnMapping);
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
        return array_flip($this->columnMapping);
    }

    /**
     * @return int
     */
    public function getCheckboxLanguageID(): int
    {
        return $this->checkboxLanguageID;
    }

    /**
     * @param  $checkboxLanguageID
     * @return CheckboxLanguageDataObject
     */
    public function setCheckboxLanguageID($checkboxLanguageID): CheckboxLanguageDataObject
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
     * @param  $checkboxID
     * @return CheckboxLanguageDataObject
     */
    public function setCheckboxID($checkboxID): CheckboxLanguageDataObject
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
     * @param  $languageID
     * @return CheckboxLanguageDataObject
     */
    public function setLanguageID($languageID): CheckboxLanguageDataObject
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
