<?php declare(strict_types=1);

namespace JTL\Extensions\Upload;

use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class Scheme
 * @package JTL\Extensions\Upload
 */
final class Scheme
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
     * Scheme constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        $this->licenseOK = self::checkLicense();
        if ($id > 0 && $this->licenseOK === true) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_UPLOADS);
    }

    /**
     * @param int $id
     */
    private function loadFromDB(int $id): void
    {
        $upload = Shop::Container()->getDB()->getSingleObject(
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
                'uid' => $id
            ]
        );
        if ($upload !== null && (int)$upload->kUploadSchema > 0) {
            $this->copyMembers($upload);
        }
    }

    /**
     * @param int $kCustomID
     * @param int $type
     * @return stdClass[]
     */
    public function fetchAll(int $kCustomID, int $type): array
    {
        if (!$this->licenseOK) {
            return [];
        }
        $sql = $type === \UPLOAD_TYP_WARENKORBPOS
            ? ' AND kCustomID = ' . $kCustomID
            : '';

        return \array_map(
            static function (stdClass $item) {
                $item->kUploadSchema = (int)$item->kUploadSchema;
                $item->kCustomID     = (int)$item->kCustomID;
                $item->nTyp          = (int)$item->nTyp;
                $item->nPflicht      = (int)$item->nPflicht;

                return $item;
            },
            Shop::Container()->getDB()->getObjects(
                'SELECT tuploadschema.kUploadSchema, tuploadschema.kCustomID, tuploadschema.nTyp, 
                    tuploadschema.cDateiTyp, tuploadschema.nPflicht, 
                    IFNULL(tuploadschemasprache.cName,tuploadschema.cName ) cName,
                    IFNULL(tuploadschemasprache.cBeschreibung, tuploadschema.cBeschreibung) cBeschreibung
                    FROM tuploadschema
                    LEFT JOIN tuploadschemasprache
                        ON tuploadschemasprache.kArtikelUpload = tuploadschema.kUploadSchema
                        AND tuploadschemasprache.kSprache = :lid
                    WHERE nTyp = :tpe' . $sql,
                ['tpe' => $type, 'lid' => Shop::getLanguageID()]
            )
        );
    }

    /**
     * @param stdClass $from
     * @return $this
     */
    private function copyMembers(stdClass $from): self
    {
        foreach (\array_keys(\get_object_vars($from)) as $member) {
            $this->$member = $from->$member;
        }
        $this->kUploadSchema = (int)$this->kUploadSchema;
        $this->kCustomID     = (int)$this->kCustomID;
        $this->nTyp          = (int)$this->nTyp;
        $this->nPflicht      = (int)$this->nPflicht;

        return $this;
    }
}
