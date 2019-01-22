<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

use OPC\Portlets\MissingPortlet;

class MissingPortletInstance extends PortletInstance
{
    /**
     * @var string
     */
    protected $missingClassName = '';

    /**
     * @param MissingPortlet $portlet
     * @param string         $missingClassName
     */
    public function __construct(MissingPortlet $portlet, string $missingClassName)
    {
        parent::__construct($portlet);
        $this->setMissingClassName($missingClassName);
    }

    /**
     * @return string
     */
    public function getMissingClassName(): string
    {
        return $this->missingClassName;
    }

    /**
     * @param string $missingClassName
     * @return $this
     */
    public function setMissingClassName(string $missingClassName): self
    {
        $this->missingClassName = $missingClassName;

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerializeShort()
    {
        $result = parent::jsonSerializeShort();

        $result['missingClassName'] = $this->getMissingClassName();

        return $result;
    }
}