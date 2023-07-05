<?php declare(strict_types=1);

namespace JTL\Customer\Registration\Validator;

use JTL\Shop;

/**
 * Class AbstractValidator
 * @package JTL\Customer\Registration\Validator
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * @param array $data
     * @param array $config
     */
    public function __construct(protected array $data, protected array $config)
    {
    }

    /**
     * @param string $email
     * @param int    $customerID
     * @return bool
     * @former isEmailAvailable()
     * @since 5.2.0
     */
    public static function isEmailAvailable(string $email, int $customerID = 0): bool
    {
        return Shop::Container()->getDB()->getSingleObject(
            'SELECT *
                FROM tkunde
                WHERE cmail = :email
                  AND nRegistriert = 1
                AND kKunde != :customerID',
            ['email' => $email, 'customerID' => $customerID]
        ) === null;
    }

    /**
     * @param string $poCode
     * @param string $city
     * @param string $country
     * @return bool
     */
    public static function isValidAddress(string $poCode, string $city, string $country): bool
    {
        // Länder die wir mit Ihren Postleitzahlen in der Datenbank haben
        $supportedCountryCodes = Shop::Container()->getDB()->getCollection(
            'SELECT DISTINCT cLandISO FROM tplz'
        )
            ->pluck('cLandISO')
            ->map(static function (string $iso): string {
                return \mb_convert_case($iso, \MB_CASE_UPPER);
            })
            ->toArray();
        if (!\in_array(\mb_convert_case($country, \MB_CASE_UPPER), $supportedCountryCodes, true)) {
            return true;
        }
        $obj = Shop::Container()->getDB()->getSingleInt(
            'SELECT kPLZ
                FROM tplz
                WHERE cPLZ = :plz
                    AND INSTR(cOrt COLLATE utf8_german2_ci, :ort)
                    AND cLandISO = :land',
            'kPLZ',
            [
                'plz'  => $poCode,
                'ort'  => $city,
                'land' => $country
            ]
        );

        return $obj > 0;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }
}
