<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\DB\DbInterface;
use JTL\MagicCompatibilityTrait;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\DataModelInterface;
use JTL\Shop;
use SimpleXMLElement;
use stdClass;

/**
 * Class Model
 *
 * @package JTL\ChangeMe
 * @property string $cTemplate
 * @method string getCTemplate()
 * @method void setCTemplate(string $value)
 * @property string $type
 * @method string getType()
 * @method void setType(string $value)
 * @property string $parent
 * @method string getParent()
 * @method void setParent(string $value)
 * @property int    $templateID
 * @method int getTemplateID()
 * @method void setTemplateID(int $value)
 * @property string $name
 * @method string getName()
 * @method void setName(string $value)
 * @property string $author
 * @method string getAuthor()
 * @method void setAuthor(string $value)
 * @property string $url
 * @method string getUrl()
 * @method void setUrl(string $value)
 * @property string $version
 * @method string getVersion()
 * @method void setVersion(string $value)
 * @property string $preview
 * @method string getPreview()
 * @method void setPreview(string $value)
 * @property string $exsID
 * @method string getExsID()
 * @method void setExsID(string $value)
 * @property int    $bootstrap
 * @method int getBootstrap()
 * @method void setBootstrap(int $value)
 * @property string $framework
 * @method string getFramework()
 * @method void setFramework(string $value)
 */
final class Model extends DataModel
{
    /**
     * @var string
     */
    private $documentationURL = '';

    /**
     * @var string
     */
    private $dir = '';

    /**
     * @var string
     */
    private $shopVersion = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var bool
     */
    private $isChild = false;

    /**
     * @var bool
     */
    private $isActive = false;

    /**
     * @var bool
     */
    private $hasConfig = false;

    /**
     * @var bool
     */
    private $isResponsive = false;

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @param array $attributes
     * @return $this
     * @throws Exception
     */
    public function loadFull(array $attributes): self
    {
        $template = self::loadByAttributes($attributes, $this->getDB());
        $reader   = new XMLReader();

        return $this->mergeWithXML(
            $template->getCTemplate(),
            $reader->getXML($template->getCTemplate(), $template->getType() === 'admin')
        );
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function loadActiveTemplate(): self
    {
        return $this->loadFull(['type' => 'standard']);
    }

    /**
     * @param string                $dir
     * @param SimpleXMLElement      $xml
     * @param SimpleXMLElement|null $parentXML
     * @return $this
     * @throws Exception
     */
    public function mergeWithXML(string $dir, SimpleXMLElement $xml, ?SimpleXMLElement $parentXML = null): self
    {
        $template = self::loadByAttributes(['cTemplate' => $dir], $this->getDB(), self::ON_NOTEXISTS_NEW);
        $template->setName(\trim((string)$xml->Name));
        $template->setDir($dir);
        $template->setAuthor(\trim((string)$xml->Author));
        $template->setUrl(\trim((string)$xml->URL));
        $template->setVersion(\trim((string)$xml->Version));
        $template->setShopVersion(\trim((string)$xml->ShopVersion));
        $template->setPreview(\trim((string)$xml->Preview));
        $template->setDocumentationURL(\trim((string)$xml->DokuURL));
        $template->setIsChild(!empty($xml->Parent));
        $template->setParent(!empty($xml->Parent) ? \trim((string)$xml->Parent) : '');
        $template->setIsResponsive(empty($xml['isFullResponsive'])
            ? false
            : (\strtolower((string)$xml['isFullResponsive']) === 'true'));
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
        $template->setHasConfig(isset($xml->Settings->Section) || $template->isChild());
        if (\mb_strlen($template->getName()) === 0) {
            $template->setName($dir);
        }

        return $template;
    }

    /**
     * @return string
     */
    public function getDocumentationURL(): string
    {
        return $this->documentationURL;
    }

    /**
     * @param string $documentationURL
     */
    public function setDocumentationURL(string $documentationURL): void
    {
        $this->documentationURL = $documentationURL;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getShopVersion(): string
    {
        return $this->shopVersion;
    }

    /**
     * @param string $shopVersion
     */
    public function setShopVersion(string $shopVersion): void
    {
        $this->shopVersion = $shopVersion;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->isChild;
    }

    /**
     * @param bool $isChild
     */
    public function setIsChild(bool $isChild): void
    {
        $this->isChild = $isChild;
    }

    /**
     * @return bool
     */
    public function isResponsive(): bool
    {
        return $this->isResponsive;
    }

    /**
     * @param bool $isResponsive
     */
    public function setIsResponsive(bool $isResponsive): void
    {
        $this->isResponsive = $isResponsive;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @param bool $hasError
     */
    public function setHasError(bool $hasError): void
    {
        $this->hasError = $hasError;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return bool
     */
    public function hasConfig(): bool
    {
        return $this->hasConfig;
    }

    /**
     * @param bool $hasConfig
     */
    public function setHasConfig(bool $hasConfig): void
    {
        $this->hasConfig = $hasConfig;
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'ttemplate';
    }

    /**
     * Setting of keyname is not supported!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @inheritdoc
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes === null) {
            $attributes               = [];
            $attributes['cTemplate']  = DataAttribute::create('cTemplate', 'varchar');
            $attributes['type']       = DataAttribute::create('eTyp', 'enum', null, false);
            $attributes['parent']     = DataAttribute::create('parent', 'varchar');
            $attributes['templateID'] = DataAttribute::create('templateID', 'int', null, false, true);
            $attributes['name']       = DataAttribute::create('name', 'varchar');
            $attributes['author']     = DataAttribute::create('author', 'varchar');
            $attributes['url']        = DataAttribute::create('url', 'varchar');
            $attributes['version']    = DataAttribute::create('version', 'varchar', null, false);
            $attributes['preview']    = DataAttribute::create('preview', 'varchar');
            $attributes['exsID']      = DataAttribute::create('exsID', 'varchar');
            $attributes['bootstrap']  = DataAttribute::create('bootstrap', 'tinyint', self::cast('0', 'tinyint'), false);
            $attributes['framework']  = DataAttribute::create('framework', 'varchar');
        }

        return $attributes;
    }
}
