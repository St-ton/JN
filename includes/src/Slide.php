<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Slide
 */
class Slide
{
    /**
     * @var int
     */
    public $kSlide;

    /**
     * @var int
     */
    public $kSlider;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var string
     */
    public $cText;

    /**
     * @var string
     */
    public $cThumbnail;

    /**
     * @var string
     */
    public $cLink;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cBildAbsolut;

    /**
     * @var string
     */
    public $cThumbnailAbsolut;

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param int $kSlider
     * @param int $kSlide
     */
    public function __construct($kSlider = 0, $kSlide = 0)
    {
        if ($kSlider > 0 && $kSlide > 0) {
            $this->load($kSlider, $kSlide);
        }
    }

    /**
     * @param int $kSlider
     * @param int $kSlide
     * @return bool
     */
    public function load($kSlider = 0, $kSlide = 0)
    {
        if ((int)$kSlider > 0
            || (!empty($this->kSlider) && (int)$this->kSlider > 0 && (int)$kSlide > 0)
            || (!empty($this->kSlide) && (int)$this->kSlide > 0)
        ) {
            if (empty($kSlider) || (int)$kSlider === 0) {
                $kSlider = $this->kSlider;
            }
            if (empty($kSlide) || (int)$kSlide === 0) {
                $kSlide = $this->kSlide;
            }

            $oSlide = Shop::Container()->getDB()->select('tslide', 'kSlide', (int)$kSlide);

            if (is_object($oSlide)) {
                $this->set((array)$oSlide);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $cData_arr
     * @return $this
     */
    public function set(array $cData_arr)
    {
        $cObjectFields_arr = get_class_vars('Slide');
        foreach ($cObjectFields_arr as $cField => $cValue) {
            if (isset($cData_arr[$cField])) {
                $this->$cField = $cData_arr[$cField];
            }
        }

        return $this->setAbsoluteImagePaths();
    }

    /**
     * @return $this
     */
    private function setAbsoluteImagePaths()
    {
        $imageBaseURL = Shop::getImageBaseURL();
        $this->cBildAbsolut      = $imageBaseURL . PFAD_MEDIAFILES .
            str_replace($imageBaseURL . PFAD_MEDIAFILES, '', $this->cBild);
        $this->cThumbnailAbsolut = $imageBaseURL . PFAD_MEDIAFILES .
            str_replace($imageBaseURL . PFAD_MEDIAFILES, '', $this->cThumbnail);

        return $this;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!empty($this->cBild)) {
            $cShopUrl  = parse_url(Shop::getURL(), PHP_URL_PATH);
            $cShopUrl2 = parse_url(URL_SHOP, PHP_URL_PATH);
            if (strrpos($cShopUrl, '/') !== (strlen($cShopUrl) - 1)) {
                $cShopUrl .= '/';
            }
            if (strrpos($cShopUrl2, '/') !== (strlen($cShopUrl2) - 1)) {
                $cShopUrl2 .= '/';
            }
            $cPfad  = $cShopUrl . PFAD_MEDIAFILES;
            $cPfad2 = $cShopUrl2 . PFAD_MEDIAFILES;
            if (strpos($this->cBild, $cPfad) !== false) {
                $nStrLength       = strlen($cPfad);
                $this->cBild      = substr($this->cBild, $nStrLength);
                $this->cThumbnail = '.thumbs/' . $this->cBild;
            } elseif (strpos($this->cBild, $cPfad2) !== false) {
                $nStrLength       = strlen($cPfad2);
                $this->cBild      = substr($this->cBild, $nStrLength);
                $this->cThumbnail = '.thumbs/' . $this->cBild;
            }
        }

        return $this->kSlide === null
            ? $this->append()
            : $this->update();
    }

    /**
     * @return int
     */
    private function update()
    {
        $oSlide = clone $this;
        if (empty($oSlide->cThumbnail)) {
            unset($oSlide->cThumbnail);
        }
        unset($oSlide->cBildAbsolut, $oSlide->cThumbnailAbsolut, $oSlide->kSlide);

        return Shop::Container()->getDB()->update('tslide', 'kSlide', (int)$this->kSlide, $oSlide);
    }

    /**
     * @return bool
     */
    private function append()
    {
        if (!empty($this->cBild)) {
            $oSlide = clone $this;
            unset($oSlide->cBildAbsolut, $oSlide->cThumbnailAbsolut, $oSlide->kSlide);
            if ($this->nSort === null) {
                $oSort = Shop::Container()->getDB()->queryPrepared(
                    'SELECT nSort
                        FROM tslide
                        WHERE kSlider = :sliderID
                        ORDER BY nSort DESC LIMIT 1',
                    ['sliderID' => $this->kSlider],
                    \DB\ReturnType::SINGLE_OBJECT
                );
                $oSlide->nSort = (!is_object($oSort) || (int)$oSort->nSort === 0) ? 1 : ($oSort->nSort + 1);
            }
            $kSlide = Shop::Container()->getDB()->insert('tslide', $oSlide);
            if ($kSlide > 0) {
                $this->kSlide = $kSlide;

                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return $this->kSlide !== null
            && (int)$this->kSlide > 0
            && Shop::DB()->delete('tslide', 'kSlide', (int)$this->kSlide) > 0;
    }
}
