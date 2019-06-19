<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Category;

use JTL\Helpers\Text;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;

/**
 * Class MenuItem
 * @package JTL\Catalog\Category
 */
class MenuItem
{
    use MagicCompatibilityTrait;

    public static $mapping = [
        'kKategorie'                 => 'ID',
        'kOberKategorie'             => 'ParentID',
        'cBeschreibung'              => 'Description',
        'cURL'                       => 'URL',
        'cURLFull'                   => 'URL',
        'cBildURL'                   => 'ImageURL',
        'cBildURLFull'               => 'ImageURL',
        'cName'                      => 'Name',
        'cKurzbezeichnung'           => 'ShortName',
        'cnt'                        => 'ProductCount',
        'categoryAttributes'         => 'Attributes',
        'categoryFunctionAttributes' => 'FunctionalAttributes',
        'bUnterKategorien'           => 'HasChildrenCompat',
        'Unterkategorien'            => 'Children',
        'cSeo'                       => 'URL',
    ];

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $parentID = 0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $shortName = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $url = '';

    /**
     * @var string
     */
    private $imageURL = '';

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $functionalAttributes = [];

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var bool
     */
    private $hasChildren = false;

    /**
     * @var int
     */
    private $productCount = -1;

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getParentID(): int
    {
        return $this->parentID;
    }

    /**
     * @param int $parentID
     */
    public function setParentID(int $parentID): void
    {
        $this->parentID = $parentID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     */
    public function setShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = Text::parseNewsText($description);
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getImageURL(): string
    {
        return $this->imageURL;
    }

    /**
     * @param string $imageURL
     */
    public function setImageURL(string $imageURL): void
    {
        $this->imageURL  = Shop::getImageBaseURL();
        $this->imageURL .= empty($imageURL)
            ? \BILD_KEIN_KATEGORIEBILD_VORHANDEN
            : \PFAD_KATEGORIEBILDER . $imageURL;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getFunctionalAttributes(): array
    {
        return $this->functionalAttributes;
    }

    /**
     * @param array $functionalAttributes
     */
    public function setFunctionalAttributes(array $functionalAttributes): void
    {
        $this->functionalAttributes = $functionalAttributes;
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->hasChildren;
    }

    /**
     * @return int
     */
    public function getHasChildrenCompat(): int
    {
        return (int)$this->hasChildren;
    }

    /**
     * @param int $has
     */
    public function setHasChildrenCompat(int $has): void
    {
        $this->hasChildren = (bool)$has;
    }

    /**
     * @return bool
     */
    public function getHasChildren(): bool
    {
        return $this->hasChildren;
    }

    /**
     * @param bool $hasChildren
     */
    public function setHasChildren(bool $hasChildren): void
    {
        $this->hasChildren = $hasChildren;
    }

    /**
     * @return int
     */
    public function getProductCount(): int
    {
        return $this->productCount;
    }

    /**
     * @param int $productCount
     */
    public function setProductCount(int $productCount): void
    {
        $this->productCount = $productCount;
    }

    /**
     * MenuItem constructor.
     * @param stdClass $data
     */
    public function __construct($data)
    {
        $this->setID($data->kKategorie);
        $this->setParentID($data->kOberKategorie);
        if (empty($data->cName_spr)) {
            $this->setName($data->cName);
        } else {
            $this->setName($data->cName_spr);
        }
        if (empty($data->cBeschreibung_spr)) {
            $this->setDescription($data->cBeschreibung);
        } else {
            $this->setDescription($data->cBeschreibung_spr);
        }

        $this->setUrl($data->cSeo ?? '');
        $this->setImageURL($data->cPfad ?? '');
        $this->setProductCount($data->cnt);
    }
}
