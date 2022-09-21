<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class CharacteristicValueModel
 *
 * @property int                                                $kMerkmalWert
 * @property int                                                $id
 * @property int                                                $kMerkmal
 * @property int                                                $characteristicID
 * @property int                                                $nSort
 * @property int                                                $sort
 * @property string                                             $cBildpfad
 * @property string                                             $imagePath
 * @property Collection|CharacteristicValueLocalizationModel[] $localization
 */
final class CharacteristicValueModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tmerkmalwert';
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
                return $value;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->localization ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['characteristicValueID'])) {
                    $data['characteristicValueID'] = $model->id;
                }
                try {
                    $loc = CharacteristicValueLocalizationModel::loadByAttributes($data, $this->getDB(), self::ON_NOTEXISTS_NEW);
                } catch (Exception $e) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($loc) {
                    return $e->characteristicValueID === $loc->characteristicValueID && $e->languageID === $loc->languageID;
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

        if ($attributes === null) {
            $attributes                     = [];
            $attributes['id']               = DataAttribute::create('kMerkmalWert', 'int', null, false, true);
            $attributes['characteristicID'] = DataAttribute::create('kMerkmal', 'int');
            $attributes['sort']             = DataAttribute::create('nSort', 'int');
            $attributes['imagePath']        = DataAttribute::create('cBildpfad', 'varchar', '', false);

            $attributes['localization'] = DataAttribute::create('localization', CharacteristicValueLocalizationModel::class, null, true, false, 'kMerkmalWert');
        }

        return $attributes;
    }
}
