<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;

/**
 * Class PluginState
 * @package Mapper
 */
class PluginState
{
    /**
     * @param int $state
     * @return string
     */
    public function map(int $state): string
    {
        switch ($state) {
            case \Plugin::PLUGIN_DISABLED:
                return 'Deaktiviert';
            case \Plugin::PLUGIN_ACTIVATED:
                return 'Aktiviert';
            case \Plugin::PLUGIN_ERRONEOUS:
                return 'Fehlerhaft';
            case \Plugin::PLUGIN_UPDATE_FAILED:
                return 'Update fehlgeschlagen';
            case \Plugin::PLUGIN_LICENSE_KEY_MISSING:
                return 'Lizenzschlüssel fehlt';
            case \Plugin::PLUGIN_LICENSE_KEY_INVALID:
                return 'Lizenzschlüssel ungültig';
            default:
                return 'Unbekannt';
        }
    }
}
