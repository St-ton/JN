<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Debug\DataCollector;

use DebugBar\DataCollector\TimeDataCollector;

/**
 * Class DummyTimeDataCollector
 * @package Debug\DataCollector
 */
class DummyTimeDataCollector extends TimeDataCollector
{
    /**
     * @inheritdoc
     */
    public function startMeasure($name, $label = null, $collector = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function stopMeasure($name, $params = [])
    {
    }
}
