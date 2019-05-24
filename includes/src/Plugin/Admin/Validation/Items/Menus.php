<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin\Admin\Validation\Items;

use JTL\Plugin\Admin\InputType;
use JTL\Plugin\InstallCode;
use JTL\Shop;

/**
 * Class Menus
 * @package JTL\Plugin\Admin\Validation\Items
 */
class Menus extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['Adminmenu'][0])) {
            return InstallCode::OK;
        }
        $node = $node['Adminmenu'][0];
        if (isset($node['Customlink'])
            && \is_array($node['Customlink'])
            && \count($node['Customlink']) > 0
        ) {
            foreach ($node['Customlink'] as $i => $customLink) {
                $i = (string)$i;
                \preg_match('/[0-9]+\sattr/', $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                    \preg_match(
                        '/[a-zA-Z0-9äÄüÜöÖß' . '\_\- ]+/',
                        $customLink['Name'],
                        $hits
                    );
                    if (empty($customLink['Name']) || \mb_strlen($hits[0]) !== \mb_strlen($customLink['Name'])) {
                        return InstallCode::INVALID_CUSTOM_LINK_NAME;
                    }
                    if (empty($customLink['Filename'])) {
                        return InstallCode::INVALID_CUSTOM_LINK_FILE_NAME;
                    }
                    if (!\file_exists($dir . \PFAD_PLUGIN_ADMINMENU . $customLink['Filename'])) {
                        return InstallCode::MISSING_CUSTOM_LINK_FILE;
                    }
                }
            }
        }
        if (!isset($node['Settingslink']) || !\is_array($node['Settingslink'])) {
            return InstallCode::OK;
        }
        foreach ($node['Settingslink'] as $i => $settingsLink) {
            $i            = (string)$i;
            $settingsLink = $this->sanitizeSettingsLink($settingsLink);
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\mb_strlen($hits2[0]) === \mb_strlen($i)) {
                if (empty($settingsLink['Name'])) {
                    return InstallCode::INVALID_CONFIG_LINK_NAME;
                }
                $type = '';
                if (!isset($settingsLink['Setting'])
                    || !\is_array($settingsLink['Setting'])
                    || \count($settingsLink['Setting']) === 0
                ) {
                    return InstallCode::MISSING_CONFIG;
                }
                foreach ($settingsLink['Setting'] as $j => $setting) {
                    if (!\is_array($setting)) {
                        return InstallCode::MISSING_CONFIG;
                    }
                    $j       = (string)$j;
                    $setting = $this->sanitizeSetting($setting);
                    \preg_match('/[0-9]+\sattr/', $j, $hits3);
                    \preg_match('/[0-9]+/', $j, $hits4);

                    if (isset($hits3[0]) && \mb_strlen($hits3[0]) === \mb_strlen($j)) {
                        $type = $setting['type'];
                        if (\mb_strlen($setting['type']) === 0) {
                            return InstallCode::INVALID_CONFIG_TYPE;
                        }
                        if (\mb_strlen($setting['sort']) === 0) {
                            return InstallCode::INVALID_CONFIG_SORT_VALUE;
                        }
                        if (\mb_strlen($setting['conf']) === 0) {
                            return InstallCode::INVALID_CONF;
                        }
                    } elseif (\mb_strlen($hits4[0]) === \mb_strlen($j)) {
                        if (\mb_strlen($setting['Name']) === 0) {
                            return InstallCode::INVALID_CONFIG_NAME;
                        }
                        if (!\is_string($setting['ValueName']) || \mb_strlen($setting['ValueName']) === 0) {
                            return InstallCode::INVALID_CONF_VALUE_NAME;
                        }
                        if ($type === InputType::SELECT) {
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                                if (empty($setting['OptionsSource'][0]['File'])) {
                                    return InstallCode::INVALID_OPTIONS_SOURE_FILE;
                                }
                                if (!\file_exists($dir .
                                    \PFAD_PLUGIN_ADMINMENU .
                                    $setting['OptionsSource'][0]['File'])
                                ) {
                                    return InstallCode::MISSING_OPTIONS_SOURE_FILE;
                                }
                            } elseif (isset($setting['SelectboxOptions'])
                                && \is_array($setting['SelectboxOptions'])
                                && \count($setting['SelectboxOptions']) > 0
                            ) {
                                if (\count($setting['SelectboxOptions'][0]) === 1) {
                                    foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $option) {
                                        $y = (string)$y;
                                        \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                        \preg_match('/[0-9]+/', $y, $hits7);

                                        if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                            if (\mb_strlen($option['value']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                            if (\mb_strlen($option['sort']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        } elseif (\mb_strlen($hits7[0]) === \mb_strlen($y)) {
                                            if (\mb_strlen($option) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        }
                                    }
                                } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                                    if (\mb_strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                        return InstallCode::INVALID_CONFIG_OPTION;
                                    }
                                    if (\mb_strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                        return InstallCode::INVALID_CONFIG_OPTION;
                                    }
                                    if (\mb_strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                                        return InstallCode::INVALID_CONFIG_OPTION;
                                    }
                                }
                            } else {
                                return InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS;
                            }
                        } elseif ($type === InputType::RADIO) {
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (isset($setting['RadioOptions'])
                                && \is_array($setting['RadioOptions'])
                                && \count($setting['RadioOptions']) > 0
                            ) {
                                if (\count($setting['RadioOptions'][0]) === 1) {
                                    foreach ($setting['RadioOptions'][0]['Option'] as $y => $option) {
                                        $y = (string)$y;
                                        \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                        \preg_match('/[0-9]+/', $y, $hits7);
                                        if (isset($hits6[0]) && \mb_strlen($hits6[0]) === \mb_strlen($y)) {
                                            if (\mb_strlen($option['value']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                            if (\mb_strlen($option['sort']) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        } elseif (\mb_strlen($hits7[0]) === \mb_strlen($y)) {
                                            if (\mb_strlen($option) === 0) {
                                                return InstallCode::INVALID_CONFIG_OPTION;
                                            }
                                        }
                                    }
                                } elseif (\count($setting['RadioOptions'][0]) === 2) {
                                    if (\mb_strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                                        return InstallCode::INVALID_CONFIG_OPTION;
                                    }
                                    if (\mb_strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                        return InstallCode::INVALID_CONFIG_OPTION;
                                    }
                                    if (\mb_strlen($setting['RadioOptions'][0]['Option']) === 0) {
                                        return InstallCode::INVALID_CONFIG_OPTION;
                                    }
                                }
                            } else {
                                return InstallCode::MISSING_CONFIG_SELECTBOX_OPTIONS;
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $setting
     * @return array
     */
    private function sanitizeSetting(array $setting): array
    {
        $setting['Name']      = $setting['Name'] ?? '';
        $setting['ValueName'] = $setting['ValueName'] ?? '';
        $setting['type']      = $setting['type'] ?? '';

        return $setting;
    }

    /**
     * @param array $settingsLink
     * @return array
     */
    private function sanitizeSettingsLink(array $settingsLink): array
    {
        return $settingsLink;
    }
}
