<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

/**
 * Class Alert
 */
class AlertService implements AlertServiceInterface
{
    private $alertError;
    private $alertNotice;
    private $alertCustom = [];

    /**
     * @var AlertService
     */
    private static $instance;

    /**
     * Alertservice constructor.
     */
    public function __construct()
    {
        $this->initFromSession();
//        self::$instance = $this;
    }

    public function initFromSession(): void
    {
        if (isset($_SESSION['alerts']) && is_a($_SESSION['alerts'], 'AlertService')) {
            $this->alertError  = $_SESSION['alerts']->getErrorAlert();
            $this->alertNotice = $_SESSION['alerts']->getNoticeAlert();
            $this->alertCustom = $_SESSION['alerts']->getCustomAlerts();
        }
    }

    /**
     * @inheritdoc
     */
//    public static function getInstance()
//    {
//        return self::$instance ?? new self();
//    }

    public function addAlert(
        string $variant,
        string $message,
        string $type,
        string $key = ''
    ): \Alert {
        $alert = new \Alert($variant, $message, $type, $key);
        if($type === \Alert::TYPE_CUSTOM) {
            $this->alertCustom[$key] = $alert;
        } else {
            $alertName = 'alert'.$type;
            $this->$alertName = $alert;
        }
        //TODO:: mit Session Klasse arbeiten?
        $_SESSION['alerts'] = $this;

        return $alert;
    }

    public function setErrorAlert(string $variant, string $message): \Alert
    {
        $alert = $this->addAlert($variant, $message, \Alert::TYPE_ERROR);

        return $alert;
    }

    public function setNoticeAlert(string $variant, string $message): \Alert
    {
        $alert = $this->addAlert($variant, $message, \Alert::TYPE_NOTICE);

        return $alert;
    }

    public function addCustomAlert(string $variant, string $message, string $key): \Alert
    {
        $alert = $this->addAlert($variant, $message, \Alert::TYPE_CUSTOM, $key);

        return $alert;
    }

    public function getErrorAlert(): ?\Alert
    {
        return $this->alertError;
    }

    public function getNoticeAlert(): ?\Alert
    {
        return $this->alertNotice;
    }

    public function getCustomAlert(string $key): ?\Alert
    {
        return $this->alertCustom[$key] ?? null;
    }

    public function getCustomAlerts(): array
    {
        return $this->alertCustom;
    }

    public function unsetAlert(\Alert $alert): void
    {
        if($alert->getType() === \Alert::TYPE_CUSTOM) {
            unset($_SESSION['alerts']->alertCustom[$alert->getKey()]);
        } else {
            $alertName = 'alert'.$alert->getType() ;
            $_SESSION['alerts']->$alertName = null;
        }
    }
}
