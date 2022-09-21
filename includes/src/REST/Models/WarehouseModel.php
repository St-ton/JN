<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class WarehouseModel
 *
 * @property int    $kWarenlager
 * @property string $cName
 * @property string $cKuerzel
 * @property string $cLagerTyp
 * @property string $cBeschreibung
 * @property string $cStrasse
 * @property string $cPLZ
 * @property string $cOrt
 * @property string $cLand
 * @property int    $nFulfillment
 * @property int    $nAktiv
 */
final class WarehouseModel extends DataModel
{
    /**
     * @return string
     * @see DataModel::getTableName()
     */
    public function getTableName(): string
    {
        return 'twarenlager';
    }

    /**
     * Setting of keyname is not supported!!!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @param string $keyName
     * @throws Exception
     * @see IDataModel::setKeyName()
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @return DataAttribute[]
     * @see IDataModel::getAttributes()
     *
     */
    public function getAttributes(): array
    {
        static $attributes = null;

        if ($attributes === null) {
            $attributes                 = [];
            $attributes['id']           = DataAttribute::create('kWarenlager', 'int', null, false, true);
            $attributes['name']         = DataAttribute::create('cName', 'varchar');
            $attributes['code']         = DataAttribute::create('cKuerzel', 'varchar');
            $attributes['type']         = DataAttribute::create('cLagerTyp', 'varchar');
            $attributes['description']  = DataAttribute::create('cBeschreibung', 'varchar');
            $attributes['street']       = DataAttribute::create('cStrasse', 'varchar');
            $attributes['zip']          = DataAttribute::create('cPLZ', 'varchar');
            $attributes['city']         = DataAttribute::create('cOrt', 'varchar');
            $attributes['country']      = DataAttribute::create('cLand', 'varchar');
            $attributes['fullfillment'] = DataAttribute::create('nFulfillment', 'tinyint');
            $attributes['active']       = DataAttribute::create('nAktiv', 'tinyint', self::cast('0', 'tinyint'));
        }

        return $attributes;
    }
}
