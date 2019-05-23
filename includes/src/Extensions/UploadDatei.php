<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\DB\ReturnType;
use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class UploadDatei
 * @package JTL\Extensions
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
     * @var bool
     */
    private $licenseOK;

    /**
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
        if ($this->licenseOK && isset($upload->kUpload) && (int)$upload->kUpload > 0) {
            self::copyMembers($upload, $this);

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
        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tbestellung.kKunde
                FROM tuploaddatei 
                JOIN tbestellung
                ON tbestellung.kBestellung = tuploaddatei.kCustomID
                WHERE tuploaddatei.kCustomID = :ulid AND tbestellung.kKunde = :cid',
            ['ulid' => $this->kCustomID ?? 0, 'cid' => $customerID],
            ReturnType::SINGLE_OBJECT
        ) !== false;
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
     * @param int $type
     * @return array
     */
    public static function fetchAll(int $kCustomID, int $type): array
    {
        if (!self::checkLicense()) {
            return [];
        }
        $files   = Shop::Container()->getDB()->selectAll(
            'tuploaddatei',
            ['kCustomID', 'nTyp'],
            [$kCustomID, $type]
        );
        $baseURL = Shop::getURL();
        $crypto  = Shop::Container()->getCryptoService();
        foreach ($files as &$upload) {
            $upload             = self::copyMembers($upload);
            $upload->cGroesse   = Upload::formatGroesse($upload->nBytes);
            $upload->bVorhanden = \is_file(\PFAD_UPLOADS . $upload->cPfad);
            $upload->bVorschau  = Upload::vorschauTyp($upload->cName);
            $upload->cBildpfad  = \sprintf(
                '%s/%s?action=preview&secret=%s&sid=%s',
                $baseURL,
                \PFAD_UPLOAD_CALLBACK,
                \rawurlencode($crypto->encryptXTEA($upload->kUpload)),
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
        $userAgent = !empty($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : '';
        if (\preg_match('/Opera\/([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'opera';
        } elseif (\preg_match('/MSIE ([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'ie';
        } elseif (\preg_match('/OmniWeb\/([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'omniweb';
        } elseif (\preg_match('/Mozilla\/([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'mozilla';
        } elseif (\preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $userAgent, $log_version)) {
            $browser = 'konqueror';
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
