<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class TaxRateModel
 *
 * @property int   $kSteuersatz
 * @property int   $id
 * @property int   $kSteuerzone
 * @property int   $zoneID
 * @property int   $kSteuerklasse
 * @property int   $taxClassID
 * @property float $fSteuersatz
 * @property float $rate
 * @property int   $nPrio
 * @property int   $priority
 */
final class TaxRateModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tsteuersatz';
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
        $attributes               = [];
        $attributes['id']         = DataAttribute::create('kSteuersatz', 'int', self::cast('0', 'int'), false, true);
        $attributes['zoneID']     = DataAttribute::create('kSteuerzone', 'int');
        $attributes['taxClassID'] = DataAttribute::create('kSteuerklasse', 'int');
        $attributes['rate']       = DataAttribute::create('fSteuersatz', 'double');
        $attributes['priority']   = DataAttribute::create('nPrio', 'tinyint');

        return $attributes;
    }
}
