<?php declare(strict_types=1);

namespace JTL\CheckBox;

use JTL\DataObjects\GenericDataObject;

class CheckBoxDataObject extends GenericDataObject
{
    protected string $primaryKey = 'kCheckBox';

    protected int $kCheckBox         = 0;
    protected int $kLink             = 0;
    protected int $kCheckBoxFunktion = 0;
    protected string $cName          = '';
    protected string $cKundengruppe  = '';
    protected string $cAnzeigeOrt    = '';
    protected bool $nAktiv           = true;
    protected bool $nPflicht         = false;
    protected bool $nLogging         = true;
    protected int $nSort             = 0;
    protected string $dErstellt      = '';
    protected bool $nInternal        = false;

    protected array $mapping = [
        'checkboxID'             => 'kCheckBox',
        'linkID'                 => 'kLink',
        'checkBoxFunctionID'     => 'kCheckBoxFunktion',
        'name'                   => 'cName',
        'customerGroupsSelected' => 'cKundengruppe',
        'displayAt'              => 'cAnzeigeOrt',
        'isActive'               => 'nAktiv',
        'isMandatory'            => 'nPflicht',
        'hasLogging'             => 'nLogging',
        'sort'                   => 'nSort',
        'created'                => 'dErstellt',
        'isInternal'             => 'nInternal',
    ];


    public function __set(string $name, $value): void
    {
        if ($name === 'kCheckBox') {
            $this->kCheckBox = (int)$value;
        }
    }

    public function __get(string $name): ?int
    {
        if ($name === 'kCheckBox') {
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

    public function getKCheckBox(): ?int
    {
        return $this->kCheckBox;
    }
    
    /**
     * @return int
     */
    public function getKLink(): int
    {
        return $this->kLink;
    }

    /**
     * @param int $kLink
     * @return CheckBoxDataObject
     */
    public function setKLink(int $kLink): CheckBoxDataObject
    {
        $this->kLink = $kLink;
        return $this;
    }

    /**
     * @return int
     */
    public function getKCheckBoxFunktion(): int
    {
        return $this->kCheckBoxFunktion;
    }

    /**
     * @param int $kCheckBoxFunktion
     * @return CheckBoxDataObject
     */
    public function setKCheckBoxFunktion(int $kCheckBoxFunktion): CheckBoxDataObject
    {
        $this->kCheckBoxFunktion = $kCheckBoxFunktion;
        return $this;
    }

    /**
     * @return string
     */
    public function getCName(): string
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return CheckBoxDataObject
     */
    public function setCName(string $cName): CheckBoxDataObject
    {
        $this->cName = $cName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCKundengruppe(): string
    {
        return $this->cKundengruppe;
    }

    /**
     * @param string $cKundengruppe
     * @return CheckBoxDataObject
     */
    public function setCKundengruppe(string $cKundengruppe): CheckBoxDataObject
    {
        $this->cKundengruppe = $cKundengruppe;
        return $this;
    }

    /**
     * @return string
     */
    public function getCAnzeigeOrt(): string
    {
        return $this->cAnzeigeOrt;
    }

    /**
     * @param string $cAnzeigeOrt
     * @return CheckBoxDataObject
     */
    public function setCAnzeigeOrt(string $cAnzeigeOrt): CheckBoxDataObject
    {
        $this->cAnzeigeOrt = $cAnzeigeOrt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNAktiv(): bool
    {
        return $this->nAktiv;
    }

    /**
     * @param $nAktiv
     * @return CheckBoxDataObject
     */
    public function setNAktiv(int $nAktiv): CheckBoxDataObject
    {
        $this->nAktiv = (bool)$nAktiv;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNPflicht(): bool
    {
        return $this->nPflicht;
    }

    /**
     * @param bool $nPflicht
     * @return CheckBoxDataObject
     */
    public function setNPflicht(int $nPflicht): CheckBoxDataObject
    {
        $this->nPflicht = (bool)$nPflicht;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNLogging(): bool
    {
        return $this->nLogging;
    }

    /**
     * @param bool $nLogging
     * @return CheckBoxDataObject
     */
    public function setNLogging(int  $nLogging): CheckBoxDataObject
    {
        $this->nLogging = (bool)$nLogging;
        return $this;
    }

    /**
     * @return int
     */
    public function getNSort(): int
    {
        return $this->nSort;
    }

    /**
     * @param int $nSort
     * @return CheckBoxDataObject
     */
    public function setNSort(int $nSort): CheckBoxDataObject
    {
        $this->nSort = $nSort;
        return $this;
    }

    /**
     * @return string
     */
    public function getDErstellt(): string
    {
        return $this->dErstellt;
    }

    /**
     * @param string $dErstellt
     * @return CheckBoxDataObject
     */
    public function setDErstellt(string $dErstellt): CheckBoxDataObject
    {
        $this->dErstellt = $dErstellt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNInternal(): bool
    {
        return $this->nInternal;
    }

    /**
     * @param bool $nInternal
     * @return CheckBoxDataObject
     */
    public function setNInternal(bool $nInternal): CheckBoxDataObject
    {
        $this->nInternal = $nInternal;
        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getReverseMapping()
    {
        return array_flip($this->mapping);
    }
}
