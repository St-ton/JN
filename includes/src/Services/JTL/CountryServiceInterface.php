<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Services\JTL;

use Illuminate\Support\Collection;
use JTL\Country\Country;

/**
 * Interface CountryServiceInterface
 * @package JTL\Services\JTL
 */
interface CountryServiceInterface
{
    /**
     * @return void
     */
    public function init(): void;


    /**
     * @return Collection
     */
    public function getCountrylist(): Collection;

    /**
     * @param string $ISO
     * @return null|Country
     */
    public function getCountry(string $ISO): ?Country;

    /**
     * @param array $ISOToFilter
     * @param bool $getAllIfEmpty
     * @return Collection
     */
    public function getFilteredCountryList(array $ISOToFilter, bool $getAllIfEmpty = false): Collection;

    /**
     * @param string $countryName
     * @return null|string
     */
    public function getIsoByCountryName(string $countryName): ?string;

    /**
     * @param bool $getEU
     * @param array $selectedCountries
     * @return array
     */
    public function getCountriesGroupedByContinent(bool $getEU = false, array $selectedCountries = []): array;
}
