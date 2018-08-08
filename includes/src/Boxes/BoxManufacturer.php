<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxManufacturer
 * @package Boxes
 */
final class BoxManufacturer extends AbstractBox
{
    /**
     * @var array
     */
    private $manufacturerList;

    /**
     * BoxManufacturer constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('manufacturers', 'Manufacturers');
        $this->setManufacturers(\HerstellerHelper::getInstance()->getManufacturers());
        $this->setShow(\count($this->manufacturerList) > 0);
    }

    /**
     * @return array
     */
    public function getManufacturers(): array
    {
        return $this->manufacturerList;
    }

    /**
     * @param array $manufacturers
     */
    public function setManufacturers(array $manufacturers)
    {
        $this->manufacturerList = $manufacturers;
    }
}
