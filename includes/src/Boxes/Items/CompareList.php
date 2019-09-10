<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\Artikel;
use JTL\Helpers\Text;
use JTL\Services\JTL\LinkService;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class CompareList
 * @package JTL\Boxes\Items
 */
final class CompareList extends AbstractBox
{
    /**
     * CompareList constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('cAnzeigen', 'ShowBox');
        $this->setShow(true);
        $productList = Frontend::get('Vergleichsliste')->oArtikel_arr ?? [];
        $products    = [];
        if (\count($productList) > 0) {
            $validParams = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
            $extra       = '';
            $postData    = \array_keys($_REQUEST);
            foreach ($postData as $param) {
                if ((int)$_REQUEST[$param] > 0 && \in_array($param, $validParams, true)) {
                    $extra .= '&' . $param . '=' . $_REQUEST[$param];
                }
            }
            $extra          = Text::filterXSS($extra);
            $defaultOptions = Artikel::getDefaultOptions();
            $baseURL        = LinkService::getInstance()->getStaticRoute('vergleichsliste.php');
            foreach ($productList as $item) {
                $product = new Artikel();
                $product->fuelleArtikel($item->kArtikel, $defaultOptions);
                $product->cURLDEL = $baseURL . '?vlplo=' . $item->kArtikel . $extra;
                if (isset($item->oVariationen_arr) && \count($item->oVariationen_arr) > 0) {
                    $product->Variationen = $item->oVariationen_arr;
                }
                if ($product->kArtikel > 0) {
                    $products[] = $product;
                }
            }
        }
        $this->setItemCount((int)$this->config['vergleichsliste']['vergleichsliste_anzahl']);
        $this->setProducts($products);
        \executeHook(\HOOK_BOXEN_INC_VERGLEICHSLISTE, ['box' => $this]);
    }

    /**
     * @return string
     */
    public function getShowBox(): string
    {
        return $this->config['boxen']['boxen_vergleichsliste_anzeigen'];
    }

    /**
     * @param string $value
     */
    public function setShowBox(string $value): void
    {
    }
}
