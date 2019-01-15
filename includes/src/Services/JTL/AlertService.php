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
        $alerts = $_SESSION['alerts'] ?? '';

        if (!empty($alerts)) {
            foreach ($alerts as $alertSerialized) {
                $alert                             = unserialize($alertSerialized, ['allowed_classes', 'Alert']);
                $this->alertList[$alert->getKey()] = $alert;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addAlert(string $type, string $message, string $key, array $options = null): Alert
    {
        $alert                 = new Alert($type, $message, $key, $options);
        $this->alertList[$key] = $alert;

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
    public function displayAlertByKey(string $key): void
    {
        if ($alert = $this->getAlert($key)) {
            $alert->display();
        }
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
    public function alertTypeExists(string $type): bool
    {
        foreach ($this->getAlertList() as $alert) {
            if ($alert->getType() === $type) {
                return true;
            }
        }
        return false;
    }
}
