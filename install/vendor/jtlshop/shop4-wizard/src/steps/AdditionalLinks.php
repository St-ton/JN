<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use jtl\Wizard\Shop4Wizard;

/**
 * Class AdditionalLinks
 */
class AdditionalLinks extends Step implements IStep
{
    /**
     * AdditionalLinks constructor.
     * @param Shop4Wizard
     *
     * Step 3
     */
    public function __construct($wizard)
    {

    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Weiterf&uumlhrende Links';
    }

    /**
     * @param bool $jumpToNext
     * @return mixed|void
     */
    public function finishStep($jumpToNext = true)
    {

    }
}
