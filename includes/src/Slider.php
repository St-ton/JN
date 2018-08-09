<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Slider
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
    public $oSlide_arr = [];

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
     * @var bool
     */
    public $bUseKB = true;

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
        $kSlider = (int)$kSlider;
        if ($kSlider > 0 && $this->load($kSlider, 'AND bAktiv = 1') === true) {
            Shop::Smarty()->assign('oSlider', $this);
        }

        return $this;
    }

    /**
     * @param array $cData_arr
     * @return $this
     */
    public function set(array $cData_arr): self
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
     * @param int    $kSlider
     * @param string $filter
     * @param int $limit
     * @return bool
     */
    public function load(int $kSlider = 0, $filter = '', int $limit = 1): bool
    {
        if ($kSlider > 0 || (!empty($this->kSlider) && (int)$this->kSlider > 0)) {
            if (empty($kSlider) || $kSlider === 0) {
                $kSlider = $this->kSlider;
            }
            $cSlider_arr = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM tslider
                    WHERE kSlider = " . $kSlider . " " . $filter . "
                    LIMIT " . $limit, 8
            );
            if ($cSlider_arr === null) {
                return false;
            }
            $slides = Shop::Container()->getDB()->queryPrepared(
                'SELECT kSlide
                    FROM tslide
                    WHERE kSlider = :sliderID
                    ORDER BY nSort ASC',
                ['sliderID' => $kSlider],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($slides as $slide) {
                $this->oSlide_arr[] = new Slide($cSlider_arr['kSlider'], $slide->kSlide);
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
        return $this->kSlider > 0
            ? $this->update()
            : $this->append();
    }

    /**
     * @return bool
     */
    private function append(): bool
    {
        $oSlider = clone $this;
        unset($oSlider->oSlide_arr, $oSlider->kSlider);

        $kSlider = Shop::Container()->getDB()->insert('tslider', $oSlider);

        if ($kSlider > 0) {
            $this->kSlider = $kSlider;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function update(): bool
    {
        $oSlider = clone $this;

        unset($oSlider->oSlide_arr, $oSlider->kSlider);

        return Shop::Container()->getDB()->update('tslider', 'kSlider', $this->kSlider, $oSlider) >= 0;
    }

    /**
     * @param int $kSlider
     * @return bool
     */
    public function delete(int $kSlider = 0): bool
    {
        if ((int)$this->kSlider !== 0 && $kSlider !== 0) {
            $kSlider = $this->kSlider;
        }
        if ($kSlider !== 0) {
            $affected = Shop::Container()->getDB()->delete('tslider', 'kSlider', $kSlider);
            Shop::Container()->getDB()->delete('textensionpoint', ['cClass', 'kInitial'], ['Slider', $kSlider]);

            if ($affected > 0) {
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
