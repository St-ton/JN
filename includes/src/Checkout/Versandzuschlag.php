<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Checkout;

use JTL\Shop;
use JTL\DB\ReturnType;
use JTL\MagicCompatibilityTrait;

/**
 * Class Versandzuschlag
 * @package JTL\Checkout
 */
class Versandzuschlag
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'kVersandzuschlag' => 'SurchargeID',
        'cISO'             => 'ISO',
        'cName'            => 'Title',
        'fZuschlag'        => 'Surcharge'
    ];

    /**
     * @var int
     */
    public $surchargeID;

    /**
     * @var string
     */
    public $ISO;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $surcharge;

    /**
     * @var int
     */
    public $shippingMethod;

    /**
     * @var array
     */
    public $ZIPCodes;

    /**
     * @var array
     */
    public $ZIPAreas = [];

    /**
     * @var array
     */
    public $names;

    /**
     * Versandzuschlag constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->setSurchargeID($id)
                 ->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     */
    public function loadFromDB(int $id): void
    {
        $db        = Shop::Container()->getDB();
        $surcharge = $db->queryPrepared(
            'SELECT * 
                FROM tversandzuschlag
                WHERE kVersandzuschlag = :id',
            ['id' => $id],
            ReturnType::SINGLE_OBJECT
        );

        if ($surcharge !== null) {
            $this->setTitle($surcharge->cName)
                 ->setISO($surcharge->cISO)
                 ->setSurcharge((float)$surcharge->fZuschlag)
                 ->setShippingMethod((int)$surcharge->kVersandart);

            $zips = $db->queryPrepared(
                'SELECT vzp.cPLZ, vzp.cPLZAb, vzp.cPLZBis 
                    FROM tversandzuschlag AS vz
                    JOIN tversandzuschlagplz AS vzp USING(kVersandzuschlag) 
                    WHERE vz.kVersandzuschlag = :id',
                ['id' => $id],
                ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($zips as $zip) {
                if ($zip->cPLZ !== null) {
                    $this->setZIPCode($zip->cPLZ);
                } elseif ($zip->cPLZAb !== null && $zip->cPLZBis !== null) {
                    $this->setZIPArea((int)$zip->cPLZAb, (int)$zip->cPLZBis);
                }
            }

            $names = $db->queryPrepared(
                'SELECT vzs.cName, s.kSprache 
                    FROM tversandzuschlag AS vz
                    LEFT JOIN tversandzuschlagsprache AS vzs USING(kVersandzuschlag) 
                    LEFT JOIN tsprache as s ON s.cISO = vzs.cISOSprache
                    WHERE vz.kVersandzuschlag = :id',
                ['id' => $id],
                ReturnType::ARRAY_OF_OBJECTS
            );

            foreach ($names as $name) {
                $this->setName($name->cName, (int)$name->kSprache);
            }
        }
    }

    /**
     * @param string $zip
     * @return bool
     */
    public function hasZIPCode(string $zip): bool
    {
        foreach ($this->getZIPCodes() as $zipTMP) {
            if ($zip === $zipTMP) {
                return true;
            }
        }

        foreach ($this->getZIPAreas() as $zipArea) {
            if ($zipArea->isInArea((int)$zip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getSurchargeID(): int
    {
        return $this->surchargeID;
    }

    /**
     * @param int $id
     * @return Versandzuschlag
     */
    public function setSurchargeID(int $id): self
    {
        $this->surchargeID = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getISO(): string
    {
        return $this->ISO;
    }

    /**
     * @param string $ISO
     * @return Versandzuschlag
     */
    public function setISO(string $ISO): self
    {
        $this->ISO = $ISO;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Versandzuschlag
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return float
     */
    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    /**
     * @param float $surcharge
     * @return Versandzuschlag
     */
    public function setSurcharge(float $surcharge): self
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    /**
     * @return int
     */
    public function getShippingMethod(): int
    {
        return $this->shippingMethod;
    }

    /**
     * @param int $shippingMethod
     * @return Versandzuschlag
     */
    public function setShippingMethod(int $shippingMethod): self
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @return array
     */
    public function getZIPCodes(): array
    {
        return $this->ZIPCodes;
    }

    /**
     * @param array $ZIPCodes
     * @return Versandzuschlag
     */
    public function setZIPCodes(array $ZIPCodes): self
    {
        $this->ZIPCodes = $ZIPCodes;

        return $this;
    }

    /**
     * @param string $ZIPCode
     * @return Versandzuschlag
     */
    public function setZIPCode(string $ZIPCode): self
    {
        $this->ZIPCodes[] = $ZIPCode;

        return $this;
    }

    /**
     * @return array
     */
    public function getZIPAreas(): array
    {
        return $this->ZIPAreas;
    }

    /**
     * @param array $ZIPAreas
     * @return Versandzuschlag
     */
    public function setZIPAreas(array $ZIPAreas): self
    {
        $this->ZIPAreas = $ZIPAreas;

        return $this;
    }

    /**
     * @param int $ZIPFrom
     * @param int $ZIPTo
     * @return Versandzuschlag
     */
    public function setZIPArea(int $ZIPFrom, int $ZIPTo): self
    {
        $this->ZIPAreas[] = new VersandzuschlagBereich($ZIPTo, $ZIPFrom);

        return $this;
    }


    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string
    {
        $idx = $idx ?? Shop::getLanguageID();

        return $this->names[$idx] ?? '';
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @param string $name
     * @param int|null $idx
     */
    public function setName(string $name, int $idx = null): void
    {
        $this->names[$idx ?? Shop::getLanguageID()] = $name;
    }

    /**
     * @param array $names
     */
    public function setNames(array $names): void
    {
        $this->names = $names;
    }
}
