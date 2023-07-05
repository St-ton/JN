<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\RMA\PickupAddress\PickupAddressDataTableObject;
use JTL\RMA\PickupAddress\PickupAddressRepository;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * Class RMARepository
 * @package JTL\RMA
 * @since 5.3.0
 */
class RMARepository extends AbstractRepository
{
    
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * @param int $customerID
     * @param int $langID
     * @param string|null $status
     * @param string|null $createdBeforeDate
     * @param int|null $pickupAddressID
     * @param int|null $productID
     * @param int|null $shippingNoteID
     * @return string
     */
    private function buildQuery(
        int $customerID,
        int $langID,
        ?int $id,
        ?string $status,
        ?string $createdBeforeDate,
        ?int $pickupAddressID,
        ?int $productID,
        ?int $shippingNoteID
    ): string {
        $filter  = [
            'customerID' => $customerID,
            'langID' => $langID,
        ];
        $queries = [
            'id' => '',
            'status' => '',
            'beforeDate' => '',
            'pickupAddress' => '',
            'product' => '',
            'shippingNote' => '',
        ];
        if ($id !== null) {
            $filter['id']  = $id;
            $queries['id'] = ' AND rma.id = :id';
        }
        if ($status !== null) {
            $filter['status']  = $status;
            $queries['status'] = ' AND rma.status = :status';
        }
        if ($createdBeforeDate !== null) {
            $filter['beforeDate']  = $createdBeforeDate;
            $queries['beforeDate'] = ' AND rma.createDate < :beforeDate';
        }
        if ($pickupAddressID !== null) {
            $filter['aID']            = $pickupAddressID;
            $queries['pickupAddress'] = ' AND rma.pickup_addressID = :aID';
        }
        if ($productID !== null) {
            $filter['pID']      = $productID;
            $queries['product'] = ' AND positions.productID = :pID';
        }
        if ($shippingNoteID !== null) {
            $filter['sID']           = $shippingNoteID;
            $queries['shippingNote'] = ' AND shippingNote.kLieferschein = :sID';
        }

        return $this->getDB()->readableQuery(
            'SELECT
                rma.id AS rmaID,
                rma.wawiID,
                rma.pickupAddressID,
                rma.customerID,
                rma.status AS rmaStatus,
                rma.createDate AS rmaCreateDate,
                rma.lastModified AS rmaLastModified,
                
                positions.id AS rmaPosID,
                positions.orderPosID,
                positions.productID,
                positions.reasonID,
                positions.name,
                positions.unitPriceNet,
                positions.quantity,
                positions.vat,
                positions.unit,
                positions.stockBeforePurchase,
                positions.longestMaxDelivery,
                positions.longestMinDelivery,
                positions.shippingNotePosID,
                positions.comment AS rmaPosComment,
                positions.status AS rmaPosStatus,
                positions.createDate AS rmaPosCreateDate,
    
                pickup_address.id AS addressID,
                pickup_address.salutation AS addressSalutation,
                pickup_address.firstName AS addressFirstName,
                pickup_address.lastName AS addressLastName,
                pickup_address.academicTitle AS addressAcademicTitle,
                pickup_address.companyName AS addressCompanyName,
                pickup_address.companyAdditional AS addressCompanyAdditional,
                pickup_address.street AS addressStreet,
                pickup_address.houseNumber AS addressHouseNumber,
                pickup_address.addressAdditional AS addressAddressAdditional,
                pickup_address.postalCode AS addressPostalCode,
                pickup_address.city AS addressCity,
                pickup_address.state AS addressState,
                pickup_address.country AS addressCountry,
                pickup_address.phone AS addressPhone,
                pickup_address.mobilePhone AS addressMobilePhone,
                pickup_address.fax AS addressFax,
                pickup_address.mail AS addressMail,
                pickup_address.hash AS addressHash,
                
                rma_reasons.wawiID AS reasonWawiID,
                
                rma_reasons_lang.title AS reasonLocalized,
                
                shippingNote.kLieferschein
            FROM
                rma
            RIGHT JOIN rma_pos AS positions
            ON
                rma.id = positions.rmaID' . $queries['product'] . '
            JOIN tlieferscheinpos AS shippingNote
            ON
                positions.shippingNotePosID = shippingNote.kLieferscheinPos' . $queries['shippingNote'] . '
            LEFT JOIN rma_reasons
            ON
                positions.reasonID = rma_reasons.id
            LEFT JOIN rma_reasons_lang
            ON
                positions.reasonID = rma_reasons_lang.reasonID AND rma_reasons_lang.langID = :langID
            JOIN pickup_address
            ON
                rma.pickupAddressID = pickup_address.id
            WHERE rma.customerID = :customerID'
            . $queries['id']
            . $queries['status']
            . $queries['beforeDate']
            . $queries['pickupAddress']
            . ' GROUP BY positions.id',
            $filter
        );
    }
    
    /**
     * @param array $filters
     * @return array
     * @since 5.3.0
     */
    public function getList(array $filters): array
    {
        $results = [];

        if ($filters['customerID'] === null || $filters['langID'] === null) {
            return $results;
        }

        $data = $this->getDB()->executeQuery(
            $this->buildQuery(
                $filters['customerID'],
                $filters['langID'],
                $filters['id'] ?? null,
                $filters['status'] ?? null,
                $filters['createdBeforeDate'] ?? null,
                $filters['pickupAddressID'] ?? null,
                $filters['productID'] ?? null,
                $filters['shippingNoteID'] ?? null
            ),
            ReturnType::ARRAY_OF_OBJECTS
        );

        $positions = [];
        foreach ($data as $obj) {
            if (!isset($results[$obj->rmaID])) {
                $results[$obj->rmaID] = (new RMADataTableObject())->hydrateWithObject($obj);
                $results[$obj->rmaID]->setPickupAddress(
                    (new PickupAddressDataTableObject())->hydrateWithObject($obj)
                );
            }
            $results[$obj->rmaID]->addPosition(
                (new RMAPosDataTableObject())->hydrateWithObject($obj)
            );
        }

        return $results;
    }
}
