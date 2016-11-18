<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';

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
         * @var array - mapping from kMerkmal to AuswahlAssistentFrage
         */
        private $oFrage_assoc = [];

        /**
         * @var int
         */
        private $nCurQuestion = 0;

        /**
         * @var array
         */
        private $kSelection_arr = [];

        /**
         * @var stdClass
         */
        private $oNaviFilter = null;

        /**
         * @var array
         */
        private $config = [];

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
            $kKey         = (int)$kKey;
            $kSprache     = (int)$kSprache;
            $this->config = Shop::getSettings(CONF_AUSWAHLASSISTENT)['auswahlassistent'];

            if ($kSprache === 0) {
                $kSprache = Shop::getLanguage();
            }

            if (!empty($cKey) && $kKey > 0 && $kSprache > 0) {
                $this->loadFromDB($cKey, $kKey, $kSprache, $bOnlyActive);
            }
        }

        /**
         * @param $cKey
         * @param $kKey
         * @param int $kSprache
         * @param bool $bOnlyActive
         */
        private function loadFromDB($cKey, $kKey, $kSprache, $bOnlyActive = true)
        {
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

            if ($oDbResult !== null && $oDbResult !== false) {
                foreach (get_object_vars($oDbResult) as $name => $value) {
                    $this->$name = $value;
                }

                $this->kAuswahlAssistentOrt    = (int)$this->kAuswahlAssistentOrt;
                $this->kAuswahlAssistentGruppe = (int)$this->kAuswahlAssistentGruppe;
                $this->kKey                    = (int)$this->kKey;
                $this->kSprache                = (int)$this->kSprache;
                $this->nAktiv                  = (int)$this->nAktiv;

                $oAuswahlAssistentFrageDB_arr = Shop::DB()->query("
                        SELECT kAuswahlAssistentFrage
                            FROM tauswahlassistentfrage
                            WHERE kAuswahlAssistentGruppe = " . $this->kAuswahlAssistentGruppe . "
                                " . ($bOnlyActive ? "AND nAktiv = 1" : "") . "
                            ORDER BY nSort
                    ", 2);

                $this->oFrage_arr = [];

                foreach ($oAuswahlAssistentFrageDB_arr as $oFrageDB) {
                    $oFrage                                = new AuswahlAssistentFrage($oFrageDB->kAuswahlAssistentFrage);
                    $this->oFrage_arr[]                    = $oFrage;
                    $this->oFrage_assoc[$oFrage->kMerkmal] = $oFrage;
                }
            }
        }

        /**
         * @param $kMerkmalWert
         * @return $this
         */
        public function setNextSelection($kWert)
        {
            if($this->nCurQuestion < count($this->oFrage_arr)) {
                $this->kSelection_arr[] = $kWert;
                $this->nCurQuestion    += 1;
            }

            return $this;
        }

        /**
         * @return $this
         */
        public function filter()
        {
            $cParameter_arr = [];

            if ($this->cKey === AUSWAHLASSISTENT_ORT_KATEGORIE) {
                $cParameter_arr['kKategorie'] = $this->kKey;

                if (count($this->kSelection_arr) > 0) {
                    $cParameter_arr['MerkmalFilter_arr'] = $this->kSelection_arr;
                }
            } else {
                if (count($this->kSelection_arr) > 0) {
                    $cParameter_arr['kMerkmalWert'] = $this->kSelection_arr[0];

                    if (count($this->kSelection_arr) > 1) {
                        $cParameter_arr['MerkmalFilter_arr'] = array_slice($this->kSelection_arr, 1);
                    }
                }
            }

            $NaviFilter                     = Shop::buildNaviFilter($cParameter_arr);
            $FilterSQL                      = new stdClass();
            $FilterSQL->oMerkmalFilterSQL   = gibMerkmalFilterSQL($NaviFilter);
            $FilterSQL->oKategorieFilterSQL = gibKategorieFilterSQL($NaviFilter);
            $oMerkmalFilter_arr             = gibMerkmalFilterOptionen($FilterSQL, $NaviFilter, null, true);

            foreach ($oMerkmalFilter_arr as &$oMerkmalFilter) {
                $oFrage                    = $this->oFrage_assoc[(int)$oMerkmalFilter->kMerkmal];
                $oFrage->oWert_arr         = $oMerkmalFilter->oMerkmalWerte_arr;
                $oFrage->nTotalResultCount = 0;

                // Used by old AWA
                $oFrage->oMerkmalWert_arr = $oFrage->oWert_arr;

                foreach($oMerkmalFilter->oMerkmalWerte_arr as &$oWert) {
                    $oWert->kMerkmalWert                       = (int)$oWert->kMerkmalWert;
                    $oWert->nAnzahl                            = (int)$oWert->nAnzahl;
                    $oFrage->nTotalResultCount                += $oWert->nAnzahl;
                    $oFrage->oWert_assoc[$oWert->kMerkmalWert] = $oWert;
                }
            }

            $this->oNaviFilter = $NaviFilter;

            return $this;
        }

        /**
         * @param Smarty $smarty
         * @return $this
         */
        public function assignToSmarty($smarty)
        {
            $smarty->assign('AWA', $this);

            return $this;
        }

        /**
         * Return the HTML for this selection wizard in its current state
         *
         * @param Smarty $smarty
         * @return string
         */
        public function fetchForm($smarty)
        {
            $this->assignToSmarty($smarty);

            return $smarty->fetch('selectionwizard/form.tpl');
        }

        /**
         * @return int
         */
        public function getLocationId()
        {
            return $this->kAuswahlAssistentOrt;
        }

        /**
         * @return int
         */
        public function getGroupId()
        {
            return $this->kAuswahlAssistentGruppe;
        }

        /**
         * @return string
         */
        public function getLocationKeyName()
        {
            return $this->cKey;
        }

        /**
         * @return int
         */
        public function getLocationKeyId()
        {
            return $this->kKey;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->cName;
        }

        /**
         * @return string
         */
        public function getDescription()
        {
            return $this->cBeschreibung;
        }

        /**
         * @return int
         */
        public function isActive()
        {
            return $this->nAktiv === 1;
        }

        /**
         * @return AuswahlAssistentFrage
         */
        public function getQuestion($nFrage)
        {
            return $this->oFrage_arr[$nFrage];
        }

        /**
         * @return array
         */
        public function getQuestions()
        {
            return $this->oFrage_arr;
        }

        /**
         * @return int
         */
        public function getQuestionCount()
        {
            return count($this->oFrage_arr);
        }

        /**
         * @return int
         */
        public function getCurQuestion()
        {
            return $this->nCurQuestion;
        }

        /**
         * @return array
         */
        public function getSelections()
        {
            return $this->kSelection_arr;
        }

        /**
         * @param $nFrage
         * @return mixed
         */
        public function getSelectedValue($nFrage)
        {
            $oFrage         = $this->oFrage_arr[$nFrage];
            $kSelectedValue = $this->kSelection_arr[$nFrage];

            return $oFrage->oWert_assoc[$kSelectedValue];
        }

        /**
         * @return stdClass
         */
        public function getNaviFilter()
        {
            return $this->oNaviFilter;
        }

        /**
         * @return stdClass
         */
        public function getLastSelectedValue()
        {
            $oFrage         = end($this->oFrage_arr);
            $kSelectedValue = end($this->kSelection_arr);

            return $oFrage->oWert_assoc[$kSelectedValue];
        }

        /**
         * @param $cName
         * @return mixed
         */
        public function getConf($cName)
        {
            return $this->config[$cName];
        }

        /**
         * Tells whether the product wizard is enabled in the shop settings
         *
         * @return bool
         */
        public static function isRequired()
        {
            return Shop::getSettings(CONF_AUSWAHLASSISTENT)['auswahlassistent']['auswahlassistent_nutzen'] === 'Y';
        }

        /**
         * @param $cKey
         * @param $kKey
         * @param int $kSprache
         * @param int $kKategorie
         * @return self|null
         */
        public static function startIfRequired($cKey, $kKey, $kSprache = 0, $smarty = null, $nSelection_arr = [])
        {
            // only start if enabled in the backend settings
            if (self::isRequired()) {
                $nAnzahlFilter = isset($GLOBALS['NaviFilter']) ? (int)$GLOBALS['NaviFilter']->nAnzahlFilter : 0;

                // only start if no filters are already set
                if ($nAnzahlFilter === 0) {
                    $AWA = new self($cKey, $kKey, $kSprache, true);

                    // only start if the respective selection wizard group is enabled (active)
                    if ($AWA->isActive()) {
                        foreach ($nSelection_arr as $kMerkmalWert) {
                            $AWA->setNextSelection($kMerkmalWert);
                        }

                        $AWA->filter();

                        if ($smarty !== null) {
                            $AWA->assignToSmarty($smarty);
                        }

                        return $AWA;
                    }
                }
            }

            return null;
        }

        /**
         * Used by old AWA
         *
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
         * Used by old AWA
         *
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
    }
}
