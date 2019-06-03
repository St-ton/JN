<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Language;

use Exception;
use JTL\Helpers\Text;
use JTL\Model\DataAttribute;
use JTL\Model\DataModel;
use JTL\Shop;
use Locale;

/**
 * Class LanguageModel
 *
 * @package JTL\Language
 * @property int    $kSprache
 * @property int    $id
 * @property string $cNameEnglisch
 * @property string $nameEN
 * @property string $cNameDeutsch
 * @property string $nameDE
 * @property string $cStandard
 * @property string $default
 * @property string $cISO
 * @property string $iso
 * @property string $cShopStandard
 * @property string $shopDefault
 * @property string $iso639
 * @property string $displayLanguage
 * @property string $localizedName
 * @property string $url
 * @property string $urlFull
 */
final class LanguageModel extends DataModel
{
    /**
     * @return string
     */
    public function getLocalizedName(): string
    {
        return $this->localizedName;
    }

    /**
     * @param string $localizedName
     */
    public function setLocalizedName(string $localizedName): void
    {
        $this->localizedName = $localizedName;
    }

    /**
     * @return string
     */
    public function getDisplayLanguage(): string
    {
        return $this->displayLanguage;
    }

    /**
     * @param string $displayLanguage
     */
    public function setDisplayLanguage(string $displayLanguage): void
    {
        $this->displayLanguage = $displayLanguage;
    }

    /**
     * @return string
     */
    public function getIso639(): string
    {
        return $this->iso639;
    }

    /**
     * @param string $iso639
     */
    public function setIso639(string $iso639): void
    {
        $this->iso639 = $iso639;
    }

    /**
     * @return string
     * @see DataModel::getTableName()
     */
    public function getTableName(): string
    {
        return 'tsprache';
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->iso;
    }

    /**
     * Setting of keyname is not supported!!!
     * Call will always throw an Exception with code ERR_DATABASE!
     * @param string $keyName
     * @throws Exception
     * @see IDataModel::setKeyName()
     */
    public function setKeyName($keyName): void
    {
        throw new Exception(__METHOD__ . ': setting of keyname is not supported', self::ERR_DATABASE);
    }

    /**
     * @see DataModel::onRegisterHandlers()
     */
    protected function onRegisterHandlers(): void
    {
        parent::onRegisterHandlers();
        $this->registerSetter('url', function ($value) {
            $this->urlFull = $value;

            return $value;
        });
    }

    /**
     * @inheritDoc
     */
    public function onInstanciation(): void
    {
        if ($this->iso === null) {
            return;
        }
        $activeLangCode        = Shop::getLanguageCode();
        $this->iso639          = Text::convertISO2ISO639($this->iso);
        $this->displayLanguage = Locale::getDisplayLanguage($this->iso639, $this->iso639);
        if (isset($_SESSION['AdminAccount'])) {
            $this->localizedName = Locale::getDisplayLanguage(
                $this->iso639,
                $_SESSION['AdminAccount']->language
            );
        } elseif ($activeLangCode !== null) {
            $this->localizedName = Locale::getDisplayLanguage(
                $this->iso639,
                Text::convertISO2ISO639($activeLangCode)
            );
        }
    }

    /**
     * @return DataAttribute[]
     * @see IDataModel::getAttributes()
     *
     */
    public function getAttributes(): array
    {
        static $attributes = null;

        if (!isset($attributes)) {
            $attributes                    = [];
            $attributes['id']              = DataAttribute::create('kSprache', 'tinyint', self::cast('0', 'tinyint'), false, true);
            $attributes['nameEN']          = DataAttribute::create('cNameEnglisch', 'varchar');
            $attributes['nameDE']          = DataAttribute::create('cNameDeutsch', 'varchar');
            $attributes['default']         = DataAttribute::create('cStandard', 'char', self::cast('N', 'char'));
            $attributes['iso']             = DataAttribute::create('cISO', 'varchar', null, false);
            $attributes['shopDefault']     = DataAttribute::create('cShopStandard', 'char', self::cast('N', 'char'));
            $attributes['iso639']          = DataAttribute::create('cISO639', 'varchar', '', false, false, null, null, true);
            $attributes['displayLanguage'] = DataAttribute::create('displayLanguage', 'varchar', '', false, false, null, null, true);
            $attributes['localizedName']   = DataAttribute::create('localizedName', 'varchar', '', false, false, null, null, true);
            $attributes['url']             = DataAttribute::create('cURL', 'varchar', '', false, false, null, null, true);
            $attributes['urlFull']         = DataAttribute::create('cURLFull', 'varchar', '', false, false, null, null, true);
        }

        return $attributes;
    }
}
