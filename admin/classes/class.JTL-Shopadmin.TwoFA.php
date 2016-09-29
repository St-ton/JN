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
     * @var null|PHPGangsta_GoogleAuthenticator
     */
    private $oGA = null;

    /**
     * @var object
     */
    private $oUserTupel;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->oUserTupel                 = new stdClass();
        $this->oUserTupel->cLogin         = '';
        $this->oUserTupel->b2FAauth       = false;
        $this->oUserTupel->c2FAauthSecret = '';
    }

    /**
     * tell the asker if 2FA is active for the "object-known" user
     *
     * @return bool - true="2FA is active"|false="2FA inactive"
     */
    public function is2FAauth()
    {
        return (bool)$this->oUserTupel->b2FAauth;
    }

    /**
     * tell the asker if a secret exists for that user
     *
     * @param void
     * @return bool - true="secret is there"|false="no secret"
     */
    public function is2FAauthSecretExist()
    {
        return ('' !== $this->oUserTupel->c2FAauthSecret);
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

        if(null === $this->oUserTupel) {
            $this->oUserTupel = new stdClass();
        }

        $this->oUserTupel->c2FAauthSecret = $this->oGA->createSecret();

        return $this;
    }

    /**
     * to save this secret, if the user decides to save the new admin-credetials
     *
     * @return string - something like "2BHAADRCQLA7IMH7"
     */
    public function getSecret()
    {
        return $this->oUserTupel->c2FAauthSecret;
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

        return $this->oGA->verifyCode($this->oUserTupel->c2FAauthSecret, $szCode);
    }

    /**
     * deliver a QR-code for the given user and his secret
     * (fetch only the name of the current shop from the DB too)
     *
     * @return string - generated QR-code
     */
    public function getQRcode()
    {
        if ('' !== $this->oUserTupel->c2FAauthSecret) {

            // find out the global shop-name, if anyone administer more than one shop
            //
            $oResult = Shop::DB()->select('teinstellungen', 'cName', 'global_shopname');
            $szShopName = ('' !== $oResult->cWert) ? $oResult->cWert : '';

            // create the QR-code
            //
            $szQRString = new QRCode(
                  'otpauth://totp/'.rawurlencode('JTL-Shop ' . $this->oUserTupel->cLogin . '@' . $szShopName)
                . '?secret=' . $this->oUserTupel->c2FAauthSecret
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
     * @return void
     */
    public function setUserByID($iID)
    {
        $this->oUserTupel = Shop::DB()->select('tadminlogin', 'kAdminlogin', (int)$iID);
    }

    /**
     * fetch  a tupel of user-data from the DB, by his name(`cLogin`)
     * this setter can called too, if the user is unknown yet
     * (store the fetched data in this object)
     *
     * @param string - the users login-name
     * @return void
     */
    public function setUserByName($szUserName)
    {
        // write at least the user's name we get via e.g. ajax
        $this->oUserTupel->cLogin = $szUserName;
        // check if we know that user yet
        if($oTupel = Shop::DB()->select('tadminlogin', 'cLogin', $szUserName)) {
            $this->oUserTupel = $oTupel;
        }
    }

    /**
     * serialize this objects data into a string,
     * mostly for debugging and logging
     *
     * @return string - object-data
     */
    public function __toString()
    {
        return print_r($this->oUserTupel, true);
    }

}
