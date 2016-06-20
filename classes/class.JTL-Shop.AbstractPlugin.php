<?php

/**
 * class AbstractPlugin
 */
abstract class AbstractPlugin implements IPlugin
{
    /**
     * @var string
     */
    private $pluginId;
    
    /**
     * @var array
     */
    private $notifications = [];

    final public function __construct($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    public function boot(EventDispatcher $dispatcher)
    {
        $dispatcher->listen('backend.notification', function(\Notification $notify) use(&$dispatcher) {
            $dispatcher->forget('backend.notification');
            if (count($this->notifications) > 0) {
                foreach ($this->notifications as $n) {
                    $notify->addNotify($n);
                }
            }
        });
    }

    final public function addNotify($type, $title, $description = null)
    {
        $notify = new NotificationEntry($type, $title, $description);
        $notify->setPluginId($this->pluginId);
        $this->notifications[] = $notify;
    }

    public function installed() { }
    public function uninstalled() { }

    public function enabled() { }
    public function disabled() { }
}