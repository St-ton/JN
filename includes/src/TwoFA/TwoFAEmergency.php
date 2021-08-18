<?php declare(strict_types=1);

namespace JTL\TwoFA;

use Exception;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class TwoFAEmergency
 * @package JTL\TwoFA
 */
class TwoFAEmergency
{
    /**
     * all the generated emergency codes, in plain-text
     *
     * @var array
     */
    private $codes = [];

    /**
     * generate 10 codes
     *
     * @var int
     */
    private $codeCount = 10;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * TwoFAEmergency constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * create a pool of emergency-codes
     * for the current admin-account and store them in the DB.
     *
     * @param UserData $userData - user-data, as delivered from TwoFA-object
     * @return array - new created emergency-codes (as written into the DB)
     * @throws Exception
     */
    public function createNewCodes(UserData $userData): array
    {
        $passwordService = Shop::Container()->getPasswordService();
        $userID          = $userData->getID();
        for ($i = 0; $i < $this->codeCount; $i++) {
            $code          = \mb_substr(\md5((string)\random_int(1000, 9000)), 0, 16);
            $this->codes[] = $code;
            $hashed        = $passwordService->hash($code);
            $this->db->insert('tadmin2facodes', (object)['kAdminlogin' => $userID, 'cEmergencyCode' => $hashed]);
        }

        return $this->codes;
    }

    /**
     * delete all the existing codes for the given user
     *
     * @param UserData $userTuple - user data, as delivered from TwoFA-object
     * @todo
     */
    public function removeExistingCodes(UserData $userTuple): void
    {
        $effected = $this->db->deleteRow(
            'tadmin2facodes',
            'kAdminlogin',
            $userTuple->getID()
        );
        if ($this->codeCount !== $effected) {
            Shop::Container()->getLogService()->error(
                '2FA-Notfall-Codes für diesen Account konnten nicht entfernt werden.'
            );
        }
    }

    /**
     * check a given code for his existence in a given users emergency-code pool
     * (keep this method as fast as possible, because it's called during each admin-login)
     *
     * @param int    $adminID - admin-account ID
     * @param string $code - code, as typed in the login-fields
     * @return bool - true="valid emergency-code", false="not a valid emergency-code"
     * @todo
     */
    public function isValidEmergencyCode(int $adminID, string $code): bool
    {
        $hashes = $this->db->selectArray('tadmin2facodes', 'kAdminlogin', $adminID);
        if (\count($hashes) < 1) {
            return false; // no emergency-codes are there
        }

        foreach ($hashes as $item) {
            if (\password_verify($code, $item->cEmergencyCode) === true) {
                // valid code found. remove it from DB and return a 'true'
                $effected = $this->db->delete(
                    'tadmin2facodes',
                    ['kAdminlogin', 'cEmergencyCode'],
                    [$adminID, $item->cEmergencyCode]
                );
                if ($effected !== 1) {
                    Shop::Container()->getLogService()->error('2FA-Notfall-Code konnte nicht gelöscht werden.');
                }

                return true;
            }
        }

        return false; // not a valid emergency code, so no further action here
    }
}
