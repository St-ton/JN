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
         * Constructor
         *
         * @param int $kKonfigitem
         * @param int $kKundengruppe
         */
        public function __construct($kKonfigitem = 0, $kKundengruppe = 0)
        {
            if ((int)$kKonfigitem > 0 && (int)$kKundengruppe > 0) {
                $this->loadFromDB($kKonfigitem, $kKundengruppe);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfigitem
         * @param int $kKundengruppe
         */
        private function loadFromDB($kKonfigitem = 0, $kKundengruppe = 0)
        {
            $oObj = Shop::DB()->select(
                'tkonfigitempreis',
                'kKonfigitem',
                (int)$kKonfigitem,
                'kKundengruppe',
                (int)$kKundengruppe
            );

            if (isset($oObj->kKonfigitem, $oObj->kKundengruppe) &&
                $oObj->kKonfigitem > 0 &&
                $oObj->kKundengruppe > 0
            ) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
                $this->kKonfigitem   = (int)$this->kKonfigitem;
                $this->kKundengruppe = (int)$this->kKundengruppe;
                $this->kSteuerklasse = (int)$this->kSteuerklasse;
                $this->nTyp          = (int)$this->nTyp;
            }
        }

        /**
         * Store the class in the database
         *
         * @param bool $bPrim - Controls the return of the method
         * @return bool|int
         */
        public function save($bPrim = true)
        {
            $oObj        = new stdClass();
            $cMember_arr = array_keys(get_object_vars($this));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $oObj->$cMember = $this->$cMember;
                }
            }
            unset($oObj->kKonfigitem, $oObj->kKundengruppe);

            $kPrim = Shop::DB()->insert('tkonfigitempreis', $oObj);

            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }

            return false;
        }

        /**
         * Update the class in the database
         *
         * @return int
         */
        public function update()
        {
            $_upd                = new stdClass();
            $_upd->kSteuerklasse = $this->getSteuerklasse();
            $_upd->fPreis        = $this->fPreis;
            $_upd->nTyp          = $this->getTyp();

            return Shop::DB()->update(
                'tkonfigitempreis',
                ['kKonfigitem', 'kKundengruppe'],
                [$this->getKonfigitem(), $this->getKundengruppe()],
                $_upd
            );
        }

        /**
         * Delete the class in the database
         *
         * @return int
         */
        public function delete()
        {
            return Shop::DB()->delete(
                'tkonfigitempreis',
                ['kKonfigitem', 'kKundengruppe'],
                [(int)$this->kKonfigitem, (int)$this->kKundengruppe]
            );
        }

        /**
         * @param int $kKonfigitem
         * @return $this
         */
        public function setKonfigitem($kKonfigitem)
        {
            $this->kKonfigitem = (int)$kKonfigitem;

            return $this;
        }

        /**
         * @param int $kKundengruppe
         * @return $this
         */
        public function setKundengruppe($kKundengruppe)
        {
            $this->kKundengruppe = (int)$kKundengruppe;

            return $this;
        }

        /**
         * @param int $kSteuerklasse
         * @return $this
         */
        public function setSteuerklasse($kSteuerklasse)
        {
            $this->kSteuerklasse = (int)$kSteuerklasse;

            return $this;
        }

        /**
         * @param float $fPreis
         * @return $this
         */
        public function setPreis($fPreis)
        {
            $this->fPreis = (float)$fPreis;

            return $this;
        }

        /**
         * @return int
         */
        public function getKonfigitem()
        {
            return (int)$this->kKonfigitem;
        }

        /**
         * @return int
         */
        public function getKundengruppe()
        {
            return (int)$this->kKundengruppe;
        }

        /**
         * @return int
         */
        public function getSteuerklasse()
        {
            return (int)$this->kSteuerklasse;
        }

        /**
         * @param bool $bConvertCurrency
         * @return float
         */
        public function getPreis($bConvertCurrency = false)
        {
            $fPreis = $this->fPreis;
            if ($bConvertCurrency && $fPreis > 0) {
                $fPreis *= Session::Currency()->getConversionFactor();
            }

            return $fPreis;
        }

        /**
         * @return int
         */
        public function getTyp()
        {
            return $this->nTyp;
        }
    }
}
