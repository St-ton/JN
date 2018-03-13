<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

abstract class Portlet implements \JsonSerializable
{
    /**
     * @var PortletModel
     */
    protected $model;

    /**
     * Portlet constructor.
     * @param PortletModel $model
     */
    final public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getPreviewHtmlFromTpl($instance)
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->model->getClass() . '/preview.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getFinalHtmlFromTpl($instance)
    {
        return \Shop::Smarty()
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->model->getClass() . '/final.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    final protected function getConfigPanelHtmlFromTpl($instance)
    {
        return (new \JTLSmarty(true))
            ->assign('portlet', $this)
            ->assign('instance', $instance)
            ->fetch('portlets/' . $this->model->getClass() . '/configpanel.tpl');
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    final protected function getAutoConfigPanelHtml($instance)
    {
        $res = '';

        foreach ($this->getDefaultProps() as $name => $prop) {
            if ($instance->hasProperty($name)) {
                $prop = $instance->getProperty($name);
            }

            if (!is_array($prop)) {
                $title = ucfirst($name);
                $res  .= "<div class=\"form-group\">
                    <label for=\"config-$name\">$title</label>
                    <input type=\"text\" class=\"form-control\" name=\"$name\" value=\"$prop\" id=\"config-$name\">
                    </div>";
            }
        }

        return $res;
    }

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getPreviewHtml($instance);

    /**
     * @param PortletInstance $instance
     * @return string
     */
    abstract public function getFinalHtml($instance);

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getConfigPanelHtml($instance)
    {
        return '';
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->model->getTitle();
    }

    /**
     * @return array
     */
    public function getDefaultProps()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->model->getId();
    }

    /**
     * @return int
     */
    public function getPluginId()
    {
        return $this->model->getPluginId();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->model->getTitle();
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->model->getClass();
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->model->getGroup();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->model->isActive();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result                 = $this->model->jsonSerialize();
        $result['defaultProps'] = $this->getDefaultProps();
        $result['buttonHtml']   = $this->getButtonHtml();

        return $result;
    }

    /**
     * @param string $id
     * @return Portlet
     * @throws \Exception
     */
    public static function fromId($id)
    {
        $model = new PortletModel($id);
        $class = '\\OPC\\Portlets\\' . $model->getClass();

        return new $class($model);
    }
}