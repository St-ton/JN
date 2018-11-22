<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use Plugin\InstallCode;

/**
 * Class FrontendLinks
 * @package Plugin\Admin\Installation\Items
 */
class FrontendLinks extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function getNode(): array
    {
        return isset($this->baseNode['Install'][0]['FrontendLink'][0]['Link'])
        && \is_array($this->baseNode['Install'][0]['FrontendLink'][0]['Link'])
            ? $this->baseNode['Install'][0]['FrontendLink'][0]['Link']
            : [];
    }

    /**
     * @inheritdoc
     */
    public function install(): int
    {
        foreach ($this->getNode() as $u => $links) {
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
}
