<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Console\Command;

use JTL\Console\Application;
use JTL\Console\ConsoleIO;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Command
 * @package JTL\Console\Command
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
     * @return Application|\Symfony\Component\Console\Application
     */
    public function getApp()
    {
        return $this->getApplication();
    }

    /**
     * @return ConsoleIO
     */
    public function getIO()
    {
        return $this->getApp()->getIO();
    }

    /**
     * @param $name
     * @return InputArgument
     */
    public function getArgumentDefinition($name)
    {
        return $this->getDefinition()->getArgument($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasMissingOption($name)
    {
        $option = $this->getDefinition()->getOption($name);
        $value  = \trim($this->getIO()->getInput()->getOption($name));

        return $option->isValueRequired() && $option->acceptValue() && empty($value);
    }

    /**
     * @param $name
     * @return InputOption
     */
    public function getOptionDefinition($name)
    {
        return $this->getDefinition()->getOption($name);
    }

    /**
     * @param $name
     *
     * @return string|array
     */
    public function getOption($name)
    {
        $value = $this->getIO()->getInput()->getOption($name);

        if (\is_string($value)) {
            $value = \trim($value);
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
     * @return array
     */
    public function getOptions()
    {
        return $this->getIO()->getInput()->getOptions();
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->getIO()->getInput()->hasOption($name);
    }
}
