<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_RMA)) {
    /**
     * Class RMAArtikel
     */
    class RMAArtikel
    {
        /**
         * @access public
         * @var int
         */
        public $kRMA;

        /**
         * @access public
         * @var int
         */
        public $kBestellung;

        /**
         * @access public
         * @var int
         */
        public $kArtikel;

        /**
         * @access public
         * @var float
         */
        public $fAnzahl;

        /**
         * @access public
         * @var string
         */
        public $cGrund;

        /**
         * @access public
         * @var string
         */
        public $cKommentar;

        /**
         * @param int  $kRMA
         * @param int  $kArtikel
         * @param bool $bProductName
         * @param bool $bProductObject
         * @param int  $kSprache
         * @param int  $kKundengruppe
         */
        public function __construct($kRMA = 0, $kArtikel = 0, $bProductName = false, $bProductObject = false, $kSprache = 0, $kKundengruppe = 0)
        {
            if (intval($kRMA) > 0 && intval($kArtikel) > 0) {
                $this->loadFromDB($kRMA, $kArtikel, $bProductName, $bProductObject, $kSprache, $kKundengruppe);
            }
        }

        /**
         * @param int  $kRMA
         * @param int  $kArtikel
         * @param bool $bProductName
         * @param bool $bProductObject
         * @param int  $kSprache
         * @param int  $kKundengruppe
         */
        private function loadFromDB($kRMA, $kArtikel, $bProductName, $bProductObject, $kSprache, $kKundengruppe)
        {
            $oObj = Shop::DB()->query(
                "SELECT *
                  FROM trmaartikel
                  WHERE kRMA = " . intval($kRMA) . "
                    AND kArtikel = " . intval($kArtikel), 1
            );
            if (isset($oObj->kRMA) && $oObj->kRMA > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
            }
            // Load only product name
            if ($bProductName) {
                $oSprache = gibStandardsprache();
                if (($kSprache > 0 && $kSprache == $oSprache->kSprache) || !$kSprache) {
                    $oObj = Shop::DB()->query("SELECT cName FROM tartikel WHERE kArtikel = " . intval($kArtikel), 1);
                    if (isset($oObj->cName) && strlen($oObj->cName) > 0) {
                        $this->cArtikelName = $oObj->cName;
                    }
                } else {
                    $oObj = Shop::DB()->query("SELECT cName FROM tartikelsprache WHERE kArtikel = " . intval($kArtikel) . " AND kSprache = " . intval($kSprache), 1);
                    if (isset($oObj->cName) && strlen($oObj->cName) > 0) {
                        $this->cArtikelName = $oObj->cName;
                    } else {
                        $oObj = Shop::DB()->query("SELECT cName FROM tartikel WHERE kArtikel = " . intval($kArtikel), 1);
                        if (isset($oObj->cName) && strlen($oObj->cName) > 0) {
                            $this->cArtikelName = $oObj->cName;
                        }
                    }
                }

                $oTMP = Shop::DB()->query("SELECT cSeo FROM tseo WHERE cKey = 'kArtikel' AND kKey = " . intval($kArtikel), 1);
                if (isset($oTMP->cSeo) && strlen($oTMP->cSeo) > 0) {
                    $this->cArtikelURL = Shop::getURL() . '/' . $oTMP->cSeo;
                } else {
                    $this->cArtikelURL = Shop::getURL() . '/index.php?a=' . intval($kArtikel);
                }
            }

            // Load complete product object
            if ($bProductObject) {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
                $this->oArtikel = new Artikel();
                $this->oArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions(), $kKundengruppe, $kSprache);
            }
        }

        /**
         * Store the class in the database
         *
         * @param bool $bPrim - Controls the return of the method
         * @return bool|int
         * @access public
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

            unset($oObj->cArtikelName);
            unset($oObj->cArtikelURL);
            unset($oObj->oArtikel);

            $kPrim = Shop::DB()->insert('trmaartikel', $oObj);

            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }

            return false;
        }

        /**
         * Update the class in the database
         *
         * @return int
         * @access public
         */
        public function update()
        {
            return Shop::DB()->query(
                "UPDATE trmaartikel
                   SET kRMA = " . $this->kRMA . ",
                       kBestellung = " . $this->kBestellung . ",
                       kArtikel = " . $this->kArtikel . ",
                       fAnzahl = " . $this->fAnzahl . ",
                       cGrund = " . $this->cGrund . ",
                       cKommentar = " . $this->cKommentar . "
                   WHERE kRMA = " . $this->kRMA, 3
            );
        }

        /**
         * Delete the class in the database
         *
         * @return int
         * @access public
         */
        public function delete()
        {
            return Shop::DB()->query(
                "DELETE FROM trmaartikel
                   WHERE kRMA = " . $this->kRMA, 3
            );
        }

        /**
         * Sets the kRMA
         *
         * @access public
         * @param int $kRMA
         * @return $this
         */
        public function setRMA($kRMA)
        {
            $this->kRMA = intval($kRMA);

            return $this;
        }

        /**
         * Sets the kBestellung
         *
         * @access public
         * @param int $kBestellung
         * @return $this
         */
        public function setBestellung($kBestellung)
        {
            $this->kBestellung = intval($kBestellung);

            return $this;
        }

        /**
         * Sets the kArtikel
         *
         * @access public
         * @param int $kArtikel
         * @return $this
         */
        public function setArtikel($kArtikel)
        {
            $this->kArtikel = intval($kArtikel);

            return $this;
        }

        /**
         * Sets the fAnzahl
         *
         * @access public
         * @param float $fAnzahl
         * @return $this
         */
        public function setAnzahl($fAnzahl)
        {
            $this->fAnzahl = floatval($fAnzahl);

            return $this;
        }

        /**
         * Sets the cGrund
         *
         * @access public
         * @param string $cGrund
         * @return $this
         */
        public function setGrund($cGrund)
        {
            $this->cGrund = Shop::DB()->escape($cGrund);

            return $this;
        }

        /**
         * Sets the cKommentar
         *
         * @access public
         * @param string $cKommentar
         * @return $this
         */
        public function setKommentar($cKommentar)
        {
            $this->cKommentar = Shop::DB()->escape($cKommentar);

            return $this;
        }

        /**
         * Gets the kRMA
         *
         * @access public
         * @return int
         */
        public function getRMA()
        {
            return $this->kRMA;
        }

        /**
         * Gets the kBestellung
         *
         * @access public
         * @return int
         */
        public function getBestellung()
        {
            return $this->kBestellung;
        }

        /**
         * Gets the kArtikel
         *
         * @access public
         * @return int
         */
        public function getArtikel()
        {
            return $this->kArtikel;
        }

        /**
         * Gets the fAnzahl
         *
         * @access public
         * @return float
         */
        public function getAnzahl()
        {
            return $this->fAnzahl;
        }

        /**
         * Gets the cGrund
         *
         * @access public
         * @return string
         */
        public function getGrund()
        {
            return $this->cGrund;
        }

        /**
         * Gets the cKommentar
         *
         * @access public
         * @return string
         */
        public function getKommentar()
        {
            return $this->cKommentar;
        }

        /**
         * @param int   $kBestellung
         * @param array $kArtikel_arr
         * @param array $cRMAPostAssoc_arr
         * @return bool
         */
        public static function isOrderExisting($kBestellung, $kArtikel_arr, &$cRMAPostAssoc_arr)
        {
            $kBestellung = intval($kBestellung);

            if ($kBestellung > 0 && is_array($kArtikel_arr) && count($kArtikel_arr) > 0 && is_array($cRMAPostAssoc_arr) && count($cRMAPostAssoc_arr) > 0) {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';

                foreach ($kArtikel_arr as $kArtikel) {
                    $fRMAArtikelQuantity = self::getRMAQuantity($kBestellung, $kArtikel); // Bereits zurueckgeschickte Anzahl

                    if ($fRMAArtikelQuantity && $fRMAArtikelQuantity > 0) {
                        // TODO: Anzahl checken
                        $kRMAGrund  = intval($cRMAPostAssoc_arr['cGrund'][$kArtikel]);
                        $fAnzahlNow = floatval($cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund]); // Aktuelle Anzahl

                        $fAnzahlBestellung = Bestellung::getProductAmount($kBestellung, $kArtikel);
                        if ($fAnzahlBestellung) {
                            // Aktuelle Anzahl - Bereits zurueckgeschickte Anzahl > Bestellte Anzahl
                            if ($fAnzahlNow - $fRMAArtikelQuantity > $fAnzahlBestellung) {
                                return true;
                            }

                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }

            return true;
        }

        /**
         * @param int $kBestellung
         * @return array
         */
        public static function getProductsByOrder($kBestellung)
        {
            $kBestellung  = intval($kBestellung);
            $kArtikel_arr = array();

            if ($kBestellung > 0) {
                $oObj_arr = Shop::DB()->query(
                    "SELECT kArtikel
                        FROM trmaartikel
                        WHERE kBestellung = " . $kBestellung, 2
                );

                if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                    foreach ($oObj_arr as $oObj) {
                        $kArtikel_arr[] = $oObj->kArtikel;
                    }
                }
            }

            return $kArtikel_arr;
        }

        /**
         * @param int $kBestellung
         * @return bool
         */
        public static function hasOrderProducts($kBestellung)
        {
            $kBestellung = intval($kBestellung);
            if ($kBestellung > 0) {
                $kArtikel_arr = self::getProductsByOrder($kBestellung);
                if (count($kArtikel_arr) > 0) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param int $kBestellung
         * @param int $kArtikel
         * @return bool
         */
        public static function getRMAQuantity($kBestellung, $kArtikel)
        {
            $kBestellung = intval($kBestellung);
            $kArtikel    = intval($kArtikel);
            if ($kBestellung > 0 && $kArtikel > 0) {
                $oObj = Shop::DB()->query(
                    "SELECT SUM(fAnzahl) AS fAnzahlSum
                        FROM trmaartikel
                        WHERE kArtikel = " . $kArtikel . "
                            AND kBestellung = " . $kBestellung, 1
                );

                if (isset($oObj->fAnzahlSum) && $oObj->fAnzahlSum > 0) {
                    return $oObj->fAnzahlSum;
                }
            }

            return false;
        }
    }
}
