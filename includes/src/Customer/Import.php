<?php declare(strict_types=1);

namespace JTL\Customer;

use InvalidArgumentException;
use JTL\DB\DbInterface;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Services\JTL\PasswordServiceInterface;
use JTL\Shop;
use PHPMailer\PHPMailer\Exception;

/**
 * Class Import
 * @package JTL\Customer
 */
class Import
{
    protected const DEFAULT_ACCEPTED_FIELDS = [
        'cKundenNr',
        'cPasswort',
        'cAnrede',
        'cTitel',
        'cVorname',
        'cNachname',
        'cFirma',
        'cZusatz',
        'cStrasse',
        'cHausnummer',
        'cAdressZusatz',
        'cPLZ',
        'cOrt',
        'cBundesland',
        'cLand',
        'cTel',
        'cMobil',
        'cFax',
        'cMail',
        'cUSTID',
        'cWWW',
        'fGuthaben',
        'cNewsletter',
        'dGeburtstag',
        'fRabatt',
        'cHerkunft',
        'dErstellt',
        'cAktiv'
    ];

    /**
     * @var int
     */
    private int $customerGroupID = 1;

    /**
     * @var int
     */
    private int $languageID = 1;

    /**
     * @var bool
     */
    private bool $usePasswordsFromCsv = false;

    /**
     * @var Mailer
     */
    private Mailer $mailer;

    /**
     * @var PasswordServiceInterface
     */
    private PasswordServiceInterface $passwordService;

    /**
     * @var string|null
     */
    private ?string $defaultCountryCode = null;

    /**
     * @var string[]
     */
    private array $errors = [];

    /**
     * @var int
     */
    private int $importedRowsCount = 0;

    /**
     * @var int[]
     */
    private array $noPasswordCustomerIds = [];

    /**
     * Import constructor.
     * @param DbInterface $db
     * @param array       $acceptedFields
     */
    public function __construct(
        private readonly DbInterface $db,
        private readonly array       $acceptedFields = self::DEFAULT_ACCEPTED_FIELDS
    ) {
        $this->passwordService = Shop::Container()->getPasswordService();
        $this->mailer          = Shop::Container()->get(Mailer::class);
        $this->initDefaultCountry();
    }

    /**
     * @param string $filename
     * @return bool
     * @throws InvalidArgumentException
     */
    public function processFile(string $filename): bool
    {
        $this->errors                = [];
        $this->importedRowsCount     = 0;
        $this->noPasswordCustomerIds = [];

        $file = \fopen($filename, 'rb');
        if ($file === false) {
            throw new InvalidArgumentException('Cannot open file ' . $filename);
        }

        $delimiter = \JTL\CSV\Import::getCsvDelimiter($filename);
        $head      = \fgetcsv($file, null, $delimiter);
        if ($head === false) {
            $this->errors[] = \__('errorFormatNotFound');
            \fclose($file);
            return false;
        }

        $format = $this->validateHead($head);
        if ($format === false) {
            $this->errors[] = \__('errorFormatNotFound');
            \fclose($file);
            return false;
        }

        $index = 1;
        while (($data = \fgetcsv($file, null, $delimiter)) !== false) {
            if ($this->processLine($index, $format, $data)) {
                $this->importedRowsCount++;
            }
            $index++;
        }

        \fclose($file);
        return \count($this->errors) === 0;
    }

    /**
     * @param string[] $head
     * @return string[]|false
     */
    protected function validateHead(array $head): array|bool
    {
        if (!\in_array('cMail', $head, true)) {
            return false;
        }
        if (\in_array('cPasswort', $head, true)) {
            $this->usePasswordsFromCsv = true;
        }
        return \array_map(
            function ($field) {
                return \in_array($field, $this->acceptedFields, true) ? $field : false;
            },
            $head
        );
    }

