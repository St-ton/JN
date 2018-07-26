<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxCompareList
 * @package Boxes
 */
final class BoxCompareList extends AbstractBox
{
    /**
     * BoxCompareList constructor.
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
            $cZusatzParams         = '';
            $cPostMembers_arr      = \array_keys($_REQUEST);
            foreach ($cPostMembers_arr as $cPostMember) {
                if ((int)$_REQUEST[$cPostMember] > 0 && \in_array($cPostMember, $cGueltigePostVars_arr, true)) {
                    $cZusatzParams .= '&' . $cPostMember . '=' . $_REQUEST[$cPostMember];
                }
            }
            $cZusatzParams  = \StringHandler::filterXSS($cZusatzParams);
            $products       = [];
            $cRequestURI    = \Shop::getRequestUri();
            $defaultOptions = \Artikel::getDefaultOptions();
            if ($cRequestURI === 'io.php') {
                // Box wird von einem Ajax-Call gerendert
                $cRequestURI = \LinkHelper::getInstance()->getStaticRoute('vergleichsliste.php');
            }
            foreach ($oArtikel_arr as $oArtikel) {
                $nPosAnd   = \strrpos($cRequestURI, '&');
                $nPosQuest = \strrpos($cRequestURI, '?');
                $nPosWD    = \strpos($cRequestURI, 'vlplo=');

                if ($nPosWD) {
                    $cRequestURI = \substr($cRequestURI, 0, $nPosWD);
                }
                // z.b. index.php
                $cDeleteParam = '?vlplo=';
                if ($nPosAnd === \strlen($cRequestURI) - 1) {
                    // z.b. index.php?a=4&
                    $cDeleteParam = 'vlplo=';
                } elseif ($nPosAnd) {
                    // z.b. index.php?a=4&b=2
                    $cDeleteParam = '&vlplo=';
                } elseif ($nPosQuest) {
                    // z.b. index.php?a=4
                    $cDeleteParam = '&vlplo=';
                } elseif ($nPosQuest === \strlen($cRequestURI) - 1) {
                    // z.b. index.php?
                    $cDeleteParam = 'vlplo=';
                }
                $artikel = new \Artikel();
                $artikel->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                $artikel->cURLDEL = $cRequestURI . $cDeleteParam . $oArtikel->kArtikel . $cZusatzParams;
                if (isset($oArtikel->oVariationen_arr) && \count($oArtikel->oVariationen_arr) > 0) {
                    $artikel->Variationen = $oArtikel->oVariationen_arr;
                }
                if ($artikel->kArtikel > 0) {
                    $products[] = $artikel;
                }
            }
            $this->setShow(true);
            $this->setItemCount((int)$this->config['vergleichsliste']['vergleichsliste_anzahl']);
            $this->setProducts($products);

            \executeHook(HOOK_BOXEN_INC_VERGLEICHSLISTE, ['box' => $this]);
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
