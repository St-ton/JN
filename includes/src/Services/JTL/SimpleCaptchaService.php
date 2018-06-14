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

        if (!isset($token) || !isset($code)) {
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
}
