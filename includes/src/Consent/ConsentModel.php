<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Consent;

use Exception;
use Illuminate\Support\Collection;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Plugin\Admin\InputType;

/**
 * Class ConsentModel
 *
 * @package JTL\Consent
 * @property int    $id
 * @property string $itemID
 * @method string getItemID()
 * @method void setItemID(string $value)
 * @property string $company
 * @method string getCompany()
 * @method void setCompany(string $value)
 * @property int    $pluginID
 * @method int getPluginID()
 * @method void setPluginID(int $value)
 * @property int    $active
 * @method int getActive()
 * @method void setActive(int $value)
 * @method Collection getLocalization()
 */
final class ConsentModel extends DataModel
{

    /**
     * @var ConsentLocalizationModel
     */
    private $currentLocalization;

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'tconsent';
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
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
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
                if (!isset($data['consentID'])) {
                    $data['consentID'] = $model->id;
                }
                try {
                    $loc = ConsentLocalizationModel::loadByAttributes(
                        $data,
                        $this->getDB(),
                        ConsentLocalizationModel::ON_NOTEXISTS_NEW
                    );
                } catch (Exception $e) {
                    continue;
                }
                $existing = $res->first(static function ($e) use ($loc) {
                    return $e->consentID === $loc->consentID && $e->languageID === $loc->languageID;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->getCurrentLocalization()->getName();
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getTos(int $idx = null): string
    {
        return $this->getCurrentLocalization()->getTos();
    }

    /**
     * @return string
     */
    public function getPurpose(): string
    {
        return $this->getCurrentLocalization()->getPurpose();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getCurrentLocalization()->getDescription();
    }

    /**
     * @param int|null $languageID
     * @return ConsentLocalizationModel
     */
    public function getCurrentLocalization(int $languageID = null): ConsentLocalizationModel
    {
        return $this->currentLocalization;
    }

    /**
     * @param int $languageID
     * @return bool
     */
    public function initFrontend(int $languageID): bool
    {
        $this->currentLocalization = $this->getLocalization()->first(static function (ConsentLocalizationModel $e) use ($languageID) {
                return $e->getLanguageID() === $languageID;
            }) ?? $this->getLocalization()->first();

        return $this->currentLocalization !== null;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes === null) {
            $attributes = [];
            $id         = DataAttribute::create('id', 'int', null, false, true);
            $id->getInputConfig()->setHidden(true);
            $attributes['id'] = $id;
            $itemID           = DataAttribute::create('itemID', 'varchar', null, false);
            $itemID->getInputConfig()->setModifyable(false);
            $attributes['itemID']  = $itemID;
            $attributes['company'] = DataAttribute::create('company', 'varchar', null, false);
            $pluginID              = DataAttribute::create('pluginID', 'int', self::cast('0', 'int'), false);
            $pluginID->getInputConfig()->setModifyable(false);
            $pluginID->getInputConfig()->setInputType(InputType::NUMBER);
            $attributes['pluginID'] = $pluginID;
            $active                 = DataAttribute::create('active', 'tinyint', self::cast('1', 'tinyint'), false);
            $active->getInputConfig()->setAllowedValues([
                0 => 'inactive',
                1 => 'active'
            ]);
            $active->getInputConfig()->setInputType(InputType::SELECT);
            $attributes['active'] = $active;


            $attributes['localization'] = DataAttribute::create(
                'localization',
                ConsentLocalizationModel::class,
                null,
                true,
                false,
                'id',
                'consentID'
            );
        }

        return $attributes;
    }
}
