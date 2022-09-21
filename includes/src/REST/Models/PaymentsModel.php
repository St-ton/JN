<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class PaymentsModel
 *
 * @package JTL\REST\Models
 * @property int $kZahlungseingang
 * @method int getKZahlungseingang()
 * @method void setKZahlungseingang(int $value)
 * @property int $kBestellung
 * @method int getKBestellung()
 * @method void setKBestellung(int $value)
 * @property string $cZahlungsanbieter
 * @method string getCZahlungsanbieter()
 * @method void setCZahlungsanbieter(string $value)
 * @property float $fBetrag
 * @method float getFBetrag()
 * @method void setFBetrag(float $value)
 * @property float $fZahlungsgebuehr
 * @method float getFZahlungsgebuehr()
 * @method void setFZahlungsgebuehr(float $value)
 * @property string $cISO
 * @method string getCISO()
 * @method void setCISO(string $value)
 * @property string $cEmpfaenger
 * @method string getCEmpfaenger()
 * @method void setCEmpfaenger(string $value)
 * @property string $cZahler
 * @method string getCZahler()
 * @method void setCZahler(string $value)
 * @property DateTime $dZeit
 * @method DateTime getDZeit()
 * @method void setDZeit(DateTime $value)
 * @property string $cHinweis
 * @method string getCHinweis()
 * @method void setCHinweis(string $value)
 * @property string $cAbgeholt
 * @method string getCAbgeholt()
 * @method void setCAbgeholt(string $value)
 */
final class PaymentsModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tzahlungseingang';
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
        $this->registerGetter('dZeit', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dZeit', static function ($value) {
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
            $attributes   = [];
            $attributes['kZahlungseingang'] = DataAttribute::create('kZahlungseingang', 'int', null, false, true);
            $attributes['kBestellung'] = DataAttribute::create('kBestellung', 'int', null, true, false);
            $attributes['cZahlungsanbieter'] = DataAttribute::create('cZahlungsanbieter', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['fBetrag'] = DataAttribute::create('fBetrag', 'double', null, true, false);
            $attributes['fZahlungsgebuehr'] = DataAttribute::create('fZahlungsgebuehr', 'double', null, true, false);
            $attributes['cISO'] = DataAttribute::create('cISO', 'varchar', null, false, false);
            $attributes['cEmpfaenger'] = DataAttribute::create('cEmpfaenger', 'varchar', null, true, false);
            $attributes['cZahler'] = DataAttribute::create('cZahler', 'varchar', null, true, false);
            $attributes['dZeit'] = DataAttribute::create('dZeit', 'datetime', null, true, false);
            $attributes['cHinweis'] = DataAttribute::create('cHinweis', 'varchar', null, false, false);
            $attributes['cAbgeholt'] = DataAttribute::create('cAbgeholt', 'char', self::cast('N', 'char'), false, false);
        }

        return $attributes;
    }
}
