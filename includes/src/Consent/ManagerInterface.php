<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Consent;

/**
 * Interface ManagerInterface
 * @package JTL\Consent
 */
interface ManagerInterface
{
    /**
     * @param ItemInterface $item
     * @return bool
     */
    public function hasConsent(ItemInterface $item): bool;

    /**
     * @param ItemInterface $item
     */
    public function giveConsent(ItemInterface $item): void;

    /**
     * @param ItemInterface $item
     */
    public function revokeConset(ItemInterface $item): void;
}
