<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl_google_recaptcha
 * @since         5.0
 */

/**
 * Class GoogleReCaptchaHelper
 */
class GoogleReCaptchaHelper
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var GoogleReCaptchaHelper
     */
    private static $instance;

    /**
     * @param Plugin $oPlugin
     * @return GoogleReCaptchaHelper
     */
    public static function getInstance(Plugin $oPlugin)
    {
        if (self::$instance === null) {
            self::$instance = new self($oPlugin);
        }

        return static::$instance;
    }

    /**
     * GoogleReCaptchaHelper constructor.
     * @param Plugin $oPlugin
     */
    private function __construct(Plugin $oPlugin)
    {
        $this->plugin = $oPlugin;
    }

    /**
     * @return string
     */
    private function getHead()
    {
        return '<script type="text/javascript">jtl.load("' . $this->plugin->cFrontendPfadURL . 'js/jtl_google_recaptcha.js");</script>';
    }

    /**
     * @return string
     */
    private function getBody()
    {
        try {
            return Shop::Smarty()->assign('jtl_google_recaptcha_sitekey', $this->plugin->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_sitekey'])
                ->fetch($this->plugin->cFrontendPfad . '/templates/google_recaptcha.tpl');
        } catch (Exception $e) {
            Jtllog::writeLog($e->getMessage(), JTLLOG_LEVEL_ERROR);

            return '';
        }
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return !empty($this->plugin->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_sitekey'])
            && !empty($this->plugin->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_secretkey']);
    }

    /**
     * @param bool $getBody
     * @return string
     */
    public function getMarkup($getBody)
    {
        if ($getBody) {
            return $this->getBody();
        }

        return $this->getHead();
    }

    /**
     * @param  array $requestData
     * @return bool
     */
    public function validate($requestData)
    {
        $secret = $this->plugin->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_secretkey'];
        $url    = 'https://www.google.com/recaptcha/api/siteverify';
        if (empty($secret)) {
            return true;
        }

        $json = http_get_contents($url, 30, [
            'secret'   => $secret,
            'response' => $requestData['g-recaptcha-response'],
            'remoteip' => getRealIp()
        ]);

        if (is_string($json)) {
            $result = json_decode($json);
            if (json_last_error() === JSON_ERROR_NONE) {
                return isset($result->success) && $result->success;
            }
        }

        return false;
    }
}
