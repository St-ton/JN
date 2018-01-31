<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
use Imanee\Imanee;

/**
 * Class CMSPortlet
 */
abstract class CMSPortlet
{
    /**
     * @var array -
     */
    protected static $dirSizes = [
        '.xs/' => WIDTH_CMS_IMAGE_XS,
        '.sm/' => WIDTH_CMS_IMAGE_SM,
        '.md/' => WIDTH_CMS_IMAGE_MD,
        '.lg/' => WIDTH_CMS_IMAGE_LG,
        '.xl/' => WIDTH_CMS_IMAGE_XL,
    ];

    /**
     * @var int
     */
    public $kPortlet = 0;

    /**
     * @var null|Plugin
     */
    public $oPlugin = null;

    /**
     * @var string
     */
    public $cTitle = '';

    /**
     * @var string
     */
    public $cGroup = '';

    /**
     * @var array
     */
    public $properties = [];

    /**
     * @var array
     */
    public $subAreas = [];

    /**
     * @param int $kPortlet
     * @throws Exception
     */
    public function __construct($kPortlet)
    {
        $kPortlet = (int)$kPortlet;

        if ($kPortlet === 0) {
            throw new Exception('Portlet ID is invalid.');
        }

        $oDbPortlet = Shop::DB()->select('tcmsportlet', 'kPortlet', $kPortlet);

        if (!is_object($oDbPortlet)) {
            throw new Exception('Portlet ID could not be found in the database.');
        }

        $this->kPortlet   = $oDbPortlet->kPortlet;
        $this->oPlugin    = new Plugin($oDbPortlet->kPlugin);
        $this->cTitle     = $oDbPortlet->cTitle;
        $this->cGroup     = $oDbPortlet->cGroup;
        $this->properties = $this->getDefaultProps();
    }

    /**
     * @return string - side panel button HTML
     */
    public function getButton()
    {
        return $this->cTitle;
    }

    /**
     * @return string - editor mode HTML content
     */
    public function getPreviewHtml()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getFullPreviewHtml()
    {
        phpQuery::newDocument();

        $html    = $this->getPreviewHtml();
        $portlet = pq($html);
        $portlet->attr('data-portletid', $this->kPortlet);
        $portlet->attr('data-portlettitle', $this->cTitle);
        $portlet->attr('data-properties', json_encode($this->properties));

        $subAreas = pq('.cle-area', $portlet);

        foreach ($this->subAreas as $i => $areaPortlets) {
            $subArea = pq($subAreas->elements[$i], $portlet);

            foreach ($areaPortlets as $areaPortlet) {
                try {
                    $subhtml       = CMS::getInstance()
                        ->createPortlet($areaPortlet['portletId'])
                        ->setProperties($areaPortlet['properties'])
                        ->setSubAreas($areaPortlet['subAreas'])
                        ->getFullPreviewHtml();
                    $pqAreaPortlet = pq($subhtml);
                    $pqAreaPortlet->attr('data-portlettitle', $areaPortlet['portletTitle']);
                    $pqAreaPortlet->attr('data-portletid', $areaPortlet['portletId']);
                    $pqAreaPortlet->attr('data-properties', $areaPortlet['properties']);
                    $subArea->append($pqAreaPortlet);
                } catch (Exception $e) {
                    // one portlet in this sub area could not be created
                }
            }
        }

        return $portlet->htmlOuter();
    }

    /**
     * @return string - front end-final HTML content
     */
    public function getFinalHtml()
    {
        return '';
    }

    /**
     * @return string - HTML for the portlet's configuration panel
     */
    public function getConfigPanelHtml()
    {
        return '';
    }

