<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CharacteristicValueImageModel
 *
 * @package JTL\REST\Models
 * @property int    $kMerkmalWert
 * @method int getKMerkmalWert()
 * @method void setKMerkmalWert(int $value)
 * @property string $cBildpfad
 * @method string getCBildpfad()
 * @method void setCBildpfad(string $value)
 */
final class CharacteristicValueImageModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmerkmalwertbild';
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
            $attributes         = [];
            $attributes['id']   = DataAttribute::create('kMerkmalWert', 'int', null, false);
            $attributes['path'] = DataAttribute::create('cBildpfad', 'varchar', null, false);
        }

        return $attributes;
    }
}
