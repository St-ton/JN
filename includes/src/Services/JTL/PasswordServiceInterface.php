<?php declare(strict_types=1);

namespace JTL\Services\JTL;

/**
 * Interface PasswordServiceInterface
 * @package JTL\Services\JTL
 */
interface PasswordServiceInterface
{

    /**
     * only use for upgrading from shop 4 --> 5!
     *
     * @param string      $password
     * @param null|string $passwordHash
     * @return bool|string
     */
    public function cryptOldPasswort($password, $passwordHash = null);

    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function generate($length): string;

    /**
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public function hash($password): string;

    /**
     * @param string $password
     * @param string $hash
     * @return string|bool
     * @throws \Exception
     */
    public function verify($password, $hash);

    /**
     * @param string $hash
     * @return bool
     * @throws \Exception
     */
    public function needsRehash($hash): bool;

    /**
     * @param string $hash
     * @return array
     */
    public function getInfo($hash): array;

    /**
     * @param string $pass
     * @param string $validCharRegex
     * @return bool
     */
    public function hasOnlyValidCharacters(string $pass, string $validCharRegex = ''): bool;
}
