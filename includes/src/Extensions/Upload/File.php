<?php declare(strict_types=1);

namespace JTL\Extensions\Upload;

use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class File
 * @package JTL\Extensions\Upload
 */
class File
{
    /**
     * @var int|null
     */
    public ?int $kUpload = null;

    /**
     * @var int
     */
    public int $kCustomID = 0;

    /**
     * @var int
     */
    public int $nTyp = 0;

    /**
     * @var int
     */
    public int $nBytes = 0;

    /**
     * @var string|null
     */
    public ?string $cName = null;

    /**
     * @var string|null
     */
    public ?string $cPfad = null;

    /**
     * @var string|null
     */
    public ?string $dErstellt = null;

    /**
     * @var bool
     */
    private bool $licenseOK;

    /**
     * File constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        $this->licenseOK = self::checkLicense();
        if ($id > 0 && $this->licenseOK) {
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
     * @return bool
     */
    public function loadFromDB(int $id): bool
    {
        $upload = Shop::Container()->getDB()->select('tuploaddatei', 'kUpload', $id);
        if ($this->licenseOK && $upload !== null && $upload->kUpload > 0) {
            $this->kUpload   = (int)$upload->kUpload;
            $this->kCustomID = (int)$upload->kCustomID;
            $this->nTyp      = (int)$upload->nTyp;
            $this->nBytes    = (int)$upload->nBytes;
            $this->cName     = $upload->cName;
            $this->cPfad     = $upload->cPfad;
            $this->dErstellt = $upload->dErstellt;

            return true;
        }

        return false;
    }

    /**
     * @param int $customerID
     * @return bool
     */
    public function validateOwner(int $customerID): bool
    {
        return Shop::Container()->getDB()->getSingleObject(
            'SELECT tbestellung.kKunde
                FROM tuploaddatei 
                JOIN tbestellung
                ON tbestellung.kBestellung = tuploaddatei.kCustomID
                WHERE tuploaddatei.kCustomID = :ulid AND tbestellung.kKunde = :cid',
            ['ulid' => $this->kCustomID ?? 0, 'cid' => $customerID]
        ) !== null;
    }

    /**
     * @param int $customID
     * @param int $type
     * @return stdClass[]
     */
    public static function fetchAll(int $customID, int $type): array
    {
        if (!self::checkLicense()) {
            return [];
        }
        $files   = Shop::Container()->getDB()->selectAll(
            'tuploaddatei',
            ['kCustomID', 'nTyp'],
            [$customID, $type]
        );
        $baseURL = Shop::getURL();
        $crypto  = Shop::Container()->getCryptoService();
        foreach ($files as &$upload) {
            $upload = self::copyMembers($upload);
            if ($upload === null) {
                continue;
            }
            $upload->cGroesse   = Upload::formatGroesse($upload->nBytes);
            $upload->bVorhanden = \is_file(\PFAD_UPLOADS . $upload->cPfad);
            $upload->bVorschau  = Upload::vorschauTyp($upload->cName);
            $upload->cBildpfad  = \sprintf(
                '%s/%s?action=preview&secret=%s&sid=%s',
                $baseURL,
                \PFAD_UPLOAD_CALLBACK,
                \rawurlencode($crypto->encryptXTEA((string)$upload->kUpload)),
                \session_id()
            );
        }

        return $files;
    }

    /**
     * @param object      $objFrom
     * @param null|object $objTo
     * @return object
     */
    private static function copyMembers($objFrom, &$objTo = null)
    {
        if (!\is_object($objTo)) {
            $objTo = new stdClass();
        }
        foreach (\array_keys(\get_object_vars($objFrom)) as $member) {
            $objTo->$member = $objFrom->$member;
        }
        $objTo->kUpload   = (int)$objTo->kUpload;
        $objTo->kCustomID = (int)$objTo->kCustomID;
        $objTo->nBytes    = (int)$objTo->nBytes;
        $objTo->nTyp      = (int)$objTo->nTyp;

        return $objTo;
    }

    /**
     * @param string $filename
     * @param string $mimetype
     * @param string $downloadName
     */
    public static function send_file_to_browser(string $filename, string $mimetype, string $downloadName): void
    {
        $browser   = 'other';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (\preg_match('/Opera\/(\d.\d{1,2})/', $userAgent, $log_version)) {
            $browser = 'opera';
        } elseif (\preg_match('/MSIE (\d.\d{1,2})/', $userAgent, $log_version)) {
            $browser = 'ie';
        }
        if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
            $mimetype = ($browser === 'ie' || $browser === 'opera')
                ? 'application/octetstream'
                : 'application/octet-stream';
        }

        @\ob_end_clean();
        @\ini_set('zlib.output_compression', 'Off');

        \header('Pragma: public');
        \header('Content-Transfer-Encoding: none');
        if ($browser === 'ie') {
            \header('Content-Type: ' . $mimetype);
            \header('Content-Disposition: inline; filename="' . $downloadName . '"');
        } else {
            \header('Content-Type: ' . $mimetype . '; name="' . \basename($filename) . '"');
            \header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        }

        $size = @\filesize($filename);
        if ($size) {
            \header('Content-length: ' . $size);
        }

        \readfile($filename);
        exit;
    }
}
