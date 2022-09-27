<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class ProductCategoryDiscountModel
 *
 * @package JTL\REST\Models
 * @property int   $kArtikel
 * @method int getKArtikel()
 * @method void setKArtikel(int $value)
 * @property int   $kKundengruppe
 * @method int getKKundengruppe()
 * @method void setKKundengruppe(int $value)
 * @property int   $kKategorie
 * @method int getKKategorie()
 * @method void setKKategorie(int $value)
 * @property float $fRabatt
 * @method float getFRabatt()
 * @method void setFRabatt(float $value)
 */
final class ProductCategoryDiscountModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelkategorierabatt';
    }

    /**
     * Setting of keyname is not supported!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @inheritdoc
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @inheritdoc
     */
    protected function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes === null) {
            $attributes                    = [];
            $attributes['productID']       = DataAttribute::create('kArtikel', 'int', null, false, true);
            $attributes['customerGroupID'] = DataAttribute::create('kKundengruppe', 'int', null, false, true);
            $attributes['categoryID']      = DataAttribute::create('kKategorie', 'int', null, false);
            $attributes['discount']        = DataAttribute::create('fRabatt', 'double', null, false);
        }

        return $attributes;
    }
}
