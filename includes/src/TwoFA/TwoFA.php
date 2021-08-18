<?php declare(strict_types=1);

namespace JTL\TwoFA;

use JTL\DB\DbInterface;
use PHPGangsta_GoogleAuthenticator;
use qrcodegenerator\QRCode\Output\QRString;
use qrcodegenerator\QRCode\QRCode;

/**
 * Class TwoFA
 * @package JTL\TwoFA
 */
class TwoFA
{
    /**
     * @var PHPGangsta_GoogleAuthenticator
     */
    private $authenticator;

    /**
     * @var UserData
     */
    private $userTuple;

    /**
     * @var string
     */
    private $shopName;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * TwoFA constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db        = $db;
        $this->userTuple = new UserData();
        $this->shopName  = '';
    }

    /**
     * @return bool
     */
    public function is2FAauth(): bool
    {
        return $this->userTuple->isUse2FA();
    }

    /**
     * @return bool
     */
    public function is2FAauthSecretExist(): bool
    {
        return $this->userTuple->getSecret() !== '';
    }

    /**
     * @return $this
     */
    public function createNewSecret(): self
    {
        // store a google-authenticator-object instance
        // (only if we want a new secret! (something like lazy loading))
        $this->authenticator = new PHPGangsta_GoogleAuthenticator();
        if ($this->userTuple === null) {
            $this->userTuple = new UserData();
        }
        $this->userTuple->setSecret($this->authenticator->createSecret());

        return $this;
    }

    /**
     * @return string - something like "2BHAADRCQLA7IMH7"
     */
    public function getSecret(): string
    {
        return $this->userTuple->getSecret();
    }

    /**
     * instantiate a authenticator-object and try to verify the given code
     *
     * @param string $code - numerical code from the login screen (the code, which the user has found on his mobile)
     * @return bool
     */
    public function isCodeValid(string $code): bool
    {
        // store a google-authenticator-object instance
        // (only if we check any credential! (something like lazy loading))
        $this->authenticator = new PHPGangsta_GoogleAuthenticator();
        // codes with a length over 6 chars are emergency-codes
        if (\mb_strlen($code) > 6) {
            // try to find this code in the emergency-code-pool
            $twoFAEmergency = new TwoFAEmergency($this->db);

            return $twoFAEmergency->isValidEmergencyCode($this->userTuple->getID(), $code);
        }
        return $this->authenticator->verifyCode($this->userTuple->getSecret(), $code);
    }

    /**
     * deliver a QR-code for the given user and his secret
     *
     * @return string - generated QR-code
     */
    public function getQRcode(): string
    {
        if ($this->userTuple->getSecret() === '') {
            return '';
        }
        $totpUrl = \rawurlencode('JTL-Shop ' . $this->userTuple->getName() . '@' . $this->getShopName());
        // by the QR-Code there are 63 bytes allowed for this URL-appendix
        // so we shorten that string (and we take care about the hex-character-replacements!)
        $overflow = \mb_strlen($totpUrl) - 63;
        if (0 < $overflow) {
            for ($i = 0; $i < $overflow; $i++) {
                if ($totpUrl[\mb_strlen($totpUrl) - 3] === '%') {
                    $totpUrl   = \mb_substr($totpUrl, 0, -3); // shorten by 3 byte..
                    $overflow -= 2;                         // ..and correct the counter (here nOverhang)
                } else {
                    $totpUrl = \mb_substr($totpUrl, 0, -1);  // shorten by 1 byte
                }
            }
        }
        // create the QR-code
        $qrCode = new QRCode(
            'otpauth://totp/' . $totpUrl . '?secret=' . $this->userTuple->getSecret() . '&issuer=JTL-Software',
            new QRString()
        );

        return $qrCode->output();
    }

    /**
     * @return UserData
     */
    public function getUserTuple(): UserData
    {
        return $this->userTuple;
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        if ($this->shopName === '') {
            $this->shopName = $this->db->select('teinstellungen', 'cName', 'global_shopname')->cWert;
        }

        return \trim($this->shopName);
    }


    /**
     * @return string - object-data
     */
    public function __toString()
    {
        return \print_r($this->userTuple, true);
    }

    /**
     * @param UserData $userData
     */
    public function setUserData(UserData $userData): void
    {
        $this->userTuple = $userData;
    }
}
