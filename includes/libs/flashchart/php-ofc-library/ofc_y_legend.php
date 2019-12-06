<?php

class y_legend
{
    /**
     * @var string
     */
    public $style;

    /**
     * @var string
     */
    public $text;

    public function __construct($text = '')
    {
        $this->text = $text;
    }

    public function set_style($css)
    {
        $this->style = $css;
    }
}
