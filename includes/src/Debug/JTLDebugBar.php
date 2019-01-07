<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Debug;

use Debug\DataCollector\DummyTimeDataCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer;

/**
 * Class JTLDebugBar
 * @package Debug
 */
class JTLDebugBar extends DebugBar
{
    /**
     * @var TimeDataCollector
     */
    private $timer;

    /**
     * JTLDebugBar constructor.
     * @param \PDO  $pdo
     * @param array $config
     * @throws \DebugBar\DebugBarException
     */
    public function __construct(\PDO $pdo, array $config)
    {
        if (\SHOW_DEBUG_BAR === true) {
            $this->initDefault($pdo, $config);
        } else {
            $this->initDummy();
        }
    }

    /**
     * @return TimeDataCollector
     */
    public function getTimer(): TimeDataCollector
    {
        return $this->timer;
    }

    /**
     * @param \PDO  $pdo
     * @param array $config
     * @throws \DebugBar\DebugBarException
     */
    private function initDefault(\PDO $pdo, array $config): void
    {
        $this->timer      = new TimeDataCollector();
        $this->jsRenderer = new JavascriptRenderer($this);
        $this->timer->startMeasure('init', 'Shop start to end');
        $this->jsRenderer->setBaseUrl(\URL_SHOP . '/' . \rtrim(\PFAD_INCLUDES, '/') . $this->jsRenderer->getBaseUrl());
        $this->addCollector($this->timer);
        $this->addCollector(new PDOCollector(new TraceablePDO($pdo)));
        $this->addCollector(new ConfigCollector($config));
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MessagesCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new MemoryCollector());
        $this->addCollector(new ExceptionsCollector());
    }

    /**
     *
     */
    private function initDummy(): void
    {
        $this->timer      = new DummyTimeDataCollector();
        $this->jsRenderer = new DummyRenderer($this);
        $this->jsRenderer->setBaseUrl(\URL_SHOP . '/' . \rtrim(\PFAD_INCLUDES, '/') . $this->jsRenderer->getBaseUrl());
    }
}
