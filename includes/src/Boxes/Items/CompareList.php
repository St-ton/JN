<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Boxes\Items;

use JTL\Catalog\Product\Artikel;
use JTL\Helpers\Text;
use JTL\Services\JTL\LinkService;
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
        $productList = [];
        $products    = [];
        if (isset($_SESSION['Vergleichsliste']->oArtikel_arr)) {
            $productList = $_SESSION['Vergleichsliste']->oArtikel_arr;
        }
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
            $requestURI     = Shop::getRequestUri();
            $defaultOptions = Artikel::getDefaultOptions();
            if ($requestURI === 'io.php') {
                // render via ajax call
                $requestURI = LinkService::getInstance()->getStaticRoute('vergleichsliste.php');
            }
            foreach ($productList as $_prod) {
                $nPosAnd   = \mb_strrpos($requestURI, '&');
                $nPosQuest = \mb_strrpos($requestURI, '?');
                $nPosWD    = \mb_strpos($requestURI, 'vlplo=');

                if ($nPosWD) {
                    $requestURI = \mb_substr($requestURI, 0, $nPosWD);
                }
                $del = '?vlplo=';
                if ($nPosAnd === \mb_strlen($requestURI) - 1) {
                    $del = 'vlplo=';
                } elseif ($nPosAnd) {
                    $del = '&vlplo=';
                } elseif ($nPosQuest) {
                    $del = '&vlplo=';
                } elseif ($nPosQuest === \mb_strlen($requestURI) - 1) {
                    $del = 'vlplo=';
                }
                $product = new Artikel();
                $product->fuelleArtikel($_prod->kArtikel, $defaultOptions);
                $product->cURLDEL = $requestURI . $del . $_prod->kArtikel . $extra;
                if (isset($_prod->oVariationen_arr) && \count($_prod->oVariationen_arr) > 0) {
                    $product->Variationen = $_prod->oVariationen_arr;
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
