<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Warenlager
 */
class Warenlager extends MainModel
{
    /**
     * @var int
     */
    public $kWarenlager;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cKuerzel;

    /**
     * @var string
     */
    public $cLagerTyp;

    /**
     * @var string
     */
    public $cBeschreibung;

    /**
     * @var string
     */
    public $cStrasse;

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
    public $cLand;

    /**
     * @var int
     */
    public $nFulfillment;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var stdClass
     */
    public $oLageranzeige;

    /**
     * @var array
     */
    public $cSpracheAssoc_arr;

    /**
     * @var float
     */
    public $fBestand;

    /**
     * @var float
     */
    public $fZulauf;

    /**
     * @var string
     */
    public $dZulaufDatum;

    /**
     * @var string
     */
    public $dZulaufDatum_de;

    /**
     * @return stdClass|null
     */
    public function getOLageranzeige(): ?stdClass
    {
        return $this->oLageranzeige;
    }

    /**
     * @param stdClass $oLageranzeige
     * @return $this
     */
    public function setOLageranzeige($oLageranzeige): self
    {
        $this->oLageranzeige = $oLageranzeige;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getLanguages(): ?array
    {
        return $this->cSpracheAssoc_arr;
    }

    /**
     * @param array $cSpracheAssoc_arr
     * @return $this
     */
    public function setLanguages($cSpracheAssoc_arr): self
    {
        $this->cSpracheAssoc_arr = $cSpracheAssoc_arr;

        return $this;
    }

    /**
     * @return float
     */
    public function getStock()
    {
        return $this->fBestand;
    }

    /**
     * @param float $fBestand
     * @return $this
     */
    public function setStock($fBestand): self
    {
        $this->fBestand = $fBestand;

        return $this;
    }

    /**
     * @return float
     */
    public function getBackorder()
    {
        return $this->fZulauf;
    }

    /**
     * @param float $fZulauf
     * @return $this
     */
    public function setBackorder($fZulauf): self
    {
        $this->fZulauf = $fZulauf;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackorderDate(): ?string
    {
        return $this->dZulaufDatum;
    }

    /**
     * @param string $dZulaufDatum
     * @return $this
     */
    public function setBackorderDate($dZulaufDatum): self
    {
        $this->dZulaufDatum = $dZulaufDatum;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBackorderDateDE(): ?string
    {
        return $this->dZulaufDatum_de;
    }

    /**
     * @param string $dZulaufDatum_de
     * @return $this
     */
    public function setBackorderDateDE($dZulaufDatum_de): self
    {
        $this->dZulaufDatum_de = $dZulaufDatum_de;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getID(): ?int
    {
        return $this->kWarenlager;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setID(int $id): self
    {
        $this->kWarenlager = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWarenlager(): ?int
    {
        return $this->getID();
    }

    /**
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager(int $kWarenlager): self
    {
        return $this->setID($kWarenlager);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName): self
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getKuerzel(): ?string
    {
        return $this->cKuerzel;
    }

    /**
     * @param string $cKuerzel
     * @return $this
     */
    public function setKuerzel($cKuerzel): self
    {
        $this->cKuerzel = $cKuerzel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLagerTyp(): ?string
    {
        return $this->cLagerTyp;
    }

    /**
     * @param string $cLagerTyp
     * @return $this
     */
    public function setLagerTyp($cLagerTyp): self
    {
        $this->cLagerTyp = $cLagerTyp;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): string
    {
        return $this->cBeschreibung;
    }

    /**
     * @param string $cBeschreibung
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStrasse(): ?string
    {
        return $this->cStrasse;
    }

    /**
     * @param string $cStrasse
     * @return $this
     */
    public function setStrasse($cStrasse): self
    {
        $this->cStrasse = $cStrasse;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPLZ(): ?string
    {
        return $this->cPLZ;
    }

    /**
     * @param string $cPLZ
     * @return $this
     */
    public function setPLZ($cPLZ): self
    {
        $this->cPLZ = $cPLZ;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrt(): ?string
    {
        return $this->cOrt;
    }

    /**
     * @param string $cOrt
     * @return $this
     */
    public function setOrt($cOrt): self
    {
        $this->cOrt = $cOrt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLand(): ?string
    {
        return $this->cLand;
    }

    /**
     * @param string $cLand
     * @return $this
     */
    public function setLand($cLand): self
    {
        $this->cLand = $cLand;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFulfillment(): ?int
    {
        return $this->nFulfillment;
    }

    /**
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment($nFulfillment): self
    {
        $this->nFulfillment = (int)$nFulfillment;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAktiv(): ?int
    {
        return $this->nAktiv;
    }

    /**
     * @param int $nAktiv
     * @return $this
     */
    public function setAktiv($nAktiv): self
    {
        $this->nAktiv = (int)$nAktiv;

        return $this;
    }

    /**
     * @param int         $kKey
     * @param null|object $oObj
     * @param int|null    $xOption
     */
    public function load($kKey, $oObj = null, $xOption = null): void
    {
        if ($kKey !== null) {
            $kKey = (int)$kKey;
            if ($kKey > 0) {
                $cSqlSelect = '';
                $cSqlJoin   = '';
                if ($xOption !== null && (int)$xOption > 0) {
                    $xOption    = (int)$xOption;
                    $cSqlSelect = ', IF (twarenlagersprache.cName IS NOT NULL, twarenlagersprache.cName, twarenlager.cName) AS cName';
                    $cSqlJoin   = 'LEFT JOIN twarenlagersprache 
                                        ON twarenlagersprache.kWarenlager = twarenlager.kWarenlager
                                        AND twarenlagersprache.kSprache = ' . $xOption;
                }

                $oObj = Shop::Container()->getDB()->query(
                    "SELECT twarenlager.* {$cSqlSelect}
                         FROM twarenlager
                         {$cSqlJoin}
                         WHERE twarenlager.kWarenlager = {$kKey}",
                    \DB\ReturnType::SINGLE_OBJECT
                );
            }
        }
        if (isset($oObj->kWarenlager) && $oObj->kWarenlager > 0) {
            $this->loadObject($oObj);
        }
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     * @throws Exception
     */
    public function save(bool $bPrim = true)
    {
        $oObj = new stdClass();
        foreach (array_keys(get_object_vars($this)) as $cMember) {
            $oObj->$cMember = $this->$cMember;
        }
        if ($this->getWarenlager() === null) {
            $kPrim = Shop::Container()->getDB()->insert('twarenlager', $oObj);
            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }
        } else {
            $xResult = $this->update();
            if ($xResult) {
                return $bPrim ? -1 : true;
            }
        }

        return false;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        $cQuery      = 'UPDATE twarenlager SET ';
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
            $cQuery .= " WHERE kWarenlager = {$this->kWarenlager}";

            return Shop::Container()->getDB()->query($cQuery, \DB\ReturnType::AFFECTED_ROWS);
        }
        throw new Exception('ERROR: Object has no members!');
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->queryPrepared(
            'DELETE twarenlager, twarenlagersprache
                FROM twarenlager
                LEFT JOIN twarenlagersprache 
                    ON twarenlagersprache.kWarenlager = twarenlager.kWarenlager
                WHERE twarenlager.kWarenlager = :lid',
            ['lid' => (int)$this->kWarenlager],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @return bool
     */
    public function loadLanguages(): bool
    {
        if ($this->getWarenlager() > 0) {
            $data = Shop::Container()->getDB()->selectAll('twarenlagersprache', 'kWarenlager', $this->getWarenlager());
            if (count($data) > 0) {
                $this->cSpracheAssoc_arr = [];
                foreach ($data as $oObj) {
                    $this->cSpracheAssoc_arr[(int)$oObj->kSprache] = $oObj->cName;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $bActive
     * @param bool $bLoadLanguages
     * @return array
     */
    public static function getAll(bool $bActive = true, bool $bLoadLanguages = false): array
    {
        $oWarenlager_arr = [];
        $cSql            = $bActive ? ' WHERE nAktiv = 1' : '';
        $oObj_arr = Shop::Container()->getDB()->query(
            "SELECT *
               FROM twarenlager
               {$cSql}",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oObj_arr as $oObj) {
            $oWarenlager = new self(null, $oObj);
            if ($bLoadLanguages) {
                $oWarenlager->loadLanguages();
            }
            $oWarenlager_arr[] = $oWarenlager;
        }

        return $oWarenlager_arr;
    }

    /**
     * @param int        $kArtikel
     * @param int|null   $kSprache
     * @param null|array $xOption_arr
     * @param bool       $bActive
     * @return array
     */
    public static function getByProduct(
        int $kArtikel,
        int $kSprache = null,
        $xOption_arr = null,
        bool $bActive = true
    ): array {
        $oWarenlager_arr = [];
        if ($kArtikel > 0) {
            $cSql     = $bActive ? ' AND twarenlager.nAktiv = 1' : '';
            $oObj_arr = Shop::Container()->getDB()->queryPrepared(
                "SELECT tartikelwarenlager.*
                    FROM tartikelwarenlager
                    JOIN twarenlager 
                        ON twarenlager.kWarenlager = tartikelwarenlager.kWarenlager
                       {$cSql}
                    WHERE tartikelwarenlager.kArtikel = :articleID",
                ['articleID' => $kArtikel],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($oObj_arr as $oObj) {
                $oWarenlager               = new self($oObj->kWarenlager, null, $kSprache);
                $oWarenlager->fBestand     = $oObj->fBestand;
                $oWarenlager->fZulauf      = $oObj->fZulauf;
                $oWarenlager->dZulaufDatum = $oObj->dZulaufDatum;
                if (strlen($oWarenlager->dZulaufDatum) > 1) {
                    try {
                        $oWarenlager->dZulaufDatum_de = (new DateTime($oObj->dZulaufDatum))->format('d.m.Y');
                    } catch (Exception $exc) {
                        $oWarenlager->dZulaufDatum_de = '00.00.0000';
                    }
                }
                if (is_array($xOption_arr)) {
                    $oWarenlager->buildWarehouseInfo($oWarenlager->fBestand, $xOption_arr);
                }
                $oWarenlager_arr[] = $oWarenlager;
            }
        }

        return $oWarenlager_arr;
    }

    /**
     * @param float $fBestand
     * @param array $xOption_arr
     * @return $this
     */
    public function buildWarehouseInfo($fBestand, array $xOption_arr): self
    {
        $this->oLageranzeige                = new stdClass();
        $this->oLageranzeige->cLagerhinweis = [];
        $conf                               = Shop::getSettings([CONF_GLOBAL, CONF_ARTIKELDETAILS]);
        if ($xOption_arr['cLagerBeachten'] === 'Y') {
            if ($fBestand > 0) {
                $this->oLageranzeige->cLagerhinweis['genau']          = $fBestand . ' '
                    . (!empty($xOption_arr['cEinheit']) ? ($xOption_arr['cEinheit'] . ' ') : '')
                    . Shop::Lang()->get('inStock');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productAvailable');
                if (isset($conf['artikeldetails']['artikel_lagerbestandsanzeige'])
                    && $conf['artikeldetails']['artikel_lagerbestandsanzeige'] === 'verfuegbarkeit'
                ) {
                    $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
                }
            } elseif ($xOption_arr['cLagerKleinerNull'] === 'Y') {
                $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
            } else {
                $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('productNotAvailable');
                $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('productNotAvailable');
            }
        } else {
            $this->oLageranzeige->cLagerhinweis['genau']          = Shop::Lang()->get('ampelGruen');
            $this->oLageranzeige->cLagerhinweis['verfuegbarkeit'] = Shop::Lang()->get('ampelGruen');
        }
        if ($xOption_arr['cLagerBeachten'] === 'Y') {
            $this->oLageranzeige->nStatus   = 1;
            $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gelb'];
            if ($fBestand <= (int)$conf['global']['artikel_lagerampel_rot']) {
                $this->oLageranzeige->nStatus   = 0;
                $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_rot'];
            }
            if ($xOption_arr['cLagerBeachten'] !== 'Y'
                || $fBestand >= (int)$conf['global']['artikel_lagerampel_gruen']
                || ($xOption_arr['cLagerBeachten'] === 'Y'
                    && $xOption_arr['cLagerKleinerNull'] === 'Y'
                    && $conf['global']['artikel_ampel_lagernull_gruen'] === 'Y')
            ) {
                $this->oLageranzeige->nStatus   = 2;
                $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gruen'];
            }
        } else {
            $this->oLageranzeige->nStatus = (int)$conf['global']['artikel_lagerampel_keinlager'];

            switch ($this->oLageranzeige->nStatus) {
                case 1:
                    $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gelb'];
                    break;
                case 0:
                    $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_rot'];
                    break;
                default:
                    $this->oLageranzeige->nStatus = 2;
                    $this->oLageranzeige->AmpelText = $xOption_arr['attribut_ampeltext_gruen'];
                    break;
            }
        }

        return $this;
    }
}
