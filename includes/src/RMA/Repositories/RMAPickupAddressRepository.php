<?php declare(strict_types=1);

namespace JTL\RMA\Repositories;

use JTL\Abstracts\AbstractRepositoryTim;

/**
 * Class RMAReasonRepository
 * @package JTL\RMA
 */
class RMAPickupAddressRepository extends AbstractRepositoryTim
{

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return [
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
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array
    {
        $default = [
            'id'                => 0,
            'customerID'        => 0,
            'salutation'        => '',
            'firstName'         => '',
            'lastName'          => '',
            'academicTitle'     => null,
            'companyName'       => null,
            'companyAdditional' => null,
            'street'            => '',
            'houseNumber'       => '',
            'addressAdditional' => null,
            'postalCode'        => '',
            'city'              => '',
            'state'             => '',
            'country'           => '',
            'phone'             => null,
            'mobilePhone'       => null,
            'fax'               => null,
            'mail'              => null,
            'hash'              => ''
        ];
        return $this->combineData($default, $data);
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_reasons';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
