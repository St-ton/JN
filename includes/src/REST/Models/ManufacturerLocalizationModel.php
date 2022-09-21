<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ManufacturerLocalizationModel
 *
 * @property int    $manufacturerID
 * @property int    $kHersteller
 * @property int    $languageID
 * @property int    $kSprache
 * @property string $metaTitle
 * @property string $cMetaTitle
 * @property string $metaKeywords
 * @property string $cMetaKeywords
 * @property string $metaDescription
 * @property string $cMetaDescription
 * @property string $description
 * @property string $cBeschreibung
 */
final class ManufacturerLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'therstellersprache';
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
            $attributes['manufacturerID']  = DataAttribute::create('kHersteller', 'int', null, false, true);
            $attributes['languageID']      = DataAttribute::create('kSprache', 'int', null, false, true);
            $attributes['metaTitle']       = DataAttribute::create('cMetaTitle', 'mediumtext', self::cast('', 'varchar'), false);
            $attributes['metaKeywords']    = DataAttribute::create('cMetaKeywords', 'mediumtext', self::cast('', 'varchar'), false);
            $attributes['metaDescription'] = DataAttribute::create('cMetaDescription', 'mediumtext', self::cast('', 'varchar'), false);
            $attributes['description']     = DataAttribute::create('cBeschreibung', 'mediumtext', self::cast('', 'varchar'), false);
        }

        return $attributes;
    }
}
