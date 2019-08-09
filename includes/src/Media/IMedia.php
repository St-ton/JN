<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Media;

/**
 * Interface IMedia
 * @package JTL\Media
 */
interface IMedia
{
    /**
     * @param string $request
     * @return bool
     */
    public function isValid(string $request): bool;

    /**
     * @param string $request
     * @return mixed
     */
    public function handle(string $request);
}
