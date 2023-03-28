<?php declare(strict_types=1);

namespace JTL\Settings\Branding;

use JTL\Abstracts\AbstractRepository;
use JTL\Interfaces\SettingsRepositoryInterface;

/**
 * Class BrandingSettingsRepository
 * @package JTL\Settings
 */
class BrandingSettingsRepository extends AbstractRepository implements SettingsRepositoryInterface
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'tbrandingeinstellung';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'kBrandingEinstellung';
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->db->getObjects(
            'SELECT tbranding.kBranding AS id, tbranding.cBildKategorie AS type, 
            tbrandingeinstellung.cPosition AS position, tbrandingeinstellung.cBrandingBild AS path,
            tbrandingeinstellung.dTransparenz AS transparency, tbrandingeinstellung.dGroesse AS size
                FROM tbrandingeinstellung
                INNER JOIN tbranding 
                    ON tbrandingeinstellung.kBranding = tbranding.kBranding
                WHERE tbrandingeinstellung.nAktiv = 1'
        );
    }
}
