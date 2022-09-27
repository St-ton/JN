<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class ProductPropertyValueModel
 *
 * @package JTL\REST\Models
 * @property int    $kEigenschaftWert
 * @method int getKEigenschaftWert()
 * @method void setKEigenschaftWert(int $value)
 * @property int    $kEigenschaft
 * @method int getKEigenschaft()
 * @method void setKEigenschaft(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property float  $fAufpreisNetto
 * @method float getFAufpreisNetto()
 * @method void setFAufpreisNetto(float $value)
 * @property float  $fGewichtDiff
 * @method float getFGewichtDiff()
 * @method void setFGewichtDiff(float $value)
 * @property string $cArtNr
 * @method string getCArtNr()
 * @method void setCArtNr(string $value)
 * @property int    $nSort
 * @method int getNSort()
 * @method void setNSort(int $value)
 * @property float  $fLagerbestand
 * @method float getFLagerbestand()
 * @method void setFLagerbestand(float $value)
 * @property float  $fPackeinheit
 * @method float getFPackeinheit()
 * @method void setFPackeinheit(float $value)
 */
final class ProductPropertyValueModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'teigenschaftwert';
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
        if ($attributes !== null) {
            return $attributes;
        }
        $attributes                    = [];
        $attributes['propertyValueID'] = DataAttribute::create('kEigenschaftWert', 'int', self::cast('0', 'int'), false, true);
        $attributes['propertyID']      = DataAttribute::create('kEigenschaft', 'int', null, true, false);
        $attributes['name']            = DataAttribute::create('cName', 'varchar', null, true, false);
        $attributes['surchargeNet']    = DataAttribute::create('fAufpreisNetto', 'double', self::cast('0.0000', 'double'), false, false);
        $attributes['weightDiff']      = DataAttribute::create('fGewichtDiff', 'double', null, true, false);
        $attributes['sku']             = DataAttribute::create('cArtNr', 'varchar', null, true, false);
        $attributes['sort']            = DataAttribute::create('nSort', 'int', self::cast('0', 'int'), true, false);
        $attributes['stock']           = DataAttribute::create('fLagerbestand', 'double', null, true, false);
        $attributes['packagingUnit']   = DataAttribute::create('fPackeinheit', 'double', self::cast('1.0000', 'double'), true, false);

        $attributes['localization'] = DataAttribute::create(
            'localization',
            ProductPropertyValueLocalizationModel::class,
            null,
            true,
            false,
            'kEigenschaftWert'
        );

        return $attributes;
    }
}
