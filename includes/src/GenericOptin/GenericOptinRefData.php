<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GenericOptin;

/**
 * Class GenericOptinRefData
 * @package JTL\GenericOptin
 */
class GenericOptinRefData implements \Serializable
{
    /**
     * @var int
     */
    private $optinType;

    /**
     * @var int
     */
    private $languageID;

    /**
     * @var int
     */
    private $customerID;

    /**
     * @var string
     */
    private $salutation = '';

    /**
     * @var string
     */
    private $firstName = '';

    /**
     * @var string
     */
    private $lastName = '';

    /**
     * @var string
     */
    private $email = '';

    /**
     * @var string
     */
    private $realIP = '';

    /**
     * @var int
     */
    private $productID;

    /**
     * @return string
     */
    public function serialize(): string
    {
        return \serialize([
            $this->optinType,
            $this->languageID,
            $this->customerID,
            $this->salutation,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->realIP,
            $this->productID
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        [
            $this->optinType,
            $this->languageID,
            $this->customerID,
            $this->salutation,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->realIP,
            $this->productID
        ] = \unserialize($serialized, ['GenericOptinRefData']);
    }

    /**
     * @param int $optinType
     * @return GenericOptinRefData
     */
    public function setOptinType(int $optinType): self
    {
        $this->optinType = $optinType;

        return $this;
    }

    /**
     * @param int $languageID
     * @return GenericOptinRefData
     */
    public function setLanguageID(int $languageID): self
    {
        $this->languageID = $languageID;

        return $this;
    }

    /**
     * @param int $customerID
     * @return GenericOptinRefData
     */
    public function setCustomerID(int $customerID): self
    {
        $this->customerID = $customerID;

        return $this;
    }

    /**
     * @param string $salutation
     * @return GenericOptinRefData
     */
    public function setSalutation(string $salutation): self
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * @param string $firstName
     * @return GenericOptinRefData
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     * @return GenericOptinRefData
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $email
     * @return GenericOptinRefData
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $realIP
     * @return GenericOptinRefData
     */
    public function setRealIP(string $realIP): self
    {
        $this->realIP = $realIP;

        return $this;
    }

    /**
     * @param int $productId
     * @return GenericOptinRefData
     */
    public function setProductId(int $productId): self
    {
        $this->productID = $productId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOptinType(): int
    {
        return $this->optinType;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @return string
     */
    public function getSalutation(): string
    {
        return $this->salutation;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRealIP(): string
    {
        return $this->realIP;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productID;
    }

    /**
     * @return $this
     */
    public function anonymized(): self
    {
        $this->setEmail('anonym');
        $this->setRealIP('-');

        return $this;
    }

    /**
     * @return false|mixed|string
     */
    public function __toString()
    {
        return $this->serialize();
    }
}
