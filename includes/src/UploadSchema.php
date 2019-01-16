<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_UPLOADS)) {
    /**
     * Class UploadSchema
     */
    class UploadSchema
    {
        /**
         * @var int
         */
        public $kUploadSchema;

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
        public $cBeschreibung;

        /**
         * @var string
         */
        public $cDateiTyp;

        /**
         * @var int
         */
        public $nPflicht;

        /**
         * @param int $kUploadSchema
         */
        public function __construct(int $kUploadSchema = 0)
        {
            if ($kUploadSchema > 0) {
                $this->loadFromDB($kUploadSchema);
            }
        }

        /**
         * @param int $kUploadSchema
         */
        private function loadFromDB(int $kUploadSchema): void
        {
            $oUpload = Shop::Container()->getDB()->queryPrepared(
                'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                    tuploadschema.cDateiTyp, tuploadschema.nPflicht, tuploadschemasprache.cName, 
                    tuploadschemasprache.cBeschreibung
                    FROM tuploadschema
                    LEFT JOIN tuploadschemasprache
                        ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                        AND tuploadschemasprache.kSprache = :lid
                    WHERE kUploadSchema =  :uid',
                [
                    'lid' => Shop::getLanguageID(),
                    'uid' => $kUploadSchema
                ],
                \DB\ReturnType::SINGLE_OBJECT
            );

            if (isset($oUpload->kUploadSchema) && (int)$oUpload->kUploadSchema > 0) {
                self::copyMembers($oUpload, $this);
            }
        }

        /**
         * @return int
         */
        public function save(): int
        {
            return Shop::Container()->getDB()->insert('tuploadschema', self::copyMembers($this));
        }

        /**
         * @return int
         */
        public function update(): int
        {
            return Shop::Container()->getDB()->update(
                'tuploadschema',
                'kUploadSchema',
                (int)$this->kUploadSchema,
                self::copyMembers($this)
            );
        }

        /**
         * @return int
         */
        public function delete(): int
        {
            return Shop::Container()->getDB()->delete('tuploadschema', 'kUploadSchema', (int)$this->kUploadSchema);
        }

        /**
         * @param int $kCustomID
         * @param int $nTyp
         * @return array
         */
        public static function fetchAll(int $kCustomID, int $nTyp): array
        {
            $cSql = $nTyp === UPLOAD_TYP_WARENKORBPOS
                ? ' AND kCustomID = ' . $kCustomID
                : '';

            return Shop::Container()->getDB()->queryPrepared(
                'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                    tuploadschema.cDateiTyp, tuploadschema.nPflicht, 
                    IFNULL(tuploadschemasprache.cName,tuploadschema.cName ) cName,
                    IFNULL(tuploadschemasprache.cBeschreibung, tuploadschema.cBeschreibung) cBeschreibung
                    FROM tuploadschema
                    LEFT JOIN tuploadschemasprache
                        ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                        AND tuploadschemasprache.kSprache = :lid
                    WHERE nTyp = :tpe' . $cSql,
                ['tpe' => $nTyp, 'lid' => Shop::getLanguage()],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }

        /**
         * @param object      $objFrom
         * @param object|null $objTo
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
    }
}
