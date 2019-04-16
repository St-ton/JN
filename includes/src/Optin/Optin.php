<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Optin;

use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidInputException;
use JTL\Shop;

/**
 * Class Optin
 * @package JTL\Optin
 */
class Optin extends OptinBase
{
    /**
     * @var OptinAvailAgain|null
     */
    protected $currentOptin;

    /**
     * @var string actionPrefix
     */
    protected $externalAction;

    /**
     * Optin constructor.
     * @param string|null $optinClass
     * @throws EmptyResultSetException
     */
    public function __construct(string $optinClass = null)
    {
        $this->dbHandler   = Shop::Container()->getDB();
        $this->nowDataTime = new \DateTime();

        if ($optinClass !== null) {
            $this->generateOptin($optinClass);
        }
    }

    /**
     * @return OptinInterface
     */
    public function getOptinInstance(): OptinInterface
    {
        return $this->currentOptin;
    }

    /**
     * set a action-prefix, if we have no code but a email
     *
     * @param string $action
     * @return Optin
     */
    public function setAction(string $action): Optin
    {
        $this->externalAction = $action;

        return $this;
    }

    /**
     * return message meanings:
     * 'optinCanceled'      = cancel (a previously active) subscription
     * 'optinRemoved'       = cancel optin without the existence of a subscription
     * 'optinSucceded'      = subscription successfully
     * 'optinSuccededAgain' = user clicked again
     *
     * @return string
     * @throws EmptyResultSetException
     * @throws InvalidInputException
     */
    public function handleOptin(): string
    {
        if ($this->optCode === '' && $this->emailAddress === '') {
            throw new InvalidInputException('missing email and/or optin-code.');
        }
        $this->loadOptin();
        if (empty($this->foundOptinTupel)) {
            throw new EmptyResultSetException('Double-Opt-in not found: ' .
                (($this->emailAddress === '') ? $this->emailAddress : $this->optCode));
        }
        $this->generateOptin($this->refData->getOptinClass());
        if ($this->actionPrefix === self::DELETE_CODE || $this->externalAction === self::DELETE_CODE) {
            $this->deactivateOptin();

            return !empty($this->foundOptinTupel->dActivated) ? 'optinCanceled' : 'optinRemoved';
        }
        if ($this->actionPrefix === self::ACTIVATE_CODE || $this->externalAction === self::ACTIVATE_CODE) {
            $this->activateOptin();

            return empty($this->foundOptinTupel->dActivated) ? 'optinSucceded' : 'optinSuccededAgain';
        }
        throw new InvalidInputException('unknown action received.');
    }

    /**
     * @throws \Exception
     */
    public function activateOptin(): void
    {
        if (!empty($this->currentOptin)) {
            $this->currentOptin->activateOptin();
        }
        $rowData = new \stdClass();
        if (empty($this->foundOptinTupel->dActivated)) {
            $rowData->dActivated = $this->nowDataTime->format('Y-m-d H:i:s');
            $this->dbHandler->update('toptin', 'kOptinCode', $this->optCode, $rowData);
        }
    }

    /**
     * deactivate and cleanup this optin
     * (class specific deactivations AND finishing here)
     */
    public function deactivateOptin(): void
    {
        if (!empty($this->currentOptin)) {
            $this->currentOptin->deactivateOptin();
        }
        $this->finishOptin();
    }

    /**
     * only move the optin-tupel to history
     * (e.g. used for "one shot opt-in" actions)
     */
    public function finishOptin(): void
    {
        $newRow               = new \stdClass();
        $newRow->kOptinCode   = $this->foundOptinTupel->kOptinCode;
        $newRow->kOptinClass  = $this->foundOptinTupel->kOptinClass;
        $newRow->cMail        = 'anonym'; // anonymized for history
        $newRow->cRefData     = \serialize($this->refData->anonymized()); // anonymized for history
        $newRow->dCreated     = $this->foundOptinTupel->dCreated;
        $newRow->dActivated   = $this->foundOptinTupel->dActivated;
        $newRow->dDeActivated = $this->nowDataTime->format('Y-m-d H:i:s');
        $this->dbHandler->insert('toptinhistory', $newRow);
        $this->dbHandler->delete('toptin', 'kOptinCode', $this->foundOptinTupel->kOptinCode);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $this->loadOptin();

        return !empty($this->foundOptinTupel->dActivated);
    }

    /**
     * @param string $optinClass
     * @throws EmptyResultSetException
     */
    private function generateOptin(string $optinClass): void
    {
        $this->currentOptin = OptinFactory::getInstance(
            $optinClass,
            $this->dbHandler,
            $this->nowDataTime,
            $this->refData,
            $this->emailAddress,
            $this->optCode,
            $this->actionPrefix
        );
        if ($this->currentOptin === null) {
            throw new EmptyResultSetException('Optin class not found');
        }
    }
}
