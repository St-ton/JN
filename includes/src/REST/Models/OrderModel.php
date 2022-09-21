<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class OrderModel
 *
 * @package JTL\REST\Models
 * @property int      $kBestellung
 * @method int getKBestellung()
 * @method void setKBestellung(int $value)
 * @property int      $kWarenkorb
 * @method int getKWarenkorb()
 * @method void setKWarenkorb(int $value)
 * @property int      $kKunde
 * @method int getKKunde()
 * @method void setKKunde(int $value)
 * @property int      $kLieferadresse
 * @method int getKLieferadresse()
 * @method void setKLieferadresse(int $value)
 * @property int      $kRechnungsadresse
 * @method int getKRechnungsadresse()
 * @method void setKRechnungsadresse(int $value)
 * @property int      $kZahlungsart
 * @method int getKZahlungsart()
 * @method void setKZahlungsart(int $value)
 * @property int      $kVersandart
 * @method int getKVersandart()
 * @method void setKVersandart(int $value)
 * @property int      $kSprache
 * @method int getKSprache()
 * @method void setKSprache(int $value)
 * @property int      $kWaehrung
 * @method int getKWaehrung()
 * @method void setKWaehrung(int $value)
 * @property int      $nZahlungsTyp
 * @method int getNZahlungsTyp()
 * @method void setNZahlungsTyp(int $value)
 * @property float    $fGuthaben
 * @method float getFGuthaben()
 * @method void setFGuthaben(float $value)
 * @property float    $fGesamtsumme
 * @method float getFGesamtsumme()
 * @method void setFGesamtsumme(float $value)
 * @property string   $cSession
 * @method string getCSession()
 * @method void setCSession(string $value)
 * @property string   $cVersandartName
 * @method string getCVersandartName()
 * @method void setCVersandartName(string $value)
 * @property string   $cZahlungsartName
 * @method string getCZahlungsartName()
 * @method void setCZahlungsartName(string $value)
 * @property string   $cBestellNr
 * @method string getCBestellNr()
 * @method void setCBestellNr(string $value)
 * @property string   $cVersandInfo
 * @method string getCVersandInfo()
 * @method void setCVersandInfo(string $value)
 * @property int      $nLongestMinDelivery
 * @method int getNLongestMinDelivery()
 * @method void setNLongestMinDelivery(int $value)
 * @property int      $nLongestMaxDelivery
 * @method int getNLongestMaxDelivery()
 * @method void setNLongestMaxDelivery(int $value)
 * @property DateTime $dVersandDatum
 * @method DateTime getDVersandDatum()
 * @method void setDVersandDatum(DateTime $value)
 * @property DateTime $dBezahltDatum
 * @method DateTime getDBezahltDatum()
 * @method void setDBezahltDatum(DateTime $value)
 * @property DateTime $dBewertungErinnerung
 * @method DateTime getDBewertungErinnerung()
 * @method void setDBewertungErinnerung(DateTime $value)
 * @property string   $cTracking
 * @method string getCTracking()
 * @method void setCTracking(string $value)
 * @property string   $cKommentar
 * @method string getCKommentar()
 * @method void setCKommentar(string $value)
 * @property string   $cLogistiker
 * @method string getCLogistiker()
 * @method void setCLogistiker(string $value)
 * @property string   $cTrackingURL
 * @method string getCTrackingURL()
 * @method void setCTrackingURL(string $value)
 * @property string   $cIP
 * @method string getCIP()
 * @method void setCIP(string $value)
 * @property string   $cAbgeholt
 * @method string getCAbgeholt()
 * @method void setCAbgeholt(string $value)
 * @property string   $cStatus
 * @method string getCStatus()
 * @method void setCStatus(string $value)
 * @property DateTime $dErstellt
 * @method DateTime getDErstellt()
 * @method void setDErstellt(DateTime $value)
 * @property float    $fWaehrungsFaktor
 * @method float getFWaehrungsFaktor()
 * @method void setFWaehrungsFaktor(float $value)
 * @property string   $cPUIZahlungsdaten
 * @method string getCPUIZahlungsdaten()
 * @method void setCPUIZahlungsdaten(string $value)
 */
