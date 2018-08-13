<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes\Items;


/**
 * Class PriceRadar
 * @package Boxes
 */
final class PriceRadar extends AbstractBox
{
    /**
     * PriceRadar constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setShow(false);
        $limit    = (isset($config['boxen']['boxen_preisradar_anzahl'])
            && (int)$config['boxen']['boxen_preisradar_anzahl'] > 0)
            ? (int)$config['boxen']['boxen_preisradar_anzahl']
            : 3;
        $days     = (isset($config['boxen']['boxen_preisradar_anzahltage'])
            && (int)$config['boxen']['boxen_preisradar_anzahltage'] > 0)
            ? (int)$config['boxen']['boxen_preisradar_anzahltage']
            : 30;
        $data     = \Preisradar::getProducts(\Session::CustomerGroup()->getID(), $limit, $days);
        $products = [];
        if (\count($data) > 0) {
            $this->setShow(true);
            $defaultOptions = \Artikel::getDefaultOptions();
            foreach ($data as $oPreisradar) {
                $product = new \Artikel();
                $product->fuelleArtikel($oPreisradar->kArtikel, $defaultOptions);
                $product->oPreisradar                     = new \stdClass();
                $product->oPreisradar->fDiff              = $oPreisradar->fDiff * -1;
                $product->oPreisradar->fDiffLocalized[0]  = \Preise::getLocalizedPriceString(
                    \TaxHelper::getGross($product->oPreisradar->fDiff, $product->Preise->fUst)
                );
                $product->oPreisradar->fDiffLocalized[1]  = \Preise::getLocalizedPriceString(
                    $product->oPreisradar->fDiff
                );
                $product->oPreisradar->fOldVKLocalized[0] = \Preise::getLocalizedPriceString(
                    \TaxHelper::getGross(
                        $product->Preise->fVKNetto + $product->oPreisradar->fDiff,
                        $product->Preise->fUst
                    )
                );
                $product->oPreisradar->fOldVKLocalized[1] = \Preise::getLocalizedPriceString(
                    $product->Preise->fVKNetto + $product->oPreisradar->fDiff
                );
                $product->oPreisradar->fProzentDiff       = $oPreisradar->fProzentDiff;

                if ((int)$product->kArtikel > 0) {
                    $products[] = $product;
                }
            }
            $this->setProducts($products);
        }
    }
}
