<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use DirectoryIterator;
use JTL\Backend\FileCheck;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Template as CurrentTemplate;
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
     * get list of all backend templates
     *
     * @return array
     */
    public function getAdminTemplates(): array
    {
        $templates = [];
        $folders   = $this->getAdminTemplateFolders();
        foreach ($folders as $folder) {
            $templateData = $this->getData($folder, true);
            if ($templateData) {
                $templates[] = $templateData;
            }
        }

        return $templates;
    }

    /**
     * @return array
     */
    public function getStoredTemplates(): array
    {
        $storedTemplates  = [];
        $subTemplateDir   = 'original' . \DIRECTORY_SEPARATOR;
        $storeTemplateDir = \PFAD_ROOT . \PFAD_TEMPLATES . $subTemplateDir;

        $folders      = $this->getFrontendTemplateFolders();
        $childFolders = $this->getFolders($storeTemplateDir, 2);

        foreach ($childFolders as $version => $dirs) {
            $intersect = \array_intersect(
                \array_values($folders),
                \array_keys($dirs)
            );
            foreach ($intersect as $dir) {
                $d = $subTemplateDir . $version . \DIRECTORY_SEPARATOR . $dir;
                if (($data = $this->getData($d, false)) !== false) {
                    $storedTemplates[$dir][] = $data;
                }
            }
        }

        return $storedTemplates;
    }

    /**
     * get list of all frontend templates
     *
     * @return array
     */
    public function getFrontendTemplates(): array
    {
        $templates = [];
        $folders   = $this->getFrontendTemplateFolders();
        foreach ($folders as $folder) {
            $templateData = $this->getData($folder, false);
            if ($templateData) {
                $templates[] = $templateData;
            }
        }

        foreach ($templates as $template) {
            //check if given parent template is available
            if ($template->bChild === true) {
                $template->bHasError = true;
                foreach ($templates as $_template) {
                    if ($_template->cOrdner === $template->cParent) {
                        $template->bHasError = false;
                        break;
                    }
                }
            }
        }

        return $templates;
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
     * @param string    $cOrdner
     * @param bool|null $isAdmin
     * @return null|SimpleXMLElement
     */
    public function getXML($cOrdner, bool $isAdmin = null)
    {
        $isAdmin = $isAdmin ?? $this->isAdmin;
        $xmlFile = $isAdmin === false
            ? \PFAD_ROOT . \PFAD_TEMPLATES . $cOrdner . \DIRECTORY_SEPARATOR . \TEMPLATE_XML
            : \PFAD_ROOT . \PFAD_ADMIN . \PFAD_TEMPLATES . $cOrdner . \DIRECTORY_SEPARATOR . \TEMPLATE_XML;
        if (!\file_exists($xmlFile)) {
            return null;
        }
        if (\defined('LIBXML_NOWARNING')) {
            //try to suppress warning if opening fails
            $xml = \simplexml_load_file($xmlFile, 'SimpleXMLElement', \LIBXML_NOWARNING);
        } else {
            $xml = \simplexml_load_file($xmlFile);
        }
        if ($xml === false) {
            $xml = \simplexml_load_string(\file_get_contents($xmlFile));
        }

        if (\is_a($xml, SimpleXMLElement::class)) {
            $xml->Ordner = $cOrdner;
        } else {
            $xml = null;
        }
        if (\EVO_COMPATIBILITY === false
            && ((string)$xml->Name === 'Evo' || (string)$xml->Parent ?? '' === 'Evo')
            && CurrentTemplate::getInstance()->getName() !== (string)$xml->Name
        ) {
            return null;
        }

        return $xml;
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
     * @return bool|stdClass
     */
    public function getData($dir, bool $isAdmin = null)
    {
        $isAdmin = $isAdmin ?? $this->isAdmin;
        $cacheID = 'tpl_' . $dir . ($isAdmin ? '_admin' : '');
        if ($this->cachingEnabled === true && ($template = Shop::Container()->getCache()->get($cacheID)) !== false) {
            return $template;
        }
        $template = new stdClass();
        $xml      = $this->getXML($dir, $isAdmin);
        if (!$xml) {
            return false;
        }
        $template->cName        = \trim((string)$xml->Name);
        $template->cOrdner      = (string)$dir;
        $template->cAuthor      = \trim((string)$xml->Author);
        $template->cURL         = \trim((string)$xml->URL);
        $template->cVersion     = \trim((string)$xml->Version);
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
            if ($parentConfig !== false && empty($template->cVersion)) {
                $template->cVersion = $parentConfig->cVersion;
            }
        } else {
            $template->checksums = $this->getChecksums((string)$dir);
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

    /**
     * @param string $dirname
     * @return array|bool|null
     */
    private function getChecksums(string $dirname)
    {
        $files       = [];
        $errorsCount = 0;
        $base        = \PFAD_ROOT . \PFAD_TEMPLATES . \basename($dirname) . '/';
        $checker     = new FileCheck();

        $res = $checker->validateCsvFile($base . 'checksums.csv', $files, $errorsCount, $base);
        if ($res === FileCheck::ERROR_INPUT_FILE_MISSING || $res === FileCheck::ERROR_NO_HASHES_FOUND) {
            return null;
        }

        return $errorsCount === 0 ? true : $files;
    }
}
