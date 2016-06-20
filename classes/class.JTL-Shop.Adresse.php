<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Adresse
 */
class Adresse
{
    /**
     * @var string
     */
    public $cAnrede;

    /**
     * @var string
     */
    public $cVorname;

    /**
     * @var string
     */
    public $cNachname;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cFirma;

    /**
     * @var string
     */
    public $cStrasse;

    /**
     * @var string
     */
    public $cAdressZusatz;

    /**
     * @var string
     */
    public $cPLZ;

    /**
     * @var string
     */
    public $cOrt;

    /**
     * @var string
     */
    public $cBundesland;

    /**
     * @var string
     */
    public $cLand;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cMobil;

    /**
     * @var string
     */
    public $cFax;

    /**
     * @var string
     */
    public $cMail;

    /**
     * @var string
     */
    public $cHausnummer;

    /**
     * @var string
     */
    public $cZusatz;

    /**
     * @var array
     */
    protected static $encodedProperties = [
        'cNachname', 'cFirma', 'cZusatz', 'cStrasse'
    ];

    /**
     * Konstruktor
     */
    public function __construct()
    {
    }

    /**
     * encrypt shipping address
     *
     * @return $this
     */
    public function encrypt()
    {
        foreach (self::$encodedProperties as $property) {
            $this->{$property} = verschluesselXTEA(trim($this->{$property}));
        }

        return $this;
    }

    /**
     * decrypt shipping address
     *
     * @return $this
     */
    public function decrypt()
    {
        foreach (self::$encodedProperties as $property) {
            $this->{$property} = trim(entschluesselXTEA($this->{$property}));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (array) get_object_vars($this);
    }

    public function toObject()
    {
        return (object) $this->toArray();
    }

    /**
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * @param object $object
     * @return $this
     */
    public function fromObject($object)
    {
        return $this->fromArray((array)$object);
    }

    public function mappeAnrede($anrede)
    {
        switch (strtolower($anrede)) {
            case 'm':
            Shop::Lang()->get('salutationM', 'global');
                break;
            case 'w':
                Shop::Lang()->get('salutationW', 'global');
                break;
            default:
                return null;
        }
    }

    public function pruefeLandISO($iso)
    {
        preg_match('/[a-zA-Z]{2}/', $iso, $matches);
        if (strlen($matches[0]) != strlen($iso)) {
            $o = landISO($iso);
            if (strlen($o) > 0 && $o !== 'noISO') {
                $iso = $o;
            }
        }
        return $iso;
    }
}
