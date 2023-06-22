<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\RMA\PickupAddress\PickupAddressRepository;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;

/**
 * Class RMARepository
 * @package JTL\RMA
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
     * @param array $filters
     * @return array
     * @since 5.3.0
     */
    public function getList(array $filters): array
    {
        $results = [];

        $data = parent::getList($filters);
        foreach ($data as $obj) {
            $obj->id         = (int)$obj->id;
            $obj->status     = \langRMAStatus((int)$obj->status);
            $obj->createDate = date('d.m.Y H:i', \strtotime($obj->createDate));
            $dataTableObject = new RMADataTableObject();
            $rma             = $dataTableObject->hydrateWithObject($obj);
            $rmaPos          = new RMAPosRepository();
            $rma->setPositions(
                $rmaPos->getList(['rmaID' => $rma->getID()])
            );
            $rmaPickupAddress = new PickupAddressRepository();
            $rma->setPickupAddress(
                $rmaPickupAddress->get($rma->getID()) ?? new \stdClass()
            );

            $results[] = $rma;
        }
        return $results;
    }

    /**
     * @param string|null $status
     * @param string|null $createdBeforeDate
     * @param int|null $pickupAddressID
     * @param int|null $productID
     * @param int|null $shippingNoteID
     * @return array
     * @since 5.3.0
     */
    public function loadFromDB(
        ?string $status,
        ?string $createdBeforeDate,
        ?int $pickupAddressID,
        ?int $productID,
        ?int $shippingNoteID
    ): array {
        $result  = [];
        $filter  = [
            'customerID' => Frontend::getCustomer()->getID(),
            'langID' => Shop::getLanguageID(),
        ];
        $queries = [
            'status' => '',
            'beforeDate' => '',
            'pickupAddress' => '',
            'product' => '',
            'shippingNote' => '',
        ];
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
            $queries['pickupAddress'] = ' AND rma.pickupAddressID = :aID';
        }
        if ($productID !== null) {
            $filter['pID']      = $productID;
            $queries['product'] = ' AND positions.productID = :pID';
        }
        if ($shippingNoteID !== null) {
            $filter['sID']           = $shippingNoteID;
            $queries['shippingNote'] = ' AND shippingNote.kLieferschein = :sID';
        }

        $this->getDB()->getCollection(
            'SELECT
                rma.id AS rmaID,
                rma.wawiID,
                rma.pickupAddressID,
                rma.customerID,
                rma.status AS rmaStatus,
                rma.createDate AS rmaCreateDate,
                rma.lastModified AS rmaLastModified,
                
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
                positions.comment AS rmaPosComment,
                positions.status AS rmaPosStatus,
                positions.createDate AS rmaPosCreateDate,
                
                GROUP_CONCAT(
                    CONCAT(history.title, \';;;\', history.lastModified, \';;;\', history.value)
                    ORDER BY history.lastModified ASC SEPARATOR \'|||\'
                ) AS rmaPosHistory,
    
                pickupaddress.id AS pkAddressID,
                pickupaddress.salutation AS pkAddressSalutation,
                pickupaddress.firstName AS pkAddressFirstName,
                pickupaddress.lastName AS pkAddressLastName,
                pickupaddress.academicTitle AS pkAddressAcademicTitle,
                pickupaddress.companyName AS pkAddressCompanyName,
                pickupaddress.companyAdditional AS pkAddressCompanyAdditional,
                pickupaddress.street AS pkAddressStreet,
                pickupaddress.houseNumber AS pkAddressHouseNumber,
                pickupaddress.addressAdditional AS pkAddressAddressAdditional,
                pickupaddress.postalCode AS pkAddressPostalCode,
                pickupaddress.city AS pkAddressCity,
                pickupaddress.state AS pkAddressState,
                pickupaddress.country AS pkAddressCountry,
                pickupaddress.phone AS pkAddressPhone,
                pickupaddress.mobilePhone AS pkAddressMobilePhone,
                pickupaddress.fax AS pkAddressFax,
                pickupaddress.mail AS pkAddressMail,
                pickupaddress.hash AS pkAddressHash,
                
                rmareasons.wawiID AS reasonWawiID,
                
                rmareasonslang.title AS reasonLocalized,
                
                shippingNote.kLieferschein
            FROM
                rma
            RIGHT JOIN rmapos AS positions
            ON
                rma.id = positions.rmaID' . $queries['product'] . '
            JOIN tlieferscheinpos AS shippingNote
            ON
                positions.shippingNotePosID = shippingNote.kLieferscheinPos' . $queries['shippingNote'] . '
            LEFT JOIN rmareasons
            ON
                positions.reasonID = rmareasons.id
            LEFT JOIN rmareasonslang
            ON
                positions.reasonID = rmareasonslang.reasonID AND rmareasonslang.langID = :langID
            JOIN pickupaddress
            ON
                rma.pickupAddressID = pickupaddress.id
            RIGHT JOIN rmahistory AS history
            ON
                positions.id = history.rmaPosID
            WHERE rma.customerID = :customerID
            GROUP BY positions.id'
            . $queries['status']
            . $queries['beforeDate']
            . $queries['pickupAddress'],
            $filter
        )->each(
            static function (\stdClass $rmaPos) use (&$result) {
                $rmaPos->rmaID = (int)$rmaPos->rmaID;
                if (!isset($result[$rmaPos->rmaID])) {
                    $result[$rmaPos->rmaID]                  = new \stdClass();
                    $result[$rmaPos->rmaID]->positions       = [];
                    $result[$rmaPos->rmaID]->pickupAddress   = new \stdClass();
                    $result[$rmaPos->rmaID]->rmaID           = $rmaPos->rmaID;
                    $result[$rmaPos->rmaID]->wawiID          = (int)$rmaPos->wawiID;
                    $result[$rmaPos->rmaID]->pickupAddressID = (int)$rmaPos->pickupAddressID;
                    $result[$rmaPos->rmaID]->customerID      = (int)$rmaPos->customerID;
                    $result[$rmaPos->rmaID]->shippingNoteID  = (int)$rmaPos->kLieferschein;
                    $result[$rmaPos->rmaID]->rmaCreateDate   =
                        \date('d-m-Y H:i', \strtotime($rmaPos->rmaCreateDate ?? '1970-01-01'));
                    $result[$rmaPos->rmaID]->rmaLastModified =
                        \date('d-m-Y H:i', \strtotime($rmaPos->rmaLastModified ?? '1970-01-01'));
                    $result[$rmaPos->rmaID]->rmaStatus       = $rmaPos->rmaStatus;
                }

                $position                        = new \stdClass();
                $position->orderPosID            = (int)$rmaPos->orderPosID;
                $position->productID             = (int)$rmaPos->productID;
                $position->reasonID              = (int)$rmaPos->reasonID;
                $position->reasonWawiID          = (int)$rmaPos->reasonWawiID;
                $position->reason                = $rmaPos->reasonLocalized;
                $position->name                  = $rmaPos->name;
                $position->unitPriceNet          = (float)$rmaPos->unitPriceNet;
                $position->unitPriceNetLocalized = Preise::getLocalizedPriceString($rmaPos->unitPriceNet);
                $position->quantity              = (float)$rmaPos->quantity;
                $position->vat                   = (float)$rmaPos->vat;
                $position->unit                  = $rmaPos->unit;
                $position->stockBeforePurchase   = (float)$rmaPos->stockBeforePurchase;
                $position->longestMaxDelivery    = (int)$rmaPos->longestMaxDelivery;
                $position->longestMinDelivery    = (int)$rmaPos->longestMinDelivery;
                $position->comment               = $rmaPos->rmaPosComment;
                $position->status                = $rmaPos->rmaPosStatus;
                $position->createDate            = $rmaPos->rmaPosCreateDate;
                $position->history               = \explode('|||', $rmaPos->rmaPosHistory);
                if ($position->history) {
                    foreach ($position->history as &$history) {
                        $historyArr        = \explode(';;;', $history);
                        $tmp               = new \stdClass();
                        $tmp->title        = $historyArr[0];
                        $tmp->lastModified = \date('d-m-Y H:i', \strtotime($historyArr[1] ?? '1970-01-01'));
                        $tmp->value        = $historyArr[2];
                        $history           = $tmp;
                    }
                }

                $result[$rmaPos->rmaID]->positions[] = $position;

                $result[$rmaPos->rmaID]->pickupAddress->id                = (int)$rmaPos->pkAddressID;
                $result[$rmaPos->rmaID]->pickupAddress->salutation        = $rmaPos->pkAddressSalutation;
                $result[$rmaPos->rmaID]->pickupAddress->firstName         = $rmaPos->pkAddressFirstName;
                $result[$rmaPos->rmaID]->pickupAddress->lastName          = $rmaPos->pkAddressLastName;
                $result[$rmaPos->rmaID]->pickupAddress->academicTitle     = $rmaPos->pkAddressAcademicTitle;
                $result[$rmaPos->rmaID]->pickupAddress->companyName       = $rmaPos->pkAddressCompanyName;
                $result[$rmaPos->rmaID]->pickupAddress->companyAdditional = $rmaPos->pkAddressCompanyAdditional;
                $result[$rmaPos->rmaID]->pickupAddress->street            = $rmaPos->pkAddressStreet;
                $result[$rmaPos->rmaID]->pickupAddress->houseNumber       = $rmaPos->pkAddressHouseNumber;
                $result[$rmaPos->rmaID]->pickupAddress->addressAdditional = $rmaPos->pkAddressAddressAdditional;
                $result[$rmaPos->rmaID]->pickupAddress->postalCode        = $rmaPos->pkAddressPostalCode;
                $result[$rmaPos->rmaID]->pickupAddress->city              = $rmaPos->pkAddressCity;
                $result[$rmaPos->rmaID]->pickupAddress->state             = $rmaPos->pkAddressState;
                $result[$rmaPos->rmaID]->pickupAddress->country           = $rmaPos->pkAddressCountry;
                $result[$rmaPos->rmaID]->pickupAddress->phone             = $rmaPos->pkAddressPhone;
                $result[$rmaPos->rmaID]->pickupAddress->mobilePhone       = $rmaPos->pkAddressMobilePhone;
                $result[$rmaPos->rmaID]->pickupAddress->fax               = $rmaPos->pkAddressFax;
                $result[$rmaPos->rmaID]->pickupAddress->mail              = $rmaPos->pkAddressMail;
                $result[$rmaPos->rmaID]->pickupAddress->hash              = $rmaPos->pkAddressHash;
            }
        );
        return $result;
    }

    /**
     * @param array $values
     * @return bool
     */
    public function delete(array $values): bool
    {
        $result     = true;
        $customerID = Frontend::getCustomer()->getID();

        foreach ($values as $id) {
            if ($this->getDB()->deleteRow(
                $this->getTableName(),
                [$this->getKeyName(), 'customerID', 'wawiID'],
                [(int)$id, $customerID, null]
            ) === self::DELETE_FAILED) {
                $result = false;
            }
        }
        return $result;
    }
}
