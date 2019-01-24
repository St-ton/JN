<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

use DB\ReturnType;

/**
 * Class UploadSchema
 *
 * @package Extensions
 */
final class UploadSchema
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
     * @var bool
     */
    private $licenseOK;

    /**
     * @param int $kUploadSchema
     */
    public function __construct(int $kUploadSchema = 0)
    {
        $this->licenseOK = self::checkLicense();
        if ($kUploadSchema > 0 && $this->licenseOK === true) {
            $this->loadFromDB($kUploadSchema);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return \Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_UPLOADS);
    }

    /**
     * @param int $kUploadSchema
     */
    private function loadFromDB(int $kUploadSchema): void
    {
        $upload = \Shop::Container()->getDB()->queryPrepared(
            'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                tuploadschema.cDateiTyp, tuploadschema.nPflicht, tuploadschemasprache.cName, 
                tuploadschemasprache.cBeschreibung
                FROM tuploadschema
                LEFT JOIN tuploadschemasprache
                    ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                    AND tuploadschemasprache.kSprache = :lid
                WHERE kUploadSchema =  :uid',
            [
                'lid' => \Shop::getLanguageID(),
                'uid' => $kUploadSchema
            ],
            ReturnType::SINGLE_OBJECT
        );

        if (isset($upload->kUploadSchema) && (int)$upload->kUploadSchema > 0) {
            self::copyMembers($upload, $this);
        }
    }

    /**
     * @return int
     */
    public function save(): int
    {
        return \Shop::Container()->getDB()->insert('tuploadschema', self::copyMembers($this));
    }

    /**
     * @return int
     */
    public function update(): int
    {
        return \Shop::Container()->getDB()->update(
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
        return \Shop::Container()->getDB()->delete('tuploadschema', 'kUploadSchema', (int)$this->kUploadSchema);
    }

    /**
     * @param int $kCustomID
     * @param int $type
     * @return \stdClass[]
     */
    public function fetchAll(int $kCustomID, int $type): array
    {
        if (!$this->licenseOK) {
            return [];
        }
        $cSql = $type === \UPLOAD_TYP_WARENKORBPOS
            ? ' AND kCustomID = ' . $kCustomID
            : '';

        return \Shop::Container()->getDB()->queryPrepared(
            'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                tuploadschema.cDateiTyp, tuploadschema.nPflicht, 
                IFNULL(tuploadschemasprache.cName,tuploadschema.cName ) cName,
                IFNULL(tuploadschemasprache.cBeschreibung, tuploadschema.cBeschreibung) cBeschreibung
                FROM tuploadschema
                LEFT JOIN tuploadschemasprache
                    ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                    AND tuploadschemasprache.kSprache = :lid
                WHERE nTyp = :tpe' . $cSql,
            ['tpe' => $type, 'lid' => \Shop::getLanguage()],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param object      $objFrom
     * @param object|null $objTo
     * @return null|object
     */
    private static function copyMembers($objFrom, &$objTo = null)
    {
        if (!\is_object($objTo)) {
            $objTo = new \stdClass();
        }
        foreach (\array_keys(\get_object_vars($objFrom)) as $member) {
            $objTo->$member = $objFrom->$member;
        }

        return $objTo;
    }
}
