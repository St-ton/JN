<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
if (class_exists('AuswahlAssistent')) {
    /**
     * Class AuswahlAssistentFrage
     */
    class AuswahlAssistentFrage
    {
        /**
         * @var int
         */
        public $kAuswahlAssistentFrage;

        /**
         * @var int
         */
        public $kAuswahlAssistentGruppe;

        /**
         * @var int
         */
        public $kMerkmal;

        /**
         * @var string
         */
        public $cFrage;

        /**
         * @var int
         */
        public $nSort;

        /**
         * @var int
         */
        public $nAktiv;

        /**
         * @var object
         */
        public $oMerkmal;

        /**
         * @param int  $kAuswahlAssistentFrage
         * @param bool $bAktiv
         */
        public function __construct($kAuswahlAssistentFrage = 0, $bAktiv = true)
        {
            if (intval($kAuswahlAssistentFrage) > 0) {
                $this->loadFromDB($kAuswahlAssistentFrage, $bAktiv);
            }
        }

        /**
         * @param int  $kAuswahlAssistentFrage
         * @param bool $bAktiv
         */
        private function loadFromDB($kAuswahlAssistentFrage, $bAktiv)
        {
            $kAuswahlAssistentFrage = (int)$kAuswahlAssistentFrage;
            if ($kAuswahlAssistentFrage > 0) {
                $cAktivSQL = '';
                if ($bAktiv) {
                    $cAktivSQL = " AND nAktiv = 1";
                }
                $oFrage = Shop::DB()->query(
                    "SELECT *
                        FROM tauswahlassistentfrage
                        WHERE kAuswahlAssistentFrage = " . $kAuswahlAssistentFrage . $cAktivSQL, 1
                );

                if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) {
                    $cMember_arr = array_keys(get_object_vars($oFrage));
                    if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                        foreach ($cMember_arr as $cMember) {
                            $this->$cMember = $oFrage->$cMember;
                        }
                    }
                    $this->oMerkmal = self::getMerkmal($this->kMerkmal, true);
                }
            }
        }

        /**
         * @param int  $kAuswahlAssistentGruppe
         * @param bool $bAktiv
         * @return array
         */
        public static function getQuestions($kAuswahlAssistentGruppe, $bAktiv = true)
        {
            $oAuswahlAssistentFrage_arr = array();
            if (intval($kAuswahlAssistentGruppe) > 0) {
                $cAktivSQL = '';
                if ($bAktiv) {
                    $cAktivSQL = " AND nAktiv = 1";
                }
                $oFrage_arr = Shop::DB()->query(
                    "SELECT *
                        FROM tauswahlassistentfrage
                        WHERE kAuswahlAssistentGruppe = " . (int)$kAuswahlAssistentGruppe . $cAktivSQL . "
                        ORDER BY nSort", 2
                );
                if (count($oFrage_arr) > 0) {
                    foreach ($oFrage_arr as $oFrage) {
                        $oAuswahlAssistentFrage_arr[] = new self($oFrage->kAuswahlAssistentFrage, $bAktiv);
                    }
                }
            }

            return $oAuswahlAssistentFrage_arr;
        }

        /**
         * @param bool $bPrimary
         * @return array|bool
         */
        public function saveQuestion($bPrimary = false)
        {
            $cPlausi_arr = $this->checkQuestion();
            if (count($cPlausi_arr) === 0) {
                $oObj = kopiereMembers($this);
                unset($oObj->oMerkmal);
                $kAuswahlAssistentFrage = Shop::DB()->insert('tauswahlassistentfrage', $oObj);
                if ($kAuswahlAssistentFrage > 0) {
                    return $bPrimary ? $kAuswahlAssistentFrage : true;
                }

                return false;
            }

            return $cPlausi_arr;
        }

        /**
         * @return array|bool
         */
        public function updateQuestion()
        {
            $cPlausi_arr = $this->checkQuestion(true);
            if (count($cPlausi_arr) == 0) {
                $_upd                          = new stdClass();
                $_upd->kAuswahlAssistentGruppe = $this->kAuswahlAssistentGruppe;
                $_upd->kMerkmal                = $this->kMerkmal;
                $_upd->cFrage                  = $this->cFrage;
                $_upd->nSort                   = $this->nSort;
                $_upd->nAktiv                  = $this->nAktiv;

                Shop::DB()->update('tauswahlassistentfrage', 'kAuswahlAssistentFrage', (int)$this->kAuswahlAssistentFrage, $_upd);

                return true;
            }

            return $cPlausi_arr;
        }

        /**
         * @param $cParam_arr
         * @return bool
         */
        public static function deleteQuestion($cParam_arr)
        {
            if (isset($cParam_arr['kAuswahlAssistentFrage_arr']) && is_array($cParam_arr['kAuswahlAssistentFrage_arr']) && count($cParam_arr['kAuswahlAssistentFrage_arr']) > 0) {
                foreach ($cParam_arr['kAuswahlAssistentFrage_arr'] as $kAuswahlAssistentFrage) {
                    Shop::DB()->query(
                        "DELETE FROM tauswahlassistentfrage
                            WHERE tauswahlassistentfrage.kAuswahlAssistentFrage = " . (int)$kAuswahlAssistentFrage, 3
                    );
                }

                return true;
            }

            return false;
        }

        /**
         * @param bool $bUpdate
         * @return array
         */
        public function checkQuestion($bUpdate = false)
        {
            $cPlausi_arr = array();
            // Frage
            if (strlen($this->cFrage) === 0) {
                $cPlausi_arr['cFrage'] = 1;
            }
            // Gruppe
            if ($this->kAuswahlAssistentGruppe == 0 || $this->kAuswahlAssistentGruppe == -1) {
                $cPlausi_arr['kAuswahlAssistentGruppe'] = 1;
            }
            // Merkmal
            if ($this->kMerkmal == 0 || $this->kMerkmal == -1) {
                $cPlausi_arr['kMerkmal'] = 1;
            }
            if (!$bUpdate) {
                if ($this->isMerkmalTaken($this->kMerkmal, $this->kAuswahlAssistentGruppe)) {
                    $cPlausi_arr['kMerkmal'] = 2;
                }
            }
            // Sortierung
            if ($this->nSort <= 0) {
                $cPlausi_arr['nSort'] = 1;
            }
            // Aktiv
            if ($this->nAktiv != 0 && $this->nAktiv != 1) {
                $cPlausi_arr['nAktiv'] = 1;
            }

            return $cPlausi_arr;
        }

        /**
         * @param int $kMerkmal
         * @param int $kAuswahlAssistentGruppe
         * @return bool
         */
        private function isMerkmalTaken($kMerkmal, $kAuswahlAssistentGruppe)
        {
            if ($kMerkmal > 0 && $kAuswahlAssistentGruppe > 0) {
                $oFrage = Shop::DB()->query(
                    "SELECT kAuswahlAssistentFrage
                        FROM tauswahlassistentfrage
                        WHERE kMerkmal = " . (int)$kMerkmal . "
                            AND kAuswahlAssistentGruppe = " . (int)$kAuswahlAssistentGruppe, 1
                );
                if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param int  $kMerkmal
         * @param bool $bMMW
         * @return Merkmal|stdClass
         */
        public static function getMerkmal($kMerkmal, $bMMW = false)
        {
            $kMerkmal = (int)$kMerkmal;
            if ($kMerkmal > 0) {
                return new Merkmal($kMerkmal, $bMMW);
            }

            return new stdClass();
        }
    }
}
