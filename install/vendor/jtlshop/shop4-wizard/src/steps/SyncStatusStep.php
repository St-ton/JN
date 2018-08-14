<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use jtl\Wizard\Shop4Wizard;

/**
 * Class SyncStatusStep
 */
class SyncStatusStep extends Step implements IStep, \JsonSerializable
{
    /**
     * @var Shop4Wizard
     */
    private $wizard;

    /**
     * @var \stdClass|null
     */
    private $company;

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var array
     */
    private $currencies = [];

    /**
     * @var bool
     */
    private $sync;

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Wawi-Abgleich';
    }

    /**
     * SyncStatusStep constructor.
     * @param Shop4Wizard $wizard
     */
    public function __construct($wizard)
    {
        $this->wizard  = $wizard;
        $this->company = \Shop::DB()->query('SELECT * FROM tfirma', 1);
        $this->sync    = \Shop::DB()->query('SELECT COUNT(*) AS cnt FROM tbrocken', 1)->cnt > 0;

        if ($this->sync) {
            // Kundengruppen
            $this->groups = \Shop::DB()->query('SELECT cName, cStandard FROM tkundengruppe', 2);
            // Sprachen
            $this->languages = \Shop::DB()->query('SELECT cNameDeutsch, cShopStandard FROM tsprache', 2);
            // Währungen
            $this->currencies = \Shop::DB()->query('SELECT cName, cStandard FROM twaehrung', 2);
        }
    }

    /**
     * @return array
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return array $currencies[]
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @return bool
     */
    public function isSync()
    {
        return $this->sync;
    }

    /**
     * @param bool $jumpToNext
     * @return mixed|void
     */
    public function finishStep($jumpToNext = true)
    {
        if ($jumpToNext === true) {
            $this->wizard->setStep(new GlobalSettingsStep($this->wizard));
        }
    }


    /**
     * @return \stdClass
     */
    public function jsonSerialize()
    {
        $data  = new \stdClass();
        foreach (get_object_vars($this) as $k => $v)
        {
            $data->$k = $v;
        }

        return $data;
    }
}