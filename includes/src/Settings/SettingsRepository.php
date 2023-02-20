<?php

namespace JTL\Settings;

use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Per definition this repository should be used preferably to interact with table teinstellungen (and childs) only
 * Since there are neither existing repositories for ttemplateeinstellungen nor for tbrandingeinstellung
 * this data will - for now - be collected here.
 *
 * Suggestion is to provide subdirectories Settings/TemplateSettings and Settings/BrandingsSettings to be home
 * to these classes.
 */

class SettingsRepository
{
    //ToDo: Service vom Abstract Repository extenden sobald verfügbar
    /**
     * @param DbInterface|null $db
     */
    public function __construct(
        protected ?DbInterface $db = null,
    ) {
        if (\is_null($db)) {
            $this->db = Shop::Container()->getDB();
        }
    }

    /**
     * @return array
     */
    public function getAllSettings(): array
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

    /**
     * @return array
     */
    public function getTemplateConfig(): array
    {
        return $this->db->getObjects(
            "SELECT cSektion AS sec, cWert AS val, cName AS name 
                FROM ttemplateeinstellungen 
                WHERE cTemplate = (SELECT cTemplate FROM ttemplate WHERE eTyp = 'standard')"
        );
    }

    /**
     * @return array
     */
    public function getBrandingConfig(): array
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
