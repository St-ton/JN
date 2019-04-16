<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Plugin\InstallCode;
use JTL\Sprache;
use stdClass;

/**
 * Class MailTemplates
 * @package JTL\Plugin\Admin\Installation\Items
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
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($i)) {
                continue;
            }
            $mailTpl                = new stdClass();
            $mailTpl->kPlugin       = $this->plugin->kPlugin;
            $mailTpl->cName         = $template['Name'];
            $mailTpl->cBeschreibung = \is_array($template['Description'])
                ? $template['Description'][0]
                : $template['Description'];
            $mailTpl->cMailTyp      = $template['Type'] ?? 'text/html';
            $mailTpl->cModulId      = $template['ModulId'];
            $mailTpl->cDateiname    = $template['Filename'] ?? null;
            $mailTpl->cAktiv        = $template['Active'] ?? 'N';
            $mailTpl->nAKZ          = (int)($template['AKZ'] ?? 0);
            $mailTpl->nAGB          = (int)($template['AGB'] ?? 0);
            $mailTpl->nWRB          = (int)($template['WRB'] ?? 0);
            $mailTpl->nWRBForm      = (int)($template['WRBForm'] ?? 0);
            $mailTpl->nDSE          = (int)($template['DSE'] ?? 0);
            $mailTplID              = $this->db->insert('tpluginemailvorlage', $mailTpl);
            if ($mailTplID <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_EMAIL_TEMPLATE;
            }
            $localizedTpl                = new stdClass();
            $localizedTpl->kEmailvorlage = $mailTplID;
            $iso                         = '';
            $allLanguages                = Sprache::getAllLanguages(2);
            $fallbackLocalization        = null;
            $availableLocalizations      = [];
            $addedLanguages              = [];
            $first                       = true;
            foreach ($template['TemplateLanguage'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($l)) {
                    $iso = \mb_convert_case($localized['iso'], \MB_CASE_LOWER);
                } elseif (isset($hits2[0]) && \mb_strlen($hits2[0]) === \mb_strlen($l)) {
                    $localizedTpl->kEmailvorlage = $mailTplID;
                    $localizedTpl->kSprache      = $allLanguages[$iso]->kSprache ?? 0;
                    $localizedTpl->cBetreff      = $localized['Subject'];
                    $localizedTpl->cContentHtml  = $localized['ContentHtml'];
                    $localizedTpl->cContentText  = $localized['ContentText'];
                    $localizedTpl->cPDFS         = $localized['PDFS'] ?? null;
                    $localizedTpl->cPDFNames     = $localized['Filename'] ?? null;
                    $availableLocalizations[]    = $localizedTpl;
                    if ($fallbackLocalization === null) {
                        $fallbackLocalization = $localizedTpl;
                    }
                }
            }

            foreach ($availableLocalizations as $localizedTpl) {
                if ($localizedTpl->kSprache === 0) {
                    continue;
                }
                $addedLanguages[] = $localizedTpl->kSprache;
                if (!isset($this->oldPlugin->kPlugin) || !$this->oldPlugin->kPlugin) {
                    $this->db->insert('tpluginemailvorlagesprache', $localizedTpl);
                }
                $this->db->insert('tpluginemailvorlagespracheoriginal', $localizedTpl);
            }

            // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
            foreach ($allLanguages as $language) {
                if (\in_array($language->kSprache, $addedLanguages, true)) {
                    continue;
                }
                if ($first === true) {
                    $this->db->update(
                        'tpluginemailvorlage',
                        'kEmailvorlage',
                        $mailTplID,
                        (object)['nFehlerhaft' => 1, 'cAktiv' => 'N']
                    );
                    $first = false;
                }
                $fallbackLocalization->kSprache = $language->kSprache;
                if (!isset($this->oldPlugin->kPlugin) || !$this->oldPlugin->kPlugin) {
                    $this->db->insert('tpluginemailvorlagesprache', $fallbackLocalization);
                }
                $this->db->insert('tpluginemailvorlagespracheoriginal', $fallbackLocalization);
            }
        }

        return InstallCode::OK;
    }
}
