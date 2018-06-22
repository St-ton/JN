<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

namespace Services\JTL;

use Exception;
use Session;
use Shop;

/**
 * Class SimpleCaptchaService
 * @package Services\JTL
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
     * @param \JTLSmarty $smarty
     * @return string
     */
    public function getHeadMarkup($smarty): string
    {
        return '';
    }

    /**
     * @param \JTLSmarty $smarty
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
            $code  = $code . ':' . time();
        } catch (Exception $e) {
            $token = 'token';
            $code  = rand() . ':' . time();
        }

        Session::set('simplecaptcha.token', $token);
        Session::set('simplecaptcha.code', $code);

        return $smarty->assign('captchaToken', $token)
                      ->assign('captchaCode', sha1($code))
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

        $token = Session::get('simplecaptcha.token');
        $code  = Session::get('simplecaptcha.code');

        if (!isset($token, $code)) {
            return false;
        }

        Session::set('simplecaptcha.token', null);
        Session::set('simplecaptcha.code', null);

        $time = substr($code, strpos($code, ':') + 1);

        // if form is filled out during lower than 5 seconds it must be a bot...
        return time() > $time + 5
            && isset($requestData[$token])
            && ($requestData[$token] === sha1($code));
    }


    /**
     * @param string $plain
     * @return string
     */
    public static function encodeCode(string $plain): string
    {
        if (strlen($plain) !== 4) {
            return '0';
        }
        $cryptoService = Shop::Container()->getCryptoService();
        $key           = BLOWFISH_KEY;
        $mod1          = (ord($key[0]) + ord($key[1]) + ord($key[2])) % 9 + 1;
        $mod2          = strlen($_SERVER['DOCUMENT_ROOT']) % 9 + 1;

        $s1 = ord($plain{0}) - $mod2 + $mod1 + 123;
        $s2 = ord($plain{1}) - $mod1 + $mod2 + 234;
        $s3 = ord($plain{2}) + $mod1 + 345;
        $s4 = ord($plain{3}) + $mod2 + 456;

        $r1 = $cryptoService->randomInt(100, 999);
        $r2 = $cryptoService->randomInt(0, 9);
        $r3 = $cryptoService->randomInt(10, 99);
        $r4 = $cryptoService->randomInt(1000, 9999);

        return $r1 . $s3 . $r2 . $s4 . $r3 . $s1 . $s2 . $r4;
    }
}
