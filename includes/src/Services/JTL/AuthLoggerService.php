<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 * Class AuthLoggerService
 * @package Services\JTL
 */
class AuthLoggerService implements AuthLoggerServiceInterface
{
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
     * @var Logger
     */
    protected $logger;

    /**
     * @return string
     */
    protected function getIP(): string
    {
        return $this->ip ?? 'unknown ip';
    }

    /**
     * @return int
     */
    protected function getCode(): int
    {
        return $this->code ?? \AdminAccount::ERROR_UNKNOWN;
    }

    /**
     * @return string
     */
    protected function getUser(): string
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
    protected function mapLoginCode($code): string
    {
        switch ($code) {
            case \AdminAccount::LOGIN_OK:
                return 'user {user}@{ip} successfully logged in';
            case \AdminAccount::ERROR_NOT_AUTHORIZED:
                return 'user {user}@{ip} is not authorized';
            case \AdminAccount::ERROR_INVALID_PASSWORD_LOCKED:
            case \AdminAccount::ERROR_INVALID_PASSWORD:
                return 'invalid password for user {user}@{ip}';
            case \AdminAccount::ERROR_USER_NOT_FOUND:
                return 'user {user}@{ip} not found';
            case \AdminAccount::ERROR_USER_DISABLED:
                return 'user {user}@{ip} disabled';
            case \AdminAccount::ERROR_LOGIN_EXPIRED:
                return 'login for user {user}@{ip} expired';
            case \AdminAccount::ERROR_TWO_FACTOR_AUTH_EXPIRED:
                return 'two factor authentication token for user {user}@{ip} expired';
            case \AdminAccount::ERROR_UNKNOWN:
            default:
                return 'unknown error for user {user}@{ip}';
        }
    }

    /**
     * @inheritdoc
     */
    public function log(): bool
    {
        $code   = $this->getCode();
        $method = $code < 1
            ? 'error'
            : 'info';
        $this->logger->$method($this->mapLoginCode($code),
            [
                'user' => $this->getUser(),
                'ip'   => $this->getIP(),
                'code' => $code,
                'type' => $method
            ]);

        return true;
    }
}
