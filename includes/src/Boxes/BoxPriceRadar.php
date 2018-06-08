<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxPriceRadar
 * @package Boxes
 */
final class BoxPriceRadar extends AbstractBox
{
    /**
     * BoxPriceRadar constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $nLimit          = (isset($config['boxen']['boxen_preisradar_anzahl'])
            && (int)$config['boxen']['boxen_preisradar_anzahl'] > 0)
            ? (int)$config['boxen']['boxen_preisradar_anzahl']
            : 3;
        $nTage           = (isset($config['boxen']['boxen_preisradar_anzahltage'])
            && (int)$config['boxen']['boxen_preisradar_anzahltage'] > 0)
            ? (int)$config['boxen']['boxen_preisradar_anzahltage']
            : 30;
        $oPreisradar_arr = \Preisradar::getProducts(\Session::CustomerGroup()->getID(), $nLimit, $nTage);
        $products        = [];
        if (count($oPreisradar_arr) > 0) {
            $this->setShow(true);
            $defaultOptions = \Artikel::getDefaultOptions();
            foreach ($oPreisradar_arr as $oPreisradar) {
                $oArtikel = new \Artikel();
                $oArtikel->fuelleArtikel($oPreisradar->kArtikel, $defaultOptions);
                $oArtikel->oPreisradar                     = new \stdClass();
                $oArtikel->oPreisradar->fDiff              = $oPreisradar->fDiff * -1;
                $oArtikel->oPreisradar->fDiffLocalized[0]  = gibPreisStringLocalized(
                    berechneBrutto($oArtikel->oPreisradar->fDiff, $oArtikel->Preise->fUst)
                );
                $oArtikel->oPreisradar->fDiffLocalized[1]  = gibPreisStringLocalized(
                    $oArtikel->oPreisradar->fDiff
                );
                $oArtikel->oPreisradar->fOldVKLocalized[0] = gibPreisStringLocalized(
                    berechneBrutto(
                        $oArtikel->Preise->fVKNetto + $oArtikel->oPreisradar->fDiff,
                        $oArtikel->Preise->fUst
                    )
                );
                $oArtikel->oPreisradar->fOldVKLocalized[1] = gibPreisStringLocalized(
                    $oArtikel->Preise->fVKNetto + $oArtikel->oPreisradar->fDiff
                );
                $oArtikel->oPreisradar->fProzentDiff       = $oPreisradar->fProzentDiff;

                if ((int)$oArtikel->kArtikel > 0) {
                    $products[] = $oArtikel;
                }
            }
            $this->setProducts($products);
        }
    }
}
