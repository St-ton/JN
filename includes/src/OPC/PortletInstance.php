<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class PortletInstance implements \JsonSerializable
{
    /**
     * @var array
     */
    protected static $dirSizes = [
        '.xs/' => WIDTH_CMS_IMAGE_XS,
        '.sm/' => WIDTH_CMS_IMAGE_SM,
        '.md/' => WIDTH_CMS_IMAGE_MD,
        '.lg/' => WIDTH_CMS_IMAGE_LG,
        '.xl/' => WIDTH_CMS_IMAGE_XL,
    ];

    /**
     * @var Portlet
     */
    protected $portlet;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $styles = [];

    /**
     * @var array
     */
    protected $animations = [];

    /**
     * @var array
     */
    protected $widthHeuristics = [
        'xs' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
    ];

    /**
     * @var null|AreaList mapping area ids to subareas
     */
    protected $subareaList = null;

    /**
     * PortletInstance constructor.
     * @param Portlet $portlet
     */
    public function __construct(Portlet $portlet)
    {
        $this->portlet     = $portlet;
        $this->properties  = $portlet->getDefaultProps();
        $this->subareaList = new AreaList();
    }

    /**
     * @return Portlet
     */
    public function getPortlet()
    {
        return $this->portlet;
    }

    /**
     * @return string
     */
    public function getPreviewHtml()
    {
        return $this->portlet->getPreviewHtml($this);
    }

    /**
     * @return string
     */
    public function getFinalHtml()
    {
        return $this->portlet->getFinalHtml($this);
    }

    /**
     * @return string
     */
    public function getConfigPanelHtml()
    {
        return $this->portlet->getConfigPanelHtml($this);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaPreviewHtml($id)
    {
        return $this->hasSubarea($id)
            ? $this->getSubarea($id)->getPreviewHtml()
            : '';
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaFinalHtml($id)
    {
        return $this->hasSubarea($id)
            ? $this->getSubarea($id)->getFinalHtml()
            : '';
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getProperty($name)
    {
        return $this->properties[$name] ?? '';
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return PortletInstance
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @return null|AreaList
     */
    public function getSubareaList()
    {
        return $this->subareaList;
    }

    /**
     * @param string $id
     * @return Area
     */
    public function getSubarea($id)
    {
        return $this->subareaList->getArea($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasSubarea($id)
    {
        return $this->subareaList->hasArea($id);
    }

    /**
     * @param Area $area
     * @return PortletInstance
     */
    public function putSubarea($area)
    {
        $this->subareaList->putArea($area);

        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name] ?? '';
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addClass($className)
    {
        $classes = explode(' ', $this->getAttribute('class'));

        if (!in_array($className, $classes)) {
            $classes[] = $className;
        }

        $this->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * @return array
     */
    public function getStyles()
    {
        foreach ($this->portlet->getStylesPropertyDesc() as $propname => $propdesc) {
            if ($this->hasProperty($propname)) {
                $this->setStyle($propname, $this->getProperty($propname));
            }
        }

        return $this->styles;
    }

    /**
     * @return array
     */
    public function getAnimations()
    {
        foreach ($this->portlet->getAnimationsPropertyDesc() as $propname => $propdesc) {
            if ($this->hasProperty($propname)) {
                $this->setAnimation($propname, $this->getProperty($propname));
            }
        }

        return $this->animations;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setStyle($name, $value)
    {
        $this->styles[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAnimation($name, $value)
    {
        $this->animations[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function updateAttributes()
    {
        $styles      = $this->getStyles();
        $styleString = '';
        $animations  = $this->getAnimations();

        foreach ($styles as $styleName => $styleValue) {
            if (!empty($styleValue)) {
                if (stripos($styleName, 'margin-') !== false ||
                    stripos($styleName, 'padding-') !== false ||
                    stripos($styleName, 'border-width') !== false ||
                    stripos($styleName, '-width') !== false ||
                    stripos($styleName, '-height') !== false
                ) {
                    $styleString .= "$styleName:" . htmlspecialchars($styleValue, ENT_QUOTES) . "px; ";
                } else {
                    $styleString .= "$styleName:" . htmlspecialchars($styleValue, ENT_QUOTES) . "; ";
                }
            }
        }

        $this->setAttribute('style', $styleString);

        foreach ($animations as $aniName => $aniValue) {
            if ($aniName === 'animation-style') {
                $this->addClass("wow " . $aniValue);
            } else {
                if (!empty($aniValue)) {
                    $this->setAttribute($aniName, $aniValue);
                }
            }
        }

        return $this;
    }

    public function getAttributes()
    {
        $this->updateAttributes();
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getAttributeString()
    {
        $result = '';

        foreach ($this->getAttributes() as $name => $value) {
            $result .= "$name='$value'";
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getDataAttributeString()
    {
        return 'data-portlet="' . htmlspecialchars(json_encode($this->jsonSerializeShort()), ENT_QUOTES) . '"';
    }

    /**
     * @param string $src
     * @param string $alt
     * @param string $title
     * @return array
     */
    public function getImageAttributes($src = null, $alt = null, $title = null, $divisor = 1)
    {
        $src      = $src ?? $this->getProperty('src');
        $alt      = $alt ?? $this->getProperty('alt');
        $title    = $title ?? $this->getProperty('title');
        $srcset   = '';
        $srcsizes = '';

        if (empty($src)) {
            $src = \Shop::getURL() . '/gfx/keinBild.gif';
            return [
                'srcset' => $srcset,
                'srcsizes' => $srcsizes,
                'src' => $src,
                'alt' => $alt,
                'title' => $title,
            ];
        }

        $widthHeuristics = $this->widthHeuristics;
        $settings        = \Shop::getSettings([CONF_BILDER]);
        $name            = explode('/', $src);
        $name            = end($name);

        foreach (static::$dirSizes as $size => $width) {
            $sizedImgPath = PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $size . $name;

            if (!file_exists($sizedImgPath) === true) {
                $image     = new \Imanee\Imanee(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $name);
                $imageSize = $image->getSize();
                $factor    = $width / $imageSize['width'];

                $image
                    ->resize((int)$width, (int)($imageSize['height'] * $factor))
                    ->write(
                        PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $size . $name,
                        $settings['bilder']['bilder_jpg_quali']
                    );
            }

            $srcset .= PFAD_MEDIAFILES . 'Bilder/' . $size . $name . ' ' . $width . 'w,';
        }

        $srcset = substr($srcset, 0, -1); // remove trailing comma

        if (is_array($widthHeuristics)) {

            foreach ($widthHeuristics as $breakpoint => $col) {
                if (!empty($col)) {
                    $factor = 1;

                    if (is_array($divisor) && !empty($divisor[$breakpoint])) {
                        $factor = (float)($divisor[$breakpoint] / 12);
                    }

                    switch ($breakpoint) {
                        case 'xs':
                            $breakpoint = 767;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) ' . (int)($col * 100 * $factor) . 'vw, ';
                            break;
                        case 'sm':
                            $breakpoint = 991;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint * $factor) . 'px, ';
                            break;
                        case 'md':
                            $breakpoint = 1199;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint * $factor) . 'px, ';
                            break;
                        case 'lg':
                            $breakpoint = 1200;
                            $srcsizes  .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint * $factor) . 'px, ';
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        $srcsizes .= '100vw';
        $src       = PFAD_MEDIAFILES . 'Bilder/.md/' . $name;

        return [
            'srcset' => $srcset,
            'srcsizes' => $srcsizes,
            'src' => $src,
            'alt' => $alt,
            'title' => $title,
        ];
    }

    /**
     * @param string $src
     * @param string $alt
     * @param string $title
     * @param int $divisor
     * @return $this
     */
    public function setImageAttributes($src = null, $alt = null, $title = null, $divisor = 1)
    {
        $imageAttributes = $this->getImageAttributes($src, $alt, $title);

        $this->setAttribute('srcset', $imageAttributes['srcset']);
        $this->setAttribute('sizes', $imageAttributes['srcsizes']);
        $this->setAttribute('src', $imageAttributes['src']);
        $this->setAttribute('alt', $imageAttributes['alt']);
        $this->setAttribute('title', $imageAttributes['title']);

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function deserialize($data)
    {
        if (isset($data['properties']) && is_array($data['properties'])) {
            foreach ($data['properties'] as $name => $value) {
                $this->setProperty($name, $value);
            }
        }

        if (isset($data['subareas']) && is_array($data['subareas'])) {
            foreach ($data['subareas'] as $areaData) {
                $area = new Area();
                $area->deserialize($areaData);
                $this->putSubarea($area);
            }
        }

        if (isset($data['widthHeuristics']) && is_array($data['widthHeuristics'])) {
            $this->widthHeuristics = $data['widthHeuristics'];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerializeShort()
    {
        $result = [
            'id'              => $this->portlet->getId(),
            'class'           => $this->portlet->getClass(),
            'title'           => $this->portlet->getTitle(),
            'properties'      => $this->properties,
            'widthHeuristics' => $this->widthHeuristics,
        ];

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result             = $this->jsonSerializeShort();
        $result['subareas'] = $this->subareaList->jsonSerialize();

        return $result;
    }
}