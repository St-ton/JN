<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxCart
 * @package Boxes
 */
final class BoxCart extends AbstractBox
{
    /**
     * BoxCart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('elemente', 'Items');
        if (isset($_SESSION['Warenkorb']->PositionenArr)) {
            $products = [];
            foreach ($_SESSION['Warenkorb']->PositionenArr as $position) {
                $products[] = $position;
            }
            $this->setItems(\array_reverse($products));
        }
        $this->setShow(true);
    }
}
