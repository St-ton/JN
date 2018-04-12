<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

class Blueprint implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var null|PortletInstance
     */
    protected $instance = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return PortletInstance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param PortletInstance $instance
     * @return $this;
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function deserialize($data)
    {
        $this->setName($data['name']);
        $instance = \Shop::Container()->getOPC()->getPortletInstance($data['content']);
        $this->setInstance($instance);

        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'       => $this->getId(),
            'name'     => $this->getName(),
            'instance' => $this->instance->jsonSerialize(),
        ];
    }
}
