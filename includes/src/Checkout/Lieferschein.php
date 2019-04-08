<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Checkout;

use JTL\Shop;
use stdClass;

/**
 * Class Lieferschein
 * @package JTL\Checkout
 */
class Lieferschein
{
    /**
     * @var int
     */
    protected $kLieferschein;

    /**
     * @var int
     */
    protected $kInetBestellung;

    /**
     * @var string
     */
    protected $cLieferscheinNr;

    /**
     * @var string
     */
    protected $cHinweis;

    /**
     * @var int
     */
    protected $nFulfillment;

    /**
     * @var int
     */
    protected $nStatus;

    /**
     * @var string
     */
    protected $dErstellt;

    /**
     * @var bool
     */
    protected $bEmailVerschickt;

    /**
     * @var array
     */
    public $oLieferscheinPos_arr = [];

    /**
     * @var array
     */
    public $oVersand_arr = [];

    /**
     * @var array
     */
    public $oPosition_arr = [];

    /**
     * Constructor
     *
     * @param int    $kLieferschein
     * @param object $oData
     */
    public function __construct(int $kLieferschein = 0, $oData = null)
    {
        if ($kLieferschein > 0) {
            $this->loadFromDB($kLieferschein, $oData);
        }
    }

    /**
     * @param int    $kLieferschein
     * @param object $oData
     * @return $this
     */
    private function loadFromDB(int $kLieferschein = 0, $oData = null): self
    {
        $item = Shop::Container()->getDB()->select('tlieferschein', 'kLieferschein', $kLieferschein);
        if ($item !== null && $item->kLieferschein > 0) {
            foreach (\array_keys(\get_object_vars($item)) as $member) {
                $setter = 'set' . \mb_substr($member, 1);
                if (\is_callable([$this, $setter])) {
                    $this->$setter($item->$member);
                } else {
                    $this->$member = $item->$member;
                }
            }

            $positions = Shop::Container()->getDB()->selectAll(
                'tlieferscheinpos',
                'kLieferschein',
                $kLieferschein,
                'kLieferscheinPos'
            );
            foreach ($positions as $position) {
                $pos                           = new Lieferscheinpos($position->kLieferscheinPos);
                $pos->oLieferscheinPosInfo_arr = [];

                $posInfos = Shop::Container()->getDB()->selectAll(
                    'tlieferscheinposinfo',
                    'kLieferscheinPos',
                    (int)$position->kLieferscheinPos,
                    'kLieferscheinPosInfo'
                );
                if (\is_array($posInfos) && !empty($posInfos)) {
                    foreach ($posInfos as $posInfo) {
                        $pos->oLieferscheinPosInfo_arr[] = new Lieferscheinposinfo($posInfo->kLieferscheinPosInfo);
                    }
                }

                $this->oLieferscheinPos_arr[] = $pos;
            }

            $shippings = Shop::Container()->getDB()->selectAll(
                'tversand',
                'kLieferschein',
                $kLieferschein,
                'kVersand'
            );
            foreach ($shippings as $shipping) {
                $this->oVersand_arr[] = new Versand($shipping->kVersand, $oData);
            }
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins                   = new stdClass();
        $ins->kInetBestellung  = $this->kInetBestellung;
        $ins->cLieferscheinNr  = $this->cLieferscheinNr;
        $ins->cHinweis         = $this->cHinweis;
        $ins->nFulfillment     = $this->nFulfillment;
        $ins->nStatus          = $this->nStatus;
        $ins->dErstellt        = $this->dErstellt;
        $ins->bEmailVerschickt = $this->bEmailVerschickt ? 1 : 0;
        $kPrim                 = Shop::Container()->getDB()->insert('tlieferschein', $ins);
        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                   = new stdClass();
        $upd->kInetBestellung  = $this->kInetBestellung;
        $upd->cLieferscheinNr  = $this->cLieferscheinNr;
        $upd->cHinweis         = $this->cHinweis;
        $upd->nFulfillment     = $this->nFulfillment;
        $upd->nStatus          = $this->nStatus;
        $upd->dErstellt        = $this->dErstellt;
        $upd->bEmailVerschickt = $this->bEmailVerschickt ? 1 : 0;

        return Shop::Container()->getDB()->update('tlieferschein', 'kLieferschein', (int)$this->kLieferschein, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tlieferschein', 'kLieferschein', $this->getLieferschein());
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
     * @param int $kInetBestellung
     * @return $this
     */
    public function setInetBestellung(int $kInetBestellung): self
    {
        $this->kInetBestellung = $kInetBestellung;

        return $this;
    }

    /**
     * @param string $cLieferscheinNr
     * @return $this
     */
    public function setLieferscheinNr($cLieferscheinNr): self
    {
        $this->cLieferscheinNr = Shop::Container()->getDB()->escape($cLieferscheinNr);

        return $this;
    }

    /**
     * @param string $cHinweis
     * @return $this
     */
    public function setHinweis($cHinweis): self
    {
        $this->cHinweis = Shop::Container()->getDB()->escape($cHinweis);

        return $this;
    }

    /**
     * @param int $nFulfillment
     * @return $this
     */
    public function setFulfillment(int $nFulfillment): self
    {
        $this->nFulfillment = $nFulfillment;

        return $this;
    }

    /**
     * @param int $nStatus
     * @return $this
     */
    public function setStatus(int $nStatus): self
    {
        $this->nStatus = $nStatus;

        return $this;
    }

    /**
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt): self
    {
        $this->dErstellt = Shop::Container()->getDB()->escape($dErstellt);

        return $this;
    }

    /**
     * @param bool $bEmailVerschickt
     * @return $this
     */
    public function setEmailVerschickt($bEmailVerschickt): self
    {
        $this->bEmailVerschickt = (bool)$bEmailVerschickt;

        return $this;
    }

    /**
     * @return int
     */
    public function getLieferschein(): int
    {
        return (int)$this->kLieferschein;
    }

    /**
     * @return int|null
     */
    public function getInetBestellung(): ?int
    {
        return $this->kInetBestellung;
    }

    /**
     * @return string|null
     */
    public function getLieferscheinNr(): ?string
    {
        return $this->cLieferscheinNr;
    }

    /**
     * @return string|null
     */
    public function getHinweis(): ?string
    {
        return $this->cHinweis;
    }

    /**
     * @return int|null
     */
    public function getFulfillment(): ?int
    {
        return $this->nFulfillment;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->nStatus;
    }

    /**
     * @return string|null
     */
    public function getErstellt(): ?string
    {
        return $this->dErstellt;
    }

    /**
     * @return bool|null
     */
    public function getEmailVerschickt(): ?bool
    {
        return $this->bEmailVerschickt;
    }
}
