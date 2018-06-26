<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class PortletGroup
 * @package OPC
 */
class PortletGroup
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var Portlet[]
     */
    protected $portlets = [];

    /**
     * PortletGroup constructor.
     * @param string $name
     * @throws \Exception
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return Portlet[]
     */
    public function getPortlets(): array
    {
        return $this->portlets;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Portlet $portlet
     * @return $this
     */
    public function addPortlet(Portlet $portlet): self
    {
        $this->portlets[] = $portlet;

        return $this;
    }
}
