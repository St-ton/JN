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
    private int $id = 0;
    
    /**
     * @var int
     */
    private int $customerID = 0;
    
    /**
     * @var string
     */
    private string $salutation = '';
    
    /**
     * @var string
     */
    private string $firstName = '';
    
    /**
     * @var string
     */
    private string $lastName = '';
    
    /**
     * @var string
     */
    private string $academicTitle = '';
    
    /**
     * @var string
     */
    private string $companyName = '';
    
    /**
     * @var string
     */
    private string $companyAdditional = '';
    
    /**
     * @var string
     */
    
    private string $street = '';
    /**
     * @var string
     */
    
    private string $houseNumber = '';
    
    /**
     * @var string
     */
    private string $addressAdditional = '';
    
    /**
     * @var string
     */
    private string $postalCode = '';
    
    /**
     * @var string
     */
    private string $city = '';
    
    /**
     * @var string
     */
    private string $state = '';
    
    /**
     * @var string
     */
    private string $country = '';
    
    /**
     * @var string
     */
    private string $phone = '';
    
    /**
     * @var string
     */
    private string $mobilePhone = '';
    
    /**
     * @var string
     */
    private string $fax = '';
    
    /**
     * @var string
     */
    private string $mail = '';
    
    
    
    /**
     * @var string[]
     */
    private array $columnMapping = [
        'id'                => 'id',
        'customerID'        => 'customerID',
        'salutation'        => 'salutation',
        'firstName'         => 'firstName',
        'lastName'          => 'lastName',
        'academicTitle'     => 'academicTitle',
        'companyName'       => 'companyName',
        'companyAdditional' => 'companyAdditional',
        'street'            => 'street',
        'houseNumber'       => 'houseNumber',
        'addressAdditional' => 'addressAdditional',
        'postalCode'        => 'postalCode',
        'city'              => 'city',
        'state'             => 'state',
        'country'           => 'country',
        'phone'             => 'phone',
        'mobilePhone'       => 'mobilePhone',
        'fax'               => 'fax',
        'mail'              => 'mail'
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
     * @return mixed
     */
    public function getID(): mixed
    {
        return $this->{$this->getPrimaryKey()};
    }
    
    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return $this->columnMapping;
    }
}
