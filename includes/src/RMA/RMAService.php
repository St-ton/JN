<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractServiceTim;
use JTL\RMA\DomainObjects\RMADomainObject;
use JTL\RMA\DomainObjects\RMAPositionDomainObject;
use JTL\RMA\DomainObjects\RMAReasonLangDomainObject;
use JTL\RMA\Repositories\RMAPositionRepository;
use JTL\RMA\Repositories\RMAReasonLangRepository;
use JTL\RMA\Repositories\RMAReasonRepository;
use JTL\RMA\Repositories\RMARepository;

/**
 * Class RMAService
 * @package JTL\RMA
 */
class RMAService extends AbstractServiceTim
{
    private ?RMARepository $RMARepository;

    private ?RMAPositionRepository $RMAPositionRepository;

    private ?RMAReasonRepository $RMAReasonsRepository;

    private ?RMAReasonLangRepository $RMAReasonLangRepository;

    /**
     * @var RMADomainObject[]
     */
    public array $rmas = [];

    /**
     * @var RMAReasonLangDomainObject[]|null
     */
    public ?array $reasons = null;

    /**
     * @var RMAPositionDomainObject[]|null
     */
    private readonly ?array $returnableProducts;

    /**
     * @return RMARepository
     * @since 5.3.0
     */
    public function getRMARepository(): RMARepository
    {
        if (!isset($this->RMARepository)) {
            $this->RMARepository = new RMARepository($this->db);
        }

        return $this->RMARepository;
    }

    /**
     * @return RMAPositionRepository
     * @since 5.3.0
     */
    public function getRMAPositionRepository(): RMAPositionRepository
    {
        if (!isset($this->RMAPositionRepository)) {
            $this->RMAPositionRepository = new RMAPositionRepository($this->db);
        }

        return $this->RMAPositionRepository;
    }

    /**
     * @return RMAReasonRepository
     * @since 5.3.0
     */
    public function getRMAReasonRepository(): RMAReasonRepository
    {
        if (!isset($this->RMAReasonsRepository)) {
            $this->RMAReasonsRepository = new RMAReasonRepository($this->db);
        }

        return $this->RMAReasonsRepository;
    }

    /**
     * @return RMAReasonLangRepository
     * @since 5.3.0
     */
    public function getRMAReasonLangRepository(): RMAReasonLangRepository
    {
        if (!isset($this->RMAReasonLangRepository)) {
            $this->RMAReasonLangRepository = new RMAReasonLangRepository($this->db);
        }

        return $this->RMAReasonLangRepository;
    }

    /**
     * @param int $customerID
     * @param int $langID
     * @return $this
     * @since 5.3.0
     */
    public function loadRMAs(int $customerID, int $langID): self
    {
        foreach ($this->getRMARepository()->getList([
            'customerID' => $customerID,
            'langID' => $langID
        ]) as $rma) {
            $this->rmas[$rma->getID()] = $rma;
        }

        return $this;
    }

    /**
     * @param int $id
     * @param int $customerID
     * @param int $langID
     * @return RMADomainObject
     * @since 5.3.0
     */
    public function loadRMA(int $id, int $customerID, int $langID): RMADomainObject
    {
        if (isset($this->rmas[$id])) {
            return $this->rmas[$id];
        }

        return $this->loadRMAs(
            $customerID,
            $langID
        )->rmas[$id] ?? $this->generateDomainObject(
            RMADomainObject::class,
            $this->getRMARepository()->getDefaultValues()
        );
    }

    /*
    public function getProduct(int $id = 0): Artikel
    {
        $result = new Artikel();
        if ($id > 0) {
            $result->fuelleArtikel($id);
        }
        return $result;
    }
    */

    /**
     * @param array $positions
     * @return array
     * @since 5.3.0
     */
    public function getOrderIDs(array $positions): array
    {
        $result = [];
        foreach ($positions as $pos) {
            if ($pos->orderID !== null && !\in_array($pos->orderID, $result)) {
                $result[] = $pos->orderID;
            }
        }
        return $result;
    }

    /**
     * @param RMADomainObject[] $rmas
     * @return array
     * @since 5.3.0
     */
    public function getRMAPositions(array $rmas): array
    {
        $rmaIDs = [];
        foreach ($rmas as $rma) {
            $rmaIDs[] = $rma->id;
        }
        return $this->getRMAPositionRepository()->getPositionsFor($rmaIDs);
    }

