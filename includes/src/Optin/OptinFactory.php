<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Optin;

/**
 * Class OptinFactory
 * @package JTL\Optin
 */
abstract class OptinFactory
{
    /**
     * @param string $optinClass
     * @param array  $inheritData
     * @return OptinInterface|null
     */
    public static function getInstance(string $optinClass, ...$inheritData): ?OptinInterface
    {
        return \class_exists($optinClass) ? new $optinClass($inheritData) : null;
    }
}
