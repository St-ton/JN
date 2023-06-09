<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class MeasurementUnitModel
 *
 * @package JTL\REST\Models
 * @property int    $kMassEinheit
 * @method int getKMassEinheit()
 * @method void setKMassEinheit(int $value)
 * @property string $cCode
 * @method string getCCode()
 * @method void setCCode(string $value)
 */
final class MeasurementUnitModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmasseinheit';
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
        $attributes['id']           = DataAttribute::create('kMassEinheit', 'int', null, false, true);
        $attributes['code']         = DataAttribute::create('cCode', 'varchar', null, false);
        $attributes['localization'] = DataAttribute::create(
            'localization',
            MeasurementUnitLocalizationModel::class,
            null,
            true,
            false,
            'kMassEinheit'
        );

        return $attributes;
    }
}
