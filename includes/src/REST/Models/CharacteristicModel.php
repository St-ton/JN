<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CharacteristicModel
 *
 * @property int                                           $kMerkmal
 * @property int                                           $id
 * @property int                                           $nSort
 * @property int                                           $sort
 * @property string                                        $cName
 * @property string                                        $name
 * @property string                                        $cBildpfad
 * @property string                                        $image
 * @property string                                        $cTyp
 * @property string                                        $type
 * @property int                                           $nMehrfachauswahl
 * @property int                                           $isMulti
 * @property Collection|CharacteristicValueModel[]        $value
 * @property Collection|CharacteristicLocalizationModel[] $localization
 */
final class CharacteristicModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmerkmal';
    }

    /**
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
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->localization ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['characteristicID'])) {
                    $data['characteristicID'] = $model->id;
                }
                try {
                    $loc = CharacteristicLocalizationModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        self::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($loc) {
                    return $e->characteristicID === $loc->characteristicID && $e->languageID === $loc->languageID;
                });
                if ($existing === null) {
                    $res->push($loc);
                } else {
                    foreach ($loc->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $loc->getAttribValue($attribute));
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
        $attributes            = [];
        $attributes['id']      = DataAttribute::create('kMerkmal', 'int', null, false, true);
        $attributes['sort']    = DataAttribute::create('nSort', 'int');
        $attributes['name']    = DataAttribute::create('cName', 'varchar');
        $attributes['image']   = DataAttribute::create('cBildpfad', 'varchar', '', false);
        $attributes['type']    = DataAttribute::create('cTyp', 'varchar', null, false);
        $attributes['isMulti'] = DataAttribute::create('nMehrfachauswahl', 'tinyint', self::cast('0', 'tinyint'), false);

        $attributes['value']        = DataAttribute::create(
            'value',
            CharacteristicValueModel::class,
            null,
            true,
            false,
            'kMerkmal'
        );
        $attributes['localization'] = DataAttribute::create(
            'localization',
            CharacteristicLocalizationModel::class,
            null,
            true,
            false,
            'kMerkmal'
        );

        return $attributes;
    }
}
