<?php

namespace JTL;

use Exception;
use JTL\DB\ReturnType;
use JTL\Helpers\Template as TemplateHelper;
use JTL\Plugin\State;
use JTL\Template\Model;
use JTL\Template\XMLReader;
use LogicException;
use SimpleXMLElement;
use stdClass;
use function Functional\group;

/**
 * Class Template
 * @package JTL
 */
class Template
{
    /**
     * @var string
     */
    public static $cTemplate;

    /**
     * @var int
     */
    public static $nVersion;

    /**
     * @var Template
     */
    private static $frontEndInstance;

    /**
     * @var string
     */
    private static $parent;

    /**
     * @var TemplateHelper
     */
    private static $helper;

    /**
     * @var string
     */
    public $xmlData;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $preview;

    /**
     * @var XMLReader
     */
    private $reader;

    /**
     *
     */
    public function __construct()
    {
        $this->init();
        self::$frontEndInstance = $this;
        $this->reader           = new XMLReader();
        $this->xmlData          = self::$helper->getData(self::$cTemplate, false);
    }

    /**
     * @return Template
     */
    public static function getInstance(): self
    {
        return self::$frontEndInstance ?? new self();
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $cacheID = 'current_template';
        if (($template = Shop::Container()->getCache()->get($cacheID)) !== false) {
            $this->loadFromObject($template);

            return $this;
        }
        try {
            $template = Model::loadByAttributes(['type' => 'standard'], Shop::Container()->getDB());
            $this->loadFromObject($template);
            Shop::Container()->getCache()->set($cacheID, $template, [\CACHING_GROUP_TEMPLATE]);
        } catch (Exception $e) {
            throw new LogicException('No template loaded');
        }

        return $this;
    }

    /**
     * @param object $object
     * @return $this
     */
    private function loadFromObject($object): self
    {
        self::$cTemplate = $object->cTemplate;
        self::$parent    = !empty($object->parent) ? $object->parent : null;
        $this->name      = $object->name;
        $this->author    = $object->author;
        $this->url       = $object->url;
        $this->version   = $object->version;
        $this->preview   = $object->preview;

        return $this;
    }

    /**
     * returns current template's name
     *
     * @return string|null
     */
    public function getFrontendTemplate(): ?string
    {
        $template = Model::loadByAttributes(['type' => 'standard'], Shop::Container()->getDB());

        self::$cTemplate = $template->getCTemplate();
        self::$parent    = $template->getParent();

        return self::$cTemplate;
    }

    /**
     * @param null|string $dir
     * @return null|SimpleXMLElement
     * @deprecated since 5.0.0
     */
    public function leseXML($dir = null)
    {
        return $this->reader->getXML($dir ?? self::$cTemplate);
    }

