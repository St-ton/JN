<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

namespace JTL\Services\JTL;

use Exception;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class SimpleCaptchaService
 * @package JTL\Services\JTL
 */
class SimpleCaptchaService implements CaptchaServiceInterface
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * CaptchaService constructor.
     * @param bool $enabled
     */
    public function __construct(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param JTLSmarty $smarty
     * @return string
     */
    public function getHeadMarkup($smarty): string
    {
        return '';
    }

    /**
     * @param JTLSmarty $smarty
     * @return string
     * @throws \SmartyException
     */
    public function getBodyMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $cryptoService = Shop::Container()->getCryptoService();
        try {
            $token = $cryptoService->randomString(8);
            $code  = $cryptoService->randomString(12);
            $code  = $code . ':' . \time();
        } catch (Exception $e) {
            $token = 'token';
            $code  = \rand() . ':' . \time();
        }

        Frontend::set('simplecaptcha.token', $token);
        Frontend::set('simplecaptcha.code', $code);

        return $smarty->assign('captchaToken', $token)
                      ->assign('captchaCode', \sha1($code))
                      ->fetch('snippets/simple_captcha.tpl');
    }

    /**
     * @param  array $requestData
     * @return bool
     */
    public function validate(array $requestData): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        $token = Frontend::get('simplecaptcha.token');
        $code  = Frontend::get('simplecaptcha.code');

        if (!isset($token, $code)) {
            return false;
        }

        Frontend::set('simplecaptcha.token', null);
        Frontend::set('simplecaptcha.code', null);

        $time = \mb_substr($code, \mb_strpos($code, ':') + 1);

        // if form is filled out during lower than 5 seconds it must be a bot...
        return \time() > $time + 5
            && isset($requestData[$token])
            && ($requestData[$token] === \sha1($code));
    }


    /**
     * @param string $plain
     * @return string
     */
    public static function encodeCode(string $plain): string
    {
        if (\mb_strlen($plain) !== 4) {
            return '0';
        }
        $cryptoService = Shop::Container()->getCryptoService();
        $key           = \BLOWFISH_KEY;
        $mod1          = (\mb_ord($key[0]) + \mb_ord($key[1]) + \mb_ord($key[2])) % 9 + 1;
        $mod2          = \mb_strlen($_SERVER['DOCUMENT_ROOT']) % 9 + 1;

        $s1 = \mb_ord($plain{0}) - $mod2 + $mod1 + 123;
        $s2 = \mb_ord($plain{1}) - $mod1 + $mod2 + 234;
        $s3 = \mb_ord($plain{2}) + $mod1 + 345;
        $s4 = \mb_ord($plain{3}) + $mod2 + 456;

        $r1 = $cryptoService->randomInt(100, 999);
        $r2 = $cryptoService->randomInt(0, 9);
        $r3 = $cryptoService->randomInt(10, 99);
        $r4 = $cryptoService->randomInt(1000, 9999);

        return $r1 . $s3 . $r2 . $s4 . $r3 . $s1 . $s2 . $r4;
    }
}