final class OrderModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tbestellung';
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
        $this->registerGetter('dVersandDatum', static function ($value, $default) {
            return ModelHelper::fromStrToDate($value, $default);
        });
        $this->registerSetter('dVersandDatum', static function ($value) {
            return ModelHelper::fromDateToStr($value);
        });
        $this->registerGetter('dBezahltDatum', static function ($value, $default) {
            return ModelHelper::fromStrToDate($value, $default);
        });
        $this->registerSetter('dBezahltDatum', static function ($value) {
            return ModelHelper::fromDateToStr($value);
        });
        $this->registerGetter('dBewertungErinnerung', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dBewertungErinnerung', static function ($value) {
            return ModelHelper::fromDateTimeToStr($value);
        });
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
            $attributes                             = [];
            $attributes['id']                       = DataAttribute::create('kBestellung', 'int', null, false, true);
            $attributes['cartID']                   = DataAttribute::create('kWarenkorb', 'int', self::cast('0', 'int'), false, false);
            $attributes['customerID']               = DataAttribute::create('kKunde', 'int', self::cast('0', 'int'), false, false);
            $attributes['deliveryAddressID']        = DataAttribute::create('kLieferadresse', 'int', self::cast('0', 'int'), false, false);
            $attributes['billingAddressID']         = DataAttribute::create('kRechnungsadresse', 'int', null, false, false);
            $attributes['paymentMethodID']          = DataAttribute::create('kZahlungsart', 'int', self::cast('0', 'int'), false, false);
            $attributes['shippingMethodID']         = DataAttribute::create('kVersandart', 'int', null, false, false);
            $attributes['languageID']               = DataAttribute::create('kSprache', 'int', self::cast('0', 'int'), false, false);
            $attributes['currencyID']               = DataAttribute::create('kWaehrung', 'int', self::cast('0', 'int'), false, false);
            $attributes['paymentType']              = DataAttribute::create('nZahlungsTyp', 'int', self::cast('0', 'int'), false, false);
            $attributes['balance']                  = DataAttribute::create('fGuthaben', 'double', self::cast('0.0000', 'double'), false, false);
            $attributes['total']                    = DataAttribute::create('fGesamtsumme', 'double', self::cast('0', 'double'), false, false);
            $attributes['sessionID']                = DataAttribute::create('cSession', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['shippingMethodName']       = DataAttribute::create('cVersandartName', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['paymentMethodName']        = DataAttribute::create('cZahlungsartName', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['orderNO']                  = DataAttribute::create('cBestellNr', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['shippingInfo']             = DataAttribute::create('cVersandInfo', 'varchar', null, true, false);
            $attributes['longestMinDelivery']       = DataAttribute::create('nLongestMinDelivery', 'int', self::cast('0', 'int'), false, false);
            $attributes['longestMaxDelivery']       = DataAttribute::create('nLongestMaxDelivery', 'int', self::cast('0', 'int'), false, false);
            $attributes['shippingDate']             = DataAttribute::create('dVersandDatum', 'date', null, true, false);
            $attributes['paymentDate']              = DataAttribute::create('dBezahltDatum', 'date', null, true, false);
            $attributes['reviewReminder']           = DataAttribute::create('dBewertungErinnerung', 'datetime', null, true, false);
            $attributes['trackingID']               = DataAttribute::create('cTracking', 'varchar', null, true, false);
            $attributes['comment']                  = DataAttribute::create('cKommentar', 'mediumtext', null, true, false);
            $attributes['logistics']                = DataAttribute::create('cLogistiker', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['trackingURL']              = DataAttribute::create('cTrackingURL', 'varchar', self::cast('', 'varchar'), false, false);
            $attributes['ipAddress']                = DataAttribute::create('cIP', 'varchar', null, false, false);
            $attributes['fetched']                  = DataAttribute::create('cAbgeholt', 'char', self::cast('N', 'char'), true, false);
            $attributes['state']                    = DataAttribute::create('cStatus', 'char', null, true, false);
            $attributes['created']                  = DataAttribute::create('dErstellt', 'datetime', null, true, false);
            $attributes['currencyConversionFactor'] = DataAttribute::create('fWaehrungsFaktor', 'float', self::cast('1', 'float'), false, false);
            $attributes['puidPaymentData']          = DataAttribute::create('cPUIZahlungsdaten', 'mediumtext', null, true, false);

            $attributes['attributes'] = DataAttribute::create('attributes', OrderAttributeModel::class, null, true, false, 'kBestellung');
        }

        return $attributes;
    }
}
