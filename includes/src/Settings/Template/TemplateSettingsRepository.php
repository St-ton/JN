<?php declare(strict_types=1);

namespace JTL\Settings\Template;

use JTL\Abstracts\AbstractRepository;
use JTL\Interfaces\SettingsRepositoryInterface;

/**
 * Class TemplateSettingsRepository
 * @package JTL\Settings
 */

class TemplateSettingsRepository extends AbstractRepository implements SettingsRepositoryInterface
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'ttemplateeinstellungen';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return '';
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->db->getObjects(
            'SELECT cSektion AS sec, cWert AS val, cName AS name FROM `' . $this->getTableName() . '`' .
            " WHERE cTemplate = (SELECT cTemplate FROM ttemplate WHERE eTyp = 'standard')"
        );
    }
}
