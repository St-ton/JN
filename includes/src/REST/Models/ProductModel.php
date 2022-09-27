<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Shop;

/**
 * Class ProductModel
 * @OA\Schema(
 *     title="Product model",
 *     description="Product model",
 * )
 *
 * @OA\Property(
 *   property="id",
 *   type="integer",
 *   description="The product id"
 * )
 * @property int                                     $id
 * @OA\Property(
 *   property="manufacturerID",
 *   type="integer",
 *   description="The manufacturer's id"
 * )
 * @property int                                     $manufacturerID
 * @OA\Property(
 *   property="deliveryStatus",
 *   type="integer",
 *   description="The deliery status"
 * )
 * @property int                                     $deliveryStatus
 * @OA\Property(
 *   property="taxClassID",
 *   type="integer"
 * )
 * @property int                                     $taxClassID
 * @OA\Property(
 *   property="unitID",
 *   type="integer"
 * )
 * @property int                                     $unitID
 * @OA\Property(
 *   property="shippingClassID",
 *   type="integer"
 * )
 * @property int                                     $shippingClassID
 * @property int                                     $kEigenschaftKombi
 * @property int                                     $parentID
 * @property int                                     $partlistID
 * @property int                                     $commodityGroup
 * @property int                                     $kVPEEinheit
 * @property int                                     $kMassEinheit
 * @property int                                     $basePriceUnit
 * @OA\Property(
 *   property="slug",
 *   type="string",
 *   description="The url slug"
 * )
 * @property string                                  $slug
 * @OA\Property(
 *   property="sku",
 *   type="string",
 *   description="product SKU"
 * )
 * @property string                                  $sku
 * @OA\Property(
 *   property="name",
 *   type="string",
 *   description="The product name"
 * )
 * @property string                                  $name
 * @OA\Property(
 *   property="description",
 *   type="string",
 *   description="The description"
 * )
 * @property string                                  $description
 * @property string                                  $comment
 * @property float                                   $stockQty
 * @property float                                   $fStandardpreisNetto
 * @property float                                   $taxRate
 * @property float                                   $minOrderQty
 * @property float                                   $fLieferantenlagerbestand
 * @property float                                   $fLieferzeit
 * @property string                                  $barcode
 * @property string                                  $topProduct
 * @property float                                   $weight
 * @property float                                   $productWeight
 * @property float                                   $fMassMenge
 * @property float                                   $fGrundpreisMenge
 * @property float                                   $width
 * @property float                                   $height
 * @property float                                   $length
 * @property string                                  $new
 * @OA\Property(
 *   property="shortdescription",
 *   type="string",
 *   description="The short description"
 * )
 * @property string                                  $shortdescription
 * @property float                                   $msrp
 * @property string                                  $cLagerBeachten
 * @property string                                  $cLagerKleinerNull
 * @property string                                  $cLagerVariation
 * @property string                                  $divisible
 * @property float                                   $fPackeinheit
 * @property float                                   $fAbnahmeintervall
 * @property float                                   $fZulauf
 * @property string                                  $cVPE
 * @property float                                   $fVPEWert
 * @property string                                  $cVPEEinheit
 * @property string                                  $searchTerms
 * @property int                                     $sort
 * @OA\Property(
 *     property="release",
 *     default="2022-09-22 18:31:45",
 *     format="datetime",
 *     description="Release date",
 *     title="Release date",
 *     type="string"
 * )
 * @property DateTime                                $release
 * @property DateTime                                $created
 * @property DateTime                                $lastModified
 * @property DateTime                                $dZulaufDatum
 * @property DateTime                                $bbd
 * @property string                                  $series
 * @property string                                  $isbn
 * @property string                                  $asin
 * @property string                                  $han
 * @property string                                  $cUNNummer
 * @property string                                  $cGefahrnr
 * @property string                                  $cTaric
 * @property string                                  $cUPC
 * @property string                                  $originCountry
 * @property string                                  $epid
 * @property int                                     $isParent
 * @property int                                     $nLiefertageWennAusverkauft
 * @property int                                     $autoDeliveryCalculation
 * @property int                                     $nBearbeitungszeit
 * @property Collection|ProductLocalizationModel[]   $localization
 * @property Collection|ProductCharacteristicModel[] $characteristics
 * @property Collection|ProductAttributeModel[]      $functionalAttributes
 * @property Collection|AttributeModel[]             $attributes
 * @property Collection|ProductVisibilityModel[]     $visibility
 * @property Collection|ProductDownloadModel[]       $downloads
 */
