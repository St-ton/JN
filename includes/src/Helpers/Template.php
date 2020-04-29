<?php

namespace JTL\Helpers;

use DirectoryIterator;
use JTL\Backend\FileCheck;
use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Template as CurrentTemplate;
use JTL\Template\Model;
use JTL\Template\TemplateServiceInterface;
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
     * @return array
     * @deprecated since 5.0.0
     */
    public function getConfig(string $dir): array
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use Shop::getSettings().', \E_USER_DEPRECATED);
        return Shop::getSettings([\CONF_TEMPLATE])['template'];
    }

    /**
     * @param string    $dir
     * @param bool|null $isAdmin
     * @param SimpleXMLElement|null $xml
     * @return Model
     * @deprecated since 5.0.0
     */
    public function getData($dir, bool $isAdmin = null, SimpleXMLElement $xml = null)
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return Shop::Container()->get(TemplateServiceInterface::class)->loadFull(['cTemplate' => $dir]);
    }
}
