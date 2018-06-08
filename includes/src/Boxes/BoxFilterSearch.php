<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Filter\Visibility;

/**
 * Class BoxFilterSearch
 * @package Boxes
 */
final class BoxFilterSearch extends AbstractBox
{
    /**
     * BoxFilterAttribute constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter = \Shop::getProductFilter()->searchFilterCompat;
        $show   = !$filter->getVisibility()->equals(Visibility::SHOW_NEVER())
            && !$filter->getVisibility()->equals(Visibility::SHOW_CONTENT())
            && count($filter->getOptions()) > 0
            && empty(\Shop::getProductFilter()->getSearch()->getValue());
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
    }
}
