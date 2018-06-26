<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class Blueprint
 * @package OPC
 */
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
    protected $instance;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return PortletInstance|null
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param PortletInstance|null $instance
     * @return $this;
     */
    public function setInstance($instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws \Exception
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
