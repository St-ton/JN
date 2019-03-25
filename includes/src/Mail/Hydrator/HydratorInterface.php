<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Hydrator;

/**
 * Interface HydratorInterface
 * @package JTL\Mail\Hydrator
 */
interface HydratorInterface
{
    /**
     * @param object|null $data
     * @param object      $lang
     */
    public function hydrate(?object $data, object $lang): void;

    /**
     * @param string $variable
     * @param mixed   $content
     */
    public function add(string $variable, $content): void;
}
