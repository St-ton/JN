<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\OPC;

use JTL\Helpers\GeneralObject;
use JTL\Media\Image;
use JTL\Media\MultiSizeImage;
use JTL\Shop;

/**
 * Class PortletInstance
 * @package JTL\OPC
 */
class PortletInstance implements \JsonSerializable
{
    use MultiSizeImage;

    /**
     * @var array
     */
    protected static $dirSizes = [
        Image::SIZE_XL => \WIDTH_OPC_IMAGE_XL,
        Image::SIZE_LG => \WIDTH_OPC_IMAGE_LG,
        Image::SIZE_MD => \WIDTH_OPC_IMAGE_MD,
        Image::SIZE_SM => \WIDTH_OPC_IMAGE_SM,
        Image::SIZE_XS => \WIDTH_OPC_IMAGE_XS,
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
     * @var string
     */
    protected $uid;

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
        $this->setImageType(Image::TYPE_OPC);
        $this->portlet     = $portlet;
        $this->properties  = $portlet->getDefaultProps();
        $this->subareaList = new AreaList();
        $this->uid         = 'uid_' . \uniqid('', false);
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
     * @throws \Exception
     */
    public function getPreviewHtml(): string
    {
        $result = $this->portlet->getPreviewHtml($this);

        Shop::fire('shop.OPC.PortletInstance.getPreviewHtml', [
            'portletInstance' => $this,
            'result' => &$result
        ]);

        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getFinalHtml(): string
    {
        $result = $this->portlet->getFinalHtml($this);

        Shop::fire('shop.OPC.PortletInstance.getFinalHtml', [
            'portletInstance' => $this,
            'result' => &$result
        ]);

        return $result;
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
     * @return string|null
     */
    public function getUid(): string
    {
        return $this->uid;
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
                if ($propname === 'box-styles') {
                    $boxStyles = $this->getProperty($propname);

                    foreach ($boxStyles as $styleName => $styleValue) {
                        $this->setStyle($styleName, $styleValue);
                    }
                } else {
                    $this->setStyle($propname, $this->getProperty($propname));
                }
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
                if (\mb_stripos($styleName, 'margin-') === 0
                    || \mb_stripos($styleName, 'padding-') === 0
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
     * @return string
     */
    public function getAnimationClass(): string
    {
        $style = $this->getProperty('animation-style');

        return $style !== '' ? 'wow ' . $style : '';
    }

    /**
     * @return array
     */
    public function getAnimationData(): array
    {
        $data = [];

        foreach ($this->portlet->getAnimationsPropertyDesc() as $propname => $propdesc) {
            if ($this->hasProperty($propname) && \strpos($propname, 'wow-') === 0 &&
                !empty($this->getProperty($propname))
            ) {
                $data[$propname] = $this->getProperty($propname);
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getAnimationDataAttributeString(): string
    {
        $res = '';

        foreach ($this->getAnimationData() as $key => $val) {
            $res .= ' data-' . $key . '="' . $val . '"';
        }

        return $res;
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
            $result .= ' ' . $name . '="' . $value . '"';
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
     * @param string    $src
     * @param string    $alt
     * @param string    $title
     * @param int|array $divisor
     * @param string    $default
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
            return [
                'srcset'   => '',
                'srcsizes' => '',
                'src'      => $default ?? '',
                'alt'      => $alt,
                'title'    => $title,
            ];
        }

        $decodedName = \rawurldecode(\basename($src));
        $this->generateAllImageSizes(true, 1, \PFAD_ROOT . \PFAD_MEDIAFILES . 'Bilder/' . $decodedName);
        foreach ($this->getImages() as $size => $i) {
            $width = self::$dirSizes[$size] ?? null;
            if ($width !== null) {
                $srcset .= $i . ' ' . $width . 'w,';
            }
        }
        $srcset = \mb_substr($srcset, 0, -1); // remove trailing comma
        foreach ($this->widthHeuristics as $breakpoint => $col) {
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

        $srcsizes .= '100vw';

        return [
            'srcset'   => $srcset,
            'srcsizes' => $srcsizes,
            'src'      => $this->getImage(),
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
        if (GeneralObject::isCountable('properties', $data)) {
            foreach ($data['properties'] as $name => $value) {
                $this->setProperty($name, $value);
            }
        }

        if (GeneralObject::isCountable('subareas', $data)) {
            foreach ($data['subareas'] as $areaData) {
                $area = new Area();
                $area->deserialize($areaData);
                $this->putSubarea($area);
            }
        }

        if (GeneralObject::isCountable('widthHeuristics', $data)) {
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
