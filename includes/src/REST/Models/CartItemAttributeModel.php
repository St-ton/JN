<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CartItemAttributeModel
 *
 * @package JTL\REST\Models
 * @property int    $kWarenkorbPosEigenschaft
 * @method int getKWarenkorbPosEigenschaft()
 * @method void setKWarenkorbPosEigenschaft(int $value)
 * @property int    $kWarenkorbPos
 * @method int getKWarenkorbPos()
 * @method void setKWarenkorbPos(int $value)
 * @property int    $kEigenschaft
 * @method int getKEigenschaft()
 * @method void setKEigenschaft(int $value)
 * @property int    $kEigenschaftWert
 * @method int getKEigenschaftWert()
 * @method void setKEigenschaftWert(int $value)
 * @property string $cEigenschaftName
 * @method string getCEigenschaftName()
 * @method void setCEigenschaftName(string $value)
 * @property string $cEigenschaftWertName
 * @method string getCEigenschaftWertName()
 * @method void setCEigenschaftWertName(string $value)
 * @property string $cFreifeldWert
 * @method string getCFreifeldWert()
 * @method void setCFreifeldWert(string $value)
 * @property float  $fAufpreis
 * @method float getFAufpreis()
 * @method void setFAufpreis(float $value)
 */
final class CartItemAttributeModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'twarenkorbposeigenschaft';
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
        $attributes                       = [];
        $attributes['id']                 = DataAttribute::create('kWarenkorbPosEigenschaft', 'int', null, false, true);
        $attributes['cartItemID']         = DataAttribute::create('kWarenkorbPos', 'int', self::cast('0', 'int'), false);
        $attributes['attributeID']        = DataAttribute::create('kEigenschaft', 'int', self::cast('0', 'int'), false);
        $attributes['attributeValueID']   = DataAttribute::create('kEigenschaftWert', 'int', self::cast('0', 'int'), false);
        $attributes['attributeName']      = DataAttribute::create('cEigenschaftName', 'varchar', self::cast('', 'varchar'), false);
        $attributes['attributeValueName'] = DataAttribute::create('cEigenschaftWertName', 'varchar', self::cast('', 'varchar'), false);
        $attributes['freeTextValue']      = DataAttribute::create('cFreifeldWert', 'varchar', self::cast('', 'varchar'), false);
        $attributes['surcharge']          = DataAttribute::create('fAufpreis', 'double', self::cast('0', 'double'));

        return $attributes;
    }
}
