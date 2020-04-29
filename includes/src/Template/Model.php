<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\DB\DbInterface;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Shop;
use SimpleXMLElement;

/**
 * Class Model
 *
 * @package JTL\Template
 * @property string $cTemplate
 * @method string getCTemplate()
 * @method void setCTemplate(string $value)
 * @property string $type
 * @method string getType()
 * @method void setType(string $value)
 * @property string $cParent
 * @method string getCParent()
 * @method void setCParent(string $value)
 * @property int    $templateID
 * @method int getTemplateID()
 * @method void setTemplateID(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $author
 * @method string getAuthor()
 * @method void setAuthor(string $value)
 * @property string $url
 * @method string getUrl()
 * @method void setUrl(string $value)
 * @property string $cVersion
 * @method string getCVersion()
 * @method void setCVersion(string $value)
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
 * @property bool   $isActive
 * @property bool   $hasConfig
 * @property bool   $hasError
 * @property bool   $isResponsive
 * @property bool   $isChild
 * @property string $description
 * @property string $shopVersion
 * @property string $cOrdner
 * @property string $dir
 * @method string getDir()
 * @method void setDir(string $value)
 * @method string getDescription()
 * @method void setDescription(string $value)
 * @method string getShopVersion()
 * @method void setShopVersion(string $value)
 * @method string getDocumentationURL()
 * @method void setDocumentationURL(string $value)
 * @method void setIsChild(bool $value)
 * @method void setIsActive(bool $value)
 * @method void setHasError(bool $value)
 * @method void setHasConfig(bool $value)
 * @method void setIsResponsive(bool $value)
 */
final class Model extends DataModel
{
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
     * @param DbInterface|null $db
     * @return static
     * @throws Exception
     */
    public static function loadActiveTemplate(?DbInterface $db = null): self
    {
        return self::newInstance($db ?? Shop::Container()->getDB())->loadFull(['type' => 'standard']);
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
     * @return bool
     */
    public function isResponsive(): bool
    {
        return $this->isResponsive;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function hasConfig(): bool
    {
        return $this->hasConfig;
    }

    /**
     * @return bool
     */
    public function isChild(): bool
    {
        return $this->isChild;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->cName;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->cName = $name;
    }

    /**
     * @return string|null
     */
    public function getParent(): ?string
    {
        return $this->cParent;
    }

    /**
     * @param string|null $name
     */
    public function setParent(?string $name): void
    {
        $this->cParent = $name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->cVersion;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->cVersion = $version;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->cTemplate;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->cTemplate = $template;
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
            $attributes['cParent']    = DataAttribute::create('parent', 'varchar');
            $attributes['templateID'] = DataAttribute::create('templateID', 'int', null, false, true);
            $attributes['cName']      = DataAttribute::create('name', 'varchar');
            $attributes['author']     = DataAttribute::create('author', 'varchar');
            $attributes['url']        = DataAttribute::create('url', 'varchar');
            $attributes['cVersion']   = DataAttribute::create('version', 'varchar', null, false);
            $attributes['preview']    = DataAttribute::create('preview', 'varchar');
            $attributes['exsID']      = DataAttribute::create('exsID', 'varchar');
            $attributes['bootstrap']  = DataAttribute::create('bootstrap', 'tinyint', self::cast('0', 'tinyint'), false);
            $attributes['framework']  = DataAttribute::create('framework', 'varchar');

            $dir = new DataAttribute();
            $dir->setName('cOrdner')
                ->setDataType('varchar')
                ->setDefault('')
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['dir'] = $dir;

            $shopVersion = new DataAttribute();
            $shopVersion->setName('shopVersion')
                ->setDataType('varchar')
                ->setDefault('')
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['shopVersion'] = $shopVersion;

            $documentationURL = new DataAttribute();
            $documentationURL->setName('documentationURL')
                ->setDataType('varchar')
                ->setDefault('')
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['documentationURL'] = $documentationURL;

            $description = new DataAttribute();
            $description->setName('description')
                ->setDataType('varchar')
                ->setDefault('')
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['description'] = $description;

            $isChild = new DataAttribute();
            $isChild->setName('isChild')
                ->setDataType('bool')
                ->setDefault(false)
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['isChild'] = $isChild;

            $isResponsive = new DataAttribute();
            $isResponsive->setName('isResponsive')
                ->setDataType('bool')
                ->setDefault(false)
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['isResponsive'] = $isResponsive;

            $hasError = new DataAttribute();
            $hasError->setName('hasError')
                ->setDataType('bool')
                ->setDefault(false)
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['hasError'] = $hasError;

            $hasConfig = new DataAttribute();
            $hasConfig->setName('hasConfig')
                ->setDataType('bool')
                ->setDefault(false)
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['hasConfig'] = $hasConfig;

            $isActive = new DataAttribute();
            $isActive->setName('isActive')
                ->setDataType('bool')
                ->setDefault(false)
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['isActive'] = $isActive;
        }

        return $attributes;
    }
}
