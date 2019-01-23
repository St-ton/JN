<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

/**
 * Class Cart
 * @package Boxes\Items
 */
final class Cart extends AbstractBox
{
    /**
     * Cart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('elemente', 'Items');
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
