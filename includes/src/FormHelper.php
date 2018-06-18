<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FormHelper
 * @since 5.0.0
 */
class FormHelper
{
    /**
     * @param array $requestData
     * @return bool
     */
    public static function validateCaptcha(array $requestData): bool
    {
        $valid = Shop::Container()->getCaptchaService()->validate($requestData);

        if ($valid) {
            Session::set('bAnti_spam_already_checked', true);
        } else {
            Shop::Smarty()->assign('bAnti_spam_failed', true);
        }

        return $valid;
    }

    /**
     * create a hidden input field for xsrf validation
     *
     * @return string
     * @throws Exception
     */
    public static function getTokenInput(): string
    {
        if (!isset($_SESSION['jtl_token'])) {
            $_SESSION['jtl_token'] = Shop::Container()->getCryptoService()->randomString(32);
        }

        return '<input type="hidden" class="jtl_token" name="jtl_token" value="' . $_SESSION['jtl_token'] . '" />';
    }

    /**
     * validate token from POST/GET
     *
     * @return bool
     */
    public static function validateToken(): bool
    {
        if (!isset($_SESSION['jtl_token'])) {
            return false;
        }

        $token = $_POST['jtl_token'] ?? $_GET['token'] ?? null;

        if ($token === null) {
            return false;
        }

        return Shop::Container()->getCryptoService()->stableStringEquals($_SESSION['jtl_token'], $token);
    }

    /**
     * @param array $fehlendeAngaben
     * @return int
     */
    public static function eingabenKorrekt(array $fehlendeAngaben): int
    {
        return (int)\Functional\none($fehlendeAngaben, function ($e) { return $e > 0; });
    }
}