final class ProductModel extends DataModel
{
    /**
     * pseudo auto increment for ProductCategories model
     *
     * @var int
     */
    protected int $lastProductCategoryID = -1;

    /**
     * pseudo auto increment for ProductAttributes model
     *
     * @var int
     */
    protected int $lastProductAttributeID = -1;

    /**
     * pseudo auto increment for Attribute model
     *
     * @var int
     */
    protected int $lastAttributeID = -1;

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikel';
    }

    /**
     * @inheritdoc
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @inheritdoc
     */
    public function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();

        $this->registerSetter('characteristics', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->characteristics ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!\is_array($data)) {
                    // support adding product characteristics by a simple array of characteristic value IDs
                    $id = (int)$data;
                    if ($id <= 0) {
                        continue;
                    }
                    $data = ['valueID' => $id];
                    try {
                        $char = CharacteristicValueModel::loadByAttributes(
                            ['id' => $id],
                            $this->getDB()
                        );
                    } catch (Exception) {
                        continue;
                    }
                    $data['id'] = $char->characteristicID;
                }
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                try {
                    $item = ProductCharacteristicModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $res->push($item);
                continue;
                $existing = $res->first(static function ($e) use ($item): bool {
                    return $e->productID === $item->productID && $e->valueID === $item->valueID;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                        }
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('localization', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->localization ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                try {
                    $item = ProductLocalizationModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($item): bool {
                    return $e->productID === $item->productID && $e->languageID === $item->languageID;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                        }
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('categories', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->categories ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                if (!isset($data['id'])) {
                    // tkategorieartikel has no auto increment ID...
                    if ($this->lastProductCategoryID === -1) {
                        $this->lastProductCategoryID = $this->getDB()?->getSingleInt(
                            'SELECT MAX(kKategorieArtikel) AS newID FROM tkategorieartikel',
                            'newID'
                        );
                    }
                    $data['id'] = ++$this->lastProductCategoryID;
                }
                try {
                    $item = ProductCategoriesModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($item): bool {
                    return $e->id === $item->id && $e->id > 0;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                        }
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('functionalAttributes', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->functionalAttributes ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                if (!isset($data['id'])) {
                    // tartikelattribut has no auto increment ID...
                    if ($this->lastProductAttributeID === -1) {
                        $this->lastProductAttributeID = $this->getDB()?->getSingleInt(
                            'SELECT MAX(kArtikelAttribut) AS newID FROM tartikelattribut',
                            'newID'
                        );
                    }
                    $data['id'] = ++$this->lastProductCategoryID;
                }
                try {
                    $item = ProductAttributeModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($item): bool {
                    return $e->id === $item->id && $e->id > 0;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                        }
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('attributes', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->attributes ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                if (!isset($data['id'])) {
                    // tattribut has no auto increment ID...
                    if ($this->lastAttributeID === -1) {
                        $this->lastAttributeID = $this->getDB()?->getSingleInt(
                            'SELECT MAX(kAttribut) AS newID FROM tattribut',
                            'newID'
                        );
                    }
                    $data['id'] = ++$this->lastAttributeID;
                }
                try {
                    $item = AttributeModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($item): bool {
                    return $e->id === $item->id && $e->id > 0;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                        }
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('images', function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->images ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                try {
                    $img = ProductImageModel::loadByAttributes($data, $this->getDB(), self::ON_NOTEXISTS_NEW);
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($img) {
                    return $e->productID === $img->productID && $e->id === $img->id;
                });
                if ($existing === null) {
                    $res->push($img);
                } else {
                    foreach ($img->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $img->getAttribValue($attribute));
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('prices', function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->prices ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                try {
                    $price = PriceModel::loadByAttributes($data, $this->getDB(), self::ON_NOTEXISTS_NEW);
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($price): bool {
                    return $e->kPreis === $price->kPreis
                        && $e->customerGroupID === $price->customerGroupID
                        && $e->customerID === $price->customerID;
                });
                if ($existing === null) {
                    $res->push($price);
                } else {
                    foreach ($price->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $price->getAttribValue($attribute));
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('minimumOrderQuantities', function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->minimumOrderQuantities ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                try {
                    $minQty = MinimumPurchaseQuantityModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($minQty): bool {
                    return $e->productID === $minQty->productID;
                });
                if ($existing === null) {
                    $res->push($minQty);
                } else {
                    foreach ($minQty->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $minQty->getAttribValue($attribute));
                    }
                }
            }

            return $res;
        });

        $this->registerSetter('categoryDiscounts', function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->categoryDiscounts ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['productID'])) {
                    $data['productID'] = $model->id;
                }
                try {
                    $discounts = ProductCategoryDiscountModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($discounts): bool {
                    return $e->productID === $discounts->productID;
                });
                if ($existing === null) {
                    $res->push($discounts);
                } else {
                    foreach ($discounts->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $discounts->getAttribValue($attribute));
                    }
                }
            }

            return $res;
        });
    }


    /**
     *
     */
    protected function onInstanciation(): void
    {
        if ($this->id <= 0) {
            return;
        }
        if ($this->characteristics instanceof Collection && $this->characteristics->count() > 0) {
            $characteristics = new Collection();
            foreach ($this->characteristics as $data) {
                $item = CharacteristicValueModel::loadByAttributes(
                    ['characteristicID' => $data->id, 'id' => $data->valueID],
                    $this->getDB()
                );
                $characteristics->push($item);
            }
            $this->characteristics = $characteristics;
        }
        if ($this->categories instanceof Collection && $this->categories->count() > 0) {
            $categories = new Collection();
            foreach ($this->categories as $data) {
                try {
                    $item = CategoryModel::loadByAttributes(
                        ['id' => $data->categoryID],
                        $this->getDB()
                    );
                } catch (Exception) {
                    continue;
                }
                $categories->push($item);
            }
            $this->categories = $categories;
        }
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;

        if ($attributes !== null) {
            return $attributes;
        }
        $attributes                            = [];
        $attributes['id']                      = DataAttribute::create('kArtikel', 'int', self::cast('0', 'int'), false, true);
        $attributes['manufacturerID']          = DataAttribute::create('kHersteller', 'int', self::cast('0', 'int'), false);
        $attributes['taxClassID']              = DataAttribute::create('kSteuerklasse', 'int', self::cast('0', 'int'), false);
        $attributes['unitID']                  = DataAttribute::create('kEinheit', 'int', self::cast('0', 'int'), false);
        $attributes['shippingClassID']         = DataAttribute::create('kVersandklasse', 'int', self::cast('0', 'int'), false);
        $attributes['parentID']                = DataAttribute::create('kVaterArtikel', 'bigint', self::cast('0', 'int'), false);
        $attributes['partlistID']              = DataAttribute::create('kStueckliste', 'int', self::cast('0', 'int'), false);
        $attributes['slug']                    = DataAttribute::create('cSeo', 'varchar', self::cast('', 'varchar'), false);
        $attributes['sku']                     = DataAttribute::create('cArtNr', 'varchar');
        $attributes['name']                    = DataAttribute::create('cName', 'varchar');
        $attributes['description']             = DataAttribute::create('cBeschreibung', 'mediumtext');
        $attributes['comment']                 = DataAttribute::create('cAnmerkung', 'mediumtext');
        $attributes['stockQty']                = DataAttribute::create('fLagerbestand', 'double', self::cast('0', 'double'));
        $attributes['taxRate']                 = DataAttribute::create('fMwSt', 'float');
        $attributes['minOrderQty']             = DataAttribute::create('fMindestbestellmenge', 'double', self::cast('0', 'double'));
        $attributes['topProduct']              = DataAttribute::create('cTopArtikel', 'yesno', self::cast('N', 'yesno'));
        $attributes['weight']                  = DataAttribute::create('fGewicht', 'double', self::cast('0', 'double'), false);
        $attributes['productWeight']           = DataAttribute::create('fArtikelgewicht', 'double', self::cast('0', 'double'), false);
        $attributes['width']                   = DataAttribute::create('fBreite', 'double', self::cast('0', 'double'));
        $attributes['height']                  = DataAttribute::create('fHoehe', 'double', self::cast('0', 'double'));
        $attributes['length']                  = DataAttribute::create('fLaenge', 'double', self::cast('0', 'double'));
        $attributes['new']                     = DataAttribute::create('cNeu', 'yesno', self::cast('N', 'yesno'));
        $attributes['shortDescription']        = DataAttribute::create('cKurzBeschreibung', 'mediumtext');
        $attributes['msrp']                    = DataAttribute::create('fUVP', 'float', self::cast('0', 'double'));
        $attributes['divisible']               = DataAttribute::create('cTeilbar', 'yesno', self::cast('N', 'yesno'));
        $attributes['searchTerms']             = DataAttribute::create('cSuchbegriffe', 'varchar');
        $attributes['sort']                    = DataAttribute::create('nSort', 'int', self::cast('0', 'int'), false);
        $attributes['release']                 = DataAttribute::create('dErscheinungsdatum', 'date');
        $attributes['created']                 = DataAttribute::create('dErstellt', 'date', 'now()');
        $attributes['lastModified']            = DataAttribute::create('dLetzteAktualisierung', 'datetime');
        $attributes['series']                  = DataAttribute::create('cSerie', 'varchar');
        $attributes['isbn']                    = DataAttribute::create('cISBN', 'varchar');
        $attributes['asin']                    = DataAttribute::create('cASIN', 'varchar');
        $attributes['han']                     = DataAttribute::create('cHAN', 'varchar');
        $attributes['originCountry']           = DataAttribute::create('cHerkunftsland', 'varchar');
        $attributes['epid']                    = DataAttribute::create('cEPID', 'varchar');
        $attributes['isParent']                = DataAttribute::create('nIstVater', 'tinyint', self::cast('0', 'tinyint'), false);
        $attributes['autoDeliveryCalculation'] = DataAttribute::create('nAutomatischeLiefertageberechnung', 'int');
        $attributes['deliveryStatus']          = DataAttribute::create('kLieferstatus', 'int', self::cast('0', 'int'), false);
        $attributes['commodityGroup']          = DataAttribute::create('kWarengruppe', 'int', self::cast('0', 'int'), false);
        $attributes['basePriceUnit']           = DataAttribute::create('kGrundPreisEinheit', 'int');
        $attributes['shelflifeExpirationDate'] = DataAttribute::create('dMHD', 'date');

        $attributes['propertyCombinationID']      = DataAttribute::create('kEigenschaftKombi', 'int', self::cast('0', 'int'), false);
        $attributes['packagingUnitID']            = DataAttribute::create('kVPEEinheit', 'int');
        $attributes['measurementUnitID']          = DataAttribute::create('kMassEinheit', 'int');
        $attributes['standardPriceNet']           = DataAttribute::create('fStandardpreisNetto', 'double');
        $attributes['deliveryTime']               = DataAttribute::create('fLieferzeit', 'double', self::cast('0', 'double'), false);
        $attributes['barcode']                    = DataAttribute::create('cBarcode', 'varchar');
        $attributes['measurementAmount']          = DataAttribute::create('fMassMenge', 'double', self::cast('0', 'double'));
        $attributes['basePriceAmount']            = DataAttribute::create('fGrundpreisMenge', 'double', self::cast('0', 'double'));
        $attributes['trackStock']                 = DataAttribute::create('cLagerBeachten', 'yesno', self::cast('N', 'yesno'));
        $attributes['stockMayBeSmallerThanZero']  = DataAttribute::create('cLagerKleinerNull', 'yesno', self::cast('N', 'yesno'));
        $attributes['trackStocksOfVariations']    = DataAttribute::create('cLagerVariation', 'yesno', self::cast('N', 'yesno'));
        $attributes['packagingUnit']              = DataAttribute::create('fPackeinheit', 'double', self::cast('1.0000', 'double'));
        $attributes['permissibleOrderQty']        = DataAttribute::create('fAbnahmeintervall', 'double', self::cast('0', 'double'), false);
        $attributes['awaitedDelivery']            = DataAttribute::create('fZulauf', 'double', self::cast('0', 'double'));
        $attributes['hasPackagingUnit']           = DataAttribute::create('cVPE', 'yesno', self::cast('N', 'yesno'));
        $attributes['packagingUnitAmount']        = DataAttribute::create('fVPEWert', 'double');
        $attributes['packagingUnitName']          = DataAttribute::create('cVPEEinheit', 'varchar', self::cast('0', 'double'));
        $attributes['supplierStocks']             = DataAttribute::create('fLieferantenlagerbestand', 'double', self::cast('0', 'float'), false);
        $attributes['deliveryDaysWhenSoldOut']    = DataAttribute::create('nLiefertageWennAusverkauft', 'int', self::cast('0', 'int'));
        $attributes['handlingTime']               = DataAttribute::create('nBearbeitungszeit', 'int', self::cast('0', 'int'));
        $attributes['unNumber']                   = DataAttribute::create('cUNNummer', 'varchar');
        $attributes['hazardIdentificationNumber'] = DataAttribute::create('cGefahrnr', 'varchar');
        $attributes['taricCode']                  = DataAttribute::create('cTaric', 'varchar');
        $attributes['upc']                        = DataAttribute::create('cUPC', 'varchar');
        $attributes['dateOfAwaitedDelivery']      = DataAttribute::create('dZulaufDatum', 'date');

        $attributes['localization']           = DataAttribute::create(
            'localization', ProductLocalizationModel::class, null, true, false, 'kArtikel'
        );
        $attributes['characteristics']        = DataAttribute::create(
            'characteristics', ProductCharacteristicModel::class, null, true, false, 'kArtikel'
        );
        $attributes['functionalAttributes']   = DataAttribute::create(
            'functionalAttributes', ProductAttributeModel::class, null, true, false, 'kArtikel'
        );
        $attributes['attributes']             = DataAttribute::create(
            'attributes', AttributeModel::class, null, true, false, 'kArtikel'
        );
        $attributes['visibility']             = DataAttribute::create(
            'visibility', ProductVisibilityModel::class, null, true, false, 'kArtikel'
        );
        $attributes['downloads']              = DataAttribute::create(
            'downloads', ProductDownloadModel::class, null, true, false, 'kArtikel'
        );
        $attributes['images']                 = DataAttribute::create(
            'images', ProductImageModel::class, null, true, false, 'kArtikel'
        );
        $attributes['prices']                 = DataAttribute::create(
            'prices', PriceModel::class, null, true, false, 'kArtikel'
        );
        $attributes['categories']             = DataAttribute::create(
            'categories', ProductCategoriesModel::class, null, true, false, 'kArtikel'
        );
        $attributes['stock']                  = DataAttribute::create(
            'stock', StockModel::class, null, true, false, 'kArtikel'
        );
        $attributes['minimumOrderQuantities'] = DataAttribute::create(
            'minimumOrderQuantities',
            MinimumPurchaseQuantityModel::class,
            null,
            true,
            false,
            'kArtikel'
        );
        $attributes['categoryDiscounts']      = DataAttribute::create(
            'categoryDiscounts',
            ProductCategoryDiscountModel::class,
            null,
            true,
            false,
            'kArtikel'
        );
        $attributes['properties']             = DataAttribute::create(
            'properties',
            ProductPropertyModel::class,
            null,
            true,
            false,
            'kArtikel'
        );

        return $attributes;
    }
}
