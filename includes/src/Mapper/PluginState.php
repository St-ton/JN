<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Mapper;

use Plugin\State;

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
            case State::DISABLED:
                return 'Deaktiviert';
            case State::ACTIVATED:
                return 'Aktiviert';
            case State::ERRONEOUS:
                return 'Fehlerhaft';
            case State::UPDATE_FAILED:
                return 'Update fehlgeschlagen';
            case State::LICENSE_KEY_MISSING:
                return 'Lizenzschlüssel fehlt';
            case State::LICENSE_KEY_INVALID:
                return 'Lizenzschlüssel ungültig';
            default:
                return 'Unbekannt';
        }
    }
}
