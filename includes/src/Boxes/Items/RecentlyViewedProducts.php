<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\Artikel;
use JTL\Session\Frontend;

/**
 * Class RecentlyViewedProducts
 * @package JTL\Boxes\Items
 */
final class RecentlyViewedProducts extends AbstractBox
{
    /**
     * RecentlyViewedProducts constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        if (isset($_SESSION['ZuletztBesuchteArtikel'])
            && \is_array($_SESSION['ZuletztBesuchteArtikel'])
            && \count($_SESSION['ZuletztBesuchteArtikel']) > 0
            && Frontend::getCustomerGroup()->mayViewCategories()
        ) {
            $products       = [];
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($_SESSION['ZuletztBesuchteArtikel'] as $i => $item) {
                $product = new Artikel();
                $product->fuelleArtikel($item->kArtikel, $defaultOptions);
                if ($product->kArtikel > 0) {
                    $products[$i] = $product;
                }
            }
            $this->setProducts(\array_reverse($products));
            $this->setShow(true);

            \executeHook(\HOOK_BOXEN_INC_ZULETZTANGESEHEN, ['box' => $this]);
        }
    }
}
