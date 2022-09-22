<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class ManufacturerModel
 *
 * @property int                                        $id
 * @property int                                        $kHersteller
 * @property string                                     $name
 * @property string                                     $cName
 * @property string                                     $slug
 * @property string                                     $cSeo
 * @property string                                     $homepage
 * @property string                                     $cHomepage
 * @property int                                        $sort
 * @property int                                        $nSortNr
 * @property string                                     $image
 * @property string                                     $cBildpfad
 * @property Collection|ManufacturerLocalizationModel[] $localization
 */
final class ManufacturerModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'thersteller';
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
        $this->registerSetter('localization', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->localization ?? new Collection();
            foreach (\array_filter($value) as $data) {
                if (!isset($data['manufacturerID'])) {
                    $data['manufacturerID'] = $model->id;
                }
                try {
                    $loc = ManufacturerLocalizationModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        ManufacturerLocalizationModel::ON_NOTEXISTS_NEW
                    );
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($loc): bool {
                    return $e->manufacturerID === $loc->manufacturerID && $e->languageID === $loc->languageID;
                });
                if ($existing === null) {
                    $res->push($loc);
                } else {
                    foreach ($loc->getAttributes() as $attribute => $v) {
                        if (\array_key_exists($attribute, $data)) {
                            $existing->setAttribValue($attribute, $loc->getAttribValue($attribute));
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
        $attributes['id']           = DataAttribute::create('kHersteller', 'int', self::cast('0', 'int'), false, true);
        $attributes['name']         = DataAttribute::create('cName', 'varchar');
        $attributes['slug']         = DataAttribute::create('cSeo', 'varchar', null, false);
        $attributes['homepage']     = DataAttribute::create('cHomepage', 'varchar');
        $attributes['sort']         = DataAttribute::create('nSortNr', 'tinyint', 0);
        $attributes['image']        = DataAttribute::create('cBildpfad', 'varchar', '', false);
        $attributes['localization'] = DataAttribute::create(
            'localization',
            ManufacturerLocalizationModel::class,
            null,
            true,
            false,
            'kHersteller'
        );

        return $attributes;
    }
}
