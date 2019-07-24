<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;

/**
 * Speichert das aktuelle ShopLogo
 *
 * @param array $files
 * @return int
 * 1 = Alles O.K.
 * 2 = Dateiname leer
 * 3 = Dateityp entspricht nicht der Konvention (Nur jpg/gif/png/bmp/ Bilder) oder fehlt
 * 4 = Konnte nicht bewegen
 */
function saveShopLogo(array $files): int
{
    if (!file_exists(PFAD_ROOT . PFAD_SHOPLOGO)) {
        mkdir(PFAD_ROOT . PFAD_SHOPLOGO);
    }
    // Prüfe Dateiname
    if (mb_strlen($files['shopLogo']['name']) > 0) {
        // Prüfe Dateityp
        if ($files['shopLogo']['type'] !== 'image/jpeg'
            && $files['shopLogo']['type'] !== 'image/pjpeg'
            && $files['shopLogo']['type'] !== 'image/gif'
            && $files['shopLogo']['type'] !== 'image/png'
            && $files['shopLogo']['type'] !== 'image/bmp'
            && $files['shopLogo']['type'] !== 'image/x-png'
            && $files['shopLogo']['type'] !== 'image/jpg'
        ) {
            // Dateityp entspricht nicht der Konvention (Nur jpg/gif/png/bmp/ Bilder) oder fehlt
            return 3;
        }
        $uploadFile = PFAD_ROOT . PFAD_SHOPLOGO . basename($files['shopLogo']['name']);
        if ($files['shopLogo']['error'] === UPLOAD_ERR_OK
            && move_uploaded_file($files['shopLogo']['tmp_name'], $uploadFile)
        ) {
            $option                        = new stdClass();
            $option->kEinstellungenSektion = CONF_LOGO;
            $option->cName                 = 'shop_logo';
            $option->cWert                 = $files['shopLogo']['name'];
            Shop::Container()->getDB()->update('teinstellungen', 'cName', 'shop_logo', $option);
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION]);

            return 1; // Alles O.K.
        }

        return 4;
    }

    return 2; // Dateiname fehlt
}

/**
 * @var string $logo
 * @return bool
 */
function deleteShopLogo(string $logo): bool
{
    return is_file(PFAD_ROOT . $logo)
        ? unlink(PFAD_ROOT . $logo)
        : false;
}

/**
 * @return bool
 */
function loescheAlleShopBilder(): bool
{
    if (is_dir(PFAD_ROOT . PFAD_SHOPLOGO) && $dh = opendir(PFAD_ROOT . PFAD_SHOPLOGO)) {
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                @unlink(PFAD_ROOT . PFAD_SHOPLOGO . $file);
            }
        }
        closedir($dh);

        return true;
    }

    return false;
}

/**
 * @param string $type
 * @return string
 */
function mappeFileTyp(string $type): string
{
    switch ($type) {
        case 'image/jpeg':
            return '.jpg';
            break;
        case 'image/pjpeg':
            return '.jpg';
            break;
        case 'image/gif':
            return '.gif';
            break;
        case 'image/png':
            return '.png';
            break;
        case 'image/bmp':
            return '.bmp';
            break;
        // Adding MIME types that Internet Explorer returns
        case 'image/x-png':
            return '.png';
            break;
        case 'image/jpg':
            return '.jpg';
            break;
        //default jpg
        default:
            return '.jpg';
            break;
    }
}
