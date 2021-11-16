<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\License\Manager;
use JTL\License\Struct\ExpiredExsLicense;
use JTL\Plugin\InstallCode;
use JTL\Shop;
use JTL\Template\Admin\Installation\TemplateInstallerFactory;
use SimpleXMLElement;

/**
 * Class TemplateService
 * @package JTL\Template
 */
class TemplateService implements TemplateServiceInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var Model|null
     */
    private $activeTemplate;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var string
     */
    private $cacheID = 'active_tpl';

    /**
     * TemplateService constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function setActiveTemplate(string $dir, string $type = 'standard'): bool
    {
        $this->db->delete('ttemplate', 'eTyp', $type);
        $this->db->delete('ttemplate', 'cTemplate', $dir);
        $reader       = new XMLReader();
        $xml          = $reader->getXML($dir);
        $parentConfig = false;
        if ($xml !== null && !empty($xml->Parent)) {
            if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . $xml->Parent)) {
                return false;
            }
            $parent       = (string)$xml->Parent;
            $parentConfig = $reader->getXML($parent);
        }
        $model = new Model($this->db);
        if (isset($xml->ExsID)) {
            $model->setExsID((string)$xml->ExsID);
        }
        $model->setCTemplate($dir);
        $model->setType($type);
        if (!empty($xml->Parent)) {
            $model->setParent((string)$xml->Parent);
        }
        $model->setName((string)$xml->Name);
        $model->setAuthor((string)$xml->Author);
        $model->setUrl((string)$xml->URL);
        $model->setPreview((string)$xml->Preview);
        $version = empty($xml->Version) && $parentConfig
            ? (string)$parentConfig->Version
            : (string)$xml->Version;
        $model->setVersion($version);
        if (!empty($xml->Framework)) {
            $model->setFramework((string)$xml->Framework);
        }
        $model->setBootstrap((int)\file_exists(\PFAD_ROOT . \PFAD_TEMPLATES . $dir . '/Bootstrap.php'));
        $save = $model->save();
        if ($save === true) {
            $installer = new TemplateInstallerFactory($this->db, $xml, $model);
            $res       = $installer->install();
            if ($res !== InstallCode::OK) {
                return false;
            }
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
        $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE, \CACHING_GROUP_CORE]);

        return $save;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        if ($this->loaded === false) {
            $this->cache->set(
                $this->cacheID,
                $this->activeTemplate,
                $this->activeTemplate->getResources()->getCacheTags()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getActiveTemplate(bool $withLicense = true): Model
    {
        if ($this->activeTemplate === null) {
            if (($this->activeTemplate = $this->cache->get($this->cacheID)) === false) {
                $this->activeTemplate = $this->loadFull(['type' => 'standard'], $withLicense);
            } else {
                $this->loaded = true;
            }
        }
        $_SESSION['cTemplate'] = $this->activeTemplate->getTemplate();

        return $this->activeTemplate;
    }

    /**
     * @inheritDoc
     */
    public function loadFull(array $attributes, bool $withLicense = true): Model
    {
        try {
            $template = Model::loadByAttributes($attributes, $this->db);
        } catch (Exception $e) {
            $template = new Model($this->db);
            $template->setTemplate('no-template');
        }
        $reader    = new XMLReader();
        $tplXML    = $reader->getXML($template->getTemplate(), $template->getType() === 'admin');
        $parentXML = ($tplXML === null || empty($tplXML->Parent)) ? null : $reader->getXML((string)$tplXML->Parent);
        $dir       = $template->getTemplate();
        if ($dir === null || $tplXML === null) {
            $model = new Model($this->db);
            $model->setName($template->cTemplate ?? 'undefined');

            return $model;
        }
        $template = $this->mergeWithXML($dir, $tplXML, $template, $parentXML);
        if ($withLicense === true) {
            $manager    = new Manager($this->db, $this->cache);
            $exsLicense = $manager->getLicenseByItemID($template->getTemplate());
            if ($exsLicense === null && $template->getExsID() !== null) {
                $exsLicense = new ExpiredExsLicense();
                $exsLicense->initFromTemplateData($template);
            }
            $template->setExsLicense($exsLicense);
        }
        $template->setBoxLayout($this->getBoxLayout($tplXML, $parentXML));
        $template->setResources(new Resources($this->db, $tplXML, $parentXML));

        return $template;
    }

    /**
     * @param string                $dir
     * @param SimpleXMLElement      $xml
     * @param Model|null            $template
     * @param SimpleXMLElement|null $parentXML
     * @return Model
     * @throws Exception
     */
    private function mergeWithXML(
        string $dir,
        SimpleXMLElement $xml,
        ?Model $template = null,
        ?SimpleXMLElement $parentXML = null
    ): Model {
        $template = $template ?? Model::loadByAttributes(['cTemplate' => $dir], $this->db, Model::ON_NOTEXISTS_NEW);
        $template->setName(\trim((string)$xml->Name));
        $template->setDir($dir);
        $template->setAuthor(\trim((string)$xml->Author));
        $template->setUrl(\trim((string)$xml->URL));
        $template->setVersion(\trim((string)$xml->Version));
        $template->setFileVersion(\trim((string)$xml->Version));
        $template->setShopVersion(\trim((string)$xml->ShopVersion));
        $template->setPreview(\trim((string)$xml->Preview));
        $template->setDocumentationURL(\trim((string)$xml->DokuURL));
        $template->setIsChild(!empty($xml->Parent));
        $template->setParent(!empty($xml->Parent) ? \trim((string)$xml->Parent) : null);
        $template->setIsResponsive(!empty($xml['isFullResponsive'])
            && \strtolower((string)$xml['isFullResponsive']) === 'true');
        $template->setHasError(false);
        $template->setDescription(!empty($xml->Description) ? \trim((string)$xml->Description) : '');
        if ($parentXML !== null && !empty($xml->Parent)) {
            $parentConfig = $this->mergeWithXML((string)$xml->Parent, $parentXML);
            if ($parentConfig !== false) {
                $version = !empty($template->getVersion()) ? $template->getVersion() : $parentConfig->getVersion();
                $template->setVersion($version);
                $shopVersion = !empty($template->getShopVersion())
                    ? $template->getShopVersion()
                    : $parentConfig->getShopVersion();
                $template->setShopVersion($shopVersion);
            }
        }
        $version = $template->getVersion();
        if (empty($version)) {
            $template->setVersion($template->getShopVersion());
        }
        if (empty($template->getFileVersion())) {
            $template->setFileVersion($template->getVersion());
        }
        $template->setHasConfig(isset($xml->Settings->Section) || $template->isChild());
        if (\mb_strlen($template->getName()) === 0) {
            $template->setName($dir);
        }
        $config = new Config($template->getDir(), $this->db);
        $template->setConfig($config);

        return $template;
    }

    /**
     * @param SimpleXMLElement      $tplXML
     * @param SimpleXMLElement|null $parentXML
     * @return array
     */
    private function getBoxLayout(SimpleXMLElement $tplXML, ?SimpleXMLElement $parentXML = null): array
    {
        $items = [];
        foreach ([$parentXML, $tplXML] as $xml) {
            if ($xml === null || !isset($xml->Boxes) || \count($xml->Boxes) !== 1) {
                continue;
            }
            foreach ($xml->Boxes[0] as $item) {
                /** @var SimpleXMLElement $item */
                $attr                           = $item->attributes();
                $items[(string)$attr->Position] = (bool)(int)$attr->Available;
            }
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->activeTemplate = null;
    }
}
