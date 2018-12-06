<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\Admin\InputType;
use Plugin\ExtensionData\Config;
use Plugin\InstallCode;

/**
 * Class SettingsLinks
 * @package Plugin\Admin\Installation\Items
 */
class SettingsLinks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['Adminmenu'])
        && \is_array($this->baseNode['Install'][0]['Adminmenu'])
            ? $this->baseNode['Install'][0]['Adminmenu']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        $node     = $this->getNode();
        $pluginID = $this->plugin->kPlugin;
        if (!isset($node[0]['Settingslink'])
            || !\is_array($node[0]['Settingslink'])
            || \count($node[0]['Settingslink']) === 0
        ) {
            return InstallCode::OK;
        }
        $sort = 0;
        foreach ($node[0]['Settingslink'] as $i => $settingsLinks) {
            $i = (string)$i;
            \preg_match("/[0-9]+\sattr/", $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                $sort = (int)$settingsLinks['sort'];
            } elseif (\strlen($hits2[0]) === \strlen($i)) {
                $menuItem             = new \stdClass();
                $menuItem->kPlugin    = $pluginID;
                $menuItem->cName      = $settingsLinks['Name'];
                $menuItem->cDateiname = '';
                $menuItem->nSort      = $sort;
                $menuItem->nConf      = 1;

                $menuID = $this->db->insert('tpluginadminmenu', $menuItem);
                if ($menuID <= 0) {
                    return InstallCode::SQL_CANNOT_SAVE_SETTINGS_ITEM;
                }
                $type         = '';
                $initialValue = '';
                $sort         = 0;
                $cConf        = 'Y';
                $multiple     = false;
                foreach ($settingsLinks['Setting'] as $j => $setting) {
                    $j = (string)$j;
                    \preg_match("/[0-9]+\sattr/", $j, $hits3);
                    \preg_match('/[0-9]+/', $j, $hits4);
                    if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                        $type         = $setting['type'];
                        $multiple     = (isset($setting['multiple'])
                            && $setting['multiple'] === 'Y'
                            && $type === InputType::SELECT);
                        $initialValue = ($multiple === true) ?
                            \serialize([$setting['initialValue']])
                            : $setting['initialValue'];
                        $sort         = $setting['sort'];
                        $cConf        = $setting['conf'];
                    } elseif (\strlen($hits4[0]) === \strlen($j)) {
                        $plgnConf          = new \stdClass();
                        $plgnConf->kPlugin = $pluginID;
                        $plgnConf->cName   = \is_array($setting['ValueName'])
                            ? $setting['ValueName']['0']
                            : $setting['ValueName'];
                        $plgnConf->cWert   = $initialValue;
                        $exists            = $this->db->select(
                            'tplugineinstellungen',
                            ['cName', 'kPlugin'],
                            [$plgnConf->cName, $plgnConf->kPlugin]
                        );

                        if ($exists !== null) {
                            $this->db->update(
                                'tplugineinstellungen',
                                ['cName', 'kPlugin'],
                                [$plgnConf->cName, $plgnConf->kPlugin],
                                $plgnConf
                            );
                        } else {
                            $this->db->insert('tplugineinstellungen', $plgnConf);
                        }
                        $plgnConf                   = new \stdClass();
                        $plgnConf->kPlugin          = $pluginID;
                        $plgnConf->kPluginAdminMenu = $menuID;
                        $plgnConf->cName            = $setting['Name'];
                        $plgnConf->cBeschreibung    = (!isset($setting['Description'])
                            || \is_array($setting['Description']))
                            ? ''
                            : $setting['Description'];
                        $plgnConf->cWertName        = \is_array($setting['ValueName'])
                            ? $setting['ValueName']['0']
                            : $setting['ValueName'];
                        $plgnConf->cInputTyp        = $type;
                        $plgnConf->nSort            = $sort;
                        $plgnConf->cConf            = $cConf;
                        //dynamic data source for selectbox/radio
                        if ($type === InputType::SELECT || $type === InputType::RADIO) {
                            if (isset($setting['OptionsSource'][0]['File'])) {
                                $plgnConf->cSourceFile = $setting['OptionsSource'][0]['File'];
                            }
                            if ($multiple === true) {
                                $plgnConf->cConf = Config::TYPE_DYNAMIC;
                            }
                        }
                        $plgnConfTmpID = $this->db->select(
                            'tplugineinstellungenconf',
                            ['kPlugin', 'cWertName'],
                            [$plgnConf->kPlugin, $plgnConf->cWertName]
                        );
                        if ($plgnConfTmpID !== null) {
                            $this->db->update(
                                'tplugineinstellungenconf',
                                ['kPlugin', 'cWertName'],
                                [$plgnConf->kPlugin, $plgnConf->cWertName],
                                $plgnConf
                            );
                            $confID = $plgnConfTmpID->kPluginEinstellungenConf;
                        } else {
                            $confID = $this->db->insert(
                                'tplugineinstellungenconf',
                                $plgnConf
                            );
                        }
                        if ($confID <= 0) {
                            return InstallCode::SQL_CANNOT_SAVE_SETTING;
                        }
                        $sort = 0;
                        // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                        if ($type === InputType::SELECT) {
                            $optNode = $setting['SelectboxOptions'][0] ?? [];
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (\count($optNode) === 1) { // Es gibt mehr als eine Option
                                foreach ($optNode['Option'] as $y => $option) {
                                    $y = (string)$y;
                                    \preg_match("/[0-9]+\sattr/", $y, $hits6);
                                    if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                        $cWert = $option['value'];
                                        $sort  = $option['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $optNode['Option'][$yx];

                                        $plgnConfValues                           = new \stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $confID;
                                        $plgnConfValues->cName                    = $cName;
                                        $plgnConfValues->cWert                    = $cWert;
                                        $plgnConfValues->nSort                    = $sort;

                                        $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                                    }
                                }
                            } elseif (\count($optNode) === 2) { // Es gibt nur eine Option
                                $plgnConfValues                           = new \stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $confID;
                                $plgnConfValues->cName                    = $optNode['Option'];
                                $plgnConfValues->cWert                    = $optNode['Option attr']['value'];
                                $plgnConfValues->nSort                    = $optNode['Option attr']['sort'];
                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        } elseif ($type === InputType::RADIO) {
                            $optNode = $setting['RadioOptions'][0] ?? [];
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                            } elseif (\count($optNode) === 1) { // Es gibt mehr als eine Option
                                foreach ($optNode['Option'] as $y => $option) {
                                    \preg_match("/[0-9]+\sattr/", $y, $hits6);
                                    if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                        $cWert = $option['value'];
                                        $sort  = $option['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $optNode['Option'][$yx];

                                        $plgnConfValues                           = new \stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $confID;
                                        $plgnConfValues->cName                    = $cName;
                                        $plgnConfValues->cWert                    = $cWert;
                                        $plgnConfValues->nSort                    = $sort;

                                        $this->db->insert(
                                            'tplugineinstellungenconfwerte',
                                            $plgnConfValues
                                        );
                                    }
                                }
                            } elseif (\count($optNode) === 2) { // Es gibt nur eine Option
                                $plgnConfValues                           = new \stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $confID;
                                $plgnConfValues->cName                    = $optNode['Option'];
                                $plgnConfValues->cWert                    = $optNode['Option attr']['value'];
                                $plgnConfValues->nSort                    = $optNode['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}
