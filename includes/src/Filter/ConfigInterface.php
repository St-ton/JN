<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class Config
 * @package Filter
 */
interface ConfigInterface
{
    /**
     * @return ConfigInterface
     */
    public static function getDefault(): self;

    /**
     * @return int
     */
    public function getLanguageID(): int;

    /**
     * @param int $langID
     */
    public function setLanguageID(int $langID);

    /**
     * @return array
     */
    public function getLanguages(): array;

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages);

    /**
     * @param string|null $section
     * @return array|string|int
     */
    public function getConfig($section = null);

    /**
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * @return int
     */
    public function getCustomerGroupID(): int;

    /**
     * @param int $customerGroupID
     */
    public function setCustomerGroupID(int $customerGroupID);

    /**
     * @return string
     */
    public function getBaseURL(): string;

    /**
     * @param string $baseURL
     */
    public function setBaseURL(string $baseURL);
}
