<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CartItemModel
 *
 * @package JTL\REST\Models
 * @property int    $kWarenkorbPos
 * @method int getKWarenkorbPos()
 * @method void setKWarenkorbPos(int $value)
 * @property int    $kWarenkorb
 * @method int getKWarenkorb()
 * @method void setKWarenkorb(int $value)
 * @property int    $kArtikel
 * @method int getKArtikel()
 * @method void setKArtikel(int $value)
 * @property int    $kVersandklasse
 * @method int getKVersandklasse()
 * @method void setKVersandklasse(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $cLieferstatus
 * @method string getCLieferstatus()
 * @method void setCLieferstatus(string $value)
 * @property string $cArtNr
 * @method string getCArtNr()
 * @method void setCArtNr(string $value)
 * @property string $cEinheit
 * @method string getCEinheit()
 * @method void setCEinheit(string $value)
 * @property float  $fPreisEinzelNetto
 * @method float getFPreisEinzelNetto()
 * @method void setFPreisEinzelNetto(float $value)
 * @property float  $fPreis
 * @method float getFPreis()
 * @method void setFPreis(float $value)
 * @property float  $fMwSt
 * @method float getFMwSt()
 * @method void setFMwSt(float $value)
 * @property float  $nAnzahl
 * @method float getNAnzahl()
 * @method void setNAnzahl(float $value)
 * @property int    $nPosTyp
 * @method int getNPosTyp()
 * @method void setNPosTyp(int $value)
 * @property string $cHinweis
 * @method string getCHinweis()
 * @method void setCHinweis(string $value)
 * @property string $cUnique
 * @method string getCUnique()
 * @method void setCUnique(string $value)
 * @property string $cResponsibility
 * @method string getCResponsibility()
 * @method void setCResponsibility(string $value)
 * @property int    $kKonfigitem
 * @method int getKKonfigitem()
 * @method void setKKonfigitem(int $value)
 * @property int    $kBestellpos
 * @method int getKBestellpos()
 * @method void setKBestellpos(int $value)
 * @property float  $fLagerbestandVorAbschluss
 * @method float getFLagerbestandVorAbschluss()
 * @method void setFLagerbestandVorAbschluss(float $value)
 * @property int    $nLongestMinDelivery
 * @method int getNLongestMinDelivery()
 * @method void setNLongestMinDelivery(int $value)
 * @property int    $nLongestMaxDelivery
 * @method int getNLongestMaxDelivery()
 * @method void setNLongestMaxDelivery(int $value)
 */
final class CartItemModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'twarenkorbpos';
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
        $attributes                       = [];
        $attributes['id']                 = DataAttribute::create('kWarenkorbPos', 'int', null, false, true);
        $attributes['cartID']             = DataAttribute::create('kWarenkorb', 'int', self::cast('0', 'int'), false);
        $attributes['productID']          = DataAttribute::create('kArtikel', 'int', self::cast('0', 'int'), false);
        $attributes['shippingClassID']    = DataAttribute::create(
            'kVersandklasse',
            'int',
            self::cast('1', 'int'),
            false
        );
        $attributes['name']               = DataAttribute::create('cName', 'varchar');
        $attributes['deliveryState']      = DataAttribute::create(
            'cLieferstatus',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['sku']                = DataAttribute::create(
            'cArtNr',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['unit']               = DataAttribute::create(
            'cEinheit',
            'varchar',
            self::cast('', 'varchar'),
            false
        );
        $attributes['netSinglePrice']     = DataAttribute::create(
            'fPreisEinzelNetto',
            'double',
            self::cast('0', 'double'),
            false
        );
        $attributes['price']              = DataAttribute::create('fPreis', 'double', self::cast('0', 'double'), false);
        $attributes['taxPercent']         = DataAttribute::create('fMwSt', 'float');
        $attributes['qty']                = DataAttribute::create(
            'nAnzahl',
            'double',
            self::cast('0.0000', 'double'),
            false
        );
        $attributes['posType']            = DataAttribute::create(
            'nPosTyp',
            'tinyint',
            self::cast('1', 'tinyint'),
            false
        );
        $attributes['notice']             = DataAttribute::create('cHinweis', 'varchar', null, false);
        $attributes['unique']             = DataAttribute::create('cUnique', 'varchar', null, false);
        $attributes['responsibility']     = DataAttribute::create(
            'cResponsibility',
            'varchar',
            self::cast('core', 'varchar'),
            false
        );
        $attributes['configItemID']       = DataAttribute::create('kKonfigitem', 'int', self::cast('0', 'int'), false);
        $attributes['orderItemID']        = DataAttribute::create('kBestellpos', 'int', self::cast('0', 'int'), false);
        $attributes['stockBefore']        = DataAttribute::create('fLagerbestandVorAbschluss', 'double');
        $attributes['longestMinDelivery'] = DataAttribute::create(
            'nLongestMinDelivery',
            'int',
            self::cast('0', 'int'),
            false
        );
        $attributes['longestMaxDelivery'] = DataAttribute::create(
            'nLongestMaxDelivery',
            'int',
            self::cast('0', 'int'),
            false
        );

        $attributes['attributes'] = DataAttribute::create(
            'attributes',
            CartItemAttributeModel::class,
            null,
            true,
            false,
            'kWarenkorbPos'
        );

        return $attributes;
    }
}
