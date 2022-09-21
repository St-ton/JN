<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductCharacteristicModel
 *
 * @property int $kMerkmal
 * @property int $id
 * @property int $kMerkmalWert
 * @property int $valueID
 * @property int $kArtikel
 * @property int $productID
 */
final class ProductCharacteristicModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelmerkmal';
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
            $attributes              = [];
            $attributes['id']        = DataAttribute::create('kMerkmal', 'int');
            $attributes['valueID']   = DataAttribute::create('kMerkmalWert', 'int');
            $attributes['productID'] = DataAttribute::create('kArtikel', 'int');
        }

        return $attributes;
    }
}
