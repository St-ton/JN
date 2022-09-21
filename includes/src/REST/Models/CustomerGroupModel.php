<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CustomerGroupModel
 *
 * @package JTL\REST\Models
 * @property int    $kKundengruppe
 * @method int getKKundengruppe()
 * @method void setKKundengruppe(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property float  $fRabatt
 * @method float getFRabatt()
 * @method void setFRabatt(float $value)
 * @property string $cStandard
 * @method string getCStandard()
 * @method void setCStandard(string $value)
 * @property string $cShopLogin
 * @method string getCShopLogin()
 * @method void setCShopLogin(string $value)
 * @property int    $nNettoPreise
 * @method int getNNettoPreise()
 * @method void setNNettoPreise(int $value)
 */
final class CustomerGroupModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkundengruppe';
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
            $attributes              = [];
            $attributes['id']        = DataAttribute::create('kKundengruppe', 'int', self::cast('0', 'int'), false, true);
            $attributes['name']      = DataAttribute::create('cName', 'varchar', null, true, false);
            $attributes['discount']  = DataAttribute::create('fRabatt', 'double', null, true, false);
            $attributes['default']   = DataAttribute::create('cStandard', 'char', self::cast('N', 'char'), true, false);
            $attributes['shopLogin'] = DataAttribute::create('cShopLogin', 'char', self::cast('N', 'char'), false, false);
            $attributes['net']       = DataAttribute::create('nNettoPreise', 'tinyint', self::cast('0', 'tinyint'), false, false);
        }

        return $attributes;
    }
}
