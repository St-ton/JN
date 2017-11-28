<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CMSPortlet
 */
abstract class CMSPortlet
{
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
}
