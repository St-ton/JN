<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractService;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Interfaces\RepositoryInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use stdClass;

/**
 * Class RMAService
 * @package JTL\RMA
 */
class RMAService extends AbstractService
{
    /**
     * @var RMARepository
     */
    protected RepositoryInterface $repository;
    
    /**
     * @param int $id
     * @return RMADataTableObject
     */
    public function get(int $id): RMADataTableObject
    {
        return (new RMADataTableObject())->hydrateWithObject($this->getRepository()->get($id) ?? new stdClass());
    }
    
    /**
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        return $this->getRepository()->delete($ids);
    }
    
    /**
     * @return RMARepository
     */
    public function getRepository(): RMARepository
    {
        return $this->repository;
    }
    
    /**
     * @return void
     */
    protected function initRepository(): void
    {
        $this->repository = new RMARepository();
    }
    
    /**
     * @return Artikel
     * @since 5.3.0
     */
    public function getProduct(int $id = 0): Artikel
    {
        $result = new Artikel();
        if ($id > 0) {
            $result->fuelleArtikel($id);
        }
        return $result;
    }
    
    /**
     * @param array $rmaPositions
     * @return array
     * @since 5.3.0
     */
    public function getOrderIDs(array $rmaPositions): array
    {
        $result = [];
        foreach ($rmaPositions as $obj) {
            if ($obj->orderID !== null && !\in_array($obj->orderID, $result)) {
                $result[] = $obj->orderID;
            }
        }
        return $result;
    }
    
    /**
     * @param int $customerID
     * @return array
     * @since 5.3.0
     */
    public function getReturnableProducts(int $customerID = 0): array
    {
        $customerID       = ($customerID === 0) ? Frontend::getCustomer()->getID() : $customerID;
        $cancellationTime = Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_cancellation_time');
        return Shop::Container()->getDB()->getCollection(
            "SELECT twarenkorbpos.kArtikel AS id, twarenkorbpos.cEinheit AS unit,
       twarenkorbpos.cArtNr AS productNR, twarenkorbpos.fPreisEinzelNetto AS unitPriceNet, twarenkorbpos.fMwSt AS vat,
       twarenkorbpos.cName AS name, tbestellung.kBestellung AS orderID, tbestellung.kKunde AS clientID,
       tbestellung.kLieferadresse AS shippingAddressID, tbestellung.cStatus AS status,
       tbestellung.cBestellNr AS orderID, tlieferscheinpos.kLieferscheinPos AS shippingNotePosID,
       tlieferscheinpos.kLieferschein AS shippingNoteID, tlieferscheinpos.fAnzahl AS quantity,
       tartikel.cSeo AS seo, DATE_FORMAT(FROM_UNIXTIME(tversand.dErstellt), '%d-%m-%Y') AS createDate,
       tartikelattribut.cWert AS notReturnable
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
                AND tartikelattribut.cName = 'nicht_retournierbar'
            JOIN tartikel
                ON tartikel.kArtikel = twarenkorbpos.kArtikel
            WHERE tbestellung.kKunde = :customerID
                AND tbestellung.cStatus IN (:status_versandt, :status_teilversandt)
                AND tartikelattribut.cWert IS NULL",
            [
                'customerID' => $customerID,
                'status_versandt' => \BESTELLUNG_STATUS_VERSANDT,
                'status_teilversandt' => \BESTELLUNG_STATUS_TEILVERSANDT,
                'cancellationTime' => $cancellationTime
            ]
        )->map(static function ($product): \stdClass {
            $product->unitPriceNet      = Preise::getLocalizedPriceString($product->unitPriceNet);
            $product->Artikel           = new Artikel();
            $product->Artikel->kArtikel = (int)$product->id;
            $product->Artikel->holBilder();
            
            return $product;
        })->all();
    }
}
