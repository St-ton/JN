<?php declare(strict_types=1);

namespace JTL\Model;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class TCheckboxModel
 * This class is generated by shopcli model:create
 *
 * @package JTL\ChangeMe
 * @property int $kCheckBox
 * @method int getKCheckBox()
 * @method void setKCheckBox(int $value)
 * @property int $kLink
 * @method int getKLink()
 * @method void setKLink(int $value)
 * @property int $kCheckBoxFunktion
 * @method int getKCheckBoxFunktion()
 * @method void setKCheckBoxFunktion(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $cKundengruppe
 * @method string getCKundengruppe()
 * @method void setCKundengruppe(string $value)
 * @property string $cAnzeigeOrt
 * @method string getCAnzeigeOrt()
 * @method void setCAnzeigeOrt(string $value)
 * @property int $nAktiv
 * @method int getNAktiv()
 * @method void setNAktiv(int $value)
 * @property int $nPflicht
 * @method int getNPflicht()
 * @method void setNPflicht(int $value)
 * @property int $nLogging
 * @method int getNLogging()
 * @method void setNLogging(int $value)
 * @property int $nSort
 * @method int getNSort()
 * @method void setNSort(int $value)
 * @property DateTime $dErstellt
 * @method DateTime getDErstellt()
 * @method void setDErstellt(DateTime $value)
 * @property int $nInternal
 * @method int getNInternal()
 * @method void setNInternal(int $value)
 */
final class TCheckboxModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tcheckbox';
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
            $attributes                      = [];
            $attributes['kCheckBox']         = DataAttribute::create('kCheckBox', 'int', null, false, true);
            $attributes['kLink']             = DataAttribute::create('kLink', 'int', 0, true, false);
            $attributes['kCheckBoxFunktion'] = DataAttribute::create('kCheckBoxFunktion', 'int', null, true, false);
            $attributes['cName']             = DataAttribute::create('cName', 'varchar', null, false, false);
            $attributes['cKundengruppe']     = DataAttribute::create('cKundengruppe', 'varchar', null, false, false);
            $attributes['cAnzeigeOrt']       = DataAttribute::create('cAnzeigeOrt', 'varchar', null, false, false);
            $attributes['nAktiv']            = DataAttribute::create('nAktiv', 'tinyint', null, false, false);
            $attributes['nPflicht']          = DataAttribute::create('nPflicht', 'tinyint', null, false, false);
            $attributes['nLogging']          = DataAttribute::create('nLogging', 'tinyint', null, false, false);
            $attributes['nSort']             = DataAttribute::create('nSort', 'int', null, false, false);
            $attributes['dErstellt']         = DataAttribute::create('dErstellt', 'datetime', null, false, false);
            $attributes['nInternal']         =
                DataAttribute::create('nInternal', 'tinyint', self::cast('0', 'tinyint'), true, false);
        }

        return $attributes;
    }
}
