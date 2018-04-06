<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AdminAccount
 */
class AdminAccount
{
    const LOGIN_OK = 1;

    const ERROR_NOT_AUTHORIZED = 0;

    const ERROR_INVALID_PASSWORD = -1;

    const ERROR_INVALID_PASSWORD_LOCKED = -2;

    const ERROR_USER_NOT_FOUND = -3;

    const ERROR_USER_DISABLED = -4;

    const ERROR_LOGIN_EXPIRED = -5;

    const ERROR_TWO_FACTOR_AUTH_EXPIRED = -6;

    const ERROR_UNKNOWN = -7;

    /**
     * @var bool
     */
    private $_bLogged = false;

    /**
     * @var bool
     */
    private $twoFaAuthenticated = false;

    /**
     * @var \Monolog\Logger
     */
    private $authLogger;

    /**
     * @var \Mapper\AdminLoginStatusToLogLevel
     */
    private $levelMapper;

    /**
     * @var \Mapper\AdminLoginStatusMessageMapper
     */
    private $messageMapper;

    /**
     * @param bool $bInitialize
     */
    public function __construct($bInitialize = true)
    {
        $this->authLogger    = Shop::Container()->getBackendLogService();
        $this->messageMapper = new \Mapper\AdminLoginStatusMessageMapper();
        $this->levelMapper   = new \Mapper\AdminLoginStatusToLogLevel();
        if ($bInitialize) {
            AdminSession::getInstance();
            $this->_validateSession();
        }
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * checks user submitted hash against the ones saved in db
     *
     * @param string $hash - the hash received via email
     * @param string $mail - the admin account's email address
     * @return bool - true if successfully verified
     * @throws Exception
     */
    public function verifyResetPasswordHash($hash, $mail) : bool
    {
        $user = Shop::Container()->getDB()->select('tadminlogin', 'cMail', $mail);
        if ($user !== null) {
            //there should be a string <created_timestamp>:<hash> in the DB
            $timestampAndHash = explode(':', $user->cResetPasswordHash);
            if (count($timestampAndHash) === 2) {
                $timeStamp    = $timestampAndHash[0];
                $originalHash = $timestampAndHash[1];
                //check if the link is not expired (=24 hours valid)
                $createdAt = (new DateTime())->setTimestamp((int)$timeStamp);
                $now       = new DateTime();
                $diff      = $now->diff($createdAt);
                $secs      = ($diff->format('%a') * (60 * 60 * 24)); //total days
                $secs      += (int)$diff->format('%h') * (60 * 60); //hours
                $secs      += (int)$diff->format('%i') * 60; //minutes
                $secs      += (int)$diff->format('%s'); //seconds
                if ($secs > (60 * 60 * 24)) {
                    return false;
                }
                // check the submitted hash against the saved one
                return Shop::Container()->getPasswordService()->verify($hash, $originalHash);
            }
        }

        return false;
    }

    /**
     * creates hashes and sends mails for forgotten admin passwords
     *
     * @param string $mail - the admin account's email address
     * @return bool - true if valid admin account
     * @throws Exception
     */
    public function prepareResetPassword($mail)
    {
        $cryptoService            = Shop::Container()->getCryptoService();
        $passwordService          = Shop::Container()->getPasswordService();
        $now                      = new DateTime();
        $timestamp                = $now->format('U');
        $stringToSend             = md5($mail . $cryptoService->randomString(30));
        $_upd                     = new stdClass();
        $_upd->cResetPasswordHash = $timestamp . ':' . $passwordService->hash($stringToSend);
        $res                      = Shop::Container()->getDB()->update('tadminlogin', 'cMail', $mail, $_upd);
        if ($res > 0) {
            $user = Shop::Container()->getDB()->select('tadminlogin', 'cMail', $mail);
            require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
            $obj                    = new stdClass();
            $obj->passwordResetLink = Shop::getAdminURL() . '/pass.php?fpwh=' . $stringToSend . '&mail=' . $mail;
            $obj->cHash             = $stringToSend;
            $obj->mail              = new stdClass();
            $obj->mail->toEmail     = $mail;
            $obj->mail->toName      = $user->cLogin;
            sendeMail(MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN, $obj);

            return true;
        }

        return false;
    }

    /**
     * @param int    $code
     * @param string $user
     * @return int
     */
    private function handleLoginResult($code, $user) : int
    {
        $log = new \Model\AuthLogEntry();

        $log->setIP(getRealIp());
        $log->setCode($code);
        $log->setUser($user);

        $this->authLogger->log(
            $this->levelMapper->map($code),
            $this->messageMapper->map($code),
            $log->asArray()
        );

        return $code;
    }

    /**
     * @param string $cLogin
     * @param string $cPass
     * @return int
     * @throws Exception
     */
    public function login($cLogin, $cPass)
    {
        $oAdmin = Shop::Container()->getDB()->select(
            'tadminlogin',
            'cLogin',
            $cLogin,
            null,
            null,
            null,
            null,
            false,
            '*, UNIX_TIMESTAMP(dGueltigBis) AS dGueltigTS'
        );
        if ($oAdmin === null || !is_object($oAdmin)) {
            return $this->handleLoginResult(self::ERROR_USER_NOT_FOUND, $cLogin);
        }
        $oAdmin->kAdminlogingruppe = (int)$oAdmin->kAdminlogingruppe;
        if (!$oAdmin->bAktiv && $oAdmin->kAdminlogingruppe !== ADMINGROUP) {
            return $this->handleLoginResult(self::ERROR_USER_DISABLED, $cLogin);
        }
        if ($oAdmin->dGueltigTS && $oAdmin->kAdminlogingruppe !== ADMINGROUP && $oAdmin->dGueltigTS < time()) {
            return $this->handleLoginResult(self::ERROR_LOGIN_EXPIRED, $cLogin);
        }
        $verified     = false;
        $cPassCrypted = null;
        if (strlen($oAdmin->cPass) === 32) {
            // old md5 hash support
            if (md5($cPass) !== $oAdmin->cPass) {
                $this->_setRetryCount($oAdmin->cLogin);

                return $this->handleLoginResult(($oAdmin->nLoginVersuch + 1) >= 3
                    ? self::ERROR_INVALID_PASSWORD_LOCKED
                    : self::ERROR_INVALID_PASSWORD, $cLogin);
            }
            if (!isset($_SESSION['AdminAccount'])) {
                $_SESSION['AdminAccount'] = new stdClass();
            }
            // login successful - update password hash
            $_SESSION['AdminAccount']->cPass  = md5($cPass);
            $_SESSION['AdminAccount']->cLogin = $cLogin;
            $verified                         = true;
            if ($this->checkAndUpdateHash($cPass) === true) {
                $oAdmin = Shop::Container()->getDB()->select(
                    'tadminlogin',
                    'cLogin',
                    $cLogin,
                    null,
                    null,
                    null,
                    null,
                    false,
                    '*, UNIX_TIMESTAMP(dGueltigBis) AS dGueltigTS'
                );
            }
        } elseif (strlen($oAdmin->cPass) === 40) {
            //default login until Shop4
            $cPassCrypted = cryptPasswort($cPass, $oAdmin->cPass);
        } else {
            //new default login from 4.0 on
            $verified = password_verify($cPass, $oAdmin->cPass);
        }
        if ($verified === true || ($cPassCrypted !== null && $oAdmin->cPass === $cPassCrypted)) {
            // Wartungsmodus aktiv? Nein => loesche Session
            $settings = Shop::getSettings(CONF_GLOBAL);
            if ($settings['global']['wartungsmodus_aktiviert'] === 'N' && is_array($_SESSION) && count($_SESSION) > 0) {
                foreach ($_SESSION as $i => $xSession) {
                    unset($_SESSION[$i]);
                }
            }

            $this->_toSession($oAdmin);
            //check password hash and update if necessary
            $this->checkAndUpdateHash($cPass);
            if (!$this->getIsTwoFaAuthenticated()) {
                return $this->handleLoginResult(self::ERROR_TWO_FACTOR_AUTH_EXPIRED);
            }

            return $this->handleLoginResult($this->logged() ? self::LOGIN_OK : self::ERROR_NOT_AUTHORIZED, $cLogin);
        }
        $this->_setRetryCount($oAdmin->cLogin);

        return $this->handleLoginResult(($oAdmin->nLoginVersuch + 1) >= 3
            ? self::ERROR_INVALID_PASSWORD_LOCKED
            : self::ERROR_INVALID_PASSWORD, $cLogin);
    }

    /**
     * @return $this
     */
    public function logout()
    {
        $this->_bLogged = false;
        session_destroy();

        return $this;
    }

    /**
     * @return $this
     */
    public function lock()
    {
        $this->_bLogged = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function logged()
    {
        return $this->getIsTwoFaAuthenticated() && $this->getIsAuthenticated();
    }

    /**
     * @return bool
     */
    public function getIsAuthenticated()
    {
        return $this->_bLogged;
    }

    /**
     * @return bool
     */
    public function getIsTwoFaAuthenticated()
    {
        return $this->twoFaAuthenticated;
    }

    /**
     *
     */
    public function redirectOnFailure()
    {
        if (!$this->logged()) {
            $url = strpos(basename($_SERVER['REQUEST_URI']), 'logout.php') === false
                ? '?uri=' . base64_encode(basename($_SERVER['REQUEST_URI']))
                : '';
            header('Location: index.php' . $url);
            exit();
        }
    }

    /**
     * @return bool|stdClass
     */
    public function account()
    {
        return $this->getIsAuthenticated() ? $_SESSION['AdminAccount'] : false;
    }

    /**
     * @param string $cRecht
     * @param bool   $bRedirectToLogin
     * @param bool   $bShowNoAccessPage
     * @return bool
     */
    public function permission($cRecht, $bRedirectToLogin = false, $bShowNoAccessPage = false)
    {
        if ($bRedirectToLogin) {
            $this->redirectOnFailure();
        }
        // grant full access to admin
        if ($this->account() !== false && (int)$this->account()->oGroup->kAdminlogingruppe === ADMINGROUP) {
            return true;
        }
        $bAccess = (isset($_SESSION['AdminAccount']->oGroup) && is_object($_SESSION['AdminAccount']->oGroup)
            && is_array($_SESSION['AdminAccount']->oGroup->oPermission_arr)
            && in_array($cRecht, $_SESSION['AdminAccount']->oGroup->oPermission_arr, true));
        if ($bShowNoAccessPage && !$bAccess) {
            Shop::Smarty()->display('tpl_inc/berechtigung.tpl');
            exit;
        }

        return $bAccess;
    }

    /**
     * @param int   $nAdminLoginGroup
     * @param int   $nAdminMenuGroup
     * @return array
     */
    public function getVisibleMenu($nAdminLoginGroup, $nAdminMenuGroup)
    {
        $nAdminLoginGroup = (int)$nAdminLoginGroup;
        $nAdminMenuGroup  = (int)$nAdminMenuGroup;

        if ($nAdminLoginGroup === ADMINGROUP) {
            $oLink_arr = Shop::Container()->getDB()->selectAll(
                'tadminmenu',
                'kAdminmenueGruppe',
                $nAdminMenuGroup,
                '*',
                'cLinkname, nSort'
            );
        } else {
            $oLink_arr = Shop::Container()->getDB()->queryPrepared(
                'SELECT tadminmenu.* 
                    FROM tadminmenu 
                        JOIN tadminrechtegruppe 
                        ON tadminmenu.cRecht = tadminrechtegruppe.cRecht 
                    WHERE kAdminmenueGruppe = :kAdminmenueGruppe 
                        AND kAdminlogingruppe = :kAdminlogingruppe 
                    ORDER BY cLinkname, nSort;',
                [
                    'kAdminmenueGruppe' => $nAdminMenuGroup,
                    'kAdminlogingruppe' => $nAdminLoginGroup
                ],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
        }

        return $oLink_arr;
    }

    /**
     *
     */
    public function redirectOnUrl()
    {
        $cUrl       = Shop::getURL() . '/' . PFAD_ADMIN . 'index.php';
        $xParse_arr = parse_url($cUrl);
        $cHost      = $xParse_arr['host'];

        if (!empty($xParse_arr['port']) && (int)$xParse_arr['port'] > 0) {
            $cHost .= ':' . $xParse_arr['port'];
        }

        if (isset($_SERVER['HTTP_HOST']) && $cHost !== $_SERVER['HTTP_HOST'] && strlen($_SERVER['HTTP_HOST']) > 0) {
            header("Location: {$cUrl}");
            exit;
        }
    }

    /**
     * @return $this
     */
    private function _validateSession()
    {
        $this->_bLogged = false;
        if (isset($_SESSION['AdminAccount']->cLogin, $_SESSION['AdminAccount']->cPass, $_SESSION['AdminAccount']->cURL)
            && $_SESSION['AdminAccount']->cURL === Shop::getURL()
        ) {
            $oAccount = Shop::Container()->getDB()->select(
                'tadminlogin',
                'cLogin', $_SESSION['AdminAccount']->cLogin,
                'cPass', $_SESSION['AdminAccount']->cPass
            );
            $this->twoFaAuthenticated = (isset($oAccount->b2FAauth) && $oAccount->b2FAauth === '1')
                ? (isset($_SESSION['AdminAccount']->TwoFA_valid) && true === $_SESSION['AdminAccount']->TwoFA_valid)
                : true;
            $this->_bLogged = isset($oAccount->cLogin);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function doTwoFA() : bool
    {
        if (isset($_SESSION['AdminAccount']->cLogin, $_POST['TwoFA_code'])) {
            $oTwoFA = new TwoFA();
            $oTwoFA->setUserByName($_SESSION['AdminAccount']->cLogin);
            // check the 2fa-code here really
            $this->twoFaAuthenticated = $_SESSION['AdminAccount']->TwoFA_valid = $oTwoFA->isCodeValid($_POST['TwoFA_code']);

            return $this->twoFaAuthenticated;
        }

        return false;
    }

    /**
     * @return array|int
     */
    public function favorites()
    {
        return $this->logged()
            ? AdminFavorite::fetchAll($_SESSION['AdminAccount']->kAdminlogin)
            : [];
    }

    /**
     * @param stdClass $oAdmin
     * @return $this
     */
    private function _toSession($oAdmin)
    {
        $oGroup = $this->_getPermissionsByGroup($oAdmin->kAdminlogingruppe);
        if (is_object($oGroup) || (int)$oAdmin->kAdminlogingruppe === ADMINGROUP) {
            $_SESSION['AdminAccount']              = new stdClass();
            $_SESSION['AdminAccount']->cURL        = Shop::getURL();
            $_SESSION['AdminAccount']->kAdminlogin = $oAdmin->kAdminlogin;
            $_SESSION['AdminAccount']->cLogin      = $oAdmin->cLogin;
            $_SESSION['AdminAccount']->cMail       = $oAdmin->cMail;
            $_SESSION['AdminAccount']->cPass       = $oAdmin->cPass;

            $_SESSION['KCFINDER']             = [];
            $_SESSION['KCFINDER']['disabled'] = false;

            if (!is_object($oGroup)) {
                $oGroup                    = new stdClass();
                $oGroup->kAdminlogingruppe = ADMINGROUP;
            }

            $_SESSION['AdminAccount']->oGroup = $oGroup;

            $this->_setLastLogin($oAdmin->cLogin)
                 ->_setRetryCount($oAdmin->cLogin, true)
                 ->_validateSession();
        }

        return $this;
    }

    /**
     * @param string $cLogin
     * @return $this
     */
    private function _setLastLogin($cLogin)
    {
        Shop::Container()->getDB()->update('tadminlogin', 'cLogin', $cLogin, (object)['dLetzterLogin' => 'now()']);

        return $this;
    }

    /**
     * @param string $cLogin
     * @param bool   $bReset
     * @return $this
     */
    private function _setRetryCount($cLogin, $bReset = false)
    {
        if ($bReset) {
            Shop::Container()->getDB()->update('tadminlogin', 'cLogin', $cLogin, (object)['nLoginVersuch' => 0]);
        } else {
            Shop::Container()->getDB()->queryPrepared(
                "UPDATE tadminlogin
                    SET nLoginVersuch = nLoginVersuch+1
                    WHERE cLogin = :login",
                ['login' => $cLogin],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        return $this;
    }

    /**
     * @param int $kAdminlogingruppe
     * @return bool|object
     */
    private function _getPermissionsByGroup($kAdminlogingruppe)
    {
        $kAdminlogingruppe = (int)$kAdminlogingruppe;
        $oGroup            = Shop::Container()->getDB()->select('tadminlogingruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
        if ($oGroup !== null && isset($oGroup->kAdminlogingruppe)) {
            $oPermission_arr = Shop::Container()->getDB()->selectAll(
                'tadminrechtegruppe',
                'kAdminlogingruppe',
                $kAdminlogingruppe,
                'cRecht'
            );
            if (is_array($oPermission_arr)) {
                $oGroup->oPermission_arr = [];
                foreach ($oPermission_arr as $oPermission) {
                    $oGroup->oPermission_arr[] = $oPermission->cRecht;
                }

                return $oGroup;
            }
        }

        return false;
    }

    /**
     * @param string $password
     * @return false|string
     * @deprecated since 5.0
     * @throws Exception
     */
    public static function generatePasswordHash($password)
    {
        return Shop::Container()->getPasswordService()->hash($password);
    }

    /**
     * update password hash if necessary
     *
     * @param string $password
     * @return bool - true when hash was updated
     * @throws Exception
     */
    private function checkAndUpdateHash($password) : bool
    {
        $passwordService = Shop::Container()->getPasswordService();
        // only update hash if the db update to 4.00+ was already executed
        if (isset($_SESSION['AdminAccount']->cPass, $_SESSION['AdminAccount']->cLogin)
            && $passwordService->needsRehash($_SESSION['AdminAccount']->cPass)
        ) {
            $_upd        = new stdClass();
            $_upd->cPass = $passwordService->hash($password);
            Shop::Container()->getDB()->update('tadminlogin', 'cLogin', $_SESSION['AdminAccount']->cLogin, $_upd);

            return true;
        }

        return false;
    }
}
