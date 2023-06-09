<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ShippingMethodModel
 *
 * @package JTL\REST\Models
 * @property int    $kVersandart
 * @method int getKVersandart()
 * @method void setKVersandart(int $value)
 * @property int    $kVersandberechnung
 * @method int getKVersandberechnung()
 * @method void setKVersandberechnung(int $value)
 * @property string $cVersandklassen
 * @method string getCVersandklassen()
 * @method void setCVersandklassen(string $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $cLaender
 * @method string getCLaender()
 * @method void setCLaender(string $value)
 * @property string $cAnzeigen
 * @method string getCAnzeigen()
 * @method void setCAnzeigen(string $value)
 * @property string $cKundengruppen
 * @method string getCKundengruppen()
 * @method void setCKundengruppen(string $value)
 * @property string $cBild
 * @method string getCBild()
 * @method void setCBild(string $value)
 * @property string $eSteuer
 * @method string getESteuer()
 * @method void setESteuer(string $value)
 * @property int    $nSort
 * @method int getNSort()
 * @method void setNSort(int $value)
 * @property int    $nMinLiefertage
 * @method int getNMinLiefertage()
 * @method void setNMinLiefertage(int $value)
 * @property int    $nMaxLiefertage
 * @method int getNMaxLiefertage()
 * @method void setNMaxLiefertage(int $value)
 * @property float  $fPreis
 * @method float getFPreis()
 * @method void setFPreis(float $value)
 * @property float  $fVersandkostenfreiAbX
 * @method float getFVersandkostenfreiAbX()
 * @method void setFVersandkostenfreiAbX(float $value)
 * @property float  $fDeckelung
 * @method float getFDeckelung()
 * @method void setFDeckelung(float $value)
 * @property string $cNurAbhaengigeVersandart
 * @method string getCNurAbhaengigeVersandart()
 * @method void setCNurAbhaengigeVersandart(string $value)
 * @property string $cSendConfirmationMail
 * @method string getCSendConfirmationMail()
 * @method void setCSendConfirmationMail(string $value)
 * @property string $cIgnoreShippingProposal
 * @method string getCIgnoreShippingProposal()
 * @method void setCIgnoreShippingProposal(string $value)
 */
final class ShippingMethodModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tversandart';
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
    public function getAttributes(): array
    {
        $attributes                   = [];
        $attributes['id']             = DataAttribute::create('kVersandart', 'int', null, false, true);
        $attributes['calculationID']  = DataAttribute::create('kVersandberechnung', 'int');
        $attributes['methods']        = DataAttribute::create('cVersandklassen', 'varchar');
        $attributes['name']           = DataAttribute::create('cName', 'varchar');
        $attributes['countries']      = DataAttribute::create('cLaender', 'mediumtext');
        $attributes['show']           = DataAttribute::create('cAnzeigen', 'varchar');
        $attributes['customerGroups'] = DataAttribute::create('cKundengruppen', 'varchar', null, false);
        $attributes['image']          = DataAttribute::create('cBild', 'varchar', null, false);
        $attributes['tax']            = DataAttribute::create('eSteuer', 'enum', null, false);

        $attributes['sort']                   = DataAttribute::create(
            'nSort',
            'tinyint',
            self::cast('0', 'tinyint'),
            false
        );
        $attributes['minDeliveryDays']        = DataAttribute::create(
            'nMinLiefertage',
            'tinyint',
            self::cast('2', 'tinyint')
        );
        $attributes['maxDeliveryDays']        = DataAttribute::create(
            'nMaxLiefertage',
            'tinyint',
            self::cast('3', 'tinyint')
        );
        $attributes['price']                  = DataAttribute::create(
            'fPreis',
            'double',
            self::cast('0.00', 'double'),
            false
        );
        $attributes['shippingFreeFrom']       = DataAttribute::create(
            'fVersandkostenfreiAbX',
            'double',
            self::cast('0.00', 'double'),
            false
        );
        $attributes['cap']                    = DataAttribute::create(
            'fDeckelung',
            'double',
            self::cast('0.00', 'double'),
            false
        );
        $attributes['depending']              = DataAttribute::create(
            'cNurAbhaengigeVersandart',
            'char',
            self::cast('N', 'char'),
            false
        );
        $attributes['sendConfirmationMail']   = DataAttribute::create(
            'cSendConfirmationMail',
            'char',
            self::cast('Y', 'char'),
            false
        );
        $attributes['ignoreShippingProposal'] = DataAttribute::create(
            'cIgnoreShippingProposal',
            'char',
            self::cast('N', 'char'),
            false
        );
        $attributes['localization']           = DataAttribute::create(
            'localization',
            ShippingMethodLocalizationModel::class,
            null,
            true,
            false,
            'kVersandart'
        );

        return $attributes;
    }
}
