<?php declare(strict_types=1);

namespace JTL\Catalog\Wishlist;

use JTL\Catalog\Product\Artikel;
use JTL\Shop;
use stdClass;
use function Functional\select;
use function Functional\some;

/**
 * Class WishlistItem
 * @package JTL\Catalog\Wishlist
 */
class WishlistItem
{
    /**
     * @var string[]
     */
    protected static array $mapping = [
        'kWunschliste'                   => 'ishlistID',
        'kWunschlistePos'                => 'ID',
        'kArtikel'                       => 'ProductID',
        'fAnzahl'                        => 'Qty',
        'cArtikelName'                   => 'ProductName',
        'cKommentar'                     => 'Comment',
        'dHinzugefuegt'                  => 'DateAdded',
        'dHinzugefuegt_de'               => 'DateAddedLocalized',
        'CWunschlistePosEigenschaft_arr' => 'Properties',
        'Artikel'                        => 'Product',
        'cPreis'                         => 'Price',
        'cURL'                           => 'URL'
    ];

    /**
     * @var int
     */
    public $kWunschlistePos;

    /**
     * @var int
     */
    public $kWunschliste;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fAnzahl;

    /**
     * @var string
     */
    public $cArtikelName;

    /**
     * @var string
     */
    public $cKommentar = '';

    /**
     * @var string
     */
    public $dHinzugefuegt;

    /**
     * @var string
     */
    public $dHinzugefuegt_de;

    /**
     * @var array
     */
    public $CWunschlistePosEigenschaft_arr = [];

    /**
     * @var Artikel|null
     */
    public $Artikel;

    /**
     * @var string
     */
    public $cPreis = '';

    /**
     * @var string
     */
    public $cURL = '';

    public function __wakeup(): void
    {
        if ($this->kArtikel === null) {
            return;
        }
        $this->Artikel = new Artikel();
        $this->Artikel->fuelleArtikel($this->kArtikel, Artikel::getDefaultOptions());
    }

    /**
     * @return array
     */
    public function __sleep(): array
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e): bool {
            return $e !== 'Artikel';
        });
    }

    /**
     * WishlistItem constructor.
     * @param int          $productID
     * @param string       $productName
     * @param float|string $qty
     * @param int          $wihlistID
     */
    public function __construct(int $productID, string $productName, $qty, int $wihlistID)
    {
        $this->kArtikel     = $productID;
        $this->cArtikelName = $productName;
        $this->fAnzahl      = $qty;
        $this->kWunschliste = $wihlistID;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function erstellePosEigenschaften(array $values): self
    {
        foreach ($values as $value) {
            $wlItemProp = new WishlistItemProperty(
                $value->kEigenschaft,
                !empty($value->kEigenschaftWert) ? $value->kEigenschaftWert : null,
                !empty($value->cFreifeldWert) ? $value->cFreifeldWert : null,
                !empty($value->cEigenschaftName) ? $value->cEigenschaftName : null,
                !empty($value->cEigenschaftWertName) ? $value->cEigenschaftWertName : null,
                $this->kWunschlistePos
            );
            $wlItemProp->schreibeDB();
            $this->CWunschlistePosEigenschaft_arr[] = $wlItemProp;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $ins                = new stdClass();
        $ins->kWunschliste  = $this->kWunschliste;
        $ins->kArtikel      = $this->kArtikel;
        $ins->fAnzahl       = $this->fAnzahl;
        $ins->cArtikelName  = $this->cArtikelName;
        $ins->cKommentar    = $this->cKommentar;
        $ins->dHinzugefuegt = $this->dHinzugefuegt;

        $this->kWunschlistePos = Shop::Container()->getDB()->insert('twunschlistepos', $ins);

        return $this;
    }

    /**
     * @return $this
     */
    public function updateDB(): self
    {
        $upd                  = new stdClass();
        $upd->kWunschlistePos = $this->kWunschlistePos;
        $upd->kWunschliste    = $this->kWunschliste;
        $upd->kArtikel        = $this->kArtikel;
        $upd->fAnzahl         = $this->fAnzahl;
        $upd->cArtikelName    = $this->cArtikelName;
        $upd->cKommentar      = $this->cKommentar;
        $upd->dHinzugefuegt   = $this->dHinzugefuegt;

        Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $this->kWunschlistePos, $upd);

        return $this;
    }

    /**
     * @param int      $propertyID
     * @param null|int $propertyValueID
     * @return bool
     */
    public function istEigenschaftEnthalten(int $propertyID, ?int $propertyValueID): bool
    {
        return some(
            $this->CWunschlistePosEigenschaft_arr,
            static function ($e) use ($propertyID, $propertyValueID): bool {
                return (int)$e->kEigenschaft === $propertyID && (int)$e->kEigenschaftWert === $propertyValueID;
            }
        );
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->kWunschlistePos;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->kWunschlistePos = $id;
    }

    /**
     * @return int
     */
    public function getWishlistID(): int
    {
        return $this->kWunschliste;
    }

    /**
     * @param int $wishlistID
     */
    public function setWishlistID(int $wishlistID): void
    {
        $this->kWunschliste = $wishlistID;
    }

    /**
     * @return int
     */
    public function getProductID(): int
    {
        return $this->kArtikel;
    }

    /**
     * @param int $productID
     */
    public function setProductID(int $productID): void
    {
        $this->kArtikel = $productID;
    }

    /**
     * @return float|int|string
     */
    public function getQty()
    {
        return $this->fAnzahl;
    }

    /**
     * @param float|int|string $qty
     */
    public function setQty($qty): void
    {
        $this->fAnzahl = $qty;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->cArtikelName;
    }

    /**
     * @param string $productName
     */
    public function setProductName(string $productName): void
    {
        $this->cArtikelName = $productName;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->cKommentar;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->cKommentar = $comment;
    }

    /**
     * @return string
     */
    public function getDateAdded(): string
    {
        return $this->dHinzugefuegt;
    }

    /**
     * @param string $date
     */
    public function setDateAdded(string $date): void
    {
        $this->dHinzugefuegt = $date;
    }

    /**
     * @return string
     */
    public function getDateAddedLocalized(): string
    {
        return $this->dHinzugefuegt_de;
    }

    /**
     * @param string $date
     */
    public function setDateAddedLocalized(string $date): void
    {
        $this->dHinzugefuegt_de = $date;
    }

    /**
     * @return WishlistItemProperty[]
     */
    public function getProperties(): array
    {
        return $this->CWunschlistePosEigenschaft_arr;
    }

    /**
     * @param WishlistItemProperty[] $properties
     */
    public function setProperties(array $properties): void
    {
        $this->CWunschlistePosEigenschaft_arr = $properties;
    }

    /**
     * @param WishlistItemProperty $property
     */
    public function addProperty(WishlistItemProperty $property): void
    {
        $this->CWunschlistePosEigenschaft_arr[] = $property;
    }

    /**
     * @return Artikel|null
     */
    public function getProduct(): ?Artikel
    {
        return $this->Artikel;
    }

    /**
     * @param Artikel $product
     */
    public function setProduct(Artikel $product): void
    {
        $this->Artikel = $product;
    }

    public function unsetProduct(): void
    {
        unset($this->Artikel);
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->cPreis;
    }

    /**
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->cPreis = $price;
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
}
