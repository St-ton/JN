<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Shop;

/**
 * Class MerkmalWert
 * @package JTL\Catalog\Product
 */
class MerkmalWert
{
    use MultiSizeImage;

    /**
     * @var int
     */
    public int $kSprache = 0;

    /**
     * @var int
     */
    public int $kMerkmalWert = 0;

    /**
     * @var int
     */
    public int $kMerkmal = 0;

    /**
     * @var int
     */
    public int $nSort = 0;

    /**
     * @var string|null
     */
    public ?string $cWert;

    /**
     * @var string|null
     */
    public ?string $cMetaKeywords = null;

    /**
     * @var string|null
     */
    public ?string $cMetaDescription = null;

    /**
     * @var string|null
     */
    public ?string $cMetaTitle = null;

    /**
     * @var string|null
     */
    public ?string $cBeschreibung = null;

    /**
     * @var string|null
     */
    public ?string $cSeo = null;

    /**
     * @var string|null
     */
    public ?string $cURL = null;

    /**
     * @var string|null
     */
    public ?string $cURLFull = null;

    /**
     * @var string|null
     */
    public ?string $cBildpfad = null;

    /**
     * @var string
     */
    public string $cBildpfadKlein = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;

    /**
     * @var int
     */
    public int $nBildKleinVorhanden = 0;

    /**
     * @var string
     */
    public string $cBildpfadNormal = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;

    /**
     * @var int
     */
    public int $nBildNormalVorhanden = 0;

    /**
     * @var string
     */
    public string $cBildURLKlein;

    /**
     * @var string
     */
    public string $cBildURLNormal;

