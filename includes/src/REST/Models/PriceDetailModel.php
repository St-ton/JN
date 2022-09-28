<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class PriceDetailModel
 * @OA\Schema(
 *     title="Price detail model",
 *     description="Price detail model",
 * )
 * @property int   $kPreisDetail
 * @property int   $kPreis
 * @property int   $nAnzahlAb
 * @property float $fVKNetto
 */
final class PriceDetailModel extends DataModel
{
    /**
     * @OA\Property(
     *   property="id",
     *   type="int",
     *   example=1,
     *   description="The primary key"
     * )
     * @OA\Property(
     *   property="priceID",
     *   type="int",
     *   example=1,
     *   description="The price ID"
     * )
     * @OA\Property(
     *   property="amountFrom",
     *   type="int",
     *   example=20,
     *   description="Quantity scle price start"
     * )
     * @OA\Property(
     *   property="netPrice",
     *   type="float",
     *   example=1.2345,
     *   description="The net price"
     * )
     */

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

    public function getKeyName(bool $realName = false): string
    {
        return $realName ? 'kPreis' : 'priceID';
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
            $attributes['priceID']    = DataAttribute::create('kPreis', 'int', null, false);
            $attributes['amountFrom'] = DataAttribute::create('nAnzahlAb', 'int', null, false);
            $attributes['netPrice']   = DataAttribute::create('fVKNetto', 'double', null, false);
        }

        return $attributes;
    }
}
