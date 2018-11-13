<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use DB\DbInterface;

/**
 * Class PluginLoader
 * @package Plugin
 */
class PluginLoader
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * PluginLoader constructor.
     * @param Plugin      $plugin
     * @param DbInterface $db
     */
    public function __construct(Plugin $plugin, DbInterface $db)
    {
        $this->plugin = $plugin;
        $this->db     = $db;
    }

    /**
     *
     */
    public function loadHooks(): void
    {
        $this->plugin->oPluginHook_arr = \array_map(function ($hook) {
            $hook->kPluginHook = (int)$hook->kPluginHook;
            $hook->kPlugin     = (int)$hook->kPlugin;
            $hook->nHook       = (int)$hook->nHook;
            $hook->nPriority   = (int)$hook->nPriority;

            return $hook;
        }, $this->db->selectAll('tpluginhook', 'kPlugin', $this->plugin->kPlugin));
    }

    /**
     *
     */
    public function loadAdminMenu(): void
    {
        $this->plugin->oPluginAdminMenu_arr = \array_map(function ($menu) {
            $menu->kPluginAdminMenu = (int)$menu->kPluginAdminMenu;
            $menu->kPlugin          = (int)$menu->kPlugin;
            $menu->nSort            = (int)$menu->nSort;
            $menu->nConf            = (int)$menu->nConf;

            return $menu;
        }, $this->db->selectAll('tpluginadminmenu', 'kPlugin', $this->plugin->kPlugin, '*', 'nSort'));
    }

    /**
     *
     */
    public function loadPaths(): void
    {
        $shopURL                            = \Shop::getURL();
        $shopURLSSL                         = \Shop::getURL(true);
        $basePath                           = \PFAD_ROOT . \PFAD_PLUGIN;
        $versioned                          = $this->plugin->cVerzeichnis . '/' .
            \PFAD_PLUGIN_VERSION . $this->plugin->nVersion . '/';
        $pluginBase                         = \PFAD_PLUGIN . $versioned;
        $this->plugin->cPluginPfad          = $basePath . $versioned;
        $this->plugin->cFrontendPfad        = $this->plugin->cPluginPfad . \PFAD_PLUGIN_FRONTEND;
        $this->plugin->cFrontendPfadURL     = $shopURL . '/' . $pluginBase . \PFAD_PLUGIN_FRONTEND; // deprecated
        $this->plugin->cFrontendPfadURLSSL  = $shopURLSSL . '/' . $pluginBase . \PFAD_PLUGIN_FRONTEND;
        $this->plugin->cAdminmenuPfad       = $this->plugin->cPluginPfad . \PFAD_PLUGIN_ADMINMENU;
        $this->plugin->cAdminmenuPfadURL    = $shopURL . '/' . $pluginBase . \PFAD_PLUGIN_ADMINMENU;
        $this->plugin->cAdminmenuPfadURLSSL = $shopURLSSL . '/' . $pluginBase . \PFAD_PLUGIN_ADMINMENU;
        $this->plugin->cLicencePfad         = $this->plugin->cPluginPfad . \PFAD_PLUGIN_LICENCE;
        $this->plugin->cLicencePfadURL      = $shopURL . '/' . $pluginBase . \PFAD_PLUGIN_LICENCE;
        $this->plugin->cLicencePfadURLSSL   = $shopURLSSL . '/' . $pluginBase . \PFAD_PLUGIN_LICENCE;
    }

    /**
     *
     */
    public function loadMarkdownFiles(): void
    {
        $szPluginMainPath = \PFAD_ROOT . \PFAD_PLUGIN . $this->plugin->cVerzeichnis . '/';
        if ($this->plugin->cTextReadmePath === '' && $this->checkFileExistence($szPluginMainPath . 'README.md')) {
            $this->plugin->cTextReadmePath = $szPluginMainPath . 'README.md';
        }
        if ($this->plugin->changelogPath === '' && $this->checkFileExistence($szPluginMainPath . 'CHANGELOG.md')) {
            $this->plugin->changelogPath = $szPluginMainPath . 'CHANGELOG.md';
        }
        if ($this->plugin->cTextLicensePath === '') {
            foreach (['license.md', 'License.md', 'LICENSE.md'] as $licenseName) {
                if ($this->checkFileExistence($licenseName)) {
                    $this->plugin->cTextLicensePath = $szPluginMainPath . $licenseName;
                    break;
                }
            }
        }
    }

    /**
     *
     */
    public function loadConfig(): void
    {
        $this->plugin->oPluginEinstellung_arr = $this->db->query(
            'SELECT tplugineinstellungen.*, tplugineinstellungenconf.cConf
                FROM tplugineinstellungen
                LEFT JOIN tplugineinstellungenconf
                    ON tplugineinstellungenconf.kPlugin = tplugineinstellungen.kPlugin
                    AND tplugineinstellungen.cName = tplugineinstellungenconf.cWertName
                WHERE tplugineinstellungen.kPlugin = ' . $this->plugin->kPlugin,
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($this->plugin->oPluginEinstellung_arr as $conf) {
            $conf->kPlugin = (int)$conf->kPlugin;
            if ($conf->cConf === 'M') {
                $conf->cWert = \unserialize($conf->cWert, ['allowed_classes' => false]);
            }
            unset($conf->cConf);
        }
        $tmpConf = $this->db->selectAll(
            'tplugineinstellungenconf',
            'kPlugin',
            $this->plugin->kPlugin,
            '*',
            'nSort'
        );
        foreach ($tmpConf as $i => $cfg) {
            $cfg->kPluginEinstellungenConf = (int)$cfg->kPluginEinstellungenConf;
            $cfg->kPlugin                  = (int)$cfg->kPlugin;
            $cfg->kPluginAdminMenu         = (int)$cfg->kPluginAdminMenu;
            $cfg->nSort                    = (int)$cfg->nSort;

            $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = [];
            if ($cfg->cInputTyp === 'selectbox' || $cfg->cInputTyp === 'radio') {
                if (!empty($cfg->cSourceFile)) {
                    $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = $this->plugin->getDynamicOptions($cfg);
                } else {
                    $confValues                                     = \array_map(function ($c) {
                        $c->kPluginEinstellungenConf = (int)$c->kPluginEinstellungenConf;
                        $c->nSort                    = (int)$c->nSort;

                        return $c;
                    }, $this->db->selectAll(
                        'tplugineinstellungenconfwerte',
                        'kPluginEinstellungenConf',
                        (int)$cfg->kPluginEinstellungenConf,
                        '*',
                        'nSort'
                    ));
                    $tmpConf[$i]->oPluginEinstellungenConfWerte_arr = $confValues;
                }
            }
        }
        $this->plugin->oPluginEinstellungConf_arr  = $tmpConf;
        $this->plugin->oPluginEinstellungAssoc_arr = PluginHelper::getConfigByID($this->plugin->kPlugin);
        $this->plugin->oPluginSprachvariable_arr   = PluginHelper::getLanguageVariables($this->plugin->kPlugin);
    }

    /**
     *
     */
    public function loadLocalization(): void
    {
        $iso = '';
        if (isset($_SESSION['cISOSprache']) && \strlen($_SESSION['cISOSprache']) > 0) {
            $iso = $_SESSION['cISOSprache'];
        } else {
            $oSprache = \Sprache::getDefaultLanguage();
            if ($oSprache !== null && \strlen($oSprache->cISO) > 0) {
                $iso = $oSprache->cISO;
            }
        }
        $this->plugin->oPluginSprachvariableAssoc_arr = PluginHelper::getLanguageVariablesByID(
            $this->plugin->kPlugin,
            $iso
        );
    }

    /**
     *
     */
    public function loadLinks(): void
    {
        $linkData = $this->db->queryPrepared(
            "SELECT tlink.*, tlinksprache.*, tsprache.kSprache 
                FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                WHERE tlink.kPlugin = :plgn",
            ['plgn' => $this->plugin->kPlugin],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $linkData = \Functional\group($linkData, function ($e) {
            return $e->kLink;
        });
        foreach ($linkData as $data) {
            $baseData                             = \Functional\first($data);
            $link                                 = new \stdClass();
            $link->kLink                          = (int)$baseData->kLink;
            $link->kVaterLink                     = (int)$baseData->kVaterLink;
            $link->kPlugin                        = (int)$baseData->kPlugin;
            $link->cName                          = $baseData->cName;
            $link->nLinkart                       = (int)$baseData->nLinkart;
            $link->cNoFollow                      = $baseData->cNoFollow;
            $link->cKundengruppen                 = $baseData->cKundengruppen;
            $link->cSichtbarNachLogin             = $baseData->cSichtbarNachLogin;
            $link->cDruckButton                   = $baseData->cDruckButton;
            $link->nSort                          = (int)$baseData->nSort;
            $link->bSSL                           = (int)$baseData->bSSL;
            $link->bIsFluid                       = (int)$baseData->bIsFluid;
            $link->cIdentifier                    = $baseData->cIdentifier;
            $link->bIsActive                      = (int)$baseData->bIsActive;
            $link->oPluginFrontendLinkSprache_arr = [];
            foreach ($data as $localizedData) {
                $localizedLink                          = new \stdClass();
                $localizedLink->kLink                   = (int)$localizedData->kLink;
                $localizedLink->kSprache                = (int)$localizedData->kSprache;
                $localizedLink->cSeo                    = $localizedData->cSeo;
                $localizedLink->cISOSprache             = $localizedData->cISOSprache;
                $localizedLink->cName                   = $localizedData->cName;
                $localizedLink->cTitle                  = $localizedData->cTitle;
                $localizedLink->cContent                = $localizedData->cContent;
                $localizedLink->cMetaTitle              = $localizedData->cMetaTitle;
                $localizedLink->cMetaKeywords           = $localizedData->cMetaKeywords;
                $localizedLink->cMetaDescription        = $localizedData->cMetaDescription;
                $link->oPluginFrontendLinkSprache_arr[] = $localizedLink;
            }
            $this->plugin->oPluginFrontendLink_arr[] = $link;
        }
    }

    /**
     *
     */
    public function loadPaymentMethods(): void
    {
        $methodsAssoc = [];
        $methods      = $this->db->query(
            "SELECT *
                FROM tzahlungsart
                WHERE cModulId LIKE 'kPlugin\_" . $this->plugin->kPlugin . "%'",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($methods as $method) {
            $method->cZusatzschrittTemplate          = \strlen($method->cZusatzschrittTemplate)
                ? \PFAD_ROOT . \PFAD_PLUGIN . $this->plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->plugin->nVersion . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $method->cZusatzschrittTemplate
                : '';
            $method->cTemplateFileURL                = \strlen($method->cPluginTemplate)
                ? \PFAD_ROOT . \PFAD_PLUGIN . $this->plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->plugin->nVersion . '/' .
                \PFAD_PLUGIN_PAYMENTMETHOD . $method->cPluginTemplate
                : '';
            $method->oZahlungsmethodeSprache_arr     = $this->db->selectAll(
                'tzahlungsartsprache',
                'kZahlungsart',
                (int)$method->kZahlungsart
            );
            $cModulId                                = PluginHelper::getModuleIDByPluginID(
                $this->plugin->kPlugin,
                $method->cName
            );
            $method->oZahlungsmethodeEinstellung_arr = $this->db->query(
                "SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $cModulId . "_%'
                        AND cConf = 'Y'
                    ORDER BY nSort",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $methodsAssoc[$method->cModulId]         = $method;
        }
        $this->plugin->oPluginZahlungsmethode_arr      = $methods;
        $this->plugin->oPluginZahlungsmethodeAssoc_arr = $methodsAssoc;
        $paymentMethodClasses                          = $this->db->selectAll(
            'tpluginzahlungsartklasse',
            'kPlugin',
            (int)$this->plugin->kPlugin
        );
        foreach ($paymentMethodClasses as $oZahlungsartKlasse) {
            if (isset($oZahlungsartKlasse->cModulId) && \strlen($oZahlungsartKlasse->cModulId) > 0) {
                $this->plugin->oPluginZahlungsKlasseAssoc_arr[$oZahlungsartKlasse->cModulId] = $oZahlungsartKlasse;
            }
        }
    }

    /**
     *
     */
    public function loadMailTemplates(): void
    {
        $mailTplAssoc = [];
        $mailTpls     = $this->db->selectAll('tpluginemailvorlage', 'kPlugin', (int)$this->plugin->kPlugin);
        foreach ($mailTpls as $i => $oPluginEmailvorlage) {
            $mailTpls[$i]->oPluginEmailvorlageSprache_arr = $this->db->selectAll(
                'tpluginemailvorlagesprache',
                'kEmailvorlage',
                (int)$oPluginEmailvorlage->kEmailvorlage
            );
            if (\is_array($mailTpls[$i]->oPluginEmailvorlageSprache_arr)
                && \count($mailTpls[$i]->oPluginEmailvorlageSprache_arr) > 0
            ) {
                $mailTpls[$i]->oPluginEmailvorlageSpracheAssoc_arr = [];
                foreach ($mailTpls[$i]->oPluginEmailvorlageSprache_arr as $oPluginEmailvorlageSprache) {
                    $mailTpls[$i]->oPluginEmailvorlageSpracheAssoc_arr[$oPluginEmailvorlageSprache->kSprache] =
                        $oPluginEmailvorlageSprache;
                }
            }
            $mailTplAssoc[$oPluginEmailvorlage->cModulId] = $mailTpls[$i];
        }

        $this->plugin->oPluginEmailvorlage_arr      = $mailTpls;
        $this->plugin->oPluginEmailvorlageAssoc_arr = $mailTplAssoc;
    }

    /**
     *
     */
    public function loadWidgets(): void
    {
        $this->plugin->oPluginAdminWidget_arr = $this->db->selectAll(
            'tadminwidgets',
            'kPlugin',
            (int)$this->plugin->kPlugin
        );
        foreach ($this->plugin->oPluginAdminWidget_arr as $i => $oPluginAdminWidget) {
            $this->plugin->oPluginAdminWidget_arr[$i]->cClassAbs                     = $this->plugin->cAdminmenuPfad .
                \PFAD_PLUGIN_WIDGET . 'class.Widget' .
                $oPluginAdminWidget->cClass . '.php';
            $this->plugin->oPluginAdminWidgetAssoc_arr[$oPluginAdminWidget->kWidget] =
                $this->plugin->oPluginAdminWidget_arr[$i];
        }
    }

    /**
     *
     */
    public function loadPortlets(): void
    {
        try {
            $this->plugin->oPluginEditorPortlet_arr = $this->db->selectAll(
                'topcportlet',
                'kPlugin',
                $this->plugin->kPlugin
            );
        } catch (\InvalidArgumentException $e) {
            $this->plugin->oPluginEditorPortlet_arr = [];
        }
        foreach ($this->plugin->oPluginEditorPortlet_arr as $i => $oPluginEditorPortlet) {
            $this->plugin->oPluginEditorPortlet_arr[$i]->cClassAbs = $this->plugin->cAdminmenuPfad .
                \PFAD_PLUGIN_PORTLETS . $oPluginEditorPortlet->cClass . '/' .
                $oPluginEditorPortlet->cClass . '.php';

            $this->plugin->oPluginEditorPortletAssoc_arr[$oPluginEditorPortlet->kPortlet] =
                $this->plugin->oPluginEditorPortlet_arr[$i];
        }
    }

    /**
     *
     */
    public function loadUninstall(): void
    {
        $this->plugin->oPluginUninstall = $this->db->select(
            'tpluginuninstall',
            'kPlugin',
            (int)$this->plugin->kPlugin
        );
        if ($this->plugin->oPluginUninstall !== null) {
            $this->plugin->cPluginUninstallPfad = \PFAD_ROOT . \PFAD_PLUGIN . $this->plugin->cVerzeichnis . '/' .
                \PFAD_PLUGIN_VERSION . $this->plugin->nVersion . '/' .
                \PFAD_PLUGIN_UNINSTALL . $this->plugin->oPluginUninstall->cDateiname;
        }
    }

    /**
     * perform a "search for a particular file" only once
     *
     * @param string $szCanonicalFileName - full path of the file to check
     * @return bool
     */
    private function checkFileExistence($szCanonicalFileName): bool
    {
        static $vChecked = [];
        if (!\array_key_exists($szCanonicalFileName, $vChecked)) {
            // only if we did not know that file (in our "remember-array"), we perform this check
            $vChecked[$szCanonicalFileName] = \file_exists($szCanonicalFileName); // do the actual check
        }

        return $vChecked[$szCanonicalFileName];
    }
}
