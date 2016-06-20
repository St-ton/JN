<?php

/**
 * Interface IPlugin
 */
interface IPlugin
{
    public function boot(EventDispatcher $dispatcher);

    public function installed();
    public function uninstalled();
    
    public function enabled();
    public function disabled();

    public function addNotify($type, $title, $description = null);
}