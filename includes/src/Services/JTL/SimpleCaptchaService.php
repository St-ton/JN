<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

namespace Services\JTL;

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
        $code          = '';
        $chars         = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        for ($i = 0; $i < 4; $i++) {
            try {
                $code .= $chars{$cryptoService->randomInt(0, strlen($chars) - 1)};
            } catch (\Exception $e) {
                $code .= $chars{rand(0, strlen($chars) - 1)};
            }
        }

        try {
            $rndInt = $cryptoService->randomInt(0, 9);
        } catch (\Exception $e) {
            $rndInt = rand(0, 9);
        }

        $captchaURL = Shop::getURL() . '/' .
            PFAD_INCLUDES . 'captcha/captcha.php?c=' .
            self::encodeCode($code) . '&amp;s=3&amp;l=' . $rndInt;

        return $smarty->assign('captchaCodeURL', $captchaURL)
                      ->assign('captchaCodemd5', md5(PFAD_ROOT . $code))
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

        return isset($requestData['captcha'])
            && isset($requestData['md5'])
            && ($requestData['md5'] === md5(PFAD_ROOT . strtoupper($requestData['captcha'])));
    }


    /**
     * @param string $klartext
     * @return string
     */
    public static function encodeCode(string $klartext): string
    {
        $cryptoService = Shop::Container()->getCryptoService();
        if (strlen($klartext) !== 4) {
            return '0';
        }
        $key  = BLOWFISH_KEY;
        $mod1 = (ord($key[0]) + ord($key[1]) + ord($key[2])) % 9 + 1;
        $mod2 = strlen($_SERVER['DOCUMENT_ROOT']) % 9 + 1;

        $s1 = ord($klartext{0}) - $mod2 + $mod1 + 123;
        $s2 = ord($klartext{1}) - $mod1 + $mod2 + 234;
        $s3 = ord($klartext{2}) + $mod1 + 345;
        $s4 = ord($klartext{3}) + $mod2 + 456;

        $r1 = $cryptoService->randomInt(100, 999);
        $r2 = $cryptoService->randomInt(0, 9);
        $r3 = $cryptoService->randomInt(10, 99);
        $r4 = $cryptoService->randomInt(1000, 9999);

        return $r1 . $s3 . $r2 . $s4 . $r3 . $s1 . $s2 . $r4;
    }
}
