<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Debug;

use DebugBar\JavascriptRenderer;

/**
 * Class DummyRenderer
 * @package Debug
 */
class DummyRenderer extends JavascriptRenderer
{
    /**
     * @inheritdoc
     */
    public function renderHead()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function render($initialize = true, $renderStackedData = true)
    {
        return '';
    }
}
