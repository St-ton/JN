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
     * @var object stdClass
     */
    protected $foundOptinTupel;

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
     * @param int|null $optinType
     * @throws \Exception
     */
    public function __construct(int $optinType = null)
    {
        $this->dbHandler   = Shop::Container()->getDB();
        $this->nowDataTime = new \DateTime();

        if ($optinType !== null) {
            $this->generateOptin($optinType);
        }
    }

    /**
     * @return OptinAvailAgain
     */
    public function getOptin(): OptinInterface
    {
        return $this->currentOptin;
    }

    /**
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
     * 'optinRemoved'       = cancel optin without the existence  of a subscription
     * 'optinSucceded'      = subscription successfully
     * 'optinSuccededAgain' = user clicked again
     *
     * @return string
     * @throws InvalidInputException
     * @throws EmptyResultSetException
     */
    public function handleOptin(): string
    {
        if ($this->optCode === '' && $this->emailAddress === '') {
            throw new InvalidInputException('missing email and/or optin-code.');
        }
        if (empty($this->emailAddress)) {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'kOptinCode', $this->optCode);
        } else {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'cMail', $this->emailAddress);
        }
        if (empty($this->foundOptinTupel)) {
            throw new EmptyResultSetException('Double-Opt-in not found: ' .
                ($this->emailAddress === '' ?: $this->optCode));
        }
        $this->refData = \unserialize($this->foundOptinTupel->cRefData, ['OptinRefData']);
        $this->generateOptin($this->refData->getOptinType());
        if ($this->actionPrefix === self::CLEAR_CODE || $this->externalAction === self::CLEAR_CODE) {
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
    protected function activateOptin(): void
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
     * @throws \Exception
     */
    protected function deactivateOptin(): void
    {
        if (!empty($this->currentOptin)) {
            $this->currentOptin->deactivateOptin();
        }
        $newRow               = new \stdClass();
        $newRow->kOptinCode   = $this->optCode;
        $newRow->kOptinType   = $this->refData->getOptinType();
        $newRow->cMail        = 'anonym'; // anonymized for history
        $newRow->cRefData     = \serialize($this->refData->anonymized()); // anonymized for history
        $newRow->dCreated     = $this->foundOptinTupel->dCreated;
        $newRow->dActivated   = $this->foundOptinTupel->dActivated;
        $newRow->dDeActivated = $this->nowDataTime->format('Y-m-d H:i:s');
        $this->dbHandler->insert('toptinhistory', $newRow);
        $this->dbHandler->delete('toptin', 'kOptinCode', $this->optCode);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->emailAddress === null) {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'kOptinCode', $this->optCode);
        } else {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'cMail', $this->emailAddress);
        }

        return !empty($this->foundOptinTupel->dActivated);
    }

    /**
     * @param int $optinType
     */
    private function generateOptin(int $optinType): void
    {
        $this->currentOptin = OptinFactory::instantiate(
            $optinType,
            $this->dbHandler,
            $this->nowDataTime,
            $this->refData,
            $this->emailAddress,
            $this->optCode,
            $this->actionPrefix
        );
    }
}
