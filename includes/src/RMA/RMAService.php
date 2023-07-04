<?php declare(strict_types=1);

namespace JTL\RMA;

use JTL\Abstracts\AbstractService;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Helpers\Product;
use JTL\Interfaces\RepositoryInterface;
use JTL\RMA\PickupAddress\PickupAddressRepository;
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
     * @var RepositoryInterface[]
     */
    protected array $repositories;

    /**
     * @var array
     */
    private array $reasons;
    
    /**
     * @param int $id
     * @return RMADataTableObject
     */
    public function get(int $id): RMADataTableObject
    {
        if ($id === 0) {
            return new RMADataTableObject();
        }
        return (new RMADataTableObject())->hydrateWithObject($this->getRepository()->get($id) ?? new \stdClass());
    }
    
    /**
     * @param array $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        $repo        = $this->getRepository();
        $deleteItems = Shop::Container()->getDB()->getInts(
            'SELECT ' . $repo->getTableName() . '.' . $repo->getKeyName()
            . ' FROM ' . $repo->getTableName()
            . ' WHERE ' . $repo->getTableName() . '.' . $repo->getKeyName() . ' IN (:' . $repo->getKeyName() . ')'
            . ' AND ' . $repo->getTableName() . '.customerID = :customerID',
            $repo->getKeyName(),
            [
                $repo->getKeyName() => \implode(',', $ids),
                'customerID' => Frontend::getCustomer()->getID()
            ]
        );
        return $this->getRepository()->delete($deleteItems);
    }
    
    /**
     * @param string $name
     * @return RepositoryInterface
     */
    public function getRepository(string $name = ''): RepositoryInterface
    {
        $name = $name === '' ? 'RMARepository' : $name;
        if (!\in_array($name, $this->repositories)) {
            switch ($name) {
                case 'RMARepository':
                    $this->repositories[$name] = new RMARepository();
                    break;
                case 'RMAHistoryRepository':
                    $this->repositories[$name] = new RMAHistoryRepository();
                    break;
                case 'RMAPosRepository':
                    $this->repositories[$name] = new RMAPosRepository();
                    break;
                case 'RMAReasonsRepository':
                    $this->repositories[$name] = new RMAReasonsRepository();
                    break;
                case 'RMAReasonsLangRepository':
                    $this->repositories[$name] = new RMAReasonsLangRepository();
                    break;
                case 'PickupAddressRepository':
                    $this->repositories[$name] = new PickupAddressRepository();
                    break;
            }
        }
        return $this->repositories[$name];
    }
    
    /**
     * @return void
     */
    protected function initRepository(): void
    {
        $this->repositories['RMARepository'] = new RMARepository();
    }
    
    /**
     * @return void
     */
    protected function initDependencies(): void
    {
        $this->initRepository();
    }
    
    /**
     * @param int $id
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
        //ToDo: Test if product has already been requested for an RMA
        $customerID       = ($customerID === 0) ? Frontend::getCustomer()->getID() : $customerID;
        $cancellationTime = Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_cancellation_time');
        return Shop::Container()->getDB()->getCollection(
            "SELECT twarenkorbpos.kArtikel AS id, twarenkorbpos.cEinheit AS unit,
       twarenkorbpos.cArtNr AS productNR, twarenkorbpos.fPreisEinzelNetto AS unitPriceNet, twarenkorbpos.fMwSt AS vat,
       twarenkorbpos.cName AS name, tbestellung.kKunde AS clientID,
       tbestellung.kLieferadresse AS shippingAddressID, tbestellung.cStatus AS orderStatus,
       tbestellung.cBestellNr AS orderID, tlieferscheinpos.kLieferscheinPos AS shippingNotePosID,
       tlieferscheinpos.kLieferschein AS shippingNoteID, tlieferscheinpos.fAnzahl AS quantity,
       tartikel.cSeo AS seo, DATE_FORMAT(FROM_UNIXTIME(tversand.dErstellt), '%d-%m-%Y') AS createDate,
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
                'langID' => Shop::getLanguageID(),
                'status_versandt' => \BESTELLUNG_STATUS_VERSANDT,
                'status_teilversandt' => \BESTELLUNG_STATUS_TEILVERSANDT,
                'cancellationTime' => $cancellationTime,
                'notReturnable' => \PRODUCT_NOT_RETURNABLE
            ]
        )->map(static function ($product): \stdClass {
            $product->id                    = (int)$product->id;
            $product->vat                   = (float)$product->vat;
            $product->clientID              = (int)$product->clientID;
            $product->shippingAddressID     = (int)$product->shippingAddressID;
            $product->shippingNotePosID     = (int)$product->shippingNotePosID;
            $product->shippingNoteID        = (int)$product->shippingNoteID;
            $product->quantity              = (int)$product->quantity;
            $product->unitPriceNet          = (float)$product->unitPriceNet;
            $product->unitPriceNetLocalized = Preise::getLocalizedPriceString($product->unitPriceNet);
            $product->Artikel               = new Artikel();
            // Set ID and do "$product->Artikel->holBilder();" to get only images
            $property          = new \stdClass();
            $property->name    = $product->propertyNameLocalized ?? $product->propertyName ?? '';
            $property->value   = $product->propertyValueLocalized ?? $product->propertyValue ?? '';
            $product->property = $property;

            $product->Artikel = (new Artikel())->fuelleArtikel((int)$product->id);

            return $product;
        })->keyBy('shippingNotePosID')->all();
    }

    /**
     * @param int|null $langID
     * @return array
     * @since 5.3.0
     */
    public function getReasons(?int $langID = null): array
    {
        $result          = [];
        $langID          = $langID ?? Shop::getLanguageID();
        $reasonsLangRepo = new RMAReasonsLangRepository();
        foreach ($reasonsLangRepo->getList(['langID' => $langID]) as $reason) {
            $result[]        = $reason;
            $this->reasons[] = $reason;
        }
        return $result;
    }

    /**
     * @param int|string $reasonID
     * @return object
     * @since 5.3.0
     */
    public function getReason(int|string $reasonID): object
    {
        $reasonID = (int)$reasonID;
        $result   = new \stdClass();
        if ($reasonID === 0) {
            return $result;
        }
        $reasons = $this->reasons ?? $this->getReasons();
        foreach ($reasons as $reason) {
            if ($reason->reasonID === $reasonID) {
                $result = $reason;
                break;
            }
        }
        return $result;
    }

    /**
     * @return RMADataTableObject
     * @since 5.3.0
     */
    public function getRMA(int $id): RMADataTableObject
    {
        return \array_values((new RMARepository())->getList([
            'id'         => $id,
            'customerID' => Frontend::getCustomer()->getID(),
            'langID'     => Shop::getLanguageID()
        ]))[0] ?? new RMADataTableObject();
    }

    /**
     * @return array
     * @since 5.3.0
     */
    public function getRMAs(): array
    {
        return \array_values((new RMARepository())->getList([
            'customerID' => Frontend::getCustomer()->getID(),
            'langID' => Shop::getLanguageID()
        ]));
    }

    /**
     * @param int $rmaID
     * @return array
     * @since 5.3.0
     */
    public static function getItems(int $rmaID): array
    {
        if ($rmaID === 0) {
            return [];
        }
        $rmaRepo = new RMARepository();
        $rma     = $rmaRepo->getList(['customerID' => Frontend::getCustomer()->getID(), 'id' => $rmaID]);
        return $rma->positions ?? [];
    }
}
