<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use JTL\Contracts\RoutableInterface;
use JTL\DB\DbInterface;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Router\RoutableTrait;
use JTL\Router\Router;
use JTL\Shop;
use function Functional\select;

/**
 * Class MerkmalWert
 * @package JTL\Catalog\Product
 */
class MerkmalWert implements RoutableInterface
{
    use MultiSizeImage;
    use MagicCompatibilityTrait;
    use RoutableTrait;

    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var int
     */
    public int $characteristicID = 0;

    /**
     * @var int
     */
    private int $sort = 0;

    /**
     * @var string[]
     */
    private array $metaTitles = [];

    /**
     * @var string[]
     */
    private array $metaKeywords = [];

    /**
     * @var string[]
     */
    private array $metaDescriptions = [];

    /**
     * @var string[]
     */
    private array $descriptions = [];

    /**
     * @var string
     */
    private string $imagePath = '';

    /**
     * @var string[]
     */
    private array $characteristicNames = [];

    /**
     * @var string[]
     */
    private array $values = [];

    /**
     * @var string
     * @deprecated since 5.2.0
     */
    public string $cBildpfadKlein = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;

    /**
     * @var int
     * @deprecated since 5.2.0
     */
    public int $nBildKleinVorhanden = 0;

    /**
     * @var string
     * @deprecated since 5.2.0
     */
    public string $cBildpfadNormal = \BILD_KEIN_MERKMALWERTBILD_VORHANDEN;

    /**
     * @var int
     * @deprecated since 5.2.0
     */
    public int $nBildNormalVorhanden = 0;

    /**
     * @var string
     * @deprecated since 5.2.0
     */
    public string $cBildURLKlein;

    /**
     * @var string
     * @deprecated since 5.2.0
     */
    public string $cBildURLNormal;

    /**
     * @var string[]
     */
    protected static array $mapping = [
        'cURL'             => 'URL',
        'cURLFull'         => 'URL',
        'nSort'            => 'Sort',
        'cBildpfad'        => 'ImagePath',
        'cBeschreibung'    => 'Description',
        'kSprache'         => 'LanguageID',
        'kMerkmal'         => 'CharacteristicID',
        'kMerkmalWert'     => 'ID',
        'cMetaTitle'       => 'MetaTitle',
        'cMetaKeywords'    => 'MetaKeywords',
        'cMetaDescription' => 'MetaDescription',
        'cSeo'             => 'Slug',
        'cWert'            => 'Value'
    ];

