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
        '.xl/' => WIDTH_CMS_IMAGE_XL
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
            $this->properties['attr']['class'] .= ' wow ' . $animationStyle;
        }

        $attr_str = '';

        if (!empty($this->properties['attr']) && is_array($this->properties['attr'])) {
            foreach ($this->properties['attr'] as $name => $value) {
                if (trim($value) !== '') {
                    $attr_str .= $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '" ';
                }
            }
        }

        return $attr_str !== '' ? ' ' . $attr_str : '';
    }

    /**
     * @return string
     */
    protected function getStyleString()
    {
        $style_str = '';

        if (!empty($this->properties['style']) && is_array($this->properties['style'])) {
            foreach ($this->properties['style'] as $name => $value) {
                if (trim($value) !== '') {
                    if (stripos($name, 'margin-') !== false ||
                        stripos($name, 'padding-') !== false ||
                        stripos($name, '-width') !== false
                    ) {
                        $style_str .= $name . ':' . htmlspecialchars($value, ENT_QUOTES) . 'px;';
                    } else {
                        $style_str .= $name . ':' . htmlspecialchars($value, ENT_QUOTES) . ';';
                    }
                }
            }
        }

        return $style_str !== '' ? ' style="' . $style_str . '"' : '';
    }

    /**
     * @param String $src
     * @return string
     */
    /*protected function getSrcString($src, $calcWidth = 100)
    {
        if (empty($src)) {
            return ' src="' . BILD_KEIN_ARTIKELBILD_VORHANDEN . '"';
        }

        // EVO specific CSS styles
        $containerWidth = 1140;
        $finalWidth     = (int)($containerWidth / 100 * $calcWidth);

        $settings  = Shop::getSettings([CONF_BILDER]);
        $name      = explode('/', $src);
        $name      = end($name);
        $srcString = ' srcset="';

        foreach (static::$dirSizes as $sizeDir => $width) {
            if (!file_exists(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $sizeDir . $name) === true) {
                $image     = new Imanee(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $name);
                $imageSize = $image->getSize();
                $factor    = $width / $imageSize['width'];

                $image
                    ->resize((int)$width, (int)($imageSize['height'] * $factor))
                    ->write(
                        PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $sizeDir . $name,
                        $settings['bilder']['bilder_jpg_quali']
                    );

                unset($image);
            }
            $srcString .= PFAD_MEDIAFILES . 'Bilder/' . $sizeDir . $name . ' ' . $width . 'w,';
        }

        $srcString = substr($srcString, 0, -1) . '" sizes="' . $finalWidth . 'px" src="' . PFAD_MEDIAFILES
            . 'Bilder/.lg/' . $name . '"';

        return $srcString;
    }*/

    /**
     * @param String $src
     * @return string
     */
    protected function getSrcString($src, $colWidths = false)
    {
        if (empty($src)) {
            return ' src="' . BILD_KEIN_ARTIKELBILD_VORHANDEN . '"';
        }
        // EVO specific CSS styles
        $containerWidth = 1140;
        $finalWidth = (int)($containerWidth*0.9);
        $settings = Shop::getSettings([CONF_BILDER]);

        $size_arr = [
            '.xs/' => WIDTH_CMS_IMAGE_XS,
            '.sm/' => WIDTH_CMS_IMAGE_SM,
            '.md/' => WIDTH_CMS_IMAGE_MD,
            '.lg/' => WIDTH_CMS_IMAGE_LG,
            '.xl/' => WIDTH_CMS_IMAGE_XL
        ];
        $name = explode('/', $src);
        $name = end($name);
        $srcString = ' srcset="';

        foreach ($size_arr as $size => $width){
            if (!file_exists(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $size . $name) === true){
                $image = new Imanee(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $name);
                $imageSize = $image->getSize();
                $factor = $width/$imageSize['width'];
                $image->resize((int)$width, (int)($imageSize['height']*$factor))
                    ->write(PFAD_ROOT . PFAD_MEDIAFILES . 'Bilder/' . $size . $name, $settings['bilder']['bilder_jpg_quali']);

                unset($image);
            }
            $srcString .= PFAD_MEDIAFILES . 'Bilder/' . $size . $name . ' ' . $width . 'w,';
        }

        $srcString = substr($srcString, 0, -1) . '"';
        $srcString .= ' sizes="';
        // todo editor: mit kleinster größe anfangen?
        if (!empty($colWidths)) {
            foreach ($colWidths as $breakpoint => $col) {
                switch ($breakpoint){
                    case 'xs':
                        $breakpoint = 767;
                        $srcString .= '(max-width: ' . $breakpoint . 'px) ' . (int)($col/12*$breakpoint) . 'px, ' ;
                        break;
                    case 'sm':
                        $breakpoint = 768;
                        $srcString .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col/12*$breakpoint) . 'px, ' ;
                        break;
                    case 'md':
                        $breakpoint = 992;
                        $srcString .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col/12*$breakpoint) . 'px, ' ;
                        break;
                    case 'lg':
                        $breakpoint = 1200;
                        $srcString .= '(min-width: ' . $breakpoint . 'px) ' . (int)($col/12*$breakpoint) . 'px, ' ;
                        break;
                    default:
                        break;
                }
            }
        }
        $srcString .= $finalWidth . 'px" src="' . PFAD_MEDIAFILES . 'Bilder/.md/' . $name . '"';

        return $srcString;
    }
}
