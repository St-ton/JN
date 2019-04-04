<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GenericOptin;

use JTL\DB\DbInterface;

/**
 * Class OptinBase
 * @package JTL\GenericOptin
 */
abstract class GenericOptinBase extends GenericOptinFactory
{
    /**
     * action prefix
     */
    protected const ACTIVATE_CODE = 'ac';

    /**
     * action prefix
     */
    protected const CLEAR_CODE = 'cc';

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
     * @var GenericOptinRefData
     */
    protected $refData;

    /**
     * @param string $mailaddress
     * @return GenericOptin
     */
    public function setEmail(string $mailaddress): self
    {
        $this->emailAddress = $mailaddress;

        return $this;
    }

    /**
     * @param $optinCode
     * @return GenericOptin
     */
    public function setCode(string $optinCode): self
    {
        $this->actionPrefix = \substr($optinCode, 0, 2);
        $this->optCode      = \substr($optinCode, 2);

        return $this;
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
        $this->optCode      = $optCode;
        $newRow             = new \stdClass();
        $newRow->kOptinCode = $this->optCode;
        $newRow->kOptinType = $this->refData->getOptinType();
        $newRow->cMail      = $this->refData->getEmail();
        $newRow->cRefData   = \serialize($this->refData);
        $newRow->dCreated   = $this->nowDataTime->format('Y-m-d H:i:s');
        $this->dbHandler->insert('toptin', $newRow);
    }
}
