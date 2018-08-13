<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


/**
 * Class ProductCategories
 * @package Boxes
 */
final class ProductCategories extends AbstractBox
{
    /**
     * DirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $show = isset($config['global']['global_sichtbarkeit'])
            && ((int)$config['global']['global_sichtbarkeit'] !== 3 || \Session::Customer()->getID() > 0);
        $this->setShow($show);
    }
}
