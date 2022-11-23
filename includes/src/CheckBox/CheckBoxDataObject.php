<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\DataObjects\GenericDataObject;

/**
 *
 */
class CheckboxDataObject extends GenericDataObject
{
    /**
     * @var string
     */
    private string $primaryKey = 'kCheckBox';

    /**
     * @var int
     */
    protected int $kCheckBox = 0;
    /**
     * @var int
     */
    protected int $kLink = 0;
    /**
     * @var int
     */
    protected int $kCheckBoxFunktion = 0;
    /**
     * @var string
     */
    protected string $cName = '';
    /**
     * @var string
     */
    protected string $cKundengruppe = '';
    /**
     * @var string
     */
    protected string $cAnzeigeOrt = '';
    /**
     * @var bool
     */
    protected bool $nAktiv = true;
    /**
     * @var bool
     */
    protected bool $nPflicht = false;
    /**
     * @var bool
     */
    protected bool $nLogging = true;
    /**
     * @var int
     */
    protected int $nSort = 0;
    /**
     * @var string
     */
    protected string $dErstellt = '';
    /**
     * @var string
     */
    private string $dErstellt_DE = '';
    /**
     * @var bool
     */
    protected bool $nInternal = false;

    /**
     * @var array|string[]
     */
    private array $mapping = [
        'checkboxID'             => 'kCheckBox',
        'linkID'                 => 'kLink',
        'checkboxFunctionID'     => 'kCheckBoxFunktion',
        'name'                   => 'cName',
        'customerGroupsSelected' => 'cKundengruppe',
        'displayAt'              => 'cAnzeigeOrt',
        'isActive'               => 'nAktiv',
        'isMandatory'            => 'nPflicht',
        'hasLogging'             => 'nLogging',
        'sort'                   => 'nSort',
        'created'                => 'dErstellt',
        'created_DE'             => 'dErstellt_DE',
        'isInternal'             => 'nInternal',
    ];

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if ($name === $this->primaryKey) {
            $this->kCheckBox = (int)$value;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($name === 'kCheckBox') {
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
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return int|null
     */
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
     * @param  $kLink
     * @return CheckboxDataObject
     */
    public function setKLink($kLink): CheckboxDataObject
    {
        $this->kLink = (int)$kLink;
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
     * @param  $kCheckBoxFunktion
     * @return CheckboxDataObject
     */
    public function setKCheckBoxFunktion($kCheckBoxFunktion): CheckboxDataObject
    {
        $this->kCheckBoxFunktion = (int)$kCheckBoxFunktion;
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
     * @return CheckboxDataObject
     */
    public function setCName(string $cName): CheckboxDataObject
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
     * @return CheckboxDataObject
     */
    public function setCKundengruppe(string $cKundengruppe): CheckboxDataObject
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
     * @return CheckboxDataObject
     */
    public function setCAnzeigeOrt(string $cAnzeigeOrt): CheckboxDataObject
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
     * @return bool
     */
    public function getNAktiv(): bool
    {
        return $this->nAktiv;
    }

    /**
     * @param $nAktiv
     * @return CheckboxDataObject
     */
    public function setNAktiv($nAktiv): CheckboxDataObject
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
     * @return bool
     */
    public function getNPflicht(): bool
    {
        return $this->nPflicht;
    }

    /**
     * @param $nPflicht
     * @return CheckboxDataObject
     */
    public function setNPflicht($nPflicht): CheckboxDataObject
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
     * @return bool
     */
    public function getNLogging(): bool
    {
        return $this->nLogging;
    }

    /**
     * @param  $nLogging
     * @return CheckboxDataObject
     */
    public function setNLogging($nLogging): CheckboxDataObject
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
     * @param  $nSort
     * @return CheckboxDataObject
     */
    public function setNSort($nSort): CheckboxDataObject
    {
        $this->nSort = (int)$nSort;
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
     * @return CheckboxDataObject
     * @noinspection PhpUnused
     */
    public function setDErstellt(string $dErstellt): CheckboxDataObject
    {
        $this->dErstellt = $dErstellt;
        return $this;
    }

    /**
     * @return string
     */
    public function getDErstelltDE(): string
    {
        return $this->dErstellt_DE;
    }

    /**
     * @param string $dErstellt_DE
     * @return CheckboxDataObject
     */
    public function setDErstelltDE(string $dErstellt_DE): CheckboxDataObject
    {
        $this->dErstellt_DE = $dErstellt_DE;
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
     * @return bool
     */
    public function getNInternal(): bool
    {
        return $this->nInternal;
    }

    /**
     * @param  $nInternal
     * @return CheckboxDataObject
     */
    public function setNInternal($nInternal): CheckboxDataObject
    {
        $this->nInternal = (bool)$nInternal;
        return $this;
    }

    /**
     * @return array|string[]
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
