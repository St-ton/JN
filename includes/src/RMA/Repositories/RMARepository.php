<?php declare(strict_types=1);

namespace JTL\RMA\Repositories;

use JTL\Abstracts\AbstractRepositoryTim;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\RMA\DomainObjects\RMAPositionDomainObject;
use JTL\RMA\DomainObjects\RMAProductDomainObject;

/**
 * Class RMARepository
 * @package JTL\RMA
 * @since 5.3.0
 */
class RMARepository extends AbstractRepositoryTim
{

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return [
            'rmaID'           => 'id',
            'wawiID'          => 'wawiID',
            'customerID'      => 'customerID',
            'pickupAddressID' => 'pickupAddressID',
            'rmaStatus'       => 'status',
            'rmaCreateDate'   => 'createDate',
            'rmaLastModified' => 'lastModified'
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array
    {
        $default = [
            'id'              => 0,
            'wawiID'          => null,
            'customerID'      => 0,
            'pickupAddressID' => 0,
            'status'          => null,
            'createDate'      => \date('Y-m-d H:i:s'),
            'lastModified'    => null,
            'positions'       => null
        ];
        return $this->arrayCombine($default, $data);
    }

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
     * @param int|null $id
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

        return $this->getDB()->executeQuery(
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
    }


    /**
     * @param int $customerID
     * @param $languageID
     * @param int $cancellationTime
     * @return array
     * @since 5.3.0
     */
    public function getReturnableProducts(int $customerID, $languageID, int $cancellationTime): array
    {
        //ToDo: Test if product has already been requested for an RMA
        return $this->getDB()->getCollection(
            "SELECT twarenkorbpos.kArtikel AS id, twarenkorbpos.cEinheit AS unit,
       twarenkorbpos.cArtNr AS productNR, twarenkorbpos.fPreisEinzelNetto AS unitPriceNet, twarenkorbpos.fMwSt AS vat,
       twarenkorbpos.cName AS name, tbestellung.kKunde AS customerID,
       tbestellung.kLieferadresse AS shippingAddressID, tbestellung.cStatus AS orderStatus,
       tbestellung.cBestellNr AS orderNo, tbestellung.kBestellung AS orderID,
       tlieferscheinpos.kLieferscheinPos AS shippingNotePosID, tlieferscheinpos.kLieferschein AS shippingNoteID,
       tlieferscheinpos.fAnzahl AS quantity, tartikel.cSeo AS seo,
       DATE_FORMAT(FROM_UNIXTIME(tversand.dErstellt), '%d-%m-%Y') AS createDate,
       twarenkorbposeigenschaft.cEigenschaftName AS propertyName,
       twarenkorbposeigenschaft.cEigenschaftWertName AS propertyValue,
       teigenschaftsprache.cName AS propertyNameLocalized, teigenschaftwertsprache.cName AS propertyValueLocalized
            FROM tbestellung
            JOIN twarenkorbpos
                ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                AND twarenkorbpos.kArtikel > 0
            JOIN tlieferscheinpos
                ON tlieferscheinpos.kBestellPos = twarenkorbpos.kBestellpos
            JOIN tversand
                ON tversand.kLieferschein = tlieferscheinpos.kLieferschein
                AND DATE(FROM_UNIXTIME(tversand.dErstellt)) >= DATE_SUB(NOW(), INTERVAL :cancellationTime DAY)
            LEFT JOIN tartikelattribut
                ON tartikelattribut.kArtikel = twarenkorbpos.kArtikel
                AND tartikelattribut.cName = :notReturnable
            LEFT JOIN tartikeldownload
                ON tartikeldownload.kArtikel = twarenkorbpos.kArtikel
            LEFT JOIN twarenkorbposeigenschaft
                ON twarenkorbposeigenschaft.kWarenkorbPos = twarenkorbpos.kWarenkorbPos
            LEFT JOIN teigenschaftsprache
                ON teigenschaftsprache.kEigenschaft = twarenkorbposeigenschaft.kEigenschaft
                AND teigenschaftsprache.kSprache = :langID
            LEFT JOIN teigenschaftwertsprache
                ON teigenschaftwertsprache.kEigenschaftWert = twarenkorbposeigenschaft.kEigenschaftWert
                AND teigenschaftwertsprache.kSprache = :langID
            JOIN tartikel
                ON tartikel.kArtikel = twarenkorbpos.kArtikel
            WHERE tbestellung.kKunde = :customerID
                AND tbestellung.cStatus IN (:status_versandt, :status_teilversandt)
                AND tartikelattribut.cWert IS NULL
                AND tartikeldownload.kArtikel IS NULL",
            [
                'customerID' => $customerID,
                'langID' => $languageID,
                'status_versandt' => \BESTELLUNG_STATUS_VERSANDT,
                'status_teilversandt' => \BESTELLUNG_STATUS_TEILVERSANDT,
                'cancellationTime' => $cancellationTime,
                'notReturnable' => \PRODUCT_NOT_RETURNABLE
            ]
        )->map(static function ($product): object {
            $product->property        = new \stdClass();
            $product->property->name  = $product->propertyNameLocalized ?? $product->propertyName ?? '';
            $product->property->value = $product->propertyValueLocalized ?? $product->propertyValue ?? '';

            unset($product->propertyNameLocalized);
            unset($product->propertyValueLocalized);
            unset($product->propertyName);
            unset($product->propertyValue);

            $product->product           = new Artikel();
            $product->product->kArtikel = (int)$product->id;
            $product->product->cName    = '';
            $product->product->holBilder();

            return $product;
        })->keyBy('shippingNotePosID')->all();
    }
}
