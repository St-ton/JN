<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use JTL\Catalog\Product\Artikel;
use JTL\DB\ReturnType;
use stdClass;

/**
 * Class ImageMap
 * @package JTL
 */
class ImageMap implements IExtensionPoint
{
    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * ImageMap constructor.
     */
    public function __construct()
    {
        $this->kSprache      = Shop::getLanguage();
        $this->kKundengruppe = isset($_SESSION['Kundengruppe']->kKundengruppe)
            ? Session\Frontend::getCustomerGroup()->getID()
            : null;
        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
            $this->kKundengruppe = (int)$_SESSION['Kunde']->kKundengruppe;
        }
    }

    /**
     * @param int  $kInitial
     * @param bool $fetch_all
     * @return $this
     */
    public function init($kInitial, $fetch_all = false): self
    {
        $imageMap = $this->fetch($kInitial, $fetch_all);
        if (\is_object($imageMap)) {
            Shop::Smarty()->assign('oImageMap', $imageMap);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function fetchAll(): array
    {
        return Shop::Container()->getDB()->query(
            'SELECT *, IF((CURDATE() >= DATE(vDatum)) AND (CURDATE() <= DATE(bDatum) OR bDatum = 0), 1, 0) AS active 
                FROM timagemap
                ORDER BY bDatum DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int  $kImageMap
     * @param bool $fetchAll
     * @param bool $fill
     * @return stdClass|bool
     */
    public function fetch(int $kImageMap, bool $fetchAll = false, bool $fill = true)
    {
        $db   = Shop::Container()->getDB();
        $cSQL = 'SELECT *
                    FROM timagemap
                    WHERE kImageMap = ' . $kImageMap;
        if (!$fetchAll) {
            $cSQL .= ' AND (CURDATE() >= DATE(vDatum)) AND (CURDATE() <= DATE(bDatum) OR bDatum = 0)';
        }
        $imageMap = $db->query($cSQL, ReturnType::SINGLE_OBJECT);
        if (!\is_object($imageMap)) {
            return false;
        }
        $imageMap->oArea_arr = $db->selectAll(
            'timagemaparea',
            'kImageMap',
            (int)$imageMap->kImageMap
        );
        $imageMap->cBildPfad = Shop::getImageBaseURL() . \PFAD_IMAGEMAP . $imageMap->cBildPfad;
        $parsed              = \parse_url($imageMap->cBildPfad);
        $imageMap->cBild     = \mb_substr($parsed['path'], \mb_strrpos($parsed['path'], '/') + 1);
        $defaultOptions      = Artikel::getDefaultOptions();

        [$imageMap->fWidth, $imageMap->fHeight] = \getimagesize(\PFAD_ROOT . \PFAD_IMAGEMAP . $imageMap->cBildPfad);
        foreach ($imageMap->oArea_arr as &$area) {
            $area->oCoords = new stdClass();
            $aMap          = \explode(',', $area->cCoords);
            if (\count($aMap) === 4) {
                $area->oCoords->x = (int)$aMap[0];
                $area->oCoords->y = (int)$aMap[1];
                $area->oCoords->w = (int)$aMap[2];
                $area->oCoords->h = (int)$aMap[3];
            }

            $area->oArtikel = null;
            if ((int)$area->kArtikel > 0) {
                $area->oArtikel = new Artikel();
                if ($fill === true) {
                    $area->oArtikel->fuelleArtikel(
                        $area->kArtikel,
                        $defaultOptions,
                        $this->kKundengruppe ?? 0,
                        $this->kSprache
                    );
                } else {
                    $area->oArtikel->kArtikel = $area->kArtikel;
                    $area->oArtikel->cName    = $db->select(
                        'tartikel',
                        'kArtikel',
                        $area->kArtikel,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'cName'
                    )->cName;
                }
                if (\mb_strlen($area->cTitel) === 0) {
                    $area->cTitel = $area->oArtikel->cName;
                }
                if (\mb_strlen($area->cUrl) === 0) {
                    $area->cUrl = $area->oArtikel->cURL;
                }
                if (\mb_strlen($area->cBeschreibung) === 0) {
                    $area->cBeschreibung = $area->oArtikel->cKurzBeschreibung;
                }
            }
        }

        return $imageMap;
    }

    /**
     * @param string $cTitel
     * @param string $cBildPfad
     * @param string $vDatum
     * @param string $bDatum
     * @return int
     */
    public function save($cTitel, $cBildPfad, $vDatum, $bDatum): int
    {
        $ins            = new stdClass();
        $ins->cTitel    = Shop::Container()->getDB()->escape($cTitel);
        $ins->cBildPfad = Shop::Container()->getDB()->escape($cBildPfad);
        $ins->vDatum    = $vDatum;
        $ins->bDatum    = $bDatum;

        return Shop::Container()->getDB()->insert('timagemap', $ins);
    }

    /**
     * @param int    $kImageMap
     * @param string $cTitel
     * @param string $cBildPfad
     * @param string $vDatum
     * @param string $bDatum
     * @return bool
     */
    public function update(int $kImageMap, $cTitel, $cBildPfad, $vDatum, $bDatum): bool
    {
        if (empty($vDatum)) {
            $vDatum = '_DBNULL_';
        }
        if (empty($bDatum)) {
            $bDatum = '_DBNULL_';
        }
        $upd            = new stdClass();
        $upd->cTitel    = $cTitel;
        $upd->cBildPfad = $cBildPfad;
        $upd->vDatum    = $vDatum;
        $upd->bDatum    = $bDatum;

        return Shop::Container()->getDB()->update('timagemap', 'kImageMap', $kImageMap, $upd) >= 0;
    }

    /**
     * @param int $kImageMap
     * @return bool
     */
    public function delete(int $kImageMap): bool
    {
        return Shop::Container()->getDB()->delete('timagemap', 'kImageMap', $kImageMap) >= 0;
    }

    /**
     * @param stdClass $oData
     */
    public function saveAreas($oData): void
    {
        $db = Shop::Container()->getDB();
        $db->delete('timagemaparea', 'kImageMap', (int)$oData->kImageMap);
        foreach ($oData->oArea_arr as $area) {
            $ins                = new stdClass();
            $ins->kImageMap     = $area->kImageMap;
            $ins->kArtikel      = $area->kArtikel;
            $ins->cStyle        = $area->cStyle;
            $ins->cTitel        = $area->cTitel;
            $ins->cUrl          = $area->cUrl;
            $ins->cBeschreibung = $area->cBeschreibung;
            $ins->cCoords       = $area->oCoords->x . ',' .
                $area->oCoords->y . ',' .
                $area->oCoords->w . ',' .
                $area->oCoords->h;

            $db->insert('timagemaparea', $ins);
        }
    }
}
