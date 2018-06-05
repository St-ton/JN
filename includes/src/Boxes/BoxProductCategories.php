<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxProductCategories
 * @package Boxes
 */
final class BoxProductCategories extends AbstractBox
{
    /**
     * BoxDirectPurchase constructor.
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
