<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    /**
     * Class AuswahlAssistent
     */
    class AuswahlAssistent
    {
        /**
         * @var int
         */
        private $kAuswahlAssistentOrt = 0;

        /**
         * @var int
         */
        private $kAuswahlAssistentGruppe = 0;

        /**
         * @var string
         */
        private $cKey = '';

        /**
         * @var int
         */
        private $kKey = 0;

        /**
         * @var int
         */
        private $kSprache = 0;

        /**
         * @var string
         */
        private $cName = '';

        /**
         * @var string
         */
        private $cBeschreibung = '';

        /**
         * @var int
         */
        private $nAktiv = 0;

        /**
         * @var array
         */
        private $oFrage_arr = [];

        /**
         * @var int
         */
        private $nQuestion = 0;

        /**
         * @var array
         */
        private $nSelection_arr = [];

        /**
         * AuswahlAssistent constructor.
         *
         * @param string $cKey
         * @param int    $kKey
         * @param int    $kSprache
         * @param bool   $bOnlyActive
         */
        public function __construct($cKey, $kKey, $kSprache = 0, $bOnlyActive = true)
        {
            $kKey     = (int)$kKey;
            $kSprache = (int)$kSprache;

            if ($kSprache === 0) {
                $kSprache = Shop::getLanguage();
            }

            $oDbResult = Shop::DB()->query("
                    SELECT *
                        FROM tauswahlassistentort AS ao
                            JOIN tauswahlassistentgruppe AS ag
                                ON ao.kAuswahlAssistentGruppe = ag.kAuswahlAssistentGruppe
                                    AND ao.cKey = '" . Shop::DB()->escape($cKey) . "'
                                    AND ao.kKey = " . $kKey . "
                                    AND ag.kSprache = " . $kSprache . "
                                    " . ($bOnlyActive ? "AND ag.nAktiv = 1" : "") . "
                ", 1);

            if ($oDbResult !== null) {
                foreach (get_object_vars($oDbResult) as $name => $value) {
                    $this->$name = $value;
                }

                $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kKey                    = (int)$this->kKey;
                $this->kSprache                = (int)$this->kSprache;
                $this->nAktiv                  = (int)$this->nAktiv;

                $this->oFrage_arr = Shop::DB()->query("
                        SELECT af.kAuswahlAssistentFrage, af.kMerkmal, af.cFrage, af.nAktiv, m.cBildpfad,
                                COALESCE(m.cName, ms.cName) AS cName
                            FROM tauswahlassistentfrage AS af
                                JOIN tmerkmal AS m
                                    ON af.kMerkmal = m.kMerkmal
                                        AND af.kAuswahlAssistentGruppe = " . $this->kAuswahlAssistentGruppe . "
                                        " . ($bOnlyActive ? "AND af.nAktiv = 1" : "") . "
                                LEFT JOIN tmerkmalsprache AS ms
                                    ON m.kMerkmal = ms.kMerkmal
                                        AND ms.kSprache = " . $kSprache . "
                            ORDER BY af.nSort
                    ", 2);

                foreach ($this->oFrage_arr as &$oFrage) {
                    $oFrage->kAuswahlAssistentFrage = (int)$oFrage->kAuswahlAssistentFrage;
                    $oFrage->kMerkmal               = (int)$oFrage->kMerkmal;
                    $oFrage->nAktiv                 = (int)$oFrage->nAktiv;

//                    $oFrage->
                }
            }
        }

        /**
         * @return mixed
         */
        public static function getLinks()
        {
            return Shop::DB()->query(
                "SELECT *
                    FROM tlink
                    WHERE nLinkart = " . LINKTYP_AUSWAHLASSISTENT, 2
            );
        }

        /**
         * @param string $cKey
         * @param int    $kKey
         * @param int    $kSprache
         * @return AuswahlAssistentGruppe|bool
         */
        public static function getGroupsByLocation($cKey, $kKey, $kSprache)
        {
            if (strlen($cKey) > 0 && intval($kKey) > 0 && intval($kSprache) > 0) {
                $oOrt = Shop::DB()->query(
                    "SELECT tauswahlassistentort.kAuswahlAssistentGruppe
                        FROM tauswahlassistentort
                        JOIN tauswahlassistentgruppe ON tauswahlassistentgruppe.kAuswahlAssistentGruppe = tauswahlassistentort.kAuswahlAssistentGruppe
                            AND tauswahlassistentgruppe.kSprache = " . (int)$kSprache . "
                        WHERE tauswahlassistentort.cKey = '" . Shop::DB()->escape($cKey) . "'
                            AND tauswahlassistentort.kKey = " . (int)$kKey, 1
                );

                if (isset($oOrt->kAuswahlAssistentGruppe) && $oOrt->kAuswahlAssistentGruppe > 0) {
                    return new AuswahlAssistentGruppe($oOrt->kAuswahlAssistentGruppe);
                }
            }

            return false;
        }

        /**
         * Tells wether the product wizard is enabled
         *
         * @return bool
         */
        public static function isRequired()
        {
            return Shop::getSettings(CONF_AUSWAHLASSISTENT)['auswahlassistent']['auswahlassistent_nutzen'] === 'Y';
        }
    }
}
