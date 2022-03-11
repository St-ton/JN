<?php declare(strict_types=1);

namespace JTL\Catalog;

use JTL\DB\SqlObject;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Hersteller
 * @package JTL\Catalog
 */
class Hersteller
{
    use MultiSizeImage;
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    public int $kHersteller = 0;

    /**
     * @var string
     */
    public string $cName = '';

    /**
     * @var string
     */
    public string $cSeo = '';

    /**
     * @var string
     */
    public string $originalSeo = '';

    /**
     * @var string
     */
    public string $cMetaTitle = '';

    /**
     * @var string
     */
    public string $cMetaKeywords = '';

    /**
     * @var string
     */
    public string $cMetaDescription = '';

    /**
     * @var string
     */
    public string $cBeschreibung = '';

    /**
     * @var string
     */
    public string $cBildpfad = '';

    /**
     * @var int
     */
    public int $nSortNr = 0;

    /**
     * @var string
     */
    public string $cURL = '';

    /**
     * @var string[]
     */
    protected static array $mapping = [
        'cURL'             => 'URL',
        'cURLFull'         => 'URL',
        'nSortNr'          => 'SortNo',
        'cBildpfad'        => 'ImagePath',
        'cBeschreibung'    => 'Description',
        'kHersteller'      => 'ID',
        'cName'            => 'Name',
        'cMetaTitle'       => 'MetaTitle',
        'cMetaKeywords'    => 'MetaKeywords',
        'cMetaDescription' => 'MetaDescription',
        'cSeo'             => 'Seo',
        'originalSeo'      => 'OriginalSeo',
        'cHomepage'        => 'Homepage'
    ];

    /**
     * @var string
     * @deprecated since 5.0.0
     */
    public string $cBildpfadKlein = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;

    /**
     * @var string
     * @deprecated since 5.0.0
     */
    public string $cBildpfadNormal = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;

    /**
     * @var string
     * @deprecated since 5.0.0
     */
    public string $cBildURLKlein = '';

    /**
     * @var string
     * @deprecated since 5.0.0
     */
    public string $cBildURLNormal = '';

    /**
     * @var string
     */
    public string $cHomepage = '';

