<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class TwoFAEmergency
 */
class TwoFAEmergency
{
    /**
     * all the generated emergency-codes, in plain-text
     *
     * @var array
     */
    private $vEmergeCodes;

    /**
     * generate 10 codes (maybe should placed into a config)
     *
     * @var integer
     */
    private $iCodeCount;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->vEmergeCodes = [];
        $this->iCodeCount   = 10;
    }

    /**
     * create a pool of emergency-codes
     * for the current admin-account and store them in the DB.
     *
     * @param object $oUserTuple - user-data, as delivered from TwoFA-object
     * @return array - new created emergency-codes (as written into the DB)
     * @throws Exception
     */
    public function createNewCodes($oUserTuple): array
    {
        $passwordService = Shop::Container()->getPasswordService();
        $vAnalogyArray   = [];
        $szSqlRowValues  = '';
        $iValCount       = 'a';
        for ($i = 0; $i < $this->iCodeCount; $i++) {
            $szEmergeCode         = substr(md5(rand(1000, 9000)), 0, 16);
            $this->vEmergeCodes[] = $szEmergeCode;

            if ('' !== $szSqlRowValues) {
                $szSqlRowValues .= ', ';
            }
            $szEmergeCode = $passwordService->hash($szEmergeCode);

            // to prevent the fireing from within a loop against the DB
            // we build a values-string (like this: "(:a, :b), (:c, :d), ... " )
            // and an according array
            $vAnalogyArray[$iValCount] = $oUserTuple->kAdminlogin;
            $szSqlRowValues            .= '(:' . $iValCount . ',';
            $iValCount++;
            $vAnalogyArray[$iValCount] = $szEmergeCode;
            $szSqlRowValues            .= ' :' . $iValCount . ')';
            $iValCount++;
        }
        // now write into the DB what we got till now
        Shop::Container()->getDB()->executeQueryPrepared(
            'INSERT INTO `tadmin2facodes`(`kAdminlogin`, `cEmergencyCode`) VALUES' . $szSqlRowValues,
            $vAnalogyArray,
            \DB\ReturnType::AFFECTED_ROWS
        );

        return $this->vEmergeCodes;
    }

    /**
     * delete all the existing codes for the given user
     *
     * @param object $oUserTuple - user-data, as delivered from TwoFA-object
     */
    public function removeExistingCodes($oUserTuple)
    {
        $iEffectedRows = Shop::Container()->getDB()->deleteRow(
            'tadmin2facodes',
            'kAdminlogin',
            $oUserTuple->kAdminlogin
        );
        if ($this->iCodeCount !== $iEffectedRows) {
            Shop::Container()->getLogService()->error('2FA-Notfall-Codes für diesen Account konnten nicht entfernt werden.');
        }
    }

    /**
     * check a given code for his existence in a given users emergency-code pool
     * (keep this method as fast as possible, because it's called during each admin-login)
     *
     * @param int    $iAdminID - admin-account ID
     * @param string $szCode - code, as typed in the login-fields
     * @return bool - true="valid emergency-code", false="not a valid emergency-code"
     */
    public function isValidEmergencyCode($iAdminID, $szCode): bool
    {
        $voHashes = Shop::Container()->getDB()->selectArray('tadmin2facodes', 'kAdminlogin', $iAdminID);
        if (1 > count($voHashes)) {

            return false; // no emergency-codes are there
        }

        foreach ($voHashes as $oElement) {
            if (true === password_verify($szCode, $oElement->cEmergencyCode)) {
                // valid code found. remove it from DB and return a 'true'
                $iEffectedRows = Shop::Container()->getDB()->delete(
                    'tadmin2facodes',
                    ['kAdminlogin', 'cEmergencyCode'],
                    [$iAdminID, $oElement->cEmergencyCode],
                    \DB\ReturnType::AFFECTED_ROWS
                );
                if (1 !== $iEffectedRows) {
                    Shop::Container()->getLogService()->error('2FA-Notfall-Code konnte nicht gelöscht werden.');
                }

                return true;
            }
        }

        return false; // not a valid emergency code, so no further action here
    }
}
