<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

namespace jtl_google_recaptcha;

use EventDispatcher;
use Exception;
use Jtllog;
use Notification;
use NotificationEntry;
use Shop;

class Bootstrap extends \AbstractPlugin
{
    public function boot(EventDispatcher $dispatcher)
    {
        parent::boot($dispatcher);

        $dispatcher->listen('shop.hook.' . HOOK_CAPTCHA_CONFIGURED, [$this, 'reCaptchaConfigured']);
        $dispatcher->listen('shop.hook.' . HOOK_CAPTCHA_MARKUP, [$this, 'reCaptchaMarkup']);
        $dispatcher->listen('shop.hook.' . HOOK_CAPTCHA_VALIDATE, [$this, 'reCaptchaValidate']);
        $dispatcher->listen('backend.notification', [$this, 'setNotification']);
    }

    /**
     * @return bool
     */
    protected function isConfigured():bool
    {
        return !empty($this->getPlugin()->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_sitekey'])
            && !empty($this->getPlugin()->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_secretkey']);
    }

    /**
     * @return string
     */
    protected function getHead(): string
    {
        return '<script type="text/javascript">jtl.load("' . $this->getPlugin()->cFrontendPfadURL . 'js/jtl_google_recaptcha.js");</script>';
    }

    /**
     * @return string
     */
    protected function getBody():string
    {
        try {
            switch ($this->getPlugin()->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_type']) {
                case 'invisible':
                    $tplFile = 'google_invisible_recaptcha.tpl';
                    break;
                case 'checkbox':
                default:
                    $tplFile = 'google_recaptcha.tpl';
            }

            return Shop::Smarty()
                ->assign('jtl_google_recaptcha_sitekey', $this->getPlugin()->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_sitekey'])
                ->fetch($this->getPlugin()->cFrontendPfad . '/templates/' . $tplFile);
        } catch (Exception $e) {
            Jtllog::writeLog($e->getMessage(), JTLLOG_LEVEL_ERROR);

            return '';
        }
    }

    /**
     * @param  array $requestData
     * @return bool
     */
    protected function validate(array $requestData):bool
    {
        $secret = $this->getPlugin()->oPluginEinstellungAssoc_arr['jtl_google_recaptcha_secretkey'];
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

    /**
     * @param Notification $notification
     * @return void
     */
    public function setNotification(Notification $notification)
    {
        if (!$this->isConfigured()) {
            $notification->add(
                NotificationEntry::TYPE_WARNING,
                $this->getPlugin()->cName,
                'Sie haben Google reCaptcha als Spamschutz-Methode aktiviert, aber keine Konfiguration angegeben.',
                'plugin.php?kPlugin=' . $this->getPlugin()->kPlugin
            );
        }
    }

    /**
     * @param array $args
     * @return void
     */
    public function reCaptchaConfigured(array $args)
    {
        $args['isConfigured'] = $this->isConfigured();
    }

    /**
     * @param array $args
     * @return void
     */
    public function reCaptchaMarkup(array $args)
    {
        $args['markup'] = (isset($args['getBody']) && $args['getBody'])
            ? $this->getBody()
            : $this->getHead();
    }

    /**
     * @param array $args
     * @return void
     */
    public function reCaptchaValidate(array $args)
    {
        $args['isValid'] = $this->validate(isset($args['requestData']) ? $args['requestData'] : []);
    }
}
