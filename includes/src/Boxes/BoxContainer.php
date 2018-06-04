<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use Boxes\Renderer\ContainerRenderer;

/**
 * Class BoxContainer
 * @package Boxes
 */
class BoxContainer extends AbstractBox
{
    /**
     * BoxContainer constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('innerHTML', 'HTML');
        parent::addMapping('oContainer_arr', 'Children');
    }

    /**
     * @return string
     */
    public function getRenderer(): string
    {
        return ContainerRenderer::class;
    }
}
