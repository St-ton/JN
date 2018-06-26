<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxGlobalAttributes
 * @package Boxes
 */
final class BoxGlobalAttributes extends AbstractBox
{
    /**
     * BoxDirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('globaleMerkmale', 'Items');
        $this->setShow(true);
        require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';
        $attributes = \Session::CustomerGroup()->mayViewCategories()
            ? gibSitemapGlobaleMerkmale()
            : [];
        $this->setItems($attributes);
    }
}
