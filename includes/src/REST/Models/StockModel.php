<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class StockModel
 *
 * @package JTL\REST\Models
 * @property int      $kArtikel
 * @method int getKArtikel()
 * @method void setKArtikel(int $value)
 * @property int      $kWarenlager
 * @method int getKWarenlager()
 * @method void setKWarenlager(int $value)
 * @property float    $fBestand
 * @method float getFBestand()
 * @method void setFBestand(float $value)
 * @property float    $fZulauf
 * @method float getFZulauf()
 * @method void setFZulauf(float $value)
 * @property DateTime $dZulaufDatum
 * @method DateTime getDZulaufDatum()
 * @method void setDZulaufDatum(DateTime $value)
 */
final class StockModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tartikelwarenlager';
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
        $this->registerGetter('dZulaufDatum', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dZulaufDatum', static function ($value) {
            return ModelHelper::fromDateTimeToStr($value);
        });
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes === null) {
            $attributes                 = [];
            $attributes['productID']    = DataAttribute::create('kArtikel', 'int', null, false, true);
            $attributes['warehouseID']  = DataAttribute::create('kWarenlager', 'int', null, false, true);
            $attributes['stock']        = DataAttribute::create('fBestand', 'double', null, false);
            $attributes['procured']     = DataAttribute::create('fZulauf', 'double', null, false);
            $attributes['procuredDate'] = DataAttribute::create('dZulaufDatum', 'datetime');
        }

        return $attributes;
    }
}
