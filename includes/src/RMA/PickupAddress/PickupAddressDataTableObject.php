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
     * @var string
     */
    protected string $academicTitle = '';
    
    /**
     * @var string
     */
    protected string $companyName = '';
    
    /**
     * @var string
     */
    protected string $companyAdditional = '';
    
    /**
     * @var string
     */

    protected string $street = '';
    /**
     * @var string
     */

    protected string $houseNumber = '';
    
    /**
     * @var string
     */
    protected string $addressAdditional = '';
    
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
     * @var string
     */
    protected string $phone = '';
    
    /**
     * @var string
     */
    protected string $mobilePhone = '';
    
    /**
     * @var string
     */
    protected string $fax = '';
    
    /**
     * @var string
     */
    protected string $mail = '';
    
    
    
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
