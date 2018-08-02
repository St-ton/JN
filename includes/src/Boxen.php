<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Boxen
 * @deprecated since 5.0.0
 */
class Boxen
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'boxes'     => 'BoxList',
        'boxConfig' => 'Config'
    ];

    /**
     * @var array
     */
    public $boxConfig = [];

    /**
     * @var string
     */
    public $lagerFilter = '';

    /**
     * @var string
     */
    public $cVaterSQL = ' AND tartikel.kVaterArtikel = 0';

    /**
     * unrendered box template file name + data
     *
     * @var array
     */
    public $rawData = [];

    /**
     * @var array
     */
    public $visibility;

    /**
     * @var Boxen
     */
    private static $_instance;

    /**
     * @var \Services\JTL\BoxService
     */
    private $boxService;

    /**
     * @return Boxen
     * @deprecated since 5.0.0
     */
    public static function getInstance(): self
    {
        trigger_error(__CLASS__ . ' is deprecated.', E_USER_DEPRECATED);
        return self::$_instance ?? new self();
    }

    /**
     * @deprecated since 5.0.0
     */
    public function __construct()
    {
        $this->boxService = Shop::Container()->getBoxService();
        self::$_instance  = $this;
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getBoxList(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return $this->boxService->getBoxes();
    }

    /**
     * @param array $boxList
     */
    public function setBoxList(array $boxList)
    {
        trigger_error(__CLASS__ . ': setting boxes here is not possible anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int $page
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeVorlagen(int $page = -1): array
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return [];
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @return mixed
     * @deprecated since 5.0.0
     */
    public function gibBoxInhalt(int $kBox, string $cISO = '')
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return null;
    }

    /**
     * @param int  $page
     * @param bool $active
     * @param bool $visible
     * @param bool $force
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeBoxen(int $page = 0, bool $active = true, bool $visible = false, bool $force = false): array
    {
        trigger_error(
            __METHOD__ . ' is deprecated. Use ' . get_class($this->boxService) . ' instead',
            E_USER_DEPRECATED
        );
        if (count($this->boxService->getBoxes()) === 0) {
            return $this->boxService->buildList($page, $active, $visible);
        }

        return $this->boxService->getBoxes();
    }

    /**
     * generate array of currently active boxes
     *
     * @param int  $page
     * @param bool $active
     * @param bool $visible
     * @return $this
     * @deprecated since 5.0.0
     */
    public function build(int $page = 0, bool $active = true, bool $visible = false): self
    {
        trigger_error(
            __METHOD__ . ' is deprecated. Use ' . get_class($this->boxService) . ' instead',
            E_USER_DEPRECATED
        );
        if (count($this->boxService->getBoxes()) === 0) {
            $this->boxService->buildList($page, $active, $visible);
        }

        return $this;
    }

    /**
     * read linkgroup array and search for specific ID
     *
     * @param int $id
     * @return \Link\LinkGroupInterface|null
     * @deprecated since 5.0.0
     */
    private function getLinkGroupByID(int $id)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return Shop::Container()->getLinkService()->getLinkGroupByID($id);
    }

    /**
     * supply data for specific box types
     *
     * @param int    $kBoxVorlage
     * @param object $oBox
     * @return mixed
     * @deprecated since 5.0.0
     */
    public function prepareBox(int $kBoxVorlage, $oBox)
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return null;
    }

    /**
     * @param string $filename_cache
     * @param int    $timeout
     * @return bool
     * @deprecated since 5.0.0
     */
    private function cachecheck(string $filename_cache, int $timeout = 10800): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return array
     * @throws Exception
     * @throws SmartyException
     * @deprecated since 5.0.0
     */
    public function render(): array
    {
        trigger_error(
            __METHOD__ . ' is deprecated. Use ' . get_class($this->boxService) . ' instead',
            E_USER_DEPRECATED
        );

        return $this->boxService->render($this->boxService->getBoxes());
    }

    /**
     * @param int $kArtikel
     * @param int $nMaxAnzahl
     * @deprecated since 5.0.0
     */
    public function addRecentlyViewed(int $kArtikel, $nMaxAnzahl = null)
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 5.0.0
     * @param int $kSeite
     * @return string
     */
    public function mappekSeite(int $kSeite): string
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return 'Unbekannt';
    }

    /**
     * @param int  $page
     * @param bool $bGlobal
     * @return array|bool
     * @deprecated since 5.0.0
     */
    public function holeBoxAnzeige(int $page, bool $bGlobal = true)
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @param int      $page
     * @param string   $ePosition
     * @param bool|int $bAnzeigen
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzeBoxAnzeige(int $page, string $ePosition, $bAnzeigen): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @param int $kBoxvorlage
     * @return stdClass|null
     * @deprecated since 5.0.0
     */
    public function holeVorlage(int $kBoxvorlage)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return Shop::Container()->getDB()->select('tboxvorlage', 'kBoxvorlage', $kBoxvorlage);
    }

    /**
     * @param string $ePosition
     * @return array
     * @deprecated since 5.0.0
     */
    public function holeContainer(string $ePosition): array
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int    $kBoxvorlage
     * @param int    $page
     * @param string $ePosition
     * @param int    $kContainer
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzeBox(int $kBoxvorlage, int $page, string $ePosition = 'left', int $kContainer = 0): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @param int $kBox
     * @return stdClass
     * @deprecated since 5.0.0
     */
    public function holeBox(int $kBox): stdClass
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        $oBox = Shop::Container()->getDB()->query(
            "SELECT tboxen.kBox, tboxen.kBoxvorlage, tboxen.kCustomID, tboxen.cTitel, tboxen.ePosition,
                tboxvorlage.eTyp, tboxvorlage.cName, tboxvorlage.cVerfuegbar, tboxvorlage.cTemplate
                FROM tboxen
                LEFT JOIN tboxvorlage 
                    ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE kBox = " . $kBox,
            \DB\ReturnType::SINGLE_OBJECT
        );

        $oBox->oSprache_arr      = ($oBox && ($oBox->eTyp === 'text' || $oBox->eTyp === 'catbox'))
            ? $this->gibBoxInhalt($kBox)
            : [];
        $oBox->kBox              = (int)$oBox->kBox;
        $oBox->kBoxvorlage       = (int)$oBox->kBoxvorlage;
        $oBox->supportsRevisions = $oBox->kBoxvorlage === 30 || $oBox->kBoxvorlage === 31; // only "Eigene Box"

        return $oBox;
    }

    /**
     * @param int    $kBox
     * @param string $cTitel
     * @param int    $kCustomID
     * @return bool
     * @deprecated since 5.0.0
     */
    public function bearbeiteBox(int $kBox, $cTitel, int $kCustomID = 0): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int    $kBox
     * @param string $cISO
     * @param string $cTitel
     * @param string $cInhalt
     * @return bool
     * @deprecated since 5.0.0
     */
    public function bearbeiteBoxSprache(int $kBox, string $cISO, string $cTitel, string $cInhalt): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int    $page
     * @param string $ePosition
     * @param int    $kContainer
     * @return int
     * @deprecated since 5.0.0
     */
    public function letzteSortierID(int $page, string $ePosition = 'left', int $kContainer = 0): int
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore..', E_USER_DEPRECATED);
    }

    /**
     * @param int          $kBox
     * @param int          $kSeite
     * @param string|array $cFilter
     * @return int
     * @deprecated since 5.0.0
     */
    public function filterBoxVisibility(int $kBox, int $kSeite, $cFilter = ''): int
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore..', E_USER_DEPRECATED);
    }

    /**
     * @param int      $kBox
     * @param int      $page
     * @param int      $nSort
     * @param bool|int $active
     * @return bool
     * @deprecated since 5.0.0
     */
    public function sortBox(int $kBox, int $page, int $nSort, $active = true): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int      $kBox
     * @param int      $page
     * @param bool|int $active
     * @return bool
     * @deprecated since 5.0.0
     */
    public function aktiviereBox(int $kBox, int $page, $active = true): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
    }

    /**
     * @param int $kBox
     * @return bool
     * @deprecated since 5.0.0
     */
    public function loescheBox(int $kBox): bool
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function gibLinkGruppen(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        return Shop::Container()->getDB()->query('SELECT * FROM tlinkgruppe', \DB\ReturnType::ARRAY_OF_OBJECTS);
    }

    /**
     * @param int $kBoxvorlage
     * @return bool
     * @deprecated since 5.0.0
     */
    public function isVisible(int $kBoxvorlage): bool
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        foreach ($this->boxes as $_position => $_boxes) {
            foreach ($_boxes as $_box) {
                if ((int)$_box->kBoxvorlage === $kBoxvorlage) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Filter\ProductFilter $pf
     * @param \Filter\SearchResults $sr
     * @return bool
     * @deprecated since 5.0.0
     */
    public function gibBoxenFilterNach(\Filter\ProductFilter $pf, \Filter\SearchResults $sr): bool
    {
        return $this->boxService->showBoxes($pf, $sr);
    }

    /**
     * get raw data from visible boxes
     * to allow custom renderes
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function getRawData(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return $this->boxService->getRawData();
    }

    /**
     * compatibility layer for gibBoxen() which returns unrendered content
     *
     * @return array
     * @deprecated since 5.0.0
     */
    public function compatGet(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return [];
    }

    /**
     * special json string for sidebar clouds
     *
     * @param array  $c
     * @param string $speed
     * @param string $opacity
     * @param bool   $color
     * @param bool   $hover
     * @return string
     * @deprecated since 5.0.0
     */
    public static function gibJSONString($c, $speed = '1', $opacity = '0.2', $color = false, $hover = false): string {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return '';
    }

    /**
     * get classname for sidebar panels
     *
     * @return string
     * @deprecated since 5.0.0
     */
    public function getClass(): string
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return '';
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getInvisibleBoxes(): array
    {
        trigger_error(__METHOD__ . ' is deprecated and does not work anymore.', E_USER_DEPRECATED);
        return [];
    }

    /**
     * @return array
     * @deprecated since 5.0.0
     */
    public function getValidPageTypes(): array
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
        return [
            PAGE_UNBEKANNT,
            PAGE_ARTIKEL,
            PAGE_ARTIKELLISTE,
            PAGE_WARENKORB,
            PAGE_MEINKONTO,
            PAGE_KONTAKT,
            PAGE_UMFRAGE,
            PAGE_NEWS,
            PAGE_NEWSLETTER,
            PAGE_LOGIN,
            PAGE_REGISTRIERUNG,
            PAGE_BESTELLVORGANG,
            PAGE_BEWERTUNG,
            PAGE_DRUCKANSICHT,
            PAGE_PASSWORTVERGESSEN,
            PAGE_WARTUNG,
            PAGE_WUNSCHLISTE,
            PAGE_VERGLEICHSLISTE,
            PAGE_STARTSEITE,
            PAGE_VERSAND,
            PAGE_AGB,
            PAGE_DATENSCHUTZ,
            PAGE_TAGGING,
            PAGE_LIVESUCHE,
            PAGE_HERSTELLER,
            PAGE_SITEMAP,
            PAGE_GRATISGESCHENK,
            PAGE_WRB,
            PAGE_PLUGIN,
            PAGE_NEWSLETTERARCHIV,
            PAGE_NEWSARCHIV,
            PAGE_EIGENE,
            PAGE_AUSWAHLASSISTENT,
            PAGE_BESTELLABSCHLUSS
        ];
    }
}
