<?php declare(strict_types=1);

use JTL\ImageMap;
use JTL\IO\IOResponse;
use JTL\Shop;

/**
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function holeAlleBanner(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ImageMap class instead.', E_USER_DEPRECATED);
    return (new ImageMap(Shop::Container()->getDB()))->fetchAll();
}

/**
 * @param int  $imageMapID
 * @param bool $fill
 * @return bool|stdClass
 * @deprecated since 5.2.0
 */
function holeBanner(int $imageMapID, bool $fill = true)
{
    trigger_error(__FUNCTION__ . ' is deprecated. Use ImageMap class instead.', E_USER_DEPRECATED);
    return (new ImageMap(Shop::Container()->getDB()))->fetch($imageMapID, true, $fill);
}

/**
 * @param int $imageMapID
 * @return stdClass|null
 * @deprecated since 5.2.0
 */
function holeExtension(int $imageMapID)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'ImageMap', 'kInitial', $imageMapID);
}

/**
 * @param int $imageMapID
 * @return bool
 * @deprecated since 5.2.0
 */
function entferneBanner(int $imageMapID): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return string[]
 * @deprecated since 5.2.0
 */
function holeBannerDateien(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param mixed $data
 * @return IOResponse
 * @deprecated since 5.2.0
 */
function saveBannerAreasIO($data): IOResponse
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
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
