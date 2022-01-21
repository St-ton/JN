<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

use stdClass;

/**
 * Class Headline
 * @package Backend\Settings
 */
class Headline extends Item
{
    public $cSektionsPfad = '';

    public $cURL = '';

    public $settingsAnchor = '';

    public $specialSetting = false;

    public $oEinstellung_arr = [];

    public function parseFromDB(stdClass $dbItem): void
    {
        parent::parseFromDB($dbItem);
        $this->cSektionsPfad  = $dbItem->cSektionsPfad ?? '';
        $this->cURL           = $dbItem->cURL ?? '';
        $this->settingsAnchor = $dbItem->settingsAnchor ?? '';
        $this->specialSetting = $dbItem->specialSetting ?? false;
    }
}
