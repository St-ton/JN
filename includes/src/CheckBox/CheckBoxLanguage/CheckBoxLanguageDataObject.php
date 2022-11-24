<?php

namespace JTL\CheckBox\CheckboxLanguage;

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
    protected int $kCheckBoxSprache = 0;
    /**
     * @var int
     */
    protected int $kCheckBox = 0;
    /**
     * @var int
     */
    protected int $kSprache = 0;
    /**
     * @var string
     */
    protected string $cText = '';
    /**
     * @var string
     */
    protected string $cBeschreibung = '';

    /**
     * @var string[]
     */
    private array $mapping = [
        'checkboxLanguageID' => 'kCheckBoxSprache',
        'checkboxID'         => 'kCheckBox',
        'languageID'         => 'kSprache',
        'text'               => 'cText',
        'description'        => 'cBeschreibung',
    ];

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if ($name === $this->primaryKey) {
            $this->kCheckBoxSprache = (int)$value;
        }

        if (isset($this->mapping[$name])) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->mapping[$name])));
            $this->$method($value);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($name === 'kCheckBoxSprache') {
            return $this->kCheckBox;
        }

        if (isset($this->mapping[$name])) {
            $prop = $this->mapping[$name];

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
        return isset($this->name);
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
     * @return int
     */
    public function getKCheckBoxSprache(): int
    {
        return $this->kCheckBoxSprache;
    }

    /**
     * @param $kCheckBoxSprache
     * @return CheckboxLanguageDataObject
     */
    public function setKCheckBoxSprache($kCheckBoxSprache): CheckboxLanguageDataObject
    {
        $this->kCheckBoxSprache = (int)$kCheckBoxSprache;
        return $this;
    }

    /**
     * @return int
     */
    public function getKCheckBox(): int
    {
        return $this->kCheckBox;
    }

    /**
     * @param  $kCheckBox
     * @return CheckboxLanguageDataObject
     */
    public function setKCheckBox($kCheckBox): CheckboxLanguageDataObject
    {
        $this->kCheckBox = (int)$kCheckBox;
        return $this;
    }

    /**
     * @return int
     */
    public function getKSprache(): int
    {
        return $this->kSprache;
    }

    /**
     * @param  $kSprache
     * @return CheckboxLanguageDataObject
     */
    public function setKSprache($kSprache): CheckboxLanguageDataObject
    {
        $this->kSprache = (int)$kSprache;
        return $this;
    }

    /**
     * @return string
     */
    public function getCText(): string
    {
        return $this->cText;
    }

    /**
     * @param string $cText
     * @return CheckboxLanguageDataObject
     */
    public function setCText(string $cText): CheckboxLanguageDataObject
    {
        $this->cText = $cText;
        return $this;
    }

    /**
     * @return string
     */
    public function getCBeschreibung(): string
    {
        return $this->cBeschreibung;
    }

    /**
     * @param string $cBeschreibung
     * @return CheckboxLanguageDataObject
     */
    public function setCBeschreibung(string $cBeschreibung): CheckboxLanguageDataObject
    {
        $this->cBeschreibung = $cBeschreibung;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return array_flip($this->mapping);
    }
}
