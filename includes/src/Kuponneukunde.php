<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\ObjectHelper;

/**
 * Class Kuponneukunde
 */
class Kuponneukunde
{
    /**
     * @var int
     */
    public $kKuponNeukunde;

    /**
     * @var int
     */
    public $kKupon;

    /**
     * @var string
     */
    public $cEmail;

    /**
     * @var string
     */
    public $cDatenHash;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $cVerwendet;

    /**
     * Constructor
     *
     * @param object $oObj
     */
    public function __construct($oObj = null)
    {
        if (is_object($oObj)) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $cMethod = 'set' . substr($cMember, 1);
                    if (method_exists($this, $cMethod)) {
                        $this->$cMethod($oObj->$cMember);
                    }
                }
            }
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods, true)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if ($this->kKuponNeukunde > 0) {
            Shop::Container()->getDB()->delete('tkuponneukunde', 'kKuponNeukunde', (int)$this->kKuponNeukunde);
        }
        $obj = ObjectHelper::copyMembers($this);
        unset($obj->kKuponNeukunde);

        return Shop::Container()->getDB()->insert('tkuponneukunde', $obj) > 0;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return Shop::Container()->getDB()->delete('tkuponneukunde', 'kKuponNeukunde', (int)$this->kKuponNeukunde) === 1;
    }

    /**
     * @param int $kKuponNeukunde
     * @return $this
     */
    public function setKuponNeukunde(int $kKuponNeukunde): self
    {
        $this->kKuponNeukunde = $kKuponNeukunde;

        return $this;
    }

    /**
     * @param int $kKupon
     * @return $this
     */
    public function setKupon(int $kKupon): self
    {
        $this->kKupon = $kKupon;

        return $this;
    }

    /**
     * @param string $cEmail
     * @return $this
     */
    public function setEmail($cEmail): self
    {
        $this->cEmail = $cEmail;

        return $this;
    }

    /**
     * @param string $cDatenHash
     * @return $this
     */
    public function setDatenHash($cDatenHash): self
    {
        $this->cDatenHash = $cDatenHash;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = strtoupper($dErstellt) === 'NOW()'
            ? date('Y-m-d H:i:s')
            : $dErstellt;

        return $this;
    }

    /**
     * @param string $cVerwendet
     * @return $this
     */
    public function setVerwendet($cVerwendet): self
    {
        $this->cVerwendet = $cVerwendet;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getKuponNeukunde(): ?int
    {
        return $this->kKuponNeukunde;
    }

    /**
     * @return int|null
     */
    public function getKupon(): ?int
    {
        return $this->kKupon;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->cEmail;
    }

    /**
     * @return string|null
     */
    public function getDatenHash(): ?string
    {
        return $this->cDatenHash;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @param string $email
     * @param string $hash
     * @return Kuponneukunde|null
     */
    public static function load(string $email, string $hash): ?self
    {
        if (strlen($email) > 0 && strlen($hash) > 0) {
            $Obj = Shop::Container()->getDB()->executeQueryPrepared(
                'SELECT *
                    FROM tkuponneukunde
                    WHERE cEmail = :email
                    OR cDatenHash = :hash',
                [
                    'email' => $email,
                    'hash'  => $hash
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (isset($Obj->kKuponNeukunde) && $Obj->kKuponNeukunde > 0) {
                return new self($Obj);
            }
        }

        return null;
    }

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $street
     * @param string|null $streetnumber
     * @param string|null $zipcode
     * @param string|null $town
     * @param string|null $country
     * @return string
     */
    public static function hash(
        $firstname = null,
        $lastname = null,
        $street = null,
        $streetnumber = null,
        $zipcode = null,
        $town = null,
        $country = null
    ): string {
        $Str = '';
        $Sep = ';';
        if ($firstname !== null) {
            $Str .= $firstname . $Sep;
        }
        if ($lastname !== null) {
            $Str .= $lastname . $Sep;
        }
        if ($street !== null) {
            $Str .= $street . $Sep;
        }
        if ($streetnumber !== null) {
            $Str .= $streetnumber . $Sep;
        }
        if ($zipcode !== null) {
            $Str .= $zipcode . $Sep;
        }
        if ($town !== null) {
            $Str .= $town . $Sep;
        }
        if ($country !== null) {
            $Str .= $country . $Sep;
        }

        return md5($Str);
    }
}
