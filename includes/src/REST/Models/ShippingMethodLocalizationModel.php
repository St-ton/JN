<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class ShippingMethodLocalizationModel
 *
 * @package JTL\REST\Models
 * @property int    $kVersandart
 * @method int getKVersandart()
 * @method void setKVersandart(int $value)
 * @property string $cISOSprache
 * @method string getCISOSprache()
 * @method void setCISOSprache(string $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $cLieferdauer
 * @method string getCLieferdauer()
 * @method void setCLieferdauer(string $value)
 * @property string $cHinweistext
 * @method string getCHinweistext()
 * @method void setCHinweistext(string $value)
 * @property string $cHinweistextShop
 * @method string getCHinweistextShop()
 * @method void setCHinweistextShop(string $value)
 */
final class ShippingMethodLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tversandartsprache';
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
    public function getAttributes(): array
    {
        $attributes                 = [];
        $attributes['id']           = DataAttribute::create('kVersandart', 'int', self::cast('0', 'int'), false, true);
        $attributes['code']         = DataAttribute::create('cISOSprache', 'varchar', null, false, true);
        $attributes['name']         = DataAttribute::create('cName', 'varchar');
        $attributes['deliveryTime'] = DataAttribute::create('cLieferdauer', 'varchar');
        $attributes['notice']       = DataAttribute::create('cHinweistext', 'mediumtext');
        $attributes['noticeShop']   = DataAttribute::create('cHinweistextShop', 'mediumtext');

        return $attributes;
    }
}
