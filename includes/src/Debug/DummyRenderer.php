<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Debug;

use DebugBar\JavascriptRenderer;

/**
 * Class DummyRenderer
 * @package JTL\Debug
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