    /**
     * Hersteller constructor.
     *
     * @param int  $id
     * @param int  $languageID
     * @param bool $noCache - set to true to avoid caching
     */
    public function __construct(int $id = 0, int $languageID = 0, bool $noCache = false)
    {
        $this->setImageType(Image::TYPE_MANUFACTURER);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID, $noCache);
        }
    }

    /**
     * @param stdClass $obj
     * @return $this
     */
    public function loadFromObject(stdClass $obj): self
    {
        $this->kHersteller      = (int)$obj->kHersteller;
        $this->nSortNr          = (int)$obj->nSortNr;
        $this->cName            = $obj->cName ?? '';
        $this->cBildpfad        = $obj->cBildpfad ?? '';
        $this->cMetaTitle       = $obj->cBildpfad ?? '';
        $this->cMetaKeywords    = $obj->cMetaKeywords ?? '';
        $this->cMetaDescription = $obj->cMetaDescription ?? '';
        $this->cBeschreibung    = $obj->cBeschreibung ?? '';
        $this->cSeo             = $obj->cSeo ?? '';
        $this->originalSeo      = $obj->originalSeo ?? '';
        $homepage               = Text::filterURL($obj->cHomepage ?? '', true, true);
        $this->cHomepage        = $homepage === false ? '' : $homepage;
        $this->loadImages();

        return $this;
    }

    /**
     * @param int  $id
     * @param int  $languageID
     * @param bool $noCache
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0, bool $noCache = false)
    {
        // noCache param to avoid problem with de-serialization of class properties with jtl search
        $languageID = $languageID > 0 ? $languageID : Shop::getLanguageID();
        if ($languageID === 0) {
            $languageID = LanguageHelper::getDefaultLanguage()->getId();
        }
        $cacheID   = 'manuf_' . $id . '_' . $languageID . Shop::Container()->getCache()->getBaseID();
        $cacheTags = [\CACHING_GROUP_MANUFACTURER];
        $cached    = true;
        if ($noCache === true || ($manufacturer = Shop::Container()->getCache()->get($cacheID)) === false) {
            $manufacturer = Shop::Container()->getDB()->getSingleObject(
                "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                    thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                    therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                    tseo.cSeo, thersteller.cSeo AS originalSeo
                    FROM thersteller
                    LEFT JOIN therstellersprache 
                        ON therstellersprache.kHersteller = thersteller.kHersteller
                        AND therstellersprache.kSprache = :langID
                    LEFT JOIN tseo 
                        ON tseo.kKey = thersteller.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = :langID
                    WHERE thersteller.kHersteller = :manfID
                        AND thersteller.nAktiv = 1",
                [
                    'langID' => $languageID,
                    'manfID' => $id
                ]
            );
            $cached       = false;
            \executeHook(\HOOK_HERSTELLER_CLASS_LOADFROMDB, [
                'oHersteller' => &$manufacturer,
                'cached'      => false,
                'cacheTags'   => &$cacheTags
            ]);
            Shop::Container()->getCache()->set($cacheID, $manufacturer, $cacheTags);
        }
        if ($cached === true) {
            \executeHook(\HOOK_HERSTELLER_CLASS_LOADFROMDB, [
                'oHersteller' => &$manufacturer,
                'cached'      => true,
                'cacheTags'   => &$cacheTags
            ]);
        }
        if ($manufacturer !== null) {
            $this->loadFromObject($manufacturer);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadImages(): self
    {
        $imageBaseURL          = Shop::getImageBaseURL();
        $this->cBildpfadKlein  = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;
        $this->cBildpfadNormal = \BILD_KEIN_HERSTELLERBILD_VORHANDEN;
        if ($this->kHersteller > 0) {
            $this->cURL = $this->cSeo !== ''
                ? Shop::getURL() . '/' . $this->cSeo
                : Shop::getURL() . '/?h=' . $this->kHersteller;
        }
        if ($this->cBildpfad !== '') {
            $this->cBildpfadKlein  = \PFAD_HERSTELLERBILDER_KLEIN . $this->cBildpfad;
            $this->cBildpfadNormal = \PFAD_HERSTELLERBILDER_NORMAL . $this->cBildpfad;
            $this->generateAllImageSizes(true, 1, $this->cBildpfad);
        }
        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;

        return $this;
    }

    /**
     * @param bool $productLookup
     * @param int  $languageID
     * @param int  $customerGroupID
     * @return self[]
     */
    public static function getAll(bool $productLookup = true, int $languageID = 0, int $customerGroupID = 0): array
    {
        $customerGroupID = $customerGroupID ?: Frontend::getCustomerGroup()->getID();
        $languageID      = $languageID ?: Shop::getLanguageID();
        $sql             = new SqlObject();
        $sql->setWhere('thersteller.nAktiv = 1');
        $sql->addParam(':lid', $languageID);
        if ($productLookup) {
            $sql->setWhere('EXISTS (
                            SELECT 1
                            FROM tartikel
                            WHERE tartikel.kHersteller = thersteller.kHersteller
                                ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . '
                                AND NOT EXISTS (
                                SELECT 1 FROM tartikelsichtbarkeit
                                WHERE tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                                    AND tartikelsichtbarkeit.kKundengruppe = :cgid))');
            $sql->addParam(':cgid', $customerGroupID);
        }

        return Shop::Container()->getDB()->getCollection(
            "SELECT thersteller.kHersteller, thersteller.cName, thersteller.cHomepage, thersteller.nSortNr, 
                thersteller.cBildpfad, therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                therstellersprache.cMetaDescription, therstellersprache.cBeschreibung,
                tseo.cSeo, thersteller.cSeo AS originalSeo
                FROM thersteller
                LEFT JOIN therstellersprache 
                    ON therstellersprache.kHersteller = thersteller.kHersteller
                    AND therstellersprache.kSprache = :lid
                LEFT JOIN tseo 
                    ON tseo.kKey = thersteller.kHersteller
                    AND tseo.cKey = 'kHersteller'
                    AND tseo.kSprache = :lid 
                WHERE " . $sql->getWhere() . '
                ORDER BY thersteller.nSortNr, thersteller.cName',
            $sql->getParams()
        )->map(static function (stdClass $item) {
            $manufacturer = new self();
            $manufacturer->loadFromObject($item);

            return $manufacturer;
        })->toArray();
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->kHersteller;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->kHersteller = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->cName;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->cName = $name;
    }

    /**
     * @return string
     */
    public function getSeo(): string
    {
        return $this->cSeo;
    }

    /**
     * @param string $seo
     */
    public function setSeo(string $seo): void
    {
        $this->cSeo = $seo;
    }

    /**
     * @return string
     */
    public function getOriginalSeo(): string
    {
        return $this->originalSeo;
    }

    /**
     * @param string $originalSeo
     */
    public function setOriginalSeo(string $originalSeo): void
    {
        $this->originalSeo = $originalSeo;
    }

    /**
     * @return string
     */
    public function getMetaTitle(): string
    {
        return $this->cMetaTitle;
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle(string $metaTitle): void
    {
        $this->cMetaTitle = $metaTitle;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->cMetaKeywords;
    }

    /**
     * @param string $metaKeywords
     */
    public function setCMetaKeywords(string $metaKeywords): void
    {
        $this->cMetaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->cMetaDescription;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription(string $metaDescription): void
    {
        $this->cMetaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getDesciption(): string
    {
        return $this->cBeschreibung;
    }

    /**
     * @param string $description
     */
    public function setDecription(string $description): void
    {
        $this->cBeschreibung = $description;
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->cBildpfad;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath(string $imagePath): void
    {
        $this->cBildpfad = $imagePath;
    }

    /**
     * @return int
     */
    public function getSortNo(): int
    {
        return $this->nSortNr;
    }

    /**
     * @param int $sortNo
     */
    public function setSortNo(int $sortNo): void
    {
        $this->nSortNr = $sortNo;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->cURL;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->cURL = $url;
    }

    /**
     * @return string
     */
    public function getImagePathSmall(): string
    {
        return $this->cBildpfadKlein;
    }

    /**
     * @param string $path
     */
    public function setImagePathSmall(string $path): void
    {
        $this->cBildpfadKlein = $path;
    }

    /**
     * @return string
     */
    public function getImagePathNormal(): string
    {
        return $this->cBildpfadNormal;
    }

    /**
     * @param string $path
     */
    public function setImagePathNormal(string $path): void
    {
        $this->cBildpfadNormal = $path;
    }

    /**
     * @return string
     */
    public function getImageURLSmall(): string
    {
        return $this->cBildURLKlein;
    }

    /**
     * @param string $url
     */
    public function setImageURLSmall(string $url): void
    {
        $this->cBildURLKlein = $url;
    }

    /**
     * @return string
     */
    public function getImageURLNormal(): string
    {
        return $this->cBildURLNormal;
    }

    /**
     * @param string $url
     */
    public function setImageURLNormal(string $url): void
    {
        $this->cBildURLNormal = $url;
    }

    /**
     * @return string
     */
    public function getHomepage(): string
    {
        return $this->cHomepage;
    }

    /**
     * @param string $url
     */
    public function setHomepage(string $url): void
    {
        $this->cHomepage = $url;
    }
}
