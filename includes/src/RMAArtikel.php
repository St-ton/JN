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
         * @var int
         */
        public $kRMA;

        /**
         * @var int
         */
        public $kBestellung;

        /**
         * @var int
         */
        public $kArtikel;

        /**
         * @var float
         */
        public $fAnzahl;

        /**
         * @var string
         */
        public $cGrund;

        /**
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
            if ((int)$kRMA > 0 && (int)$kArtikel > 0) {
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
            $oObj = Shop::Container()->getDB()->select('trmaartikel', 'kRMA', (int)$kRMA, 'kArtikel', (int)$kArtikel);
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
                    $oObj = Shop::Container()->getDB()->query(
                        "SELECT cName 
                            FROM tartikel 
                            WHERE kArtikel = " . (int)$kArtikel,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (isset($oObj->cName) && strlen($oObj->cName) > 0) {
                        $this->cArtikelName = $oObj->cName;
                    }
                } else {
                    $oObj = Shop::Container()->getDB()->query(
                        "SELECT cName 
                            FROM tartikelsprache 
                            WHERE kArtikel = " . (int)$kArtikel . " 
                            AND kSprache = " . (int)$kSprache,
                        \DB\ReturnType::SINGLE_OBJECT
                    );
                    if (isset($oObj->cName) && strlen($oObj->cName) > 0) {
                        $this->cArtikelName = $oObj->cName;
                    } else {
                        $oObj = Shop::Container()->getDB()->query(
                            "SELECT cName 
                                FROM tartikel 
                                WHERE kArtikel = " . (int)$kArtikel,
                            \DB\ReturnType::SINGLE_OBJECT
                        );
                        if (isset($oObj->cName) && strlen($oObj->cName) > 0) {
                            $this->cArtikelName = $oObj->cName;
                        }
                    }
                }

                $oTMP = Shop::Container()->getDB()->query(
                    "SELECT cSeo 
                        FROM tseo 
                        WHERE cKey = 'kArtikel' 
                        AND kKey = " . (int)$kArtikel,
                    \DB\ReturnType::SINGLE_OBJECT
                );
                if (isset($oTMP->cSeo) && strlen($oTMP->cSeo) > 0) {
                    $this->cArtikelURL = Shop::getURL() . '/' . $oTMP->cSeo;
                } else {
                    $this->cArtikelURL = Shop::getURL() . '/?a=' . (int)$kArtikel;
                }
            }

            // Load complete product object
            if ($bProductObject) {
                $this->oArtikel = (new Artikel())
                    ->fuelleArtikel($kArtikel, Artikel::getDefaultOptions(), $kKundengruppe, $kSprache);
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
            unset($oObj->cArtikelName, $oObj->cArtikelURL, $oObj->oArtikel);

            $kPrim = Shop::Container()->getDB()->insert('trmaartikel', $oObj);

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
         */
        public function delete()
        {
            return Shop::Container()->getDB()->delete('trmaartikel', 'kRMA', $this->getRMA());
        }

        /**
         * @param int $kRMA
         * @return $this
         */
        public function setRMA($kRMA)
        {
            $this->kRMA = (int)$kRMA;

            return $this;
        }

        /**
         * @param int $kBestellung
         * @return $this
         */
        public function setBestellung($kBestellung)
        {
            $this->kBestellung = (int)$kBestellung;

            return $this;
        }

        /**
         * @param int $kArtikel
         * @return $this
         */
        public function setArtikel($kArtikel)
        {
            $this->kArtikel = (int)$kArtikel;

            return $this;
        }

        /**
         * @param float $fAnzahl
         * @return $this
         */
        public function setAnzahl($fAnzahl)
        {
            $this->fAnzahl = (float)$fAnzahl;

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
         * @return int
         */
        public function getRMA()
        {
            return (int)$this->kRMA;
        }

        /**
         * @return int
         */
        public function getBestellung()
        {
            return (int)$this->kBestellung;
        }

        /**
         * @return int
         */
        public function getArtikel()
        {
            return (int)$this->kArtikel;
        }

        /**
         * @return float
         */
        public function getAnzahl()
        {
            return $this->fAnzahl;
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
         * @param int   $kBestellung
         * @param array $kArtikel_arr
         * @param array $cRMAPostAssoc_arr
         * @return bool
         */
        public static function isOrderExisting($kBestellung, $kArtikel_arr, &$cRMAPostAssoc_arr)
        {
            $kBestellung = (int)$kBestellung;
            if ($kBestellung > 0
                && is_array($kArtikel_arr)
                && is_array($cRMAPostAssoc_arr)
                && count($kArtikel_arr) > 0
                && count($cRMAPostAssoc_arr) > 0
            ) {
                foreach ($kArtikel_arr as $kArtikel) {
                    $fRMAArtikelQuantity = self::getRMAQuantity($kBestellung, $kArtikel); // Bereits zurueckgeschickte Anzahl

                    if ($fRMAArtikelQuantity && $fRMAArtikelQuantity > 0) {
                        // TODO: Anzahl checken
                        $kRMAGrund  = (int)$cRMAPostAssoc_arr['cGrund'][$kArtikel];
                        $fAnzahlNow = (float)$cRMAPostAssoc_arr['fAnzahl'][$kArtikel][$kRMAGrund]; // Aktuelle Anzahl

                        $fAnzahlBestellung = Bestellung::getProductAmount($kBestellung, $kArtikel);
                        if ($fAnzahlBestellung) {
                            // Aktuelle Anzahl - Bereits zurueckgeschickte Anzahl > Bestellte Anzahl
                            return ($fAnzahlNow - $fRMAArtikelQuantity > $fAnzahlBestellung);
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
            $kBestellung  = (int)$kBestellung;
            $kArtikel_arr = [];

            if ($kBestellung > 0) {
                $oObj_arr = Shop::Container()->getDB()->query(
                    "SELECT kArtikel
                        FROM trmaartikel
                        WHERE kBestellung = " . $kBestellung,
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($oObj_arr as $oObj) {
                    $kArtikel_arr[] = (int)$oObj->kArtikel;
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
            $kBestellung = (int)$kBestellung;
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
            $kBestellung = (int)$kBestellung;
            $kArtikel    = (int)$kArtikel;
            if ($kBestellung > 0 && $kArtikel > 0) {
                $oObj = Shop::Container()->getDB()->query(
                    "SELECT SUM(fAnzahl) AS fAnzahlSum
                        FROM trmaartikel
                        WHERE kArtikel = " . $kArtikel . "
                            AND kBestellung = " . $kBestellung,
                    \DB\ReturnType::SINGLE_OBJECT
                );

                if (isset($oObj->fAnzahlSum) && $oObj->fAnzahlSum > 0) {
                    return $oObj->fAnzahlSum;
                }
            }

            return false;
        }
    }
}
