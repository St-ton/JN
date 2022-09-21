<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class PriceDetailModel
 *
 * @property int   $kPreisDetail
 * @property int   $kPreis
 * @property int   $nAnzahlAb
 * @property float $fVKNetto
 */
final class PriceDetailModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tpreisdetail';
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
            $attributes['id']         = DataAttribute::create('kPreisDetail', 'int', null, false, true);
            $attributes['kPreis']    = DataAttribute::create('kPreis', 'int', null, false);
            $attributes['amountFrom'] = DataAttribute::create('nAnzahlAb', 'int', null, false);
            $attributes['netPrice']   = DataAttribute::create('fVKNetto', 'double', null, false);
        }

        return $attributes;
    }
}