    /**
     * @param RMAPositionDomainObject[] $positions
     * @return array
     */
    public function getOrderArray(array $positions): array
    {
        $result = [];
        foreach ($positions as $pos) {
            if (isset($result[$pos->orderID])) {
                continue;
            }
            $result[$pos->orderID] = $pos->getOrderNo();
        }
        return $result;
    }

    /**
     * @param array $orderIDs
     * @return array
     * @since 5.3.0
     */
    public function orderIDsToNOs(array $orderIDs): array
    {
        return $this->getRMARepository()->orderIDsToNOs($orderIDs);
    }

    /**
     * @param array $positions
     * @param string $by
     * @return RMAPositionDomainObject[]
     * @since 5.3.0
     */
    public function groupRMAPositions(array $positions, string $by = 'order'): array
    {
        $result      = [];
        $allowedKeys = [
            'order'   => 'orderID',
            'product' => 'productID',
            'reason'  => 'reasonID',
            'status'  => 'status',
            'date'    => 'createDate' // ToDo: Group by day?
        ];
        $arrayKeys   = [];
        $groupByKey  = $allowedKeys[$by] ?? 'orderID';

        if ($by === 'order') {
            $arrayKeys = $this->getOrderArray($positions);
        }
        foreach ($positions as $pos) {
            $groupBy            = $arrayKeys[$pos->{$groupByKey}] ?? $pos->{$groupByKey};
            $result[$groupBy][] = $pos;
        }
        return $result;
    }

    /**
     * @param RMADomainObject $rma
     * @return RMAPositionDomainObject[]
     * @since 5.3.0
     */
    public function getItems(RMADomainObject $rma): array
    {
        $result = [];

        foreach ($rma->getPositions() as $item) {
            $result[] = $this->generateDomainObject(
                RMAPositionDomainObject::class,
                $this->getRMAPositionRepository()->getDefaultValues($item->toArray())
            );
        }
        return $result;
    }

    /**
     * @param int $customerID
     * @param int $languageID
     * @param int $cancellationTime
     * @return RMAPositionDomainObject[]
     * @since 5.3.0
     */
    public function getReturnableProducts(
        int $customerID,
        int $languageID,
        int $cancellationTime
    ): array {
        if (!isset($this->returnableProducts)) {
            $returnableProducts = [];
            foreach ($this->getRMARepository()->getReturnableProducts(
                $customerID,
                $languageID,
                $cancellationTime
            ) as $returnableProduct) {
                $returnableProducts[] = $this->generateDomainObject(
                    RMAPositionDomainObject::class,
                    $this->getRMAPositionRepository()->getDefaultValues(
                        (array)$returnableProduct
                    )
                );
            }
            $this->returnableProducts = $returnableProducts;
        }

        return $this->returnableProducts;
    }

    /**
     * @param int $langID
     * @return $this
     * @since 5.3.0
     */
    public function loadReasons(int $langID): self
    {
        foreach ($this->getRMAReasonLangRepository()->getList(['langID' => $langID]) as &$reason) {
            $reason->id                       = (int)$reason->id;
            $reason->reasonID                 = (int)$reason->reasonID;
            $reason->langID                   = (int)$reason->langID;
            $this->reasons[$reason->reasonID] = $this->generateDomainObject(
                RMAReasonLangDomainObject::class,
                $this->getRMAReasonLangRepository()->getDefaultValues((array)$reason)
            );
        }

        return $this;
    }

    /**
     * @param int $id
     * @param int $languageID
     * @return RMAReasonLangDomainObject
     */
    public function getReason(int $id, int $languageID): RMAReasonLangDomainObject
    {
        if (!isset($this->reasons)) {
            $this->loadReasons($languageID);
        }
        return $this->reasons[$id] ?? $this->generateDomainObject(
            RMAReasonLangDomainObject::class,
            $this->getRMAReasonLangRepository()->getDefaultValues()
        );
    }

    /**
     * @param RMADomainObject $rma
     * @param int $shippingNotePosID
     * @return RMAPositionDomainObject
     * @since 5.3.0
     */
    public function getPosition(RMADomainObject $rma, int $shippingNotePosID): RMAPositionDomainObject
    {
        return $rma->getPositions()[$shippingNotePosID] ?? $this->generateDomainObject(
            RMAPositionDomainObject::class,
            $this->getRMAPositionRepository()->getDefaultValues()
        );
    }

    /**
     * @param RMADomainObject $rma
     * @return float
     * @since 5.3.0
     */
    public function getTotalPrice(RMADomainObject $rma): float
    {
        $total = 0;
        foreach ($rma->getPositions() as $pos) {
            $total += $pos->quantity * $pos->unitPriceNet;
        }
        return $total;
    }
}
