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

    /**
     * AbstractPlugin constructor.
     * @param string $pluginId
     */
    final public function __construct($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    public function boot(EventDispatcher $dispatcher)
    {
        $dispatcher->listen('backend.notification', function (\Notification $notify) use (&$dispatcher) {
            $dispatcher->forget('backend.notification');
            if (count($this->notifications) > 0) {
                foreach ($this->notifications as $n) {
                    $notify->addNotify($n);
                }
            }
        });
    }

    /**
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     */
    final public function addNotify($type, $title, $description = null)
    {
        $this->notifications[] = (new NotificationEntry($type, $title, $description))->setPluginId($this->pluginId);
    }

    /**
     *
     */
    public function installed()
    {
    }

    /**
     *
     */
    public function uninstalled()
    {
    }

    /**
     *
     */
    public function enabled()
    {
    }

    /**
     *
     */
    public function disabled()
    {
    }
}
