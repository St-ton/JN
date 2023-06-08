<?php declare(strict_types=1);

namespace JTL\Services\JTL;

/**
 * Class CaptchaService
 * @package JTL\Services\JTL
 */
class CaptchaService implements CaptchaServiceInterface
{
    /**
     * CaptchaService constructor.
     * @param CaptchaServiceInterface $fallbackCaptcha
     */
    public function __construct(private readonly CaptchaServiceInterface $fallbackCaptcha)
    {
    }

    /**
     * @inheritdoc
     */
    public function isConfigured(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $result = false;
        \executeHook(\HOOK_CAPTCHA_CONFIGURED, [
            'isConfigured' => &$result,
        ]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return $this->fallbackCaptcha->isEnabled();
    }

    /**
     * @inheritdoc
     */
    public function getHeadMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->isConfigured()) {
            $result = '';
            \executeHook(\HOOK_CAPTCHA_MARKUP, [
                'getBody' => false,
                'markup'  => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->getHeadMarkup($smarty);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getBodyMarkup($smarty): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->isConfigured()) {
            $result = '';
            \executeHook(\HOOK_CAPTCHA_MARKUP, [
                'getBody' => true,
                'markup'  => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->getBodyMarkup($smarty);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $requestData): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if ($this->isConfigured()) {
            $result = false;
            \executeHook(\HOOK_CAPTCHA_VALIDATE, [
                'requestData' => $requestData,
                'isValid'     => &$result,
            ]);
        } else {
            $result = $this->fallbackCaptcha->validate($requestData);
        }

        return $result;
    }
}
