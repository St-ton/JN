<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CartModel
 *
 * @OA\Schema(
 *     title="Cart model",
 *     description="Cart model",
 * )
 *
 * @package JTL\REST\Models
 * @OA\Property(
 *   property="id",
 *   type="integer",
 *   description="The cart's id"
 * )
 * @property int $id
 * @property int $kWarenkorb
 * @method int getId()
 * @method void setId(int $value)
 * @OA\Property(
 *   property="customerID",
 *   type="integer",
 *   description="The customers's id"
 * )
 * @property int $customerID
 * @property int $kKunde
 * @method int getCustomerId()
 * @method void setCustomerId(int $value)
 * @OA\Property(
 *   property="deliveryAddressID",
 *   type="integer",
 *   description="The delivery address id"
 * )
 * @property int $deliveryAddressID
 * @property int $kLieferadresse
 * @method int getDeliveryAddressId()
 * @method void setDeliveryAddressId(int $value)
 * @OA\Property(
 *   property="paymentInfoID",
 *   type="integer",
 *   description="The payment method's id"
 * )
 * @property int $paymentInfoID
 * @property int $kZahlungsInfo
 * @method int getPaymentInfoId()
 * @method void setPaymentInfoId(int $value)
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
