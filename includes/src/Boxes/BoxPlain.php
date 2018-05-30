<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxCompareList
 * @package Boxes
 */
final class BoxPlain extends AbstractBox
{
    /**
     * BoxPlain constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->supportsRevisions = true;
    }
}
