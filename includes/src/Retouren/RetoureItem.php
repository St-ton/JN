<?php

namespace JTL\Retouren;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Shop;

/**
 * Class RetoureItem
 * @package JTL\Retouren
 */

class RetoureItem
{
    /**
     * @var int
     */
    public int $kRetourePos;

    /**
     * @var int|null
     */
    public ?int $kRetourePosWawi;

    /**
     * @var int
     */
    public int $kRetoure;

    /**
     * @var int|null
     */
    public ?int $kArtikel;
    
    /**
     * @var string
     */
    public $cName = '';
    
    /**
     * @var float
     */
    public float $fPreisEinzelNetto;
    
    /**
     * @var string
     */
    public string $Preis;

    /**
     * @var float
     */
    public float $nAnzahl;

    /**
     * @var float|null
     */
    public ?float $fMwSt;

    /**
     * @var string
     */
    public $cEinheit = '';

    /**
     * @var float|null
     */
    public ?float $fLagerbestandVorAbschluss;

    /**
     * @var int
     */
    public int $nLongestMinDelivery;

    /**
     * @var int
     */
    public int $nLongestMaxDelivery;

    /**
     * @var string|null
     */
    public ?string $cHinweis;

    /**
     * @var string|null
     */
    public ?string $cStatus;

    /**
     * @var string
     */
    public string $dErstellt;
    
    /**
     * @var Artikel|null
     */
    public ?Artikel $Artikel;

    /**
     * @param int $id
     * @since 5.3.0
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $kRetourePos
     * @return $this
     */
    public function loadFromDB(int $kRetourePos): self
    {
        $obj     = Shop::Container()->getDB()->select('tretourepos', 'kRetourePos', $kRetourePos);
        $members = \array_keys(\get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }
        $this->Preis = Preise::getLocalizedPriceString($obj->fPreisEinzelNetto);

        return $this;
    }

    public static function loadAllFromDB(int $kRetoure): Collection
    {
        return Shop::Container()->getDB()->getCollection(
            'SELECT tretourepos.*
            FROM tretourepos
            WHERE tretourepos.kRetoure LIKE :kRetoure',
            ['kRetoure' => $kRetoure]
        )->map(static function ($retourePos): self {
            $rtPos   = new self();
            $members = \array_keys(\get_object_vars($retourePos));
            foreach ($members as $member) {
                $rtPos->$member = $retourePos->$member;
            }
            $rtPos->Preis = Preise::getLocalizedPriceString($retourePos->fPreisEinzelNetto);

            return $rtPos;
        });
    }
    
    /**
     * @return void
     * @since 5.3.0
     */
    public function fuelleArtikel(): void
    {
        if ($this->kArtikel > 0) {
            $this->Artikel = new Artikel();
            $this->Artikel->fuelleArtikel($this->kArtikel);
        }
    }
}