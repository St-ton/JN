<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfiggruppe
     */
    class Konfiggruppe implements JsonSerializable
    {
        /**
         * @var int
         */
        protected $kKonfiggruppe;

        /**
         * @var string
         */
        protected $cBildPfad;

        /**
         * @var int
         */
        protected $nMin;

        /**
         * @var int
         */
        protected $nMax;

        /**
         * @var int
         */
        protected $nTyp;

        /**
         * @var int
         */
        protected $nSort;

        /**
         * @var string
         */
        public $cKommentar;

        /**
         * @var object
         */
        public $oSprache;

        /**
         * @var array
         */
        public $oItem_arr = [];

        /**
         * @var bool|null
         */
        public $bAktiv;

        /**
         * Constructor
         *
         * @param int $kKonfiggruppe
         * @param int $kSprache
         */
        public function __construct(int $kKonfiggruppe = 0, int $kSprache = 0)
        {
            $this->kKonfiggruppe = $kKonfiggruppe;
            if ($this->kKonfiggruppe > 0) {
                $this->loadFromDB($this->kKonfiggruppe, $kSprache);
            }
        }

        /**
         * Specify data which should be serialized to JSON
         *
         * @return array
         */
        public function jsonSerialize()
        {
            if ($this->oSprache === null) {
                $this->oSprache  = new Konfiggruppesprache($this->kKonfiggruppe);
            }
            $override = [
                'kKonfiggruppe' => (int)$this->kKonfiggruppe,
                'cBildPfad'     => $this->getBildPfad(),
                'nMin'          => (float)$this->nMin,
                'nMax'          => (float)$this->nMax,
                'nTyp'          => (int)$this->nTyp,
                'fInitial'      => (float)$this->getInitQuantity(),
                'bAnzahl'       => $this->getAnzeigeTyp() == KONFIG_ANZEIGE_TYP_RADIO || $this->getAnzeigeTyp() == KONFIG_ANZEIGE_TYP_DROPDOWN,
                'cName'         => $this->oSprache->getName(),
                'cBeschreibung' => $this->oSprache->getBeschreibung(),
                'oItem_arr'     => $this->oItem_arr
            ];
            $result = array_merge(get_object_vars($this), $override);

            return StringHandler::utf8_convert_recursive($result);
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfiggruppe
         * @param int $kSprache
         * @return $this
         */
        private function loadFromDB(int $kKonfiggruppe = 0, int $kSprache = 0)
        {
            $oObj = Shop::Container()->getDB()->select('tkonfiggruppe', 'kKonfiggruppe', $kKonfiggruppe);
            if (isset($oObj->kKonfiggruppe) && $oObj->kKonfiggruppe > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
                if (!$kSprache) {
                    $kSprache = Shop::getLanguageID();
                }
                $this->oSprache  = new Konfiggruppesprache($this->kKonfiggruppe, $kSprache);
                $this->oItem_arr = Konfigitem::fetchAll($this->kKonfiggruppe);
            }

            return $this;
        }

        /**
         * @param bool $bPrim
         * @return bool|int
         */
        public function save(bool $bPrim = true)
        {
            $oObj             = new stdClass();
            $oObj->cBildPfad  = $this->cBildPfad;
            $oObj->nMin       = $this->nMin;
            $oObj->nMax       = $this->nMax;
            $oObj->nTyp       = $this->nTyp;
            $oObj->nSort      = $this->nSort;
            $oObj->cKommentar = $this->cKommentar;

            $kPrim = Shop::Container()->getDB()->insert('tkonfiggruppe', $oObj);
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
            $_upd             = new stdClass();
            $_upd->cBildPfad  = $this->cBildPfad;
            $_upd->nMin       = $this->nMin;
            $_upd->nMax       = $this->nMax;
            $_upd->nTyp       = $this->nTyp;
            $_upd->nSort      = $this->nSort;
            $_upd->cKommentar = $this->cKommentar;

            return Shop::Container()->getDB()->update('tkonfiggruppe', 'kKonfiggruppe', (int)$this->kKonfiggruppe, $_upd);
        }

        /**
         * @return int
         */
        public function delete(): int
        {
            return Shop::Container()->getDB()->delete('tkonfiggruppe', 'kKonfiggruppe', (int)$this->kKonfiggruppe);
        }

        /**
         * @param int $kKonfiggruppe
         * @return $this
         */
        public function setKonfiggruppe(int $kKonfiggruppe): self
        {
            $this->kKonfiggruppe = $kKonfiggruppe;

            return $this;
        }

        /**
         * @param string $cBildPfad
         * @return $this
         */
        public function setBildPfad($cBildPfad): self
        {
            $this->cBildPfad = Shop::Container()->getDB()->escape($cBildPfad);

            return $this;
        }

        /**
         * @param int $nTyp
         * @return $this
         */
        public function setAnzeigeTyp(int $nTyp): self
        {
            $this->nTyp = $nTyp;

            return $this;
        }

        /**
         * @param int $nSort
         * @return $this
         */
        public function setSort(int $nSort): self
        {
            $this->nSort = $nSort;

            return $this;
        }

        /**
         * @return int
         */
        public function getKonfiggruppe()
        {
            return $this->kKonfiggruppe;
        }

        /**
         * @return string|null
         */
        public function getBildPfad()
        {
            return !empty($this->cBildPfad)
                ? PFAD_KONFIGURATOR_KLEIN . $this->cBildPfad
                : null;
        }

        /**
         * @return int
         */
        public function getMin()
        {
            return $this->nMin;
        }

        /**
         * @return int
         */
        public function getMax()
        {
            return $this->nMax;
        }

        /**
         * @return int
         */
        public function getAuswahlTyp()
        {
            return 0;
        }

        /**
         * @return int
         */
        public function getAnzeigeTyp()
        {
            return $this->nTyp;
        }

        /**
         * @return int
         */
        public function getSort()
        {
            return $this->nSort;
        }

        /**
         * @return mixed
         */
        public function getKommentar()
        {
            return $this->cKommentar;
        }

        /**
         * @return mixed
         */
        public function getSprache()
        {
            return $this->oSprache;
        }

        /**
         * @return int
         */
        public function getItemCount(): int
        {
            $oCount = Shop::Container()->getDB()->query("
                SELECT COUNT(*) AS nCount 
                    FROM tkonfigitem 
                    WHERE kKonfiggruppe = " . (int)$this->kKonfiggruppe,
                \DB\ReturnType::SINGLE_OBJECT
            );

            return isset($oCount->nCount)
                ? (int)$oCount->nCount
                : 0;
        }

        /**
         * @return bool
         */
        public function quantityEquals(): bool
        {
            $bEquals = false;
            if (count($this->oItem_arr) > 0) {
                $oItem = $this->oItem_arr[0];
                if ($oItem->getMin() == $oItem->getMax()) {
                    $bEquals = true;
                    $nKey    = $oItem->getMin();
                    foreach ($this->oItem_arr as &$oItem) {
                        if (!($oItem->getMin() == $oItem->getMax() && $oItem->getMin() == $nKey)) {
                            $bEquals = false;
                        }
                    }
                }
            }

            return $bEquals;
        }

        /**
         * @return int
         */
        public function getInitQuantity()
        {
            $fQuantity = 1;
            foreach ($this->oItem_arr as &$oItem) {
                if ($oItem->getSelektiert()) {
                    $fQuantity = $oItem->getInitial();
                }
            }

            return $fQuantity;
        }
    }
}
