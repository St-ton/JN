<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Events;

/**
 * Class Dispatcher
 * @package Events
 */
final class Dispatcher
{
    use \SingletonTrait;

    /**
     * The registered event listeners.
     *
     * @var array
     */
    private $listeners = [];

    /**
     * The wildcard listeners.
     *
     * @var array
     */
    private $wildcards = [];

    /**
     * Determine if a given event has listeners.
     *
     * @param string $eventName
     * @return bool
     */
    public function hasListeners($eventName): bool
    {
        return isset($this->listeners[$eventName]) || isset($this->wildcards[$eventName]);
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param string|array $eventNames
     * @param callable $listener
     */
    public function listen($eventNames, $listener): void
    {
        foreach ((array)$eventNames as $event) {
            if (\mb_strpos($event, '*') !== false) {
                $this->wildcards[$event][] = $listener;
            } else {
                $this->listeners[$event][] = $listener;
            }
        }
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param string|object $eventName
     * @param array|object $arguments
     */
    public function fire($eventName, $arguments = []): void
    {
        foreach ($this->getListeners($eventName) as $listener) {
            $listener($arguments);
        }
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param string $eventName
     * @return void
     */
    public function forget($eventName): void
    {
        if (\mb_strpos($eventName, '*') !== false) {
            if (isset($this->wildcards[$eventName])) {
                unset($this->wildcards[$eventName]);
            }
        } elseif (isset($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        }
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param string $eventName
     * @return array
     */
    public function getListeners($eventName): array
    {
        $listeners = $this->getWildcardListeners($eventName);
        if (isset($this->listeners[$eventName])) {
            $listeners = \array_merge($listeners, $this->listeners[$eventName]);
        }

        return $listeners;
    }

    /**
     * Get the wildcard listeners for the event.
     *
     * @param  string  $eventName
     * @return array
     */
    private function getWildcardListeners($eventName): array
    {
        $wildcards = [];
        foreach ($this->wildcards as $key => $listeners) {
            if (\fnmatch($key, $eventName)) {
                $wildcards = \array_merge($wildcards, $listeners);
            }
        }

        return $wildcards;
    }
}
