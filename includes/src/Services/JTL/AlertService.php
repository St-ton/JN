<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Alert;

/**
 * Class AlertService
 */
class AlertService implements AlertServiceInterface
{
    private $alertList = [];

    /**
     * Alertservice constructor.
     */
    public function __construct()
    {
        $this->initFromSession();
    }

    /**
     * @inheritdoc
     */
    public function initFromSession(): void
    {
        if (isset($_SESSION['alerts']) && is_a($_SESSION['alerts'], 'AlertService')) {
            $this->alertList = $_SESSION['alerts']->getAlertList();
        }
    }

    /**
     * @inheritdoc
     */
    public function addAlert(string $type, string $message, string $key = ''): Alert {

        $alert                 = new Alert($type, $message, $key);
        $this->alertList[$key] = $alert;
        $_SESSION['alerts']    = $this;

        return $alert;
    }

    /**
     * @inheritdoc
     */
    public function getAlert(string $key): ?Alert
    {
        return $this->alertList[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getAlertList(): array
    {
        return $this->alertList;
    }

    /**
     * @inheritdoc
     */
    public function unsetAlert(string $key): void
    {
        if (isset($_SESSION['alerts'], $_SESSION['alerts']->alertList[$key])) {
            unset($_SESSION['alerts']->alertList[$key]);
        }
    }
}
