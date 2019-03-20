<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;

/**
 * @param int $kSlider
 * @return mixed
 */
function holeExtension(int $kSlider)
{
    return Shop::Container()->getDB()->select('textensionpoint', 'cClass', 'Slider', 'kInitial', $kSlider);
}
