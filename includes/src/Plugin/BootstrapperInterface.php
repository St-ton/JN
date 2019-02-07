<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use Events\Dispatcher;

/**
 * Interface BootstrapperInterface
 * @package Plugin
 */
interface BootstrapperInterface
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher);

    /**
     * @return mixed
     */
    public function installed();

    /**
     * @return mixed
     */
    public function uninstalled();

    /**
     * @return mixed
     */
    public function enabled();

    /**
     * @return mixed
     */
    public function disabled();

    /**
     * @param mixed $oldVersion
     * @param mixed $newVersion
     * @return mixed
     */
    public function updated($oldVersion, $newVersion);

    /**
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     */
    public function addNotify($type, $title, $description = null);
}
