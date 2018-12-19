<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;

use Filter\AbstractFilter;
use Filter\FilterInterface;
use Filter\ProductFilter;

/**
 * Class DummyState
 */
class DummyState extends AbstractFilter
{
    /**
     * @var null
     */
    public $dummyValue;

    /**
     * DummyState constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('ds')
             ->setUrlParamSEO(null);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->dummyValue = (int)$value;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->dummyValue;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function init($id): FilterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }
}
