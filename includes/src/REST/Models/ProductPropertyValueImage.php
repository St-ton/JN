<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class ProductPropertyValueImage
 *
 * @package JTL\REST\Models
 * @property int    $kEigenschaftWertPict
 * @method int getKEigenschaftWertPict()
 * @method void setKEigenschaftWertPict(int $value)
 * @property int    $kEigenschaftWert
 * @method int getKEigenschaftWert()
 * @method void setKEigenschaftWert(int $value)
 * @property string $cPfad
 * @method string getCPfad()
 * @method void setCPfad(string $value)
 * @property string $cType
 * @method string getCType()
 * @method void setCType(string $value)
 */
final class ProductPropertyValueImage extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'teigenschaftwertpict';
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
        $attributes['id']              = DataAttribute::create('kEigenschaftWertPict', 'int', self::cast('0', 'int'), false, true);
        $attributes['propertyValueID'] = DataAttribute::create('kEigenschaftWert', 'int', self::cast('0', 'int'), false, false);
        $attributes['path']            = DataAttribute::create('cPfad', 'varchar', null, true, false);
        $attributes['type']            = DataAttribute::create('cType', 'char', null, true, false);

        return $attributes;
    }
}
