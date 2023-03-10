<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class WarehouseLocalizationModel
 * @OA\Schema(
 *     title="Warehouse localization model",
 *     description="Warehouse localization model",
 * )
 * @property int    $kWarenlager
 * @property int    $kSprache
 * @property string $cName
 */
final class WarehouseLocalizationModel extends DataModel
{
    /**
     * @OA\Property(
     *   property="warehouseID",
     *   type="integer",
     *   example=55,
     *   description="The warehouse ID"
     * )
     * @OA\Property(
     *   property="languageID",
     *   type="integer",
     *   example=1,
     *   description="The languageID"
     * )
     * @OA\Property(
     *   property="name",
     *   type="string",
     *   example="Mein Lager in Hückelhoven",
     *   description="The warehouse's localized name"
     * )
     */
    /**
     * @return string
     * @see DataModel::getTableName()
     */
    public function getTableName(): string
    {
        return 'twarenlagersprache';
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
            $attributes                = [];
            $attributes['warehouseID'] = DataAttribute::create('kWarenlager', 'int', null, false, true);
            $attributes['languageID']  = DataAttribute::create('kSprache', 'int', null, false, true);
            $attributes['namee']       = DataAttribute::create('cName', 'varchar', null, false);
        }

        return $attributes;
    }
}
