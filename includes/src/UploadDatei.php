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
        public function __construct(int $kUpload = 0)
        {
            if ($kUpload > 0) {
                $this->loadFromDB($kUpload);
            }
        }

        /**
         * @param int $kUpload
         * @return bool
         */
        public function loadFromDB(int $kUpload): bool
        {
            $oUpload = Shop::Container()->getDB()->select('tuploaddatei', 'kUpload', $kUpload);
            if (isset($oUpload->kUpload) && (int)$oUpload->kUpload > 0) {
                self::copyMembers($oUpload, $this);

                return true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function save(): int
        {
            return Shop::Container()->getDB()->insert('tuploaddatei', self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function update(): int
        {
            return Shop::Container()->getDB()->update(
                'tuploaddatei',
                'kUpload',
                (int)$this->kUpload,
                self::copyMembers($this)
            );
        }

        /**
         * @return int
         */
        public function delete(): int
        {
            return Shop::Container()->getDB()->delete('tuploaddatei', 'kUpload', (int)$this->kUpload);
        }

        /**
         * @param int $kCustomID
         * @param int $nTyp
         * @return array
         */
        public static function fetchAll(int $kCustomID, int $nTyp): array
        {
            $files = Shop::Container()->getDB()->selectAll(
                'tuploaddatei',
                ['kCustomID', 'nTyp'],
                [$kCustomID, $nTyp]
            );
            foreach ($files as $upload) {
                $upload->cGroesse   = Upload::formatGroesse($upload->nBytes);
                $upload->bVorhanden = is_file(PFAD_UPLOADS . $upload->cPfad);
                $upload->bVorschau  = Upload::vorschauTyp($upload->cName);
                $upload->cBildpfad  = sprintf(
                    '%s/%s?action=preview&secret=%s&sid=%s',
                    Shop::getURL(),
                    PFAD_UPLOAD_CALLBACK,
                    rawurlencode(Shop::Container()->getCryptoService()->encryptXTEA($upload->kUpload)),
                    session_id()
                );
            }

            return $files;
        }

        /**
         * @param object      $objFrom
         * @param null|object $objTo
         * @return null|object
         */
        private static function copyMembers($objFrom, &$objTo = null)
        {
            if (!is_object($objTo)) {
                $objTo = new stdClass();
            }
            foreach (array_keys(get_object_vars($objFrom)) as $member) {
                $objTo->$member = $objFrom->$member;
            }

            return $objTo;
        }

        /**
         * @param string $filename
         * @param string $mimetype
         * @param string $downloadName
         */
        public static function send_file_to_browser(string $filename, string $mimetype, string $downloadName)
        {
            $browser_agent   = 'other';
            $HTTP_USER_AGENT = !empty($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : '';
            if (preg_match('/Opera\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'opera';
            } elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'ie';
            } elseif (preg_match('/OmniWeb\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'omniweb';
            } elseif (preg_match('/Mozilla\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'mozilla';
            } elseif (preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browser_agent = 'konqueror';
            }
            if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
                $mimetype = ($browser_agent === 'ie' || $browser_agent === 'opera')
                    ? 'application/octetstream'
                    : 'application/octet-stream';
            }

            @ob_end_clean();
            @ini_set('zlib.output_compression', 'Off');

            header('Pragma: public');
            header('Content-Transfer-Encoding: none');
            if ($browser_agent === 'ie') {
                header('Content-Type: ' . $mimetype);
                header('Content-Disposition: inline; filename="' . $downloadName . '"');
            } else {
                header('Content-Type: ' . $mimetype . '; name="' . basename($filename) . '"');
                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            }

            $size = @filesize($filename);
            if ($size) {
                header('Content-length: ' . $size);
            }

            readfile($filename);
            exit;
        }
    }
}
