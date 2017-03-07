<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class TwoFAemergency
 */
class TwoFAemergency
{

    /**
     * all the generated emergency-codes, in plain-text
     *
     * @var array
     */
    private $vEmergeCodes;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->vEmergeCodes = [];
    }


    /**
     * create a pool of emergency-codes
     * for the current admin-account and store them in the DB.
     *
     * @param object  user-data, as delivered from TwoFA-object
     * @return array  new created emergency-codes (as written into the DB)
     */
    public function createNewCodes($oUserTupel)
    {
        $iCodeCount     = 10; // generate 10 codes (maybe should placed into a config)
        $szSqlRowValues = '';
        for ($i = 0; $i < $iCodeCount; $i++) {
            $szEmergeCode         = substr(md5(rand(1000,9000)), 0, 16);
            $this->vEmergeCodes[] = $szEmergeCode;

            if ('' !== $szSqlRowValues) {
                $szSqlRowValues .= ', ';
            }
            $szEmergeCode    = password_hash($szEmergeCode, PASSWORD_DEFAULT);
            $szSqlRowValues .= '('.$oUserTupel->kAdminlogin.', "'.$szEmergeCode.'")';
        }
        // now write into the DB what we got till now
        $result = Shop::DB()->executeQuery(
            'INSERT INTO `tadmin2facodes`(`kAdminlogin`, `cEmergencyCode`) VALUES' . $szSqlRowValues
            , 3);

        return $this->vEmergeCodes;
    }


    /**
     * delete all the existing codes for the given user
     *
     * @param object  user-data, as delivered from TwoFA-object
     */
    public function removeExistingCodes($oUserTupel)
    {
        Shop::DB()->executeQuery(
            'DELETE FROM `tadmin2facodes` WHERE `kAdminlogin` = ' . $oUserTupel->kAdminlogin
            , 3);
    }


    /**
     * check a given code for his existence in a given users emergency-code pool
     *
     * @param integer   admin-account ID
     * @param string    code, as typed in the login-fields
     * @return boolean  true="valid emergency-code", false="not a valid emergency-code"
     */
    public function isValidEmergencyCode($iAdminID, $szCode)
    {
        $voHashes = Shop::DB()->executeQuery(
            'SELECT `cEmergencyCode` FROM `tadmin2facodes` WHERE `kAdminlogin` = ' . $iAdminID
            , 2);
        if (1 > count($voHashes)) {
            return false; // no emergency-codes are there
        }

        foreach ($voHashes as $oElement) {
            if (true === password_verify($szCode, $oElement->cEmergencyCode)) {
                // valid code found. remove it from DB and return a 'true'
                Shop::DB()->executeQuery(
                    'DELETE FROM tadmin2facodes WHERE kAdminlogin = ' . $iAdminID . ' AND cEmergencyCode = "' . $oElement->cEmergencyCode . '"'
                    , 3);
                return true;             }
        }
        return false; // not a valid emergency code, so no further action here
    }

}
