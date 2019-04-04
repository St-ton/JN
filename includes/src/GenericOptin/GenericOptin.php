<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GenericOptin;

use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidInputException;
use JTL\Shop;

/**
 * Class GenericOptin
 * @package JTL\GenericOptin
 */
class GenericOptin extends GenericOptinBase
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
     * @var string 'ac'|'cc'
     */
    protected $externalAction;

    /**
     * GenericOptin constructor.
     * @param int $optinType
     * @throws \Exception
     */
    public function __construct(?int $optinType)
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
    public function getOptin(): GenericOptinInterface
    {
        return $this->currentOptin;
    }

    /**
     * @param $action
     * @return GenericOptin
     */
    public function setAction(string $action): GenericOptin
    {
        $this->externalAction = $action;

        return $this;
    }

    /**
     * activate or deactivate an existing optin
     * code "actionPrefixes" are:
     *   'cc' = "clear code"
     *   'ac' = "activate code"
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
        $this->refData = \unserialize($this->foundOptinTupel->cRefData, ['GenericOptinRefData']);
        $this->generateOptin($this->refData->getOptinType());
        if ($this->actionPrefix === 'cc' || $this->externalAction === 'cc') {
            $this->deactivateOptin();

            return !empty($this->foundOptinTupel->dActivated) ? 'optinCanceled' : 'optinRemoved';
        }
        if ($this->actionPrefix === 'ac' || $this->externalAction === 'ac') {
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
        $newRow->kOptinId     = $this->optCode;
        $newRow->kOptinType   = $this->refData->getOptinType();
        $newRow->cMail        = $this->refData->getEmail();
        $newRow->cRefData     = \serialize($this->refData);
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
     * @param $optinType
     */
    private function generateOptin($optinType): void
    {
        $this->currentOptin = GenericOptinFactory::instantiate(
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
