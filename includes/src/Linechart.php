<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

use stdClass;

/**
 * Class Linechart
 * @package JTL
 */
class Linechart extends Chartdata
{
    /**
     * @var array
     */
    public $series;

    /**
     * @var array
     */
    public $_xAxis;

    /**
     * @param string $label
     * @return $this
     */
    public function addAxis($label): self
    {
        if ($this->_xAxis === null) {
            $this->_xAxis             = new stdClass();
            $this->_xAxis->categories = [];
        }
        $this->_xAxis->labels               = new stdClass();
        $this->_xAxis->labels->style        = new stdClass();
        $this->_xAxis->labels->style->color = '#5cbcf6';
        $this->_xAxis->categories[]         = $label;

        return $this;
    }

    /**
     * @param        $name
     * @param array  $data
     * @param string $linecolor
     * @param string $color
     * @return $this
     */
    public function addSerie($name, array $data, $linecolor = '#5cbcf6', $areacolor = '#5cbcf6', $pointcolor = '#5cbcf6'): self
    {
        if ($this->_series === null) {
            $this->_series = [];
        }
        $serie                            = new stdClass();
        $serie->name                      = $name;
        $serie->data                      = $data;
        $serie->lineColor                 = $linecolor;
        $serie->color                     = $areacolor;
        $serie->marker                    = new stdClass();
        $serie->marker->lineColor         = $pointcolor;
        $serie->fillColor                 = new stdClass();
        $serie->fillColor->linearGradient = [0, 0, 0, 300];
        $serie->fillColor->stops          = [
            [0, $this->hex2rgba($areacolor, '0.9')],
            [0.7, $this->hex2rgba($areacolor, '0.0')]
        ];
        $this->_series[]                  = $serie;

        return $this;
    }

    private function hex2rgba($color, $opacity = false): string
    {
        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if (empty($color)) {
            return $default;
        }

        //Sanitize $color if "#" is provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        //Check if color has 6 or 3 characters and get values
        if (\strlen($color) === 6) {
            $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (\strlen($color) === 3) {
            $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb = array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if ($opacity) {
            if (abs($opacity) > 1) {
                $opacity = 1.0;
            }
            $output = 'rgba(' . implode(',', $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(',', $rgb) . ')';
        }

        //Return rgb(a) color string
        return $output;
    }
}
