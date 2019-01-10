<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class ImageMap
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
            ? \Session\Session::getCustomerGroup()->getID()
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
    public function init($kInitial, $fetch_all = false)
    {
        $oImageMap = $this->fetch($kInitial, $fetch_all);
        if (is_object($oImageMap)) {
            Shop::Smarty()->assign('oImageMap', $oImageMap);
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
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
        $oImageMap = $db->query($cSQL, \DB\ReturnType::SINGLE_OBJECT);
        if (!is_object($oImageMap)) {
            return false;
        }

        $oImageMap->oArea_arr = $db->selectAll(
            'timagemaparea',
            'kImageMap',
            (int)$oImageMap->kImageMap
        );
        $cBildPfad            = PFAD_ROOT . PFAD_IMAGEMAP . $oImageMap->cBildPfad;
        $oImageMap->cBildPfad = Shop::getImageBaseURL() . PFAD_IMAGEMAP . $oImageMap->cBildPfad;
        $cParse_arr           = parse_url($oImageMap->cBildPfad);
        $oImageMap->cBild     = substr($cParse_arr['path'], strrpos($cParse_arr['path'], '/') + 1);
        [$width, $height]     = getimagesize($cBildPfad);
        $oImageMap->fWidth    = $width;
        $oImageMap->fHeight   = $height;
        $defaultOptions       = Artikel::getDefaultOptions();

        foreach ($oImageMap->oArea_arr as &$oArea) {
            $oArea->oCoords = new stdClass();
            $aMap           = explode(',', $oArea->cCoords);
            if (count($aMap) === 4) {
                $oArea->oCoords->x = (int)$aMap[0];
                $oArea->oCoords->y = (int)$aMap[1];
                $oArea->oCoords->w = (int)$aMap[2];
                $oArea->oCoords->h = (int)$aMap[3];
            }

            $oArea->oArtikel = null;
            if ((int)$oArea->kArtikel > 0) {
                $oArea->oArtikel = new Artikel();
                if ($fill === true) {
                    $oArea->oArtikel->fuelleArtikel(
                        $oArea->kArtikel,
                        $defaultOptions,
                        $this->kKundengruppe ?? 0,
                        $this->kSprache
                    );
                } else {
                    $oArea->oArtikel->kArtikel = $oArea->kArtikel;
                    $oArea->oArtikel->cName    = $db->select(
                        'tartikel',
                        'kArtikel',
                        $oArea->kArtikel,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'cName'
                    )->cName;
                }
                if (strlen($oArea->cTitel) === 0) {
                    $oArea->cTitel = $oArea->oArtikel->cName;
                }
                if (strlen($oArea->cUrl) === 0) {
                    $oArea->cUrl = $oArea->oArtikel->cURL;
                }
                if (strlen($oArea->cBeschreibung) === 0) {
                    $oArea->cBeschreibung = $oArea->oArtikel->cKurzBeschreibung;
                }
            }
        }

        return $oImageMap;
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
        $oData            = new stdClass();
        $oData->cTitel    = Shop::Container()->getDB()->escape($cTitel);
        $oData->cBildPfad = Shop::Container()->getDB()->escape($cBildPfad);
        $oData->vDatum    = $vDatum;
        $oData->bDatum    = $bDatum;

        return Shop::Container()->getDB()->insert('timagemap', $oData);
    }

    /**
     * @param int    $kImageMap
     * @param string $cTitel
     * @param string $cBildPfad
     * @param string $vDatum
     * @param string $bDatum
     * @return bool
     */
    public function update(int $kImageMap, $cTitel, $cBildPfad, $vDatum, $bDatum)
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
            $oTmp                = new stdClass();
            $oTmp->kImageMap     = $area->kImageMap;
            $oTmp->kArtikel      = $area->kArtikel;
            $oTmp->cStyle        = $area->cStyle;
            $oTmp->cTitel        = $area->cTitel;
            $oTmp->cUrl          = $area->cUrl;
            $oTmp->cBeschreibung = $area->cBeschreibung;
            $oTmp->cCoords       = "{$area->oCoords->x},{$area->oCoords->y},{$area->oCoords->w},{$area->oCoords->h}";

            $db->insert('timagemaparea', $oTmp);
        }
    }
}
