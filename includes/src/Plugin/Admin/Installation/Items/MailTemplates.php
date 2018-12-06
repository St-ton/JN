<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class MailTemplates
 * @package Plugin\Admin\Installation\Items
 */
class MailTemplates extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Emailtemplate'][0]['Template'])
        && \is_array($this->baseNode['Install'][0]['Emailtemplate'][0]['Template'])
            ? $this->baseNode['Install'][0]['Emailtemplate'][0]['Template']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $i => $template) {
            $i = (string)$i;
            \preg_match("/[0-9]+\sattr/", $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\strlen($hits2[0]) !== \strlen($i)) {
                continue;
            }
            $mailTpl                = new \stdClass();
            $mailTpl->kPlugin       = $this->plugin->kPlugin;
            $mailTpl->cName         = $template['Name'];
            $mailTpl->cBeschreibung = \is_array($template['Description'])
                ? $template['Description'][0]
                : $template['Description'];
            $mailTpl->cMailTyp      = $template['Type'] ?? 'text/html';
            $mailTpl->cModulId      = $template['ModulId'];
            $mailTpl->cDateiname    = $template['Filename'] ?? null;
            $mailTpl->cAktiv        = $template['Active'] ?? 'N';
            $mailTpl->nAKZ          = $template['AKZ'] ?? 0;
            $mailTpl->nAGB          = $template['AGB'] ?? 0;
            $mailTpl->nWRB          = $template['WRB'] ?? 0;
            $mailTpl->nWRBForm      = $template['WRBForm'] ?? 0;
            $mailTpl->nDSE          = $template['DSE'] ?? 0;
            $mailTplID              = $this->db->insert('tpluginemailvorlage', $mailTpl);
            if ($mailTplID <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_EMAIL_TEMPLATE;
            }
            $localizedTpl                = new \stdClass();
            $iso                         = '';
            $localizedTpl->kEmailvorlage = $mailTplID;
            // Hole alle Sprachen des Shops
            // Assoc cISO
            $allLanguages = \Sprache::getAllLanguages(2);
            // Ist das erste Standard Template gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $isDefault       = false;
            $defaultLanguage = new \stdClass();
            foreach ($template['TemplateLanguage'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    $iso = \strtolower($localized['iso']);
                } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($l)) {
                    $localizedTpl->kEmailvorlage = $mailTplID;
                    $localizedTpl->kSprache      = $allLanguages[$iso]->kSprache;
                    $localizedTpl->cBetreff      = $localized['Subject'];
                    $localizedTpl->cContentHtml  = $localized['ContentHtml'];
                    $localizedTpl->cContentText  = $localized['ContentText'];
                    $localizedTpl->cPDFS         = $localized['PDFS'] ?? null;
                    $localizedTpl->cDateiname    = $localized['Filename'] ?? null;
                    if (!isset($this->oldPlugin->kPlugin) || !$this->oldPlugin->kPlugin) {
                        $this->db->insert('tpluginemailvorlagesprache', $localizedTpl);
                    }
                    $this->db->insert('tpluginemailvorlagespracheoriginal', $localizedTpl);
                    // Erste Templatesprache vom Plugin als Standard setzen
                    if (!$isDefault) {
                        $defaultLanguage = $localizedTpl;
                        $isDefault       = true;
                    }
                    if (isset($allLanguages[$iso])) {
                        // Resette aktuelle Sprache
                        unset($allLanguages[$iso]);
                        $allLanguages = \array_merge($allLanguages);
                    }
                }
            }
            foreach ($allLanguages as $language) {
                if ($language->kSprache > 0) {
                    $defaultLanguage->kSprache = $language->kSprache;
                    if (!isset($this->oldPlugin->kPlugin) || !$this->oldPlugin->kPlugin) {
                        $this->db->insert('tpluginemailvorlagesprache', $defaultLanguage);
                    }
                    $this->db->insert('tpluginemailvorlagespracheoriginal', $defaultLanguage);
                }
            }
        }

        return InstallCode::OK;
    }
}
