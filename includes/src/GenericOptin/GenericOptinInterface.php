<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GenericOptin;

interface GenericOptinInterface
{
    /**
     * @param GenericOptinRefData $refData
     * @return OptinAvailAgain
     */
    public function createOptin(GenericOptinRefData $refData);

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
