<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kSlider
 * @return mixed
 */
function holeExtension($kSlider)
{
    return Shop::DB()->query("SELECT * FROM textensionpoint WHERE cClass = 'Slider' AND kInitial = " . intval($kSlider) . " LIMIT 1", 1);
}
