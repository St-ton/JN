<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class Exports
 * @package Plugin\Admin\Installation\Items
 */
class Exports extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): ?array
    {
        return isset($this->baseNode['Install'][0]['ExportFormat'][0]['Format'])
        && \is_array($this->baseNode['Install'][0]['ExportFormat'][0]['Format'])
            ? $this->baseNode['Install'][0]['ExportFormat'][0]['Format']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $defaultCustomerGroupID = \Kundengruppe::getDefaultGroupID();
        $language               = \Sprache::getDefaultLanguage(true);
        $defaultLanguageID      = $language->kSprache;
        $defaultCurrencyID      = \Session\Session::getCurrency()->getID();
        foreach ($this->getNode() as $u => $data) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $export                   = new \stdClass();
            $export->kKundengruppe    = $defaultCustomerGroupID;
            $export->kSprache         = $defaultLanguageID;
            $export->kWaehrung        = $defaultCurrencyID;
            $export->kKampagne        = 0;
            $export->kPlugin          = $this->plugin->kPlugin;
            $export->cName            = $data['Name'];
            $export->cDateiname       = $data['FileName'];
            $export->cKopfzeile       = $data['Header'];
            $export->cContent         = (isset($data['Content']) && \strlen($data['Content']) > 0)
                ? $data['Content']
                : 'PluginContentFile_' . $data['ContentFile'];
            $export->cFusszeile       = $data['Footer'] ?? null;
            $export->cKodierung       = $data['Encoding'] ?? 'ASCII';
            $export->nSpecial         = 0;
            $export->nVarKombiOption  = $data['VarCombiOption'] ?? 1;
            $export->nSplitgroesse    = $data['SplitSize'] ?? 0;
            $export->dZuletztErstellt = '_DBNULL_';
            if (\is_array($export->cKopfzeile)) {
                //@todo: when cKopfzeile is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                $export->cKopfzeile = $export->cKopfzeile[0];
            }
            if (\is_array($export->cContent)) {
                $export->cContent = $export->cContent[0];
            }
            if (\is_array($export->cFusszeile)) {
                $export->cFusszeile = $export->cFusszeile[0];
            }
            $exportID = $this->db->insert('texportformat', $export);
            if (!$exportID) {
                return InstallCode::SQL_CANNOT_SAVE_EXPORT;
            }
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_lager_ueber_null';
            $exportConf->cWert         = \strlen($data['OnlyStockGreaterZero']) !== 0
                ? $data['OnlyStockGreaterZero']
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_preis_ueber_null';
            $exportConf->cWert         = $data['OnlyPriceGreaterZero'] === 'Y' ? 'Y' : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_beschreibung';
            $exportConf->cWert         = $data['OnlyProductsWithDescription'] === 'Y' ? 'Y' : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_lieferland';
            $exportConf->cWert         = $data['ShippingCostsDeliveryCountry'];
            $this->db->insert('texportformateinstellungen', $exportConf);
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_quot';
            $exportConf->cWert         = $data['EncodingQuote'] === 'Y' ? 'Y' : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_equot';
            $exportConf->cWert         = $data['EncodingDoubleQuote'] === 'Y' ? 'Y' : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $exportID;
            $exportConf->cName         = 'exportformate_semikolon';
            $exportConf->cWert         = $data['EncodingSemicolon'] === 'Y' ? 'Y' : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
        }

        return InstallCode::OK;
    }
}
