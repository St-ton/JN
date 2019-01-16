<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfigitempreis
     */
    class Konfigitempreis
    {
        /**
         * @var int
         */
        protected $kKonfigitem;

        /**
         * @var int
         */
        protected $kKundengruppe;

        /**
         * @var int
         */
        protected $kSteuerklasse;

        /**
         * @var float
         */
        protected $fPreis;

        /**
         * @var int
         */
        protected $nTyp;

        /**
         * Konfigitempreis constructor.
         * @param int $kKonfigitem
         * @param int $kKundengruppe
         */
        public function __construct(int $kKonfigitem = 0, int $kKundengruppe = 0)
        {
            if ($kKonfigitem > 0 && $kKundengruppe > 0) {
                $this->loadFromDB($kKonfigitem, $kKundengruppe);
            }
        }

        /**
         * @param int $kKonfigitem
         * @param int $kKundengruppe
         */
        private function loadFromDB(int $kKonfigitem = 0, int $kKundengruppe = 0)
        {
            $item = Shop::Container()->getDB()->select(
                'tkonfigitempreis',
                'kKonfigitem',
                $kKonfigitem,
                'kKundengruppe',
                $kKundengruppe
            );

            if (isset($item->kKonfigitem, $item->kKundengruppe)
                && $item->kKonfigitem > 0
                && $item->kKundengruppe > 0
            ) {
                foreach (array_keys(get_object_vars($item)) as $member) {
                    $this->$member = $item->$member;
                }
                $this->kKonfigitem   = (int)$this->kKonfigitem;
                $this->kKundengruppe = (int)$this->kKundengruppe;
                $this->kSteuerklasse = (int)$this->kSteuerklasse;
                $this->nTyp          = (int)$this->nTyp;
            }
        }

        /**
         * @param bool $bPrim
         * @return bool|int
         */
        public function save(bool $bPrim = true)
        {
            $ins = new stdClass();
            foreach (array_keys(get_object_vars($this)) as $member) {
                $ins->$member = $this->$member;
            }
            unset($ins->kKonfigitem, $ins->kKundengruppe);

            $kPrim = Shop::Container()->getDB()->insert('tkonfigitempreis', $ins);

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
            $upd                = new stdClass();
            $upd->kSteuerklasse = $this->getSteuerklasse();
            $upd->fPreis        = $this->fPreis;
            $upd->nTyp          = $this->getTyp();

            return Shop::Container()->getDB()->update(
                'tkonfigitempreis',
                ['kKonfigitem', 'kKundengruppe'],
                [$this->getKonfigitem(), $this->getKundengruppe()],
                $upd
            );
        }

        /**
         * @return int
         */
        public function delete(): int
        {
            return Shop::Container()->getDB()->delete(
                'tkonfigitempreis',
                ['kKonfigitem', 'kKundengruppe'],
                [(int)$this->kKonfigitem, (int)$this->kKundengruppe]
            );
        }

        /**
         * @param int $kKonfigitem
         * @return $this
         */
        public function setKonfigitem(int $kKonfigitem): self
        {
            $this->kKonfigitem = $kKonfigitem;

            return $this;
        }

        /**
         * @param int $kKundengruppe
         * @return $this
         */
        public function setKundengruppe(int $kKundengruppe):self
        {
            $this->kKundengruppe = $kKundengruppe;

            return $this;
        }

        /**
         * @param int $kSteuerklasse
         * @return $this
         */
        public function setSteuerklasse(int $kSteuerklasse): self
        {
            $this->kSteuerklasse = $kSteuerklasse;

            return $this;
        }

        /**
         * @param float $fPreis
         * @return $this
         */
        public function setPreis($fPreis): self
        {
            $this->fPreis = (float)$fPreis;

            return $this;
        }

        /**
         * @return int
         */
        public function getKonfigitem(): int
        {
            return (int)$this->kKonfigitem;
        }

        /**
         * @return int
         */
        public function getKundengruppe(): int
        {
            return (int)$this->kKundengruppe;
        }

        /**
         * @return int
         */
        public function getSteuerklasse(): int
        {
            return (int)$this->kSteuerklasse;
        }

        /**
         * @param bool $bConvertCurrency
         * @return float|null
         */
        public function getPreis(bool $bConvertCurrency = false)
        {
            $fPreis = $this->fPreis;
            if ($bConvertCurrency && $fPreis > 0) {
                $fPreis *= \Session\Frontend::getCurrency()->getConversionFactor();
            }

            return $fPreis;
        }

        /**
         * @return int|null
         */
        public function getTyp()
        {
            return $this->nTyp;
        }
    }
}
