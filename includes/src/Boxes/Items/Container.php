<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


use Boxes\Renderer\ContainerRenderer;

/**
 * Class Container
 * @package Boxes
 */
class Container extends AbstractBox
{
    /**
     * Container constructor.
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
