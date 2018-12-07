<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Installation\Items;

use DB\ReturnType;
use JTL\SeoHelper;
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
        $pluginID    = $this->plugin->kPlugin;
        $oldPluginID = $this->oldPlugin->kPlugin ?? 0;
        foreach ($this->getNode() as $i => $links) {
            $i = (string)$i;
            \preg_match('/[0-9]+\sattr/', $i, $hits1);
            \preg_match('/[0-9]+/', $i, $hits2);
            if (\strlen($hits2[0]) !== \strlen($i)) {
                continue;
            }
            if (empty($links['LinkGroup'])) {
                $links['LinkGroup'] = 'hidden'; // linkgroup not set? default to 'hidden'
            }
            $linkGroupID = $this->getLinkGroup($links['LinkGroup']);
            if ($linkGroupID === 0) {
                return InstallCode::SQL_CANNOT_FIND_LINK_GROUP;
            }
            $linkID = $this->addLink($pluginID, $links);
            if ($linkID <= 0) {
                return InstallCode::SQL_CANNOT_SAVE_LINK;
            }
            $this->db->insert(
                'tlinkgroupassociations',
                (object)['linkGroupID' => $linkGroupID, 'linkID' => $linkID]
            );
            $allLanguages    = \Sprache::getAllLanguages(2);
            $linkLang        = new \stdClass();
            $linkLang->kLink = $linkID;
            $bLinkStandard   = false;
            $defaultLang     = new \stdClass();
            $oldLinkID       = $oldPluginID === 0
                ? null
                : $this->db->select('tlink', 'kPlugin', $oldPluginID, 'cName', $links['Name']);
            foreach ($links['LinkLanguage'] as $l => $localized) {
                $l = (string)$l;
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    $linkLang->cISOSprache = \strtolower($localized['iso']);
                } elseif (\strlen($hits2[0]) === \strlen($l)) {
                    $linkLang->cSeo             = SeoHelper::checkSeo(SeoHelper::getSeo($localized['Seo']));
                    $linkLang->cName            = $localized['Name'];
                    $linkLang->cTitle           = $localized['Title'];
                    $linkLang->cContent         = '';
                    $linkLang->cMetaTitle       = $localized['MetaTitle'];
                    $linkLang->cMetaKeywords    = $localized['MetaKeywords'];
                    $linkLang->cMetaDescription = $localized['MetaDescription'];
                    $this->db->insert('tlinksprache', $linkLang);
                    if (!$bLinkStandard) {
                        $defaultLang   = $linkLang;
                        $bLinkStandard = true;
                    }
                    if ($allLanguages[$linkLang->cISOSprache]->kSprache > 0) {
                        $or = isset($oldLinkID->kLink) ? (' OR kKey = ' . (int)$oldLinkID->kLink) : '';
                        $this->db->query(
                            "DELETE FROM tseo
                                WHERE cKey = 'kLink'
                                    AND (kKey = " . $linkID . $or . ")
                                    AND kSprache = " . (int)$allLanguages[$linkLang->cISOSprache]->kSprache,
                            ReturnType::DEFAULT
                        );
                        $seo           = new \stdClass();
                        $seo->cSeo     = SeoHelper::checkSeo(SeoHelper::getSeo($localized['Seo']));
                        $seo->cKey     = 'kLink';
                        $seo->kKey     = $linkID;
                        $seo->kSprache = $allLanguages[$linkLang->cISOSprache]->kSprache;
                        $this->db->insert('tseo', $seo);
                    }
                    if (isset($allLanguages[$linkLang->cISOSprache])) {
                        unset($allLanguages[$linkLang->cISOSprache]);
                        $allLanguages = \array_merge($allLanguages);
                    }
                }
            }
            if (!$this->addHook($pluginID)) {
                return InstallCode::SQL_CANNOT_SAVE_HOOK;
            }
            $this->addMissingTranslations($allLanguages, $defaultLang, $linkID);
            $this->addLinkFile($pluginID, $linkID, $links);
        }

        return InstallCode::OK;
    }

    /**
     * Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
     *
     * @param array     $languages
     * @param \stdClass $defaultLang
     * @param int       $linkID
     */
    private function addMissingTranslations(array $languages, \stdClass $defaultLang, int $linkID): void
    {
        foreach ($languages as $language) {
            if ($language->kSprache <= 0) {
                continue;
            }
            $this->db->delete(
                'tseo',
                ['cKey', 'kKey', 'kSprache'],
                ['kLink', $linkID, (int)$language->kSprache]
            );
            $seo           = new \stdClass();
            $seo->cSeo     = \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($defaultLang->cSeo));
            $seo->cKey     = 'kLink';
            $seo->kKey     = $linkID;
            $seo->kSprache = $language->kSprache;
            $this->db->insert('tseo', $seo);
            $defaultLang->cSeo        = $seo->cSeo;
            $defaultLang->cISOSprache = $language->cISO;
            $this->db->insert('tlinksprache', $defaultLang);
        }
    }

    /**
     * @param int   $pluginID
     * @param int   $linkID
     * @param array $links
     * @return int
     */
    private function addLinkFile(int $pluginID, int $linkID, array $links): int
    {
        $linkFile                      = new \stdClass();
        $linkFile->kPlugin             = $pluginID;
        $linkFile->kLink               = $linkID;
        $linkFile->cDatei              = $links['Filename'];
        $linkFile->cTemplate           = $links['Template'] ?? '_DBNULL_';
        $linkFile->cFullscreenTemplate = $links['FullscreenTemplate'] ?? '_DBNULL_';

        return $this->db->insert('tpluginlinkdatei', $linkFile);
    }

    /**
     * @param int   $pluginID
     * @param array $links
     * @return int
     */
    private function addLink(int $pluginID, array $links): int
    {
        $link                     = new \stdClass();
        $link->kPlugin            = $pluginID;
        $link->cName              = $links['Name'];
        $link->nLinkart           = \LINKTYP_PLUGIN;
        $link->cSichtbarNachLogin = $links['VisibleAfterLogin'];
        $link->cDruckButton       = $links['PrintButton'];
        $link->cNoFollow          = $links['NoFollow'] ?? null;
        $link->nSort              = \LINKTYP_PLUGIN;
        $link->bSSL               = (int)($links['SSL'] ?? 0);

        return $this->db->insert('tlink', $link);
    }

    /**
     * @param int $pluginID
     * @return int
     */
    private function addHook(int $pluginID): int
    {
        $hook             = new \stdClass();
        $hook->kPlugin    = $pluginID;
        $hook->nHook      = \HOOK_SEITE_PAGE_IF_LINKART;
        $hook->cDateiname = \PLUGIN_SEITENHANDLER;

        return $this->db->insert('tpluginhook', $hook);
    }

    /**
     * @param string $name
     * @return int
     */
    private function getLinkGroup(string $name): int
    {
        $linkGroup = $this->db->select('tlinkgruppe', 'cName', $name);
        if ($linkGroup === null) {
            $linkGroup                = new \stdClass();
            $linkGroup->cName         = $name;
            $linkGroup->cTemplatename = $name;
            $linkGroup->kLinkgruppe   = $this->db->insert('tlinkgruppe', $linkGroup);
        }

        return $linkGroup->kLinkgruppe > 0
            ? (int)$linkGroup->kLinkgruppe
            : 0;
    }
}
