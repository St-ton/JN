<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Services\JTL;

use JTL\Country\Country;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;

/**
 * Class CountryService
 * @package JTL\Services\JTL
 */
class CountryService implements CountryServiceInterface
{
    /**
     * @var Collection
     */
    private $countryList;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * CountryService constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->countryList = new Collection();
        $this->db          = $db;
        $this->init();
    }

    public function init(): void
    {
        $countries = $this->db->query('SELECT cISO, nEU, cKontinent FROM tland', ReturnType::ARRAY_OF_OBJECTS);

        foreach ($countries as $country) {
            $contryTMP = new Country($country->cISO);
            $contryTMP->setEU($country->nEU)
                      ->setContinent($country->cKontinent);

            $this->getCountryList()->push($contryTMP);
        }

        $this->countryList = $this->getCountryList()->sortBy(function (Country $country) {
            return $country->getName();
        });
    }

    /**
     * @return Collection
     */
    public function getCountryList(): Collection
    {
        return $this->countryList;
    }

    /**
     * @param string $ISO
     * @return Country
     */
    public function getCountry(string $ISO): ?Country
    {
        return $this->getCountryList()->filter(function (Country $country) use ($ISO) {
            return $country->getISO() === strtoupper($ISO);
        })->pop();
    }

    /**
     * @param array $ISOToFilter
     * @param bool $getAllIfEmpty
     * @return Collection
     */
    public function getFilteredCountryList(array $ISOToFilter, bool $getAllIfEmpty = false): Collection
    {
        if ($getAllIfEmpty && empty($ISOToFilter)) {
            return $this->getCountryList();
        }
        $filterItems = \array_map('strtoupper', $ISOToFilter);

        return $this->getCountryList()->filter(function (Country $country) use ($filterItems) {
            return \in_array($country->getISO(), $filterItems, true);
        });
    }
}
