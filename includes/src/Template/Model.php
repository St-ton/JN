<?php declare(strict_types=1);

namespace JTL\Template;

use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class Model
 *
 * @package JTL\ChangeMe
 * @property string $cTemplate
 * @method string getCTemplate()
 * @method void setCTemplate(string $value)
 * @property string $eTyp
 * @method string getETyp()
 * @method void setETyp(string $value)
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
 * @property int $bootstrap
 * @method int getBootstrap()
 * @method void setBootstrap(int $value)
 * @property string $framework
 * @method string getFramework()
 * @method void setFramework(string $value)
 */
final class Model extends DataModel
{
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
            $attributes['eTyp']       = DataAttribute::create('eTyp', 'enum', null, false);
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
