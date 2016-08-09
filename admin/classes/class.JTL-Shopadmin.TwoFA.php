<?php
/**
 *
 * PRE-REQUISITES
 * prepare the database:
 *     mysql$ ALTER TABLE tadminlogin ADD b2FAauth tinyint(1) default 0, ADD c2FAauthSecret varchar(100) default '';
 *
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @author clemens, 2016-07-11
 */

// TwoFA - we need name-space-definitions for the QRCode-generator (mandatory!)
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\Output\QRStringOptions;

/**
 * Class TwoFA
 */
class TwoFA
{
    /**
     * @var string
     */
    private $szUserName = '';

    /**
     * @var null|PHPGangsta_GoogleAuthenticator
     */
    private $oGA = null;

    /**
     * @var bool
     */
    public $TwoFAauth = false;

    /**
     * CONSIDER: never give this to the world outside the application!
     * @var string
     */
    private $TwoFAauthSecret = '';

    /**
     * constructor
     */
    public function __construct()
    {
        // nothing to be done here, for the moment
    }

    /**
     * store the users name in our properties.
     * we need this name to find the appropriate user (and his secret) in the DB.
     *
     * @param string $szUserName - the name of our user
     * @return $this
     */
    public function setUser($szUserName)
    {
        $this->szUserName = $szUserName;

        $oTupel                = Shop::DB()->select('tadminlogin', 'cLogin', $this->szUserName);
        $this->TwoFAauth       = (boolean)$oTupel->b2FAauth;
        $this->TwoFAauthSecret = $oTupel->c2FAauthSecret;

        return $this;
    }

    /**
     * tell the asker if 2FA is active for that user
     *
     * @return bool - true="2FA is active"|false="2FA inactive"
     */
    public function is2FAauth()
    {
        return $this->TwoFAauth;
    }

    /**
     * tell the asker if a secret exists for that user
     *
     * @return bool - true="secret is there"|false="no secret"
     */
    public function is2FAauthSecretExist()
    {
        return ('' !== $this->TwoFAauthSecret);
    }


    /**
     * @return $this
     */
    public function createNewSecret()
    {
        // store a google-authenticator-object instance
        // (only when we check any credential! (something like lazy loading))
        $this->oGA = new PHPGangsta_GoogleAuthenticator();

        $szNewSecret         = $this->oGA->createSecret();
        $upd                 = new stdClass();
        $upd->c2FAauthSecret = $szNewSecret;
        Shop::DB()->update('tadminlogin', 'cLogin', $this->szUserName, $upd);

        return $this->setUser($this->szUserName); // update our object-properties
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
        // (only when we check any credential! (something like lazy loading))
        $this->oGA = new PHPGangsta_GoogleAuthenticator();

        $szSecret = $this->TwoFAauthSecret; // fetch the known secret of our user

        return $this->oGA->verifyCode($szSecret, $szCode);
    }

    /**
     * deliver a QR-code for the given user and his secret
     *
     * @return string - generated QR-code
     */
    public function getQRcode()
    {
        if ('' !== $this->TwoFAauthSecret) {
            // find out the global shop-name, if anyone administer more than one shop
            $vResult = Shop::DB()->select('teinstellungen', 'cName', 'global_shopname');
            $szShopName = ('' !== $vResult->cWert) ? urlencode($vResult->cWert) : '';

            // create the QR-code
            //
            $szQRString = new QRCode('otpauth://totp/JTL-Shop%20Admin%20' . $szShopName . '?'
                . 'secret=' . $this->TwoFAauthSecret
                . '&issuer=JTL-Software'
                , new QRString()
            )
            ;

            return $szQRString->output();
        }

        return ''; // better return a empty string instead of a bar-code with empty secret!
    }
}
