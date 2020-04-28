<?php

namespace JTL\Helpers;

use DirectoryIterator;
use JTL\Backend\FileCheck;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Template as CurrentTemplate;
use JTL\Template\XMLReader;
use SimpleXMLElement;
use stdClass;

/**
 * Class Template
 * @package JTL\Helpers
 */
class Template
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var bool
     */
    public $isAdmin = false;

    /**
     * @var Template[]
     */
    public static $instances = [];

    /**
     * @var bool
     */
    private $cachingEnabled = true;

    /**
     * @param bool $isAdmin
     */
    public function __construct(bool $isAdmin = false)
    {
        $this->isAdmin         = $isAdmin;
        $idx                   = $isAdmin ? 'admin' : 'frontend';
        self::$instances[$idx] = $this;
    }

    /**
     * @param bool $isAdmin
     * @return Template
     */
    public static function getInstance(bool $isAdmin = false): self
    {
        $idx = $isAdmin ? 'admin' : 'frontend';

        return !empty(self::$instances[$idx]) ? self::$instances[$idx] : new self($isAdmin);
    }

    /**
     * @return $this
     */
    public function disableCaching(): self
    {
        $this->cachingEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableCaching(): self
    {
        $this->cachingEnabled = true;

        return $this;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setTemplateDir(string $dir): self
    {
        $this->templateDir = $dir;

        return $this;
    }

    /**
     * @param string $path
     * @param int    $depth
     * @return array
     */
    public function getFolders(string $path, int $depth = 0): array
    {
        $result = [];
        if (!\is_dir($path)) {
            return $result;
        }

        foreach (\scandir($path, \SCANDIR_SORT_ASCENDING) as $value) {
            if (!\in_array($value, ['.', '..'], true) && \is_dir($path . \DIRECTORY_SEPARATOR . $value)) {
                $result[$value] = $depth > 1
                    ? $this->getFolders($path . \DIRECTORY_SEPARATOR . $value, $depth - 1)
                    : [];
            }
        }

        return $result;
    }

    /**
     * get all potential template folder names
     *
     * @param bool $path
     * @return array
     */
    private function getFrontendTemplateFolders($path = false): array
    {
        $res      = [];
        $iterator = new DirectoryIterator(\PFAD_ROOT . \PFAD_TEMPLATES);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                $res[] = $path ? $fileinfo->getRealPath() : $fileinfo->getFilename();
            }
        }

        return $res;
    }

    /**
     * get all potential admin template folder names
     *
     * @param bool $path
     * @return array
     */
    private function getAdminTemplateFolders(bool $path = false): array
    {
        $res      = [];
        $iterator = new DirectoryIterator(\PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isDir() && $fileinfo->getFilename() !== 'default') {
                // default template is deprecated since 5.0
                $res[] = $path ? $fileinfo->getRealPath() : $fileinfo->getFilename();
            }
        }

        return $res;
    }

    /**
     * read xml config file
     *
     * @param string    $dirName
     * @param bool|null $isAdmin
     * @return null|SimpleXMLElement
     */
    public function getXML($dirName, bool $isAdmin = null)
    {
        return (new XMLReader())->getXML($dirName, $isAdmin ?? $this->isAdmin);
    }

    /**
     * @param string $dir
     * @return array|bool
     */
    public function getConfig(string $dir)
    {
        $settingsData = Shop::Container()->getDB()->selectAll('ttemplateeinstellungen', 'cTemplate', $dir);
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
     * @param string    $dir
     * @param bool|null $isAdmin
     * @param SimpleXMLElement|null $xml
     * @return bool|stdClass
     */
    public function getData($dir, bool $isAdmin = null, SimpleXMLElement $xml = null)
    {
        $isAdmin = $isAdmin ?? $this->isAdmin;
        $cacheID = 'tpl_' . $dir . ($isAdmin ? '_admin' : '');
        if ($this->cachingEnabled === true && ($template = Shop::Container()->getCache()->get($cacheID)) !== false) {
            return $template;
        }
        $template = new stdClass();
        $xml      = $xml ?? $this->getXML($dir, $isAdmin);
        if (!$xml) {
            return false;
        }
        $template->cName        = \trim((string)$xml->Name);
        $template->cOrdner      = (string)$dir;
        $template->cAuthor      = \trim((string)$xml->Author);
        $template->cURL         = \trim((string)$xml->URL);
        $template->cVersion     = \trim((string)$xml->Version);
        $template->cShopVersion = \trim((string)$xml->ShopVersion);
        $template->cPreview     = \trim((string)$xml->Preview);
        $template->cDokuURL     = \trim((string)$xml->DokuURL);
        $template->bChild       = !empty($xml->Parent);
        $template->cParent      = !empty($xml->Parent) ? \trim((string)$xml->Parent) : '';
        $template->bResponsive  = empty($xml['isFullResponsive'])
            ? false
            : (\strtolower((string)$xml['isFullResponsive']) === 'true');
        $template->bHasError    = false;
        $template->eTyp         = '';
        $template->cDescription = !empty($xml->Description) ? \trim((string)$xml->Description) : '';
        if (!Text::is_utf8($template->cDescription)) {
            $template->cDescription = Text::convertUTF8($template->cDescription);
        }
        if (!empty($xml->Parent)) {
            $parentConfig = $this->getData($xml->Parent, $isAdmin);
            if ($parentConfig !== false) {
                $template->cVersion     = !empty($template->cVersion) ? $template->cVersion : $parentConfig->cVersion;
                $template->cShopVersion = !empty($template->cShopVersion)
                    ? $template->cShopVersion
                    : $parentConfig->cShopVersion;
            }
        }
        if (empty($template->cVersion)) {
            $template->cVersion = $template->cShopVersion;
        }

        $templates = Shop::Container()->getDB()->query(
            'SELECT * FROM ttemplate',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($templates as $tpl) {
            if (!isset($template->bAktiv) || !$template->bAktiv) {
                $template->bAktiv = (\strcasecmp($template->cOrdner, $tpl->cTemplate) === 0);
                if ($template->bAktiv) {
                    $template->eTyp = $tpl->eTyp;
                }
            }
        }
        $template->bEinstellungen = isset($xml->Settings->Section) || $template->bChild;
        if (\mb_strlen($template->cName) === 0) {
            $template->cName = $template->cOrdner;
        }
        if ($this->cachingEnabled === true) {
            Shop::Container()->getCache()->set($cacheID, $template, [\CACHING_GROUP_TEMPLATE]);
        }

        return $template;
    }
}
