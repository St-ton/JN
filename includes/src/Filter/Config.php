<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


use Session\Session;

/**
 * Class Config
 * @package Filter
 */
class Config implements ConfigInterface
{
    /**
     * @var int
     */
    private $langID = 0;

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var int
     */
    private $customerGroupID = 0;

    /**
     * @var string
     */
    private $baseURL = '';

    /**
     * @inheritdoc
     */
    public static function getDefault(): ConfigInterface
    {
        $config = new self();
        $config->setLanguageID(\Shop::getLanguageID());
        $config->setLanguages(\Sprache::getInstance()->getLangArray());
        $config->setConfig(\Shopsetting::getInstance()->getAll());
        $config->setCustomerGroupID(Session::CustomerGroup()->getID());
        $config->setBaseURL(\Shop::getURL() . '/');

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageID(): int
    {
        return $this->langID;
    }

    /**
     * @inheritdoc
     */
    public function setLanguageID(int $langID)
    {
        $this->langID = $langID;
    }

    /**
     * @inheritdoc
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @inheritdoc
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * @inheritdoc
     */
    public function getConfig($section = null)
    {
        return $section === null ? $this->config : $this->config[$section];
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupID(int $customerGroupID)
    {
        $this->customerGroupID = $customerGroupID;
    }

    /**
     * @inheritdoc
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @inheritdoc
     */
    public function setBaseURL(string $baseURL)
    {
        $this->baseURL = $baseURL;
    }
}
