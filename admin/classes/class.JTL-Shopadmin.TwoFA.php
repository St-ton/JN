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
    private $szShopName;


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
        $this->szShopName                 = '';
    }

    /**
     * tell the asker if 2FA is active for the "object-known" user
     *
     * @return bool - true="2FA is active"|false="2FA inactive"
     */
    public function is2FAauth()
    {
        return (bool)$this->oUserTuple->b2FAauth;
    }

    /**
     * tell the asker if a secret exists for that user
     *
     * @return bool - true="secret is there"|false="no secret"
     */
    public function is2FAauthSecretExist()
    {
        return ('' !== $this->oUserTuple->c2FAauthSecret);
    }

    /**
     * generate a new secret
     *
     * @return $this
     */
    public function createNewSecret()
    {
        // store a google-authenticator-object instance
        // (only if we want a new secret! (something like lazy loading))
        $this->oGA = new PHPGangsta_GoogleAuthenticator();

        if (null === $this->oUserTuple) {
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
     * @param string $szCode - numerical code from the login screen (the code, which the user has found on his mobile)
     * @return bool - true="code ist valid" | false="code is invalid"
     */
    public function isCodeValid($szCode)
    {
        // store a google-authenticator-object instance
        // (only if we check any credential! (something like lazy loading))
        //
        $this->oGA = new PHPGangsta_GoogleAuthenticator();

        // codes with a length over 6 chars are emergency-codes
        if (6 < strlen($szCode)) {
            // try to find this code in the emergency-code-pool
            $o2FAemergency = new TwoFAEmergency();

            return $o2FAemergency->isValidEmergencyCode($this->oUserTuple->kAdminlogin, $szCode);
        } else {

            return $this->oGA->verifyCode($this->oUserTuple->c2FAauthSecret, $szCode);
        }
    }


    /**
     * deliver a QR-code for the given user and his secret
     * (fetch only the name of the current shop from the DB too)
     *
     * @return string - generated QR-code
     */
    public function getQRcode()
    {
        if ('' !== $this->oUserTuple->c2FAauthSecret) {
            // create the QR-code
            $szQRString = new QRCode(
                  'otpauth://totp/'.rawurlencode('JTL-Shop ' . $this->oUserTuple->cLogin . '@' . $this->getShopName())
                . '?secret=' . $this->oUserTuple->c2FAauthSecret
                . '&issuer=JTL-Software'
                , new QRString()
            );

            return $szQRString->output();
        }

        return ''; // better return a empty string instead of a bar-code with empty secret!
    }

    /**
     * fetch a tupel of user-data from the DB, by his ID(`kAdminlogin`)
     * (store the fetched data in this object)
     *
     * @param int - the (DB-)id of this user-account
     */
    public function setUserByID($iID)
    {
        $this->oUserTuple = Shop::DB()->select('tadminlogin', 'kAdminlogin', (int)$iID);
    }

    /**
     * fetch  a tupel of user-data from the DB, by his name(`cLogin`)
     * this setter can called too, if the user is unknown yet
     * (store the fetched data in this object)
     *
     * @param string - the users login-name
     */
    public function setUserByName($szUserName)
    {
        // write at least the user's name we get via e.g. ajax
        $this->oUserTuple->cLogin = $szUserName;
        // check if we know that user yet
        if ($oTuple = Shop::DB()->select('tadminlogin', 'cLogin', $szUserName)) {
            $this->oUserTuple = $oTuple;
        }
    }

    /**
     * deliver the account-data, if there are any
     *
     * @return object  accountdata if there're any, or null
     */
    public function getUserTuple()
    {
        return $this->oUserTuple ? $this->oUserTuple : null;
    }

    /**
     * find out the global shop-name, if anyone administer more than one shop
     *
     * @return string  the name of the current shop
     */
    public function getShopName()
    {
        if ('' === $this->szShopName) {
            $oResult          = Shop::DB()->select('teinstellungen', 'cName', 'global_shopname');
            $this->szShopName = ('' !== $oResult->cWert) ? $oResult->cWert : '';
        }

        return $this->szShopName;
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
     * @param $userName
     * @return string
     */
    public static function getNewTwoFA($userName)
    {
        $oTwoFA = new TwoFA();
        $oTwoFA->setUserByName($userName);

        $oUserData           = new stdClass();
        $oUserData->szSecret = $oTwoFA->createNewSecret()->getSecret();
        $oUserData->szQRcode = $oTwoFA->getQRcode();

        return json_encode($oUserData);
    }

    /**
     * @param $userName
     * @return string
     */
    public static function genTwoFAEmergencyCodes($userName)
    {
        $oTwoFA = new TwoFA();
        $oTwoFA->setUserByName($userName);

        $data            = new stdClass();
        $data->loginName = $oTwoFA->getUserTuple()->cLogin;
        $data->shopName  = $oTwoFA->getShopName();

        /*
        // create, what the user can print out
        $szText  = '<h4>JTL-shop Backend Notfall-Codes</h4>';
        $szText .= 'Account: <b>' . $oTwoFA->getUserTuple()->cLogin . '</b><br>';
        $szText .= 'Shop: <b>' . $oTwoFA->getShopName() . '</b><br><br>';
        */

        $oTwoFAgenEmergCodes = new TwoFAEmergency();
        $oTwoFAgenEmergCodes->removeExistingCodes($oTwoFA->getUserTuple());

        $data->vCodes = $oTwoFAgenEmergCodes->createNewCodes($oTwoFA->getUserTuple());

        /*
        $szText      .= '<font face = "monospace" size = "+1">';
        $nCol         = 0;
        $iCodesLength = count($vCodes);
        for ($i = 0; $i < $iCodesLength; $i++) {
            if (1 > $nCol) {
                $szText .= '<span style="padding:3px;">' . $vCodes[$i] . '</span>';
                $nCol++;
            } else {
                $szText .= $vCodes[$i] . '<br>';
                $nCol    = 0;
            }
        }
        $szText .= '</font>';

        return $szText;
        */

        return $data;
    }
}
