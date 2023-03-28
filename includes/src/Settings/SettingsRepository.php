<?php declare(strict_types=1);

namespace JTL\Settings;

use JTL\Abstracts\AbstractRepository;
use JTL\Interfaces\SettingsRepositoryInterface;

/**
 * Class SettingsRepository
 * @package JTL\Settings
 */
class SettingsRepository extends AbstractRepository implements SettingsRepositoryInterface
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'teinstellungenconf';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'kEinstellungenConf';
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->db->getArrays(
            'SELECT teinstellungen.kEinstellungenSektion, teinstellungen.cName, teinstellungen.cWert,
                teinstellungenconf.cInputTyp AS type
                FROM teinstellungen
                LEFT JOIN teinstellungenconf
                    ON teinstellungenconf.cWertName = teinstellungen.cName
                    AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
                ORDER BY kEinstellungenSektion'
        );
    }
}
