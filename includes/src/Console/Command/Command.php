<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Class Command.
 */
class Command extends BaseCommand
{
    /**
     * Command constructor.
     *
     * @param null $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return \JTL\Console\Application|\Symfony\Component\Console\Application
     */
    public function getApp()
    {
        return $this->getApplication();
    }

    /**
     * @return \JTL\Console\ConsoleIO
     */
    public function getIO()
    {
        return $this->getApp()->getIO();
    }

    public function getArgumentDefinition($name)
    {
        $argument = $this->getDefinition()->getArgument($name);

        return $argument;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasMissingOption($name)
    {
        $option = $this->getDefinition()->getOption($name);
        $value  = trim($this->getIO()->getInput()->getOption($name));

        if ($option->isValueRequired() && $option->acceptValue()) {
            if (empty($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    public function getOptionDefinition($name)
    {
        $def = $this->getDefinition()->getOption($name);

        return $def;
    }

    /**
     * @param $name
     *
     * @return string|array
     */
    public function getOption($name)
    {
        $value = $this->getIO()->getInput()->getOption($name);

        if (is_string($value)) {
            $value = trim($value);
        }

        /*
        $option = $this->getOptionDefinition($name);
        if ($option->isValueRequired() && $option->acceptValue()) {
            if (empty($value)) {
                throw new \RuntimeException("Missing option '--{$name}' value");
            }
        }
        */

        return $value;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getIO()->getInput()->getOptions();
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function hasOption($name)
    {
        return $this->getIO()->getInput()->hasOption($name);
    }
}
