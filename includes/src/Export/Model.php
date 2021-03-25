<?php declare(strict_types=1);

namespace JTL\Export;

use DateTime;
use Exception;
use JTL\Catalog\Currency;
use JTL\Customer\CustomerGroup;
use JTL\Language\LanguageModel;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Model\ModelHelper;
use JTL\Plugin\State;
use JTL\Shop;

/**
 * Class Model
 *
 * @package JTL\Export
 * @property int           $kExportformat
 * @property int           $id
 * @method int getId()
 * @method void setId(int $value)
 * @property int           $kKundengruppe
 * @property int           $customerGroupID
 * @method int getCustomerGroupID()
 * @method void setCustomerGroupID(int $value)
 * @property int           $kSprache
 * @property int           $languageID
 * @method int getLanguageID()
 * @method void setLanguageID(int $value)
 * @property int           $kWaehrung
 * @property int           $currencyID
 * @method int getCurrencyID()
 * @method void setCurrencyID(int $value)
 * @property int           $kKampagne
 * @property int           $campaignID
 * @method int getCampaignID()
 * @method void setCampaignID(int $value)
 * @property int           $kPlugin
 * @property int           $pluginID
 * @method int getPluginID()
 * @method void setPluginID(int $value)
 * @property string        $cName
 * @property string        $name
 * @method string getName()
 * @method void setName(string $value)
 * @property string        $cDateiname
 * @property string        $filename
 * @method string getFilename()
 * @method void setFilename(string $value)
 * @property string        $cKopfzeile
 * @property string        $header
 * @method string getHeader()
 * @method void setHeader(string $value)
 * @property string        $content
 * @property string        $cContent
 * @method string getContent()
 * @method void setContent(string $value)
 * @property string        $cFusszeile
 * @property string        $footer
 * @method string getFooter()
 * @method void setFooter(string $value)
 * @property string        $cKodierung
 * @property string        $encoding
 * @method string getEncoding()
 * @method void setEncoding(string $value)
 * @property int           $nSpecial
 * @method int getIsSpecial()
 * @method void setIsSpecial(int $value)
 * @property int           $nVarKombiOption
 * @property int           $varcombOption
 * @method int getVarcombOption()
 * @method void setVarcombOption(int $value)
 * @property int           $nSplitgroesse
 * @property int           $spliSize
 * @method int getSplitSize()
 * @method void setSplitSize(int $value)
 * @property DateTime      $dZuletztErstellt
 * @property DateTime      $dateLastCreated
 * @method DateTime getDateLastCreated()
 * @method void setDateLastCreated(DateTime|string $value)
 * @property int           $nUseCache
 * @property int           $useCache
 * @method int getUseCache()
 * @method void setUseCache(int $value)
 * @property int           $nFehlerhaft
 * @method int getHasError()
 * @method void setHasError(int $value)
 * @property int           $async
 * @method int getAsync()
 * @method void setAsync(int $value)
 * @property string        $campaignParameter
 * @method string getCampaignParameter()
 * @method void setCampaignParameter(string $value)
 * @property string        $campaignValue
 * @method string getCampaignValue()
 * @method void setCampaignValue(string $value)
 * @property bool          $enabled
 * @method bool getEnabled()
 * @method void setEnabled(bool $value)
 * @property LanguageModel $language
 * @method LanguageModel getLanguage()
 * @method void setLanguage(LanguageModel $value)
 * @property Currency      $currency
 * @method Currency getCurrency()
 * @method void setCurrency(Currency $value)
 * @property CustomerGroup $customerGroup
 * @method Currency getCustomerGroup()
 * @method void setustomerGroup(CustomerGroup $value)
 */
