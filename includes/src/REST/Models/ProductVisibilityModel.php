<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductVisibilityModel
 *
 * @property int $kArtikel
 * @property int $productID
 * @property int $kKundengruppe
 * @property int $customerGroupID
 */
final class ProductVisibilityModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelsichtbarkeit';
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

        if ($attributes !== null) {
            return $attributes;
        }
        $attributes                    = [];
        $attributes['productID']       = DataAttribute::create('kArtikel', 'int', self::cast('0', 'int'), false, true);
        $attributes['customerGroupID'] = DataAttribute::create('kKundengruppe', 'int', self::cast('0', 'int'), false, true);

        return $attributes;
    }
}
