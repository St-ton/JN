<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class OrderStateModel
 *
 * @package JTL\REST\Models
 * @property int      $kBestellung
 * @method int getKBestellung()
 * @method void setKBestellung(int $value)
 * @property string   $cUID
 * @method string getCUID()
 * @method void setCUID(string $value)
 * @property DateTime $dDatum
 * @method DateTime getDDatum()
 * @method void setDDatum(DateTime $value)
 * @property int      $failedAttempts
 * @method int getFailedAttempts()
 * @method void setFailedAttempts(int $value)
 */
final class OrderStateModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tbestellstatus';
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
            $attributes                   = [];
            $attributes['orderID']        = DataAttribute::create('kBestellung', 'int', null, false, true);
            $attributes['uid']            = DataAttribute::create('cUID', 'varchar', null, true, false);
            $attributes['date']           = DataAttribute::create('dDatum', 'datetime', null, true, false);
            $attributes['failedAttempts'] = DataAttribute::create('failedAttempts', 'int', self::cast('0', 'int'), false, false);
        }

        return $attributes;
    }
}
