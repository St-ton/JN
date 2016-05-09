<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES . 'interface.JTL-Shop.ExtensionPoint.php';

/**
 * Class Slider
 *
 * @access public
 */
class Slider implements IExtensionPoint
{
    /**
     * @var int
     */
    public $kSlider;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $nSeitenTyp;

    /**
     * @var string
     */
    public $cTheme;

    /**
     * @var int
     */
    public $bAktiv = 0;

    /**
     * @var string
     */
    public $cEffects = 'random';

    /**
     * @var int
     */
    public $nPauseTime = 3000;

    /**
     * @var bool
     */
    public $bThumbnail = false;

    /**
     * @var int
     */
    public $nAnimationSpeed = 500;

    /**
     * @var bool
     */
    public $bPauseOnHover = false;

    /**
     * @var array
     */
    public $oSlide_arr = array();

    /**
     * @var bool
     */
    public $bControlNav = true;

    /**
     * @var bool
     */
    public $bRandomStart = false;

    /**
     * @var bool
     */
    public $bDirectionNav = true;

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param int $kSlider
     * @return $this
     */
    public function init($kSlider)
    {
        if (intval($kSlider) != 0) {
            global $smarty;

            if ($this->load($kSlider, 'AND bAktiv = 1') === true) {
                if ($this->bAktiv == 1) {
                    $smarty->assign('PFAD_SLIDER', Shop::getURL() . '/' . PFAD_BILDER_SLIDER);
                    $smarty->assign('oSlider', $this);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $cData_arr
     * @return $this
     */
    public function set(array $cData_arr)
    {
        $cObjectFields_arr = get_class_vars('Slider');
        unset($cObjectFields_arr['oSlide_arr']);

        foreach ($cObjectFields_arr as $cField => $cValue) {
            if (isset($cData_arr[$cField])) {
                $this->$cField = $cData_arr[$cField];
            }
        }

        return $this;
    }

    /**
     * @param string $kSlider
     * @param string $filter
     * @param string $limit
     * @return bool
     */
    public function load($kSlider = '', $filter = '', $limit = '1')
    {
        if (isset($kSlider) && intval($kSlider !== 0) || !empty($this->kSlider) && intval($this->kSlider !== 0)) {
            if (empty($kSlider) || (int)$kSlider === 0) {
                $kSlider = $this->kSlider;
            }
            $kSlider     = (int)$kSlider;
            $cSlider_arr = Shop::DB()->query("
                SELECT *
                    FROM tslider
                    WHERE kSlider = " . $kSlider . " " . $filter . "
                    LIMIT " . $limit, 8
            );
            if ($cSlider_arr === null) {
                return false;
            }
            $kSlide_arr = Shop::DB()->query("
                SELECT kslide
                    FROM tslide
                    WHERE kSlider = " . $kSlider . "
                    ORDER BY nSort ASC", 9
            );
            $oSlide_arr = array();
            foreach ($kSlide_arr as $kSlide) {
                $oSlide          = new Slide();
                $oSlide->kSlider = (int)$cSlider_arr['kSlider'];
                $oSlide->kSlide  = (int)$kSlide['kslide'];
                $oSlide->load();
                $oSlide_arr[] = $oSlide;
            }

            if (is_array($oSlide_arr)) {
                $this->oSlide_arr = $oSlide_arr;
            }

            if (is_array($cSlider_arr)) {
                $this->set($cSlider_arr);

                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (isset($this->kSlider) && intval($this->kSlider) !== 0) {
            return $this->update();
        }

        return $this->append();
    }

    /**
     * @return bool
     */
    private function append()
    {
        $oSlider = clone $this;
        unset($oSlider->oSlide_arr);
        unset($oSlider->kSlider);

        $kSlider = Shop::DB()->insert('tslider', $oSlider);

        if (intval($kSlider) != 0) {
            $this->kSlider = $kSlider;

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    private function update()
    {
        $oSlider = clone $this;

        unset($oSlider->oSlide_arr);
        unset($oSlider->kSlider);

        return Shop::DB()->update('tslider', 'kSlider', $this->kSlider, $oSlider) >= 0;
    }

    /**
     * @param int $kSlider
     * @return bool
     */
    public function delete($kSlider = 0)
    {
        if (intval($this->kSlider) !== 0 && intval($kSlider) !== 0) {
            $kSlider = $this->kSlider;
        }
        $kSlider = (int)$kSlider;
        if ($kSlider !== 0) {
            $bSuccess = Shop::DB()->delete('tslider', 'kSlider', $kSlider);
            Shop::DB()->query("DELETE FROM textensionpoint WHERE cClass = 'Slider' AND kInitial = " . $kSlider, 4);

            if ($bSuccess == true) {
                if (!empty($this->oSlide_arr)) {
                    foreach ($this->oSlide_arr as $oSlide) {
                        $oSlide->delete();
                    }
                }

                return true;
            }
        }

        return false;
    }
}
