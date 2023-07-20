<?php declare(strict_types=1);

namespace JTL\RMA\DomainObjects;

use JTL\DataObjects\AbstractDomainObject;
use JTL\DataObjects\DomainObjectInterface;

/**
 * Class RMAHistoryDomainObject
 * @package JTL\RMA
 */
readonly class RMAPickupAddressDomainObject extends AbstractDomainObject implements DomainObjectInterface
{

    /**
     * @param int $id
     * @param int $customerID
     * @param string $salutation
     * @param string $firstName
     * @param string $lastName
     * @param string|null $academicTitle
     * @param string|null $companyName
     * @param string|null $companyAdditional
     * @param string $street
     * @param string $houseNumber
     * @param string|null $addressAdditional
     * @param string $postalCode
     * @param string $city
     * @param string $state
     * @param string $country
     * @param string|null $phone
     * @param string|null $mobilePhone
     * @param string|null $fax
     * @param string|null $mail
     * @param string $hash
     */
    public function __construct(
        public int $id,
        public int $customerID,
        public string $salutation,
        public string $firstName,
        public string $lastName,
        public ?string $academicTitle,
        public ?string $companyName,
        public ?string $companyAdditional,
        public string $street,
        public string $houseNumber,
        public ?string $addressAdditional,
        public string $postalCode,
        public string $city,
        public string $state,
        public string $country,
        public ?string $phone,
        public ?string $mobilePhone,
        public ?string $fax,
        public ?string $mail,
        public string $hash
    ) {
        parent::__construct();
    }
}
