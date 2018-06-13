<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';

/**
 * Class Kundendatenhistory
 */
class Kundendatenhistory extends MainModel
{
    /**
     * @var int
     */
    public $kKundendatenHistory;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cJsonAlt;

    /**
     * @var string
     */
    public $cJsonNeu;

    /**
     * @var string
     */
    public $cQuelle;

    /**
     * @var string
     */
    public $dErstellt;

    const QUELLE_MEINKONTO = 'Mein Konto';

    const QUELLE_BESTELLUNG = 'Bestellvorgang';

    const QUELLE_DBES = 'Wawi Abgleich';

    /**
     * @return int
     */
    public function getKundendatenHistory(): int
    {
        return (int)$this->kKundendatenHistory;
    }

    /**
     * @param int $kKundendatenHistory
     * @return $this
     */
    public function setKundendatenHistory(int $kKundendatenHistory)
    {
        $this->kKundendatenHistory = $kKundendatenHistory;

        return $this;
    }

    /**
     * @return int
     */
    public function getKunde(): int
    {
        return (int)$this->kKunde;
    }

    /**
     * @param int $kKunde
     * @return $this
     */
    public function setKunde(int $kKunde)
    {
        $this->kKunde = $kKunde;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsonAlt()
    {
        return $this->cJsonAlt;
    }

    /**
     * @param string $cJsonAlt
     * @return $this
     */
    public function setJsonAlt($cJsonAlt)
    {
        $this->cJsonAlt = $cJsonAlt;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsonNeu()
    {
        return $this->cJsonNeu;
    }

    /**
     * @param string $cJsonNeu
     * @return $this
     */
    public function setJsonNeu($cJsonNeu)
    {
        $this->cJsonNeu = $cJsonNeu;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuelle()
    {
        return $this->cQuelle;
    }

    /**
     * @param string $cQuelle
     * @return $this
     */
    public function setQuelle($cQuelle)
    {
        $this->cQuelle = $cQuelle;

        return $this;
    }

    /**
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = ($dErstellt === 'now()')
            ? date('Y-m-d H:i:s')
            : $dErstellt;

        return $this;
    }

    /**
     * @param int         $kKey
     * @param null|object $oObj
     * @param null        $xOption
     * @return $this
     */
    public function load($kKey, $oObj = null, $xOption = null)
    {
        $data = Shop::Container()->getDB()->select('tkundendatenhistory', 'kKundendatenHistory', $kKey);
        if (isset($data->kKundendatenHistory) && $data->kKundendatenHistory > 0) {
            $this->loadObject($data);
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
        unset($oObj->kKundendatenHistory);
        $kPrim = Shop::Container()->getDB()->insert('tkundendatenhistory', $oObj);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        $cQuery      = 'UPDATE tkundendatenhistory SET ';
        $cSet_arr    = [];
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $val        = $this->$cMethod();
                    $mValue     = $val === null
                        ? 'NULL'
                        : ("'" . Shop::Container()->getDB()->escape($val) . "'");
                    $cSet_arr[] = "{$cMember} = {$mValue}";
                }
            }
            $cQuery .= implode(', ', $cSet_arr);
            $cQuery .= " WHERE kKundendatenHistory = {$this->getKundendatenHistory()}";

            return Shop::Container()->getDB()->query($cQuery, \DB\ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkundendatenhistory', 'kKundendatenHistory', $this->getKundendatenHistory());
    }

    /**
     * @param Kunde $oKundeOld
     * @param Kunde $oKundeNew
     * @param string $cQuelle
     * @return bool
     */
    public static function saveHistory($oKundeOld, $oKundeNew, $cQuelle): bool
    {
        if (is_object($oKundeOld) && is_object($oKundeNew)) {
            // Work Around
            if ($oKundeOld->dGeburtstag === '0000-00-00' || $oKundeOld->dGeburtstag === '00.00.0000') {
                $oKundeOld->dGeburtstag = '';
            }
            if ($oKundeNew->dGeburtstag === '0000-00-00' || $oKundeNew->dGeburtstag === '00.00.0000') {
                $oKundeNew->dGeburtstag = '';
            }

            $oKundeNew->cPasswort = $oKundeOld->cPasswort;

            if (!Kunde::isEqual($oKundeOld, $oKundeNew)) {
                $cryptoService = Shop::Container()->getCryptoService(); 
                $oKundeOld = deepCopy($oKundeOld);
                $oKundeNew = deepCopy($oKundeNew);
                // Encrypt Old
                $oKundeOld->cNachname = $cryptoService->encryptXTEA(trim($oKundeOld->cNachname));
                $oKundeOld->cFirma    = $cryptoService->encryptXTEA(trim($oKundeOld->cFirma));
                $oKundeOld->cStrasse  = $cryptoService->encryptXTEA(trim($oKundeOld->cStrasse));
                // Encrypt New
                $oKundeNew->cNachname = $cryptoService->encryptXTEA(trim($oKundeNew->cNachname));
                $oKundeNew->cFirma    = $cryptoService->encryptXTEA(trim($oKundeNew->cFirma));
                $oKundeNew->cStrasse  = $cryptoService->encryptXTEA(trim($oKundeNew->cStrasse));

                $oKundendatenhistory = new self();
                $oKundendatenhistory->setKunde($oKundeOld->kKunde)
                                    ->setJsonAlt(json_encode($oKundeOld))
                                    ->setJsonNeu(json_encode($oKundeNew))
                                    ->setQuelle($cQuelle)
                                    ->setErstellt('now()');

                if ($oKundendatenhistory->save() > 0) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }
}
