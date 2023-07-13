<?php declare(strict_types=1);

namespace JTL\Country;

use JTL\Language\LanguageModel;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;

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
    protected static array $mapping = [
        'nEU'        => 'EU',
        'cDeutsch'   => 'Name',
        'cEnglisch'  => 'Name',
        'cKontinent' => 'Continent',
        'cISO'       => 'ISO',
        'cName'      => 'Name'
    ];

    /**
     * @var string|null
     */
    private ?string $iso = null;

    /**
     * @var int
     */
    private int $eu = 0;

    /**
     * @var string|null
     */
    private ?string $continent = null;

    /**
     * @var array
     */
    private array $names = [];

    /**
     * for backwards compatibility cDeutsch
     * @var string|null
     */
    private ?string $nameDE = null;

    /**
     * for backwards compatibility cEnglisch
     * @var string|null
     */
    private ?string $nameEN = null;

    /**
     * @var bool
     */
    private bool $shippingAvailable = false;

    /**
     * @var bool
     */
    private bool $permitRegistration = false;

    /**
     * @var bool
     */
    private bool $requireStateDefinition = false;

    /**
     * @var array
     */
    private array $states = [];

    /**
     * Country constructor.
     * @param string               $iso
     * @param bool                 $initFromDB
     * @param LanguageModel[]|null $languages
     */
    public function __construct(string $iso, bool $initFromDB = false, ?array $languages = null)
    {
        $this->setISO($iso);
        foreach ($languages ?? Shop::Lang()->getAllLanguages() as $lang) {
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
        $db          = Shop::Container()->getDB();
        $countryData = $db->select('tland', 'cISO', $this->getISO());
        if ($countryData === null) {
            return;
        }
        $this->setContinent($countryData->cKontinent)
             ->setEU((int)$countryData->nEU)
             ->setNameDE($countryData->cDeutsch)
             ->setNameEN($countryData->cEnglisch)
             ->setPermitRegistration((int)$countryData->bPermitRegistration === 1)
             ->setRequireStateDefinition((int)$countryData->bRequireStateDefinition === 1)
             ->setShippingAvailable($db->getSingleObject(
                 'SELECT COUNT(*) AS cnt 
                      FROM tversandart
                      WHERE cLaender LIKE :iso',
                 ['iso' => '%' . $this->getISO() . '%']
             )->cnt > 0);
    }

    /**
     * @param string $langISO
     * @return string
     */
    public function getNameForLangISO(string $langISO): string
    {
        return \locale_get_display_region('sl-Latn-' . $this->getISO() . '-nedis', $langISO);
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
        if ($this->iso === null && $this->nameDE !== null) {
            $countryData = Shop::Container()->getDB()->select('tland', 'cDeutsch', 'nameDE');
            $this->iso   = $countryData->cISO ?? 'DE';
        }

        return $this->iso;
    }

    /**
     * @param string $iso
     * @return Country
     */
    public function setISO(string $iso): self
    {
        $this->iso = $iso;

        return $this;
    }

    /**
     * @return int
     */
    public function getEU(): int
    {
        return $this->eu;
    }

    /**
     * @param int $eu
     * @return Country
     */
    public function setEU(int $eu): self
    {
        $this->eu = $eu;

        return $this;
    }

    /**
     * @return string
     */
    public function getContinent(): string
    {
        $name = $this->continent ?? '';

        return isset($_SESSION['AdminAccount']) ? \__($name) : Shop::Lang()->get($name);
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
        return isset($_SESSION['AdminAccount']->language)
            ? $this->getNameForLangISO($_SESSION['AdminAccount']->language)
            : $this->names[$idx ?? Shop::getLanguageID()] ?? '';
    }

    /**
     * @param LanguageModel $lang
     * @return Country
     */
    public function setName(LanguageModel $lang): self
    {
        $this->names[$lang->getId()] = $this->getNameForLangISO($lang->getIso639());

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

    /**
     * @return bool
     */
    public function isShippingAvailable(): bool
    {
        return $this->shippingAvailable;
    }

    /**
     * @param bool $shippingAvailable
     * @return Country
     */
    public function setShippingAvailable(bool $shippingAvailable): self
    {
        $this->shippingAvailable = $shippingAvailable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPermitRegistration(): bool
    {
        return $this->permitRegistration;
    }

    /**
     * @param bool $permitRegistration
     * @return Country
     */
    public function setPermitRegistration(bool $permitRegistration): self
    {
        $this->permitRegistration = $permitRegistration;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequireStateDefinition(): bool
    {
        return $this->requireStateDefinition;
    }

    /**
     * @param bool $requireStateDefinition
     * @return Country
     */
    public function setRequireStateDefinition(bool $requireStateDefinition): self
    {
        $this->requireStateDefinition = $requireStateDefinition;

        return $this;
    }

    /**
     * @return array
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * @param array $states
     * @return Country
     */
    public function setStates(array $states): self
    {
        $this->states = $states;

        return $this;
    }
}
