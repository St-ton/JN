<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ManufacturerLocalizationModel
 * @OA\Schema(
 *     title="Manufacturer localization model",
 *     description="Manufacturer localization model"
 * )
 * @OA\Property(
 *   property="id",
 *   type="integer",
 *   example=33,
 *   description="The manufcaturer ID"
 * )
 * @property int    $manufacturerID
 * @property int    $kHersteller
 * @OA\Property(
 *   property="languageID",
 *   type="integer",
 *   example=1,
 *   description="The language ID"
 * )
 * @property int    $languageID
 * @property int    $kSprache
 * @OA\Property(
 *   property="metaTitle",
 *   type="string",
 *   example="Example title for example manufacturer",
 *   description="The meta description"
 * )
 * @property string $metaTitle
 * @property string $cMetaTitle
 * @OA\Property(
 *   property="metaKeywords",
 *   type="string",
 *   example="example,keywords,for,this,manufacturer",
 *   description="The meta keywords"
 * )
 * @property string $metaKeywords
 * @property string $cMetaKeywords
 * @OA\Property(
 *   property="metaDescription",
 *   type="string",
 *   example="Example manufacturer meta description",
 *   description="The meta description"
 * )
 * @property string $metaDescription
 * @property string $cMetaDescription
 * @OA\Property(
 *   property="description",
 *   type="string",
 *   example="Example manufacturer description",
 *   description="The description"
 * )
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

        if ($attributes !== null) {
            return $attributes;
        }
        $attributes                    = [];
        $attributes['manufacturerID']  = DataAttribute::create('kHersteller', 'int', null, false, true);
        $attributes['languageID']      = DataAttribute::create('kSprache', 'int', null, false, true);
        $attributes['metaTitle']       = DataAttribute::create(
            'cMetaTitle',
            'mediumtext',
            self::cast('', 'varchar'),
            false
        );
        $attributes['metaKeywords']    = DataAttribute::create(
            'cMetaKeywords',
            'mediumtext',
            self::cast('', 'varchar'),
            false
        );
        $attributes['metaDescription'] = DataAttribute::create(
            'cMetaDescription',
            'mediumtext',
            self::cast('', 'varchar'),
            false
        );
        $attributes['description']     = DataAttribute::create(
            'cBeschreibung',
            'mediumtext',
            self::cast('', 'varchar'),
            false
        );

        return $attributes;
    }
}
