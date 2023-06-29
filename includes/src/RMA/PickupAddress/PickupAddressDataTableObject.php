<?php declare(strict_types=1);

namespace JTL\RMA\PickupAddress;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class PickupAddressDataTableObject
 * @package JTL\RMA
 * @description Store pick up addresses for RMA requests
 */
class PickupAddressDataTableObject extends AbstractDataObject implements DataTableObjectInterface
{
    /**
     * @var string
     */
    private string $primaryKey = 'id';
    
    /**
     * @var int
     */
    protected int $id = 0;
    
    /**
     * @var int
     */
    protected int $customerID = 0;
    
    /**
     * @var string
     */
    protected string $salutation = '';
    
    /**
     * @var string
     */
    protected string $firstName = '';
    
    /**
     * @var string
     */
    protected string $lastName = '';
    
    /**
     * @var string|null
     */
    protected ?string $academicTitle = null;
    
    /**
     * @var string|null
     */
    protected ?string $companyName = null;
    
    /**
     * @var string|null
     */
    protected ?string $companyAdditional = null;
    
    /**
     * @var string
     */

    protected string $street = '';
    /**
     * @var string
     */

    protected string $houseNumber = '';
    
    /**
     * @var string|null
     */
    protected ?string $addressAdditional = null;
    
    /**
     * @var string
     */
    protected string $postalCode = '';
    
    /**
     * @var string
     */
    protected string $city = '';
    
    /**
     * @var string
     */
    protected string $state = '';
    
    /**
     * @var string
     */
    protected string $country = '';
    
    /**
     * @var string|null
     */
    protected ?string $phone = null;
    
    /**
     * @var string|null
     */
    protected ?string $mobilePhone = null;
    
    /**
     * @var string|null
     */
    protected ?string $fax = null;
    
    /**
     * @var string|null
     */
    protected ?string $mail = null;

    /**
     * @var string
     */
    protected string $hash = '';
    
    
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'addressID'                => 'id',
        'customerID'               => 'customerID',
        'addressSalutation'        => 'salutation',
        'addressFirstName'         => 'firstName',
        'addressLastName'          => 'lastName',
        'addressAcademicTitle'     => 'academicTitle',
        'addressCompanyName'       => 'companyName',
        'addressCompanyAdditional' => 'companyAdditional',
        'addressStreet'            => 'street',
        'addressHouseNumber'       => 'houseNumber',
        'addressAddressAdditional' => 'addressAdditional',
        'addressPostalCode'        => 'postalCode',
        'addressCity'              => 'city',
        'addressState'             => 'state',
        'addressCountry'           => 'country',
        'addressPhone'             => 'phone',
        'addressMobilePhone'       => 'mobilePhone',
        'addressFax'               => 'fax',
        'addressMail'              => 'mail',
        'addressHash'              => 'hash'
    ];
    
    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->columnMapping;
    }
    
    /**
     * @return array
     */
    public function getReverseMapping(): array
    {
        return \array_flip($this->columnMapping);
    }

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return $this->columnMapping;
    }
    
    /**
     * @return mixed
     */
    public function getID(): mixed
    {
        return $this->{$this->getPrimaryKey()};
    }

    /**
     * @param string|int $id
     * @return $this
     */
    public function setID(string|int $id): self
    {
        $this->id = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    /**
     * @param string|int $customerID
     * @return $this
     */
    public function setCustomerID(string|int $customerID): self
    {
        $this->customerID = (int)$customerID;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalutation(): string
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     * @return $this
     */
    public function setSalutation(string $salutation): self
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcademicTitle(): ?string
    {
        return $this->academicTitle;
    }

    /**
     * @param string|null $academicTitle
     * @return $this
     */
    public function setAcademicTitle(?string $academicTitle): self
    {
        $this->academicTitle = $academicTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return $this
     */
    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyAdditional(): ?string
    {
        return $this->companyAdditional;
    }

    /**
     * @param string|null $companyAdditional
     * @return $this
     */
    public function setCompanyAdditional(?string $companyAdditional): self
    {
        $this->companyAdditional = $companyAdditional;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return $this
     */
    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * @param string $houseNumber
     * @return $this
     */
    public function setHouseNumber(string $houseNumber): self
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressAdditional(): ?string
    {
        return $this->addressAdditional;
    }

    /**
     * @param string|null $addressAdditional
     * @return $this
     */
    public function setAddressAdditional(?string $addressAdditional): self
    {
        $this->addressAdditional = $addressAdditional;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     * @return $this
     */
    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return $this
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    /**
     * @param string|null $mobilePhone
     * @return $this
     */
    public function setMobilePhone(?string $mobilePhone): self
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }

    /**
     * @param string|null $fax
     * @return $this
     */
    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMail(): ?string
    {
        return $this->mail;
    }

    /**
     * @param string|null $mail
     * @return $this
     */
    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }
}
