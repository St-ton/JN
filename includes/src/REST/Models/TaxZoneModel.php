<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class TaxZoneModel
 *
 * @package JAPI\Models
 * @property int    $kSteuerzone
 * @property int    $id
 * @property string $cName
 * @property string $name
 */
final class TaxZoneModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tsteuerzone';
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
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes === null) {
            $attributes         = [];
            $attributes['id']   = DataAttribute::create('kSteuerzone', 'int', self::cast('0', 'int'), false, true);
            $attributes['name'] = DataAttribute::create('cName', 'varchar');
        }

        return $attributes;
    }
}
