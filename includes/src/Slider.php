<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\MagicCompatibilityTrait;

/**
 * Class Slider
 */
class Slider implements IExtensionPoint
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var int
     */
    private $languageID = 0;

    /**
     * @var int
     */
    private $customerGroupID = 0;

    /**
     * @var int
     */
    private $pageType = 0;

    /**
     * @var string
     */
    private $theme = '';

    /**
     * @var int
     */
    private $isActive = false;

    /**
     * @var string
     */
    private $effects = 'random';

    /**
     * @var int
     */
    private $pauseTime = 3000;

    /**
     * @var bool
     */
    private $thumbnail = false;

    /**
     * @var int
     */
    private $animationSpeed = 500;

    /**
     * @var bool
     */
    private $pauseOnHover = false;

    /**
     * @var Slide[]
     */
    private $slides = [];

    /**
     * @var bool
     */
    private $controlNav = true;

    /**
     * @var bool
     */
    private $randomStart = false;

    /**
     * @var bool
     */
    private $directionNav = true;

    /**
     * @var bool
     */
    private $useKB = true;

    /**
     * @var array
     */
    private static $mapping = [
        'bAktiv'          => 'IsActive',
        'kSlider'         => 'ID',
        'cName'           => 'Name',
        'kSprache'        => 'LanguageID',
        'nSeitentyp'      => 'PageType',
        'cTheme'          => 'Theme',
        'cEffects'        => 'Effects',
        'nPauseTime'      => 'PauseTime',
        'bThumbnail'      => 'Thumbnail',
        'nAnimationSpeed' => 'AnimationSpeed',
        'bPauseOnHover'   => 'PauseOnHover',
        'oSlide_arr'      => 'Slides',
        'bControlNav'     => 'ControlNav',
        'bRandomStart'    => 'RandomStart',
        'bDirectionNav'   => 'DirectionNav',
        'bUseKB'          => 'UseKB',
    ];

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param string $type
     * @return string|null
     */
    private function getMapping(string $type): ?string
    {
        return self::$mapping[$type] ?? null;
    }

    /**
     * @param int $kSlider
     * @return $this
     */
    public function init($kSlider)
    {
        $loaded = $this->load($kSlider);
        if ($kSlider > 0 && $loaded === true) {
            Shop::Smarty()->assign('oSlider', $this);
        }

        return $this;
    }

    /**
     * @param stdClass $data
     * @return $this
     */
    public function set(stdClass $data): self
    {
        foreach (get_object_vars($data) as $field => $value) {
            if (($mapping = $this->getMapping($field)) !== null) {
                $method = 'set' . $mapping;
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @param int  $kSlider
     * @param bool $active
     * @return bool
     */
    public function load(int $kSlider = 0, $active = true): bool
    {
        if ($kSlider <= 0 && $this->id <= 0) {
            return false;
        }
        $activeSQL = $active ? ' AND bAktiv = 1 ' : '';
        if ($kSlider === 0) {
            $kSlider = $this->id;
        }
        $data  = Shop::Container()->getDB()->queryPrepared(
            'SELECT *, tslider.kSlider AS id FROM tslider
                LEFT JOIN tslide
                    ON tslider.kSlider = tslide.kSlider
                WHERE tslider.kSlider = :kslider' . $activeSQL .
            ' ORDER BY tslide.nSort',
            ['kslider' => $kSlider],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $first = \Functional\first($data);
        if ($first !== null) {
            $this->setID($first->id);
            foreach ($data as $slideData) {
                $slideData->kSlider = $this->getID();
                if ($slideData->kSlide !== null) {
                    $slide = new Slide();
                    $slide->map($slideData);
                    $this->slides[] = $slide;
                }
            }
            $this->set($first);

            return $this->getID() > 0 && count($this->slides) > 0;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return $this->id > 0
            ? $this->update()
            : $this->append();
    }

    /**
     * @return bool
     */
    private function append(): bool
    {
        $slider = new stdClass();
        foreach (self::$mapping as $type => $methodName) {
            $method        = 'get' . $methodName;
            $slider->$type = $this->$method();
        }
        unset($slider->oSlide_arr, $slider->slides, $slider->kSlider);

        $kSlider = Shop::Container()->getDB()->insert('tslider', $slider);

        if ($kSlider > 0) {
            $this->id = $kSlider;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function update(): bool
    {
        $slider = new stdClass();
        foreach (self::$mapping as $type => $methodName) {
            $method        = 'get' . $methodName;
            $slider->$type = $this->$method();
        }
        unset($slider->oSlide_arr, $slider->slides, $slider->kSlider);

        return Shop::Container()->getDB()->update('tslider', 'kSlider', $this->getID(), $slider) >= 0;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        $id = $this->getID();
        if ($id !== 0) {
            $affected = Shop::Container()->getDB()->delete('tslider', 'kSlider', $id);
            Shop::Container()->getDB()->delete('textensionpoint', ['cClass', 'kInitial'], ['Slider', $id]);
            if ($affected > 0) {
                foreach ($this->slides as $slide) {
                    $slide->delete();
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int|string $kSlider
     */
    public function setID($kSlider): void
    {
        $this->id = (int)$kSlider;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getLanguageID(): int
    {
        return $this->languageID;
    }

    /**
     * @param int|string $languageID
     */
    public function setLanguageID($languageID): void
    {
        $this->languageID = (int)$languageID;
    }

    /**
     * @return int
     */
    public function getCustomerGroupID(): int
    {
        return $this->customerGroupID;
    }

    /**
     * @param int|string $customerGroupID
     */
    public function setCustomerGroupID($customerGroupID): void
    {
        $this->customerGroupID = (int)$customerGroupID;
    }

    /**
     * @return int
     */
    public function getPageType(): int
    {
        return $this->pageType;
    }

    /**
     * @param int|string $pageType
     */
    public function setPageType($pageType): void
    {
        $this->pageType = (int)$pageType;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * @return int
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param int|string|bool $isActive
     */
    public function setIsActive($isActive): void
    {
        $this->isActive = (bool)$isActive;
    }

    /**
     * @return string
     */
    public function getEffects(): string
    {
        return $this->effects;
    }

    /**
     * @param string $effects
     */
    public function setEffects(string $effects): void
    {
        $this->effects = $effects;
    }

    /**
     * @return int
     */
    public function getPauseTime(): int
    {
        return $this->pauseTime;
    }

    /**
     * @param int|string $pauseTime
     */
    public function setPauseTime($pauseTime): void
    {
        $this->pauseTime = (int)$pauseTime;
    }

    /**
     * @return bool
     */
    public function getThumbnail(): bool
    {
        return $this->thumbnail;
    }

    /**
     * @param bool|int|string $thumbnail
     */
    public function setThumbnail($thumbnail): void
    {
        $this->thumbnail = (bool)$thumbnail;
    }

    /**
     * @return int
     */
    public function getAnimationSpeed(): int
    {
        return $this->animationSpeed;
    }

    /**
     * @param int|string $animationSpeed
     */
    public function setAnimationSpeed($animationSpeed): void
    {
        $this->animationSpeed = (int)$animationSpeed;
    }

    /**
     * @return bool
     */
    public function getPauseOnHover(): bool
    {
        return $this->pauseOnHover;
    }

    /**
     * @param bool|int|string $pauseOnHover
     */
    public function setPauseOnHover($pauseOnHover): void
    {
        $this->pauseOnHover = (bool)$pauseOnHover;
    }

    /**
     * @return array
     */
    public function getSlides(): array
    {
        return $this->slides;
    }

    /**
     * @param array $slides
     */
    public function setSlides(array $slides): void
    {
        $this->slides = $slides;
    }

    /**
     * @return bool
     */
    public function getControlNav(): bool
    {
        return $this->controlNav;
    }

    /**
     * @param bool|string|int $controlNav
     */
    public function setControlNav($controlNav): void
    {
        $this->controlNav = (bool)$controlNav;
    }

    /**
     * @return bool
     */
    public function getRandomStart(): bool
    {
        return $this->randomStart;
    }

    /**
     * @param bool|string|int $randomStart
     */
    public function setRandomStart($randomStart): void
    {
        $this->randomStart = (bool)$randomStart;
    }

    /**
     * @return bool
     */
    public function getDirectionNav(): bool
    {
        return $this->directionNav;
    }

    /**
     * @param bool|string|int $directionNav
     */
    public function setDirectionNav($directionNav): void
    {
        $this->directionNav = (bool)$directionNav;
    }

    /**
     * @return bool
     */
    public function getUseKB(): bool
    {
        return $this->useKB;
    }

    /**
     * @param bool|string|int $useKB
     */
    public function setUseKB($useKB): void
    {
        $this->useKB = (bool)$useKB;
    }
}
