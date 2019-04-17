<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Optin;

/**
 * Interface OptinInterface
 * @package JTL\Optin
 */
interface OptinInterface
{
    /**
     * @param OptinRefData $refData
     * @return OptinInterface
     */
    public function createOptin(OptinRefData $refData);

    /**
     * @return mixed
     */
    public function activateOptin();

    /**
     * @return mixed
     */
    public function deactivateOptin();

    /**
     * @return mixed
     */
    public function sendActivationMail();
}
