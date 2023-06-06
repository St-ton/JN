<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;
use JTL\Plugin\Admin\InputType;

/**
 * Class ApiKeyModel
 *
 * @package JTL\REST\Models
 * @property int      $id
 * @method int getId()
 * @method void setId(int $value)
 * @property int      $permissions
 * @method int getPermissions()
 * @method void setPermissions(int $value)
 * @property string   $key
 * @method string getKey()
 * @method void setKey(string $value)
 * @property DateTime $created
 * @method DateTime getCreated()
 * @method void setCreated(DateTime $value)
 */
final class ApiKeyModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'api_keys';
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
    protected function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();
        $this->registerGetter('created', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('created', static function ($value) {
            return ModelHelper::fromDateTimeToStr($value);
        });
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        $attributes                = [];
        $attributes['id']          = DataAttribute::create('id', 'int', null, false, true);
        $attributes['key']         = DataAttribute::create('key', 'varchar', null, false);
        $attributes['permissions'] = DataAttribute::create('permissions', 'int', 0, false);
        $attributes['created']     = DataAttribute::create('created', 'datetime', null, false);
        $attributes['created']->getInputConfig()->setInputType(InputType::DATE);
        $attributes['created']->getInputConfig()->setHidden(true);

        return $attributes;
    }
}