final class Model extends DataModel
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'texportformat';
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
     * @inheritDoc
     */
    public function onInstanciation(): void
    {
        if ($this->id === null || $this->getDB() === null) {
            return;
        }
        if ($this->campaignID > 0) {
            $res = $this->getDB()->select('tkampagne', 'kKampagne', $this->campaignID, 'nAktiv', 1);
            if ($res !== null) {
                $this->setCampaignParameter($res->cParameter);
                $this->setCampaignValue($res->cWert);
            }
        }
        if ($this->pluginID > 0) {
            $res           = $this->getDB()->select('tplugin', 'kPlugin', $this->pluginID, 'nStatus', State::ACTIVATED);
            $this->enabled = $res !== null;
        }
        if ($this->languageID > 0) {
            $this->language = Shop::Lang()->getLanguageByID($this->languageID);
        }
        if ($this->currencyID > 0) {
            $this->currency = new Currency($this->currencyID);
        }
        if ($this->customerGroupID > 0) {
            $this->customerGroup = new CustomerGroup($this->customerGroupID);
        }
    }

    /**
     * @inheritdoc
     */
    protected function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();
        $this->registerGetter('dateLastCreated', static function ($value, $default) {
            return ModelHelper::fromStrToDateTime($value, $default);
        });
        $this->registerSetter('dateLastCreated', static function ($value) {
            return ModelHelper::fromDateTimeToStr($value);
        });
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        static $attributes = null;
        if ($attributes === null) {
            $attributes                    = [];
            $attributes['id']              = DataAttribute::create('kExportformat', 'int', null, false, true);
            $attributes['customerGroupID'] = DataAttribute::create('kKundengruppe', 'int', null, true, false);
            $attributes['languageID']      = DataAttribute::create('kSprache', 'int', null, true, false);
            $attributes['currencyID']      = DataAttribute::create('kWaehrung', 'int', null, true, false);
            $attributes['campaignID']      = DataAttribute::create(
                'kKampagne',
                'int',
                self::cast('0', 'int'),
                true,
                false
            );
            $attributes['pluginID']        = DataAttribute::create('kPlugin', 'int', null, false, false);
            $attributes['name']            = DataAttribute::create('cName', 'varchar', null, true, false);
            $attributes['filename']        = DataAttribute::create('cDateiname', 'varchar', null, true, false);
            $attributes['header']          = DataAttribute::create('cKopfzeile', 'mediumtext', null, true, false);
            $attributes['content']         = DataAttribute::create('cContent', 'mediumtext', null, true, false);
            $attributes['footer']          = DataAttribute::create('cFusszeile', 'mediumtext', null, false, false);
            $attributes['encoding']        = DataAttribute::create(
                'cKodierung',
                'varchar',
                self::cast('ASCII', 'varchar'),
                false,
                false
            );
            $attributes['isSpecial']       = DataAttribute::create(
                'nSpecial',
                'tinyint',
                self::cast('0', 'tinyint'),
                false,
                false
            );
            $attributes['varcombOption']   = DataAttribute::create(
                'nVarKombiOption',
                'tinyint',
                self::cast('1', 'tinyint'),
                false,
                false
            );
            $attributes['splitSize']       = DataAttribute::create(
                'nSplitgroesse',
                'int',
                self::cast('0', 'int'),
                true,
                false
            );
            $attributes['dateLastCreated'] = DataAttribute::create(
                'dZuletztErstellt',
                'datetime',
                null,
                true,
                false
            );
            $attributes['useCache']        = DataAttribute::create(
                'nUseCache',
                'tinyint',
                self::cast('0', 'tinyint'),
                false,
                false
            );
            $attributes['hasError']        = DataAttribute::create(
                'nFehlerhaft',
                'tinyint',
                self::cast('0', 'tinyint'),
                true,
                false
            );
            $attributes['async']           = DataAttribute::create(
                'async',
                'tinyint',
                self::cast('0', 'tinyint'),
                true,
                false
            );

            $cParam = new DataAttribute();
            $cParam->setName('campaignParameter')
                ->setDataType('varchar')
                ->setDefault('')
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['campaignParameter'] = $cParam;

            $cValue = new DataAttribute();
            $cValue->setName('campaignValue')
                ->setDataType('varchar')
                ->setDefault('')
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['campaignValue'] = $cValue;

            $enabled = new DataAttribute();
            $enabled->setName('enabled')
                ->setDataType('bool')
                ->setDefault(true)
                ->setNullable(false)
                ->setDynamic(true);
            $attributes['enabled'] = $enabled;

            $lang = new DataAttribute();
            $lang->setName('language')
                ->setDataType('object')
                ->setDefault(null)
                ->setNullable(true)
                ->setDynamic(true);
            $attributes['language'] = $lang;

            $currency = new DataAttribute();
            $currency->setName('currency')
                ->setDataType('object')
                ->setDefault(null)
                ->setNullable(true)
                ->setDynamic(true);
            $attributes['currency'] = $currency;

            $customerGroup = new DataAttribute();
            $customerGroup->setName('customerGroup')
                ->setDataType('object')
                ->setDefault(null)
                ->setNullable(true)
                ->setDynamic(true);
            $attributes['customerGroup'] = $customerGroup;
        }

        return $attributes;
    }
}
