<?php

namespace JTL\IO;

use Exception;
use JsonSerializable;

/**
 * Class IOResponse
 * @package JTL\IO
 */
class IOResponse implements JsonSerializable
{
    /**
     * @var array
     */
    private array $domAssigns = [];

    /**
     * @var array
     */
    private array $varAssigns = [];

    /**
     * @var array
     * @deprecated since 5.0.0
     */
    private array $scripts = [];

    /**
     * @var array[]
     */
    private array $debugLogLines = [];

    /**
     * @var null|string
     */
    private ?string $windowLocationHref = null;

    /**
     * @var array
     */
    private array $evoProductFunctionCalls = [];

    /**
     * @param string $target
     * @param string $attr
     * @param mixed  $data
     * @return $this
     */
    public function assignDom($target, $attr, $data): self
    {
        $this->domAssigns[] = (object)[
            'target' => $target,
            'attr'   => $attr,
            'data'   => $data
        ];

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return $this
     */
    public function assignVar(string $name, $value): self
    {
        $this->varAssigns[] = (object)[
            'name'  => $name,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setClientRedirect(string $url): self
    {
        $this->windowLocationHref = $url;

        return $this;
    }

    /**
     * @param array|null $msg
     * @param bool       $groupHead
     * @param bool       $groupEnd
     * @return $this
     */
    public function debugLog($msg, bool $groupHead = false, bool $groupEnd = false): self
    {
        $this->debugLogLines[] = [$msg, $groupHead, $groupEnd];

        return $this;
    }

    /**
     * @param string $js
     * @return $this
     * @deprecated since 5.0.0
     */
    public function script($js): self
    {
        $this->scripts[] = $js;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $args
     * @return $this
     */
    public function callEvoProductFunction($name, ...$args): self
    {
        $this->evoProductFunctionCalls[] = [$name, $args];

        if (\defined('IO_LOG_CONSOLE') && \IO_LOG_CONSOLE === true) {
            $reset  = 'background: transparent; color: #000;';
            $orange = 'background: #e86c00; color: #fff;';
            $grey   = 'background: #e8e8e8; color: #333;';

            $this->debugLog(['%c CALL %c ' . $name, $orange, $reset]);
            $this->debugLog(['%c PARAMS %c', $grey, $reset, $args]);
            $this->debugLog(['%c TOGGLE DEBUG TRACE %c', $grey, $reset], true);

            foreach ($this->generateCallTrace() as $trace) {
                $this->debugLog(['%c TOGGLE DEBUG TRACE %c', $grey, $reset, $trace]);
            }

            $this->debugLog(null, false, true);
            $this->debugLog(null, false, true);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function generateCallTrace(): array
    {
        $str   = (new Exception())->getTraceAsString();
        $trace = \explode("\n", $str);
        $trace = \array_reverse($trace);
        \array_shift($trace);
        \array_pop($trace);
        $result = [];

        foreach ($trace as $i => $t) {
            $result[] = '#' . ($i + 1) . \mb_substr($t, \mb_strpos($t, ' '));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'js'                 => $this->scripts,
            'domAssigns'         => $this->domAssigns,
            'varAssigns'         => $this->varAssigns,
            'windowLocationHref' => $this->windowLocationHref,
            'debugLogLines'      => $this->debugLogLines,
            'evoProductCalls'    => $this->evoProductFunctionCalls,
        ];
    }
}
