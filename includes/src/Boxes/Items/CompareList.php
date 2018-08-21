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
        $oArtikel_arr = [];
        if (isset($_SESSION['Vergleichsliste']->oArtikel_arr)) {
            $oArtikel_arr = $_SESSION['Vergleichsliste']->oArtikel_arr;
        }
        if (\count($oArtikel_arr) > 0) {
            $cGueltigePostVars_arr = ['a', 'k', 's', 'h', 'l', 'm', 't', 'hf', 'kf', 'show', 'suche'];
            $extra                 = '';
            $cPostMembers_arr      = \array_keys($_REQUEST);
            foreach ($cPostMembers_arr as $cPostMember) {
                if ((int)$_REQUEST[$cPostMember] > 0 && \in_array($cPostMember, $cGueltigePostVars_arr, true)) {
                    $extra .= '&' . $cPostMember . '=' . $_REQUEST[$cPostMember];
                }
            }
            $extra          = \StringHandler::filterXSS($extra);
            $products       = [];
            $requestURI     = \Shop::getRequestUri();
            $defaultOptions = \Artikel::getDefaultOptions();
            if ($requestURI === 'io.php') {
                // Box wird von einem Ajax-Call gerendert
                $requestURI = \LinkHelper::getInstance()->getStaticRoute('vergleichsliste.php');
            }
            foreach ($oArtikel_arr as $oArtikel) {
                $nPosAnd   = \strrpos($requestURI, '&');
                $nPosQuest = \strrpos($requestURI, '?');
                $nPosWD    = \strpos($requestURI, 'vlplo=');

                if ($nPosWD) {
                    $requestURI = \substr($requestURI, 0, $nPosWD);
                }
                $cDeleteParam = '?vlplo=';
                if ($nPosAnd === \strlen($requestURI) - 1) {
                    // z.b. index.php?a=4&
                    $cDeleteParam = 'vlplo=';
                } elseif ($nPosAnd) {
                    // z.b. index.php?a=4&b=2
                    $cDeleteParam = '&vlplo=';
                } elseif ($nPosQuest) {
                    // z.b. index.php?a=4
                    $cDeleteParam = '&vlplo=';
                } elseif ($nPosQuest === \strlen($requestURI) - 1) {
                    // z.b. index.php?
                    $cDeleteParam = 'vlplo=';
                }
                $product = new \Artikel();
                $product->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                $product->cURLDEL = $requestURI . $cDeleteParam . $oArtikel->kArtikel . $extra;
                if (isset($oArtikel->oVariationen_arr) && \count($oArtikel->oVariationen_arr) > 0) {
                    $product->Variationen = $oArtikel->oVariationen_arr;
                }
                if ($product->kArtikel > 0) {
                    $products[] = $product;
                }
            }
            $this->setShow(true);
            $this->setItemCount((int)$this->config['vergleichsliste']['vergleichsliste_anzahl']);
            $this->setProducts($products);

            \executeHook(\HOOK_BOXEN_INC_VERGLEICHSLISTE, ['box' => $this]);
        } else {
            $this->setShow(false);
        }
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
