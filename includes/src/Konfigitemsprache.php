<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfigitemsprache
     */
    class Konfigitemsprache
    {
        /**
         * @var int
         */
        protected $kKonfigitem;

        /**
         * @var int
         */
        protected $kSprache;

        /**
         * @var string
         */
        protected $cName = '';

        /**
         * @var string
         */
        protected $cBeschreibung = '';

        /**
         * Konfigitemsprache constructor.
         * @param int $kKonfigitem
         * @param int $kSprache
         */
        public function __construct(int $kKonfigitem = 0, int $kSprache = 0)
        {
            if ($kKonfigitem > 0 && $kSprache > 0) {
                $this->loadFromDB($kKonfigitem, $kSprache);
            }
        }

        /**
         * Loads database member into class member
         *
         * @param int $kKonfigitem
         * @param int $kSprache
         */
        private function loadFromDB(int $kKonfigitem = 0, int $kSprache = 0)
        {
            $oObj = Shop::Container()->getDB()->select(
                'tkonfigitemsprache',
                'kKonfigitem', $kKonfigitem,
                'kSprache', $kSprache
            );
            if ($oObj !== null && empty($oObj->cName)) {
                $kSprache         = gibStandardsprache();
                $StandardLanguage = Shop::Container()->getDB()->select(
                    'tkonfigitemsprache',
                    'kKonfigitem', $kKonfigitem,
                    'kSprache', (int)$kSprache->kSprache,
                    null, null,
                    false,
                    'cName'
                );
                $oObj->cName      = $StandardLanguage->cName;
            }
            if ($oObj !== null && empty($oObj->cBeschreibung)) {
                $kSprache            = gibStandardsprache();
                $StandardLanguage    = Shop::Container()->getDB()->select(
                    'tkonfigitemsprache',
                    'kKonfigitem', $kKonfigitem,
                    'kSprache', (int)$kSprache->kSprache,
                    null, null,
                    false,
                    'cBeschreibung'
                );
                $oObj->cBeschreibung = $StandardLanguage->cBeschreibung;
            }

            if (isset($oObj->kKonfigitem, $oObj->kSprache) && $oObj->kKonfigitem > 0 && $oObj->kSprache > 0) {
                $cMember_arr = array_keys(get_object_vars($oObj));
                foreach ($cMember_arr as $cMember) {
                    $this->$cMember = $oObj->$cMember;
                }
            }
        }

        /**
         * @param bool $bPrim
         * @return bool|int
         */
        public function save(bool $bPrim = true)
        {
            $oObj        = new stdClass();
            $cMember_arr = array_keys(get_object_vars($this));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $oObj->$cMember = $this->$cMember;
                }
            }
            unset($oObj->kKonfigitem, $oObj->kSprache);

            $kPrim = Shop::Container()->getDB()->insert('tkonfigitemsprache', $oObj);

            if ($kPrim > 0) {
                return $bPrim ? $kPrim : true;
            }

            return false;
        }

        /**
         * @return int
         */
        public function update(): int
        {
            $_upd                = new stdClass();
            $_upd->cName         = $this->getName();
            $_upd->cBeschreibung = $this->getBeschreibung();

            return Shop::Container()->getDB()->update(
                'tkonfigitemsprache',
                ['kKonfigitem', 'kSprache'],
                [$this->getKonfigitem(), $this->getSprache()],
                $_upd
            );
        }

        /**
         * @return int
         */
        public function delete(): int
        {
            return Shop::Container()->getDB()->delete(
                'tkonfigitemsprache',
                ['kKonfigitem', 'kSprache'],
                [(int)$this->kKonfigitem, (int)$this->kSprache]
            );
        }

        /**
         * @param int $kKonfigitem
         * @return $this
         */
        public function setKonfigitem(int $kKonfigitem): self
        {
            $this->kKonfigitem = $kKonfigitem;

            return $this;
        }

        /**
         * @param int $kSprache
         * @return $this
         */
        public function setSprache(int $kSprache): self
        {
            $this->kSprache = $kSprache;

            return $this;
        }

        /**
         * @param string $cName
         * @return $this
         */
        public function setName(string $cName): self
        {
            $this->cName = Shop::Container()->getDB()->escape($cName);

            return $this;
        }

        /**
         * @param string $cBeschreibung
         * @return $this
         */
        public function setBeschreibung(string $cBeschreibung): self
        {
            $this->cBeschreibung = Shop::Container()->getDB()->escape($cBeschreibung);

            return $this;
        }

        /**
         * @return int
         */
        public function getKonfigitem(): int
        {
            return (int)$this->kKonfigitem;
        }

        /**
         * @return int
         */
        public function getSprache(): int
        {
            return (int)$this->kSprache;
        }

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->cName;
        }

        /**
         * @return string
         */
        public function getBeschreibung(): string
        {
            return $this->cBeschreibung;
        }
    }
}
