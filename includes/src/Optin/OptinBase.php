<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Optin;

use JTL\DB\DbInterface;

/**
 * Class OptinBase
 * @package JTL\Optin
 */
abstract class OptinBase extends OptinFactory
{
    /**
     * action prefix
     */
    public const ACTIVATE_CODE = 'ac';

    /**
     * action prefix
     */
    public const DELETE_CODE = 'dc';

    /**
     * @var \DateTime
     */
    protected $nowDataTime;

    /**
     * @var DbInterface
     */
    protected $dbHandler;

    /**
     * @var string
     */
    protected $emailAddress = '';

    /**
     * @var string
     */
    protected $optCode = '';

    /**
     * @var string
     */
    protected $actionPrefix = '';

    /**
     * @var OptinRefData
     */
    protected $refData;

    /**
     * @var object stdClass
     */
    protected $foundOptinTupel;

    /**
     * @param string $mailaddress
     * @return Optin
     */
    public function setEmail(string $mailaddress): self
    {
        $this->emailAddress = $mailaddress;

        return $this;
    }

    /**
     * @param $optinCode
     * @return Optin
     */
    public function setCode(string $optinCode): self
    {
        $this->actionPrefix = \substr($optinCode, 0, 2);
        $this->optCode      = \substr($optinCode, 2);

        return $this;
    }

    /**
     * load a optin-tupel, via opt-code or email and
     * restore its reference data
     */
    protected function loadOptin(): void
    {
        if (empty($this->emailAddress)) {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'kOptinCode', $this->optCode);
        } else {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'cMail', $this->emailAddress);
        }
        if (!empty($this->foundOptinTupel)) {
            $this->refData = \unserialize($this->foundOptinTupel->cRefData, ['OptinRefData']);
        }
    }

    /**
     * @return string
     */
    protected function generateUniqOptinCode(): string
    {
        $count       = 0;
        $safetyLimit = 50;
        $Id          = function () {
            return md5($this->refData->getEmail() . \time() . \random_int(123, 456));
        };
        do {
            $newId = $Id();
            $count++;
        } while (!empty($this->dbHandler->select('toptin', 'kOptinCode', $newId)) || $count === $safetyLimit);

        return $newId;
    }

    /**
     * @param string $optCode
     */
    protected function saveOptin(string $optCode): void
    {
        $this->refData->setOptinClass(static::class); // save the caller
        $this->optCode       = $optCode;
        $newRow              = new \stdClass();
        $newRow->kOptinCode  = $this->optCode;
        $newRow->kOptinClass = static::class;
        $newRow->cMail       = $this->refData->getEmail();
        $newRow->cRefData    = \serialize($this->refData);
        $newRow->dCreated    = $this->nowDataTime->format('Y-m-d H:i:s');
        $this->dbHandler->insert('toptin', $newRow);
    }
}
