<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class Localization
 * @package Plugin\Admin\Validation\Items
 */
class Localization extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        if (!isset($node['Locales']) || !\is_array($node['Locales'])) {
            return InstallCode::OK;
        }
        if (!isset($node['Locales'][0]['Variable'])
            || !\is_array($node['Locales'][0]['Variable'])
            || \count($node['Locales'][0]['Variable']) === 0
        ) {
            return InstallCode::MISSING_LANG_VARS;
        }
        foreach ($node['Locales'][0]['Variable'] as $t => $var) {
            $t = (string)$t;
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($t)) {
                continue;
            }
            if (\mb_strlen($var['Name']) === 0) {
                return InstallCode::INVALID_LANG_VAR_NAME;
            }
            // Nur eine Sprache vorhanden
            if (isset($var['VariableLocalized attr'])
                && \is_array($var['VariableLocalized attr'])
                && \count($var['VariableLocalized attr']) > 0
            ) {
                if (!isset($var['VariableLocalized attr']['iso'])) {
                    return InstallCode::MISSING_LOCALIZED_LANG_VAR;
                }
                \preg_match('/[A-Z]{3}/', $var['VariableLocalized attr']['iso'], $hits);
                if (\mb_strlen($hits[0]) !== \mb_strlen($var['VariableLocalized attr']['iso'])) {
                    return InstallCode::INVALID_LANG_VAR_ISO;
                }
                if (\mb_strlen($var['VariableLocalized']) === 0) {
                    return InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME;
                }
            } elseif (isset($var['VariableLocalized'])
                && \is_array($var['VariableLocalized'])
                && \count($var['VariableLocalized']) > 0
            ) {
                // Mehr als eine Sprache vorhanden
                foreach ($var['VariableLocalized'] as $i => $localized) {
                    $i = (string)$i;
                    \preg_match('/[0-9]+\sattr/', $i, $hits1);
                    \preg_match('/[0-9]+/', $i, $hits2);
                    if (isset($hits1[0]) && \mb_strlen($hits1[0]) === \mb_strlen($i)) {
                        \preg_match('/[A-Z]{3}/', $localized['iso'], $hits);
                        $len = \mb_strlen($localized['iso']);
                        if ($len === 0 || \mb_strlen($hits[0]) !== $len) {
                            return InstallCode::INVALID_LANG_VAR_ISO;
                        }
                    } elseif (isset($hits2[0]) && \mb_strlen($hits2[0]) === \mb_strlen($i)) {
                        if (\mb_strlen($localized) === 0) {
                            return InstallCode::INVALID_LOCALIZED_LANG_VAR_NAME;
                        }
                    }
                }
            } else {
                return InstallCode::MISSING_LOCALIZED_LANG_VAR;
            }
        }

        return InstallCode::OK;
    }
}
