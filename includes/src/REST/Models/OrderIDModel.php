<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class OrderIDModel
 *
 * @package JTL\REST\Models
 * @property string   $cId
 * @method string getCId()
 * @method void setCId(string $value)
 * @property int      $kBestellung
 * @method int getKBestellung()
 * @method void setKBestellung(int $value)
 * @property DateTime $dDatum
 * @method DateTime getDDatum()
 * @method void setDDatum(DateTime $value)
 */
final class OrderIDModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tbestellid';
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
        $this->registerGetter('dDatum', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dDatum', static function ($value) {
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
            $attributes            = [];
            $attributes['id']      = DataAttribute::create('cId', 'varchar', null, false, true);
            $attributes['orderID'] = DataAttribute::create('kBestellung', 'int', null, true, false);
            $attributes['date']    = DataAttribute::create('dDatum', 'datetime', null, true, false);
        }

        return $attributes;
    }
}
