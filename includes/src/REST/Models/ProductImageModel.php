<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductImageModel
 * @OA\Schema(
 *     title="Product image model",
 *     description="Product image model",
 * )
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
     * @OA\Property(
     *   property="id",
     *   type="int",
     *   example=99,
     *   description="The primary key"
     * )
     * @OA\Property(
     *   property="mainImageID",
     *   type="int",
     *   example=0,
     *   description="The main image ID"
     * )
     * @OA\Property(
     *   property="productID",
     *   type="int",
     *   example=99,
     *   description="The product's ID"
     * )
     * @OA\Property(
     *   property="imageID",
     *   type="int",
     *   example=0,
     *   description="The image ID"
     * )
     * @OA\Property(
     *   property="path",
     *   type="string",
     *   example="exampleproduct.jpg",
     *   description="The image path"
     * )
     * @OA\Property(
     *   property="imageNo",
     *   type="int",
     *   example=1,
     *   description="The image number"
     * )
     */

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
