<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class MeasurementUnitLocalizationModel
 *
 * @package JTL\REST\Models
 * @property int    $kMassEinheitSprache
 * @method int getKMassEinheitSprache()
 * @method void setKMassEinheitSprache(int $value)
 * @property int    $kMassEinheit
 * @method int getKMassEinheit()
 * @method void setKMassEinheit(int $value)
 * @property int    $kSprache
 * @method int getKSprache()
 * @method void setKSprache(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 */
final class MeasurementUnitLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmasseinheitsprache';
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
        $attributes               = [];
        $attributes['id']         = DataAttribute::create('kMassEinheitSprache', 'int', null, false, true);
        $attributes['unitID']     = DataAttribute::create('kMassEinheit', 'int', null, false);
        $attributes['languageID'] = DataAttribute::create('kSprache', 'int', null, false);
        $attributes['name']       = DataAttribute::create('cName', 'varchar', null, false);

        return $attributes;
    }
}
