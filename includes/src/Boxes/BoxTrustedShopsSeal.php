<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxTrustedShopsSeal
 * @package Boxes
 */
final class BoxTrustedShopsSeal extends AbstractBox
{
    /**
     * @var string
     */
    private $logoURL = '';

    /**
     * @var string
     */
    private $logoSealURL = '';

    /**
     * @var string
     */
    private $imageURL = '';

    /**
     * @var string
     */
    private $backGroundImageURL = '';

    /**
     * BoxDirectPurchase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        parent::addMapping('cLogoURL', 'LogoURL');
        parent::addMapping('cLogoSiegelBoxURL', 'LogoSealURL');
        parent::addMapping('cBild', 'ImageURL');
        parent::addMapping('cBGBild', 'BackGroundImageURL');
        $this->setShow(false);
        if ($config['trustedshops']['trustedshops_nutzen'] === 'Y') {
            $langISO = \StringHandler::convertISO2ISO639(\Shop::getLanguageCode());
            $shopURL = \Shop::getURL(true) . '/';
            $ts      = new \TrustedShops(-1, $langISO);
            if ((int)$ts->nAktiv === 1 && !empty($ts->cLogoURL) && \strlen($ts->tsId) > 0) {
                $this->setShow(true);
                $this->setLogoURL($ts->cLogoURL);
                $this->setLogoSealURL($ts->cLogoSiegelBoxURL[$langISO]);
                $this->setImageURL($shopURL . \PFAD_GFX_TRUSTEDSHOPS . 'trustedshops_m.png');
                $this->setBackGroundImageURL($shopURL . \PFAD_GFX_TRUSTEDSHOPS . 'bg_yellow.jpg');
            }
        }
    }

    /**
     * @return string
     */
    public function getLogoURL(): string
    {
        return $this->logoURL;
    }

    /**
     * @param string $logoURL
     */
    public function setLogoURL(string $logoURL)
    {
        $this->logoURL = $logoURL;
    }

    /**
     * @return string
     */
    public function getLogoSealURL(): string
    {
        return $this->logoSealURL;
    }

    /**
     * @param string $logoSealURL
     */
    public function setLogoSealURL(string $logoSealURL)
    {
        $this->logoSealURL = $logoSealURL;
    }

    /**
     * @return string
     */
    public function getImageURL(): string
    {
        return $this->imageURL;
    }

    /**
     * @param string $imageURL
     */
    public function setImageURL(string $imageURL)
    {
        $this->imageURL = $imageURL;
    }

    /**
     * @return string
     */
    public function getBackGroundImageURL(): string
    {
        return $this->backGroundImageURL;
    }

    /**
     * @param string $backGroundImageURL
     */
    public function setBackGroundImageURL(string $backGroundImageURL)
    {
        $this->backGroundImageURL = $backGroundImageURL;
    }
}
