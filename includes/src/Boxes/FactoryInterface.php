<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes;

use JTL\Boxes\Items\BoxInterface;

/**
 * Interface FactoryInterface
 * @package JTL\Boxes
 */
interface FactoryInterface
{
    /**
     * FactoryInterface constructor.
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * @param int    $baseType
     * @param string $type
     * @return boxInterface
     */
    public function getBoxByBaseType(int $baseType, string $type = null): BoxInterface;
}
