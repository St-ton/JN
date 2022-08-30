<?php declare(strict_types=1);

namespace JTL\Backend;

use Carbon\Carbon;
use JsonException;
use JTL\DB\DbInterface;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\xtea\XTEA;

/**
 * Class AuthToken
 * @package JTL\Backend
 */
class AuthToken
{
    private const AUTH_SERVER = 'https://oauth2.api.jtl-software.com/link';

    /**
     * @var AuthToken|null
     */
    private static ?AuthToken $instance = null;

    /**
     * @var string|null
     */
    private ?string $authCode;

    /**
     * @var string|null
     */
    private ?string $token;

    /**
     * @var string|null
     */
    private ?string $decryptedToken = null;

    /**
     * @var string|null
     */
    private ?string $hash;

    /**
     * @var string|null
     */
    private ?string $verified;

    /**
     * AuthToken constructor.
     * @param DbInterface $db
     */
    public function __construct(private DbInterface $db)
    {
        $this->load();
        self::$instance = $this;
    }

    /**
     * @param DbInterface $db
     * @return static
     */
    public static function getInstance(DbInterface $db): self
    {
        return self::$instance ?? new self($db);
    }

    /**
     * @return void
     */
    private function load(): void
    {
        $this->authCode       = null;
        $this->token          = null;
        $this->hash           = null;
        $this->verified       = null;
        $this->decryptedToken = null;

        $token = $this->db->getSingleObject(
            'SELECT tstoreauth.auth_code, tstoreauth.access_token,
                tadminlogin.cPass AS hash, tstoreauth.verified
                FROM tstoreauth
                INNER JOIN tadminlogin 
                    ON tadminlogin.kAdminlogin = tstoreauth.owner
                LIMIT 1'
        );
        if ($token) {
            $this->authCode = $token->auth_code ?? null;
            $this->token    = $token->access_token ?? null;
            $this->hash     = \sha1($token->hash ?? '');
            $this->verified = $token->verified ?? null;
        }
    }

    /**
     * @return string
     */
    private function salt(): string
    {
        return \BLOWFISH_KEY . '.' . $this->hash ?? '';
    }

    /**
     * @return XTEA
     */
    private function getCrypto(): XTEA
    {
        return new XTEA(\sha1(\BLOWFISH_KEY . '.' . $this->salt()));
    }

    /**
     * @param string $authCode
     * @param string $token
     */
    public function set(string $authCode, string $token): void
    {
        $this->db->queryPrepared(
            'UPDATE tstoreauth SET
                access_token = :token,
                verified     = :verified,
                created_at   = NOW()
                WHERE auth_code = :authCode',
            [
                'token'    => $this->getCrypto()->encrypt($token),
                'verified' => \sha1($token),
                'authCode' => $authCode,
            ]
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
     * @param string $token
     * @return bool
     */
    public function isExpired(string $token): bool
    {
        if ($token === '') {
            return true;
        }
        $parts = \explode('.', $token);
        if (!isset($parts[1])) {
            return true;
        }
        $payload = \base64_decode($parts[1]);
        try {
            $expiration = Carbon::createFromTimestamp(\json_decode($payload, false, 512, \JSON_THROW_ON_ERROR)->exp);
        } catch (JsonException) {
            return true;
        }

        return Carbon::now()->diffInSeconds($expiration, false) < 0;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $token = $this->decryptToken();

        return $token !== '' && (\sha1($token) === $this->verified) && !$this->isExpired($token);
    }

    /**
     * @return string
     */
    public function get(): string
    {
        if (!$this->isValid()) {
            return '';
        }

        return $this->decryptToken();
    }

    /**
     * @return void
     */
    public function revoke(): void
    {
        if (!self::isEditable()) {
            return;
        }
        $this->db->query('TRUNCATE TABLE tstoreauth');
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

        $owner = Shop::Container()->getAdminAccount()->account()->kAdminlogin ?? 0;

        if ($owner > 0) {
            $this->db->queryPrepared(
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
                ]
            );
            $this->db->queryPrepared(
                'DELETE FROM tstoreauth WHERE owner != :owner',
                ['owner' => $owner]
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
        \header('Location: ' . self::AUTH_SERVER . '?' . \http_build_query([
                'url'  => $returnURL,
                'code' => $authCode
            ]));

        exit;
    }

    /**
     *
     */
    public function responseToken(): void
    {
        $authCode = (string)Request::postVar('code', '');
        $token    = (string)Request::postVar('token', '');
        try {
            $logger = Shop::Container()->getLogService();
        } catch (ServiceNotFoundException | CircularReferenceException) {
            $logger = null;
        }

        if ($authCode === '' || $authCode !== $this->authCode) {
            $logger?->error('Call responseToken with invalid authcode!');
            \http_response_code(404);
            exit;
        }

        if ($token === '') {
            \http_response_code(200);
            exit;
        }

        $this->set($authCode, $token);
        \http_response_code($this->isValid() ? 200 : 404);
        exit;
    }

    /**
     * @return string
     */
    private function decryptToken(): string
    {
        if ($this->decryptedToken === null) {
            $this->decryptedToken = \rtrim($this->getCrypto()->decrypt($this->token ?? ''));
        }

        return $this->decryptedToken;
    }
}
