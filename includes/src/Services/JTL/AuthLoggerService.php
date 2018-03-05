<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Class AuthLoggerService
 * @package Services\JTL
 */
class AuthLoggerService implements AuthLoggerServiceInterface
{
    const LOGFILE = PFAD_LOGFILES . 'auth.log';

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $user;

    /**
     * @return string
     */
    protected function getIP() : string
    {
        return $this->ip ?? 'unknown ip';
    }

    /**
     * @return int
     */
    protected function getCode() : int
    {
        return $this->code ?? \AdminAccount::ERROR_UNKNOWN;
    }

    /**
     * @return string
     */
    protected function getUser() : string
    {
        return $this->user !== null
            ? \StringHandler::filterXSS($this->user)
            : 'Unknown user';
    }

    /**
     * @inheritdoc
     */
    public function setIP(string $ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCode(int $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setUser(string $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param int $code
     * @return string
     */
    protected function mapLoginCode($code) : string
    {
        switch ($code) {
            case \AdminAccount::LOGIN_OK:
                return 'logged in';
            case \AdminAccount::ERROR_NOT_AUTHORIZED:
                return 'not authorized';
            case \AdminAccount::ERROR_INVALID_PASSWORD_LOCKED:
            case \AdminAccount::ERROR_INVALID_PASSWORD:
                return 'invalid password';
            case \AdminAccount::ERROR_USER_NOT_FOUND:
                return 'user not found';
            case \AdminAccount::ERROR_USER_DISABLED:
                return 'user disabled';
            case \AdminAccount::ERROR_LOGIN_EXPIRED:
                return 'login expired';
            case \AdminAccount::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                return 'two factor authentication token expired';
            case \AdminAccount::ERROR_UNKNOWN:
            default:
                return 'unknown error';
        }
    }

    /**
     * @inheritdoc
     */
    public function log() : bool
    {
        $date = new \DateTime();
        $msg = $date->format(DATE_RFC822) . ' JTL-Shop ';
        $code = $this->getCode();
        $msg .= $code < 1 ? 'ERROR: ' : 'SUCCESS: ';
        $msg .= 'User ' . $this->getUser() . ' from ' . $this->getIP() . ' ';
        $msg .= $this->mapLoginCode($code);
        $msg .= "\n";

        return file_put_contents(self::LOGFILE, $msg, FILE_APPEND) > 0;
    }
}
