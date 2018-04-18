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
    protected $widthHeuristics = [
        'lg' => 1,
        'md' => 1,
        'sm' => 1,
        'xs' => 1,
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
        return $this->properties[$name];
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
        if (!isset($this->properties['attributes'])) {
            $this->properties['attributes'] = [];
        }

        return $this->properties['attributes'][$name] ?? '';
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        if (!isset($this->properties['attributes'])) {
            $this->properties['attributes'] = [];
        }

        $this->properties['attributes'][$name] = $value;

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
        return $this->hasProperty('styles') ? $this->getProperty('styles') : [];
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setStyle($name, $value)
    {
        $styles        = $this->getStyles();
        $styles[$name] = $value;
        $this->setProperty('styles', $styles);
        $this->updateAttributes();

        return $this;
    }

    /**
     * @return $this
     */
    public function updateAttributes()
    {
        $styles      = $this->getStyles();
        $styleString = '';

        foreach ($styles as $styleName => $styleValue) {
            $styleString .= "$styleName: $styleValue; ";
        }

        $this->setAttribute('style', $styleString);

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeString()
    {
        $result = '';

        if (isset($this->properties['attributes']) && is_array($this->properties['attributes'])) {
            foreach ($this->properties['attributes'] as $name => $value) {
                $result .= "$name=\"$value\"";
            }
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
     * @return $this
     */
    public function setImageAttributes($src, $alt)
    {
        $this->setAttribute('alt', $alt);

        if (empty($src)) {
            $this->setAttribute('src', \Shop::getURL() . '/gfx/keinBild.gif');
            return $this;
        }

        $widthHeuristics = $this->widthHeuristics;
        $settings        = \Shop::getSettings([CONF_BILDER]);
        $name            = explode('/', $src);
        $name            = end($name);
        $srcset          = '';
        $srcsizes        = '';

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
            ksort($widthHeuristics);

            foreach ($widthHeuristics as $breakpoint => $col) {
                if (!empty($col)) {
                    switch ($breakpoint) {
                        case 'xs':
                            $breakpoint = 767;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) ' . (int)($col * 100) . 'vw, ';
                            break;
                        case 'sm':
                            $breakpoint = 768;
                            $srcsizes  .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint) . 'px, ';
                            break;
                        case 'md':
                            $breakpoint = 992;
                            $srcsizes  .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint) . 'px, ';
                            break;
                        case 'lg':
                            $breakpoint = 1200;
                            $srcsizes  .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint) . 'px, ';
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        $srcsizes .= '100vw';
        $src       = PFAD_MEDIAFILES . 'Bilder/.md/' . $name;

        $this->setAttribute('srcset', $srcset);
        $this->setAttribute('sizes', $srcsizes);
        $this->setAttribute('src', $src);

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
            'id'         => $this->portlet->getId(),
            'title'      => $this->portlet->getTitle(),
            'properties' => $this->properties,
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