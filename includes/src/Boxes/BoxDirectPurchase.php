<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxDirectPurchase
 * @package Boxes
 */
final class BoxDirectPurchase extends AbstractBox
{
    /**
     * BoxDirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(true);
        \executeHook(HOOK_BOXEN_INC_SCHNELLKAUF);
    }
}
