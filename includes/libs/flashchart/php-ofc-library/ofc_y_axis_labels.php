<?php

class y_axis_labels
{
    /**
     * @var string
     */
    public $steps;

    /**
     * @var string
     */
    public $colour;

    /**
     * @var string
     */
    public $text;

    /**
     * @var array
     */
    public $labels;

    /**
     * @var int
     */
    public $size;

    /**
     * @var int
     */
    public $rotate;

    public function __construct()
    {
    }

    /**
     * @param $steps
     */
    public function set_steps($steps)
    {
        $this->steps = $steps;
    }

    /**
     *
     * @param array $labels as an array of [y_axis_label or string]
     */
    public function set_labels($labels)
    {
        $this->labels = $labels;
    }

    public function set_colour($colour)
    {
        $this->colour = $colour;
    }

    /**
     * font size in pixels
     */
    public function set_size($size)
    {
        $this->size = $size;
    }

    /**
     * rotate labels
     */
    public function set_vertical()
    {
        $this->rotate = 270;
    }

    public function rotate($angle)
    {
        $this->rotate = $angle;
    }

    /**
     * @param string $text default text that all labels inherit
     */
    public function set_text($text)
    {
        $this->text = $text;
    }
}
