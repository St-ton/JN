<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductLocalizationModel
 * This class is generated by shopcli model:create
 *
 * @property int    $kArtikel
 * @property int    $productID
 * @property int    $kSprache
 * @property int    $languageID
 * @property string $cSeo
 * @property string $slug
 * @property string $cName
 * @property string $name
 * @property string $cBeschreibung
 * @property string $description
 * @property string $cKurzBeschreibung
 * @property string $shortDescription
 */
final class ProductLocalizationModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelsprache';
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
            $attributes                     = [];
            $attributes['productID']        = DataAttribute::create('kArtikel', 'int', self::cast('0', 'int'), false, true);
            $attributes['languageID']       = DataAttribute::create('kSprache', 'tinyint', self::cast('0', 'tinyint'), false, true);
            $attributes['slug']             = DataAttribute::create('cSeo', 'varchar', self::cast('', 'varchar'), false);
            $attributes['name']             = DataAttribute::create('cName', 'varchar');
            $attributes['description']      = DataAttribute::create('cBeschreibung', 'mediumtext');
            $attributes['shortDescription'] = DataAttribute::create('cKurzBeschreibung', 'mediumtext');
        }

        return $attributes;
    }
}
