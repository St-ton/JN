<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class MinimumPurchaseQuantityModel
 *
 * @package JTL\REST\Models
 * @property int   $kArtikel
 * @method int getKArtikel()
 * @method void setKArtikel(int $value)
 * @property int   $kKundengruppe
 * @method int getKKundengruppe()
 * @method void setKKundengruppe(int $value)
 * @property float $fMindestabnahme
 * @method float getFMindestabnahme()
 * @method void setFMindestabnahme(float $value)
 * @property float $fIntervall
 * @method float getFIntervall()
 * @method void setFIntervall(float $value)
 */
final class MinimumPurchaseQuantityModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelabnahme';
    }

    /**
     * Setting of keyname is not supported!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @inheritdoc
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @inheritdoc
     */
    protected function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();
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
        $attributes                        = [];
        $attributes['productID']           = DataAttribute::create('kArtikel', 'int', null, false, true);
        $attributes['customerGroupID']     = DataAttribute::create('kKundengruppe', 'int', self::cast('0', 'int'), false, true);
        $attributes['minimumOrderQty']     = DataAttribute::create('fMindestabnahme', 'double', self::cast('0', 'double'));
        $attributes['permissibleOrderQty'] = DataAttribute::create('fIntervall', 'double', self::cast('0', 'double'));

        return $attributes;
    }
}
