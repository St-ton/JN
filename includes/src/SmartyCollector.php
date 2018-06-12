<?php

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * Class SmartyCollector
 */
class SmartyCollector extends DataCollector implements Renderable
{
    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * SmartyCollector constructor.
     * @param JTLSmarty $smarty
     */
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * @return array
     */
    public function collect()
    {
        $data = [];
        $vars = $this->smarty->getTemplateVars();

        foreach ($vars as $idx => $var) {
            $data[$idx] = $this->getDataFormatter()->formatVar($var);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'smarty';
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return [
            'smarty' => [
                'icon' => 'tags',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'smarty',
                'default' => '{}'
            ]
        ];
    }
}