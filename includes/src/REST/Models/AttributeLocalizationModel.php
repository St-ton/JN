<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class AttributeLocalizationModel
 *
 * @property int    $id
 * @property int    $languageID
 * @property string $name
 * @property string $stringValue
 * @property string $textvalue
 */
final class AttributeLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tattributsprache';
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
        $attributes                = [];
        $attributes['id']          = DataAttribute::create('kAttribut', 'int', self::cast('0', 'int'), false, true);
        $attributes['languageID']  = DataAttribute::create('kSprache', 'tinyint', self::cast('0', 'tinyint'), false, true);
        $attributes['name']        = DataAttribute::create('cName', 'varchar');
        $attributes['stringValue'] = DataAttribute::create('cStringWert', 'varchar', self::cast('', 'varchar'), false);
        $attributes['textValue']   = DataAttribute::create('cTextWert', 'mediumtext', null, false);

        return $attributes;
    }
}
