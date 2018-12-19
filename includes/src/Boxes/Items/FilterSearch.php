<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use Filter\Visibility;

/**
 * Class FilterSearch
 * @package Boxes\Items
 */
final class FilterSearch extends AbstractBox
{
    /**
     * FilterSearch constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $filter = \Shop::getProductFilter()->searchFilterCompat;
        $show   = $filter->getVisibility() !== Visibility::SHOW_NEVER
            && $filter->getVisibility() !== Visibility::SHOW_CONTENT
            && \count($filter->getOptions()) > 0
            && empty(\Shop::getProductFilter()->getSearch()->getValue());
        $this->setShow($show);
        $this->setTitle($filter->getFrontendName());
    }
}
