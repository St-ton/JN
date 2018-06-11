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

        $captchaURL = Shop::getURL() . '/' . PFAD_INCLUDES . 'captcha/captcha.php?c=' . encodeCode($code) . '&amp;s=3&amp;l=' . $rndInt;

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
}
