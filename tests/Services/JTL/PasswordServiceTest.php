<?php declare(strict_types=1);

namespace Tests\Services\JTL;

use JTL\Services\JTL\CryptoService;
use JTL\Services\JTL\PasswordService;
use JTL\Shop;
use Tests\BaseTestCase;

/**
 * Class PasswordServiceTest
 * @package Tests\Services\JTL
 */
class PasswordServiceTest extends BaseTestCase
{
    public function test_generate()
    {
        $passwordService = new PasswordService(new CryptoService());
        $password        = $passwordService->generate(10);
        $this->assertEquals(10, \strlen($password));
    }

    public function test_hash()
    {
        /*
         * This is not really testable.
         * The only thing I can test is, whether the service returns the plain text password itself as an hash
         */
        $passwordService = new PasswordService(new CryptoService());
        $password        = '123456';
        $hashed          = $passwordService->hash($password);
        $this->assertNotEquals($password, $hashed);
        $this->assertNotNull($hashed);
    }

    public function test_verify()
    {
        $passwordService = new PasswordService(new CryptoService());
        $password        = $passwordService->generate(100);

        // md5 (very old mechanism)
        $hash = \md5($password);
        $this->assertTrue($passwordService->verify($password, $hash));

        // sha based (old mechanism)
        $hash = Shop::Container()->getPasswordService()->cryptOldPasswort($password);
        $this->assertTrue($passwordService->verify($password, $hash));

        // latest mechanism
        $hashed = $passwordService->hash($password);
        $this->assertTrue($passwordService->verify($password, $hashed));
    }

    public function test_needsRehash()
    {
        $passwordService = new PasswordService(new CryptoService());
        $password        = $passwordService->generate(100);

        // md5 (very old mechanism)
        $hash = \md5($password);
        $this->assertTrue($passwordService->needsRehash($hash));

        // sha based (old mechanism)
        $hash = Shop::Container()->getPasswordService()->cryptOldPasswort($password);
        $this->assertTrue($passwordService->needsRehash($hash));

        // latest mechanism
        $hashed      = \password_hash($password, \PASSWORD_ARGON2I);
        $needsRehash = $passwordService->needsRehash($hashed);
        $this->assertTrue($needsRehash);
    }
}
