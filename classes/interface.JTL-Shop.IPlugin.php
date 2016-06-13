<?php

/**
 * Interface IPlugin
 */
interface IPlugin
{
    public function boot(EventDispatcher $events);
}