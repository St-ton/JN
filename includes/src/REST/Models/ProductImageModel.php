<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductImageModel
 *
 * @property int    $kArtikelPict
 * @property int    $id
 * @property int    $kMainArtikelBild
 * @property int    $mainImageID
 * @property int    $kArtikel
 * @property int    $productID
 * @property int    $kBild
 * @property int    $imageID
 * @property string $cPfad
 * @property string $path
 * @property int    $nNr
 * @property int    $imageNo
 */
final class ProductImageModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelpict';
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
        $attributes['id']          = DataAttribute::create('kArtikelPict', 'int', self::cast('0', 'int'), false, true);
        $attributes['mainImageID'] = DataAttribute::create('kMainArtikelBild', 'int', self::cast('0', 'int'), false);
        $attributes['productID']   = DataAttribute::create('kArtikel', 'int', self::cast('0', 'int'), false);
        $attributes['imageID']     = DataAttribute::create('kBild', 'int', self::cast('0', 'int'), false);
        $attributes['path']        = DataAttribute::create('cPfad', 'varchar');
        $attributes['imageNo']     = DataAttribute::create('nNr', 'tinyint');

        return $attributes;
    }

    /**
     * @return int
     */
    public function getNewID(): int
    {
        return ($this->getDB()?->getSingleInt(
            'SELECT MAX(kArtikelPict) AS newID FROM ' . $this->getTableName(),
            'newID'
        )) + 1;
    }

    /**
     * @return int
     */
    public function getNewImageID(): int
    {
        return ($this->getDB()?->getSingleInt(
            'SELECT MAX(kBild) AS newID FROM ' . $this->getTableName(),
            'newID'
        )) + 1;
    }
}
