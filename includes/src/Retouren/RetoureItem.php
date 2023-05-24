<?php

namespace JTL\Retouren;

use Illuminate\Support\Collection;
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
     * @var float
     */
    public float $fPreisEinzelNetto;

    /**
     * @var float
     */
    public float $nAnzahl;

    /**
     * @var float|null
     */
    public ?float $fMwSt;

    /**
     * @var int
     */
    public int $nPosTyp;

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

            return $rtPos;
        });
    }
}
