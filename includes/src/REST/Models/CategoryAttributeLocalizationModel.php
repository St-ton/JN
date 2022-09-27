<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CategoryAttributeLocalizationModel
 * @OA\Schema(
 *     title="Category attribute localization model",
 *     description="Category attribute localization model"
 * )
 * @property int    $kAttribut
 * @property int    $attributeID
 * @property int    $kSprache
 * @property int    $languageID
 * @property string $cName
 * @property string $name
 * @property string $cWert
 * @property string $value
 */
final class CategoryAttributeLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkategorieattributsprache';
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
            $attributes['attributeID'] = DataAttribute::create('kAttribut', 'int', null, false, true);
            $attributes['languageID']  = DataAttribute::create('kSprache', 'int', null, false, true);
            $attributes['name']        = DataAttribute::create('cName', 'varchar', null, false);
            $attributes['value']       = DataAttribute::create('cWert', 'mediumtext', null, false);
        }

        return $attributes;
    }
}
