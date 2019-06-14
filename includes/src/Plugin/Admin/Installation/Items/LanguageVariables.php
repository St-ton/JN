<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\Language\LanguageHelper;
use JTL\Plugin\InstallCode;
use stdClass;

/**
 * Class LanguageVariables
 * @package JTL\Plugin\Admin\Installation\Items
 */
class LanguageVariables extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return $this->baseNode['Install'][0]['Locales'][0]['Variable'] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $languages = LanguageHelper::getAllLanguages(2);
        foreach ($this->getNode() as $t => $langVar) {
            $t = (string)$t;
            \preg_match('/[0-9]+/', $t, $hits1);
            if (\mb_strlen($hits1[0]) !== \mb_strlen($t)) {
                continue;
            }
            $pluginLangVar          = new stdClass();
            $pluginLangVar->kPlugin = $this->plugin->kPlugin;
            $pluginLangVar->cName   = $langVar['Name'];
            if (isset($langVar['Description']) && \is_array($langVar['Description'])) {
                $pluginLangVar->cBeschreibung = '';
            } else {
                $pluginLangVar->cBeschreibung = \preg_replace('/\s+/', ' ', $langVar['Description']);
            }
            $id = $this->db->insert('tpluginsprachvariable', $pluginLangVar);
            if ($id <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_LANG_VAR;
            }
            // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bVariableStandard   = false;
            $oVariableSpracheStd = new stdClass();
            // Nur eine Sprache vorhanden
            if (isset($langVar['VariableLocalized attr'])
                && \is_array($langVar['VariableLocalized attr'])
                && \count($langVar['VariableLocalized attr']) > 0
            ) {
                // tpluginsprachvariablesprache füllen
                $localized                        = new stdClass();
                $localized->kPluginSprachvariable = $id;
                $localized->cISO                  = $langVar['VariableLocalized attr']['iso'];
                $localized->cName                 = \preg_replace('/\s+/', ' ', $langVar['VariableLocalized']);

                $this->db->insert('tpluginsprachvariablesprache', $localized);

                // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                if (!$bVariableStandard) {
                    $oVariableSpracheStd = $localized;
                    $bVariableStandard   = true;
                }

                if (isset($languages[\mb_convert_case($localized->cISO, \MB_CASE_LOWER)])) {
                    // Resette aktuelle Sprache
                    unset($languages[\mb_convert_case($localized->cISO, \MB_CASE_LOWER)]);
                    $languages = \array_merge($languages);
                }
            } elseif (isset($langVar['VariableLocalized'])
                && \is_array($langVar['VariableLocalized'])
                && \count($langVar['VariableLocalized']) > 0
            ) {
                foreach ($langVar['VariableLocalized'] as $i => $loc) {
                    $i = (string)$i;
                    \preg_match('/[0-9]+\sattr/', $i, $hits1);

                    if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($i)) {
                        $cISO                             = $loc['iso'];
                        $yx                               = \mb_substr($i, 0, \mb_strpos($i, ' '));
                        $cName                            = $langVar['VariableLocalized'][$yx];
                        $localized                        = new stdClass();
                        $localized->kPluginSprachvariable = $id;
                        $localized->cISO                  = $cISO;
                        $localized->cName                 = \preg_replace('/\s+/', ' ', $cName);

                        $this->db->insert('tpluginsprachvariablesprache', $localized);
                        // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                        if (!$bVariableStandard) {
                            $oVariableSpracheStd = $localized;
                            $bVariableStandard   = true;
                        }

                        if (isset($languages[\mb_convert_case($localized->cISO, \MB_CASE_LOWER)])) {
                            unset($languages[\mb_convert_case($localized->cISO, \MB_CASE_LOWER)]);
                            $languages = \array_merge($languages);
                        }
                    }
                }
            }
            foreach ($languages as $oSprachAssoc) {
                $oVariableSpracheStd->cISO = \mb_convert_case($oSprachAssoc->cISO, \MB_CASE_UPPER);
                if (!$this->db->insert('tpluginsprachvariablesprache', $oVariableSpracheStd)) {
                    return InstallCode::SQL_CANNOT_SAVE_LANG_VAR_LOCALIZATION;
                }
            }
        }

        return InstallCode::OK;
    }
}
