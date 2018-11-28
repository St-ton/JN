<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;

use DB\DbInterface;
use Plugin\InstallCode;
use Plugin\Plugin;

/**
 * Class TableCreator
 * @package Plugin\Admin
 */
class TableCreator
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var Plugin|null
     */
    private $plugin;

    /**
     * @var Plugin|null
     */
    private $oldPlugin;

    /**
     * TableCreator constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return Plugin|null
     */
    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }

    /**
     * @param Plugin|null $plugin
     */
    public function setPlugin($plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return Plugin|null
     */
    public function getOldPlugin(): ?Plugin
    {
        return $this->oldPlugin;
    }

    /**
     * @param Plugin|null $oldPlugin
     */
    public function setOldPlugin($oldPlugin): void
    {
        $this->oldPlugin = $oldPlugin;
    }

    /**
     * Installiert die tplugin* Tabellen für ein Plugin in der Datenbank
     *
     * @param array  $xml
     * @param object $plugin
     * @return int
     */
    public function installPluginTables($xml, $plugin): int
    {
        $this->plugin   = $plugin;
        $hooksNode      = isset($xml['jtlshop3plugin'][0]['Install'][0]['Hooks'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['Hooks'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['Hooks']
            : null;
        $uninstallNode  = !empty($xml['jtlshop3plugin'][0]['Uninstall'])
            ? $xml['jtlshop3plugin'][0]['Uninstall']
            : null;
        $adminNode      = isset($xml['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['Adminmenu']
            : null;
        $frontendNode   = isset($xml['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link']
            : [];
        $paymentNode    = isset($xml['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'])
        && \count($xml['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) > 0
            ? $xml['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']
            : [];
        $boxesNode      = isset($xml['jtlshop3plugin'][0]['Install'][0]['Boxes'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['Boxes'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box']
            : [];
        $checkboxesNode = isset($xml['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'])
        && \count($xml['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) > 0
            ? $xml['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']
            : [];
        $templatesNode  = isset($xml['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
            ? (array)$xml['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'][0]['Template']
            : [];
        $mailNode       = isset($xml['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template']
            : [];
        $localeNode     = $xml['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable'] ?? [];
        $widgetsNode    = isset($xml['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']
            : [];
        $portletsNode   = isset($xml['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet']
            : [];
        $blueprintsNode = isset($xml['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint']
            : [];
        $exportNode     = isset($xml['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']
            : [];
        $cssNode        = isset($xml['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file']
            : [];
        $jsNode         = isset($xml['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])
        && \is_array($xml['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])
            ? $xml['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file']
            : [];
        if ($hooksNode !== null && ($res = $this->installHooks($hooksNode)) !== InstallCode::OK) {
            return $res;
        }
        if ($uninstallNode !== null && ($res = $this->installUninstall($uninstallNode)) !== InstallCode::OK) {
            return $res;
        }
        if ($adminNode !== null && ($res = $this->installAdminMenu($adminNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installFrontendLinks($frontendNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installPaymentMethods($paymentNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installBoxes($boxesNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installTemplates($templatesNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installMailTemplates($mailNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installLangVars($localeNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installCheckboxes($checkboxesNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installAdminWidgets($widgetsNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installPortlets($portletsNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installBlueprints($blueprintsNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installExports($exportNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installCSS($cssNode)) !== InstallCode::OK) {
            return $res;
        }
        if (($res = $this->installJS($jsNode)) !== InstallCode::OK) {
            return $res;
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installHooks(array $node): int
    {
        $count     = \count($node[0]);
        $nHookID   = 0;
        $nPriority = 5;
        $hooks     = [];
        if ($count === 1) {
            foreach ($node[0]['Hook'] as $i => $hook) {
                \preg_match("/[0-9]+\sattr/", $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                    $nHookID   = (int)$hook['id'];
                    $nPriority = isset($hook['priority']) ? (int)$hook['priority'] : 5;
                } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($i)) {
                    $plugin             = new \stdClass();
                    $plugin->kPlugin    = $this->plugin->kPlugin;
                    $plugin->nHook      = $nHookID;
                    $plugin->nPriority  = $nPriority;
                    $plugin->cDateiname = $hook;

                    $hooks[] = $plugin;
                }
            }
        } elseif ($count > 1) {
            $hook               = $node[0];
            $plugin             = new \stdClass();
            $plugin->kPlugin    = $this->plugin->kPlugin;
            $plugin->nHook      = (int)$hook['Hook attr']['id'];
            $plugin->nPriority  = isset($hook['Hook attr']['priority'])
                ? (int)$hook['Hook attr']['priority']
                : $nPriority;
            $plugin->cDateiname = $hook['Hook'];

            $hooks[] = $plugin;
        }

        foreach ($hooks as $hook) {
            if (!$this->db->insert('tpluginhook', $hook)) {
                return InstallCode::SQL_CANNOT_SAVE_HOOK;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param string $node
     * @return int
     */
    private function installUninstall($node): int
    {
        $uninstall             = new \stdClass();
        $uninstall->kPlugin    = $this->plugin->kPlugin;
        $uninstall->cDateiname = $node;
        if (!$this->db->insert('tpluginuninstall', $uninstall)) {
            return InstallCode::SQL_CANNOT_SAVE_UNINSTALL;
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installAdminMenu(array $node): int
    {
        if (isset($node[0]['Customlink'])
            && \is_array($node[0]['Customlink'])
            && \count($node[0]['Customlink']) > 0
        ) {
            $sort = 0;
            foreach ($node[0]['Customlink'] as $i => $customLink) {
                \preg_match("/[0-9]+\sattr/", $i, $hits1);
                \preg_match('/[0-9]+/', $i, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                    $sort = (int)$customLink['sort'];
                } elseif (\strlen($hits2[0]) === \strlen($i)) {
                    $menuItem             = new \stdClass();
                    $menuItem->kPlugin    = $this->plugin->kPlugin;
                    $menuItem->cName      = $customLink['Name'];
                    $menuItem->cDateiname = $customLink['Filename'];
                    $menuItem->nSort      = $sort;
                    $menuItem->nConf      = 0;
                    if (!$this->db->insert('tpluginadminmenu', $menuItem)) {
                        return InstallCode::SQL_CANNOT_SAVE_ADMIN_MENU_ITEM;
                    }
                }
            }
        }

        return $this->installSettingsLinks($node);
    }

    /**
     * @param array $node
     * @return int
     */
    private function installSettingsLinks(array $node): int
    {
        if (!isset($node[0]['Settingslink'])
            || !\is_array($node[0]['Settingslink'])
            || \count($node[0]['Settingslink']) === 0
        ) {
            return InstallCode::OK;
        }
        $sort = 0;
        foreach ($node[0]['Settingslink'] as $i => $settingsLinks) {
            \preg_match("/[0-9]+\sattr/", $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                $sort = (int)$settingsLinks['sort'];
            } elseif (\strlen($hits2[0]) === \strlen($i)) {
                // tpluginadminmenu füllen
                $menuItem             = new \stdClass();
                $menuItem->kPlugin    = $this->plugin->kPlugin;
                $menuItem->cName      = $settingsLinks['Name'];
                $menuItem->cDateiname = '';
                $menuItem->nSort      = $sort;
                $menuItem->nConf      = 1;

                $kPluginAdminMenu = $this->db->insert('tpluginadminmenu', $menuItem);

                if ($kPluginAdminMenu <= 0) {
                    return InstallCode::SQL_CANNOT_SAVE_SETTINGS_ITEM;
                }
                $type         = '';
                $initialValue = '';
                $sort         = 0;
                $cConf        = 'Y';
                $multiple     = false;
                foreach ($settingsLinks['Setting'] as $j => $setting) {
                    \preg_match("/[0-9]+\sattr/", $j, $hits3);
                    \preg_match('/[0-9]+/', $j, $hits4);

                    if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                        $type         = $setting['type'];
                        $multiple     = (isset($setting['multiple'])
                            && $setting['multiple'] === 'Y'
                            && $type === 'selectbox');
                        $initialValue = ($multiple === true) ?
                            \serialize([$setting['initialValue']])
                            : $setting['initialValue'];
                        $sort         = $setting['sort'];
                        $cConf        = $setting['conf'];
                    } elseif (\strlen($hits4[0]) === \strlen($j)) {
                        // tplugineinstellungen füllen
                        $plgnConf          = new \stdClass();
                        $plgnConf->kPlugin = $this->plugin->kPlugin;
                        $plgnConf->cName   = \is_array($setting['ValueName'])
                            ? $setting['ValueName']['0']
                            : $setting['ValueName'];
                        $plgnConf->cWert   = $initialValue;
                        $exists            = $this->db->select(
                            'tplugineinstellungen',
                            'cName',
                            $plgnConf->cName
                        );

                        if ($exists !== null) {
                            $this->db->update(
                                'tplugineinstellungen',
                                'cName',
                                $plgnConf->cName,
                                $plgnConf
                            );
                        } else {
                            $this->db->insert('tplugineinstellungen', $plgnConf);
                        }
                        // tplugineinstellungenconf füllen
                        $plgnConf                   = new \stdClass();
                        $plgnConf->kPlugin          = $this->plugin->kPlugin;
                        $plgnConf->kPluginAdminMenu = $kPluginAdminMenu;
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
                        if ($type === 'selectbox' || $type === 'radio') {
                            if (isset($setting['OptionsSource'][0]['File'])) {
                                $plgnConf->cSourceFile = $setting['OptionsSource'][0]['File'];
                            }
                            if ($multiple === true) {
                                $plgnConf->cConf = 'M';
                            }
                        }
                        $plgnConfTmpID = $this->db->select(
                            'tplugineinstellungenconf',
                            'cWertName',
                            $plgnConf->cWertName
                        );
                        if ($plgnConfTmpID !== null) {
                            $this->db->update(
                                'tplugineinstellungenconf',
                                'cWertName',
                                $plgnConf->cWertName,
                                $plgnConf
                            );
                            $confID = $plgnConfTmpID->kPluginEinstellungenConf;
                        } else {
                            $confID = $this->db->insert(
                                'tplugineinstellungenconf',
                                $plgnConf
                            );
                        }
                        // tplugineinstellungenconfwerte füllen
                        if ($confID <= 0) {
                            return InstallCode::SQL_CANNOT_SAVE_SETTING;
                        }
                        $sort = 0;
                        // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                        if ($type === 'selectbox') {
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (\count($setting['SelectboxOptions'][0]) === 1) {
                                // Es gibt mehr als 1 Option
                                foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                    \preg_match("/[0-9]+\sattr/", $y, $hits6);

                                    if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                        $cWert = $Option_arr['value'];
                                        $sort  = $Option_arr['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $setting['SelectboxOptions'][0]['Option'][$yx];

                                        $plgnConfValues                           = new \stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $confID;
                                        $plgnConfValues->cName                    = $cName;
                                        $plgnConfValues->cWert                    = $cWert;
                                        $plgnConfValues->nSort                    = $sort;

                                        $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                                    }
                                }
                            } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                                // Es gibt nur eine Option
                                $plgnConfValues                           = new \stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $confID;
                                $plgnConfValues->cName                    =
                                    $setting['SelectboxOptions'][0]['Option'];
                                $plgnConfValues->cWert                    =
                                    $setting['SelectboxOptions'][0]['Option attr']['value'];
                                $plgnConfValues->nSort                    =
                                    $setting['SelectboxOptions'][0]['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        } elseif ($type === 'radio') {
                            if (isset($setting['OptionsSource'])
                                && \is_array($setting['OptionsSource'])
                                && \count($setting['OptionsSource']) > 0
                            ) {
                            } elseif (\count($setting['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                foreach ($setting['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                    \preg_match("/[0-9]+\sattr/", $y, $hits6);
                                    if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                        $cWert = $Option_arr['value'];
                                        $sort  = $Option_arr['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $setting['RadioOptions'][0]['Option'][$yx];

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
                            } elseif (\count($setting['RadioOptions'][0]) === 2) {
                                // Es gibt nur eine Option
                                $plgnConfValues                           = new \stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $confID;
                                $plgnConfValues->cName                    = $setting['RadioOptions'][0]['Option'];
                                $plgnConfValues->cWert                    =
                                    $setting['RadioOptions'][0]['Option attr']['value'];
                                $plgnConfValues->nSort                    =
                                    $setting['RadioOptions'][0]['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installFrontendLinks(array $node): int
    {
        foreach ($node as $u => $links) {
            \preg_match("/[0-9]+\sattr/", $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (empty($links['LinkGroup'])) {
                // linkgroup not set? default to 'hidden'
                $links['LinkGroup'] = 'hidden';
            }
            $linkGroup = $this->db->select('tlinkgruppe', 'cName', $links['LinkGroup']);
            if ($linkGroup === null) {
                $linkGroup                = new \stdClass();
                $linkGroup->cName         = $links['LinkGroup'];
                $linkGroup->cTemplatename = $links['LinkGroup'];
                $linkGroup->kLinkgruppe   = $this->db->insert('tlinkgruppe', $linkGroup);
            }
            if (!isset($linkGroup->kLinkgruppe) || $linkGroup->kLinkgruppe <= 0) {
                return InstallCode::SQL_CANNOT_FIND_LINK_GROUP;
            }
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $link                     = new \stdClass();
            $kLinkOld                 = empty($this->oldPlugin->kPlugin)
                ? null
                : $this->db->select('tlink', 'kPlugin', $this->oldPlugin->kPlugin, 'cName', $links['Name']);
            $link->kPlugin            = $this->plugin->kPlugin;
            $link->cName              = $links['Name'];
            $link->nLinkart           = \LINKTYP_PLUGIN;
            $link->cSichtbarNachLogin = $links['VisibleAfterLogin'];
            $link->cDruckButton       = $links['PrintButton'];
            $link->cNoFollow          = $links['NoFollow'] ?? null;
            $link->nSort              = \LINKTYP_PLUGIN;
            $link->bSSL               = isset($links['SSL'])
                ? (int)$links['SSL']
                : 0;
            $kLink                    = $this->db->insert('tlink', $link);

            if ($kLink <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_LINK;
            }
            $linkGroupAssociation              = new \stdClass();
            $linkGroupAssociation->linkGroupID = $linkGroup->kLinkgruppe;
            $linkGroupAssociation->linkID      = $kLink;
            $this->db->insert('tlinkgroupassociations', $linkGroupAssociation);

            $linkLang        = new \stdClass();
            $linkLang->kLink = $kLink;
            // Hole alle Sprachen des Shops
            // Assoc cISO
            $allLanguages = \Sprache::getAllLanguages(2);
            // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bLinkStandard = false;
            $defaultLang   = new \stdClass();
            foreach ($links['LinkLanguage'] as $l => $localized) {
                \preg_match("/[0-9]+\sattr/", $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    $linkLang->cISOSprache = \strtolower($localized['iso']);
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    // tlinksprache füllen
                    $linkLang->cSeo             = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($localized['Seo']));
                    $linkLang->cName            = $localized['Name'];
                    $linkLang->cTitle           = $localized['Title'];
                    $linkLang->cContent         = '';
                    $linkLang->cMetaTitle       = $localized['MetaTitle'];
                    $linkLang->cMetaKeywords    = $localized['MetaKeywords'];
                    $linkLang->cMetaDescription = $localized['MetaDescription'];

                    $this->db->insert('tlinksprache', $linkLang);
                    // Erste Linksprache vom Plugin als Standard setzen
                    if (!$bLinkStandard) {
                        $defaultLang   = $linkLang;
                        $bLinkStandard = true;
                    }

                    if ($allLanguages[$linkLang->cISOSprache]->kSprache > 0) {
                        $or = isset($kLinkOld->kLink) ? (' OR kKey = ' . (int)$kLinkOld->kLink) : '';
                        $this->db->query(
                            "DELETE FROM tseo
                                WHERE cKey = 'kLink'
                                    AND (kKey = " . (int)$kLink . $or . ")
                                    AND kSprache = " . (int)$allLanguages[$linkLang->cISOSprache]->kSprache,
                            \DB\ReturnType::DEFAULT
                        );
                        // tseo füllen
                        $oSeo           = new \stdClass();
                        $oSeo->cSeo     = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($localized['Seo']));
                        $oSeo->cKey     = 'kLink';
                        $oSeo->kKey     = $kLink;
                        $oSeo->kSprache = $allLanguages[$linkLang->cISOSprache]->kSprache;
                        $this->db->insert('tseo', $oSeo);
                    }

                    if (isset($allLanguages[$linkLang->cISOSprache])) {
                        unset($allLanguages[$linkLang->cISOSprache]);
                        $allLanguages = \array_merge($allLanguages);
                    }
                }
            }
            // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
            foreach ($allLanguages as $oSprachAssoc) {
                if ($oSprachAssoc->kSprache <= 0) {
                    continue;
                }
                $this->db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $kLink, (int)$oSprachAssoc->kSprache]
                );
                $oSeo           = new \stdClass();
                $oSeo->cSeo     = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($defaultLang->cSeo));
                $oSeo->cKey     = 'kLink';
                $oSeo->kKey     = $kLink;
                $oSeo->kSprache = $oSprachAssoc->kSprache;

                $this->db->insert('tseo', $oSeo);
                $defaultLang->cSeo        = $oSeo->cSeo;
                $defaultLang->cISOSprache = $oSprachAssoc->cISO;
                $this->db->insert('tlinksprache', $defaultLang);
            }
            $oPluginHook             = new \stdClass();
            $oPluginHook->kPlugin    = $this->plugin->kPlugin;
            $oPluginHook->nHook      = \HOOK_SEITE_PAGE_IF_LINKART;
            $oPluginHook->cDateiname = \PLUGIN_SEITENHANDLER;

            if (!$this->db->insert('tpluginhook', $oPluginHook)) {
                return InstallCode::SQL_CANNOT_SAVE_HOOK;
            }
            $linkFile                      = new \stdClass();
            $linkFile->kPlugin             = $this->plugin->kPlugin;
            $linkFile->kLink               = $kLink;
            $linkFile->cDatei              = $links['Filename'] ?? null;
            $linkFile->cTemplate           = $links['Template'] ?? null;
            $linkFile->cFullscreenTemplate = $links['FullscreenTemplate'] ?? null;

            $this->db->insert('tpluginlinkdatei', $linkFile);
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installPaymentMethods(array $node): int
    {
        $shopURL = \Shop::getURL(true) . '/';
        foreach ($node as $u => $data) {
            \preg_match("/[0-9]+\sattr/", $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $method                         = new \stdClass();
            $method->cName                  = $data['Name'];
            $method->cModulId               = Plugin::getModuleIDByPluginID(
                $this->plugin->kPlugin,
                $data['Name']
            );
            $method->cKundengruppen         = '';
            $method->cPluginTemplate        = $data['TemplateFile'] ?? null;
            $method->cZusatzschrittTemplate = $data['AdditionalTemplateFile'] ?? null;
            $method->nSort                  = isset($data['Sort'])
                ? (int)$data['Sort']
                : 0;
            $method->nMailSenden            = isset($data['SendMail'])
                ? (int)$data['SendMail']
                : 0;
            $method->nActive                = 1;
            $method->cAnbieter              = \is_array($data['Provider'])
                ? ''
                : $data['Provider'];
            $method->cTSCode                = \is_array($data['TSCode'])
                ? ''
                : $data['TSCode'];
            $method->nWaehrendBestellung    = (int)$data['PreOrder'];
            $method->nCURL                  = (int)$data['Curl'];
            $method->nSOAP                  = (int)$data['Soap'];
            $method->nSOCKETS               = (int)$data['Sockets'];
            $method->cBild                  = isset($data['PictureURL'])
                ? $shopURL . \PFAD_PLUGIN . $this->plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->plugin->nVersion . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $data['PictureURL']
                : '';
            $method->nNutzbar               = 0;
            $check                          = false;
            if ($method->nCURL === 0 && $method->nSOAP === 0 && $method->nSOCKETS === 0) {
                $method->nNutzbar = 1;
            } else {
                $check = true;
            }
            $methodID             = $this->db->insert('tzahlungsart', $method);
            $method->kZahlungsart = $methodID;
            if ($check) {
                \ZahlungsartHelper::activatePaymentMethod($method);
            }
            $moduleID = $method->cModulId;
            if (!$methodID) {
                return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD;
            }
            $paymentClass                         = new \stdClass();
            $paymentClass->cModulId               = Plugin::getModuleIDByPluginID(
                $this->plugin->kPlugin,
                $data['Name']
            );
            $paymentClass->kPlugin                = $this->plugin->kPlugin;
            $paymentClass->cClassPfad             = $data['ClassFile'] ?? null;
            $paymentClass->cClassName             = $data['ClassName'] ?? null;
            $paymentClass->cTemplatePfad          = $data['TemplateFile'] ?? null;
            $paymentClass->cZusatzschrittTemplate = $data['AdditionalTemplateFile'] ?? null;

            $this->db->insert('tpluginzahlungsartklasse', $paymentClass);

            $iso = '';
            // Hole alle Sprachen des Shops
            // Assoc cISO
            $allLanguages = \Sprache::getAllLanguages(2);
            // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bZahlungsartStandard   = false;
            $oZahlungsartSpracheStd = new \stdClass();

            foreach ($data['MethodLanguage'] as $l => $MethodLanguage_arr) {
                \preg_match("/[0-9]+\sattr/", $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    $iso = \strtolower($MethodLanguage_arr['iso']);
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    $oZahlungsartSprache               = new \stdClass();
                    $oZahlungsartSprache->kZahlungsart = $methodID;
                    $oZahlungsartSprache->cISOSprache  = $iso;
                    $oZahlungsartSprache->cName        = $MethodLanguage_arr['Name'];
                    $oZahlungsartSprache->cGebuehrname = $MethodLanguage_arr['ChargeName'];
                    $oZahlungsartSprache->cHinweisText = $MethodLanguage_arr['InfoText'];
                    // Erste ZahlungsartSprache vom Plugin als Standard setzen
                    if (!$bZahlungsartStandard) {
                        $oZahlungsartSpracheStd = $oZahlungsartSprache;
                        $bZahlungsartStandard   = true;
                    }
                    $kZahlungsartTMP = $this->db->insert('tzahlungsartsprache', $oZahlungsartSprache);
                    if (!$kZahlungsartTMP) {
                        // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                        return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_LOCALIZATION;
                    }

                    if (isset($allLanguages[$oZahlungsartSprache->cISOSprache])) {
                        // Resette aktuelle Sprache
                        unset($allLanguages[$oZahlungsartSprache->cISOSprache]);
                        $allLanguages = \array_merge($allLanguages);
                    }
                }
            }
            foreach ($allLanguages as $oSprachAssoc) {
                $oZahlungsartSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                $kZahlungsartTMP                     = $this->db->insert(
                    'tzahlungsartsprache',
                    $oZahlungsartSpracheStd
                );
                if (!$kZahlungsartTMP) {
                    return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_LANGUAGE;
                }
            }
            // Zahlungsmethode Einstellungen
            // Vordefinierte Einstellungen
            $names        = ['Anzahl Bestellungen nötig', 'Mindestbestellwert', 'Maximaler Bestellwert'];
            $valueNames   = ['min_bestellungen', 'min', 'max'];
            $descriptions = [
                'Nur Kunden, die min. soviele Bestellungen bereits durchgeführt haben, können diese Zahlungsart nutzen.',
                'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.',
                'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)'
            ];
            $nSort_arr    = [100, 101, 102];

            for ($z = 0; $z < 3; $z++) {
                // tplugineinstellungen füllen
                $conf          = new \stdClass();
                $conf->kPlugin = $this->plugin->kPlugin;
                $conf->cName   = $moduleID . '_' . $valueNames[$z];
                $conf->cWert   = 0;

                $this->db->insert('tplugineinstellungen', $conf);
                // tplugineinstellungenconf füllen
                $plgnConf                   = new \stdClass();
                $plgnConf->kPlugin          = $this->plugin->kPlugin;
                $plgnConf->kPluginAdminMenu = 0;
                $plgnConf->cName            = $names[$z];
                $plgnConf->cBeschreibung    = $descriptions[$z];
                $plgnConf->cWertName        = $moduleID . '_' . $valueNames[$z];
                $plgnConf->cInputTyp        = 'zahl';
                $plgnConf->nSort            = $nSort_arr[$z];
                $plgnConf->cConf            = 'Y';

                $this->db->insert('tplugineinstellungenconf', $plgnConf);
            }

            if (isset($data['Setting'])
                && \is_array($data['Setting'])
                && \count($data['Setting']) > 0
            ) {
                $type         = '';
                $initialValue = '';
                $nSort        = 0;
                $cConf        = 'Y';
                $multiple     = false;
                foreach ($data['Setting'] as $j => $Setting_arr) {
                    \preg_match('/[0-9]+\sattr/', $j, $hits3);
                    \preg_match('/[0-9]+/', $j, $hits4);

                    if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                        $type         = $Setting_arr['type'];
                        $multiple     = (isset($Setting_arr['multiple'])
                            && $Setting_arr['multiple'] === 'Y'
                            && $type === 'selectbox');
                        $initialValue = ($multiple === true)
                            ? \serialize([$Setting_arr['initialValue']])
                            : $Setting_arr['initialValue'];
                        $nSort        = $Setting_arr['sort'];
                        $cConf        = $Setting_arr['conf'];
                    } elseif (\strlen($hits4[0]) === \strlen($j)) {
                        $conf          = new \stdClass();
                        $conf->kPlugin = $this->plugin->kPlugin;
                        $conf->cName   = $moduleID . '_' . $Setting_arr['ValueName'];
                        $conf->cWert   = $initialValue;
                        if ($this->db->select('tplugineinstellungen', 'cName', $conf->cName) !== null) {
                            $this->db->update(
                                'tplugineinstellungen',
                                'cName',
                                $conf->cName,
                                $conf
                            );
                        } else {
                            $this->db->insert('tplugineinstellungen', $conf);
                        }
                        $plgnConf                   = new \stdClass();
                        $plgnConf->kPlugin          = $this->plugin->kPlugin;
                        $plgnConf->kPluginAdminMenu = 0;
                        $plgnConf->cName            = $Setting_arr['Name'];
                        $plgnConf->cBeschreibung    = (!isset($Setting_arr['Description'])
                            || \is_array($Setting_arr['Description']))
                            ? ''
                            : $Setting_arr['Description'];
                        $plgnConf->cWertName        = $moduleID . '_' . $Setting_arr['ValueName'];
                        $plgnConf->cInputTyp        = $type;
                        $plgnConf->nSort            = $nSort;
                        $plgnConf->cConf            = ($type === 'selectbox' && $multiple === true)
                            ? 'M'
                            : $cConf;
                        $plgnConfTmpID              = $this->db->select(
                            'tplugineinstellungenconf',
                            'cWertName',
                            $plgnConf->cWertName
                        );
                        if ($plgnConfTmpID !== null) {
                            $this->db->update(
                                'tplugineinstellungenconf',
                                'cWertName',
                                $plgnConf->cWertName,
                                $plgnConf
                            );
                            $kPluginEinstellungenConf = $plgnConfTmpID->kPluginEinstellungenConf;
                        } else {
                            $kPluginEinstellungenConf = $this->db->insert(
                                'tplugineinstellungenconf',
                                $plgnConf
                            );
                        }
                        // tplugineinstellungenconfwerte füllen
                        if ($kPluginEinstellungenConf <= 0) {
                            return InstallCode::SQL_CANNOT_SAVE_PAYMENT_METHOD_SETTING;
                        }
                        // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                        if ($type === 'selectbox') {
                            if (isset($Setting_arr['OptionsSource'])
                                && \is_array($Setting_arr['OptionsSource'])
                                && \count($Setting_arr['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (\count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                    \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                    if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                        $cWert = $Option_arr['value'];
                                        $nSort = $Option_arr['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                        $plgnConfValues                           = new \stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $plgnConfValues->cName                    = $cName;
                                        $plgnConfValues->cWert                    = $cWert;
                                        $plgnConfValues->nSort                    = $nSort;

                                        $this->db->insert(
                                            'tplugineinstellungenconfwerte',
                                            $plgnConfValues
                                        );
                                    }
                                }
                            } elseif (\count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                $idx                                      = $Setting_arr['SelectboxOptions'][0];
                                $plgnConfValues                           = new \stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                $plgnConfValues->cName                    = $idx['Option'];
                                $plgnConfValues->cWert                    = $idx['Option attr']['value'];
                                $plgnConfValues->nSort                    = $idx['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        } elseif ($type === 'radio') {
                            if (isset($Setting_arr['OptionsSource'])
                                && \is_array($Setting_arr['OptionsSource'])
                                && \count($Setting_arr['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (\count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                    \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                    if (\strlen($hits6[0]) === \strlen($y)) {
                                        $cWert = $Option_arr['value'];
                                        $nSort = $Option_arr['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                        $plgnConfValues                           = new \stdClass();
                                        $plgnConfValues->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $plgnConfValues->cName                    = $cName;
                                        $plgnConfValues->cWert                    = $cWert;
                                        $plgnConfValues->nSort                    = $nSort;

                                        $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                                    }
                                }
                            } elseif (\count($Setting_arr['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                                $idx                                      = $Setting_arr['RadioOptions'][0];
                                $plgnConfValues                           = new \stdClass();
                                $plgnConfValues->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                $plgnConfValues->cName                    = $idx['Option'];
                                $plgnConfValues->cWert                    = $idx['Option attr']['value'];
                                $plgnConfValues->nSort                    = $idx['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $plgnConfValues);
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installBoxes(array $node): int
    {
        foreach ($node as $h => $box) {
            \preg_match('/[0-9]+/', $h, $hits3);
            if (\strlen($hits3[0]) !== \strlen($h)) {
                continue;
            }
            $boxTpl              = new \stdClass();
            $boxTpl->kCustomID   = $this->plugin->kPlugin;
            $boxTpl->eTyp        = 'plugin';
            $boxTpl->cName       = $box['Name'];
            $boxTpl->cVerfuegbar = $box['Available'];
            $boxTpl->cTemplate   = $box['TemplateFile'];
            if (!$this->db->insert('tboxvorlage', $boxTpl)) {
                return InstallCode::SQL_CANNOT_SAVE_BOX_TEMPLATE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installTemplates(array $node): int
    {
        foreach ($node as $template) {
            \preg_match("/[a-zA-Z0-9\/_\-]+\.tpl/", $template, $hits3);
            if (\strlen($hits3[0]) !== \strlen($template)) {
                continue;
            }
            $plgnTpl            = new \stdClass();
            $plgnTpl->kPlugin   = $this->plugin->kPlugin;
            $plgnTpl->cTemplate = $template;
            if (!$this->db->insert('tplugintemplate', $plgnTpl)) {
                return InstallCode::SQL_CANNOT_SAVE_TEMPLATE;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installMailTemplates(array $node): int
    {
        foreach ($node as $u => $template) {
            \preg_match("/[0-9]+\sattr/", $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
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

    /**
     * @param array $node
     * @return int
     */
    private function installLangVars(array $node): int
    {
        $languages = \Sprache::getAllLanguages(2);
        foreach ($node as $t => $langVar) {
            \preg_match('/[0-9]+/', $t, $hits1);
            if (\strlen($hits1[0]) !== \strlen($t)) {
                continue;
            }
            $pluginLangVar          = new \stdClass();
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
            $oVariableSpracheStd = new \stdClass();
            // Nur eine Sprache vorhanden
            if (isset($langVar['VariableLocalized attr'])
                && \is_array($langVar['VariableLocalized attr'])
                && \count($langVar['VariableLocalized attr']) > 0
            ) {
                // tpluginsprachvariablesprache füllen
                $localized                        = new \stdClass();
                $localized->kPluginSprachvariable = $id;
                $localized->cISO                  = $langVar['VariableLocalized attr']['iso'];
                $localized->cName                 = \preg_replace('/\s+/', ' ', $langVar['VariableLocalized']);

                $this->db->insert('tpluginsprachvariablesprache', $localized);

                // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                if (!$bVariableStandard) {
                    $oVariableSpracheStd = $localized;
                    $bVariableStandard   = true;
                }

                if (isset($languages[\strtolower($localized->cISO)])) {
                    // Resette aktuelle Sprache
                    unset($languages[\strtolower($localized->cISO)]);
                    $languages = \array_merge($languages);
                }
            } elseif (isset($langVar['VariableLocalized'])
                && \is_array($langVar['VariableLocalized'])
                && \count($langVar['VariableLocalized']) > 0
            ) {
                foreach ($langVar['VariableLocalized'] as $i => $VariableLocalized_arr) {
                    \preg_match("/[0-9]+\sattr/", $i, $hits1);

                    if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($i)) {
                        $cISO                             = $VariableLocalized_arr['iso'];
                        $yx                               = \substr($i, 0, \strpos($i, ' '));
                        $cName                            = $langVar['VariableLocalized'][$yx];
                        $localized                        = new \stdClass();
                        $localized->kPluginSprachvariable = $id;
                        $localized->cISO                  = $cISO;
                        $localized->cName                 = \preg_replace('/\s+/', ' ', $cName);

                        $this->db->insert('tpluginsprachvariablesprache', $localized);
                        // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                        if (!$bVariableStandard) {
                            $oVariableSpracheStd = $localized;
                            $bVariableStandard   = true;
                        }

                        if (isset($languages[\strtolower($localized->cISO)])) {
                            unset($languages[\strtolower($localized->cISO)]);
                            $languages = \array_merge($languages);
                        }
                    }
                }
            }
            foreach ($languages as $oSprachAssoc) {
                $oVariableSpracheStd->cISO = \strtoupper($oSprachAssoc->cISO);
                if (!$this->db->insert('tpluginsprachvariablesprache', $oVariableSpracheStd)) {
                    return InstallCode::SQL_CANNOT_SAVE_LANG_VAR_LOCALIZATION;
                }
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installCheckboxes(array $node): int
    {
        foreach ($node as $t => $function) {
            \preg_match('/[0-9]+/', $t, $hits2);
            if (\strlen($hits2[0]) !== \strlen($t)) {
                continue;
            }
            $cbFunction          = new \stdClass();
            $cbFunction->kPlugin = $this->plugin->kPlugin;
            $cbFunction->cName   = $function['Name'];
            $cbFunction->cID     = $this->plugin->cPluginID . '_' . $function['ID'];
            $this->db->insert('tcheckboxfunktion', $cbFunction);
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installAdminWidgets(array $node): int
    {
        foreach ($node as $u => $widgetData) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $widget               = new \stdClass();
            $widget->kPlugin      = $this->plugin->kPlugin;
            $widget->cTitle       = $widgetData['Title'];
            $widget->cClass       = $widgetData['Class'] . '_' . $this->plugin->cPluginID;
            $widget->eContainer   = $widgetData['Container'];
            $widget->cDescription = $widgetData['Description'];
            if (\is_array($widget->cDescription)) {
                //@todo: when description is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                $widget->cDescription = $widget->cDescription[0];
            }
            $widget->nPos      = $widgetData['Pos'];
            $widget->bExpanded = $widgetData['Expanded'];
            $widget->bActive   = $widgetData['Active'];
            if (!$this->db->insert('tadminwidgets', $widget)) {
                return InstallCode::SQL_CANNOT_SAVE_WIDGET;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installPortlets(array $node): int
    {
        foreach ($node as $u => $portlet) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $oPortlet = (object)[
                'kPlugin' => $this->plugin->kPlugin,
                'cTitle'  => $portlet['Title'],
                'cClass'  => $portlet['Class'],
                'cGroup'  => $portlet['Group'],
                'bActive' => (int)$portlet['Active'],
            ];
            if (!$this->db->insert('topcportlet', $oPortlet)) {
                return InstallCode::SQL_CANNOT_SAVE_PORTLET;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installBlueprints(array $node): int
    {
        foreach ($node as $u => $blueprint) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $blueprintJson = \file_get_contents(
                \PFAD_ROOT . \PFAD_PLUGIN .
                $this->plugin->cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION .
                $this->plugin->nVersion . '/' .
                \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS .
                $blueprint['JSONFile']
            );

            $blueprintData = \json_decode($blueprintJson, true);
            $instanceJson  = \json_encode($blueprintData['instance']);
            $blueprintObj  = (object)[
                'kPlugin' => $this->plugin->kPlugin,
                'cName'   => $blueprint['Name'],
                'cJson'   => $instanceJson,
            ];
            if (!$this->db->insert('topcblueprint', $blueprintObj)) {
                return InstallCode::SQL_CANNOT_SAVE_BLUEPRINT;
            }
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installExports(array $node): int
    {
        $kKundengruppeStd = \Kundengruppe::getDefaultGroupID();
        $oSprache         = \Sprache::getDefaultLanguage(true);
        $kSpracheStd      = $oSprache->kSprache;
        $kWaehrungStd     = \Session\Session::getCurrency()->getID();

        foreach ($node as $u => $data) {
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            $export                   = new \stdClass();
            $export->kKundengruppe    = $kKundengruppeStd;
            $export->kSprache         = $kSpracheStd;
            $export->kWaehrung        = $kWaehrungStd;
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
            $kExportformat = $this->db->insert('texportformat', $export);

            if (!$kExportformat) {
                return InstallCode::SQL_CANNOT_SAVE_EXPORT;
            }
            // Einstellungen
            // <OnlyStockGreaterZero>N</OnlyStockGreaterZero> => exportformate_lager_ueber_null
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_lager_ueber_null';
            $exportConf->cWert         = \strlen($data['OnlyStockGreaterZero']) !== 0
                ? $data['OnlyStockGreaterZero']
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            // <OnlyPriceGreaterZero>N</OnlyPriceGreaterZero> => exportformate_preis_ueber_null
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_preis_ueber_null';
            $exportConf->cWert         = $data['OnlyPriceGreaterZero'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            // <OnlyProductsWithDescription>N</OnlyProductsWithDescription> => exportformate_beschreibung
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_beschreibung';
            $exportConf->cWert         = $data['OnlyProductsWithDescription'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            // <ShippingCostsDeliveryCountry>DE</ShippingCostsDeliveryCountry> => exportformate_lieferland
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_lieferland';
            $exportConf->cWert         = $data['ShippingCostsDeliveryCountry'];
            $this->db->insert('texportformateinstellungen', $exportConf);
            // <EncodingQuote>N</EncodingQuote> => exportformate_quot
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_quot';
            $exportConf->cWert         = $data['EncodingQuote'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            // <EncodingDoubleQuote>N</EncodingDoubleQuote> => exportformate_equot
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_equot';
            $exportConf->cWert         = $data['EncodingDoubleQuote'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
            // <EncodingSemicolon>N</EncodingSemicolon> => exportformate_semikolon
            $exportConf                = new \stdClass();
            $exportConf->kExportformat = $kExportformat;
            $exportConf->cName         = 'exportformate_semikolon';
            $exportConf->cWert         = $data['EncodingSemicolon'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $exportConf);
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installCSS(array $node): int
    {
        foreach ($node as $file) {
            if (!isset($file['name'])) {
                continue;
            }
            $res           = new \stdClass();
            $res->kPlugin  = $this->plugin->kPlugin;
            $res->type     = 'css';
            $res->path     = $file['name'];
            $res->priority = $file['priority'] ?? 5;
            $this->db->insert('tplugin_resources', $res);
        }

        return InstallCode::OK;
    }

    /**
     * @param array $node
     * @return int
     */
    private function installJS(array $node): int
    {
        foreach ($node as $file) {
            if (!isset($file['name'])) {
                continue;
            }
            $res           = new \stdClass();
            $res->kPlugin  = $this->plugin->kPlugin;
            $res->type     = 'js';
            $res->path     = $file['name'];
            $res->priority = $file['priority'] ?? 5;
            $res->position = $file['position'] ?? 'head';
            $this->db->insert('tplugin_resources', $res);
        }

        return InstallCode::OK;
    }
}