    /**
     * @param int   $index
     * @param array $format
     * @param array $values
     * @return bool
     * @throws Exception
     * @throws \SmartyException
     */
    protected function processLine(int $index, array $format, array $values): bool
    {
        $customer = $this->getCustomer();
        foreach ($format as $i => $fieldName) {
            if ($fieldName !== false) {
                $customer->{$fieldName} = $values[$i] ?? null;
            }
        }

        if (Text::filterEmailAddress($customer->cMail) === false) {
            $this->errors[] = \__('row') . ' ' . $index . ': '
                . \sprintf(\__('errorInvalidEmail'), $customer->cMail);
            return false;
        }

        if ($this->usePasswordsFromCsv === true
            && (!$customer->cPasswort || $customer->cPasswort === 'd41d8cd98f00b204e9800998ecf8427e')
        ) {
            $this->errors[] = \__('row') . ' ' . $index . ': ' . \__('errorNoPassword');
            return false;
        }

        if (!$customer->cNachname) {
            $this->errors[] = \__('row') . ' ' . $index . ': ' . \__('errorNoSurname');
            return false;
        }

        $oldMail = $this->db->select('tkunde', 'cMail', $customer->cMail);
        if ($oldMail !== null && $oldMail->kKunde > 0) {
            $this->errors[] = \__('row') . ' ' . $index . ': ' . \sprintf(\__('errorEmailDuplicate'), $customer->cMail);
            return false;
        }

        if ($customer->cAnrede === 'f' || \mb_convert_case($customer->cAnrede, \MB_CASE_LOWER) === 'frau') {
            $customer->cAnrede = 'w';
        } elseif ($customer->cAnrede === 'h' || \mb_convert_case($customer->cAnrede, \MB_CASE_LOWER) === 'herr') {
            $customer->cAnrede = 'm';
        }

        if ($customer->cNewsletter === '1' || $customer->cNewsletter === 'y' || $customer->cNewsletter === 'Y') {
            $customer->cNewsletter = 'Y';
        } else {
            $customer->cNewsletter = 'N';
        }

        if (empty($customer->cLand) && $this->defaultCountryCode !== null) {
            $customer->cLand = $this->defaultCountryCode;
        }

        if ($this->usePasswordsFromCsv === false) {
            $password            = $this->passwordService->generate(\PASSWORD_DEFAULT_LENGTH);
            $customer->cPasswort = $this->passwordService->hash($password);
        }

        if ($customer->insertInDB() === 0) {
            $this->errors[] = \__('row') . ' ' . $index . ': ' . \__('errorImportRecord');
            return false;
        }

        if ($this->usePasswordsFromCsv === false) {
            $this->noPasswordCustomerIds[] = $customer->kKunde;
        }

        return true;
    }

    protected function initDefaultCountry(): void
    {
        $data = $this->db->getSingleObject(
            "SELECT cWert AS cLand 
                FROM teinstellungen 
                WHERE cName = 'kundenregistrierung_standardland'"
        );
        if ($data !== null && \mb_strlen($data->cLand) > 0) {
            $this->defaultCountryCode = $data->cLand;
        }
    }

    /**
     * @return Customer
     */
    private function getCustomer(): Customer
    {
        $customer                = new Customer();
        $customer->kKundengruppe = $this->getCustomerGroupID();
        $customer->kSprache      = $this->getLanguageID();
        $customer->cAbgeholt     = 'Y';
        $customer->cSperre       = 'N';
        $customer->cAktiv        = 'Y';
        $customer->nRegistriert  = 1;
        $customer->dErstellt     = 'NOW()';

        return $customer;
    }

    /**
     * @param int[] $customerIds
     * @return void
     * @throws Exception
     * @throws \SmartyException
     */
    public function notifyCustomers(array $customerIds): void
    {
        $service = Shop::Container()->getPasswordService();
        foreach ($customerIds as $customerId) {
            $customer = new Customer($customerId, $service, $this->db);
            $this->notifyCustomer($customer);
        }
    }

    /**
     * @param Customer $customer
     * @return bool
     * @throws Exception
     * @throws \SmartyException
     */
    private function notifyCustomer(Customer $customer): bool
    {
        $customer->cPasswortKlartext = 'Plaintext passwords are deprecated. Please update your email template!';

        return $this->mailer->send(
            (new Mail())->createFromTemplateID(
                \MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER,
                (object)['tkunde' => $customer]
            )
        );
    }

    /**
     * @return int
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @param int $customerGroupID
     */
    public function setCustomerGroupID(int $customerGroupID): void
    {
        $this->customerGroupID = $customerGroupID;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int $languageID
     */
    public function setLanguageID(int $languageID): void
    {
        $this->languageID = $languageID;
    }

    /**
     * @return string|null
     */
    public function getDefaultCountryCode(): ?string
    {
        return $this->defaultCountryCode;
    }

    /**
     * @param string|null $defaultCountryCode
     */
    public function setDefaultCountryCode(?string $defaultCountryCode): void
    {
        $this->defaultCountryCode = $defaultCountryCode;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[] $errors
     * @return Import
     */
    public function setErrors(array $errors): Import
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return int
     */
    public function getImportedRowsCount(): int
    {
        return $this->importedRowsCount;
    }

    /**
     * @return int[]
     */
    public function getNoPasswordCustomerIds(): array
    {
        return $this->noPasswordCustomerIds;
    }
}
