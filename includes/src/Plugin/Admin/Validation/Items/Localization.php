<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\InstallCode;

/**
 * Class Localization
 * @package JTL\Plugin\Admin\Validation\Items
 */
final class Localization extends AbstractItem
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
        if (empty($node['Locales'][0]['Variable']) || !\is_array($node['Locales'][0]['Variable'])) {
            return InstallCode::MISSING_LANG_VARS;
        }

        return $this->validateVariables($node['Locales'][0]['Variable']);
    }

    /**
     * @param array $variables
     * @return int
     */
    private function validateVariables(array $variables): int
    {
        foreach ($variables as $t => $var) {
            $t = (string)$t;
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\mb_strlen($hits2[0]) !== \mb_strlen($t)) {
                continue;
            }
            if (!isset($var['Name']) || \mb_strlen($var['Name']) === 0) {
                return InstallCode::INVALID_LANG_VAR_NAME;
            }
            if (isset($var['VariableLocalized attr'])
                && \is_array($var['VariableLocalized attr'])
                && \count($var['VariableLocalized attr']) > 0
            ) {
                if (($res = $this->validateSingleLanguage($var)) !== InstallCode::OK) {
                    return $res;
                }
                continue;
            }
            if (!empty($var['VariableLocalized']) && \is_array($var['VariableLocalized'])) {
                if (($res = $this->validateMultiLanguage($var)) !== InstallCode::OK) {
                    return $res;
                }
                continue;
            }

            return InstallCode::MISSING_LOCALIZED_LANG_VAR;
        }

        return InstallCode::OK;
    }

    /**
     * @param array $var
     * @return int
     */
    private function validateSingleLanguage(array $var): int
    {
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

        return InstallCode::OK;
    }

    /**
     * @param array $var
     * @return int
     */
    private function validateMultiLanguage(array $var): int
    {
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

        return InstallCode::OK;
    }
}
