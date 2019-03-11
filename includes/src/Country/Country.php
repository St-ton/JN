<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Country;

use JTL\Shop;
use JTL\MagicCompatibilityTrait;

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
        'cKontinent' => 'continent',
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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $langISO;


    /**
     * Country constructor.
     * @param string $ISO
     * @param bool $initFromDB
     */
    public function __construct(string $ISO, bool $initFromDB = false)
    {
        $langIso = $_SESSION['AdminAccount']->kSprache ?? Shop::Lang()->gibISO();

        $this->setISO($ISO);
        $this->setLangISO($langIso === 'ger' ? 'de' : 'en');
        $this->setName();
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
                 ->setEU($countryData->nEU);
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
     * @param string $LangISO
     * @return Country
     */
    public function setNameForLangISO(string $LangISO): self
    {
        $this->name    = $this->getNameForLangISO($LangISO);
        $this->langISO = $LangISO;

        return $this;
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
        return $this->continent;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Country
     */
    public function setName(): self
    {
        $this->name = $this->getNameForLangISO($this->getLangISO());

        return $this;
    }

    /**
     * @return string
     */
    public function getLangISO(): string
    {
        return $this->langISO;
    }

    /**
     * @param string $langISO
     * @return Country
     */
    public function setLangISO(string $langISO): self
    {
        $this->langISO = $langISO;

        return $this;
    }
}
