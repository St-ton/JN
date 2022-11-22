<?php

namespace JTL\CheckBox\CheckBoxLanguage;

use JTL\DataObjects\DataObjectInterface as DataObjectInterface;
use JTL\DataObjects\GenericDataObject as GenericDataObject;

class CheckBoxLanguageDataObject extends GenericDataObject
{
    private string $primaryKey      = 'kCheckBoxSprache';
    protected int $kCheckBoxSprache = 0;
    protected int $kCheckBox        = 0;
    protected int $kSprache         = 0;
    protected string $cText         = '';
    protected string $cBeschreibung = '';

    protected $mapping = [
        'checkBoxLanguageID' => 'kCheckBoxSprache',
        'checkBoxID'         => 'kCheckBox',
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

    public function __set(string $name, $value): void
    {
        if ($name === $this->primaryKey) {
            $this->kCheckBoxSprache = (int)$value;
        }

//        if (in_array($name, $this->mapping)) {
//            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->mapping[$name])));
//            $property = $this->mapping[$name];
//            $this->$method( $value);
//        }
    }

    public function __get(string $name): ?int
    {
        if ($name === 'kCheckBoxSprache') {
            return $this->kCheckBox;
        }


        return null;
    }

    public function __isset($name)
    {
        return isset($this->name);
    }

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
     * @param int $kCheckBoxSprache
     * @return CheckBoxLanguageDataObject
     */
    public function setKCheckBoxSprache(int $kCheckBoxSprache): CheckBoxLanguageDataObject
    {
        $this->kCheckBoxSprache = $kCheckBoxSprache;
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
     * @param int $kCheckBox
     * @return CheckBoxLanguageDataObject
     */
    public function setKCheckBox(int $kCheckBox): CheckBoxLanguageDataObject
    {
        $this->kCheckBox = $kCheckBox;
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
     * @param int $kSprache
     * @return CheckBoxLanguageDataObject
     */
    public function setKSprache(int $kSprache): CheckBoxLanguageDataObject
    {
        $this->kSprache = $kSprache;
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
     * @return CheckBoxLanguageDataObject
     */
    public function setCText(string $cText): CheckBoxLanguageDataObject
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
     * @return CheckBoxLanguageDataObject
     */
    public function setCBeschreibung(string $cBeschreibung): CheckBoxLanguageDataObject
    {
        $this->cBeschreibung = $cBeschreibung;
        return $this;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getReverseMapping(): array
    {
        return array_flip($this->mapping);
    }
}
