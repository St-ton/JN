<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SimpleXMLObject
 */
class SimpleXMLObject
{
    /**
     * @return object
     */
    public function attributes()
    {
        $container = get_object_vars($this);

        return (object)$container['@attributes'];
    }

    /**
     * @return object
     */
    public function content()
    {
        $container = get_object_vars($this);

        return (object)$container['@content'];
    }
}
