<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use JTL\DB\DbInterface;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Shop;
use function Functional\select;

/**
 * Class Merkmal
 * @package JTL\Catalog\Product
 */
class Merkmal
{
    use MultiSizeImage;
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var string[]
     */
    private array $names = [];

    /**
     * @var int
     */
    private int $sort = 0;

    /**
     * @var MerkmalWert[]
     */
    private array $characteristicValues = [];

    /**
     * @var string
     */
    private string $type = 'TEXT';

    /**
     * @var string
     */
    private string $imagePath = '';

    /**
     * @var int
     */
    private int $currentLanguageID = 0;

    /**
     * @var int
     * @deprecated since 5.2.0
     */
    public int $nBildKleinVorhanden = 0;

    /**
     * @var int
     * @deprecated since 5.2.0
     */
    public int $nBildGrossVorhanden = 0;

    /**
     * @var string[]
     */
    private array $oldPaths = [
        Image::SIZE_SM => \BILD_KEIN_MERKMALBILD_VORHANDEN,
        Image::SIZE_MD => \BILD_KEIN_MERKMALBILD_VORHANDEN,
        Image::SIZE_LG => \BILD_KEIN_MERKMALBILD_VORHANDEN
    ];

    /**
     * @var string[]
     */
    private array $oldURLs = [
        Image::SIZE_SM => '',
        Image::SIZE_MD => '',
        Image::SIZE_LG => ''
    ];

    /**
     * @var string[]
     */
    protected static array $mapping = [
        'kSprache'         => 'LanguageID',
        'kMerkmal'         => 'ID',
        'cName'            => 'Name',
        'nSort'            => 'Sort',
        'cTyp'             => 'Type',
        'oMerkmalWert_arr' => 'CharacteristicValues',
        'cBildpfad'        => 'ImagePath',
        'cBildpfadGross'   => 'ImagePathLG',
        'cBildpfadNormal'  => 'ImagePathMD',
        'cBildpfadKlein'   => 'ImagePathSM',
        'cBildURLGross'    => 'ImageURLLG',
        'cBildURLNormal'   => 'ImageURLMD',
        'cBildURLKlein'    => 'ImageURLSM'
    ];

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
     * Merkmal constructor.
     * @param int              $id
     * @param bool             $getValues
     * @param int              $languageID
     * @param DbInterface|null $db
     */
    public function __construct(
        int                  $id = 0,
        bool                 $getValues = false,
        int                  $languageID = 0,
        private ?DbInterface $db = null
    ) {
        $this->db = $db ?? Shop::Container()->getDB();
        $this->setImageType(Image::TYPE_CHARACTERISTIC);
        if ($id > 0) {
            $this->loadFromDB($id, $getValues, $languageID);
        }
    }

