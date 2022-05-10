<?php declare(strict_types=1);

use JTL\Shop;

/**
 * @param int $sliderID
 * @return stdClass|null
 */
function holeExtension(int $sliderID): ?stdClass
{
    $data = Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'slider', 'kInitial', $sliderID);
    if ($data !== null) {
        $data->kExtensionPoint = (int)$data->kExtensionPoint;
        $data->kSprache        = (int)$data->kSprache;
        $data->kKundengruppe   = (int)$data->kKundengruppe;
        $data->nSeite          = (int)$data->nSeite;
        $data->kInitial        = (int)$data->kInitial;
    }

    return $data;
}
