<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use Imanee\Imanee;
use JTL\Shop;

/**
 * Class PortletInstance
 * @package JTL\OPC
 */
class PortletInstance implements \JsonSerializable
{
    /**
     * @var array
     */
    protected static $dirSizes = [
        '.xs/' => \WIDTH_OPC_IMAGE_XS,
        '.sm/' => \WIDTH_OPC_IMAGE_SM,
        '.md/' => \WIDTH_OPC_IMAGE_MD,
        '.lg/' => \WIDTH_OPC_IMAGE_LG,
        '.xl/' => \WIDTH_OPC_IMAGE_XL,
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
    protected $subareaList;

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
    public function getPortlet(): Portlet
    {
        return $this->portlet;
    }

    /**
     * @return string
     */
    public function getPreviewHtml(): string
    {
        return $this->portlet->getPreviewHtml($this);
    }

    /**
     * @return string
     */
    public function getFinalHtml(): string
    {
        return $this->portlet->getFinalHtml($this);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml(): string
    {
        return $this->portlet->getConfigPanelHtml($this);
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaPreviewHtml($id): string
    {
        return $this->hasSubarea($id)
            ? $this->getSubarea($id)->getPreviewHtml()
            : '';
    }

    /**
     * @param string $id
     * @return string
     */
    public function getSubareaFinalHtml($id): string
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
     * @param mixed  $value
     * @return PortletInstance
     */
    public function setProperty($name, $value): self
    {
        $this->properties[$name] = $value;
        $desc                    = $this->portlet->getPropertyDesc();

        if ($desc['type'] === 'radio') {
            $this->properties[$name] = (bool)$value;
        } elseif ($desc['type'] === 'number') {
            $this->properties[$name] = (float)$value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name): bool
    {
        return \array_key_exists($name, $this->properties);
    }

    /**
     * @return null|AreaList
     */
    public function getSubareaList(): ?AreaList
    {
        return $this->subareaList;
    }

    /**
     * @param string $id
     * @return Area
     */
    public function getSubarea($id): Area
    {
        return $this->subareaList->getArea($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasSubarea($id): bool
    {
        return $this->subareaList->hasArea($id);
    }

    /**
     * @param Area $area
     * @return PortletInstance
     */
    public function putSubarea(Area $area): self
    {
        $this->subareaList->putArea($area);

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getAttribute($name): string
    {
        return $this->attributes[$name] ?? '';
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function addClass(string $className): self
    {
        $classes = \explode(' ', $this->getAttribute('class'));

        if (!\in_array($className, $classes, true)) {
            $classes[] = $className;
        }

        $this->setAttribute('class', \implode(' ', $classes));

        return $this;
    }

    /**
     * @return array
     */
    public function getStyles(): array
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
    public function getAnimations(): array
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
    public function setStyle($name, $value): self
    {
        $this->styles[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAnimation($name, $value): self
    {
        $this->animations[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getStyleString(): string
    {
        $styleString = '';

        foreach ($this->getStyles() as $styleName => $styleValue) {
            if (!empty($styleValue)) {
                if (\mb_strpos($styleName, 'hidden-') !== false && !empty($styleValue)) {
                    $this->addClass($styleName);
                } elseif (\mb_stripos($styleName, 'margin-') !== false
                    || \mb_stripos($styleName, 'padding-') !== false
                    || \mb_stripos($styleName, 'border-width') !== false
                    || \mb_stripos($styleName, '-width') !== false
                    || \mb_stripos($styleName, '-height') !== false
                ) {
                    $styleString .= $styleName . ':' . \htmlspecialchars($styleValue, \ENT_QUOTES) . 'px; ';
                } else {
                    $styleString .= $styleName . ':' . \htmlspecialchars($styleValue, \ENT_QUOTES) . '; ';
                }
            }
        }

        return $styleString;
    }

    /**
     * @return $this
     */
    public function updateAttributes(): self
    {
        $this->setAttribute('style', $this->getStyleString());

        foreach ($this->getAnimations() as $aniName => $aniValue) {
            if ($aniName === 'animation-style' && !empty($aniValue)) {
                $this->addClass('wow ' . $aniValue);
            } elseif (!empty($aniValue)) {
                $this->setAttribute($aniName, $aniValue);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        $this->updateAttributes();

        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getAttributeString(): string
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
    public function getDataAttributeString(): string
    {
        return 'data-portlet="' . $this->getDataAttribute() . '"';
    }

    /**
     * @return string
     */
    public function getDataAttribute(): string
    {
        return \htmlspecialchars(\json_encode($this->getData()), \ENT_QUOTES);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->jsonSerializeShort();
    }

    /**
     * @param string $src
     * @param string $alt
     * @param string $title
     * @param int    $divisor
     * @param string $default
     * @return array
     */
    public function getImageAttributes($src = null, $alt = null, $title = null, $divisor = 1, $default = null): array
    {
        $src      = $src ?? $this->getProperty('src');
        $alt      = $alt ?? $this->getProperty('alt');
        $title    = $title ?? $this->getProperty('title');
        $srcset   = '';
        $srcsizes = '';

        if (empty($src)) {
            $src = $default ?? Shop::getURL() . '/gfx/keinBild.gif';

            return [
                'srcset'   => $srcset,
                'srcsizes' => $srcsizes,
                'src'      => $src,
                'alt'      => $alt,
                'title'    => $title,
            ];
        }

        $widthHeuristics = $this->widthHeuristics;
        $settings        = Shop::getSettings([\CONF_BILDER]);
        $name            = \basename($src);

        foreach (static::$dirSizes as $size => $width) {
            $sizedImgPath = \PFAD_ROOT . \PFAD_MEDIAFILES . 'Bilder/' . $size . $name;
            if (!\file_exists($sizedImgPath) === true) {
                $image     = new Imanee(\PFAD_ROOT . \PFAD_MEDIAFILES . 'Bilder/' . $name);
                $imageSize = $image->getSize();
                $factor    = $width / $imageSize['width'];

                $image->resize((int)$width, (int)($imageSize['height'] * $factor))
                      ->write(
                          \PFAD_ROOT . \PFAD_MEDIAFILES . 'Bilder/' . $size . $name,
                          $settings['bilder']['bilder_jpg_quali']
                      );
            }

            $srcset .= \PFAD_MEDIAFILES . 'Bilder/' . $size . $name . ' ' . $width . 'w,';
        }

        $srcset = \mb_substr($srcset, 0, -1); // remove trailing comma

        if (\is_array($widthHeuristics)) {
            foreach ($widthHeuristics as $breakpoint => $col) {
                if (!empty($col)) {
                    $factor = 1;

                    if (\is_array($divisor) && !empty($divisor[$breakpoint])) {
                        $factor = (float)($divisor[$breakpoint] / 12);
                    }

                    switch ($breakpoint) {
                        case 'xs':
                            $breakpoint = 767;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) '
                                . (int)($col * 100 * $factor) . 'vw, ';
                            break;
                        case 'sm':
                            $breakpoint = 991;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) '
                                . (int)($col * $breakpoint * $factor) . 'px, ';
                            break;
                        case 'md':
                            $breakpoint = 1199;
                            $srcsizes  .= '(max-width: ' . $breakpoint . 'px) '
                                . (int)($col * $breakpoint * $factor) . 'px, ';
                            break;
                        case 'lg':
                            $breakpoint = 1200;
                            $srcsizes  .= '(min-width: ' . $breakpoint . 'px) '
                                . (int)($col * $breakpoint * $factor) . 'px, ';
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        $srcsizes .= '100vw';
        $src       = \PFAD_MEDIAFILES . 'Bilder/.md/' . $name;

        return [
            'srcset'   => $srcset,
            'srcsizes' => $srcsizes,
            'src'      => $src,
            'alt'      => $alt,
            'title'    => $title,
        ];
    }

    /**
     * @param null $src
     * @param null $alt
     * @param null $title
     * @param int  $divisor
     * @param null $default
     * @return string
     */
    public function getImageAttributeString(
        $src = null,
        $alt = null,
        $title = null,
        $divisor = 1,
        $default = null
    ): string {
        $imgAttribs = $this->getImageAttributes($src, $alt, $title, $divisor, $default);

        return "srcset='{$imgAttribs['srcset']}' srcsizes='{$imgAttribs['srcsizes']}' src='{$imgAttribs['src']}'
            alt='{$imgAttribs['alt']}' title='{$imgAttribs['title']}'";
    }

    /**
     * @param string $src
     * @param string $alt
     * @param string $title
     * @param int    $divisor
     * @param null   $default
     * @return $this
     */
    public function setImageAttributes($src = null, $alt = null, $title = null, $divisor = 1, $default = null): self
    {
        $imageAttributes = $this->getImageAttributes($src, $alt, $title, $divisor, $default);

        $this->setAttribute('srcset', $imageAttributes['srcset']);
        $this->setAttribute('srcsizes', $imageAttributes['srcsizes']);
        $this->setAttribute('src', $imageAttributes['src']);
        $this->setAttribute('alt', $imageAttributes['alt']);
        $this->setAttribute('title', $imageAttributes['title']);

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function deserialize($data)
    {
        if (isset($data['properties']) && \is_array($data['properties'])) {
            foreach ($data['properties'] as $name => $value) {
                $this->setProperty($name, $value);
            }
        }

        if (isset($data['subareas']) && \is_array($data['subareas'])) {
            foreach ($data['subareas'] as $areaData) {
                $area = new Area();
                $area->deserialize($areaData);
                $this->putSubarea($area);
            }
        }

        if (isset($data['widthHeuristics']) && \is_array($data['widthHeuristics'])) {
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
