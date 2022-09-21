<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ProductDownloadModel
 *
 * @property int $kArtikel
 * @property int $productID
 * @property int $kDownload
 * @property int $downloadID
 */
final class ProductDownloadModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikeldownload';
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
            $attributes               = [];
            $attributes['productID']  = DataAttribute::create('kArtikel', 'int', null, false, true);
            $attributes['downloadID'] = DataAttribute::create('kDownload', 'int', null, false, true);
        }

        return $attributes;
    }
}
