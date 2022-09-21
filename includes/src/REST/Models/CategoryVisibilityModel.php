<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CategoryVisibilityModel
 *
 * @property int $categoryID
 * @property int $customerGroupID
 */
final class CategoryVisibilityModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkategoriesichtbarkeit';
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
            $attributes                    = [];
            $attributes['categoryID']      = DataAttribute::create('kKategorie', 'int', self::cast('0', 'int'), false, true);
            $attributes['customerGroupID'] = DataAttribute::create('kKundengruppe', 'int', self::cast('0', 'int'), false, true);
        }

        return $attributes;
    }
}
