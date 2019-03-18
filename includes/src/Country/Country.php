<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Country;

use JTL\Shop;
use JTL\MagicCompatibilityTrait;
use JTL\Helpers\Text;

/**
 * Class Country
 * @package JTL\Country
 */
class Country
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'nEU'        => 'EU',
        'cDeutsch'   => 'Name',
        'cEnglisch'  => 'Name',
        'cKontinent' => 'Continent',
        'cIso'       => 'ISO',
        'cName'      => 'Name'
    ];

    /**
     * @var string
     */
    private $ISO;

    /**
     * @var int
     */
    private $EU;

    /**
     * @var string
     */
    private $continent;

    /**
     * @var array
     */
    private $names;

    /**
     * for backwards compatibility cDeutsch
     * @var string
     */
    private $nameDE;

    /**
     * for backwards compatibility cEnglisch
     * @var string
     */
    private $nameEN;

    /**
     * Country constructor.
     * @param string $ISO
     * @param bool $initFromDB
     */
    public function __construct(string $ISO, bool $initFromDB = false)
    {
        $this->setISO($ISO);
        foreach (Shop::Lang()->getAllLanguages() as $lang) {
            $this->setName($lang);
        }
        if ($initFromDB) {
            $this->initFromDB();
        }
    }

    /**
     *
     */
    private function initFromDB(): void
    {
        $countryData = Shop::Container()->getDB()->select('tland', 'cISO', $this->getISO());
        if ($countryData !== null) {
            $this->setContinent($countryData->cKontinent)
                 ->setEU($countryData->nEU)
                 ->setNameDE($countryData->cDeutsch)
                 ->setNameEN($countryData->cEnglisch);
        }
    }

    /**
     * @param string $LangISO
     * @return string
     */
    public function getNameForLangISO(string $LangISO): string
    {
        return locale_get_display_region('sl-Latn-' . $this->getISO() . '-nedis', $LangISO);
    }

    /**
     * @return bool
     */
    public function isEU(): bool
    {
        return $this->getEU() === 1;
    }

    /**
     * @return string
     */
    public function getISO(): string
    {
        return $this->ISO;
    }

    /**
     * @param string $ISO
     * @return Country
     */
    public function setISO(string $ISO): self
    {
        $this->ISO = $ISO;

        return $this;
    }

    /**
     * @return int
     */
    public function getEU(): int
    {
        return $this->EU;
    }

    /**
     * @param int $EU
     * @return Country
     */
    public function setEU(int $EU): self
    {
        $this->EU = $EU;

        return $this;
    }

    /**
     * @return string
     */
    public function getContinent(): string
    {
        return isset($_SESSION['AdminAccount']) ? __($this->continent) : Shop::Lang()->get($this->continent);
    }

    /**
     * @param string $continent
     * @return Country
     */
    public function setContinent(string $continent): self
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * @param int|null $idx
     * @return string
     */
    public function getName(int $idx = null): string
    {
        $langID = $_SESSION['AdminAccount']->kSprache ?? Shop::getLanguageID();

        return $this->names[$idx ?? $langID] ?? '';
    }

    /**
     * @param \stdClass $lang
     * @return Country
     */
    public function setName(\stdClass $lang): self
    {
        $this->names[$lang->kSprache] = $this->getNameForLangISO(Text::convertISO2ISO639($lang->cISO));

        return $this;
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return $this->names;
    }

    /**
     * @param array $names
     * @return Country
     */
    public function setNames(array $names): self
    {
        $this->names = $names;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameDE(): string
    {
        return $this->nameDE;
    }

    /**
     * @param string $nameDE
     * @return Country
     */
    public function setNameDE(string $nameDE): self
    {
        $this->nameDE = $nameDE;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameEN(): string
    {
        return $this->nameEN;
    }

    /**
     * @param string $nameEN
     * @return Country
     */
    public function setNameEN(string $nameEN): self
    {
        $this->nameEN = $nameEN;

        return $this;
    }
}
