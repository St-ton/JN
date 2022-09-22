<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CharacteristicValueLocalizationModel
 *
 * @property int    $kMerkmalWert
 * @property int    $characteristicValueID
 * @property int    $kSprache
 * @property int    $languageID
 * @property string $cWert
 * @property string $value
 * @property string $cSeo
 * @property string $slug
 * @property string $cMetaTitle
 * @property string $metaTitle
 * @property string $cMetaKeywords
 * @property string $metaKeywords
 * @property string $cMetaDescription
 * @property string $metaDescription
 * @property string $cBeschreibung
 * @property string $description
 */
final class CharacteristicValueLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmerkmalwertsprache';
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
        $attributes                          = [];
        $attributes['characteristicValueID'] = DataAttribute::create('kMerkmalWert', 'int', self::cast('0', 'int'), false, true);
        $attributes['languageID']            = DataAttribute::create('kSprache', 'int', self::cast('0', 'int'), false, true);
        $attributes['value']                 = DataAttribute::create('cWert', 'varchar');
        $attributes['slug']                  = DataAttribute::create('cSeo', 'varchar', '', false);
        $attributes['metaTitle']             = DataAttribute::create('cMetaTitle', 'varchar', '', false);
        $attributes['metaKeywords']          = DataAttribute::create('cMetaKeywords', 'varchar', '', false);
        $attributes['metaDescription']       = DataAttribute::create('cMetaDescription', 'mediumtext', '', false);
        $attributes['description']           = DataAttribute::create('cBeschreibung', 'mediumtext', '', false);

        return $attributes;
    }
}
