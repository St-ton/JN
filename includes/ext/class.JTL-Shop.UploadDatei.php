<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UPLOADS)) {
    /**
     * Class UploadDatei
     */
    class UploadDatei
    {
        /**
         * @var int
         */
        public $kUpload;

        /**
         * @var int
         */
        public $kCustomID;

        /**
         * @var int
         */
        public $nTyp;

        /**
         * @var string
         */
        public $cName;

        /**
         * @var string
         */
        public $cPfad;

        /**
         * @var string
         */
        public $dErstellt;

        /**
         * @param int $kUpload
         */
        public function __construct($kUpload = 0)
        {
            if (intval($kUpload) > 0) {
                $this->loadFromDB($kUpload);
            }
        }

        /**
         * @param int $kUpload
         * @return bool
         */
        public function loadFromDB($kUpload)
        {
            $oUpload = Shop::DB()->query(
                "SELECT * FROM tuploaddatei
                  WHERE kUpload = " . intval($kUpload), 1
            );

            if (isset($oUpload->kUpload) && intval($oUpload->kUpload) > 0) {
                self::copyMembers($oUpload, $this);

                return true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function save()
        {
            return Shop::DB()->insert('tuploaddatei', self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function update()
        {
            return Shop::DB()->update('tuploaddatei', 'kUpload', intval($this->kUpload), self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function delete()
        {
            return Shop::DB()->delete('tuploaddatei', 'kUpload', (int) $this->kUpload);
        }

        /**
         * @param int $kCustomID
         * @param int $nTyp
         * @return mixed
         */
        public static function fetchAll($kCustomID, $nTyp)
        {
            $oUploadDatei_arr = Shop::DB()->query(
                "SELECT * FROM tuploaddatei
                   WHERE kCustomID = '" . intval($kCustomID) . "'
                   AND nTyp = '" . intval($nTyp) . "'", 2
            );

            if (is_array($oUploadDatei_arr)) {
                foreach ($oUploadDatei_arr as &$oUpload) {
                    $oUpload->cGroesse   = Upload::formatGroesse($oUpload->nBytes);
                    $oUpload->bVorhanden = is_file(PFAD_UPLOADS . $oUpload->cPfad);
                    $oUpload->bVorschau  = Upload::vorschauTyp($oUpload->cName);
                    $oUpload->cBildpfad  = sprintf('%s/%s?action=preview&secret=%s&sid=%s', Shop::getURL(), PFAD_UPLOAD_CALLBACK, rawurlencode(verschluesselXTEA($oUpload->kUpload)), session_id());
                }
            }

            return $oUploadDatei_arr;
        }

        /**
         * @param object $objFrom
         * @param null   $objTo
         * @return null|stdClass
         */
        private static function copyMembers($objFrom, &$objTo = null)
        {
            if (!is_object($objTo)) {
                $objTo = new stdClass();
            }
            $cMember_arr = array_keys(get_object_vars($objFrom));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $objTo->$cMember = $objFrom->$cMember;
                }
            }

            return $objTo;
        }
    }
}
