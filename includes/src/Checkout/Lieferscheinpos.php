<?php declare(strict_types=1);

namespace JTL\Checkout;

use JTL\DB\DbInterface;
use JTL\Shop;
use stdClass;

/**
 * Class Lieferscheinpos
 * @package JTL\Checkout
 */
class Lieferscheinpos
{
    /**
     * @var int
     */
    protected $kLieferscheinPos;

    /**
     * @var int|null
     */
    protected ?int $kLieferschein = null;

    /**
     * @var int|null
     */
    protected ?int $kBestellPos = null;

    /**
     * @var int|null
     */
    protected ?int $kWarenlager = null;

    /**
     * @var float|null
     */
    protected ?float $fAnzahl = null;

    /**
     * @var Lieferscheinposinfo[]
     */
    public array $oLieferscheinPosInfo_arr = [];

    /**
     * @var object|null
     */
    public ?object $oPosition = null;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * Lieferscheinpos constructor.
     *
     * @param int              $id
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, ?DbInterface $db = null)
    {
        $this->db = $db ?? Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id = 0): self
    {
        $item = $this->db->select('tlieferscheinpos', 'kLieferscheinPos', $id);
        if ($item !== null && $item->kLieferscheinPos > 0) {
            $this->kLieferscheinPos = (int)$item->kLieferscheinPos;
            $this->kLieferschein    = (int)$item->kLieferschein;
            $this->kBestellPos      = (int)$item->kBestellPos;
            $this->kWarenlager      = (int)$item->kWarenlager;
            $this->fAnzahl          = (float)$item->fAnzahl;
        }

        return $this;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = true)
    {
        $ins                = new stdClass();
        $ins->kLieferschein = $this->getLieferschein();
        $ins->kBestellPos   = $this->getBestellPos();
        $ins->kWarenlager   = $this->getWarenlager();
        $ins->fAnzahl       = $this->getAnzahl();

        $id = $this->db->insert('tlieferscheinpos', $ins);
        if ($id > 0) {
            return $primary ? $id : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                = new stdClass();
        $upd->kLieferschein = $this->getLieferschein();
        $upd->kBestellPos   = $this->getBestellPos();
        $upd->kWarenlager   = $this->getWarenlager();
        $upd->fAnzahl       = $this->getAnzahl();

        return $this->db->update(
            'tlieferscheinpos',
            'kLieferscheinPos',
            $this->getLieferscheinPos(),
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->db->delete('tlieferscheinpos', 'kLieferscheinPos', $this->getLieferscheinPos());
    }

    /**
     * @param int $kLieferscheinPos
     * @return $this
     */
    public function setLieferscheinPos(int $kLieferscheinPos): self
    {
        $this->kLieferscheinPos = $kLieferscheinPos;

        return $this;
    }

    /**
     * @param int $kLieferschein
     * @return $this
     */
    public function setLieferschein(int $kLieferschein): self
    {
        $this->kLieferschein = $kLieferschein;

        return $this;
    }

    /**
     * @param int $kBestellPos
     * @return $this
     */
    public function setBestellPos(int $kBestellPos): self
    {
        $this->kBestellPos = $kBestellPos;

        return $this;
    }

    /**
     * @param int $kWarenlager
     * @return $this
     */
    public function setWarenlager(int $kWarenlager): self
    {
        $this->kWarenlager = $kWarenlager;

        return $this;
    }

    /**
     * @param float $fAnzahl
     * @return $this
     */
    public function setAnzahl($fAnzahl): self
    {
        $this->fAnzahl = (float)$fAnzahl;

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferscheinPos(): int
    {
        return (int)$this->kLieferscheinPos;
    }

    /**
     * @return int
     */
    public function getLieferschein(): int
    {
        return (int)$this->kLieferschein;
    }

    /**
     * @return int
     */
    public function getBestellPos(): int
    {
        return (int)$this->kBestellPos;
    }

    /**
     * @return int
     */
    public function getWarenlager(): int
    {
        return (int)$this->kWarenlager;
    }

    /**
     * @return float|int
     */
    public function getAnzahl()
    {
        return $this->fAnzahl;
    }
}