    /**
     * @return array - assoc. array mapping property names to default values
     */
    public function getDefaultProps()
    {
        return [];
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties($properties)
    {
        foreach ($properties as $key => $val) {
            $this->properties[$key] = $val;
        }

        return $this;
    }

    /**
     * @param array[] $subAreas
     * @return $this
     */
    public function setSubAreas($subAreas)
    {
        $this->subAreas = $subAreas;

        return $this;
    }

    /**
     * @return string
     */
    protected function getAttribString()
    {
        $animationStyle = $this->properties['animation-style'];

        if (!empty($animationStyle)) {
            $this->addClass('wow');
            $this->addClass($animationStyle);
        }

        $attribString = '';

        if (!empty($this->properties['attr']) && is_array($this->properties['attr'])) {
            foreach ($this->properties['attr'] as $name => $value) {
                if (trim($value) !== '') {
                    $attribString .= $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '" ';
                }
            }
        }

        return $attribString;
    }

    /**
     * @param string $cls - CSS class name
     * @return $this
     */
    protected function addClass($cls)
    {
        $classes = explode(' ', $this->properties['attr']['class']);

        if (!in_array($cls, $classes)) {
            $this->properties['attr']['class'] .= ' ' . $cls;
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getStyleString()
    {
        $styleString = '';

        if (!empty($this->properties['style']) && is_array($this->properties['style'])) {
            foreach ($this->properties['style'] as $name => $value) {
                if (trim($value) !== '') {
                    if (stripos($name, 'margin-') !== false ||
                        stripos($name, 'padding-') !== false ||
                        stripos($name, '-width') !== false
                    ) {
                        $styleString .= $name . ':' . htmlspecialchars($value, ENT_QUOTES) . 'px;';
                    } else {
                        $styleString .= $name . ':' . htmlspecialchars($value, ENT_QUOTES) . ';';
                    }
                }
            }
        }

        return $styleString !== '' ? 'style="' . $styleString . '"' : '';
    }

    /**
     * @param String $src
     * @param array|null $widthHeuristics
     * @return string
     */
    protected function getSrcString($src, $widthHeuristics = null)
    {
        if (empty($src)) {
            return 'src="' . BILD_KEIN_ARTIKELBILD_VORHANDEN . '"';
        }

        $settings  = Shop::getSettings([CONF_BILDER]);
        $name      = explode('/', $src);
        $name      = end($name);
        $srcString = 'srcset="';

        foreach (static::$dirSizes as $size => $width) {
            if (!file_exists(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $size . $name) === true) {
                $image     = new Imanee(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $name);
                $imageSize = $image->getSize();
                $factor    = $width / $imageSize['width'];
                $image
                    ->resize((int)$width, (int)($imageSize['height'] * $factor))
                    ->write(
                        PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $size . $name,
                        $settings['bilder']['bilder_jpg_quali']
                    );

                unset($image);
            }
            $srcString .= PFAD_MEDIAFILES . 'Bilder/' . $size . $name . ' ' . $width . 'w,';
        }

        $srcString  = substr($srcString, 0, -1) . '"'; // remove trailing comma and append double quote
        $srcString .= ' sizes="';

        if (is_array($widthHeuristics)) {
            ksort($widthHeuristics);

            foreach ($widthHeuristics as $breakpoint => $col) {
                if (!empty($col)) {
                    switch ($breakpoint) {
                        case 'xs':
                            $breakpoint = 767;
                            $srcString .= '(max-width: ' . $breakpoint . 'px) ' . (int)($col * 100) . 'vw, ';
                            break;
                        case 'sm':
                            $breakpoint = 768;
                            $srcString .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint) . 'px, ';
                            break;
                        case 'md':
                            $breakpoint = 992;
                            $srcString .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint) . 'px, ';
                            break;
                        case 'lg':
                            $breakpoint = 1200;
                            $srcString .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col * $breakpoint) . 'px, ';
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        $srcString .= '100vw" src="' . PFAD_MEDIAFILES . 'Bilder/.md/' . $name . '"';

        return $srcString;
    }

    /**
     * If this portlet has a property 'filters' set as an array of filters then return the filtered set of product keys
     *
     * @return int[] - filtered product keys
     */
    protected function getFilteredProductIds()
    {
        if (array_key_exists('filters', $this->properties) && is_array($this->properties['filters'])) {
            $filters = $this->properties['filters'];

            $productFilter = new ProductFilter();

            foreach ($filters as $filter) {
                $productFilter->addActiveFilter(new $filter['className']($productFilter), $filter['value']);
            }

            return $productFilter->getProductKeys();
        }

        return [];
    }
}
