<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CustomerAttributeModel
 *
 * @package JTL\REST\Models
 * @property int    $kKundenAttribut
 * @method int getKKundenAttribut()
 * @method void setKKundenAttribut(int $value)
 * @property int    $kKunde
 * @method int getKKunde()
 * @method void setKKunde(int $value)
 * @property int    $kKundenfeld
 * @method int getKKundenfeld()
 * @method void setKKundenfeld(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $cWert
 * @method string getCWert()
 * @method void setCWert(string $value)
 */
final class CustomerAttributeModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkundenattribut';
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
            $attributes                    = [];
            $attributes['id']              = DataAttribute::create('kKundenAttribut', 'int', null, false, true);
            $attributes['customerID']      = DataAttribute::create('kKunde', 'int', null, true, false);
            $attributes['customerFieldID'] = DataAttribute::create('kKundenfeld', 'int', null, false, false);
            $attributes['name']            = DataAttribute::create('cName', 'varchar', null, true, false);
            $attributes['value']           = DataAttribute::create('cWert', 'varchar', null, true, false);
        }

        return $attributes;
    }
}
