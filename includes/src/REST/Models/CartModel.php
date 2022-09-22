<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CartModel
 *
 * @package JTL\REST\Models
 * @property int $kWarenkorb
 * @method int getKWarenkorb()
 * @method void setKWarenkorb(int $value)
 * @property int $kKunde
 * @method int getKKunde()
 * @method void setKKunde(int $value)
 * @property int $kLieferadresse
 * @method int getKLieferadresse()
 * @method void setKLieferadresse(int $value)
 * @property int $kZahlungsInfo
 * @method int getKZahlungsInfo()
 * @method void setKZahlungsInfo(int $value)
 */
final class CartModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'twarenkorb';
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
        $attributes                      = [];
        $attributes['id']                = DataAttribute::create('kWarenkorb', 'int', null, false, true);
        $attributes['customerID']        = DataAttribute::create('kKunde', 'int', self::cast('0', 'int'), false);
        $attributes['deliveryAddressID'] = DataAttribute::create('kLieferadresse', 'int', self::cast('0', 'int'), false);
        $attributes['paymentInfoID']     = DataAttribute::create('kZahlungsInfo', 'int', self::cast('0', 'int'));

        $attributes['items'] = DataAttribute::create('items', CartItemModel::class, null, true, false, 'kWarenkorb');

        return $attributes;
    }
}
