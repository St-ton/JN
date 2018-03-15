<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL\Validation;

/**
 * Interface ValueCarrierInterface
 * @package Services\JTL\Validation
 */
interface ValueCarrierInterface
{
    /**
     * @param mixed|null $default The default value that is returned, if the value is invalid or does not exist
     * @return mixed
     */
    public function getValue($default = null);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value);

    /**
     * Get the untransformed value (e.g. to redisplay the incorrect value to the user)
     *
     * @return mixed
     */
    public function getValueInsecure();
}
