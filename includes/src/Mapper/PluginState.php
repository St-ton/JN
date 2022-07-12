<?php declare(strict_types=1);

namespace JTL\Mapper;

use JTL\Plugin\State;

/**
 * Class PluginState
 * @package JTL\Mapper
 */
class PluginState
{
    /**
     * @param int $state
     * @return string
     */
    public function map(int $state): string
    {
        return match ($state) {
            State::DISABLED                 => 'Deaktiviert',
            State::ACTIVATED                => 'Aktiviert',
            State::ERRONEOUS                => 'Fehlerhaft',
            State::UPDATE_FAILED            => 'Update fehlgeschlagen',
            State::LICENSE_KEY_MISSING      => 'Lizenzschlüssel fehlt',
            State::LICENSE_KEY_INVALID      => 'Lizenzschlüssel ungültig',
            State::EXS_LICENSE_EXPIRED      => 'Lizenz abgelaufen',
            State::EXS_SUBSCRIPTION_EXPIRED => 'Subscription abgelaufen',
            default                         => 'Unbekannt',
        };
    }
}
