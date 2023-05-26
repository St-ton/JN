<?php

namespace JTL\Retouren;

use Illuminate\Support\Collection;
use JTL\Checkout\DeliveryAddressTemplate;
use JTL\Shop;

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
     * @since 5.3.0
     */
    public function __construct(int $kRetoure = 0)
    {
        if ($kRetoure > 0) {
            $this->loadFromDB($kRetoure);
        }
    }

    /**
     * @param int $kRetoure
     * @return Retoure
     * @since 5.3.0
     */
    public function loadFromDB(int $kRetoure): self
    {
        $obj     = Shop::Container()->getDB()->select('tretoure', 'kRetoure', $kRetoure);
        $members = \array_keys(\get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
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
            'SELECT tretoure.*
            FROM tretoure
            WHERE tretoure.kKunde LIKE :customerID',
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
