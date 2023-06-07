<?php

namespace JTL\Retouren;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\DeliveryAddressTemplate;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use stdClass;

/**
 * Class Retoure
 * @package JTL\Retoure
 */
class Retoure
{
    /**
     * @var int
     */
    public int $kRetoure = 0;

    /**
     * @var int
     */
    public int $kKunde;

    /**
     * @var int
     */
    public int $kLieferadresse;

    /**
     * @var string|null
     * @values 'Angemeldet', 'Eingetroffen', 'Abgeschlossen'
     */
    public ?string $cStatus;

    /**
     * @var string
     */
    public string $dErstellt;

    /**
     * @var Collection
     */
    public Collection $PositionenArr;
    
    /**
     * @var string|null
     */
    public ?string $Status;
    
    /**
     * @var string|null
     */
    public ?string $ErstelltDatum;
    
    /**
     * @var DeliveryAddressTemplate|null
     */
    public ?DeliveryAddressTemplate $Lieferadresse;
    
    
    /**
     * @param int $kRetoure
     * @param int $customerID
     * @since 5.3.0
     */
    public function __construct(int $kRetoure = 0, int $customerID = 0)
    {
        if ($kRetoure > 0) {
            $this->loadFromDB($kRetoure, $customerID);
        }
    }
    
    /**
     * @param int $kRetoure
     * @param int $customerID
     * @return Retoure
     * @since 5.3.0
     */
    public function loadFromDB(int $kRetoure, int $customerID = 0): self
    {
        $customerID = ($customerID === 0) ? Frontend::getCustomer()->getID() : $customerID;
        $obj        = Shop::Container()->getDB()->select(
            'tretoure',
            'kRetoure',
            $kRetoure,
            'kKunde',
            $customerID
        );
        $members    = \array_keys(\get_object_vars($obj ?? new stdClass()));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        \executeHook(\HOOK_RETOURE_CLASS_LOADFROMDB, [
            'kRetoure'   => $kRetoure,
            'customerID' => $customerID
        ]);
        
        return $this;
    }

    /**
     * @param int $customerID
     * @return Collection
     * @since 5.3.0
     */
    public static function getRetouren(int $customerID = 0): Collection
    {
        return Shop::Container()->getDB()->getCollection(
            "SELECT tretoure.*
            FROM tretoure
            WHERE tretoure.kKunde = :customerID",
            ['customerID' => $customerID]
        )->map(static function ($retoure): self {
            $rt                 = new self();
            $rt->kRetoure       = $retoure->kRetoure;
            $rt->kKunde         = $retoure->kKunde;
            $rt->kLieferadresse = $retoure->kLieferadresse;
            $rt->cStatus        = $retoure->cStatus;
            $rt->Status         = \lang_retourestatus((int)$retoure->cStatus);
            $rt->dErstellt      = $retoure->dErstellt;
            $rt->ErstelltDatum  = date('d.m.Y H:i', \strtotime($retoure->dErstellt));

            return $rt;
        });
    }
    
    /**
     * @param int $customerID
     * @return Collection
     * @since 5.3.0
     */
    public static function getReturnableProducts(int $customerID = 0): Collection
    {
        $customerID       = ($customerID === 0) ? Frontend::getCustomer()->getID() : $customerID;
        $cancellationTime = Shopsetting::getInstance()->getValue(\CONF_GLOBAL, 'global_cancellation_time');
        return Shop::Container()->getDB()->getCollection(
            "SELECT twarenkorbpos.kArtikel, twarenkorbpos.cEinheit, twarenkorbpos.cArtNr,
       twarenkorbpos.fPreisEinzelNetto, twarenkorbpos.fMwSt, twarenkorbpos.cName, tbestellung.kBestellung,
       tbestellung.kKunde, tbestellung.kLieferadresse, tbestellung.cStatus, tbestellung.cBestellNr,
       tlieferscheinpos.kLieferscheinPos, tlieferscheinpos.kLieferschein, tlieferscheinpos.fAnzahl, tartikel.cSeo,
       DATE_FORMAT(FROM_UNIXTIME(tversand.dErstellt), '%d-%m-%Y') AS dErstellt,
       tartikelattribut.cWert AS nicht_retournierbar
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
        )->map(static function ($rArtikel): \stdClass {
            $rArtikel->Preis             = Preise::getLocalizedPriceString($rArtikel->fPreisEinzelNetto);
            $rArtikel->Artikel           = new Artikel();
            $rArtikel->Artikel->kArtikel = $rArtikel->kArtikel;
            $rArtikel->Artikel->holBilder();
            
            return $rArtikel;
        });
    }
    
    /**
     * @param Collection $rArtikel
     * @return array
     * @since 5.3.0
     */
    public static function getBestellnummern(Collection $rArtikel): array
    {
        return $rArtikel->unique('cBestellNr')->pluck('cBestellNr')->toArray();
    }
    
    /**
     * @return void
     * @since 5.3.0
     */
    public function fuelleRetoure(): void
    {
        $this->PositionenArr = RetoureItem::loadAllFromDB($this->kRetoure);
        if ($this->kLieferadresse > 0) {
            $this->Lieferadresse = new DeliveryAddressTemplate(
                Shop::Container()->getDB(),
                $this->kLieferadresse
            );
        }
    }
}
