<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_RMA)) {
    /**
     * Class RMAGrund
     */
    class RMAGrund
    {
        /**
         * @var int
         */
        protected $kRMAGrund;

        /**
         * @var int
         */
        protected $kSprache;

        /**
         * @var int
         */
        protected $nSort;

        /**
         * @var string
         */
        protected $cGrund;

        /**
         * @var string
         */
        protected $cKommentar;

        /**
         * @var int
         */
        protected $nAktiv;

        /**
         * Constructor
         *
         * @param int $kRMAGrund primary key
         */
        public function __construct($kRMAGrund = 0)
        {
            if ((int)$kRMAGrund > 0) {
                $this->loadFromDB($kRMAGrund);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kRMAGrund primary key
         */
        private function loadFromDB($kRMAGrund = 0)
        {
            $oObj = Shop::Container()->getDB()->query(
                "SELECT *
                  FROM trmagrund
                  WHERE kRMAGrund = " . (int)$kRMAGrund, 1
            );

            if ($oObj->kRMAGrund > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
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

            unset($oObj->kRMAGrund);

            $kPrim = Shop::Container()->getDB()->insert('trmagrund', $oObj);

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
            return Shop::Container()->getDB()->query(
                "UPDATE trmagrund
                   SET kRMAGrund = " . $this->kRMAGrund . ",
                       kSprache = " . $this->kSprache . ",
                       nSort = " . $this->nSort . ",
                       cGrund = '" . $this->cGrund . "',
                       cKommentar = '" . $this->cKommentar . "',
                       nAktiv = " . $this->nAktiv . "
                   WHERE kRMAGrund = " . $this->kRMAGrund, 3
            );
        }

        /**
         * Delete the class in the database
         *
         * @return int
         */
        public function delete()
        {
            return Shop::Container()->getDB()->delete('trmagrund', 'kRMAGrund', $this->getRMAGrund());
        }

        /**
         * @param int $kRMAGrund
         * @return $this
         */
        public function setRMAGrund($kRMAGrund)
        {
            $this->kRMAGrund = (int)$kRMAGrund;

            return $this;
        }

        /**
         * @param int $kSprache
         * @return $this
         */
        public function setSprache($kSprache)
        {
            $this->kSprache = (int)$kSprache;

            return $this;
        }

        /**
         * @param int $nSort
         * @return $this
         */
        public function setSort($nSort)
        {
            $this->nSort = (int)$nSort;

            return $this;
        }

        /**
         * @param string $cGrund
         * @return $this
         */
        public function setGrund($cGrund)
        {
            $this->cGrund = Shop::Container()->getDB()->escape($cGrund);

            return $this;
        }

        /**
         * @param string $cKommentar
         * @return $this
         */
        public function setKommentar($cKommentar)
        {
            $this->cKommentar = Shop::Container()->getDB()->escape($cKommentar);

            return $this;
        }

        /**
         * @param int $nAktiv
         * @return $this
         */
        public function setAktiv($nAktiv)
        {
            $this->nAktiv = (int)$nAktiv;

            return $this;
        }

        /**
         * @return int
         */
        public function getRMAGrund()
        {
            return (int)$this->kRMAGrund;
        }

        /**
         * @return int
         */
        public function getSprache()
        {
            return (int)$this->kSprache;
        }

        /**
         * @return int
         */
        public function getSort()
        {
            return (int)$this->nSort;
        }

        /**
         * @return string
         */
        public function getGrund()
        {
            return $this->cGrund;
        }

        /**
         * @return string
         */
        public function getKommentar()
        {
            return $this->cKommentar;
        }

        /**
         * @return int
         */
        public function getAktiv()
        {
            return $this->nAktiv;
        }

        /**
         * @param bool $bPrimary
         * @return array|bool|int
         */
        public function saveReason($bPrimary = false)
        {
            $cPlausi_arr = $this->checkReason();

            if (count($cPlausi_arr) === 0) {
                $kRMAGrund = $this->save();

                if ($kRMAGrund > 0) {
                    return $bPrimary ? $kRMAGrund : true;
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @return array|bool
         */
        public function updateReason()
        {
            $cPlausi_arr = $this->checkReason();

            if (count($cPlausi_arr) === 0) {
                $this->update();

                return true;
            }

            return $cPlausi_arr;
        }

        /**
         * @return array
         */
        private function checkReason()
        {
            $cPlausi_arr = [];
            // Sprache
            if ($this->kSprache == 0) {
                $cPlausi_arr['kSprache'] = 1;
            }
            // Grund
            if (strlen($this->cGrund) === 0) {
                $cPlausi_arr['cGrund'] = 1;
            }
            // Kommentar
            if (strlen($this->cKommentar) === 0) {
                $cPlausi_arr['cKommentar'] = 1;
            }

            return $cPlausi_arr;
        }

        /**
         * @param int  $kSprache
         * @param bool $bAktiv
         * @return array
         */
        public static function getAll($kSprache, $bAktiv = true)
        {
            $oRMAGrund_arr = [];
            $kSprache      = (int)$kSprache;

            if ($kSprache > 0) {
                $cSQL = '';
                if ($bAktiv) {
                    $cSQL = " AND nAktiv = 1";
                }
                $oObj_arr = Shop::Container()->getDB()->query(
                    "SELECT kRMAGrund
                        FROM trmagrund
                        WHERE kSprache = " . $kSprache . "
                        " . $cSQL . "
                        ORDER BY nSort", 2
                );

                if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                    foreach ($oObj_arr as $oObj) {
                        if (isset($oObj->kRMAGrund) && $oObj->kRMAGrund > 0) {
                            $oRMAGrund_arr[] = new self($oObj->kRMAGrund);
                        }
                    }
                }
            }

            return $oRMAGrund_arr;
        }
    }
}
