<?php declare(strict_types=1);

namespace JTL\Backend;

use JTL\DB\ReturnType;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\xtea\XTEA;

/**
 * Class StoreToken
 * @package JTL\Backend
 */
class AuthToken
{
    //private const AUTH_SERVER = 'https://oauth2.api.jtl-software.com/link';
    private const AUTH_SERVER = 'https://auth.jtl-test.de/link';

    /** @var self */
    private static $instance;

    /** @var string */
    private $authCode;

    /** @var string */
    private $token;

    /** @var int */
    private $owner;

    /** @var string */
    private $hash;

    /** @var string */
    private $verified;

    /**
     * StoreToken constructor.
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     * @return self
     */
    public static function instance(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @return void
     */
    private function load(): void
    {
        $token = Shop::Container()->getDB()->queryPrepared(
            'SELECT tstoreauth.owner, tstoreauth.auth_code, tstoreauth.access_token,
                tadminlogin.cPass AS hash, tstoreauth.verified
                FROM tstoreauth
                INNER JOIN tadminlogin ON tadminlogin.kAdminlogin = tstoreauth.owner
                LIMIT 1',
            [],
            ReturnType::SINGLE_OBJECT
        );

        if ($token) {
            $this->authCode = $token->auth_code;
            $this->token    = $token->access_token;
            $this->hash     = sha1($token->hash);
            $this->owner    = (int)$token->owner;
            $this->verified = $token->verified;
        } else {
            $this->authCode = null;
            $this->token    = null;
            $this->hash     = null;
            $this->owner    = 0;
            $this->verified = null;
        }
    }

    /**
     * @return string
     */
    private function salt(): string
    {
        static $uuid = null;

        if ($uuid === null) {
            $srv = Shop::Container()->getDB()->query(
                'SELECT @@SERVER_UUID AS uuid',
                ReturnType::SINGLE_OBJECT
            );

            $uuid = $srv->uuid ?? \BLOWFISH_KEY;
        }

        return $uuid . '.' . $this->hash ?? '';
    }

    /**
     * @return XTEA
     */
    private function getCrypto(): XTEA
    {
        return new XTEA(sha1(\BLOWFISH_KEY. '.' . $this->salt()));
    }

    /**
     * @param string $authCode
     * @param string $token
     */
    private function set(string $authCode, string $token): void
    {
        Shop::Container()->getDB()->queryPrepared(
            'UPDATE tstoreauth SET
                access_token = :token,
                verified     = :verified,
                created_at   = NOW()
                WHERE auth_code = :authCode',
            [
                'token'    => $this->getCrypto()->encrypt($token),
                'verified' => sha1($token),
                'authCode' => $authCode,
            ],
            ReturnType::DEFAULT
        );
        $this->load();
    }

    /**
     * @return bool
     */
    public static function isEditable(): bool
    {
        $user = Shop::Container()->getAdminAccount()->account();

        return $user && $user->oGroup->kAdminlogingruppe === \ADMINGROUP;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $token = rtrim($this->getCrypto()->decrypt($this->token ?? ''));

        return ($token !== '') && (sha1($token) === $this->verified);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->isValid() ? rtrim($this->getCrypto()->decrypt($this->token ?? '')) : '';
    }

    /**
     * @return void
     */
    public function revoke(): void
    {
        if (!self::isEditable()) {
            return;
        }

        Shop::Container()->getDB()->query('TRUNCATE TABLE tstoreauth', ReturnType::DEFAULT);
        $this->load();
    }

    /**
     * @param string $authCode
     */
    public function reset(string $authCode): void
    {
        if (!self::isEditable()) {
            return;
        }

        $db    = Shop::Container()->getDB();
        $owner = Shop::Container()->getAdminAccount()->account()->kAdminlogin ?? 0;

        if ($owner > 0) {
            $db->queryPrepared(
                "INSERT INTO tstoreauth (owner, auth_code, access_token, created_at, verified)
                    VALUES (:owner, :authCode, '', NOW(), '')
                    ON DUPLICATE KEY UPDATE
                        auth_code    = :authCode,
                        access_token = '',
                        verified     = '',
                        created_at = NOW()",
                [
                    'owner'    => $owner,
                    'authCode' => $authCode,
                ],
                ReturnType::DEFAULT
            );
            $db->queryPrepared(
                'DELETE FROM tstoreauth WHERE owner != :owner',
                ['owner' => $owner],
                ReturnType::DEFAULT
            );
            $this->load();
        }
    }

    /**
     * @param string $authCode
     * @param string $returnURL
     */
    public function requestToken(string $authCode, string $returnURL): void
    {
        if (!self::isEditable()) {
            return;
        }
        $this->reset($authCode);
        header('location: ' . self::AUTH_SERVER . '?' . http_build_query([
                'url'  => $returnURL,
                'code' => $authCode
            ], '', '&'));

        exit;
    }

    /**
     * @return void
     */
    public function responseToken(): void
    {
        $authCode = (string)Request::postVar('code');
        $token    = (string)Request::postVar('token');
        $logger   = null;
        try {
            $logger = Shop::Container()->getLogService();
        } catch (CircularReferenceException $e) {
        } catch (ServiceNotFoundException $e) {
            $logger = null;
        }

        if ($authCode === null || $authCode !== $this->authCode) {
            $logger !== null && $logger->addError('Call responseToken with invalid authcode!');
            http_response_code(404);

            exit;
        }

        if ($token === null || $token === '') {
            http_response_code(200);

            exit;
        }

        $this->set($authCode, $token);
        http_response_code($this->isValid() ? 200 : 404);

        exit;
    }
}
