<?php

namespace JTL;

use Exception;
use InvalidArgumentException;
use JTL\Helpers\Template as TemplateHelper;
use JTL\Template\Model;
use JTL\Template\TemplateServiceInterface;
use JTL\Template\XMLReader;
use SimpleXMLElement;
use stdClass;

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
     * @deprecated since 5.0.0
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
     * @var Model
     */
    private $model;

    /**
     * Template constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->init();
        self::$frontEndInstance = $this;
        $this->reader           = new XMLReader();
        $this->model            = Shop::Container()->get(TemplateServiceInterface::class)->getActiveTemplate();
        $this->xmlData          = $this->model;
    }

    /**
     * @return Template
     */
    public static function getInstance(): self
    {
        return self::$frontEndInstance ?? new self();
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $cacheID = 'current_template';
        if (($template = Shop::Container()->getCache()->get($cacheID)) !== false) {
            $this->loadFromModel($template);

            return $this;
        }
        try {
            $template = Shop::Container()->get(TemplateServiceInterface::class)->getActiveTemplate();
            $this->loadFromModel($template);
            Shop::Container()->getCache()->set($cacheID, $template, [\CACHING_GROUP_TEMPLATE]);
        } catch (Exception $e) {
            throw new InvalidArgumentException('No template loaded - Exception: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * @param Model $model
     * @return $this
     */
    private function loadFromModel(Model $model): self
    {
        self::$cTemplate = $model->getTemplate();
        self::$parent    = !empty($model->getParent()) ? $model->getParent() : null;
        $this->name      = $model->getName();
        $this->author    = $model->getAuthor();
        $this->url       = $model->getUrl();
        $this->version   = $model->getVersion();
        $this->preview   = $model->getPreview();

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
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $this->reader->getXML($dir ?? self::$cTemplate);
    }

    /**
     * get registered plugin resources (js/css)
     *
     * @return array
     */
    public function getPluginResources(): array
    {
        // @todo
    }

    /**
     * get array of static resources in minify compatible format
     *
     * @param bool $absolute
     * @return array|mixed
     */
    public function getMinifyArray($absolute = false)
    {
        // @todo
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function hasMobileTemplate(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function isMobileTemplateActive(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
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
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
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
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
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
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
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
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
    }
}
