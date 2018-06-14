<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

namespace Services\JTL;

/**
 * Class CaptchaService
 * @package Services\JTL
 */
class CaptchaService implements CaptchaServiceInterface
{
    /**
     * @var CaptchaServiceInterface
     */
    private $fallbackCaptcha;

    /**
     * CaptchaService constructor.
     * @param CaptchaServiceInterface $fallbackCaptcha
     */
    public function __construct(CaptchaServiceInterface $fallbackCaptcha)
    {
        $this->fallbackCaptcha = $fallbackCaptcha;
    }

    /**
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $result = false;
        executeHook(HOOK_CAPTCHA_CONFIGURED, [
            'isConfigured' => &$result,
        ]);

        return $result;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->fallbackCaptcha->isEnabled();
    }

    /**
     * @param \JTLSmarty $smarty
     * @return string
     */
    public function getHeadMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->isConfigured()) {
            $result = '';
            executeHook(HOOK_CAPTCHA_MARKUP, [
                'getBody' => false,
                'markup'  => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->getHeadMarkup($smarty);
        }

        return $result;
    }

    /**
     * @param \JTLSmarty $smarty
     * @return string
     */
    public function getBodyMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->isConfigured()) {
            $result = '';
            executeHook(HOOK_CAPTCHA_MARKUP, [
                'getBody' => true,
                'markup'  => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->getBodyMarkup($smarty);
        }

        return $result;
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

        if ($this->isConfigured()) {
            $result = false;
            executeHook(HOOK_CAPTCHA_VALIDATE, [
                'requestData' => $requestData,
                'isValid'     => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->validate($requestData);
        }

        return $result;
    }
}
