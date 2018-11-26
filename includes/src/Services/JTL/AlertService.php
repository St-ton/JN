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
        $alerts = \Session::get('alerts');
        if ($alerts !== null && is_a($alerts, 'Services\JTL\AlertService')) {
            $this->alertList = $alerts->getAlertList();
        }
    }

    /**
     * @inheritdoc
     */
    public function addAlert(string $type, string $message, string $key): Alert
    {
        $alert                 = new Alert($type, $message, $key);
        $this->alertList[$key] = $alert;
        \Session::set('alerts', $this);

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
        $alerts = \Session::get('alerts');
        if (isset($alerts, $alerts->alertList[$key])) {
            unset($alerts->alertList[$key]);
        }
    }
}
