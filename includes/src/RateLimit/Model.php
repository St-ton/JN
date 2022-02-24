<?php declare(strict_types=1);

namespace JTL\RateLimit;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class Model
 *
 * @package JTL\RateLimit
 * @property int      $reference
 * @method int getReference()
 * @method void setReference(int $value)
 * @property int      $kFloodProtect
 * @method int getKFloodProtect()
 * @method void setKFloodProtect(int $value)
 * @property string   $cIP
 * @method string getCIP()
 * @method void setCIP(string $value)
 * @property string   $cTyp
 * @method string getCTyp()
 * @method void setCTyp(string $value)
 * @property DateTime $dErstellt
 * @method DateTime getDErstellt()
 * @method void setDErstellt(DateTime $value)
 */
final class Model extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tfloodprotect';
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->kFloodProtect;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->kFloodProtect = $id;
    }

    /**
     * @return string
     */
    public function getIP(): string
    {
        return $this->cIP;
    }

    /**
     * @param string $ip
     */
    public function setIP(string $ip): void
    {
        $this->cIP = $ip;
    }

    /**
     * @return string
     */
    public function getProtectedType(): string
    {
        return $this->cTyp;
    }

    /**
     * @param string $type
     */
    public function setProtectedType(string $type): void
    {
        $this->cTyp = $type;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->dErstellt = $time;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->dErstellt;
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
        $this->registerGetter('dErstellt', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dErstellt', static function ($value) {
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
            $attributes                  = [];
            $attributes['kFloodProtect'] = DataAttribute::create('kFloodProtect', 'int', null, false, true);
            $attributes['cIP']           = DataAttribute::create('cIP', 'varchar');
            $attributes['cTyp']          = DataAttribute::create('cTyp', 'varchar');
            $attributes['dErstellt']     = DataAttribute::create('dErstellt', 'datetime');
            $attributes['reference']     = DataAttribute::create('reference', 'int');
        }

        return $attributes;
    }
}
