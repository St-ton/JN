<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin;


class Test
{


    /**
     * Installiert die tplugin* Tabellen für ein Plugin in der Datenbank
     *
     * @param array  $XML_arr
     * @param object $oPlugin
     * @param object $oPluginOld
     * @return int
     */
    private function installPluginTables($XML_arr, $oPlugin, $oPluginOld)
    {
        $kPlugin      = $oPlugin->kPlugin;
        $cVerzeichnis = $oPlugin->cVerzeichnis;
        $nVersion     = $oPlugin->nVersion;

        // used in ExportFormate
        $kKundengruppeStd = \Kundengruppe::getDefaultGroupID();
        $oSprache         = \Sprache::getDefaultLanguage(true);
        $kSpracheStd      = $oSprache->kSprache;
        $kWaehrungStd     = \Session::Currency()->getID();

        $hooksNode      = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks']
            : null;
        $uninstallNode  = !empty($XML_arr['jtlshop3plugin'][0]['Uninstall'])
            ? $XML_arr['jtlshop3plugin'][0]['Uninstall']
            : null;
        $adminNode      = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu']
            : null;
        $frontendNode   = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link']
            : [];
        $paymentNode    = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'])
        && \count($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) > 0
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']
            : [];
        $boxesNode      = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box']
            : [];
        $checkboxesNode = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'])
        && \count($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) > 0
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']
            : [];
        $cTemplate_arr  = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
            ? (array)$XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'][0]['Template']
            : [];
        $mailNode       = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template']
            : [];
        $localeNode     = $XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable'] ?? [];
        $widgetsNode    = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']
            : [];
        $portletsNode   = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Portlets'][0]['Portlet']
            : [];
        $blueprintsNode = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Blueprints'][0]['Blueprint']
            : [];
        $exportNode     = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']
            : [];
        $cssNode        = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file']
            : [];
        $jsNode         = isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])
        && \is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])
            ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file']
            : [];
        // tpluginhook füllen
        if ($hooksNode !== null) {
            if (\count($hooksNode[0]) === 1) {
                // Es gibt mehr als einen Hook
                $nHookID   = 0;
                $nPriority = 5;
                foreach ($hooksNode[0]['Hook'] as $i => $hook) {
                    \preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                    \preg_match('/[0-9]+/', $i, $cTreffer2_arr);
                    if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($i)) {
                        $nHookID   = (int)$hook['id'];
                        $nPriority = isset($hook['priority']) ? (int)$hook['priority'] : 5;
                    } elseif (isset($cTreffer2_arr[0]) && \strlen($cTreffer2_arr[0]) === \strlen($i)) {
                        $oPluginHook             = new \stdClass();
                        $oPluginHook->kPlugin    = $kPlugin;
                        $oPluginHook->nHook      = $nHookID;
                        $oPluginHook->nPriority  = $nPriority;
                        $oPluginHook->cDateiname = $hook;

                        $kPluginHook = $this->db->insert('tpluginhook', $oPluginHook);

                        if (!$kPluginHook) {
                            return 3;//Ein Hook konnte nicht in die Datenbank gespeichert werden
                        }
                    }
                }
            } elseif (\count($hooksNode[0]) > 1) {
                // Es gibt nur einen Hook
                $hook = $hooksNode[0];

                $oPluginHook             = new \stdClass();
                $oPluginHook->kPlugin    = $kPlugin;
                $oPluginHook->nHook      = (int)$hook['Hook attr']['id'];
                $oPluginHook->nPriority  = isset($hook['Hook attr']['priority'])
                    ? (int)$hook['Hook attr']['priority']
                    : 5;
                $oPluginHook->cDateiname = $hook['Hook'];

                $kPluginHook = $this->db->insert('tpluginhook', $oPluginHook);

                if (!$kPluginHook) {
                    return 3;//Ein Hook konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
        // tpluginuninstall füllen
        if ($uninstallNode !== null) {
            $oPluginUninstall             = new \stdClass();
            $oPluginUninstall->kPlugin    = $kPlugin;
            $oPluginUninstall->cDateiname = $uninstallNode;

            $kPluginUninstall = $this->db->insert('tpluginuninstall', $oPluginUninstall);

            if (!$kPluginUninstall) {
                return 18;//Eine Uninstall-Datei konnte nicht in die Datenbank gespeichert werden
            }
        }
        // tpluginadminmenu füllen
        if ($adminNode !== null) {
            // Adminsmenüs vorhanden?
            if (isset($adminNode[0]['Customlink'])
                && \is_array($adminNode[0]['Customlink'])
                && \count($adminNode[0]['Customlink']) > 0
            ) {
                $nSort = 0;
                foreach ($adminNode[0]['Customlink'] as $i => $customLink) {
                    \preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                    \preg_match('/[0-9]+/', $i, $cTreffer2_arr);

                    if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($i)) {
                        $nSort = (int)$customLink['sort'];
                    } elseif (\strlen($cTreffer2_arr[0]) === \strlen($i)) {
                        $oAdminMenu             = new \stdClass();
                        $oAdminMenu->kPlugin    = $kPlugin;
                        $oAdminMenu->cName      = $customLink['Name'];
                        $oAdminMenu->cDateiname = $customLink['Filename'];
                        $oAdminMenu->nSort      = $nSort;
                        $oAdminMenu->nConf      = 0;

                        $kPluginAdminMenu = $this->db->insert('tpluginadminmenu', $oAdminMenu);

                        if (!$kPluginAdminMenu) {
                            return 4;//Ein Adminmenü-Customlink konnte nicht in die Datenbank gespeichert werden
                        }
                    }
                }
            }
            // Einstellungen vorhanden?
            if (isset($adminNode[0]['Settingslink'])
                && \is_array($adminNode[0]['Settingslink'])
                && \count($adminNode[0]['Settingslink']) > 0
            ) {
                $nSort = 0;
                foreach ($adminNode[0]['Settingslink'] as $i => $Settingslink_arr) {
                    \preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                    \preg_match('/[0-9]+/', $i, $cTreffer2_arr);
                    if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($i)) {
                        $nSort = (int)$Settingslink_arr['sort'];
                    } elseif (\strlen($cTreffer2_arr[0]) === \strlen($i)) {
                        // tpluginadminmenu füllen
                        $oAdminMenu             = new \stdClass();
                        $oAdminMenu->kPlugin    = $kPlugin;
                        $oAdminMenu->cName      = $Settingslink_arr['Name'];
                        $oAdminMenu->cDateiname = '';
                        $oAdminMenu->nSort      = $nSort;
                        $oAdminMenu->nConf      = 1;

                        $kPluginAdminMenu = $this->db->insert('tpluginadminmenu', $oAdminMenu);

                        if ($kPluginAdminMenu <= 0) {
                            return 5;// Ein Adminmenü Settingslink konnte nicht in die Datenbank gespeichert werden
                        }
                        $cTyp          = '';
                        $cInitialValue = '';
                        $nSort         = 0;
                        $cConf         = 'Y';
                        $multiple      = false;
                        foreach ($Settingslink_arr['Setting'] as $j => $Setting_arr) {
                            \preg_match("/[0-9]+\sattr/", $j, $cTreffer3_arr);
                            \preg_match('/[0-9]+/', $j, $cTreffer4_arr);

                            if (isset($cTreffer3_arr[0]) && \strlen($cTreffer3_arr[0]) === \strlen($j)) {
                                $cTyp          = $Setting_arr['type'];
                                $multiple      = (isset($Setting_arr['multiple']) && $Setting_arr['multiple'] === 'Y' && $cTyp === 'selectbox');
                                $cInitialValue = ($multiple === true) ?
                                    serialize([$Setting_arr['initialValue']])
                                    : $Setting_arr['initialValue'];
                                $nSort         = $Setting_arr['sort'];
                                $cConf         = $Setting_arr['conf'];
                            } elseif (\strlen($cTreffer4_arr[0]) === \strlen($j)) {
                                // tplugineinstellungen füllen
                                $oPluginEinstellungen          = new \stdClass();
                                $oPluginEinstellungen->kPlugin = $kPlugin;
                                $oPluginEinstellungen->cName   = \is_array($Setting_arr['ValueName'])
                                    ? $Setting_arr['ValueName']['0']
                                    : $Setting_arr['ValueName'];
                                $oPluginEinstellungen->cWert   = $cInitialValue;

                                if ($this->db->select('tplugineinstellungen', 'cName', $oPluginEinstellungen->cName) !== null) {
                                    $this->db->update('tplugineinstellungen', 'cName', $oPluginEinstellungen->cName, $oPluginEinstellungen);
                                } else {
                                    $this->db->insert('tplugineinstellungen', $oPluginEinstellungen);
                                }
                                // tplugineinstellungenconf füllen
                                $oPluginEinstellungenConf                   = new \stdClass();
                                $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                                $oPluginEinstellungenConf->kPluginAdminMenu = $kPluginAdminMenu;
                                $oPluginEinstellungenConf->cName            = $Setting_arr['Name'];
                                $oPluginEinstellungenConf->cBeschreibung    = (!isset($Setting_arr['Description']) || \is_array($Setting_arr['Description']))
                                    ? ''
                                    : $Setting_arr['Description'];
                                $oPluginEinstellungenConf->cWertName = \is_array($Setting_arr['ValueName'])
                                    ? $Setting_arr['ValueName']['0']
                                    : $Setting_arr['ValueName'];
                                $oPluginEinstellungenConf->cInputTyp = $cTyp;
                                $oPluginEinstellungenConf->nSort     = $nSort;
                                $oPluginEinstellungenConf->cConf     = $cConf;
                                //dynamic data source for selectbox/radio
                                if ($cTyp === 'selectbox' || $cTyp === 'radio') {
                                    if (isset($Setting_arr['OptionsSource'][0]['File'])) {
                                        $oPluginEinstellungenConf->cSourceFile = $Setting_arr['OptionsSource'][0]['File'];
                                    }
                                    if ($multiple === true) {
                                        $oPluginEinstellungenConf->cConf = 'M';
                                    }
                                }
                                if (($kPluginEinstellungenConfTMP = $this->db->select('tplugineinstellungenconf', 'cWertName', $oPluginEinstellungenConf->cWertName)) !== null) {
                                    $this->db->update(
                                        'tplugineinstellungenconf',
                                        'cWertName',
                                        $oPluginEinstellungenConf->cWertName,
                                        $oPluginEinstellungenConf
                                    );
                                    $kPluginEinstellungenConf = $kPluginEinstellungenConfTMP->kPluginEinstellungenConf;
                                } else {
                                    $kPluginEinstellungenConf = $this->db->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                                }
                                // tplugineinstellungenconfwerte füllen
                                if ($kPluginEinstellungenConf > 0) {
                                    $nSort = 0;
                                    // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                    if ($cTyp === 'selectbox') {
                                        if (isset($Setting_arr['OptionsSource']) && \is_array($Setting_arr['OptionsSource']) && \count($Setting_arr['OptionsSource']) > 0) {
                                            //do nothing for now
                                        } elseif (\count($Setting_arr['SelectboxOptions'][0]) === 1) { // Es gibt mehr als 1 Option
                                            foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                                \preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);

                                                if (isset($cTreffer6_arr[0]) && \strlen($cTreffer6_arr[0]) === \strlen($y)) {
                                                    $cWert = $Option_arr['value'];
                                                    $nSort = $Option_arr['sort'];
                                                    $yx    = \substr($y, 0, \strpos($y, ' '));
                                                    $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                                    $oPluginEinstellungenConfWerte                           = new \stdClass();
                                                    $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                    $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                    $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                    $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                    $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                                }
                                            }
                                        } elseif (\count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                            // Es gibt nur eine Option
                                            $oPluginEinstellungenConfWerte                           = new \stdClass();
                                            $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                            $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['SelectboxOptions'][0]['Option'];
                                            $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['value'];
                                            $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['sort'];

                                            $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                        }
                                    } elseif ($cTyp === 'radio') {
                                        if (isset($Setting_arr['OptionsSource']) && \is_array($Setting_arr['OptionsSource']) && \count($Setting_arr['OptionsSource']) > 0) {

                                        } elseif (\count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                            foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                                \preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                                if (isset($cTreffer6_arr[0]) && \strlen($cTreffer6_arr[0]) === \strlen($y)) {
                                                    $cWert = $Option_arr['value'];
                                                    $nSort = $Option_arr['sort'];
                                                    $yx    = \substr($y, 0, \strpos($y, ' '));
                                                    $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                                    $oPluginEinstellungenConfWerte                           = new \stdClass();
                                                    $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                    $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                    $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                    $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                    $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                                }
                                            }
                                        } elseif (\count($Setting_arr['RadioOptions'][0]) === 2) {
                                            // Es gibt nur eine Option
                                            $oPluginEinstellungenConfWerte                           = new \stdClass();
                                            $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                            $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['RadioOptions'][0]['Option'];
                                            $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['RadioOptions'][0]['Option attr']['value'];
                                            $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['RadioOptions'][0]['Option attr']['sort'];

                                            $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                        }
                                    }
                                } else {
                                    return 6;// Eine Einstellung konnte nicht in die Datenbank gespeichert werden
                                }
                            }
                        }
                    }
                }
            }
        }
        // FrontendLinks (falls vorhanden)
        foreach ($frontendNode as $u => $Link_arr) {
            \preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);
            $oLink = new \stdClass();
            if (empty($Link_arr['LinkGroup'])) {
                // linkgroup not set? default to 'hidden'
                $Link_arr['LinkGroup'] = 'hidden';
            }
            $oLinkgruppe = $this->db->select('tlinkgruppe', 'cName', $Link_arr['LinkGroup']);
            if ($oLinkgruppe === null) {
                // linkgroup not in database? create it anew
                $oLinkgruppe                = new \stdClass();
                $oLinkgruppe->cName         = $Link_arr['LinkGroup'];
                $oLinkgruppe->cTemplatename = $Link_arr['LinkGroup'];
                $oLinkgruppe->kLinkgruppe   = $this->db->insert('tlinkgruppe', $oLinkgruppe);
            }
            if (!isset($oLinkgruppe->kLinkgruppe) || $oLinkgruppe->kLinkgruppe <= 0) {
                return 12; // Es konnte keine Linkgruppe im Shop gefunden werden
            }
            $kLinkgruppe = $oLinkgruppe->kLinkgruppe;
            if (\strlen($cTreffer2_arr[0]) !== \strlen($u)) {
                continue;
            }
            $kLinkOld                  = empty($oPluginOld->kPlugin)
                ? null
                : $this->db->select('tlink', 'kPlugin', $oPluginOld->kPlugin, 'cName', $Link_arr['Name']);
            $oLink->kPlugin            = $kPlugin;
            $oLink->cName              = $Link_arr['Name'];
            $oLink->nLinkart           = LINKTYP_PLUGIN;
            $oLink->cSichtbarNachLogin = $Link_arr['VisibleAfterLogin'];
            $oLink->cDruckButton       = $Link_arr['PrintButton'];
            $oLink->cNoFollow          = $Link_arr['NoFollow'] ?? null;
            $oLink->nSort              = LINKTYP_PLUGIN;
            $oLink->bSSL               = isset($Link_arr['SSL'])
                ? (int)$Link_arr['SSL']
                : 0;
            $kLink = $this->db->insert('tlink', $oLink);

            if ($kLink > 0) {
                $linkGroupAssociation              = new \stdClass();
                $linkGroupAssociation->linkGroupID = $kLinkgruppe;
                $linkGroupAssociation->linkID      = $kLink;
                $this->db->insert('tlinkgroupassociations', $linkGroupAssociation);

                $oLinkSprache        = new \stdClass();
                $oLinkSprache->kLink = $kLink;
                // Hole alle Sprachen des Shops
                // Assoc cISO
                $oSprachAssoc_arr = \Sprache::getAllLanguages(2);
                // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
                // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
                // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
                $bLinkStandard   = false;
                $oLinkSpracheStd = new \stdClass();

                foreach ($Link_arr['LinkLanguage'] as $l => $LinkLanguage_arr) {
                    \preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                    \preg_match('/[0-9]+/', $l, $cTreffer2_arr);
                    if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($l)) {
                        $oLinkSprache->cISOSprache = \strtolower($LinkLanguage_arr['iso']);
                    } elseif (\strlen($cTreffer2_arr[0]) === \strlen($l)) {
                        // tlinksprache füllen
                        $oLinkSprache->cSeo             = \checkSeo(\getSeo($LinkLanguage_arr['Seo']));
                        $oLinkSprache->cName            = $LinkLanguage_arr['Name'];
                        $oLinkSprache->cTitle           = $LinkLanguage_arr['Title'];
                        $oLinkSprache->cContent         = '';
                        $oLinkSprache->cMetaTitle       = $LinkLanguage_arr['MetaTitle'];
                        $oLinkSprache->cMetaKeywords    = $LinkLanguage_arr['MetaKeywords'];
                        $oLinkSprache->cMetaDescription = $LinkLanguage_arr['MetaDescription'];

                        $this->db->insert('tlinksprache', $oLinkSprache);
                        // Erste Linksprache vom Plugin als Standard setzen
                        if (!$bLinkStandard) {
                            $oLinkSpracheStd = $oLinkSprache;
                            $bLinkStandard   = true;
                        }

                        if ($oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache > 0) {
                            $or = isset($kLinkOld->kLink) ? (' OR kKey = ' . (int)$kLinkOld->kLink) : '';
                            $this->db->query(
                                "DELETE FROM tseo
                                    WHERE cKey = 'kLink'
                                        AND (kKey = " . (int)$kLink . $or . ")
                                        AND kSprache = " . (int)$oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache,
                                \DB\ReturnType::DEFAULT
                            );
                            // tseo füllen
                            $oSeo           = new \stdClass();
                            $oSeo->cSeo     = \checkSeo(\getSeo($LinkLanguage_arr['Seo']));
                            $oSeo->cKey     = 'kLink';
                            $oSeo->kKey     = $kLink;
                            $oSeo->kSprache = $oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache;

                            $this->db->insert('tseo', $oSeo);
                        }

                        if (isset($oSprachAssoc_arr[$oLinkSprache->cISOSprache])) {
                            // Resette aktuelle Sprache
                            unset($oSprachAssoc_arr[$oLinkSprache->cISOSprache]);
                            $oSprachAssoc_arr = \array_merge($oSprachAssoc_arr);
                        }
                    }
                }
                // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
                if (\count($oSprachAssoc_arr) > 0) {
                    foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                        //$oSprache = $oSprachAssoc;
                        if ($oSprachAssoc->kSprache > 0) {
                            $this->db->delete(
                                'tseo',
                                ['cKey', 'kKey', 'kSprache'],
                                ['kLink', (int)$kLink, (int)$oSprachAssoc->kSprache]
                            );
                            // tseo füllen
                            $oSeo           = new \stdClass();
                            $oSeo->cSeo     = \checkSeo(\getSeo($oLinkSpracheStd->cSeo));
                            $oSeo->cKey     = 'kLink';
                            $oSeo->kKey     = $kLink;
                            $oSeo->kSprache = $oSprachAssoc->kSprache;

                            $this->db->insert('tseo', $oSeo);
                            // tlinksprache füllen
                            $oLinkSpracheStd->cSeo        = $oSeo->cSeo;
                            $oLinkSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                            $this->db->insert('tlinksprache', $oLinkSpracheStd);
                        }
                    }
                }
                // tpluginhook füllen (spezieller Ausnahmefall für Frontend Links)
                $oPluginHook             = new \stdClass();
                $oPluginHook->kPlugin    = $kPlugin;
                $oPluginHook->nHook      = \HOOK_SEITE_PAGE_IF_LINKART;
                $oPluginHook->cDateiname = \PLUGIN_SEITENHANDLER;

                $kPluginHook = $this->db->insert('tpluginhook', $oPluginHook);

                if (!$kPluginHook) {
                    return 3; // Ein Hook konnte nicht in die Datenbank gespeichert werden
                }
                // tpluginlinkdatei füllen
                $oPluginLinkDatei                      = new \stdClass();
                $oPluginLinkDatei->kPlugin             = $kPlugin;
                $oPluginLinkDatei->kLink               = $kLink;
                $oPluginLinkDatei->cDatei              = $Link_arr['Filename'] ?? null;
                $oPluginLinkDatei->cTemplate           = $Link_arr['Template'] ?? null;
                $oPluginLinkDatei->cFullscreenTemplate = $Link_arr['FullscreenTemplate'] ?? null;

                $this->db->insert('tpluginlinkdatei', $oPluginLinkDatei);
            } else {
                return 8; // Ein Link konnte nicht in die Datenbank gespeichert werden
            }
        }
        // Zahlungsmethode (PaymentMethod) (falls vorhanden)
        $shopURL = \Shop::getURL(true) . '/';
        foreach ($paymentNode as $u => $Method_arr) {
            \preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);
            if (\strlen($cTreffer2_arr[0]) !== \strlen($u)) {
                continue;
            }
            $oZahlungsart                         = new \stdClass();
            $oZahlungsart->cName                  = $Method_arr['Name'];
            $oZahlungsart->cModulId               = \Plugin::getModuleIDByPluginID($kPlugin, $Method_arr['Name']);
            $oZahlungsart->cKundengruppen         = '';
            $oZahlungsart->cPluginTemplate        = $Method_arr['TemplateFile'] ?? null;
            $oZahlungsart->cZusatzschrittTemplate = $Method_arr['AdditionalTemplateFile'] ?? null;
            $oZahlungsart->nSort                  = isset($Method_arr['Sort'])
                ? (int)$Method_arr['Sort']
                : 0;
            $oZahlungsart->nMailSenden            = isset($Method_arr['SendMail'])
                ? (int)$Method_arr['SendMail']
                : 0;
            $oZahlungsart->nActive                = 1;
            $oZahlungsart->cAnbieter              = \is_array($Method_arr['Provider'])
                ? ''
                : $Method_arr['Provider'];
            $oZahlungsart->cTSCode                = \is_array($Method_arr['TSCode'])
                ? ''
                : $Method_arr['TSCode'];
            $oZahlungsart->nWaehrendBestellung    = (int)$Method_arr['PreOrder'];
            $oZahlungsart->nCURL                  = (int)$Method_arr['Curl'];
            $oZahlungsart->nSOAP                  = (int)$Method_arr['Soap'];
            $oZahlungsart->nSOCKETS               = (int)$Method_arr['Sockets'];
            $oZahlungsart->cBild                  = isset($Method_arr['PictureURL'])
                ? $shopURL . PFAD_PLUGIN . $cVerzeichnis . '/' .
                PFAD_PLUGIN_VERSION . $nVersion . '/' .
                PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['PictureURL']
                : '';
            $oZahlungsart->nNutzbar = 0;
            $bPruefen               = false;
            if ($oZahlungsart->nCURL == 0 && $oZahlungsart->nSOAP == 0 && $oZahlungsart->nSOCKETS == 0) {
                $oZahlungsart->nNutzbar = 1;
            } else {
                $bPruefen = true;
            }
            $kZahlungsart               = $this->db->insert('tzahlungsart', $oZahlungsart);
            $oZahlungsart->kZahlungsart = $kZahlungsart;

            if ($bPruefen) {
                \ZahlungsartHelper::activatePaymentMethod($oZahlungsart);
            }

            $cModulId = $oZahlungsart->cModulId;

            if (!$kZahlungsart) {
                return 9; //Eine Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
            }
            // tpluginzahlungsartklasse füllen
            $oPluginZahlungsartKlasse                         = new \stdClass();
            $oPluginZahlungsartKlasse->cModulId               = \Plugin::getModuleIDByPluginID($kPlugin, $Method_arr['Name']);
            $oPluginZahlungsartKlasse->kPlugin                = $kPlugin;
            $oPluginZahlungsartKlasse->cClassPfad             = $Method_arr['ClassFile'] ?? null;
            $oPluginZahlungsartKlasse->cClassName             = $Method_arr['ClassName'] ?? null;
            $oPluginZahlungsartKlasse->cTemplatePfad          = $Method_arr['TemplateFile'] ?? null;
            $oPluginZahlungsartKlasse->cZusatzschrittTemplate = $Method_arr['AdditionalTemplateFile'] ?? null;

            $this->db->insert('tpluginzahlungsartklasse', $oPluginZahlungsartKlasse);

            $cISOSprache = '';
            // Hole alle Sprachen des Shops
            // Assoc cISO
            $oSprachAssoc_arr = \Sprache::getAllLanguages(2);
            // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bZahlungsartStandard   = false;
            $oZahlungsartSpracheStd = new \stdClass();

            foreach ($Method_arr['MethodLanguage'] as $l => $MethodLanguage_arr) {
                \preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                \preg_match('/[0-9]+/', $l, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($l)) {
                    $cISOSprache = \strtolower($MethodLanguage_arr['iso']);
                } elseif (\strlen($cTreffer2_arr[0]) === \strlen($l)) {
                    $oZahlungsartSprache               = new \stdClass();
                    $oZahlungsartSprache->kZahlungsart = $kZahlungsart;
                    $oZahlungsartSprache->cISOSprache  = $cISOSprache;
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
                        return 10; // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                    }

                    if (isset($oSprachAssoc_arr[$oZahlungsartSprache->cISOSprache])) {
                        // Resette aktuelle Sprache
                        unset($oSprachAssoc_arr[$oZahlungsartSprache->cISOSprache]);
                        $oSprachAssoc_arr = \array_merge($oSprachAssoc_arr);
                    }
                }
            }

            // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
            if (\count($oSprachAssoc_arr) > 0) {
                foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                    $oZahlungsartSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                    $kZahlungsartTMP                     = $this->db->insert('tzahlungsartsprache', $oZahlungsartSpracheStd);
                    if (!$kZahlungsartTMP) {
                        return 10; // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
            // Zahlungsmethode Einstellungen
            // Vordefinierte Einstellungen
            $cName_arr         = ['Anzahl Bestellungen nötig', 'Mindestbestellwert', 'Maximaler Bestellwert'];
            $cWertName_arr     = ['min_bestellungen', 'min', 'max'];
            $cBeschreibung_arr = [
                'Nur Kunden, die min. soviele Bestellungen bereits durchgeführt haben, können diese Zahlungsart nutzen.',
                'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.',
                'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)'];
            $nSort_arr         = [100, 101, 102];

            for ($z = 0; $z < 3; $z++) {
                // tplugineinstellungen füllen
                $oPluginEinstellungen          = new \stdClass();
                $oPluginEinstellungen->kPlugin = $kPlugin;
                $oPluginEinstellungen->cName   = $cModulId . '_' . $cWertName_arr[$z];
                $oPluginEinstellungen->cWert   = 0;

                $this->db->insert('tplugineinstellungen', $oPluginEinstellungen);
                // tplugineinstellungenconf füllen
                $oPluginEinstellungenConf                   = new \stdClass();
                $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                $oPluginEinstellungenConf->kPluginAdminMenu = 0;
                $oPluginEinstellungenConf->cName            = $cName_arr[$z];
                $oPluginEinstellungenConf->cBeschreibung    = $cBeschreibung_arr[$z];
                $oPluginEinstellungenConf->cWertName        = $cModulId . '_' . $cWertName_arr[$z];
                $oPluginEinstellungenConf->cInputTyp        = 'zahl';
                $oPluginEinstellungenConf->nSort            = $nSort_arr[$z];
                $oPluginEinstellungenConf->cConf            = 'Y';

                $this->db->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
            }

            if (isset($Method_arr['Setting'])
                && \is_array($Method_arr['Setting'])
                && \count($Method_arr['Setting']) > 0
            ) {
                $cTyp          = '';
                $cInitialValue = '';
                $nSort         = 0;
                $cConf         = 'Y';
                $multiple      = false;
                foreach ($Method_arr['Setting'] as $j => $Setting_arr) {
                    \preg_match('/[0-9]+\sattr/', $j, $cTreffer3_arr);
                    \preg_match('/[0-9]+/', $j, $cTreffer4_arr);

                    if (isset($cTreffer3_arr[0]) && \strlen($cTreffer3_arr[0]) === \strlen($j)) {
                        $cTyp          = $Setting_arr['type'];
                        $multiple      = (isset($Setting_arr['multiple']) && $Setting_arr['multiple'] === 'Y' && $cTyp === 'selectbox');
                        $cInitialValue = ($multiple === true)
                            ? serialize([$Setting_arr['initialValue']])
                            : $Setting_arr['initialValue'];
                        $nSort         = $Setting_arr['sort'];
                        $cConf         = $Setting_arr['conf'];
                    } elseif (\strlen($cTreffer4_arr[0]) === \strlen($j)) {
                        // tplugineinstellungen füllen
                        $oPluginEinstellungen          = new \stdClass();
                        $oPluginEinstellungen->kPlugin = $kPlugin;
                        $oPluginEinstellungen->cName   = $cModulId . '_' . $Setting_arr['ValueName'];
                        $oPluginEinstellungen->cWert   = $cInitialValue;
                        if ($this->db->select('tplugineinstellungen', 'cName', $oPluginEinstellungen->cName) !== null) {
                            $this->db->update('tplugineinstellungen', 'cName', $oPluginEinstellungen->cName, $oPluginEinstellungen);
                        } else {
                            $this->db->insert('tplugineinstellungen', $oPluginEinstellungen);
                        }

                        // tplugineinstellungenconf füllen
                        $oPluginEinstellungenConf                   = new \stdClass();
                        $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                        $oPluginEinstellungenConf->kPluginAdminMenu = 0;
                        $oPluginEinstellungenConf->cName            = $Setting_arr['Name'];
                        $oPluginEinstellungenConf->cBeschreibung    = (!isset($Setting_arr['Description']) || \is_array($Setting_arr['Description']))
                            ? ''
                            : $Setting_arr['Description'];
                        $oPluginEinstellungenConf->cWertName = $cModulId . '_' . $Setting_arr['ValueName'];
                        $oPluginEinstellungenConf->cInputTyp = $cTyp;
                        $oPluginEinstellungenConf->nSort     = $nSort;
                        $oPluginEinstellungenConf->cConf     = ($cTyp === 'selectbox' && $multiple === true)
                            ? 'M'
                            : $cConf;

                        if (($kPluginEinstellungenConfTMP = $this->db->select('tplugineinstellungenconf', 'cWertName', $oPluginEinstellungenConf->cWertName)) !== null) {
                            $this->db->update(
                                'tplugineinstellungenconf',
                                'cWertName',
                                $oPluginEinstellungenConf->cWertName,
                                $oPluginEinstellungenConf
                            );
                            $kPluginEinstellungenConf = $kPluginEinstellungenConfTMP->kPluginEinstellungenConf;
                        } else {
                            $kPluginEinstellungenConf = $this->db->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                        }
                        // tplugineinstellungenconfwerte füllen
                        if ($kPluginEinstellungenConf <= 0) {
                            return 11; // Eine Einstellung der Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
                        }
                        // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                        if ($cTyp === 'selectbox') {
                            if (isset($Setting_arr['OptionsSource'])
                                && \is_array($Setting_arr['OptionsSource'])
                                && \count($Setting_arr['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (\count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                // Es gibt mehr als eine Option
                                foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                    \preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);

                                    if (isset($cTreffer6_arr[0]) && \strlen($cTreffer6_arr[0]) === \strlen($y)) {
                                        $cWert = $Option_arr['value'];
                                        $nSort = $Option_arr['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                        $oPluginEinstellungenConfWerte                           = new \stdClass();
                                        $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $oPluginEinstellungenConfWerte->cName                    = $cName;
                                        $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                        $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                        $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                    }
                                }
                            } elseif (\count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                // Es gibt nur eine Option
                                $oPluginEinstellungenConfWerte                           = new \stdClass();
                                $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['SelectboxOptions'][0]['Option'];
                                $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['value'];
                                $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                            }
                        } elseif ($cTyp === 'radio') {
                            if (isset($Setting_arr['OptionsSource'])
                                && \is_array($Setting_arr['OptionsSource'])
                                && \count($Setting_arr['OptionsSource']) > 0
                            ) {
                                //do nothing for now
                            } elseif (\count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                    \preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);
                                    if (\strlen($cTreffer6_arr[0]) === \strlen($y)) {
                                        $cWert = $Option_arr['value'];
                                        $nSort = $Option_arr['sort'];
                                        $yx    = \substr($y, 0, \strpos($y, ' '));
                                        $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                        $oPluginEinstellungenConfWerte                           = new \stdClass();
                                        $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $oPluginEinstellungenConfWerte->cName                    = $cName;
                                        $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                        $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                        $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                    }
                                }
                            } elseif (\count($Setting_arr['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                                $oPluginEinstellungenConfWerte                           = new \stdClass();
                                $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['RadioOptions'][0]['Option'];
                                $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['RadioOptions'][0]['Option attr']['value'];
                                $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['RadioOptions'][0]['Option attr']['sort'];

                                $this->db->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                            }
                        }
                    }
                }
            }
        }
        // tboxvorlage füllen
        foreach ($boxesNode as $h => $Box_arr) {
            \preg_match('/[0-9]+/', $h, $cTreffer3_arr);
            if (\strlen($cTreffer3_arr[0]) === \strlen($h)) {
                $oBoxvorlage              = new \stdClass();
                $oBoxvorlage->kCustomID   = $kPlugin;
                $oBoxvorlage->eTyp        = 'plugin';
                $oBoxvorlage->cName       = $Box_arr['Name'];
                $oBoxvorlage->cVerfuegbar = $Box_arr['Available'];
                $oBoxvorlage->cTemplate   = $Box_arr['TemplateFile'];

                $kBoxvorlage = $this->db->insert('tboxvorlage', $oBoxvorlage);

                if (!$kBoxvorlage) {
                    return 13; //Eine Boxvorlage konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
        // tplugintemplate füllen
        foreach ($cTemplate_arr as $cTemplate) {
            \preg_match("/[a-zA-Z0-9\/_\-]+\.tpl/", $cTemplate, $cTreffer3_arr);
            if (\strlen($cTreffer3_arr[0]) === \strlen($cTemplate)) {
                $oPluginTemplate            = new \stdClass();
                $oPluginTemplate->kPlugin   = $kPlugin;
                $oPluginTemplate->cTemplate = $cTemplate;

                $kPluginTemplate = $this->db->insert('tplugintemplate', $oPluginTemplate);

                if (!$kPluginTemplate) {
                    return 17; //Ein Template konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
        // Emailtemplates (falls vorhanden)
        foreach ($mailNode as $u => $Template_arr) {
            \preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);

            $oTemplate = new \stdClass();
            if (\strlen($cTreffer2_arr[0]) !== \strlen($u)) {
                continue;
            }
            $oTemplate->kPlugin       = $kPlugin;
            $oTemplate->cName         = $Template_arr['Name'];
            $oTemplate->cBeschreibung = \is_array($Template_arr['Description'])
                ? $Template_arr['Description'][0]
                : $Template_arr['Description'];
            $oTemplate->cMailTyp      = $Template_arr['Type'] ?? 'text/html';
            $oTemplate->cModulId      = $Template_arr['ModulId'];
            $oTemplate->cDateiname    = $Template_arr['Filename'] ?? null;
            $oTemplate->cAktiv        = $Template_arr['Active'] ?? 'N';
            $oTemplate->nAKZ          = $Template_arr['AKZ'] ?? 0;
            $oTemplate->nAGB          = $Template_arr['AGB'] ?? 0;
            $oTemplate->nWRB          = $Template_arr['WRB'] ?? 0;
            $oTemplate->nWRBForm      = $Template_arr['WRBForm'] ?? 0;
            $oTemplate->nDSE          = $Template_arr['DSE'] ?? 0;
            // tpluginemailvorlage füllen
            $kEmailvorlage = $this->db->insert('tpluginemailvorlage', $oTemplate);

            if ($kEmailvorlage <= 0) {
                return 14; //Eine Emailvorlage konnte nicht in die Datenbank gespeichert werden
            }
            $oTemplateSprache                = new \stdClass();
            $cISOSprache                     = '';
            $oTemplateSprache->kEmailvorlage = $kEmailvorlage;
            // Hole alle Sprachen des Shops
            // Assoc cISO
            $oSprachAssoc_arr = \Sprache::getAllLanguages(2);
            // Ist das erste Standard Template gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bTemplateStandard   = false;
            $oTemplateSpracheStd = new \stdClass();
            foreach ($Template_arr['TemplateLanguage'] as $l => $TemplateLanguage_arr) {
                \preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                \preg_match('/[0-9]+/', $l, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($l)) {
                    $cISOSprache = \strtolower($TemplateLanguage_arr['iso']);
                } elseif (isset($cTreffer2_arr[0]) && \strlen($cTreffer2_arr[0]) === \strlen($l)) {
                    // tpluginemailvorlagesprache füllen
                    $oTemplateSprache->kEmailvorlage = $kEmailvorlage;
                    $oTemplateSprache->kSprache      = $oSprachAssoc_arr[$cISOSprache]->kSprache;
                    $oTemplateSprache->cBetreff      = $TemplateLanguage_arr['Subject'];
                    $oTemplateSprache->cContentHtml  = $TemplateLanguage_arr['ContentHtml'];
                    $oTemplateSprache->cContentText  = $TemplateLanguage_arr['ContentText'];
                    $oTemplateSprache->cPDFS         = $TemplateLanguage_arr['PDFS'] ?? null;
                    $oTemplateSprache->cDateiname    = $TemplateLanguage_arr['Filename'] ?? null;

                    if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
                        $this->db->insert('tpluginemailvorlagesprache', $oTemplateSprache);
                    }

                    $this->db->insert('tpluginemailvorlagespracheoriginal', $oTemplateSprache);
                    // Erste Templatesprache vom Plugin als Standard setzen
                    if (!$bTemplateStandard) {
                        $oTemplateSpracheStd = $oTemplateSprache;
                        $bTemplateStandard   = true;
                    }

                    if (isset($oSprachAssoc_arr[$cISOSprache])) {
                        // Resette aktuelle Sprache
                        unset($oSprachAssoc_arr[$cISOSprache]);
                        $oSprachAssoc_arr = \array_merge($oSprachAssoc_arr);
                    }
                }
            }
            // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
            if (\count($oSprachAssoc_arr) > 0) {
                foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                    //$oSprache = $oSprachAssoc;
                    if ($oSprachAssoc->kSprache > 0) {
                        // tpluginemailvorlagesprache füllen
                        $oTemplateSpracheStd->kSprache = $oSprachAssoc->kSprache;

                        if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
                            $this->db->insert('tpluginemailvorlagesprache', $oTemplateSpracheStd);
                        }

                        $this->db->insert('tpluginemailvorlagespracheoriginal', $oTemplateSpracheStd);
                    }
                }
            }
        }
        // tpluginsprachvariable + tpluginsprachvariablesprache füllen
        $oSprachStandardAssoc_arr = \Sprache::getAllLanguages(2);
        foreach ($localeNode as $t => $Variable_arr) {
            $oSprachAssoc_arr = $oSprachStandardAssoc_arr;
            \preg_match('/[0-9]+/', $t, $cTreffer1_arr);
            if (\strlen($cTreffer1_arr[0]) !== \strlen($t)) {
                continue;
            }
            // tpluginsprachvariable füllen
            $oPluginSprachVariable          = new \stdClass();
            $oPluginSprachVariable->kPlugin = $kPlugin;
            $oPluginSprachVariable->cName   = $Variable_arr['Name'];
            if (isset($Variable_arr['Description']) && \is_array($Variable_arr['Description'])) {
                $oPluginSprachVariable->cBeschreibung = '';
            } else {
                $oPluginSprachVariable->cBeschreibung = \preg_replace('/\s+/', ' ', $Variable_arr['Description']);
            }

            $kPluginSprachvariable = $this->db->insert('tpluginsprachvariable', $oPluginSprachVariable);

            if ($kPluginSprachvariable <= 0) {
                return 7; // Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
            }
            // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
            // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
            // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
            $bVariableStandard   = false;
            $oVariableSpracheStd = new \stdClass();
            // Nur eine Sprache vorhanden
            if (isset($Variable_arr['VariableLocalized attr'])
                && \is_array($Variable_arr['VariableLocalized attr'])
                && \count($Variable_arr['VariableLocalized attr']) > 0
            ) {
                // tpluginsprachvariablesprache füllen
                $oPluginSprachVariableSprache                        = new \stdClass();
                $oPluginSprachVariableSprache->kPluginSprachvariable = $kPluginSprachvariable;
                $oPluginSprachVariableSprache->cISO                  = $Variable_arr['VariableLocalized attr']['iso'];
                $oPluginSprachVariableSprache->cName                 = \preg_replace('/\s+/', ' ', $Variable_arr['VariableLocalized']);

                $this->db->insert('tpluginsprachvariablesprache', $oPluginSprachVariableSprache);

                // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                if (!$bVariableStandard) {
                    $oVariableSpracheStd = $oPluginSprachVariableSprache;
                    $bVariableStandard   = true;
                }

                if (isset($oSprachAssoc_arr[\strtolower($oPluginSprachVariableSprache->cISO)])) {
                    // Resette aktuelle Sprache
                    unset($oSprachAssoc_arr[\strtolower($oPluginSprachVariableSprache->cISO)]);
                    $oSprachAssoc_arr = \array_merge($oSprachAssoc_arr);
                }
            } elseif (isset($Variable_arr['VariableLocalized'])
                && \is_array($Variable_arr['VariableLocalized'])
                && \count($Variable_arr['VariableLocalized']) > 0
            ) {
                // Mehr Sprachen vorhanden
                foreach ($Variable_arr['VariableLocalized'] as $i => $VariableLocalized_arr) {
                    \preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);

                    if (isset($cTreffer1_arr[0]) && \strlen($cTreffer1_arr[0]) === \strlen($i)) {
                        $cISO = $VariableLocalized_arr['iso'];
                        //$yx = \substr($i, 0, 1);
                        $yx    = \substr($i, 0, \strpos($i, ' '));
                        $cName = $Variable_arr['VariableLocalized'][$yx];
                        // tpluginsprachvariablesprache füllen
                        $oPluginSprachVariableSprache                        = new \stdClass();
                        $oPluginSprachVariableSprache->kPluginSprachvariable = $kPluginSprachvariable;
                        $oPluginSprachVariableSprache->cISO                  = $cISO;
                        $oPluginSprachVariableSprache->cName                 = \preg_replace('/\s+/', ' ', $cName);

                        $this->db->insert('tpluginsprachvariablesprache', $oPluginSprachVariableSprache);
                        // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                        if (!$bVariableStandard) {
                            $oVariableSpracheStd = $oPluginSprachVariableSprache;
                            $bVariableStandard   = true;
                        }

                        if (isset($oSprachAssoc_arr[\strtolower($oPluginSprachVariableSprache->cISO)])) {
                            // Resette aktuelle Sprache

                            unset($oSprachAssoc_arr[\strtolower($oPluginSprachVariableSprache->cISO)]);
                            $oSprachAssoc_arr = \array_merge($oSprachAssoc_arr);
                        }
                    }
                }
            }
            // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
            if (\count($oSprachAssoc_arr) > 0) {
                foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                    $oVariableSpracheStd->cISO = \strtoupper($oSprachAssoc->cISO);
                    $kPluginSprachVariableTMP  = $this->db->insert('tpluginsprachvariablesprache', $oVariableSpracheStd);
                    if (!$kPluginSprachVariableTMP) {
                        return 7; // Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
        }
        // CheckBox tcheckboxfunktion fuellen
        foreach ($checkboxesNode as $t => $Function_arr) {
            \preg_match('/[0-9]+/', $t, $cTreffer2_arr);
            if (\strlen($cTreffer2_arr[0]) === \strlen($t)) {
                $oCheckBoxFunktion          = new \stdClass();
                $oCheckBoxFunktion->kPlugin = $kPlugin;
                $oCheckBoxFunktion->cName   = $Function_arr['Name'];
                $oCheckBoxFunktion->cID     = $oPlugin->cPluginID . '_' . $Function_arr['ID'];
                $this->db->insert('tcheckboxfunktion', $oCheckBoxFunktion);
            }
        }
        // AdminWidgets tadminwidgets fuellen
        foreach ($widgetsNode as $u => $Widget_arr) {
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);
            if (\strlen($cTreffer2_arr[0]) !== \strlen($u)) {
                continue;
            }
            $oAdminWidget               = new \stdClass();
            $oAdminWidget->kPlugin      = $kPlugin;
            $oAdminWidget->cTitle       = $Widget_arr['Title'];
            $oAdminWidget->cClass       = $Widget_arr['Class'] . '_' . $oPlugin->cPluginID;
            $oAdminWidget->eContainer   = $Widget_arr['Container'];
            $oAdminWidget->cDescription = $Widget_arr['Description'];
            if (\is_array($oAdminWidget->cDescription)) {
                //@todo: when description is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                $oAdminWidget->cDescription = $oAdminWidget->cDescription[0];
            }
            $oAdminWidget->nPos      = $Widget_arr['Pos'];
            $oAdminWidget->bExpanded = $Widget_arr['Expanded'];
            $oAdminWidget->bActive   = $Widget_arr['Active'];
            $kWidget                 = $this->db->insert('tadminwidgets', $oAdminWidget);

            if (!$kWidget) {
                return 15;// Ein AdminWidget konnte nicht in die Datenbank gespeichert werden
            }
        }
        // OPC-Portlets in topcportlet fuellen
        foreach ($portletsNode as $u => $Portlet_arr) {
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);

            if (\strlen($cTreffer2_arr[0]) === \strlen($u)) {
                $oPortlet = (object)[
                    'kPlugin' => (int)$kPlugin,
                    'cTitle'  => $Portlet_arr['Title'],
                    'cClass'  => $Portlet_arr['Class'],
                    'cGroup'  => $Portlet_arr['Group'],
                    'bActive' => (int)$Portlet_arr['Active'],
                ];

                $kPortlet = $this->db->insert('topcportlet', $oPortlet);

                if (!$kPortlet) {
                    return 19;// Ein OPC Portlet konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
        // OPC-Blueprints in topcblueprints fuellen
        foreach ($blueprintsNode as $u => $blueprint) {
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);
            if (\strlen($cTreffer2_arr[0]) === \strlen($u)) {
                $blueprintJson = \file_get_contents(
                    \PFAD_ROOT . \PFAD_PLUGIN . $cVerzeichnis . '/' . \PFAD_PLUGIN_VERSION
                    . $nVersion . '/' . \PFAD_PLUGIN_ADMINMENU . \PFAD_PLUGIN_BLUEPRINTS . $blueprint['JSONFile']
                );

                $blueprintData = \json_decode($blueprintJson, true);
                $instanceJson  = \json_encode($blueprintData['instance']);

                $blueprintObj = (object)[
                    'kPlugin' => (int)$kPlugin,
                    'cName'   => $blueprint['Name'],
                    'cJson'   => $instanceJson,
                ];

                $kBlueprint = $this->db->insert('topcblueprint', $blueprintObj);

                if (!$kBlueprint) {
                    // Ein OPC Blueprint konnte nicht in die Datenbank gespeichert werden
                    return 20;
                }
            }
        }
        // ExportFormate in texportformat fuellen
        foreach ($exportNode as $u => $Format_arr) {
            \preg_match('/[0-9]+/', $u, $cTreffer2_arr);
            if (\strlen($cTreffer2_arr[0]) !== \strlen($u)) {
                continue;
            }
            $oExportformat                   = new \stdClass();
            $oExportformat->kKundengruppe    = $kKundengruppeStd;
            $oExportformat->kSprache         = $kSpracheStd;
            $oExportformat->kWaehrung        = $kWaehrungStd;
            $oExportformat->kKampagne        = 0;
            $oExportformat->kPlugin          = $kPlugin;
            $oExportformat->cName            = $Format_arr['Name'];
            $oExportformat->cDateiname       = $Format_arr['FileName'];
            $oExportformat->cKopfzeile       = $Format_arr['Header'];
            $oExportformat->cContent         = (isset($Format_arr['Content']) && \strlen($Format_arr['Content']) > 0)
                ? $Format_arr['Content']
                : 'PluginContentFile_' . $Format_arr['ContentFile'];
            $oExportformat->cFusszeile       = $Format_arr['Footer'] ?? null;
            $oExportformat->cKodierung       = $Format_arr['Encoding'] ?? 'ASCII';
            $oExportformat->nSpecial         = 0;
            $oExportformat->nVarKombiOption  = $Format_arr['VarCombiOption'] ?? 1;
            $oExportformat->nSplitgroesse    = $Format_arr['SplitSize'] ?? 0;
            $oExportformat->dZuletztErstellt = '_DBNULL_';
            if (\is_array($oExportformat->cKopfzeile)) {
                //@todo: when cKopfzeile is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                $oExportformat->cKopfzeile = $oExportformat->cKopfzeile[0];
            }
            if (\is_array($oExportformat->cContent)) {
                $oExportformat->cContent = $oExportformat->cContent[0];
            }
            if (\is_array($oExportformat->cFusszeile)) {
                $oExportformat->cFusszeile = $oExportformat->cFusszeile[0];
            }
            $kExportformat = $this->db->insert('texportformat', $oExportformat);

            if (!$kExportformat) {
                return 16;// Ein Exportformat konnte nicht in die Datenbank gespeichert werden
            }
            // Einstellungen
            // <OnlyStockGreaterZero>N</OnlyStockGreaterZero> => exportformate_lager_ueber_null
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_lager_ueber_null';
            $oExportformatEinstellungen->cWert         = \strlen($Format_arr['OnlyStockGreaterZero']) !== 0
                ? $Format_arr['OnlyStockGreaterZero']
                : 'N';
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
            // <OnlyPriceGreaterZero>N</OnlyPriceGreaterZero> => exportformate_preis_ueber_null
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_preis_ueber_null';
            $oExportformatEinstellungen->cWert         = $Format_arr['OnlyPriceGreaterZero'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
            // <OnlyProductsWithDescription>N</OnlyProductsWithDescription> => exportformate_beschreibung
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_beschreibung';
            $oExportformatEinstellungen->cWert         = $Format_arr['OnlyProductsWithDescription'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
            // <ShippingCostsDeliveryCountry>DE</ShippingCostsDeliveryCountry> => exportformate_lieferland
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_lieferland';
            $oExportformatEinstellungen->cWert         = $Format_arr['ShippingCostsDeliveryCountry'];
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
            // <EncodingQuote>N</EncodingQuote> => exportformate_quot
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_quot';
            $oExportformatEinstellungen->cWert         = $Format_arr['EncodingQuote'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
            // <EncodingDoubleQuote>N</EncodingDoubleQuote> => exportformate_equot
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_equot';
            $oExportformatEinstellungen->cWert         = $Format_arr['EncodingDoubleQuote'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
            // <EncodingSemicolon>N</EncodingSemicolon> => exportformate_semikolon
            $oExportformatEinstellungen                = new \stdClass();
            $oExportformatEinstellungen->kExportformat = $kExportformat;
            $oExportformatEinstellungen->cName         = 'exportformate_semikolon';
            $oExportformatEinstellungen->cWert         = $Format_arr['EncodingSemicolon'] === 'Y'
                ? 'Y'
                : 'N';
            $this->db->insert('texportformateinstellungen', $oExportformatEinstellungen);
        }
        // Resourcen in tplugin_ressources fuellen
        foreach ($cssNode as $file) {
            if (isset($file['name'])) {
                $oFile          = new \stdClass();
                $oFile->kPlugin = $kPlugin;
                $oFile->type    = 'css';
                $oFile->path     = $file['name'];
                $oFile->priority = $file['priority'] ?? 5;
                $this->db->insert('tplugin_resources', $oFile);
                unset($oFile);
            }
        }
        foreach ($jsNode as $file) {
            if (isset($file['name'])) {
                $oFile          = new \stdClass();
                $oFile->kPlugin = $kPlugin;
                $oFile->type    = 'js';
                $oFile->path     = $file['name'];
                $oFile->priority = $file['priority'] ?? 5;
                $oFile->position = $file['position'] ?? 'head';
                $this->db->insert('tplugin_resources', $oFile);
                unset($oFile);
            }
        }

        return 0;
    }
}
