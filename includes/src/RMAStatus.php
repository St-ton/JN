<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_RMA)) {
    /**
     * Class RMAStatus
     */
    class RMAStatus
    {
        /**
         * @var int
         */
        protected $kRMAStatus;

        /**
         * @var int
         */
        protected $kSprache;

        /**
         * @var string
         */
        protected $cStatus;

        /**
         * @var string
         */
        protected $eFunktion;

        /**
         * @var int
         */
        protected $nAktiv;

        /**
         * Constructor
         *
         * @param int $kRMAStatus
         */
        public function __construct($kRMAStatus = 0)
        {
            if ((int)$kRMAStatus > 0) {
                $this->loadFromDB($kRMAStatus);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kRMAStatus
         */
        private function loadFromDB($kRMAStatus)
        {
            $oObj = Shop::Container()->getDB()->select('trmastatus', 'kRMAStatus', (int)$kRMAStatus);
            if (isset($oObj->kRMAStatus) && $oObj->kRMAStatus > 0) {
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
            unset($oObj->kRMAStatus);

            $kPrim = Shop::Container()->getDB()->insert('trmastatus', $oObj);

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
            $_upd            = new stdClass();
            $_upd->kSprache  = $this->getSprache();
            $_upd->cStatus   = $this->getStatus();
            $_upd->eFunktion = $this->getFunktion();
            $_upd->nAktiv    = $this->getAktiv();

            return Shop::Container()->getDB()->update('trmastatus', 'kRMAStatus', $this->getRMAStatus(), $_upd);
        }

        /**
         * Delete the class in the database
         *
         * @return int
         */
        public function delete()
        {
            return Shop::Container()->getDB()->delete('trmastatus', 'kRMAStatus', $this->getRMAStatus());
        }

        /**
         * @param int $kRMAStatus
         * @return $this
         */
        public function setRMAStatus($kRMAStatus)
        {
            $this->kRMAStatus = (int)$kRMAStatus;

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
         * @param string $cStatus
         * @return $this
         */
        public function setStatus($cStatus)
        {
            $this->cStatus = Shop::Container()->getDB()->escape($cStatus);

            return $this;
        }

        /**
         * @param string $eFunktion
         * @return $this
         */
        public function setFunktion($eFunktion)
        {
            $this->eFunktion = Shop::Container()->getDB()->escape($eFunktion);

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
        public function getRMAStatus()
        {
            return (int)$this->kRMAStatus;
        }

        /**
         * @return int
         */
        public function getSprache()
        {
            return (int)$this->kSprache;
        }

        /**
         * @return string
         */
        public function getStatus()
        {
            return $this->cStatus;
        }

        /**
         * @return string
         */
        public function getFunktion()
        {
            return $this->eFunktion;
        }

        /**
         * @return int
         */
        public function getAktiv()
        {
            return (int)$this->nAktiv;
        }

        /**
         * @param bool $bPrimary
         * @return array|bool|int
         */
        public function saveStatus($bPrimary = false)
        {
            $cPlausi_arr = $this->checkStatus();

            if (count($cPlausi_arr) === 0) {
                $kRMAStatus = $this->save();

                if ($kRMAStatus > 0) {
                    return $bPrimary ? $kRMAStatus : true;
                }
            }

            return $cPlausi_arr;
        }

        /**
         * @return array|bool
         */
        public function updateStatus()
        {
            $cPlausi_arr = $this->checkStatus();

            if (count($cPlausi_arr) === 0) {
                $this->update();

                return true;
            }

            return $cPlausi_arr;
        }

        /**
         * @return array
         */
        private function checkStatus()
        {
            $cPlausi_arr = [];
            // Sprache
            if ($this->kSprache == 0) {
                $cPlausi_arr['kSprache'] = 1;
            }
            // Status
            if (strlen($this->cStatus) === 0) {
                $cPlausi_arr['cStatus'] = 1;
            }
            // Funktion
            if (strlen($this->eFunktion) === 0) {
                $cPlausi_arr['eFunktion'] = 1;
            } elseif (!$this->checkDoubleFunction()) {
                $cPlausi_arr['eFunktion'] = 2;
            }

            return $cPlausi_arr;
        }

        /**
         * @return bool
         */
        private function checkDoubleFunction()
        {
            if ($this->kSprache > 0 && strlen($this->eFunktion) > 0) {
                $oObj = Shop::Container()->getDB()->query(
                    "SELECT kRMAStatus
                        FROM trmastatus
                        WHERE kSprache = " . $this->kSprache . "
                            AND eFunktion = '" . $this->eFunktion . "'",
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (!isset($oObj->kRMAStatus) || (int)$oObj->kRMAStatus === 0) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param int  $kSprache
         * @param bool $bAssoc
         * @param bool $bAktiv
         * @return array
         */
        public static function getAll($kSprache, $bAssoc = true, $bAktiv = true)
        {
            $oRMAStatus_arr = [];
            $kSprache       = (int)$kSprache;
            if ($kSprache > 0) {
                $cSQL = '';
                if ($bAktiv) {
                    $cSQL = " AND nAktiv = 1";
                }
                $oObj_arr = Shop::Container()->getDB()->query(
                    "SELECT kRMAStatus
                        FROM trmastatus
                        WHERE kSprache = " . $kSprache . $cSQL,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                    foreach ($oObj_arr as $oObj) {
                        if (isset($oObj->kRMAStatus) && $oObj->kRMAStatus > 0) {
                            if ($bAssoc) {
                                $oRMAStatus_arr[$oObj->kRMAStatus] = new self($oObj->kRMAStatus);
                            } else {
                                $oRMAStatus_arr[] = new self($oObj->kRMAStatus);
                            }
                        }
                    }
                }
            }

            return $oRMAStatus_arr;
        }

        /**
         * @param string $cFunktion
         * @param int    $kSprache
         * @param bool   $bAktiv
         * @return bool|RMAStatus
         */
        public static function getFromFunction($cFunktion, $kSprache = 0, $bAktiv = true)
        {
            if (strlen($cFunktion) > 0) {
                $cSQL = '';
                if ($bAktiv) {
                    $cSQL = " AND nAktiv = 1";
                }
                $oObj = Shop::Container()->getDB()->query(
                    "SELECT kRMAStatus
                        FROM trmastatus
                        WHERE kSprache = " . (int)$kSprache . "
                            AND eFunktion = '" . StringHandler::filterXSS($cFunktion) . "'" . $cSQL,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oObj->kRMAStatus) && $oObj->kRMAStatus > 0) {
                    return new self($oObj->kRMAStatus);
                }
            }

            return false;
        }

        /**
         * @param array $cPlausi_arr
         * @return array
         */
        public static function mapPlausiError($cPlausi_arr)
        {
            $cError_arr = [];
            if (is_array($cPlausi_arr) &&
                count($cPlausi_arr) > 0 &&
                isset($cPlausi_arr['eFunktion']) &&
                $cPlausi_arr['eFunktion'] == 2
            ) {
                $cError_arr[] = "Die gewählte Funktion ist bereits vorhanden. Diese darf nicht doppelt belegt werden.";
            }

            return $cError_arr;
        }
    }
}
