<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;

use Boxes\Renderer\ContainerRenderer;

/**
 * Class Container
 * @package Boxes\Items
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
        $this->addMapping('innerHTML', 'HTML');
        $this->addMapping('oContainer_arr', 'Children');
    }

    /**
     * @return string
     */
    public function getRenderer(): string
    {
        return ContainerRenderer::class;
    }
}
