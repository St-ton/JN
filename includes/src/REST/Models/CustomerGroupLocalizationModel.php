<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CustomerGroupLocalizationModel
 *
 * @package JTL\REST\Models
 * @property int    $kKundengruppe
 * @method int getKKundengruppe()
 * @method void setKKundengruppe(int $value)
 * @property int    $kSprache
 * @method int getKSprache()
 * @method void setKSprache(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 */
final class CustomerGroupLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkundengruppensprache';
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
        $attributes['customerGroupID'] = DataAttribute::create('kKundengruppe', 'int', self::cast('0', 'int'), false, true);
        $attributes['languageID']      = DataAttribute::create('kSprache', 'int', self::cast('0', 'int'), false, true);
        $attributes['name']            = DataAttribute::create('cName', 'varchar');

        return $attributes;
    }
}
