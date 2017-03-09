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
     * @param object  user-data, as delivered from TwoFA-object
     * @return array  new created emergency-codes (as written into the DB)
     */
    public function createNewCodes($oUserTupel)
    {
        $szSqlRowValues = '';
        $iValCount      = 'a';
        for ($i = 0; $i < $this->iCodeCount; $i++) {
            $szEmergeCode         = substr(md5(rand(1000,9000)), 0, 16);
            $this->vEmergeCodes[] = $szEmergeCode;

            if ('' !== $szSqlRowValues) {
                $szSqlRowValues .= ', ';
            }
            $szEmergeCode    = password_hash($szEmergeCode, PASSWORD_DEFAULT);

            // to prevent the fireing from within a loop against the DB
            // we build a values-string (like this: "(:a, :b), (:c, :d), ... " )
            // and an according array
            $vAnalogyArray[$iValCount] = $oUserTupel->kAdminlogin;
            $szSqlRowValues .= '(:'.$iValCount.',';
            $iValCount++;
            $vAnalogyArray[$iValCount] = $szEmergeCode;
            $szSqlRowValues .= ' :'.$iValCount.')';
            $iValCount++;
        }
        // now write into the DB what we got till now
        $iEffectedRows = Shop::DB()->executeQueryPrepared(
            'INSERT INTO `tadmin2facodes`(`kAdminlogin`, `cEmergencyCode`) VALUES' . $szSqlRowValues
            , $vAnalogyArray
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
        $iEffectedRows = Shop::DB()->deleteRow('tadmin2facodes', 'kAdminlogin', $oUserTupel->kAdminlogin);
        if ($this->iCodeCount !== $iEffectedRows) {
            // write this error into shop-system-log
            Jtllog::writeLog('2FA-Notfall-Codes für diesen Account konnten nicht entfernt werden.', JTLLOG_LEVEL_ERROR, false);
        }
    }


    /**
     * check a given code for his existence in a given users emergency-code pool
     * (keep this methode as fast as possible, because it's called during each admin-login)
     *
     * @param integer   admin-account ID
     * @param string    code, as typed in the login-fields
     * @return boolean  true="valid emergency-code", false="not a valid emergency-code"
     */
    public function isValidEmergencyCode($iAdminID, $szCode)
    {
        $voHashes = Shop::DB()->selectArray('tadmin2facodes', 'kAdminlogin', $iAdminID);
        if (1 > count($voHashes)) {

            return false; // no emergency-codes are there
        }

        foreach ($voHashes as $oElement) {
            if (true === password_verify($szCode, $oElement->cEmergencyCode)) {
                // valid code found. remove it from DB and return a 'true'
                $iEffectedRows = Shop::DB()->executeQueryPrepared(
                    'DELETE FROM `tadmin2facodes` WHERE `kAdminlogin` = :kAdminlogin AND `cEmergencyCode` = :cEmergencyCode'
                    , ['kAdminlogin' => $iAdminID, 'cEmergencyCode' => $oElement->cEmergencyCode]
                    , 3
                );
                if (1 !== $iEffectedRows) {
                    Jtllog::writeLog('2FA-Notfall-Code konnten nicht gelöscht werden.', JTLLOG_LEVEL_ERROR, false);
                }

                return true;
            }
        }

        return false; // not a valid emergency code, so no further action here
    }

}
