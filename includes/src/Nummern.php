<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Nummern
 */
class Nummern
{
    /**
     * @var int
     */
    protected $nNummer;

    /**
     * @var int
     */
    protected $nArt;

    /**
     * @var string
     */
    protected $dAktualisiert;

    /**
     * Nummern constructor.
     * @param int $nArt
     */
    public function __construct(int $nArt = 0)
    {
        if ($nArt > 0) {
            $this->loadFromDB($nArt);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $nArt
     * @return $this
     */
    private function loadFromDB(int $nArt = 0)
    {
        $oObj = Shop::Container()->getDB()->select('tnummern', 'nArt', $nArt);
        if ($oObj !== null && $oObj->nArt > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }
        $kPrim = Shop::Container()->getDB()->insert('tnummern', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @param bool $bDate
     * @return int
     */
    public function update(bool $bDate = true): int
    {
        if ($bDate) {
            $this->setAktualisiert('now()');
        }
        $_upd                = new stdClass();
        $_upd->nNummer       = $this->nNummer;
        $_upd->dAktualisiert = $this->dAktualisiert;

        return Shop::Container()->getDB()->update('tnummern', 'nArt', $this->nArt, $_upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tnummern', 'nArt', $this->nArt);
    }

    /**
     * @param int $nNummer
     * @return $this
     */
    public function setNummer(int $nNummer)
    {
        $this->nNummer = $nNummer;

        return $this;
    }

    /**
     * @param int $nArt
     * @return $this
     */
    public function setArt(int $nArt)
    {
        $this->nArt = $nArt;

        return $this;
    }

    /**
     * @param string $dAktualisiert
     * @return $this
     */
    public function setAktualisiert($dAktualisiert)
    {
        $this->dAktualisiert = $dAktualisiert === 'now()'
            ? date('Y-m-d H:i:s')
            : Shop::Container()->getDB()->escape($dAktualisiert);

        return $this;
    }

    /**
     * @return int
     */
    public function getNummer()
    {
        return $this->nNummer;
    }

    /**
     * @return int
     */
    public function getArt()
    {
        return $this->nArt;
    }

    /**
     * @return string
     */
    public function getAktualisiert()
    {
        return $this->dAktualisiert;
    }
}
