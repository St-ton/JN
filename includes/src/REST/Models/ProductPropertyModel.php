<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class ProductPropertyModel
 *
 * @package JTL\REST\Models
 * @property int    $kEigenschaft
 * @method int getKEigenschaft()
 * @method void setKEigenschaft(int $value)
 * @property int    $kArtikel
 * @method int getKArtikel()
 * @method void setKArtikel(int $value)
 * @property string $cName
 * @method string getCName()
 * @method void setCName(string $value)
 * @property string $cWaehlbar
 * @method string getCWaehlbar()
 * @method void setCWaehlbar(string $value)
 * @property string $cTyp
 * @method string getCTyp()
 * @method void setCTyp(string $value)
 * @property int    $nSort
 * @method int getNSort()
 * @method void setNSort(int $value)
 */
final class ProductPropertyModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'teigenschaft';
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
        $this->registerSetter('localization', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->localization ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!isset($data['propertyID'])) {
                    $data['propertyID'] = $model->id;
                }
                try {
                    $item = ProductPropertyLocalizationModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($item): bool {
                    return $e->productID === $item->productID && $e->propertyID === $item->propertyID;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                        }
                    }
                }
            }

            return $res;
        });
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes !== null) {
            return $attributes;
        }
        $attributes                 = [];
        $attributes['propertyID']   = DataAttribute::create('kEigenschaft', 'int', self::cast('0', 'int'), false, true);
        $attributes['productID']    = DataAttribute::create('kArtikel', 'int');
        $attributes['name']         = DataAttribute::create('cName', 'varchar');
        $attributes['selectable']   = DataAttribute::create('cWaehlbar', 'char');
        $attributes['type']         = DataAttribute::create('cTyp', 'varchar', self::cast('', 'varchar'), false);
        $attributes['sort']         = DataAttribute::create('nSort', 'int', self::cast('0', 'int'), false);
        $attributes['localization'] = DataAttribute::create(
            'localization',
            ProductPropertyLocalizationModel::class,
            null,
            true,
            false,
            'kEigenschaft'
        );
        $attributes['combinations'] = DataAttribute::create(
            'combinations',
            ProductPropertyCombinationValueModel::class,
            null,
            true,
            false,
            'kEigenschaft'
        );

        return $attributes;
    }
}
