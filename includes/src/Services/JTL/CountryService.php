<?php declare(strict_types=1);

namespace JTL\Services\JTL;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\Country\Country;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;

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
     * @var JTLCacheInterface
     */
    private $cache;

    public const CACHE_ID = 'serviceCountryList';

    /**
     * CountryService constructor.
     * @param DbInterface $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->countryList = new Collection();
        $this->db          = $db;
        $this->cache       = $cache;
        $this->init();
    }

    public function init(): void
    {
        if (($countries = $this->cache->get(self::CACHE_ID)) !== false) {
            $this->countryList = $countries;

            return;
        }
        $countries            = $this->db->query('SELECT * FROM tland', ReturnType::ARRAY_OF_OBJECTS);
        $shippingMethods      = $this->db->query('SELECT cLaender FROM tversandart', ReturnType::ARRAY_OF_OBJECTS);
        $deliverableCountries = [];
        foreach ($shippingMethods as $shippingMethod) {
            $deliverableCountries = \array_unique(\array_merge(
                $deliverableCountries,
                \explode(' ', $shippingMethod->cLaender)
            ));
        }
        foreach ($countries as $country) {
            $countryTMP = new Country($country->cISO);
            $countryTMP->setEU((int)$country->nEU)
                       ->setContinent($country->cKontinent)
                       ->setNameDE($country->cDeutsch)
                       ->setNameEN($country->cEnglisch)
                       ->setPermitRegistration($country->bPermitRegistration === '1')
                       ->setRequireStateDefinition($country->bRequireStateDefinition === '1');
            if (\in_array($countryTMP->getISO(), $deliverableCountries, true)) {
                $countryTMP->setShippingAvailable(true);
            }
            $this->getCountryList()->push($countryTMP);
        }

        $this->countryList = $this->getCountryList()->sortBy(static function (Country $country) {
            return $country->getName();
        });

        $this->cache->set(self::CACHE_ID, $this->countryList, [\CACHING_GROUP_OBJECT]);
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
        return $this->getCountryList()->filter(static function (Country $country) use ($ISO) {
            return $country->getISO() === \strtoupper($ISO);
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
        $filterItems = \array_map('\strtoupper', $ISOToFilter);

        return $this->getCountryList()->filter(static function (Country $country) use ($filterItems) {
            return \in_array($country->getISO(), $filterItems, true);
        });
    }

    /**
     * @param string $countryName
     * @return null|string
     */
    public function getIsoByCountryName(string $countryName): ?string
    {
        $countryName  = \strtolower($countryName);
        $countryMatch = $this->getCountryList()->filter(static function (Country $country) use ($countryName) {
            if (\strtolower($country->getNameDE()) === $countryName
                || \strtolower($country->getNameEN()) === $countryName
            ) {
                return true;
            }
            foreach ($country->getNames() as $countryNameTMP) {
                if (\strtolower($countryNameTMP) === $countryName) {
                    return true;
                }
            }

            return false;
        })->pop();

        return $countryMatch ? $countryMatch->getISO() : null;
    }

    /**
     * @param bool $getEU - get all countries in EU and all countries in Europe not in EU
     * @param array|null $selectedCountries
     * @return array
     */
    public function getCountriesGroupedByContinent(bool $getEU = false, array $selectedCountries = []): array
    {
        $continentsTMP                = [];
        $continentsSelectedCountryTMP = [];
        $continents                   = [];
        foreach ($this->getCountryList() as $country) {
            $countrySelected                           = \in_array($country->getISO(), $selectedCountries, true);
            $continentsTMP[$country->getContinent()][] = $country;
            if ($countrySelected) {
                $continentsSelectedCountryTMP[$country->getContinent()][] = $country;
            }
            if ($getEU) {
                if ($country->isEU()) {
                    $continentsTMP[__('europeanUnion')][] = $country;
                    if ($countrySelected) {
                        $continentsSelectedCountryTMP[__('europeanUnion')][] = $country;
                    }
                } elseif ($country->getContinent() === __('Europa')) {
                    $continentsTMP[__('notEuropeanUnionEurope')][] = $country;
                    if ($countrySelected) {
                        $continentsSelectedCountryTMP[__('notEuropeanUnionEurope')][] = $country;
                    }
                }
            }
        }
        foreach ($continentsTMP as $continent => $countries) {
            $continents[] = (object)[
                'name'                   => $continent,
                'countries'              => $countries,
                'countriesCount'         => \count($countries),
                'countriesSelectedCount' => \count($continentsSelectedCountryTMP[$continent] ?? []),
                'sort'                   => $this->getContinentSort($continent)
            ];
        }
        \usort($continents, static function ($a, $b) {
            return $a->sort > $b->sort;
        });

        return $continents;
    }

    /**
     * @param string $continent
     * @return int
     */
    public function getContinentSort(string $continent): int
    {
        switch ($continent) {
            case __('Europa'):
                return 1;
            case __('europeanUnion'):
                return 2;
            case __('notEuropeanUnionEurope'):
                return 3;
            case __('Asien'):
                return 4;
            case __('Afrika'):
                return 5;
            case __('Nordamerika'):
                return 6;
            case __('Suedamerika'):
                return 7;
            case __('Ozeanien'):
                return 8;
            case __('Antarktis'):
                return 9;
            default:
                return 0;
        }
    }

    /**
     * @return array
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     */
    public function getContinents(): array
    {
        $continents = [];
        try {
            $reflection = new \ReflectionClass(\JTL\Country\Continent::class);
            $continents = $reflection->getConstants();
        } catch (\ReflectionException $e) {
            Shop::Container()->getLogService()->notice($e->getMessage());
        }

        return $continents;
    }
}
