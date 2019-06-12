<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Sprache;

/**
 * Class Merkmal
 * @package JTL\Catalog\Product
 */
class Merkmal
{
    /**
     * @var int
     */
    public $kMerkmal;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cBildpfad;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cBildpfadKlein;

    /**
     * @var string
     */
    public $nBildKleinVorhanden;

    /**
     * @var string
     */
    public $cBildpfadGross;

    /**
     * @var string
     */
    public $nBildGrossVorhanden;

    /**
     * @var string
     */
    public $cBildpfadNormal;

    /**
     * @var array
     */
    public $oMerkmalWert_arr = [];

    /**
     * @var string
     */
    public $cTyp;

    /**
     * @var string
     */
    public $cBildURLKlein;

    /**
     * @var string
     */
    public $cBildURLGross;

    /**
     * @var string
     */
    public $cBildURLNormal;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * Merkmal constructor.
     * @param int  $kMerkmal
     * @param bool $bMMW
     * @param int  $kSprache
     */
    public function __construct(int $kMerkmal = 0, bool $bMMW = false, int $kSprache = 0)
    {
        if ($kMerkmal > 0) {
            $this->loadFromDB($kMerkmal, $bMMW, $kSprache);
        }
    }

    /**
     * @param int  $kMerkmal
     * @param bool $bMMW
     * @param int  $kSprache
     * @return Merkmal
     */
    public function loadFromDB(int $kMerkmal, bool $bMMW = false, int $kSprache = 0): self
    {
        $kSprache       = $kSprache === 0 ? Shop::getLanguageID() : $kSprache;
        $id             = 'mm_' . $kMerkmal . '_' . $this->kSprache;
        $this->kSprache = $kSprache;
        if ($bMMW === false && Shop::has($id)) {
            foreach (\get_object_vars(Shop::get($id)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $kStandardSprache = Sprache::getDefaultLanguage()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $cSelect = 'COALESCE(fremdSprache.cName, standardSprache.cName) AS cName';
            $cJoin   = 'INNER JOIN tmerkmalsprache AS standardSprache 
                            ON standardSprache.kMerkmal = tmerkmal.kMerkmal
                            AND standardSprache.kSprache = ' . $kStandardSprache . '
                        LEFT JOIN tmerkmalsprache AS fremdSprache 
                            ON fremdSprache.kMerkmal = tmerkmal.kMerkmal
                            AND fremdSprache.kSprache = ' . $kSprache;
        } else {
            $cSelect = 'tmerkmalsprache.cName';
            $cJoin   = 'INNER JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = ' . $kSprache;
        }
        $oMerkmal = Shop::Container()->getDB()->query(
            'SELECT tmerkmal.kMerkmal, tmerkmal.nSort, tmerkmal.cBildpfad, tmerkmal.cTyp, ' .
                $cSelect . '
                FROM tmerkmal ' .
                $cJoin . '
                WHERE tmerkmal.kMerkmal = ' . $kMerkmal . '
                ORDER BY tmerkmal.nSort',
            ReturnType::SINGLE_OBJECT
        );
        if (isset($oMerkmal->kMerkmal) && $oMerkmal->kMerkmal > 0) {
            foreach (\array_keys(\get_object_vars($oMerkmal)) as $cMember) {
                $this->$cMember = $oMerkmal->$cMember;
            }
        }
        if ($bMMW && $this->kMerkmal > 0) {
            if ($kSprache !== $kStandardSprache) {
                $cJoinMerkmalwert = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                                        ON standardSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND standardSprache.kSprache = ' . $kStandardSprache . '
                                    LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                                        ON fremdSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND fremdSprache.kSprache = ' . $kSprache;
                $cOrderBy         = 'ORDER BY tmw.nSort, COALESCE(fremdSprache.cWert, standardSprache.cWert)';
            } else {
                $cJoinMerkmalwert = 'INNER JOIN tmerkmalwertsprache AS standardSprache
                                        ON standardSprache.kMerkmalWert = tmw.kMerkmalWert
                                        AND standardSprache.kSprache = ' . $kSprache;
                $cOrderBy         = 'ORDER BY tmw.nSort, standardSprache.cWert';
            }
            $tmpAttributes          = Shop::Container()->getDB()->query(
                "SELECT tmw.kMerkmalWert
                    FROM tmerkmalwert tmw
                    {$cJoinMerkmalwert}
                    WHERE kMerkmal = {$this->kMerkmal}
                    {$cOrderBy}",
                ReturnType::ARRAY_OF_OBJECTS
            );
            $this->oMerkmalWert_arr = [];
            foreach ($tmpAttributes as $oMerkmalWertTMP) {
                $this->oMerkmalWert_arr[] = new MerkmalWert((int)$oMerkmalWertTMP->kMerkmalWert, $this->kSprache);
            }
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildpfadKlein      = \BILD_KEIN_MERKMALBILD_VORHANDEN;
        $this->nBildKleinVorhanden = 0;
        $this->cBildpfadGross      = \BILD_KEIN_MERKMALBILD_VORHANDEN;
        $this->nBildGrossVorhanden = 0;
        if (\mb_strlen($this->cBildpfad) > 0) {
            if (\file_exists(\PFAD_MERKMALBILDER_KLEIN . $this->cBildpfad)) {
                $this->cBildpfadKlein      = \PFAD_MERKMALBILDER_KLEIN . $this->cBildpfad;
                $this->nBildKleinVorhanden = 1;
            }

            if (\file_exists(\PFAD_MERKMALBILDER_NORMAL . $this->cBildpfad)) {
                $this->cBildpfadNormal     = \PFAD_MERKMALBILDER_NORMAL . $this->cBildpfad;
                $this->nBildGrossVorhanden = 1;
            }
        }
        $this->cBildURLGross       = $imageBaseURL . $this->cBildpfadGross;
        $this->cBildURLNormal      = $imageBaseURL . $this->cBildpfadNormal;
        $this->cBildURLKlein       = $imageBaseURL . $this->cBildpfadKlein;
        $this->kMerkmal            = (int)$this->kMerkmal;
        $this->nSort               = (int)$this->nSort;
        $this->nBildKleinVorhanden = (int)$this->nBildKleinVorhanden;
        $this->nBildGrossVorhanden = (int)$this->nBildGrossVorhanden;
        $this->kSprache            = (int)$this->kSprache;

        \executeHook(\HOOK_MERKMAL_CLASS_LOADFROMDB, ['instance' => $this]);
        Shop::set($id, $this);

        return $this;
    }

    /**
     * @param array $attributeIDs
     * @param bool  $bMMW
     * @return array
     */
    public function holeMerkmale(array $attributeIDs, bool $bMMW = false): array
    {
        $attributes = [];
        if (!\is_array($attributeIDs) || \count($attributeIDs) === 0) {
            return $attributes;
        }
        $kSprache = Shop::getLanguage();
        if (!$kSprache) {
            $oSprache = Sprache::getDefaultLanguage();
            if ($oSprache->kSprache > 0) {
                $kSprache = $oSprache->kSprache;
            }
        }
        $kSprache         = (int)$kSprache;
        $kStandardSprache = (int)Sprache::getDefaultLanguage()->kSprache;
        if ($kSprache !== $kStandardSprache) {
            $select = 'COALESCE(fremdSprache.cName, standardSprache.cName) AS cName';
            $join   = 'INNER JOIN tmerkmalsprache AS standardSprache 
                            ON standardSprache.kMerkmal = tmerkmal.kMerkmal
                            AND standardSprache.kSprache = ' . $kStandardSprache . '
                        LEFT JOIN tmerkmalsprache AS fremdSprache 
                            ON fremdSprache.kMerkmal = tmerkmal.kMerkmal
                            AND fremdSprache.kSprache = ' . $kSprache;
        } else {
            $select = 'tmerkmalsprache.cName';
            $join   = 'INNER JOIN tmerkmalsprache 
                            ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = ' . $kSprache;
        }

        $attributes = Shop::Container()->getDB()->query(
            'SELECT tmerkmal.kMerkmal, tmerkmal.nSort, tmerkmal.cBildpfad, tmerkmal.cTyp, ' .
                $select . ' 
                FROM tmerkmal ' .
                $join . ' WHERE tmerkmal.kMerkmal IN(' . \implode(', ', \array_filter($attributeIDs, '\intval')) .
                ') ORDER BY tmerkmal.nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );

        if ($bMMW && \is_array($attributes) && \count($attributes) > 0) {
            $imageBaseURL = Shop::getImageBaseURL();
            foreach ($attributes as $i => $attribute) {
                $attrValue                        = new MerkmalWert(0, $this->kSprache);
                $attributes[$i]->oMerkmalWert_arr = $attrValue->holeAlleMerkmalWerte($attribute->kMerkmal);

                if (\mb_strlen($attribute->cBildpfad) > 0) {
                    $attributes[$i]->cBildpfadKlein  = \PFAD_MERKMALBILDER_KLEIN . $attribute->cBildpfad;
                    $attributes[$i]->cBildpfadNormal = \PFAD_MERKMALBILDER_NORMAL . $attribute->cBildpfad;
                } else {
                    $attributes[$i]->cBildpfadKlein  = \BILD_KEIN_MERKMALBILD_VORHANDEN;
                    $attributes[$i]->cBildpfadNormal = \BILD_KEIN_MERKMALBILD_VORHANDEN;
                }
                $attributes[$i]->cBildURLKlein  = $imageBaseURL . $attributes[$i]->cBildpfadKlein;
                $attributes[$i]->cBildURLNormal = $imageBaseURL . $attributes[$i]->cBildpfadNormal;
            }
        }

        return $attributes;
    }
}