    /**
     * MerkmalWert constructor.
     * @param int              $id
     * @param int              $languageID
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, int $languageID = 0, private ?DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        $this->setImageType(Image::TYPE_CHARACTERISTIC_VALUE);
        $this->setRouteType(Router::TYPE_CHARACTERISTIC_VALUE);
        if ($id > 0) {
            $this->loadFromDB($id, $languageID);
        }
    }

    /**
     * @return void
     */
    public function __wakeup(): void
    {
        $this->initLanguageID();
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e): bool {
            return $e !== 'db';
        });
    }

    /**
     * @param int $id
     * @param int $languageID
     * @return $this
     */
    public function loadFromDB(int $id, int $languageID = 0): self
    {
        $languageID = $languageID ?: Shop::getLanguageID();
        $cacheID    = 'mmw_' . $id;
        $this->initLanguageID($languageID);
        if (Shop::has($cacheID)) {
            foreach (\get_object_vars(Shop::get($cacheID)) as $k => $v) {
                $this->$k = $v;
            }
            $this->setCurrentLanguageID($languageID);

            return $this;
        }
        $defaultLanguageID = LanguageHelper::getDefaultLanguage()->getId();
        $data              = $this->db->getObjects(
            'SELECT tmerkmalwert.*, COALESCE(loc.kSprache, def.kSprache) AS kSprache, 
                    COALESCE(loc.cWert, def.cWert) AS cWert,
                    COALESCE(loc.cMetaTitle, def.cMetaTitle) AS cMetaTitle, 
                    COALESCE(loc.cMetaKeywords, def.cMetaKeywords) AS cMetaKeywords,
                    COALESCE(loc.cMetaDescription, def.cMetaDescription) AS cMetaDescription, 
                    COALESCE(loc.cBeschreibung, def.cBeschreibung) AS cBeschreibung,
                    COALESCE(loc.cSeo, def.cSeo) AS cSeo,
                    COALESCE(tmerkmalsprache.cName, tmerkmal.cName) AS cName
                FROM tmerkmalwert 
                INNER JOIN tmerkmalwertsprache AS def 
                    ON def.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND def.kSprache = :lid
                JOIN tmerkmal
					ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                LEFT JOIN tmerkmalwertsprache AS loc 
                    ON loc.kMerkmalWert = tmerkmalwert.kMerkmalWert
                LEFT JOIN tmerkmalsprache
                    ON tmerkmalsprache.kMerkmal = tmerkmalwert.kMerkmal
                    AND tmerkmalsprache.kSprache = loc.kSprache
                WHERE tmerkmalwert.kMerkmalWert = :mid',
            ['mid' => $id, 'lid' => $defaultLanguageID]
        );
        $this->setID($id);
        $this->map($data);
        $this->createBySlug($id);
        $this->setCurrentLanguageID($languageID);

        return $this;
    }

    /**
     * @param array $data
     * @return void
     */
    private function map(array $data): void
    {
        $imagePath = null;
        foreach ($data as $item) {
            $languageID = (int)$item->kSprache;
            $imagePath  = $item->cBildpfad;
            $this->setLanguageID($languageID);
            $this->setID((int)$item->kMerkmalWert);
            $this->setSort((int)$item->nSort);
            $this->setCharacteristicID((int)$item->kMerkmal);
            $this->setValue($item->cWert, $languageID);
            $this->setMetaTitle($item->cMetaTitle, $languageID);
            $this->setMetaDescription($item->cMetaDescription, $languageID);
            $this->setMetaKeywords($item->cMetaKeywords, $languageID);
            $this->setDescription($item->cBeschreibung, $languageID);
            $this->setSlug($item->cSeo, $languageID);
            $this->setCharacteristicName($item->cName, $languageID);
            \executeHook(\HOOK_MERKMALWERT_CLASS_LOADFROMDB, ['oMerkmalWert' => &$this]);
        }
        if (empty($imagePath)) {
            return;
        }
        if (\file_exists(\PFAD_MERKMALWERTBILDER_KLEIN . $imagePath)) {
            $this->cBildpfadKlein      = \PFAD_MERKMALWERTBILDER_KLEIN . $imagePath;
            $this->nBildKleinVorhanden = 1;
        }
        if (\file_exists(\PFAD_MERKMALWERTBILDER_NORMAL . $imagePath)) {
            $this->cBildpfadNormal      = \PFAD_MERKMALWERTBILDER_NORMAL . $imagePath;
            $this->nBildNormalVorhanden = 1;
        }
        $this->setImagePath($imagePath);
        $this->generateAllImageSizes(true, 1, $imagePath);
        $this->generateAllImageDimensions(1, $imagePath);
        $imageBaseURL         = Shop::getImageBaseURL();
        $this->cBildURLKlein  = $imageBaseURL . $this->cBildpfadKlein;
        $this->cBildURLNormal = $imageBaseURL . $this->cBildpfadNormal;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getValue(int $idx = null): ?string
    {
        return $this->values[$idx ?? $this->currentLanguageID] ?? $this->values[$this->fallbackLanguageID] ?? null;
    }

    /**
     * @param string|null $value
     * @param int|null    $idx
     * @return void
     */
    public function setValue(?string $value, int $idx = null): void
    {
        $this->values[$idx ?? $this->currentLanguageID] = $value;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->currentLanguageID;
    }

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID): void
    {
        $this->currentLanguageID = $languageID;
    }

    /**
     * @return int
     */
    public function getCharacteristicID(): int
    {
        return $this->characteristicID;
    }

    /**
     * @param int $id
     */
    public function setCharacteristicID(int $id): void
    {
        $this->characteristicID = $id;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getMetaKeywords(int $idx = null): ?string
    {
        return $this->metaTitles[$idx ?? $this->currentLanguageID]
            ?? $this->metaTitles[$this->fallbackLanguageID]
            ?? null;
    }

    /**
     * @param string|null $metaKeywords
     * @param int|null    $idx
     * @return void
     */
    public function setMetaKeywords(?string $metaKeywords, int $idx = null): void
    {
        $this->metaKeywords[$idx ?? $this->currentLanguageID] = $metaKeywords;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getMetaDescription(int $idx = null): ?string
    {
        return $this->metaDescriptions[$idx ?? $this->currentLanguageID]
            ?? $this->metaDescriptions[$this->fallbackLanguageID]
            ?? null;
    }

    /**
     * @param string|null $metaDescription
     * @param int|null    $idx
     * @return void
     */
    public function setMetaDescription(?string $metaDescription, int $idx = null): void
    {
        $this->metaDescriptions[$idx ?? $this->currentLanguageID] = $metaDescription;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getMetaTitle(int $idx = null): ?string
    {
        return $this->metaTitles[$idx ?? $this->currentLanguageID]
            ?? $this->metaTitles[$this->fallbackLanguageID]
            ?? null;
    }

    /**
     * @param string|null $metaTitle
     * @param int|null    $idx
     * @return void
     */
    public function setMetaTitle(?string $metaTitle, int $idx = null): void
    {
        $this->metaTitles[$idx ?? $this->currentLanguageID] = $metaTitle;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getDescription(int $idx = null): ?string
    {
        return $this->descriptions[$idx ?? $this->currentLanguageID]
            ?? $this->descriptions[$this->fallbackLanguageID]
            ?? null;
    }

    /**
     * @param string|null $description
     * @param int|null    $idx
     * @return void
     */
    public function setDescription(?string $description, int $idx = null): void
    {
        $this->descriptions[$idx ?? $this->currentLanguageID] = $description;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getCharacteristicName(int $idx = null): string
    {
        return $this->characteristicNames[$idx ?? $this->currentLanguageID]
            ?? $this->characteristicNames[$this->fallbackLanguageID]
            ?? '';
    }

    /**
     * @param string   $characteristicName
     * @param int|null $idx
     * @return void
     */
    public function setCharacteristicName(string $characteristicName, int $idx = null): void
    {
        $this->characteristicNames[$idx ?? $this->currentLanguageID] = $characteristicName;
    }

    /**
     * @param int|null $idx
     * @return string|null
     */
    public function getSeo(int $idx = null): ?string
    {
        return $this->getSlug($idx);
    }

    /**
     * @param string|null $seo
     * @param int|null    $idx
     * @return void
     */
    public function setSeo(?string $seo, int $idx = null): void
    {
        $this->setSlug($seo, $idx);
    }

    /**
     * @return string|null
     */
    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    /**
     * @param string|null $path
     */
    public function setImagePath(?string $path): void
    {
        $this->imagePath = $path;
    }
}
