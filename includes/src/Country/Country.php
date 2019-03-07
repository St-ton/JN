<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Country;

use JTL\Shop;

/**
 * Class Country
 * @package JTL\Country
 */
class Country
{
    /**
     * @var string
     */
    private $ISO;

    /**
     * @var bool
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
                 ->setEU((int)$countryData->nEU === 1);
        }
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
     * @return bool
     */
    public function isEU(): bool
    {
        return $this->EU;
    }

    /**
     * @param bool $EU
     * @return Country
     */
    public function setEU(bool $EU): self
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
        $this->name = locale_get_display_region('sl-Latn-' . $this->getISO() . '-nedis', $this->getLangISO());

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
