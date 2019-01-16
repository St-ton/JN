<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_DOWNLOADS)) {
    /**
     * Class Download
     */
    class Download
    {
        public const ERROR_NONE = 1;

        public const ERROR_ORDER_NOT_FOUND = 2;

        public const ERROR_INVALID_CUSTOMER = 3;

        public const ERROR_PRODUCT_NOT_FOUND = 4;

        public const ERROR_DOWNLOAD_LIMIT_REACHED = 5;

        public const ERROR_DOWNLOAD_EXPIRED = 6;

        public const ERROR_MISSING_PARAMS = 7;

        /**
         * @var int
         */
        public $kDownload;

        /**
         * @var string
         */
        public $cID;

        /**
         * @var string
         */
        public $cPfad;

        /**
         * @var string
         */
        public $cPfadVorschau;

        /**
         * @var int
         */
        public $nAnzahl;

        /**
         * @var int
         */
        public $nTage;

        /**
         * @var int
         */
        public $nSort;

        /**
         * @var string
         */
        public $dErstellt;

        /**
         * @var object
         */
        public $oDownloadSprache;

        /**
         * @var array
         */
        public $oDownloadHistory_arr;

        /**
         * @var int
         */
        public $kBestellung;

        /**
         * @var string
         */
        public $dGueltigBis;

        /**
         * @var array
         */
        public $oArtikelDownload_arr;

        /**
         * @param int  $kDownload
         * @param int  $kSprache
         * @param bool $bInfo
         * @param int  $kBestellung
         */
        public function __construct(int $kDownload = 0, int $kSprache = 0, bool $bInfo = true, int $kBestellung = 0)
        {
            if ($kDownload > 0) {
                $this->loadFromDB($kDownload, $kSprache, $bInfo, $kBestellung);
            }
        }

        /**
         * @param int  $kDownload
         * @param int  $kSprache
         * @param bool $bInfo
         * @param int  $kBestellung
         */
        private function loadFromDB(int $kDownload, int $kSprache, bool $bInfo, int $kBestellung)
        {
            $oDownload = Shop::Container()->getDB()->select('tdownload', 'kDownload', $kDownload);
            if ($oDownload !== null && isset($oDownload->kDownload) && (int)$oDownload->kDownload > 0) {
                $members = array_keys(get_object_vars($oDownload));
                if (is_array($members) && count($members) > 0) {
                    foreach ($members as &$member) {
                        $this->$member = $oDownload->$member;
                    }
                    unset($member);
                    $this->kBestellung = (int)$this->kBestellung;
                    $this->nAnzahl     = (int)$this->nAnzahl;
                    $this->nTage       = (int)$this->nTage;
                    $this->nSort       = (int)$this->nSort;
                }
                if ($bInfo) {
                    if (!$kSprache) {
                        $kSprache = Shop::getLanguageID();
                    }
                    $this->oDownloadSprache = new DownloadSprache($oDownload->kDownload, $kSprache);
                    // History
                    $this->oDownloadHistory_arr = DownloadHistory::getHistory($oDownload->kDownload);
                }

                if ($kBestellung > 0) {
                    $this->kBestellung = $kBestellung;
                    $oBestellung       = Shop::Container()->getDB()->select(
                        'tbestellung',
                        'kBestellung',
                        $kBestellung,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'kBestellung, dBezahltDatum'
                    );
                    if ($oBestellung !== null
                        && $oBestellung->kBestellung > 0
                        && $oBestellung->dBezahltDatum !== null
                        && $this->getTage() > 0
                    ) {
                        $paymentDate = new DateTime($oBestellung->dBezahltDatum);
                        $modifyBy    = $this->getTage() + 1;
                        $paymentDate->modify('+' . $modifyBy . ' day');
                        $this->dGueltigBis = $paymentDate->format('d.m.Y');
                    }
                }

                // Artikel
                $this->oArtikelDownload_arr = Shop::Container()->getDB()->queryPrepared(
                    'SELECT tartikeldownload.*
                        FROM tartikeldownload
                        JOIN tdownload 
                            ON tdownload.kDownload = tartikeldownload.kDownload
                        WHERE tartikeldownload.kDownload = :dlid
                        ORDER BY tdownload.nSort',
                    ['dlid' => $this->kDownload],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            }
        }

        /**
         * @param bool $bPrimary
         * @return bool|int
         */
        public function save(bool $bPrimary = false)
        {
            $ins = $this->kopiereMembers();
            unset(
                $ins->kDownload,
                $ins->oDownloadSprache,
                $ins->oDownloadHistory_arr,
                $ins->oArtikelDownload_arr,
                $ins->cLimit,
                $ins->dGueltigBis,
                $ins->kBestellung
            );
            $kDownload = Shop::Container()->getDB()->insert('tdownload', $ins);
            if ($kDownload > 0) {
                return $bPrimary ? $kDownload : true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function update(): int
        {
            $upd                = new stdClass();
            $upd->cID           = $this->cID;
            $upd->cPfad         = $this->cPfad;
            $upd->cPfadVorschau = $this->cPfadVorschau;
            $upd->nAnzahl       = $this->nAnzahl;
            $upd->nTage         = $this->nTage;
            $upd->dErstellt     = $this->dErstellt;

            return Shop::Container()->getDB()->update('tdownload', 'kDownload', (int)$this->kDownload, $upd);
        }

        /**
         * @return int
         */
        public function delete(): int
        {
            return Shop::Container()->getDB()->queryPrepared(
                'DELETE tdownload, tdownloadhistory, tdownloadsprache, tartikeldownload
                    FROM tdownload
                    JOIN tdownloadsprache 
                        ON tdownloadsprache.kDownload = tdownload.kDownload
                    LEFT JOIN tartikeldownload 
                        ON tartikeldownload.kDownload = tdownload.kDownload
                    LEFT JOIN tdownloadhistory 
                        ON tdownloadhistory.kDownload = tdownload.kDownload
                    WHERE tdownload.kDownload = :dlid',
                ['dlid' => $this->kDownload],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        /**
         * @param array $kKey_arr
         * @param int   $kSprache
         * @return array
         */
        public static function getDownloads($kKey_arr = [], int $kSprache = 0): array
        {
            $kArtikel    = isset($kKey_arr['kArtikel']) ? (int)$kKey_arr['kArtikel'] : 0;
            $kBestellung = isset($kKey_arr['kBestellung']) ? (int)$kKey_arr['kBestellung'] : 0;
            $kKunde      = isset($kKey_arr['kKunde']) ? (int)$kKey_arr['kKunde'] : 0;
            $downloads   = [];
            if (($kArtikel > 0 || $kBestellung > 0 || $kKunde > 0) && $kSprache > 0) {
                $cSQLSelect = 'tartikeldownload.kDownload';
                $cSQLWhere  = 'kArtikel = ' . $kArtikel;
                $cSQLJoin   = 'LEFT JOIN tdownload ON tartikeldownload.kDownload = tdownload.kDownload';
                if ($kBestellung > 0) {
                    $cSQLSelect = 'tbestellung.kBestellung, tbestellung.kKunde, tartikeldownload.kDownload';
                    $cSQLWhere  = 'tartikeldownload.kArtikel = twarenkorbpos.kArtikel';
                    $cSQLJoin   = 'JOIN tbestellung ON tbestellung.kBestellung = ' . $kBestellung . '
                                   JOIN tdownload ON tdownload.kDownload = tartikeldownload.kDownload
                                   JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                                        AND twarenkorbpos.nPosTyp = ' . C_WARENKORBPOS_TYP_ARTIKEL;
                } elseif ($kKunde > 0) {
                    $cSQLSelect = 'MAX(tbestellung.kBestellung) AS kBestellung, tbestellung.kKunde, 
                        tartikeldownload.kDownload';
                    $cSQLWhere  = 'tartikeldownload.kArtikel = twarenkorbpos.kArtikel';
                    $cSQLJoin   = 'JOIN tbestellung ON tbestellung.kKunde = ' . $kKunde . '
                                   JOIN tdownload ON tdownload.kDownload = tartikeldownload.kDownload
                                   JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                                        AND twarenkorbpos.nPosTyp = ' . C_WARENKORBPOS_TYP_ARTIKEL;
                }
                $items = Shop::Container()->getDB()->query(
                    'SELECT ' . $cSQLSelect . '
                        FROM tartikeldownload
                        ' . $cSQLJoin . '
                        WHERE ' . $cSQLWhere . '
                        GROUP BY tartikeldownload.kDownload
                        ORDER BY tdownload.nSort, tdownload.dErstellt DESC',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($items as $i => &$download) {
                    $download->kDownload  = (int)$download->kDownload;
                    $downloads[$i] = new self(
                        $download->kDownload,
                        $kSprache,
                        true,
                        (int)($download->kBestellung ?? 0)
                    );
                    if (($kBestellung > 0 || $kKunde > 0) && $downloads[$i]->getAnzahl() > 0) {
                        $download->kKunde      = (int)$download->kKunde;
                        $download->kBestellung = (int)$download->kBestellung;

                        $history                        = DownloadHistory::getOrderHistory(
                            $download->kKunde,
                            $download->kBestellung
                        );
                        $kDownload                      = $downloads[$i]->getDownload();
                        $count                          = isset($history[$kDownload])
                            ? count($history[$kDownload])
                            : 0;
                        $downloads[$i]->cLimit      = $count . ' / ' . $downloads[$i]->getAnzahl();
                        $downloads[$i]->kBestellung = $download->kBestellung;
                    }
                }
            }

            return $downloads;
        }

        /**
         * @param Warenkorb $cart
         * @return bool
         */
        public static function hasDownloads($cart): bool
        {
            foreach ($cart->PositionenArr as &$oPosition) {
                if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                    && isset($oPosition->Artikel->oDownload_arr)
                    && count($oPosition->Artikel->oDownload_arr) > 0
                ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param int $kDownload
         * @param int $kKunde
         * @param int $kBestellung
         * @return int
         */
        public static function getFile(int $kDownload, int $kKunde, int $kBestellung): int
        {
            if ($kDownload > 0 && $kKunde > 0 && $kBestellung > 0) {
                $oDownload = new self($kDownload, 0, false);
                $nReturn   = $oDownload::checkFile($oDownload->kDownload, $kKunde, $kBestellung);
                if ($nReturn === 1) {
                    (new DownloadHistory())
                        ->setDownload($kDownload)
                        ->setKunde($kKunde)
                        ->setBestellung($kBestellung)
                        ->setErstellt('NOW()')
                        ->save();

                    self::send_file_to_browser(
                        PFAD_DOWNLOADS . $oDownload->getPfad(),
                        'application/octet-stream'
                    );

                    return 1;
                }

                return $nReturn;
            }

            return 7;
        }

        /**
         * Fehlercodes:
         * 1 = Alles O.K.
         * 2 = Bestellung nicht gefunden
         * 3 = Kunde stimmt nicht
         * 4 = Kein Artikel mit Downloads gefunden
         * 5 = Maximales Downloadlimit wurde erreicht
         * 6 = Maximales Datum wurde erreicht
         * 7 = Paramter fehlen
         *
         * @param int $kDownload
         * @param int $kKunde
         * @param int $kBestellung
         * @return int
         */
        public static function checkFile(int $kDownload, int $kKunde, int $kBestellung): int
        {
            if ($kDownload > 0 && $kKunde > 0 && $kBestellung > 0) {
                $order = new Bestellung($kBestellung);
                // Existiert die Bestellung und wurde Sie bezahlt?
                if ($order->kBestellung <= 0 || empty($order->dBezahltDatum) || $order->dBezahltDatum === null) {
                    return self::ERROR_ORDER_NOT_FOUND;
                }
                // Stimmt der Kunde?
                if ((int)$order->kKunde !== $kKunde) {
                    return self::ERROR_INVALID_CUSTOMER;
                }
                $order->fuelleBestellung();
                $download = new self($kDownload, 0, false);
                // Gibt es einen Artikel der zum Download passt?
                if (!is_array($download->oArtikelDownload_arr) || count($download->oArtikelDownload_arr) === 0) {
                    return self::ERROR_PRODUCT_NOT_FOUND;
                }
                foreach ($order->Positionen as &$position) {
                    foreach ($download->oArtikelDownload_arr as &$oArtikelDownload) {
                        if ($position->kArtikel != $oArtikelDownload->kArtikel) {
                            continue;
                        }
                        // Check Anzahl
                        if ($download->getAnzahl() > 0) {
                            $history = DownloadHistory::getOrderHistory($kKunde, $kBestellung);
                            if (count($history[$download->kDownload]) >= $download->getAnzahl()) {
                                return self::ERROR_DOWNLOAD_LIMIT_REACHED;
                            }
                        }
                        // Check Datum
                        $paymentDate = new DateTime($order->dBezahltDatum);
                        $paymentDate->modify('+' . ($download->getTage() + 1) . ' day');
                        if ($download->getTage() > 0 && $paymentDate < new DateTime()) {
                            return self::ERROR_DOWNLOAD_EXPIRED;
                        }

                        return self::ERROR_NONE;
                    }
                }
            }

            return self::ERROR_MISSING_PARAMS;
        }

        /**
         * Fehlercodes:
         * 2 = Bestellung nicht gefunden
         * 3 = Kunde stimmt nicht
         * 4 = Kein Artikel mit Downloads gefunden
         * 5 = Maximales Downloadlimit wurde erreicht
         * 6 = Maximales Datum wurde erreicht
         * 7 = Paramter fehlen
         *
         * @param int $errorCode
         * @return string
         */
        public static function mapGetFileErrorCode(int $errorCode): string
        {
            switch ($errorCode) {
                case self::ERROR_ORDER_NOT_FOUND: // Bestellung nicht gefunden
                    $error = Shop::Lang()->get('dlErrorOrderNotFound');
                    break;
                case self::ERROR_INVALID_CUSTOMER: // Kunde stimmt nicht
                    $error = Shop::Lang()->get('dlErrorCustomerNotMatch');
                    break;
                case self::ERROR_PRODUCT_NOT_FOUND: // Kein Artikel mit Downloads gefunden
                    $error = Shop::Lang()->get('dlErrorDownloadNotFound');
                    break;
                case self::ERROR_DOWNLOAD_LIMIT_REACHED: // Maximales Downloadlimit wurde erreicht
                    $error = Shop::Lang()->get('dlErrorDownloadLimitReached');
                    break;
                case self::ERROR_DOWNLOAD_EXPIRED: // Maximales Datum wurde erreicht
                    $error = Shop::Lang()->get('dlErrorValidityReached');
                    break;
                case self::ERROR_MISSING_PARAMS: // Paramter fehlen
                    $error = Shop::Lang()->get('dlErrorWrongParameter');
                    break;
                default:
                    $error = '';
                    break;
            }

            return $error;
        }

        /**
         * @param int $kDownload
         * @return $this
         */
        public function setDownload(int $kDownload): self
        {
            $this->kDownload = $kDownload;

            return $this;
        }

        /**
         * @param string $cID
         * @return $this
         */
        public function setID($cID): self
        {
            $this->cID = $cID;

            return $this;
        }

        /**
         * @param string $cPfad
         * @return $this
         */
        public function setPfad($cPfad): self
        {
            $this->cPfad = $cPfad;

            return $this;
        }

        /**
         * @param string $cPfadVorschau
         * @return $this
         */
        public function setPfadVorschau($cPfadVorschau): self
        {
            $this->cPfadVorschau = $cPfadVorschau;

            return $this;
        }

        /**
         * @param int $nAnzahl
         * @return $this
         */
        public function setAnzahl(int $nAnzahl): self
        {
            $this->nAnzahl = $nAnzahl;

            return $this;
        }

        /**
         * @param int $nTage
         * @return $this
         */
        public function setTage(int $nTage): self
        {
            $this->nTage = $nTage;

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
         * @param string $dErstellt
         * @return $this
         */
        public function setErstellt($dErstellt): self
        {
            $this->dErstellt = $dErstellt;

            return $this;
        }

        /**
         * @return int|null
         */
        public function getDownload()
        {
            return $this->kDownload;
        }

        /**
         * @return string|null
         */
        public function getID()
        {
            return $this->cID;
        }

        /**
         * @return string|null
         */
        public function getPfad()
        {
            return $this->cPfad;
        }

        /**
         * @return bool
         */
        public function hasPreview(): bool
        {
            return strlen($this->cPfadVorschau) > 0;
        }

        /**
         * @return string
         */
        public function getExtension(): string
        {
            if (strlen($this->cPfad) > 0) {
                $pathInfo = pathinfo($this->cPfad);
                if (is_array($pathInfo)) {
                    return strtoupper($pathInfo['extension']);
                }
            }

            return '';
        }

        /**
         * @return string
         */
        public function getPreviewExtension(): string
        {
            if (strlen($this->cPfadVorschau) > 0) {
                $pathInfo = pathinfo($this->cPfadVorschau);
                if (is_array($pathInfo)) {
                    return strtoupper($pathInfo['extension']);
                }
            }

            return '';
        }

        /**
         * @return string
         */
        public function getPreviewType(): string
        {
            switch (strtolower($this->getPreviewExtension())) {
                case 'mpeg':
                case 'mpg':
                case 'avi':
                case 'wmv':
                case 'mp4':
                    return 'video';

                case 'wav':
                case 'mp3':
                case 'wma':
                    return 'music';

                case 'gif':
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'jpe':
                case 'bmp':
                    return 'image';
                default:
                    break;
            }

            return 'misc';
        }

        /**
         * @return string
         */
        public function getPreview(): string
        {
            return Shop::getURL() . '/' . PFAD_DOWNLOADS_PREVIEW_REL . $this->cPfadVorschau;
        }

        /**
         * @return int|null
         */
        public function getAnzahl()
        {
            return $this->nAnzahl;
        }

        /**
         * @return int|null
         */
        public function getTage()
        {
            return $this->nTage;
        }

        /**
         * @return int|null
         */
        public function getSort()
        {
            return $this->nSort;
        }

        /**
         * @return string|null
         */
        public function getErstellt()
        {
            return $this->dErstellt;
        }

        /**
         * @return mixed
         */
        private function kopiereMembers()
        {
            $obj     = new stdClass();
            $members = array_keys(get_object_vars($this));
            if (is_array($members) && count($members) > 0) {
                foreach ($members as &$member) {
                    $obj->$member = $this->$member;
                }
            }

            return $obj;
        }

        /**
         * @param string $filename
         * @param string $mimetype
         */
        private static function send_file_to_browser(string $filename, string $mimetype)
        {
            $browserAgent   = 'other';
            $HTTP_USER_AGENT = !empty($_SERVER['HTTP_USER_AGENT'])
                ? $_SERVER['HTTP_USER_AGENT']
                : '';
            if (preg_match('/Opera\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browserAgent = 'opera';
            } elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browserAgent = 'ie';
            } elseif (preg_match('/OmniWeb\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browserAgent = 'omniweb';
            } elseif (preg_match('/Mozilla\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browserAgent = 'mozilla';
            } elseif (preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
                $browserAgent = 'konqueror';
            }
            if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
                $mimetype = ($browserAgent === 'ie' || $browserAgent === 'opera')
                    ? 'application/octetstream'
                    : 'application/octet-stream';
            }

            @ob_end_clean();
            @ini_set('zlib.output_compression', 'Off');

            header('Pragma: public');
            header('Content-Transfer-Encoding: none');
            if ($browserAgent === 'ie') {
                header('Content-Type: ' . $mimetype);
                header('Content-Disposition: inline; filename="' . basename($filename) . '"');
            } else {
                header('Content-Type: ' . $mimetype . '; name="' . basename($filename) . '"');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            }

            $size = @filesize($filename);
            if ($size) {
                header("Content-length: $size");
            }

            readfile($filename);
            exit;
        }
    }
}