    /**
     * @param int  $id
     * @param bool $getValues
     * @param int  $languageID
     * @return Merkmal
     */
    public function loadFromDB(int $id, bool $getValues = false, int $languageID = 0): self
    {
        $languageID = $languageID ?: Shop::getLanguageID();
        $cacheID    = 'mm_' . $id;
        if ($getValues === false && Shop::has($cacheID)) {
            foreach (\get_object_vars(Shop::get($cacheID)) as $k => $v) {
                $this->$k = $v;
            }
            $this->setLanguageID($languageID);

            return $this;
        }
        $data = $this->db->getObjects(
            'SELECT tmerkmal.*, loc.kSprache, COALESCE(loc.cName, def.cName) AS cName
                FROM tmerkmal INNER JOIN tmerkmalsprache AS def 
                    ON def.kMerkmal = tmerkmal.kMerkmal
                    AND def.kSprache = :lid
                LEFT JOIN tmerkmalsprache AS loc 
                    ON loc.kMerkmal = tmerkmal.kMerkmal
                WHERE tmerkmal.kMerkmal = :mid
                ORDER BY tmerkmal.nSort',
            ['mid' => $id, 'lid' => LanguageHelper::getDefaultLanguage()->getId()]
        );
        $this->map($data);
        if ($getValues && $this->getID() > 0) {
            $tmpAttributes = $this->db->getObjects(
                'SELECT kMerkmalWert
                    FROM tmerkmalwert
                    WHERE kMerkmal = :mid',
                ['mid' => $id]
            );
            foreach ($tmpAttributes as $item) {
                $this->characteristicValues[] = new MerkmalWert((int)$item->kMerkmalWert, $languageID, $this->db);
            }
        }
        $this->setLanguageID($languageID);
        \executeHook(\HOOK_MERKMAL_CLASS_LOADFROMDB, ['instance' => $this]);
        Shop::set($cacheID, $this);

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
            $this->setName($item->cName, $languageID);
            $this->setID((int)$item->kMerkmal);
            $this->setSort((int)$item->nSort);
            $this->setType($item->cTyp);
        }
        if (empty($imagePath)) {
            return;
        }
        $imageBaseURL = Shop::getImageBaseURL();
        if (\file_exists(\PFAD_MERKMALBILDER_KLEIN . $imagePath)) {
            $this->setImagePathSM(\PFAD_MERKMALBILDER_KLEIN . $imagePath);
            $this->nBildKleinVorhanden = 1;
        }
        if (\file_exists(\PFAD_MERKMALBILDER_NORMAL . $imagePath)) {
            $this->setImagePathMD(\PFAD_MERKMALBILDER_NORMAL . $imagePath);
            $this->nBildGrossVorhanden = 1;
        }
        $this->generateAllImageSizes(true, 1, $imagePath);
        $this->generateAllImageDimensions(1, $imagePath);
        $this->setImageURLLG($imageBaseURL . $this->getImagePathLG());
        $this->setImageURLMD($imageBaseURL . $this->getImagePathMD());
        $this->setImageURLSM($imageBaseURL . $this->getImagePathSM());
    }

    /**
     * @return int|null
     */
    public function getID(): ?int
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
    public function getName(int $idx = null): ?string
    {
        return $this->names[$idx ?? $this->currentLanguageID] ?? null;
    }

    /**
     * @param string|null $name
     * @param int|null    $idx
     * @return void
     */
    public function setName(?string $name, int $idx = null): void
    {
        $this->names[$idx ?? $this->currentLanguageID] = $name;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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

    /**
     * @return string|null
     */
    public function getImagePathSM(): ?string
    {
        return $this->oldPaths[Image::SIZE_SM];
    }

    /**
     * @param string|null $path
     */
    public function setImagePathSM(?string $path): void
    {
        $this->oldPaths[Image::SIZE_SM] = $path;
    }

    /**
     * @return string|null
     */
    public function getImagePathMD(): ?string
    {
        return $this->oldPaths[Image::SIZE_MD];
    }

    /**
     * @param string|null $path
     */
    public function setImagePathMD(?string $path): void
    {
        $this->oldPaths[Image::SIZE_MD] = $path;
    }

    /**
     * @return string|null
     */
    public function getImagePathLG(): ?string
    {
        return $this->oldPaths[Image::SIZE_LG];
    }

    /**
     * @param string|null $path
     */
    public function setImagePathLG(?string $path): void
    {
        $this->oldPaths[Image::SIZE_LG] = $path;
    }

    /**
     * @return string|null
     */
    public function getImageURLSM(): ?string
    {
        return $this->oldURLs[Image::SIZE_SM];
    }

    /**
     * @param string|null $url
     */
    public function setImageURLSM(?string $url): void
    {
        $this->oldURLs[Image::SIZE_SM] = $url;
    }

    /**
     * @return string|null
     */
    public function getImageURLMD(): ?string
    {
        return $this->oldURLs[Image::SIZE_MD];
    }

    /**
     * @param string|null $url
     */
    public function setImageURLMD(?string $url): void
    {
        $this->oldURLs[Image::SIZE_MD] = $url;
    }

    /**
     * @return string|null
     */
    public function getImageURLLG(): ?string
    {
        return $this->oldURLs[Image::SIZE_LG];
    }

    /**
     * @param string|null $url
     */
    public function setImageURLLG(?string $url): void
    {
        $this->oldURLs[Image::SIZE_LG] = $url;
    }

    /**
     * @return MerkmalWert[]
     */
    public function getCharacteristicValues(): array
    {
        return $this->characteristicValues;
    }

    /**
     * @param MerkmalWert[] $characteristicValues
     */
    public function setCharacteristicValues(array $characteristicValues): void
    {
        $this->characteristicValues = $characteristicValues;
    }

    /**
     * @param MerkmalWert $characteristicValue
     * @return void
     */
    public function addCharacteristicValue(MerkmalWert $characteristicValue): void
    {
        $this->characteristicValues[] = $characteristicValue;
    }
}