    /**
     * MerkmalWert constructor.
     * @param int $id
     * @param int $languageID
     */
    public function __construct(int $id = 0, int $languageID = 0)
    {
        $this->setImageType(Image::TYPE_CHARACTERISTIC_VALUE);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID);
        }
    }

    /**
     * @param int $id
     * @param int $languageID
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0): self
    {
        $languageID = $languageID === 0 ? Shop::getLanguageID() : $languageID;
        $cacheID    = 'mmw_' . $id . '_' . $languageID;
        if (Shop::has($cacheID)) {
            foreach (\get_object_vars(Shop::get($cacheID)) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        $defaultLanguageID = LanguageHelper::getDefaultLanguage()->getId();
        if ($languageID !== $defaultLanguageID) {
            $selectSQL = 'COALESCE(fremdSprache.kSprache, standardSprache.kSprache) AS kSprache, 
                        COALESCE(fremdSprache.cWert, standardSprache.cWert) AS cWert,
                        COALESCE(fremdSprache.cMetaTitle, standardSprache.cMetaTitle) AS cMetaTitle, 
                        COALESCE(fremdSprache.cMetaKeywords, standardSprache.cMetaKeywords) AS cMetaKeywords,
                        COALESCE(fremdSprache.cMetaDescription, standardSprache.cMetaDescription) AS cMetaDescription, 
                        COALESCE(fremdSprache.cBeschreibung, standardSprache.cBeschreibung) AS cBeschreibung,
                        COALESCE(fremdSprache.cSeo, standardSprache.cSeo) AS cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache AS standardSprache 
                            ON standardSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND standardSprache.kSprache = ' . $defaultLanguageID . '
                        LEFT JOIN tmerkmalwertsprache AS fremdSprache 
                            ON fremdSprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND fremdSprache.kSprache = :lid';
        } else {
            $selectSQL = 'tmerkmalwertsprache.kSprache, tmerkmalwertsprache.cWert, tmerkmalwertsprache.cMetaTitle,
                        tmerkmalwertsprache.cMetaKeywords, tmerkmalwertsprache.cMetaDescription,
                        tmerkmalwertsprache.cBeschreibung, tmerkmalwertsprache.cSeo';
            $joinSQL   = 'INNER JOIN tmerkmalwertsprache ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                            AND tmerkmalwertsprache.kSprache = :lid';
        }
        $data = Shop::Container()->getDB()->getSingleObject(
            'SELECT tmerkmalwert.*, ' . $selectSQL . '
                FROM tmerkmalwert ' . $joinSQL . '
                WHERE tmerkmalwert.kMerkmalWert = :mid',
            ['mid' => $id, 'lid' => $languageID]
        );
        if ($data !== null && $data->kMerkmalWert > 0) {
            $this->kMerkmalWert     = (int)$data->kMerkmalWert;
            $this->kMerkmal         = (int)$data->kMerkmal;
            $this->nSort            = (int)$data->nSort;
            $this->kSprache         = (int)$data->kSprache;
            $this->cBildpfad        = $data->cBildpfad;
            $this->cWert            = $data->cWert;
            $this->cMetaTitle       = $data->cMetaTitle;
            $this->cMetaDescription = $data->cMetaDescription;
            $this->cMetaKeywords    = $data->cMetaKeywords;
            $this->cBeschreibung    = $data->cBeschreibung;
            $this->cSeo             = $data->cSeo;
            $this->cURL             = URL::buildURL($this, \URLART_MERKMAL);
            $this->cURLFull         = URL::buildURL($this, \URLART_MERKMAL, true);
            \executeHook(\HOOK_MERKMALWERT_CLASS_LOADFROMDB, ['oMerkmalWert' => &$this]);
            if ($this->cBildpfad !== null && $this->cBildpfad !== '') {
                if (\file_exists(\PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad)) {
                    $this->cBildpfadKlein      = \PFAD_MERKMALWERTBILDER_KLEIN . $this->cBildpfad;
                    $this->nBildKleinVorhanden = 1;
                }
                if (\file_exists(\PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad)) {
                    $this->cBildpfadNormal      = \PFAD_MERKMALWERTBILDER_NORMAL . $this->cBildpfad;
                    $this->nBildNormalVorhanden = 1;
                }
                $this->generateAllImageSizes(true, 1, $this->cBildpfad);
            }
        }
        $imageBaseURL = Shop::getImageBaseURL();

        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;
        Shop::set($cacheID, $this);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getID()
    {
        return $this->kMerkmalWert;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->cWert;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->cWert = $value;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->kSprache;
    }

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID): void
    {
        $this->kSprache = $languageID;
    }

    /**
     * @return int
     */
    public function getCharacteristicID(): int
    {
        return $this->kMerkmal;
    }

    /**
     * @param int $id
     */
    public function setCharacteristicID(int $id): void
    {
        $this->kMerkmal = $id;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->nSort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->nSort = $sort;
    }

    /**
     * @return string|null
     */
    public function getMetaKeywords(): ?string
    {
        return $this->cMetaKeywords;
    }

    /**
     * @param string|null $metaKeywords
     */
    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->cMetaKeywords = $metaKeywords;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->cMetaDescription;
    }

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->cMetaDescription = $metaDescription;
    }

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->cMetaTitle;
    }

    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->cMetaTitle = $metaTitle;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->cBeschreibung;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->cBeschreibung = $description;
    }

    /**
     * @return string|null
     */
    public function getSeo(): ?string
    {
        return $this->cSeo;
    }

    /**
     * @param string|null $seo
     */
    public function setSeo(?string $seo): void
    {
        $this->cSeo = $seo;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->cURL;
    }

    /**
     * @param string|null $url
     */
    public function setURL(?string $url): void
    {
        $this->cURL = $url;
    }

    /**
     * @return string|null
     */
    public function getURLFull(): ?string
    {
        return $this->cURLFull;
    }

    /**
     * @param string|null $url
     */
    public function seCURLFull(?string $url): void
    {
        $this->cURLFull = $url;
    }

    /**
     * @return string|null
     */
    public function getImagePath(): ?string
    {
        return $this->cBildpfad;
    }

    /**
     * @param string|null $path
     */
    public function setImagePath(?string $path): void
    {
        $this->cBildpfad = $path;
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
     * @return bool
     */
    public function hasSmallImage(): bool
    {
        return $this->nBildKleinVorhanden === 1;
    }

    /**
     * @param bool $has
     */
    public function setHasSmallImage(bool $has): void
    {
        $this->nBildKleinVorhanden = (int)$has;
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
     * @return bool
     */
    public function hasNormalImage(): bool
    {
        return $this->nBildNormalVorhanden === 1;
    }

    /**
     * @param bool $has
     */
    public function setHasNormalImage(bool $has): void
    {
        $this->nBildNormalVorhanden = (int)$has;
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
}
