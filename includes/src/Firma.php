<?php declare(strict_types=1);

namespace JTL;

use JTL\Cache\JTLCacheInterface;
use JTL\Country\Country;
use JTL\DB\DbInterface;
use stdClass;

/**
 * Class Firma
 * @package JTL
 */
class Firma
{
    /**
     * @var string|null
     */
    public ?string $cName = null;

    /**
     * @var string|null
     */
    public ?string $cUnternehmer = null;

    /**
     * @var string|null
     */
    public ?string $cStrasse = null;

    /**
     * @var string|null
     */
    public ?string $cHausnummer = null;

    /**
     * @var string|null
     */
    public ?string $cPLZ = null;

    /**
     * @var string|null
     */
    public ?string $cOrt = null;

    /**
     * @var string|null
     */
    public ?string $cLand = null;

    /**
     * @var string|null
     */
    public ?string $cTel = null;

    /**
     * @var string|null
     */
    public ?string $cFax = null;

    /**
     * @var string|null
     */
    public ?string $cEMail = null;

    /**
     * @var string|null
     */
    public ?string $cWWW = null;

    /**
     * @var string|null
     */
    public ?string $cKontoinhaber = null;

    /**
     * @var string|null
     */
    public ?string $cBLZ = null;

    /**
     * @var string|null
     */
    public ?string $cKontoNr = null;

    /**
     * @var string|null
     */
    public ?string $cBank = null;

    /**
     * @var string|null
     */
    public ?string $cUSTID = null;

    /**
     * @var string|null
     */
    public ?string $cSteuerNr = null;

    /**
     * @var string|null
     */
    public ?string $cIBAN = null;

    /**
     * @var string|null
     */
    public ?string $cBIC = null;

    /**
     * @var Country|null
     */
    public ?Country $country = null;

    /**
     * @param bool                   $load
     * @param DbInterface|null       $db
     * @param JTLCacheInterface|null $cache
     */
    public function __construct(
        bool                       $load = true,
        private ?DbInterface       $db = null,
        private ?JTLCacheInterface $cache = null
    ) {
        $this->db    = $db ?? Shop::Container()->getDB();
        $this->cache = $this->cache ?? Shop::Container()->getCache();
        if ($load) {
            $this->loadFromDB();
        }
    }

    /**
     * @return $this
     */
    public function loadFromDB(): self
    {
        $cached = false;
        if (($company = $this->cache->get('jtl_company')) !== false) {
            $cached = true;
            foreach (\get_object_vars($company) as $k => $v) {
                $this->$k = $v;
            }
        } else {
            $countryHelper = Shop::Container()->getCountryService();
            $obj           = $this->db->getSingleObject('SELECT * FROM tfirma LIMIT 1');
            if ($obj !== null) {
                foreach (\get_object_vars($obj) as $k => $v) {
                    $this->$k = $v;
                }
            }
            $iso = $this->cLand !== null ? $countryHelper->getIsoByCountryName($this->cLand) : null;
            if ($iso !== null) {
                $this->country = $countryHelper->getCountry($iso);
                $obj->country  = $this->country;
            }
            if ($obj !== null) {
                $this->cache->set('jtl_company', $obj, [\CACHING_GROUP_CORE]);
            }
        }
        \executeHook(\HOOK_FIRMA_CLASS_LOADFROMDB, ['instance' => $this, 'cached' => $cached]);

        return $this;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj                = new stdClass();
        $obj->cName         = $this->cName;
        $obj->cUnternehmer  = $this->cUnternehmer;
        $obj->cStrasse      = $this->cStrasse;
        $obj->cHausnummer   = $this->cHausnummer;
        $obj->cPLZ          = $this->cPLZ;
        $obj->cOrt          = $this->cOrt;
        $obj->cLand         = $this->cLand;
        $obj->cTel          = $this->cTel;
        $obj->cFax          = $this->cFax;
        $obj->cEMail        = $this->cEMail;
        $obj->cWWW          = $this->cWWW;
        $obj->cKontoinhaber = $this->cKontoinhaber;
        $obj->cBLZ          = $this->cBLZ;
        $obj->cKontoNr      = $this->cKontoNr;
        $obj->cBank         = $this->cBank;
        $obj->cUSTID        = $this->cUSTID;
        $obj->cSteuerNr     = $this->cSteuerNr;
        $obj->cIBAN         = $this->cIBAN;
        $obj->cBIC          = $this->cBIC;

        return $this->db->update('tfirma', 1, 1, $obj);
    }
}
