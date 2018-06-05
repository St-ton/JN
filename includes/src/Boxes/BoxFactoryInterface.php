<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;


/**
 * Class BoxFactory
 *
 * @package Boxes
 */
interface BoxFactoryInterface
{
    /**
     * BoxFactory constructor.
     *
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * @param int $baseType
     * @return boxInterface
     */
    public function getBoxByBaseType(int $baseType): BoxInterface;
}