<?php declare(strict_types=1);

namespace JTL\REST\Models;

use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;

/**
 * Class PriceModel
 *
 * @property int $kPreis
 * @property int $id
 * @property int $kArtikel
 * @property int $productID
 * @property int $kKundengruppe
 * @property int $customerGroupID
 * @property int $kKunde
 * @property int $customerID
 */
final class PriceModel extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tpreis';
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
    public function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();

        // update price detail IDs after creating auto increment kPreis for this model
        $this->registerSetter('kPreis', static function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if ($model->kPreis === 0 && $model->detail instanceof Collection && $model->detail->count() > 0) {
                foreach ($model->detail as $price) {
                    $price->kPreis = $value;
                }
            }
            return $value;
        });

        $this->registerSetter('detail', function ($value, $model) {
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->detail ?? new Collection();
            if (isset($value['netPrice'])) {
                return $this->updateSingleDetailItem($res, $value, $model);
            }
            foreach ($value as $data) {
                if (!isset($data['kPreis'])) {
                    $data['kPreis'] = $model->kPreis;
                }
                $res = $this->updateSingleDetailItem($res, $value, $model);
            }

            return $res;
        });
    }

    /**
     * @param Collection $collection
     * @param array      $value
     * @param PriceModel $model
     * @return Collection
     * @throws Exception
     */
    public function updateSingleDetailItem(Collection $collection, array $value, PriceModel $model): Collection
    {
        if (!isset($value['kPreis'])) {
            $value['kPreis'] = $model->kPreis ?? 0;
        }
        $detail   = PriceDetailModel::loadByAttributes(
            $value,
            $this->getDB(),
            ProductLocalizationModel::ON_NOTEXISTS_NEW
        );
        $existing = $collection->first(static function ($e) use ($detail) {
            return $e->id === $detail->id && $e->kPreis === $detail->kPreis;
        });
        if ($existing === null) {
            $collection->push($detail);
        } else {
            foreach ($detail->getAttributes() as $attribute => $v) {
                if (\array_key_exists($attribute, $value)) {
                    $existing->setAttribValue($attribute, $detail->getAttribValue($attribute));
                }
            }
        }

        return $collection;
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
        $attributes                    = [];
        $attributes['id']              = DataAttribute::create('kPreis', 'int', null, false, true);
        $attributes['productID']       = DataAttribute::create('kArtikel', 'int', null, false);
        $attributes['customerGroupID'] = DataAttribute::create('kKundengruppe', 'int', null, false);
        $attributes['customerID']      = DataAttribute::create('kKunde', 'int');

        $attributes['detail'] = DataAttribute::create('detail', PriceDetailModel::class, null, true, false, 'kPreis');

        return $attributes;
    }
}
