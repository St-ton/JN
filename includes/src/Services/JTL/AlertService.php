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
//    private $alertList = [
//        'notice' => null,
//        'error'  => null,
//        'custom' => []
//    ];

    private $alertError;
    private $alertNotice;
    private $alertCustom = [];

    /**
     * @var AlertService
     */
    private static $instance;


    /**
     * Alertervice constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    private const TYPE_ERROR  = 'Error';
    private const TYPE_NOTICE = 'Notice';
    private const TYPE_CUSTOM = 'Custom';

    /**
     * @inheritdoc
     */
    public static function getInstance()
    {
        return self::$instance ?? new self();
    }

    public function addAlert(string $variant, string $message, string $type, string $key = '', bool $toSession = true): void
    {
        if($type === self::TYPE_CUSTOM) {
            $this->alertCustom[$key] = new \Alert($variant, $message);
        } else {
            $alertName = 'alert'.$type;
            $this->$alertName = new \Alert($variant, $message);
        }
        //TODO:: mit Session Klasse arbeiten?
        $_SESSION['alerts'] = $this;
    }

    public function setErrorAlert(string $variant, string $message): self
    {
        $this->addAlert($variant, $message, self::TYPE_ERROR);
        return $this;
    }

    public function setNoticeAlert(string $variant, string $message): self
    {
        $this->addAlert($variant, $message, self::TYPE_NOTICE);
        return $this;
    }

    public function addCustomAlert(string $variant, string $message, string $key): self
    {
        $this->addAlert($variant, $message, self::TYPE_CUSTOM, $key);
        return $this;
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

    public function unsetAlert($type, $key = ''): void
    {
        if($type === self::TYPE_CUSTOM) {
            unset($_SESSION['alerts']->alertCustom[$key]);
        } else {
            $alertName = 'alert'.$type;
            unset($_SESSION['alerts']->$alertName);
        }
    }
}
