<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface IMedia
 */
interface IMedia
{
    /**
     * @param string $request
     * @return mixed
     */
    public function isValid($request);

    /**
     * @param string $request
     * @return mixed
     */
    public function handle($request);
}
