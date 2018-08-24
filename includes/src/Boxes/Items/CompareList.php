<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


/**
 * Class CompareList
 * @package Boxes
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
        parent::addMapping('cAnzeigen', 'ShowBox');
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
            $extra = \StringHandler::filterXSS($extra);

            $requestURI     = \Shop::getRequestUri();
            $defaultOptions = \Artikel::getDefaultOptions();
            if ($requestURI === 'io.php') {
                // Box wird von einem Ajax-Call gerendert
                $requestURI = \LinkHelper::getInstance()->getStaticRoute('vergleichsliste.php');
            }
            foreach ($productList as $_prod) {
                $nPosAnd   = \strrpos($requestURI, '&');
                $nPosQuest = \strrpos($requestURI, '?');
                $nPosWD    = \strpos($requestURI, 'vlplo=');

                if ($nPosWD) {
                    $requestURI = \substr($requestURI, 0, $nPosWD);
                }
                $del = '?vlplo=';
                if ($nPosAnd === \strlen($requestURI) - 1) {
                    $del = 'vlplo=';
                } elseif ($nPosAnd) {
                    $del = '&vlplo=';
                } elseif ($nPosQuest) {
                    $del = '&vlplo=';
                } elseif ($nPosQuest === \strlen($requestURI) - 1) {
                    $del = 'vlplo=';
                }
                $product = new \Artikel();
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
    public function setShowBox(string $value)
    {

    }
}
