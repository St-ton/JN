<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JTL\Cart\Warenkorb;
use JTL\Helpers\PHPSettings;
use JTL\Nice;
use JTL\Services\JTL\LinkService;
use JTL\Shop;
use stdClass;

/**
 * Class Upload
 * @package JTL\Extensions
 */
final class Upload
{
    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_UPLOADS);
    }

    /**
     * @param int        $productID
     * @param bool|array $attributes
     * @return array
     */
    public static function gibArtikelUploads(int $productID, $attributes = false): array
    {
        $scheme  = new UploadSchema();
        $uploads = $scheme->fetchAll($productID, \UPLOAD_TYP_WARENKORBPOS);
        foreach ($uploads as $upload) {
            $upload->nEigenschaften_arr = $attributes;
            $upload->cUnique            = self::uniqueDateiname($upload);
            $upload->cDateiTyp_arr      = self::formatTypen($upload->cDateiTyp);
            $upload->cDateiListe        = \implode(';', $upload->cDateiTyp_arr);
            $upload->bVorhanden         = \is_file(\PFAD_UPLOADS . $upload->cUnique);
            $upload->prodID             = $productID;
            $file                       = $_SESSION['Uploader'][$upload->cUnique] ?? null;
            if ($file !== null && \is_object($file)) {
                $upload->cDateiname    = $file->cName;
                $upload->cDateigroesse = self::formatGroesse($file->nBytes);
            }
        }

        return $uploads;
    }

    /**
     * @param  int $productID
     * @return int
     */
    public static function deleteArtikelUploads(int $productID): int
    {
        $count = 0;
        foreach (self::gibArtikelUploads($productID) as $upload) {
            unset($_SESSION['Uploader'][$upload->cUnique]);
            if ($upload->bVorhanden && \unlink(\PFAD_UPLOADS . $upload->cUnique)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param Warenkorb $cart
     * @return stdClass[]
     */
    public static function gibWarenkorbUploads(Warenkorb $cart): array
    {
        $uploads = [];
        foreach ($cart->PositionenArr as $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || empty($item->Artikel->kArtikel)) {
                continue;
            }
            $attributes = [];
            if (!empty($item->WarenkorbPosEigenschaftArr)) {
                foreach ($item->WarenkorbPosEigenschaftArr as $attribute) {
                    $attributes[$attribute->kEigenschaft] = \is_string($attribute->cEigenschaftWertName)
                        ? $attribute->cEigenschaftWertName
                        : \reset($attribute->cEigenschaftWertName);
                }
            }
            $upload         = new stdClass();
            $upload->cName  = $item->Artikel->cName;
            $upload->prodID = $item->Artikel->kArtikel;
            if (!empty($item->WarenkorbPosEigenschaftArr)) {
                $upload->WarenkorbPosEigenschaftArr = $item->WarenkorbPosEigenschaftArr;
            }
            $upload->oUpload_arr = self::gibArtikelUploads($item->Artikel->kArtikel, $attributes);
            if (\count($upload->oUpload_arr) > 0) {
                $uploads[] = $upload;
            }
        }

        return $uploads;
    }

    /**
     * @param int $orderID
     * @return array
     */
    public static function gibBestellungUploads(int $orderID): array
    {
        return UploadDatei::fetchAll($orderID, \UPLOAD_TYP_BESTELLUNG);
    }

    /**
     * @param Warenkorb $cart
     * @return bool
     */
    public static function pruefeWarenkorbUploads(Warenkorb $cart): bool
    {
        foreach (self::gibWarenkorbUploads($cart) as $scheme) {
            foreach ($scheme->oUpload_arr as $upload) {
                if ($upload->nPflicht && !$upload->bVorhanden) {
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
            LinkService::getInstance()->getStaticRoute('warenkorb.php') .
            '?fillOut=' . $nErrorCode, true, 303);
    }

    /**
     * @param Warenkorb $cart
     * @param int       $orderID
     */
    public static function speicherUploadDateien(Warenkorb $cart, int $orderID): void
    {
        foreach (self::gibWarenkorbUploads($cart) as $scheme) {
            foreach ($scheme->oUpload_arr as $upload) {
                $info = $_SESSION['Uploader'][$upload->cUnique] ?? null;
                if ($info !== null && \is_object($info)) {
                    self::setzeUploadQueue($orderID, $upload->kCustomID);
                    self::setzeUploadDatei(
                        $orderID,
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
     * @param int    $type
     * @param string $name
     * @param string $path
     * @param int    $bytes
     */
    public static function setzeUploadDatei(int $kCustomID, int $type, $name, $path, int $bytes): void
    {
        $file            = new stdClass();
        $file->kCustomID = $kCustomID;
        $file->nTyp      = $type;
        $file->cName     = $name;
        $file->cPfad     = $path;
        $file->nBytes    = $bytes;
        $file->dErstellt = 'NOW()';

        Shop::Container()->getDB()->insert('tuploaddatei', $file);
    }

    /**
     * @param int $orderID
     * @param int $productID
     */
    public static function setzeUploadQueue(int $orderID, int $productID): void
    {
        $queue              = new stdClass();
        $queue->kBestellung = $orderID;
        $queue->kArtikel    = $productID;

        Shop::Container()->getDB()->insert('tuploadqueue', $queue);
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
            foreach ($upload->nEigenschaften_arr as $k => $v) {
                $unique .= $k . $v;
            }
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
        foreach ($fileTypes as &$type) {
            $type = '*' . $type;
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
