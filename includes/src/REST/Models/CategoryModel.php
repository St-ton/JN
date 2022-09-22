<?php declare(strict_types=1);

namespace JTL\REST\Models;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;

/**
 * Class CategoryModel
 * @package JAPI\Models
 * @property int                                    $kKategorie
 * @property int                                    $id
 * @property string                                 $cSeo
 * @property string                                 $slug
 * @property string                                 $cName
 * @property string                                 $name
 * @property string                                 $cBeschreibung
 * @property string                                 $description
 * @property int                                    $kOberKategorie
 * @property int                                    $parentID
 * @property int                                    $nSort
 * @property int                                    $sort
 * @property DateTime                               $dLetzteAktualisierung
 * @property DateTime                               $lastModified
 * @property int                                    $lft
 * @property int                                    $rght
 * @property int                                    $nLevel
 * @property int                                    $level
 * @property Collection|CategoryLocalizationModel[] $localization
 * @property Collection|CategoryImageModel[]        $images
 * @property Collection|CategoryAttributeModel[]    $attributes
 * @property Collection|CategoryVisibilityModel[]   $visibility
 */
final class CategoryModel extends DataModel
{
    /**
     * pseudo auto increment for ProductCategories model
     *
     * @var int
     */
    protected int $lastAttributeID = -1;

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tkategorie';
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
        $this->registerGetter('dLetzteAktualisierung', static function ($value, $default) {
            return ModelHelper::fromStrToDate($value, $default);
        });
        $this->registerSetter('dLetzteAktualisierung', static function ($value) {
            return ModelHelper::fromDateToStr($value);
        });
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
                if (!isset($data['categoryID'])) {
                    $data['categoryID'] = $model->id;
                }
                try {
                    $loc = CategoryLocalizationModel::loadByAttributes($data, $this->getDB(), self::ON_NOTEXISTS_NEW);
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($loc) {
                    return $e->categoryID === $loc->categoryID && $e->languageID === $loc->languageID;
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
        $this->registerSetter('attributes', function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->attributes ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['categoryID'])) {
                    $data['categoryID'] = $model->id;
                }
                if (!isset($data['id'])) {
                    // tkategorieattribut has no auto increment ID...
                    if ($this->lastAttributeID === -1) {
                        $this->lastAttributeID = $this->getDB()?->getSingleInt(
                            'SELECT MAX(kKategorieAttribut) AS newID FROM tkategorieattribut',
                            'newID'
                        );
                    }
                    $data['id'] = ++$this->lastAttributeID;
                }
                try {
                    $item = CategoryAttributeModel::loadByAttributes($data, $this->getDB(), self::ON_NOTEXISTS_NEW);
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($item) {
                    return $e->id === $item->id && $e->categoryID === $item->categoryID;
                });
                if ($existing === null) {
                    $res->push($item);
                } else {
                    foreach ($item->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $item->getAttribValue($attribute));
                    }
                }
            }

            return $res;
        });
        $this->registerSetter('images', function ($value, $model) {
            if ($value === null) {
                return null;
            }
            if (\is_a($value, Collection::class)) {
                return $value;
            }
            if (!\is_array($value)) {
                $value = [$value];
            }
            $res = $model->images ?? new Collection();
            foreach ($value as $data) {
                if (!isset($data['categoryID'])) {
                    $data['categoryID'] = $model->id;
                }
                try {
                    $img = CategoryImageModel::loadByAttributes($data, $this->getDB(), self::ON_NOTEXISTS_NEW);
                } catch (Exception) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($img) {
                    return $e->categoryID === $img->categoryID && $e->languageID === $img->languageID;
                });
                if ($existing === null) {
                    $res->push($img);
                } else {
                    foreach ($img->getAttributes() as $attribute => $v) {
                        $existing->setAttribValue($attribute, $img->getAttribValue($attribute));
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
        $attributes['id']           = DataAttribute::create('kKategorie', 'int', self::cast('0', 'int'), false, true);
        $attributes['slug']         = DataAttribute::create('cSeo', 'varchar', self::cast('', 'varchar'), false);
        $attributes['name']         = DataAttribute::create('cName', 'varchar');
        $attributes['description']  = DataAttribute::create('cBeschreibung', 'mediumtext');
        $attributes['parentID']     = DataAttribute::create('kOberKategorie', 'int', self::cast('0', 'int'));
        $attributes['sort']         = DataAttribute::create('nSort', 'int', self::cast('0', 'int'));
        $attributes['lastModified'] = DataAttribute::create('dLetzteAktualisierung', 'date');
        $attributes['lft']          = DataAttribute::create('lft', 'int', self::cast('0', 'int'), false);
        $attributes['rght']         = DataAttribute::create('rght', 'int', self::cast('0', 'int'), false);
        $attributes['level']        = DataAttribute::create('nLevel', 'int', self::cast('1', 'int'), false);

        $attributes['localization'] = DataAttribute::create(
            'localization',
            CategoryLocalizationModel::class,
            null,
            true,
            false,
            'kKategorie'
        );
        $attributes['images']       = DataAttribute::create(
            'images',
            CategoryImageModel::class,
            null,
            true,
            false,
            'kKategorie'
        );
        $attributes['attributes']   = DataAttribute::create(
            'attributes',
            CategoryAttributeModel::class,
            null,
            true,
            false,
            'kKategorie'
        );
        $attributes['visibility']   = DataAttribute::create(
            'visibility',
            CategoryVisibilityModel::class,
            null,
            true,
            false,
            'kKategorie'
        );

        return $attributes;
    }
}
