<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class AttributeModel
 *
 * this actually also is a ProductAttributeModel - but the table name is "attribut" for some reason
 * it would be JTL\Catalog\Product\Artikel::AttributeAssoc
 *
 *
 * @property int    $kAttribut
 * @property int    $kArtikel
 * @property int    $nSort
 * @property string $cName
 * @property string $cStringWert
 * @property string $cTextWert
 */
final class AttributeModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tattribut';
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
            $attributes                = [];
            $attributes['id']          = DataAttribute::create('kAttribut', 'int', self::cast('0', 'int'), false, true);
            $attributes['productID']   = DataAttribute::create('kArtikel', 'int');
            $attributes['sort']        = DataAttribute::create('nSort', 'int', null, false);
            $attributes['name']        = DataAttribute::create('cName', 'varchar');
            $attributes['stringValue'] = DataAttribute::create('cStringWert', 'varchar', self::cast('', 'varchar'), false);
            $attributes['textValue']   = DataAttribute::create('cTextWert', 'mediumtext', null, false);

            $attributes['localization'] = DataAttribute::create('localization', AttributeLocalizationModel::class, null, true, false, 'kAttribut');

        }

        return $attributes;
    }
}
