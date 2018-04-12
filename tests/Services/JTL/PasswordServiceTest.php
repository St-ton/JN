<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Class PasswordServiceTest
 * @package Services\JTL
 */
class PasswordServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_generate()
    {
        $passwordService = new PasswordService(new CryptoService());
        $password        = $passwordService->generate(10);
        $this->assertEquals(10, strlen($password));
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
        $hash = md5($password);
        $this->assertTrue($passwordService->verify($password, $hash));

        // sha based (old mechanism)
        require_once __DIR__ . '/../../../includes/tools.Global.php';
        $hash = cryptPasswort($password);
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
        $hash = md5($password);
        $this->assertTrue($passwordService->needsRehash($hash));

        // sha based (old mechanism)
        require_once __DIR__ . '/../../../includes/tools.Global.php';
        $hash = cryptPasswort($password);
        $this->assertTrue($passwordService->needsRehash($hash));

        // latest mechanism
        $hashed      = password_hash($password, PASSWORD_ARGON2I);
        $needsRehash = $passwordService->needsRehash($hashed);
        $this->assertTrue($needsRehash);
    }
}