    /**
     * get registered plugin resources (js/css)
     *
     * @return array
     */
    public function getPluginResources(): array
    {
        $resourcesc = Shop::Container()->getDB()->queryPrepared(
            'SELECT * 
                FROM tplugin_resources AS res
                JOIN tplugin
                    ON tplugin.kPlugin = res.kPlugin
                WHERE tplugin.nStatus = :state
                ORDER BY res.priority DESC',
            ['state' => State::ACTIVATED],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $grouped    = group($resourcesc, static function ($e) {
            return $e->type;
        });
        if (isset($grouped['js'])) {
            $grouped['js'] = group($grouped['js'], static function ($e) {
                return $e->position;
            });
        }

        return [
            'css'     => $this->getPluginResourcesPath($grouped['css'] ?? []),
            'js_head' => $this->getPluginResourcesPath($grouped['js']['head'] ?? []),
            'js_body' => $this->getPluginResourcesPath($grouped['js']['body'] ?? [])
        ];
    }

    /**
     * get resource path for single plugins
     *
     * @param stdClass[] $items
     * @return array
     */
    private function getPluginResourcesPath(array $items): array
    {
        foreach ($items as $item) {
            $frontend = \PFAD_PLUGIN_FRONTEND . $item->type . '/' . $item->path;
            if ((int)$item->bExtension === 1) {
                $item->rel = \PLUGIN_DIR . $item->cVerzeichnis . '/';
            } else {
                $item->rel = \PFAD_PLUGIN . $item->cVerzeichnis . '/';
                $frontend  = \PFAD_PLUGIN_VERSION . $item->nVersion . '/' . $frontend;
            }
            $item->rel .= $frontend;
            $item->abs = \PFAD_ROOT . $item->rel;
        }

        return $items;
    }

    /**
     * parse node of js/css files for insertion conditions and validate them
     *
     * @param SimpleXMLElement $node
     * @return bool
     */
    private function checkCondition($node): bool
    {
        $settingsGroup = \constant((string)$node->attributes()->DependsOnSettingGroup);
        $settingValue  = (string)$node->attributes()->DependsOnSettingValue;
        $comparator    = (string)$node->attributes()->DependsOnSettingComparison;
        $setting       = (string)$node->attributes()->DependsOnSetting;
        $conf          = Shop::getSettings([$settingsGroup]);
        $hierarchy     = \explode('.', $setting);
        $iterations    = \count($hierarchy);
        $i             = 0;
        if (empty($comparator)) {
            $comparator = '==';
        }
        foreach ($hierarchy as $_h) {
            $conf = $conf[$_h] ?? null;
            if ($conf === null) {
                return false;
            }
            if (++$i === $iterations) {
                switch ($comparator) {
                    case '==':
                        return $conf == $settingValue;
                    case '===':
                        return $conf === $settingValue;
                    case '>=':
                        return $conf >= $settingValue;
                    case '<=':
                        return $conf <= $settingValue;
                    case '>':
                        return $conf > $settingValue;
                    case '<':
                        return $conf < $settingValue;
                    default:
                        return false;
                }
            }
        }

        return false;
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array|mixed
     */
    public function getMinifyArray($absolute = false)
    {
        $dir        = $this->getDir();
        $folders    = [];
        $res        = [];
        $parentHash = '';
        if (self::$parent !== null) {
            $parentHash = self::$parent;
            $folders[]  = self::$parent;
        }
        $folders[] = $dir;
        $cacheID   = 'tpl_mnfy_dt_' . $dir . $parentHash;
        if (($tplGroups = Shop::Container()->getCache()->get($cacheID)) === false) {
            $tplGroups = [
                'plugin_css'     => [],
                'plugin_js_head' => [],
                'plugin_js_body' => []
            ];
            foreach ($folders as $dir) {
                $xml = $this->reader->getXML($dir);
                if ($xml === null) {
                    continue;
                }
                $cssSource = $xml->Minify->CSS ?? [];
                $jsSource  = $xml->Minify->JS ?? [];
                /** @var SimpleXMLElement $css */
                foreach ($cssSource as $css) {
                    $name = (string)$css->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    /** @var SimpleXMLElement $cssFile */
                    foreach ($css->File as $cssFile) {
                        $file     = (string)$cssFile->attributes()->Path;
                        $filePath = \PFAD_ROOT . \PFAD_TEMPLATES . $xml->Ordner . '/' . $file;
                        if (\file_exists($filePath)
                            && (empty($cssFile->attributes()->DependsOnSetting)
                                || $this->checkCondition($cssFile) === true)
                        ) {
                            $_file          = \PFAD_TEMPLATES . $dir . '/' . (string)$cssFile->attributes()->Path;
                            $customFilePath = \str_replace('.css', '_custom.css', $filePath);
                            if (\file_exists($customFilePath)) { //add _custom file if existing
                                $_file              = \str_replace(
                                    '.css',
                                    '_custom.css',
                                    \PFAD_TEMPLATES . $dir . '/' . (string)$cssFile->attributes()->Path
                                );
                                $tplGroups[$name][] = [
                                    'idx' => \str_replace('.css', '_custom.css', (string)$cssFile->attributes()->Path),
                                    'abs' => \realpath(\PFAD_ROOT . $_file),
                                    'rel' => $_file
                                ];
                            } else { //otherwise add normal file
                                $tplGroups[$name][] = [
                                    'idx' => $file,
                                    'abs' => \realpath(\PFAD_ROOT . $_file),
                                    'rel' => $_file
                                ];
                            }
                        }
                    }
                }
                /** @var SimpleXMLElement $js */
                foreach ($jsSource as $js) {
                    $name = (string)$js->attributes()->Name;
                    if (!isset($tplGroups[$name])) {
                        $tplGroups[$name] = [];
                    }
                    foreach ($js->File as $jsFile) {
                        if (!empty($jsFile->attributes()->DependsOnSetting) && $this->checkCondition($jsFile) !== true) {
                            continue;
                        }
                        $_file    = \PFAD_TEMPLATES . $dir . '/' . (string)$jsFile->attributes()->Path;
                        $newEntry = [
                            'idx' => (string)$jsFile->attributes()->Path,
                            'abs' => \PFAD_ROOT . $_file,
                            'rel' => $_file
                        ];
                        $found    = false;
                        if (!empty($jsFile->attributes()->override)
                            && (string)$jsFile->attributes()->override === 'true'
                        ) {
                            $idxToOverride = (string)$jsFile->attributes()->Path;
                            $max           = \count($tplGroups[$name]);
                            for ($i = 0; $i < $max; $i++) {
                                if ($tplGroups[$name][$i]['idx'] === $idxToOverride) {
                                    $tplGroups[$name][$i] = $newEntry;
                                    $found                = true;
                                    break;
                                }
                            }
                        }
                        if ($found === false) {
                            $tplGroups[$name][] = $newEntry;
                        }
                    }
                }
            }
            $pluginRes = $this->getPluginResources();
            foreach ($pluginRes['css'] as $_cssRes) {
                $customFilePath = \str_replace('.css', '_custom.css', $_cssRes->abs);
                if (\file_exists($customFilePath)) {
                    $tplGroups['plugin_css'][] = [
                        'idx' => $_cssRes->cName,
                        'abs' => $customFilePath,
                        'rel' => \str_replace('.css', '_custom.css', $_cssRes->rel)
                    ];
                } else {
                    $tplGroups['plugin_css'][] = [
                        'idx' => $_cssRes->cName,
                        'abs' => $_cssRes->abs,
                        'rel' => $_cssRes->rel
                    ];
                }
            }
            foreach ($pluginRes['js_head'] as $_jshRes) {
                $tplGroups['plugin_js_head'][] = [
                    'idx' => $_jshRes->cName,
                    'abs' => $_jshRes->abs,
                    'rel' => $_jshRes->rel
                ];
            }
            foreach ($pluginRes['js_body'] as $_jsbRes) {
                $tplGroups['plugin_js_body'][] = [
                    'idx' => $_jsbRes->cName,
                    'abs' => $_jsbRes->abs,
                    'rel' => $_jsbRes->rel
                ];
            }
            $cacheTags = [\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE, \CACHING_GROUP_PLUGIN];
            \executeHook(\HOOK_CSS_JS_LIST, [
                'groups'     => &$tplGroups,
                'cache_tags' => &$cacheTags
            ]);
            Shop::Container()->getCache()->set($cacheID, $tplGroups, $cacheTags);
        }
        foreach ($tplGroups as $name => $_tplGroup) {
            $res[$name] = [];
            foreach ($_tplGroup as $_file) {
                $res[$name][] = $absolute === true ? $_file['abs'] : $_file['rel'];
            }
        }

        return $res;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function hasMobileTemplate(): bool
    {
        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function isMobileTemplateActive(): bool
    {
        return false;
    }

    /**
     * get current template's active skin
     *
     * @return string|null
     */
    public function getSkin(): ?string
    {
        $cSkin = Shop::Container()->getDB()->select(
            'ttemplateeinstellungen',
            ['cName', 'cSektion', 'cTemplate'],
            [
                'theme_default',
                'theme',
                self::$cTemplate
            ]
        );

        return $cSkin->cWert ?? null;
    }

    /**
     * @return $this
     */
    public function setzeKundenTemplate(): self
    {
        $this->init();

        return $this;
    }

    /**
     * @param string      $folder - the current template's dir name
     * @param string|null $parent
     * @return array
     * @deprecated since 5.0.0
     */
    public function leseEinstellungenXML($folder, $parent = null): array
    {
        self::$cTemplate = $folder;

        return $this->reader->getConfigXML($folder, $parent);
    }

    /**
     * @param string|null $dirName
     * @return array
     */
    public function getBoxLayoutXML($dirName = null): array
    {
        $items  = [];
        $dirs   = self::$parent !== null ? [self::$parent] : [];
        $dirs[] = $dirName ?? self::$cTemplate;

        foreach ($dirs as $dir) {
            $xml = $this->reader->getXML($dir);
            if ($xml !== null && isset($xml->Boxes) && \count($xml->Boxes) === 1) {
                $boxXML = $xml->Boxes[0];
                /** @var SimpleXMLElement $ditem */
                foreach ($boxXML as $ditem) {
                    $cPosition         = (string)$ditem->attributes()->Position;
                    $bAvailable        = (bool)(int)$ditem->attributes()->Available;
                    $items[$cPosition] = $bAvailable;
                }
            }
        }

        return $items;
    }

    /**
     * @param string $dir
     * @return array
     * @deprecated since 5.0.0
     */
    public function leseLessXML($dir): array
    {
        $xml       = $this->reader->getXML($dir);
        $lessFiles = [];
        if (!$xml || !isset($xml->Lessfiles)) {
            return $lessFiles;
        }
        /** @var SimpleXMLElement $oXMLTheme */
        foreach ($xml->Lessfiles->THEME as $oXMLTheme) {
            $theme             = new stdClass();
            $theme->cName      = (string)$oXMLTheme->attributes()->Name;
            $theme->oFiles_arr = [];
            foreach ($oXMLTheme->File as $cFile) {
                $oThemeFiles         = new stdClass();
                $oThemeFiles->cPath  = (string)$cFile->attributes()->Path;
                $theme->oFiles_arr[] = $oThemeFiles;
            }
            $lessFiles[$theme->cName] = $theme;
        }

        return $lessFiles;
    }

    /**
     * set new frontend template
     *
     * @param string $dir
     * @param string $eTyp
     * @return bool
     */
    public function setTemplate($dir, $eTyp = 'standard'): bool
    {
        Shop::Container()->getDB()->delete('ttemplate', 'eTyp', $eTyp);
        Shop::Container()->getDB()->delete('ttemplate', 'cTemplate', $dir);
        $tplConfig = $this->reader->getXML($dir);
        if ($tplConfig !== null && !empty($tplConfig->Parent)) {
            if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . (string)$tplConfig->Parent)) {
                return false;
            }
            self::$parent = (string)$tplConfig->Parent;
            $parentConfig = $this->reader->getXML(self::$parent);
        } else {
            $parentConfig = false;
        }

        $tplObject            = new stdClass();
        $tplObject->cTemplate = $dir;
        $tplObject->eTyp      = $eTyp;
        $tplObject->parent    = !empty($tplConfig->Parent)
            ? (string)$tplConfig->Parent
            : '_DBNULL_';
        $tplObject->name      = (string)$tplConfig->Name;
        $tplObject->author    = (string)$tplConfig->Author;
        $tplObject->url       = (string)$tplConfig->URL;
        $tplObject->version   = empty($tplConfig->Version) && $parentConfig
            ? $parentConfig->Version
            : $tplConfig->Version;
        $tplObject->preview   = (string)$tplConfig->Preview;
        if (empty($tplObject->version)) {
            $tplObject->version = !empty($tplConfig->ShopVersion)
                ? $tplConfig->ShopVersion
                : $parentConfig->ShopVersion;
        }
        $inserted = Shop::Container()->getDB()->insert('ttemplate', $tplObject);
        if ($inserted > 0) {
            if (!$dh = \opendir(\PFAD_ROOT . \PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = \readdir($dh)) !== false) {
                if (\mb_strpos($obj, '.') === 0) {
                    continue;
                }
                if (!\is_dir(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj)) {
                    \unlink(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj);
                }
            }
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);

        return $inserted > 0;
    }

    /**
     * get template configuration
     *
     * @return array|bool
     */
    public function getConfig()
    {
        $settingsData = Shop::Container()->getDB()->selectAll('ttemplateeinstellungen', 'cTemplate', self::$cTemplate);
        if (\is_array($settingsData) && \count($settingsData) > 0) {
            $settings = [];
            foreach ($settingsData as $oSetting) {
                if (isset($settings[$oSetting->cSektion]) && !\is_array($settings[$oSetting->cSektion])) {
                    $settings[$oSetting->cSektion] = [];
                }
                $settings[$oSetting->cSektion][$oSetting->cName] = $oSetting->cWert;
            }

            return $settings;
        }

        return false;
    }

    /**
     * set template configuration
     *
     * @param string $dir
     * @param string $section
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setConfig($dir, $section, $name, $value): self
    {
        $config = Shop::Container()->getDB()->select(
            'ttemplateeinstellungen',
            'cTemplate',
            $dir,
            'cSektion',
            $section,
            'cName',
            $name
        );
        if ($config !== null && isset($config->cTemplate)) {
            Shop::Container()->getDB()->update(
                'ttemplateeinstellungen',
                ['cTemplate', 'cSektion', 'cName'],
                [$dir, $section, $name],
                (object)['cWert' => $value]
            );
        } else {
            $ins            = new stdClass();
            $ins->cTemplate = $dir;
            $ins->cSektion  = $section;
            $ins->cName     = $name;
            $ins->cWert     = $value;
            Shop::Container()->getDB()->insert('ttemplateeinstellungen', $ins);
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);

        return $this;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function IsMobile(): bool
    {
        return false;
    }

    /**
     * @param bool $absolute
     * @return string
     */
    public function getDir($absolute = false): string
    {
        return $absolute ? (\PFAD_ROOT . \PFAD_TEMPLATES . self::$cTemplate) : self::$cTemplate;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getParent(): ?string
    {
        return self::$parent;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getURL(): ?string
    {
        return $this->url;
    }

    /**
     * @return TemplateHelper
     */
    public function getHelper(): TemplateHelper
    {
        return self::$helper;
    }

    /**
     * @return string|null
     */
    public function getPreview(): ?string
    {
        return $this->preview;
    }

    /**
     * @param bool $bRedirect
     * @deprecated since 5.0.0
     */
    public function check($bRedirect = true): void
    {
    }
}
