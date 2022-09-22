<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductCategories
 *
 * @package JTL\ChangeMe
 * @property int $kKategorieArtikel
 * @property int $id
 * @property int $kArtikel
 * @property int $productID
 * @property int $kKategorie
 * @property int $categoryID
 */
final class ProductCategories extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkategorieartikel';
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
        $attributes               = [];
        $attributes['id']         = DataAttribute::create('kKategorieArtikel', 'int', self::cast('0', 'int'), false, true);
        $attributes['productID']  = DataAttribute::create('kArtikel', 'int');
        $attributes['categoryID'] = DataAttribute::create('kKategorie', 'int');

        return $attributes;
    }
}
