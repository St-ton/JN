<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxRecentlyViewedProducts
 * @package Boxes
 */
final class BoxRecentlyViewedProducts extends AbstractBox
{
    /**
     * BoxCart constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        if (isset($_SESSION['ZuletztBesuchteArtikel'])
            && is_array($_SESSION['ZuletztBesuchteArtikel'])
            && count($_SESSION['ZuletztBesuchteArtikel']) > 0
            && \Session::CustomerGroup()->mayViewCategories()
        ) {
            $products       = [];
            $defaultOptions = \Artikel::getDefaultOptions();
            foreach ($_SESSION['ZuletztBesuchteArtikel'] as $i => $oArtikel) {
                $product = new \Artikel();
                $product->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                if ($product->kArtikel > 0) {
                    $products[$i] = $product;
                }
            }
            $this->setProducts(array_reverse($products));
            $this->setShow(true);

            executeHook(HOOK_BOXEN_INC_ZULETZTANGESEHEN, ['box' => $this]);
        }
    }
}
