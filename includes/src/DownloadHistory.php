<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_DOWNLOADS)) {
    /**
     * Class DownloadHistory
     */
    class DownloadHistory
    {
        /**
         * @var int
         */
        protected $kDownloadHistory;

        /**
         * @var int
         */
        protected $kDownload;

        /**
         * @var int
         */
        protected $kKunde;

        /**
         * @var int
         */
        protected $kBestellung;

        /**
         * @var string
         */
        protected $dErstellt;

        /**
         * @param int $kDownloadHistory
         */
        public function __construct(int $kDownloadHistory = 0)
        {
            if ($kDownloadHistory > 0) {
                $this->loadFromDB($kDownloadHistory);
            }
        }

        /**
         * @param int $kDownloadHistory
         */
        private function loadFromDB(int $kDownloadHistory)
        {
            $oDownloadHistory = Shop::Container()->getDB()->select(
                'tdownloadhistory',
                'kDownloadHistory',
                $kDownloadHistory
            );
            if ($oDownloadHistory !== null && (int)$oDownloadHistory->kDownloadHistory > 0) {
                $cMember_arr = array_keys(get_object_vars($oDownloadHistory));
                if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                    foreach ($cMember_arr as $cMember) {
                        $this->$cMember = $oDownloadHistory->$cMember;
                    }
                    $this->kDownload        = (int)$this->kDownload;
                    $this->kDownloadHistory = (int)$this->kDownloadHistory;
                    $this->kKunde           = (int)$this->kKunde;
                    $this->kBestellung      = (int)$this->kBestellung;
                }
            }
        }

        /**
         * @param int $kDownload
         * @return array
         * @deprecated since 5.0.0
         */
        public static function getHistorys(int $kDownload): array
        {
            trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

            return self::getHistory($kDownload);
        }

        /**
         * @param int $kDownload
         * @return array
         */
        public static function getHistory(int $kDownload): array
        {
            $oDownloadHistory_arr = [];
            if ($kDownload > 0) {
                $oHistory_arr = Shop::Container()->getDB()->selectAll(
                    'tdownloadhistory',
                    'kDownload',
                    $kDownload,
                    'kDownloadHistory',
                    'dErstellt DESC'
                );
                foreach ($oHistory_arr as $oHistory) {
                    $oDownloadHistory_arr[] = new self($oHistory->kDownloadHistory);
                }
            }

            return $oDownloadHistory_arr;
        }

        /**
         * @param int $kKunde
         * @param int $kBestellung
         * @return array
         */
        public static function getOrderHistory(int $kKunde, int $kBestellung = 0): array
        {
            $oHistory_arr = [];
            if ($kBestellung > 0 || $kKunde > 0) {
                $cSQLWhere = 'kBestellung = ' . $kBestellung;
                if ($kBestellung > 0) {
                    $cSQLWhere .= ' AND kKunde = ' . $kKunde;
                }

                $oHistoryTMP_arr = Shop::Container()->getDB()->query(
                    'SELECT kDownload, kDownloadHistory
                         FROM tdownloadhistory
                         WHERE ' . $cSQLWhere . '
                         ORDER BY dErstellt DESC',
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
                foreach ($oHistoryTMP_arr as $oHistoryTMP) {
                    if (!isset($oHistory_arr[$oHistoryTMP->kDownload])
                        || !is_array($oHistory_arr[$oHistoryTMP->kDownload])
                    ) {
                        $oHistory_arr[$oHistoryTMP->kDownload] = [];
                    }
                    $oHistory_arr[$oHistoryTMP->kDownload][] = new self($oHistoryTMP->kDownloadHistory);
                }
            }

            return $oHistory_arr;
        }

        /**
         * @param bool $bPrimary
         * @return bool|int
         */
        public function save(bool $bPrimary = false)
        {
            $oObj = $this->kopiereMembers();
            unset($oObj->kDownloadHistory);

            $kDownloadHistory = Shop::Container()->getDB()->insert('tdownloadhistory', $oObj);
            if ($kDownloadHistory > 0) {
                return $bPrimary ? $kDownloadHistory : true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function update(): int
        {
            $_upd              = new stdClass();
            $_upd->kDownload   = $this->kDownload;
            $_upd->kKunde      = $this->kKunde;
            $_upd->kBestellung = $this->kBestellung;
            $_upd->dErstellt   = $this->dErstellt;

            return Shop::Container()->getDB()->update(
                'tdownloadhistory',
                'kDownloadHistory',
                (int)$this->kDownloadHistory,
                $_upd
            );
        }

        /**
         * @param int $kDownloadHistory
         * @return $this
         */
        public function setDownloadHistory(int $kDownloadHistory): self
        {
            $this->kDownloadHistory = $kDownloadHistory;

            return $this;
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
         * @param int $kKunde
         * @return $this
         */
        public function setKunde(int $kKunde): self
        {
            $this->kKunde = $kKunde;

            return $this;
        }

        /**
         * @param int $kBestellung
         * @return $this
         */
        public function setBestellung(int $kBestellung): self
        {
            $this->kBestellung = $kBestellung;

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
         * @return int
         */
        public function getDownloadHistory(): int
        {
            return (int)$this->kDownloadHistory;
        }

        /**
         * @return int
         */
        public function getDownload(): int
        {
            return (int)$this->kDownload;
        }

        /**
         * @return int
         */
        public function getKunde(): int
        {
            return (int)$this->kKunde;
        }

        /**
         * @return int
         */
        public function getBestellung(): int
        {
            return (int)$this->kBestellung;
        }

        /**
         * @return string|null
         */
        public function getErstellt()
        {
            return $this->dErstellt;
        }

        /**
         * @return stdClass
         */
        private function kopiereMembers(): stdClass
        {
            $obj         = new stdClass();
            $cMember_arr = array_keys(get_object_vars($this));

            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $obj->$cMember = $this->$cMember;
                }
            }

            return $obj;
        }
    }
}
