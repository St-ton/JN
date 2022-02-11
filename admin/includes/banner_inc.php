<?php declare(strict_types=1);

use JTL\ImageMap;
use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * @return stdClass[]
 */
function holeAlleBanner(): array
{
    return (new ImageMap(Shop::Container()->getDB()))->fetchAll();
}

/**
 * @param int  $imageMapID
 * @param bool $fill
 * @return bool|stdClass
 */
function holeBanner(int $imageMapID, bool $fill = true)
{
    return (new ImageMap(Shop::Container()->getDB()))->fetch($imageMapID, true, $fill);
}

/**
 * @param int $imageMapID
 * @return mixed
 */
function holeExtension(int $imageMapID)
{
    return Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', $imageMapID);
}

/**
 * @param int $imageMapID
 * @return bool
 */
function entferneBanner(int $imageMapID): bool
{
    $db     = Shop::Container()->getDB();
    $banner = new ImageMap($db);
    $db->delete('textensionpoint', ['cClass', 'kInitial'], ['ImageMap', $imageMapID]);

    return $banner->delete($imageMapID);
}

/**
 * @return string[]
 */
function holeBannerDateien(): array
{
    $files = [];
    if (($handle = opendir(PFAD_ROOT . PFAD_BILDER_BANNER)) !== false) {
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..' && $file[0] !== '.') {
                $files[] = $file;
            }
        }
        closedir($handle);
    }

    return $files;
}

/**
 * @param mixed $data
 * @return IOResponse
 */
function saveBannerAreasIO($data): IOResponse
{
    $banner   = new ImageMap(Shop::Container()->getDB());
    $response = new IOResponse();
    $data     = json_decode($data);
    foreach ($data->oArea_arr as $area) {
        $area->kArtikel      = (int)$area->kArtikel;
        $area->kImageMap     = (int)$area->kImageMap;
        $area->kImageMapArea = (int)$area->kImageMapArea;
    }
    $banner->saveAreas($data);

    return $response;
}
