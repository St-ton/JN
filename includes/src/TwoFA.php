<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use qrcodegenerator\QRCode\QRCode;
use qrcodegenerator\QRCode\Output\QRString;

/**
 * Class TwoFA
 */
class TwoFA
{
    /**
     * TwoFactorAuth-object
     *
     * @var PHPGangsta_GoogleAuthenticator
     */
    private $oGA;

    /**
     * user-account data
     *
     * @var stdClass
     */
    private $oUserTuple;

    /**
     * the name of the current shop
     *
     * @var string
     */
    private $shopName;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->oUserTuple                 = new stdClass();
        $this->oUserTuple->kAdminlogin    = 0;
        $this->oUserTuple->cLogin         = '';
        $this->oUserTuple->b2FAauth       = false;
        $this->oUserTuple->c2FAauthSecret = '';
        $this->shopName                   = '';
    }

    /**
     * tell the asker if 2FA is active for the "object-known" user
     *
     * @return bool - true="2FA is active"|false="2FA inactive"
     */
    public function is2FAauth(): bool
    {
        return (bool)$this->oUserTuple->b2FAauth;
    }

    /**
     * tell the asker if a secret exists for that user
     *
     * @return bool - true="secret is there"|false="no secret"
     */
    public function is2FAauthSecretExist(): bool
    {
        return $this->oUserTuple->c2FAauthSecret !== '';
    }

    /**
     * generate a new secret
     *
     * @return $this
     */
    public function createNewSecret(): self
    {
        // store a google-authenticator-object instance
        // (only if we want a new secret! (something like lazy loading))
        $this->oGA = new PHPGangsta_GoogleAuthenticator();

        if ($this->oUserTuple === null) {
            $this->oUserTuple = new stdClass();
        }
        $this->oUserTuple->c2FAauthSecret = $this->oGA->createSecret();

        return $this;
    }

    /**
     * to save this secret, if the user decides to save the new admin-credetials
     *
     * @return string - something like "2BHAADRCQLA7IMH7"
     */
    public function getSecret()
    {
        return $this->oUserTuple->c2FAauthSecret;
    }

    /**
     * instantiate a authenticator-object and try to verify the given code
     * by load the users secret
     *
     * @param string $code - numerical code from the login screen (the code, which the user has found on his mobile)
     * @return bool - true="code ist valid" | false="code is invalid"
     */
    public function isCodeValid($code): bool
    {
        // store a google-authenticator-object instance
        // (only if we check any credential! (something like lazy loading))
        $this->oGA = new PHPGangsta_GoogleAuthenticator();
        // codes with a length over 6 chars are emergency-codes
        if (6 < strlen($code)) {
            // try to find this code in the emergency-code-pool
            $o2FAemergency = new TwoFAEmergency();

            return $o2FAemergency->isValidEmergencyCode($this->oUserTuple->kAdminlogin, $code);
        }
        return $this->oGA->verifyCode($this->oUserTuple->c2FAauthSecret, $code);
    }

    /**
     * deliver a QR-code for the given user and his secret
     * (fetch only the name of the current shop from the DB too)
     *
     * @return string - generated QR-code
     */
    public function getQRcode(): string
    {
        if ($this->oUserTuple->c2FAauthSecret !== '') {
            $totpUrl = rawurlencode('JTL-Shop ' . $this->oUserTuple->cLogin . '@' . $this->getShopName());
            // by the QR-Code there are 63 bytes allowed for this URL-appendix
            // so we shorten that string (and we take care about the hex-character-replacements!)
            $overflow = strlen($totpUrl) - 63;
            if (0 < $overflow) {
                for ($i=0; $i < $overflow; $i++) {
                    if ('%' === $totpUrl[strlen($totpUrl)-3]) {
                        $totpUrl  = substr($totpUrl, 0, -3); // shorten by 3 byte..
                        $overflow -= 2;                         // ..and correct the counter (here nOverhang)
                    } else {
                        $totpUrl = substr($totpUrl, 0, -1);  // shorten by 1 byte
                    }
                }
            }
            // create the QR-code
            $qrCode = new QRCode(
                'otpauth://totp/' . $totpUrl .
                '?secret=' . $this->oUserTuple->c2FAauthSecret .
                '&issuer=JTL-Software',
                new QRString()
            );

            return $qrCode->output();
        }

        return ''; // better return a empty string instead of a bar-code with empty secret!
    }

    /**
     * fetch a tupel of user-data from the DB, by his ID(`kAdminlogin`)
     * (store the fetched data in this object)
     *
     * @param int - the (DB-)id of this user-account
     */
    public function setUserByID(int $iID): void
    {
        $this->oUserTuple = Shop::Container()->getDB()->select('tadminlogin', 'kAdminlogin', $iID);
    }

    /**
     * fetch  a tupel of user-data from the DB, by his name(`cLogin`)
     * this setter can called too, if the user is unknown yet
     * (store the fetched data in this object)
     *
     * @param string - the users login-name
     */
    public function setUserByName(string $userName): void
    {
        // write at least the user's name we get via e.g. ajax
        $this->oUserTuple->cLogin = $userName;
        // check if we know that user yet
        if (($oTuple = Shop::Container()->getDB()->select('tadminlogin', 'cLogin', $userName)) !== null) {
            $this->oUserTuple = $oTuple;
        }
    }

    /**
     * deliver the account-data, if there are any
     *
     * @return object|null - accountdata if there's any, or null
     */
    public function getUserTuple()
    {
        return $this->oUserTuple ?: null;
    }

    /**
     * find out the global shop-name, if anyone administer more than one shop
     *
     * @return string - the name of the current shop
     */
    public function getShopName(): string
    {
        if ($this->shopName !== '') {
            $result         = Shop::Container()->getDB()->select('teinstellungen', 'cName', 'global_shopname');
            $this->shopName = $result->cWert;
        }

        return trim($this->shopName);
    }


    /**
     * serialize this objects data into a string,
     * mostly for debugging and logging
     *
     * @return string - object-data
     */
    public function __toString()
    {
        return print_r($this->oUserTuple, true);
    }

    /**
     * @param string $userName
     * @return string
     */
    public static function getNewTwoFA($userName): string
    {
        $twoFA = new TwoFA();
        $twoFA->setUserByName($userName);

        $userData           = new stdClass();
        $userData->szSecret = $twoFA->createNewSecret()->getSecret();
        $userData->szQRcode = $twoFA->getQRcode();

        return json_encode($userData);
    }

    /**
     * @param string $userName
     * @return stdClass
     */
    public static function genTwoFAEmergencyCodes(string $userName): stdClass
    {
        $twoFA = new TwoFA();
        $twoFA->setUserByName($userName);

        $data            = new stdClass();
        $data->loginName = $twoFA->getUserTuple()->cLogin;
        $data->shopName  = $twoFA->getShopName();

        $emergencyCodes = new TwoFAEmergency();
        $emergencyCodes->removeExistingCodes($twoFA->getUserTuple());

        $data->vCodes = $emergencyCodes->createNewCodes($twoFA->getUserTuple());

        return $data;
    }
}
