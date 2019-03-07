<?php
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
}
