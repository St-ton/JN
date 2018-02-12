<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
     * @var Plugin
     */
    private $plugin;

    /**
     * AbstractPlugin constructor.
     * @param Plugin $plugin
     */
    final public function __construct($plugin)
    {
        $this->plugin   = $plugin;
        $this->pluginId = $plugin->cPluginID;
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    public function boot(EventDispatcher $dispatcher)
    {
        $dispatcher->listen('backend.notification', function (\Notification $notify) use (&$dispatcher) {
            $dispatcher->forget('backend.notification');
            foreach ($this->notifications as $n) {
                $notify->addNotify($n);
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

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }
}
