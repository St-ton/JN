<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Extensions;

use Helpers\PHPSettings;

/**
 * Class Upload
 *
 * @package Extensions
 */
final class Upload
{
    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return \Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_UPLOADS);
    }

    /**
     * @param int        $kArtikel
     * @param bool|array $eigenschaftenArr
     * @return array
     */
    public static function gibArtikelUploads(int $kArtikel, $eigenschaftenArr = false): array
    {
        $scheme  = new UploadSchema();
        $uploads = $scheme->fetchAll($kArtikel, \UPLOAD_TYP_WARENKORBPOS);
        foreach ($uploads as $upload) {
            $upload->nEigenschaften_arr = $eigenschaftenArr;
            $upload->cUnique            = self::uniqueDateiname($upload);
            $upload->cDateiTyp_arr      = self::formatTypen($upload->cDateiTyp);
            $upload->cDateiListe        = \implode(';', $upload->cDateiTyp_arr);
            $upload->bVorhanden         = \is_file(\PFAD_UPLOADS . $upload->cUnique);
            $file                       = $_SESSION['Uploader'][$upload->cUnique] ?? null;
            if ($file !== null && \is_object($file)) {
                $upload->cDateiname    = $file->cName;
                $upload->cDateigroesse = self::formatGroesse($file->nBytes);
            }
        }

        return $uploads;
    }

    /**
     * Deletes all uploaded files for an article with ID (kArtikel)
     *
     * @param  int $kArtikel
     * @return int
     */
    public static function deleteArtikelUploads(int $kArtikel): int
    {
        $count   = 0;
        $uploads = self::gibArtikelUploads($kArtikel);

        foreach ($uploads as $upload) {
            unset($_SESSION['Uploader'][$upload->cUnique]);
            if ($upload->bVorhanden && \unlink(\PFAD_UPLOADS . $upload->cUnique)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param \Warenkorb $cart
     * @return \stdClass[]
     */
    public static function gibWarenkorbUploads(\Warenkorb $cart): array
    {
        $uploads = [];
        foreach ($cart->PositionenArr as $position) {
            if ($position->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL || empty($position->Artikel->kArtikel)) {
                continue;
            }
            $eigenschaftArr = [];
            if (!empty($position->WarenkorbPosEigenschaftArr)) {
                foreach ($position->WarenkorbPosEigenschaftArr as $eigenschaft) {
                    $eigenschaftArr[$eigenschaft->kEigenschaft] = \is_string($eigenschaft->cEigenschaftWertName)
                        ? $eigenschaft->cEigenschaftWertName
                        : \reset($eigenschaft->cEigenschaftWertName);
                }
            }
            $upload        = new \stdClass();
            $upload->cName = $position->Artikel->cName;
            if (!empty($position->WarenkorbPosEigenschaftArr)) {
                $upload->WarenkorbPosEigenschaftArr = $position->WarenkorbPosEigenschaftArr;
            }
            $upload->oUpload_arr = self::gibArtikelUploads($position->Artikel->kArtikel, $eigenschaftArr);
            if (\count($upload->oUpload_arr) > 0) {
                $uploads[] = $upload;
            }
        }

        return $uploads;
    }

    /**
     * @param int $kBestellung
     * @return array
     */
    public static function gibBestellungUploads(int $kBestellung): array
    {
        return UploadDatei::fetchAll($kBestellung, UPLOAD_TYP_BESTELLUNG);
    }

    /**
     * @param \Warenkorb $cart
     * @return bool
     */
    public static function pruefeWarenkorbUploads(\Warenkorb $cart): bool
    {
        foreach (self::gibWarenkorbUploads($cart) as $scheme) {
            foreach ($scheme->oUpload_arr as $oUpload) {
                if ($oUpload->nPflicht && !$oUpload->bVorhanden) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param int $nErrorCode
     */
    public static function redirectWarenkorb(int $nErrorCode): void
    {
        \header('Location: ' .
            \LinkHelper::getInstance()->getStaticRoute('warenkorb.php') .
            '?fillOut=' . $nErrorCode, true, 303);
    }

    /**
     * @param \Warenkorb $cart
     * @param int       $kBestellung
     */
    public static function speicherUploadDateien(\Warenkorb $cart, int $kBestellung): void
    {
        foreach (self::gibWarenkorbUploads($cart) as $scheme) {
            foreach ($scheme->oUpload_arr as $upload) {
                $info = $_SESSION['Uploader'][$upload->cUnique] ?? null;
                if ($info !== null && \is_object($info)) {
                    self::setzeUploadQueue($kBestellung, $upload->kCustomID);
                    self::setzeUploadDatei(
                        $kBestellung,
                        \UPLOAD_TYP_BESTELLUNG,
                        $info->cName,
                        $upload->cUnique,
                        $info->nBytes
                    );
                }
                unset($_SESSION['Uploader'][$upload->cUnique]);
            }
        }
        \session_regenerate_id();
        unset($_SESSION['Uploader']);
    }

    /**
     * @param int    $kCustomID
     * @param int    $nTyp
     * @param string $cName
     * @param string $cPfad
     * @param int    $nBytes
     */
    public static function setzeUploadDatei(int $kCustomID, int $nTyp, $cName, $cPfad, int $nBytes): void
    {
        $file            = new \stdClass();
        $file->kCustomID = $kCustomID;
        $file->nTyp      = $nTyp;
        $file->cName     = $cName;
        $file->cPfad     = $cPfad;
        $file->nBytes    = $nBytes;
        $file->dErstellt = 'NOW()';

        \Shop::Container()->getDB()->insert('tuploaddatei', $file);
    }

    /**
     * @param int $kBestellung
     * @param int $kCustomID
     */
    public static function setzeUploadQueue(int $kBestellung, int $kCustomID): void
    {
        $queue              = new \stdClass();
        $queue->kBestellung = $kBestellung;
        $queue->kArtikel    = $kCustomID;

        \Shop::Container()->getDB()->insert('tuploadqueue', $queue);
    }

    /**
     * @return int|mixed
     */
    public static function uploadMax()
    {
        $helper = PHPSettings::getInstance();

        return \min(
            $helper->uploadMaxFileSize(),
            $helper->postMaxSize(),
            $helper->limit()
        );
    }

    /**
     * @param int $fileSize
     * @return string
     */
    public static function formatGroesse($fileSize): string
    {
        if (!\is_numeric($fileSize)) {
            return '---';
        }
        $step     = 0;
        $decr     = 1024;
        $prefixes = ['Byte', 'KB', 'MB', 'GB', 'TB', 'PB'];

        while (($fileSize / $decr) > 0.9) {
            $fileSize /= $decr;
            ++$step;
        }

        return \round($fileSize, 2) . ' ' . $prefixes[$step];
    }

    /**
     * @param object $upload
     * @return string
     */
    public static function uniqueDateiname($upload): string
    {
        $unique = $upload->kUploadSchema . $upload->kCustomID . $upload->nTyp . self::getSessionKey();
        if (!empty($upload->nEigenschaften_arr)) {
            $eigenschaften = '';
            foreach ($upload->nEigenschaften_arr as $k => $v) {
                $eigenschaften .= $k . $v;
            }
            $unique .= $eigenschaften;
        }

        return \md5($unique);
    }

    /**
     * @return string
     */
    private static function getSessionKey(): string
    {
        if (!isset($_SESSION['Uploader']['sessionKey'])) {
            $_SESSION['Uploader']['sessionKey'] = \uniqid('sk', true);
        }

        return $_SESSION['Uploader']['sessionKey'];
    }

    /**
     * @param string $type
     * @return array
     */
    public static function formatTypen(string $type): array
    {
        $fileTypes = \explode(',', $type);
        foreach ($fileTypes as &$cTyp) {
            $cTyp = '*' . $cTyp;
        }

        return $fileTypes;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function vorschauTyp(string $name): bool
    {
        $pathInfo = \pathinfo($name);

        return \is_array($pathInfo)
            ? \in_array(
                $pathInfo['extension'],
                ['gif', 'png', 'jpg', 'jpeg', 'bmp', 'jpe'],
                true
            )
            : false;
    }
}
