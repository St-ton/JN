<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

/**
 * Class DirectPurchase
 *
 * @package JTL\Boxes\Items
 */
final class DirectPurchase extends AbstractBox
{
    /**
     * DirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(true);
        \executeHook(\HOOK_BOXEN_INC_SCHNELLKAUF);
    }
}
