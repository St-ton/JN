<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxLogin
 * @package Boxes
 */
final class BoxLogin extends AbstractBox
{
    /**
     * BoxDirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(true);
    }
}
