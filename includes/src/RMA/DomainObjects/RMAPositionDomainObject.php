<?php declare(strict_types=1);

namespace JTL\RMA\DomainObjects;

use JTL\Catalog\Product\Artikel;
use JTL\DataObjects\AbstractDomainObject;
use JTL\DataObjects\DomainObjectInterface;

/**
 * Class ReturnableProduct
 * @package JTL\RMA
 * @description DTO for holding a returnable product
 */
readonly class RMAPositionDomainObject extends AbstractDomainObject implements DomainObjectInterface
{

    /**
     * @param int $id
     * @param int $rmaID
     * @param int $shippingNotePosID
     * @param int $orderID
     * @param int $orderPosID
     * @param int|null $productID
     * @param int|null $reasonID
     * @param string $name
     * @param float $unitPriceNet
     * @param float $quantity
     * @param float $vat
     * @param string|null $unit
     * @param float|null $stockBeforePurchase
     * @param int $longestMinDelivery
     * @param int $longestMaxDelivery
     * @param string|null $comment
     * @param string|null $status
     * @param string $createDate
     * @param array|null $history
     * @param Artikel|null $product
     * @param RMAReasonLangDomainObject|null $reason
     * @param object|null $property
     * @param string|null $productNR
     * @param string|null $orderStatus
     * @param string|null $seo
     * @param string|null $orderNo
     * @param int|null $customerID
     * @param int|null $shippingAddressID
     * @param int|null $shippingNoteID
     */
    public function __construct(
        public int $id,
        public int $rmaID,
        public int $shippingNotePosID,
        public int $orderID,
        public int $orderPosID,
        public ?int $productID,
        public ?int $reasonID,
        public string $name,
        public float $unitPriceNet,
        public float $quantity,
        public float $vat,
        public ?string $unit,
        public ?float $stockBeforePurchase,
        public int $longestMinDelivery,
        public int $longestMaxDelivery,
        public ?string $comment,
        public ?string $status,
        public string $createDate,
        private ?array $history,
        private ?Artikel $product,
        private ?RMAReasonLangDomainObject $reason,
        private ?object $property,
        private ?string $productNR,
        private ?string $orderStatus,
        private ?string $seo,
        private ?string $orderNo,
        private ?int $customerID,
        private ?int $shippingAddressID,
        private ?int $shippingNoteID
    ) {
        parent::__construct();
    }

    /**
     * @return array|null
     */
    public function getHistory(): ?array
    {
        return $this->history;
    }

    /**
     * @return Artikel|null
     */
    public function getProduct(): ?Artikel
    {
        return $this->product;
    }

    /**
     * @return RMAReasonLangDomainObject|null
     */
    public function getReason(): ?RMAReasonLangDomainObject
    {
        return $this->reason;
    }

    /**
     * @return object|null
     */
    public function getProperty(): ?object
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getProductNR(): string
    {
        return $this->productNR ?? '';
    }

    /**
     * @return string
     */
    public function getOrderStatus(): string
    {
        return $this->orderStatus ?? '';
    }

    /**
     * @return string
     */
    public function getSeo(): string
    {
        return $this->seo ?? '';
    }

    /**
     * @return string
     */
    public function getOrderNo(): string
    {
        return $this->orderNo ?? '';
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID ?? 0;
    }

    /**
     * @return int
     */
    public function getShippingAddressID(): int
    {
        return $this->shippingAddressID ?? 0;
    }

    /**
     * @return int
     */
    public function getShippingNoteID(): int
    {
        return $this->shippingNoteID ?? 0;
    }
}
