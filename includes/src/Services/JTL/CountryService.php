<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Services\JTL;

use JTL\Country\Country;
use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Cache\JTLCacheInterface;

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
        $cacheID = 'serviceCountryList';
        if (($countries = $this->cache->get($cacheID)) !== false) {
            $this->countryList = $countries;

            return;
        }
        $countries = $this->db->query('SELECT * FROM tland', ReturnType::ARRAY_OF_OBJECTS);
        foreach ($countries as $country) {
            $countryTMP = new Country($country->cISO);
            $countryTMP->setEU((int)$country->nEU)
                       ->setContinent($country->cKontinent)
                       ->setNameDE($country->cDeutsch)
                       ->setNameEN($country->cEnglisch);

            $this->getCountryList()->push($countryTMP);
        }

        $this->countryList = $this->getCountryList()->sortBy(function (Country $country) {
            return $country->getName();
        });

        $this->cache->set($cacheID, $this->countryList, [\CACHING_GROUP_OBJECT]);
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

    /**
     * @param string $countryName
     * @return null|string
     */
    public function getIsoByCountryName(string $countryName): ?string
    {
        $countryName  = strtolower($countryName);
        $countryMatch = $this->getCountryList()->filter(function (Country $country) use ($countryName) {
            if (strtolower($country->getNameDE()) === $countryName
                || strtolower($country->getNameEN()) === $countryName
            ) {
                return true;
            }
            foreach ($country->getNames() as $countryNameTMP) {
                if (strtolower($countryNameTMP) === $countryName) {
                    return true;
                }
            }

            return false;
        })->pop();

        return $countryMatch ? $countryMatch->getISO() : null;
    }
}
